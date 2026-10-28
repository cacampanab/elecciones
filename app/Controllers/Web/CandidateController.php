<?php

namespace App\Controllers\Web;

class CandidateController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $filterElection = (int)($this->request->getGet('election_id') ?? 0);
        $filterDignity = (int)($this->request->getGet('dignity_id') ?? 0);

        $candidates = model('App\Models\CandidateModel')->getWithRelations($filterElection ?: null, $filterDignity ?: null);
        $elections = model('App\Models\ElectionModel')->getActive();
        $dignities = model('App\Models\DignityModel')->where('is_active', 1)->findAll();
        $parties = model('App\Models\PoliticalPartyModel')->where('is_active', 1)->findAll();
        return $this->render('candidates/index', compact('candidates', 'elections', 'dignities', 'parties', 'filterElection', 'filterDignity'), 'Candidatos');
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $model = model('App\Models\CandidateModel');
        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'cedula' => $this->request->getPost('cedula') ?: null,
            'dignity_id' => $this->request->getPost('dignity_id'),
            'political_party_id' => $this->request->getPost('political_party_id'),
            'candidate_list_id' => $this->request->getPost('candidate_list_id') ?: null,
            'election_id' => $this->request->getPost('election_id'),
        ];
        if ($id) { $model->update($id, $data); $this->logAudit('update_candidate', 'candidates', $id); }
        else { $id = $model->insert($data); $this->logAudit('create_candidate', 'candidates', $id); }
        return redirect()->to(base_url('candidates'))->with('success', 'Candidato guardado');
    }

    public function delete($id)
    {
        model('App\Models\CandidateModel')->delete($id);
        $this->logAudit('delete_candidate', 'candidates', $id);
        return redirect()->to(base_url('candidates'))->with('success', 'Candidato eliminado');
    }

    public function import()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $electionId = (int)$this->request->getPost('election_id');
        if (!$electionId) {
            return redirect()->back()->with('error', 'Debe seleccionar una elección');
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            $error = $file ? $file->getErrorString() : 'No se recibió ningún archivo';
            return redirect()->back()->with('error', 'Archivo no válido: ' . $error);
        }

        if ($file->getSize() > 20 * 1024 * 1024) {
            return redirect()->back()->with('error', 'El archivo excede el tamaño máximo de 20MB');
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            return redirect()->back()->with('error', 'Solo se aceptan archivos .xlsx o .xls');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $rows = $spreadsheet->getActiveSheet()->toArray();

            if (count($rows) < 2) {
                return redirect()->back()->with('error', 'El archivo Excel está vacío');
            }

            $candModel = model('App\Models\CandidateModel');
            $dignityModel = model('App\Models\DignityModel');
            $partyModel = model('App\Models\PoliticalPartyModel');

            $dignities = $dignityModel->findAll();
            $imported = 0;
            $skipped = 0;
            $partiesCreated = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                if ($index == 0) continue;
                $line = $index + 1;

                $dignityRef = trim((string)($row[0] ?? ''));
                $partyAcronym = trim((string)($row[1] ?? ''));
                $firstName = trim((string)($row[2] ?? ''));
                $lastName = trim((string)($row[3] ?? ''));
                $cedula = trim((string)($row[4] ?? ''));

                if ($dignityRef === '' && $firstName === '' && $lastName === '') continue;

                if ($firstName === '' || $lastName === '') {
                    $errors[] = "Fila $line: nombres y apellidos son obligatorios";
                    continue;
                }
                if ($partyAcronym === '') {
                    $errors[] = "Fila $line: la sigla del partido es obligatoria";
                    continue;
                }

                $dignity = null;
                foreach ($dignities as $d) {
                    if (strcasecmp($d->slug, $dignityRef) === 0 || strcasecmp($d->name, $dignityRef) === 0) {
                        $dignity = $d;
                        break;
                    }
                }
                if (!$dignity) {
                    $errors[] = "Fila $line: dignidad \"$dignityRef\" no encontrada";
                    continue;
                }

                $party = $partyModel->where('acronym', $partyAcronym)->first();
                if (!$party) {
                    $party = $partyModel->where('name', $partyAcronym)->first();
                }
                if (!$party) {
                    $newPartyId = $partyModel->insert([
                        'name' => $partyAcronym,
                        'acronym' => mb_substr($partyAcronym, 0, 20),
                        'list_number' => 0,
                        'color' => null,
                        'is_active' => 1,
                    ]);
                    $party = $partyModel->find($newPartyId);
                    $partiesCreated++;
                }

                if ($cedula !== '') {
                    if ($candModel->where('cedula', $cedula)->first()) { $skipped++; continue; }
                } else {
                    $dup = $candModel->where('election_id', $electionId)
                        ->where('dignity_id', $dignity->id)
                        ->where('political_party_id', $party->id)
                        ->where('first_name', $firstName)
                        ->where('last_name', $lastName)
                        ->first();
                    if ($dup) { $skipped++; continue; }
                }

                $candModel->insert([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'cedula' => $cedula !== '' ? $cedula : null,
                    'dignity_id' => $dignity->id,
                    'political_party_id' => $party->id,
                    'candidate_list_id' => null,
                    'election_id' => $electionId,
                    'is_active' => 1,
                ]);
                $imported++;
            }

            $this->logAudit('import_candidates', 'candidates', $electionId, ['importados' => $imported, 'omitidos' => $skipped], null);

            $msg = "¡$imported candidatos importados correctamente!";
            if ($partiesCreated > 0) $msg .= " ($partiesCreated partidos creados automáticamente)";
            if ($skipped > 0) $msg .= " ($skipped omitidos por duplicados)";

            if (!empty($errors)) {
                $preview = array_slice($errors, 0, 8);
                $msg .= ' | Errores: ' . implode('; ', $preview);
                if (count($errors) > 8) $msg .= ' ... y ' . (count($errors) - 8) . ' más';
                return redirect()->to(base_url('candidates'))->with('warning', $msg);
            }

            return redirect()->to(base_url('candidates'))->with('success', $msg);

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return redirect()->back()->with('error', 'Error al leer el archivo Excel. Verifique el formato.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    public function template()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Candidatos');
        $sheet->fromArray([
            ['Dignidad', 'Partido', 'Nombres', 'Apellidos', 'Cédula'],
            ['Presidente', 'ADN', 'JUAN CARLOS', 'PEREZ GOMEZ', '0102030405'],
            ['Asambleísta Nacional', 'RC', 'MARIA ELENA', 'LOPEZ RUIZ', '0102030406'],
            ['Alcalde', 'MPD', 'PEDRO', 'MARTINEZ', ''],
        ]);
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE7F1FF');
        foreach (['A' => 24, 'B' => 16, 'C' => 24, 'D' => 24, 'E' => 16] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->freezePane('A2');

        ob_start();
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="plantilla_candidatos.xlsx"')
            ->setBody($content);
    }
}
