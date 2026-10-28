<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class PollingStationController extends BaseController
{
    public function index()
    {
        $model = model('App\Models\PollingStationModel');
        $electionId = $this->request->getVar('election_id');
        $status = $this->request->getVar('status');

        if ($electionId) $model->where('election_id', $electionId);
        if ($status) $model->where('status', $status);

        $stations = $model->findAll();
        return jsonResponse($stations);
    }

    public function show($id = null)
    {
        $station = model('App\Models\PollingStationModel')->find($id);
        if (!$station) return errorResponse('JRV no encontrada', 404);
        return jsonResponse($station);
    }

    public function create()
    {
        $rules = [
            'precinct_id' => 'required|numeric',
            'election_id' => 'required|numeric',
            'numero_junta' => 'required',
            'numero_electores' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $id = model('App\Models\PollingStationModel')->insert($data);
        $this->logAudit('create_polling_station', 'polling_stations', $id, null, $data);

        return jsonResponse(['id' => $id], 'JRV creada', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\PollingStationModel');
        if (!$model->find($id)) return errorResponse('JRV no encontrada', 404);

        $data = $this->request->getPost();
        $model->update($id, $data);
        $this->logAudit('update_polling_station', 'polling_stations', $id);

        return jsonResponse(null, 'JRV actualizada');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\PollingStationModel');
        if (!$model->find($id)) return errorResponse('JRV no encontrada', 404);

        $model->delete($id);
        $this->logAudit('delete_polling_station', 'polling_stations', $id);

        return jsonResponse(null, 'JRV eliminada');
    }

    public function import()
    {
        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return errorResponse('Archivo no válido', 400);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $model = model('App\Models\PollingStationModel');
            $precinctModel = model('App\Models\PrecinctModel');
            $electionId = $this->request->getVar('election_id');
            $imported = 0;

            foreach ($rows as $index => $row) {
                if ($index == 0) continue;

                $codeProvince = $row[0] ?? '';
                $nameProvince = $row[1] ?? '';
                $codeCanton = $row[2] ?? '';
                $nameCanton = $row[3] ?? '';
                $codeParish = $row[4] ?? '';
                $nameParish = $row[5] ?? '';
                $codePrecinct = $row[6] ?? '';
                $namePrecinct = $row[7] ?? '';
                $juntaFemInicio = (int)($row[8] ?? 0);
                $juntaFemFin = (int)($row[9] ?? 0);
                $juntaMascInicio = (int)($row[10] ?? 0);
                $juntaMascFin = (int)($row[11] ?? 0);
                $numeroElectores = (int)($row[13] ?? 0);

                $precinct = $precinctModel->where('code', $codePrecinct)->first();
                if (!$precinct) continue;

                // Female juntas
                if ($juntaFemInicio > 0 && $juntaFemFin >= $juntaFemInicio) {
                    for ($j = $juntaFemInicio; $j <= $juntaFemFin; $j++) {
                        $exists = $model->where('precinct_id', $precinct->id)
                            ->where('election_id', $electionId)
                            ->where('genero', 'femenina')
                            ->where('numero_junta', 'F-' . $j)
                            ->first();
                        if ($exists) continue;
                        $model->insert([
                            'precinct_id' => $precinct->id, 'election_id' => $electionId,
                            'code_province' => $codeProvince, 'name_province' => $nameProvince,
                            'code_canton' => $codeCanton, 'name_canton' => $nameCanton,
                            'code_parish' => $codeParish, 'name_parish' => $nameParish,
                            'code_precinct' => $codePrecinct, 'name_precinct' => $namePrecinct,
                            'genero' => 'femenina', 'numero_junta' => 'F-' . $j,
                            'numero_electores' => 0,
                        ]);
                        $imported++;
                    }
                }

                // Male juntas
                if ($juntaMascInicio > 0 && $juntaMascFin >= $juntaMascInicio) {
                    for ($j = $juntaMascInicio; $j <= $juntaMascFin; $j++) {
                        $exists = $model->where('precinct_id', $precinct->id)
                            ->where('election_id', $electionId)
                            ->where('genero', 'masculina')
                            ->where('numero_junta', 'M-' . $j)
                            ->first();
                        if ($exists) continue;
                        $model->insert([
                            'precinct_id' => $precinct->id, 'election_id' => $electionId,
                            'code_province' => $codeProvince, 'name_province' => $nameProvince,
                            'code_canton' => $codeCanton, 'name_canton' => $nameCanton,
                            'code_parish' => $codeParish, 'name_parish' => $nameParish,
                            'code_precinct' => $codePrecinct, 'name_precinct' => $namePrecinct,
                            'genero' => 'masculina', 'numero_junta' => 'M-' . $j,
                            'numero_electores' => 0,
                        ]);
                        $imported++;
                    }
                }
            }

            $this->logAudit('import_polling_stations', 'polling_stations', null, null, ['imported' => $imported]);
            return jsonResponse(['imported' => $imported], "$imported JRV importadas correctamente", 201);
        } catch (\Exception $e) {
            return errorResponse('Error al procesar archivo: ' . $e->getMessage(), 500);
        }
    }

    public function stats()
    {
        $stats = model('App\Models\PollingStationModel')->getStats();
        return jsonResponse($stats);
    }

    public function myStations()
    {
        $userId = $this->getUserId();
        $electionId = $this->request->getVar('election_id');

        $assignments = model('App\Models\AssignmentModel')->getUserAssignments($userId, $electionId);
        return jsonResponse($assignments);
    }

    public function updateStatus($id = null)
    {
        $model = model('App\Models\PollingStationModel');
        if (!$model->find($id)) return errorResponse('JRV no encontrada', 404);

        $status = $this->request->getVar('status');
        $validStatuses = ['pendiente', 'en_proceso', 'reportada', 'revisada', 'aprobada'];
        if (!in_array($status, $validStatuses)) {
            return errorResponse('Estado inválido', 400);
        }

        $model->update($id, ['status' => $status]);
        $this->logAudit('update_station_status', 'polling_stations', $id, null, ['status' => $status]);

        return jsonResponse(null, 'Estado actualizado');
    }
}
