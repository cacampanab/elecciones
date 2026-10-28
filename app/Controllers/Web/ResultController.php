<?php

namespace App\Controllers\Web;

class ResultController extends BaseWebController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('results.read')) {
            return $redirect;
        }

        $elections = model('App\Models\ElectionModel')->getActive();
        $dignities = model('App\Models\DignityModel')->where('is_active', 1)->findAll();
        $provinces = model('App\Models\ProvinceModel')->where('is_active', 1)->findAll();
        return $this->render('results/index', compact('elections', 'dignities', 'provinces'), 'Resultados');
    }

    public function register()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (!has_permission('results.create') && !has_permission('results.update')) {
            return redirect()->to(base_url('results'))->with('error', 'Su perfil es de solo lectura: no puede registrar resultados.');
        }

        $userId = session()->get('user_id');
        $myStations = model('App\Models\AssignmentModel')->getUserAssignments($userId);
        $dignities = model('App\Models\DignityModel')->where('is_active', 1)->findAll();
        $elections = model('App\Models\ElectionModel')->getActive();
        $provinces = model('App\Models\ProvinceModel')->where('is_active', 1)->findAll();
        return $this->render('results/register', compact('myStations', 'dignities', 'elections', 'provinces'), 'Registrar Resultados');
    }

    public function save()
    {
        if ($redirect = $this->requireAnyPermission(['results.create', 'results.update'])) {
            return $redirect;
        }

        $psId = (int)$this->request->getPost('polling_station_id');
        $electionId = (int)$this->request->getPost('election_id');
        $dignityId = (int)$this->request->getPost('dignity_id');
        $totalVoters = (int)$this->request->getPost('total_voters');
        $blankVotes = (int)($this->request->getPost('blank_votes') ?? 0);
        $nullVotes = (int)($this->request->getPost('null_votes') ?? 0);
        $registeredVoters = (int)$this->request->getPost('registered_voters');
        $votes = $this->request->getPost('votes') ?: [];
        $latitude = $this->request->getPost('latitude') ?: null;
        $longitude = $this->request->getPost('longitude') ?: null;
        $notes = trim((string)$this->request->getPost('notes'));
        $reason = trim((string)$this->request->getPost('reason'));

        if (!$psId || !$electionId || !$dignityId || empty($votes)) {
            return redirect()->back()->with('error', 'Datos incompletos: seleccione la dignidad y registre los votos.');
        }
        if ($redirect = $this->requireAssignment($psId, $electionId)) {
            return $redirect;
        }

        $service = new \App\Libraries\ActaService();
        if (!in_array($dignityId, $service->enabledDignityIds($electionId), true)) {
            return redirect()->back()->with('error', 'La dignidad no está habilitada para esta elección.');
        }

        $candidateModel = model('App\Models\CandidateModel');
        $validVotes = [];
        foreach ($votes as $candidateId => $voteCount) {
            $candidate = $candidateModel->find((int)$candidateId);
            if (!$candidate) {
                continue;
            }
            if ((int)$candidate->dignity_id !== $dignityId || (int)$candidate->election_id !== $electionId) {
                continue;
            }
            $validVotes[(int)$candidateId] = max(0, (int)$voteCount);
        }
        if (!$validVotes) {
            return redirect()->back()->with('error', 'No hay candidatos válidos para la dignidad seleccionada.');
        }

        $resultModel = model('App\Models\ElectionResultModel');
        $minuteModel = model('App\Models\UploadedMinuteModel');
        $userId = (int)session()->get('user_id');

        $currentMinute = $service->currentMinute($psId, $electionId, $dignityId);
        $hasResults = $resultModel
            ->where('polling_station_id', $psId)
            ->where('election_id', $electionId)
            ->where('dignity_id', $dignityId)
            ->countAllResults() > 0;
        $isCorrection = $currentMinute || $hasResults;

        if (is_operator() && $currentMinute && $currentMinute->status !== 'con_novedad') {
            return redirect()->back()->with('error', 'El acta de esta dignidad ya fue registrada. Solo puede corregirla cuando tenga una novedad.');
        }
        if ($currentMinute && $currentMinute->status === 'verificada' && !is_supervisor_or_admin()) {
            return redirect()->back()->with('error', 'El acta de esta dignidad está verificada y no puede modificarse.');
        }
        if ($isCorrection && $reason === '') {
            return redirect()->back()->with('error', 'Indique el motivo de la corrección.');
        }

        $actaFile = $this->request->getFile('acta_file');
        $hasFile = $actaFile && $actaFile->isValid() && $actaFile->getSize() > 0
            && $actaFile->getTempName() && file_exists($actaFile->getTempName());

        if (!$currentMinute && !$hasFile) {
            return redirect()->back()->with('error', 'Debe adjuntar el acta de esta dignidad.');
        }

        $ext = null;
        if ($hasFile) {
            $ext = strtolower($actaFile->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf']) || $actaFile->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'El acta debe ser JPG, PNG o PDF de máximo 10 MB.');
            }
        }

        $this->db->transBegin();
        try {
            $versionsCount = $this->db->table('result_versions')
                ->where('polling_station_id', $psId)
                ->where('election_id', $electionId)
                ->where('dignity_id', $dignityId)
                ->countAllResults();
            if ($isCorrection && $versionsCount === 0) {
                $service->snapshot($psId, $electionId, $dignityId, $userId, 'Estado previo a la corrección');
            }

            $hasVerified = $resultModel
                ->where('polling_station_id', $psId)
                ->where('election_id', $electionId)
                ->where('dignity_id', $dignityId)
                ->where('is_verified', 1)
                ->countAllResults() > 0;
            if ($hasVerified) {
                $resultModel
                    ->where('polling_station_id', $psId)
                    ->where('election_id', $electionId)
                    ->where('dignity_id', $dignityId)
                    ->set(['is_verified' => 0, 'verified_by' => null, 'verified_at' => null])
                    ->update();
            }

            $saved = 0;
            foreach ($validVotes as $candidateId => $voteCount) {
                $existing = $resultModel
                    ->where('polling_station_id', $psId)
                    ->where('election_id', $electionId)
                    ->where('dignity_id', $dignityId)
                    ->where('candidate_id', $candidateId)
                    ->first();

                $data = [
                    'polling_station_id' => $psId,
                    'candidate_id' => $candidateId,
                    'election_id' => $electionId,
                    'dignity_id' => $dignityId,
                    'votes' => (int)$voteCount,
                    'blank_votes' => $blankVotes,
                    'null_votes' => $nullVotes,
                    'total_voters' => $totalVoters,
                    'registered_voters' => $registeredVoters,
                    'is_verified' => 0,
                    'verified_by' => null,
                    'verified_at' => null,
                    'registered_by' => $userId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];

                if ($existing) {
                    $resultModel->update($existing->id, $data);
                } else {
                    $resultModel->insert($data);
                }
                $saved++;
            }

            $consistency = $service->consistency($psId, $electionId, $dignityId);
            $newStatus = $consistency['status'] === 'error' ? 'con_novedad' : 'registrada';

            if ($hasFile) {
                $prev = $service->currentMinute($psId, $electionId, $dignityId);
                $version = $prev ? ((int)$prev->version + 1) : 1;
                if ($prev) {
                    $minuteModel
                        ->where('polling_station_id', $psId)
                        ->where('election_id', $electionId)
                        ->where('dignity_id', $dignityId)
                        ->where('is_current', 1)
                        ->set(['is_current' => 0])
                        ->update();
                }

                $newName = $actaFile->getRandomName();
                $folder = FCPATH . 'uploads/minutes/' . $electionId . '/' . $psId;
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }
                $actaFile->move($folder, $newName);

                $minuteModel->insert([
                    'polling_station_id' => $psId,
                    'election_id' => $electionId,
                    'dignity_id' => $dignityId,
                    'filename' => $newName,
                    'original_name' => $actaFile->getClientName(),
                    'file_path' => $folder . '/' . $newName,
                    'file_type' => $ext,
                    'file_size' => $actaFile->getSize(),
                    'version' => $version,
                    'is_current' => 1,
                    'status' => $newStatus,
                    'notes' => $notes ?: null,
                    'consistency_status' => $consistency['status'],
                    'consistency_detail' => $consistency['detail'],
                    'uploaded_by' => $userId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
            } elseif ($currentMinute) {
                $minuteModel->update($currentMinute->id, [
                    'status' => $newStatus,
                    'notes' => $notes ?: $currentMinute->notes,
                    'consistency_status' => $consistency['status'],
                    'consistency_detail' => $consistency['detail'],
                    'verified_by' => null,
                    'verified_at' => null,
                ]);
            }

            $service->updateStationStatus($psId, $electionId);
            $service->snapshot($psId, $electionId, $dignityId, $userId, $reason ?: ($isCorrection ? 'Corrección' : 'Registro inicial'));

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al guardar resultados: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo guardar: ' . $e->getMessage());
        }

        $this->logAudit('register_results', 'election_results', $psId, null, [
            'election_id' => $electionId,
            'dignity_id' => $dignityId,
            'saved' => $saved,
            'consistency' => $consistency['status'],
            'reason' => $reason ?: null,
        ]);

        $msg = "Resultados registrados para la dignidad seleccionada ({$saved} candidatos).";
        if ($consistency['status'] === 'error') {
            $msg .= ' Inconsistencia detectada: ' . $consistency['detail'];
        } elseif (!empty($consistency['warnings'])) {
            $msg .= ' Advertencia: ' . implode(' | ', $consistency['warnings']);
        }
        if ($currentMinute && $currentMinute->status === 'verificada') {
            $msg .= ' El acta fue desverificada por la edición.';
        }

        return redirect()->to(base_url('results/register'))
            ->with($consistency['status'] === 'error' ? 'warning' : 'success', $msg);
    }
}
