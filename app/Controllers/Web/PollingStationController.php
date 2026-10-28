<?php

namespace App\Controllers\Web;

class PollingStationController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        $electionModel = model('App\Models\ElectionModel');
        $elections = $electionModel->getActive();

        $selectedElection = (int)($this->request->getGet('election_id') ?? 0);
        if (!$selectedElection) {
            $current = $electionModel->getCurrent();
            if ($current) {
                $selectedElection = (int)$current->id;
            } elseif (!empty($elections)) {
                $selectedElection = (int)$elections[0]->id;
            }
        }

        $selectedElectionName = '';
        foreach ($elections as $e) {
            if ((int)$e->id === $selectedElection) {
                $selectedElectionName = $e->name;
                break;
            }
        }

        $stations = [];
        if ($selectedElection) {
            $stations = model('App\Models\PollingStationModel')
                ->where('election_id', $selectedElection)
                ->orderBy('name_province')
                ->orderBy('name_canton')
                ->orderBy('name_parish')
                ->orderBy('name_precinct')
                ->orderBy('genero', 'ASC')
                ->orderBy('numero_junta', 'ASC')
                ->findAll();
        }

        // Add has_results and total_votes
        $db = \Config\Database::connect();
        $psIds = array_column($stations, 'id');
        $withResults = [];
        if (!empty($psIds)) {
            $results = $db->table('election_results')
                ->select('polling_station_id, SUM(votes) as total_votes')
                ->whereIn('polling_station_id', $psIds)
                ->groupBy('polling_station_id')
                ->get()->getResult();
            foreach ($results as $r) {
                $withResults[$r->polling_station_id] = $r->total_votes;
            }
        }
        foreach ($stations as $s) {
            $s->has_results = isset($withResults[$s->id]);
            $s->total_votes = $withResults[$s->id] ?? 0;
        }

        return $this->render('polling_stations/index', compact('stations', 'elections', 'selectedElection', 'selectedElectionName'), 'JRV');
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        $model = model('App\Models\PollingStationModel');
        if (!$model->find($id)) {
            return redirect()->back()->with('error', 'JRV no encontrada');
        }

        $data = [];
        if ($this->request->getPost('numero_electores') !== null) {
            $data['numero_electores'] = $this->request->getPost('numero_electores');
        }
        if ($this->request->getPost('status')) {
            $data['status'] = $this->request->getPost('status');
        }

        $model->update($id, $data);
        $this->logAudit('update_polling_station', 'polling_stations', $id);
        return redirect()->to(base_url('polling-stations'))->with('success', 'JRV actualizada correctamente');
    }

    public function import()
    {
        $file = $this->request->getFile('file');
        $electionId = $this->request->getPost('election_id');

        if (!$electionId) {
            return redirect()->back()->with('error', 'Debe seleccionar una elección');
        }

        $replace = $this->request->getPost('replace') ? true : false;
        if ($replace) {
            $redirect = $this->requireRole('Administrador General');
            if ($redirect) return $redirect;
        }

        if (!$file || !$file->isValid()) {
            $error = $file ? $file->getErrorString() : 'No se recibió ningún archivo';
            return redirect()->back()->with('error', 'Archivo no válido: ' . $error);
        }

        $maxSize = 20 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            return redirect()->back()->with('error', 'El archivo excede el tamaño máximo de 20MB');
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            return redirect()->back()->with('error', 'Solo se aceptan archivos .xlsx o .xls');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return redirect()->back()->with('error', 'El archivo Excel está vacío');
            }

            $psModel = model('App\Models\PollingStationModel');
            $provinceModel = model('App\Models\ProvinceModel');
            $cantonModel = model('App\Models\CantonModel');
            $parishModel = model('App\Models\ParishModel');
            $precinctModel = model('App\Models\PrecinctModel');

            $imported = 0;
            $skipped = 0;
            $created = 0;
            $replaced = 0;
            if ($replace) {
                $replaced = $this->purgeElectionStations($electionId);
            }

            foreach ($rows as $index => $row) {
                if ($index == 0) continue;

                $codeProvince = trim((string)($row[0] ?? ''));
                $nameProvince = trim((string)($row[1] ?? ''));
                $codeCanton = trim((string)($row[2] ?? ''));
                $nameCanton = trim((string)($row[3] ?? ''));
                $codeParish = trim((string)($row[4] ?? ''));
                $nameParish = trim((string)($row[5] ?? ''));
                $codePrecinct = trim((string)($row[6] ?? ''));
                $namePrecinct = trim((string)($row[7] ?? ''));
                $juntaFemInicio = trim((string)($row[8] ?? ''));
                $juntaFemFin = trim((string)($row[9] ?? ''));
                $juntaMascInicio = trim((string)($row[10] ?? ''));
                $juntaMascFin = trim((string)($row[11] ?? ''));
                $numeroJunta = trim((string)($row[12] ?? ''));
                $numeroElectores = (int)($row[13] ?? 0);

                // Skip empty rows
                if (empty($codeProvince) && empty($nameProvince)) continue;

                // Auto-create province
                $province = $provinceModel->where('code', $codeProvince)->first();
                if (!$province) {
                    $provId = $provinceModel->insert([
                        'code' => $codeProvince, 'name' => $nameProvince,
                        'capital' => '', 'region' => '',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $province = $provinceModel->find($provId);
                    $created++;
                }

                // Auto-create canton
                $canton = $cantonModel->where('code', $codeCanton)->first();
                if (!$canton) {
                    $canId = $cantonModel->insert([
                        'province_id' => $province->id, 'code' => $codeCanton,
                        'name' => $nameCanton, 'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $canton = $cantonModel->find($canId);
                    $created++;
                }

                // Auto-create parish
                $parish = $parishModel->where('code', $codeParish)->first();
                if (!$parish) {
                    $parId = $parishModel->insert([
                        'canton_id' => $canton->id, 'code' => $codeParish,
                        'name' => $nameParish, 'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $parish = $parishModel->find($parId);
                    $created++;
                }

                // Auto-create precinct
                $precinct = $precinctModel->where('code', $codePrecinct)->first();
                if (!$precinct) {
                    $preId = $precinctModel->insert([
                        'parish_id' => $parish->id, 'code' => $codePrecinct,
                        'name' => $namePrecinct, 'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $precinct = $precinctModel->find($preId);
                    $created++;
                }

                $femInicio = (int)$juntaFemInicio;
                $femFin = (int)$juntaFemFin;
                $mascInicio = (int)$juntaMascInicio;
                $mascFin = (int)$juntaMascFin;

                $femCount = ($femInicio > 0 && $femFin >= $femInicio) ? ($femFin - $femInicio + 1) : 0;
                $mascCount = ($mascInicio > 0 && $mascFin >= $mascInicio) ? ($mascFin - $mascInicio + 1) : 0;
                $totalJuntas = $femCount + $mascCount;
                $electoresBase = $totalJuntas > 0 ? intdiv($numeroElectores, $totalJuntas) : 0;
                $electoresResto = $totalJuntas > 0 ? ($numeroElectores % $totalJuntas) : 0;

                // Expand female juntas
                if ($femCount > 0) {
                    for ($k = 0; $k < $femCount; $k++) {
                        $numJunta = 'F-' . ($femInicio + $k);
                        $exists = $psModel
                            ->where('precinct_id', $precinct->id)
                            ->where('election_id', $electionId)
                            ->where('genero', 'femenina')
                            ->where('numero_junta', $numJunta)
                            ->first();
                        if ($exists) { $skipped++; continue; }

                        $electores = $electoresBase + ($k < $electoresResto ? 1 : 0);
                        $psModel->insert([
                            'precinct_id' => $precinct->id,
                            'election_id' => $electionId,
                            'code_province' => $codeProvince,
                            'name_province' => $nameProvince,
                            'code_canton' => $codeCanton,
                            'name_canton' => $nameCanton,
                            'code_parish' => $codeParish,
                            'name_parish' => $nameParish,
                            'code_precinct' => $codePrecinct,
                            'name_precinct' => $namePrecinct,
                            'genero' => 'femenina',
                            'numero_junta' => $numJunta,
                            'numero_electores' => $electores,
                        ]);
                        $imported++;
                    }
                }

                // Expand male juntas
                if ($mascCount > 0) {
                    for ($k = 0; $k < $mascCount; $k++) {
                        $numJunta = 'M-' . ($mascInicio + $k);
                        $exists = $psModel
                            ->where('precinct_id', $precinct->id)
                            ->where('election_id', $electionId)
                            ->where('genero', 'masculina')
                            ->where('numero_junta', $numJunta)
                            ->first();
                        if ($exists) { $skipped++; continue; }

                        $electores = $electoresBase + (($femCount + $k) < $electoresResto ? 1 : 0);
                        $psModel->insert([
                            'precinct_id' => $precinct->id,
                            'election_id' => $electionId,
                            'code_province' => $codeProvince,
                            'name_province' => $nameProvince,
                            'code_canton' => $codeCanton,
                            'name_canton' => $nameCanton,
                            'code_parish' => $codeParish,
                            'name_parish' => $nameParish,
                            'code_precinct' => $codePrecinct,
                            'name_precinct' => $namePrecinct,
                            'genero' => 'masculina',
                            'numero_junta' => $numJunta,
                            'numero_electores' => $electores,
                        ]);
                        $imported++;
                    }
                }
            }

            $this->logAudit('import_polling_stations', 'polling_stations');

            $msg = "¡$imported JRV importadas correctamente!";
            if ($replaced > 0) $msg = "Se eliminaron $replaced JRV anteriores. " . $msg;
            if ($created > 0) $msg .= " ($created registros territoriales creados automáticamente)";
            if ($skipped > 0) $msg .= " ($skipped omitidas por duplicadas)";
            return redirect()->to(base_url('polling-stations'))->with('success', $msg);

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return redirect()->back()->with('error', 'Error al leer el archivo Excel. Verifique el formato.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    public function deleteAll()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $redirect = $this->requireRole('Administrador General');
        if ($redirect) return $redirect;

        $electionId = (int)$this->request->getPost('election_id');
        if (!$electionId) {
            return redirect()->back()->with('error', 'Debe seleccionar una elección');
        }

        $count = $this->purgeElectionStations($electionId);
        $this->logAudit('delete_all_polling_stations', 'polling_stations', $electionId, ['eliminadas' => $count], null);

        if ($count === 0) {
            return redirect()->to(base_url('polling-stations'))->with('error', 'No había JRV para la elección seleccionada');
        }

        return redirect()->to(base_url('polling-stations'))->with('success', "Se eliminaron $count JRV de la elección seleccionada");
    }

    private function purgeElectionStations($electionId)
    {
        $db = \Config\Database::connect();
        $psIds = array_column(
            $db->table('polling_stations')->select('id')->where('election_id', $electionId)->get()->getResultArray(),
            'id'
        );
        if (empty($psIds)) return 0;

        $minutes = $db->table('uploaded_minutes')->select('file_path')->whereIn('polling_station_id', $psIds)->get()->getResult();
        foreach ($minutes as $m) {
            if (!empty($m->file_path) && is_file($m->file_path)) {
                @unlink($m->file_path);
            }
        }

        $db->table('polling_stations')->whereIn('id', $psIds)->delete();
        return count($psIds);
    }
}
