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
        $totalVoters = $this->normalizeInteger($this->request->getPost('total_voters'));
        $blankVotes = $this->normalizeInteger($this->request->getPost('blank_votes'));
        $nullVotes = $this->normalizeInteger($this->request->getPost('null_votes'));
        $registeredVoters = $this->normalizeInteger($this->request->getPost('registered_voters'));
        foreach ([
            'total de sufragantes' => $totalVoters,
            'votos blancos' => $blankVotes,
            'votos nulos' => $nullVotes,
            'electores en el acta' => $registeredVoters,
        ] as $fieldLabel => $fieldValue) {
            if ($fieldValue === null) {
                return redirect()->back()->with('error', 'El campo ' . $fieldLabel . ' debe ser un número entero.');
            }
        }
        $votes = $this->request->getPost('votes') ?: [];
        $listVotes = $this->request->getPost('list_votes') ?: [];
        $latitude = $this->request->getPost('latitude') ?: null;
        $longitude = $this->request->getPost('longitude') ?: null;
        $notes = trim((string)$this->request->getPost('notes'));
        $reason = trim((string)$this->request->getPost('reason'));

        if (!$psId || !$electionId || !$dignityId) {
            return redirect()->back()->with('error', 'Datos incompletos: seleccione la JRV y la dignidad.');
        }
        if ($redirect = $this->requireAssignment($psId, $electionId)) {
            return $redirect;
        }

        $service = new \App\Libraries\ActaService();
        if (!in_array($dignityId, $service->enabledDignityIds($electionId), true)) {
            return redirect()->back()->with('error', 'La dignidad no está habilitada para esta elección.');
        }

        $dignity = model('App\Models\DignityModel')->find($dignityId);
        if ($dignity && in_array($dignity->slug, ['concejal-urbano', 'concejal-rural'], true)) {
            return $this->saveConcejalResults(
                $psId,
                $electionId,
                $dignityId,
                $totalVoters,
                $blankVotes,
                $nullVotes,
                $registeredVoters,
                is_array($listVotes) ? $listVotes : [],
                $latitude,
                $longitude,
                $notes,
                $reason
            );
        }

        if (empty($votes)) {
            return redirect()->back()->with('error', 'Datos incompletos: seleccione la dignidad y registre los votos.');
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
            $voteValue = $this->normalizeInteger($voteCount);
            if ($voteValue === null) {
                return redirect()->back()->with('error', 'Los votos de los candidatos deben ser números enteros.');
            }
            $validVotes[(int)$candidateId] = $voteValue;
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

        $actaFile = $this->uploadedActaFile();
        $hasFile = $actaFile !== null;

        if (!$currentMinute && !$hasFile) {
            return redirect()->back()->with('error', 'Debe adjuntar el acta de esta dignidad.' . $this->actaUploadErrorDetail());
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

    private function saveConcejalResults(
        int $psId,
        int $electionId,
        int $dignityId,
        int $totalVoters,
        int $blankVotes,
        int $nullVotes,
        int $registeredVoters,
        array $listVotes,
        ?string $latitude,
        ?string $longitude,
        string $notes,
        string $reason
    ) {
        $station = model('App\Models\PollingStationModel')->find($psId);
        if (!$station || (int)$station->election_id !== $electionId) {
            return redirect()->back()->with('error', 'La JRV no pertenece a la elección seleccionada.');
        }

        $canton = $this->db->table('precincts pr')
            ->select('pa.id AS parish_id, pa.name AS parish_name, c.id AS canton_id')
            ->join('parishes pa', 'pa.id = pr.parish_id')
            ->join('cantons c', 'c.id = pa.canton_id')
            ->where('pr.id', (int)$station->precinct_id)
            ->get()
            ->getRow();
        if (!$canton) {
            return redirect()->back()->with('error', 'No se pudo determinar el cantón de la JRV.');
        }

        $circumscription = null;
        $hasCircumscriptions = $this->db->table('election_circumscriptions')
            ->where('election_id', $electionId)
            ->where('canton_id', (int)$canton->canton_id)
            ->where('is_active', 1)
            ->countAllResults() > 0;

        if ($hasCircumscriptions) {
            $circumscription = $this->db->table('election_circumscription_parishes ecp')
                ->select('ec.id, ec.name, ec.dignity_id')
                ->join('election_circumscriptions ec', 'ec.id = ecp.circumscription_id')
                ->where('ecp.election_id', $electionId)
                ->where('ecp.parish_id', (int)$canton->parish_id)
                ->where('ec.is_active', 1)
                ->get()
                ->getRow();

            if (!$circumscription) {
                return redirect()->back()->with('error', 'La parroquia ' . $canton->parish_name . ' no tiene circunscripción asignada.');
            }
            if ((int)$circumscription->dignity_id !== $dignityId) {
                $expected = model('App\Models\DignityModel')->find((int)$circumscription->dignity_id);
                return redirect()->back()->with('error', 'Esta JRV corresponde a ' . ($expected->name ?? 'otra dignidad') . ', no a la dignidad seleccionada.');
            }
        }

        $listsBuilder = $this->db->table('candidate_lists')
            ->select('id')
            ->where('election_id', $electionId)
            ->where('canton_id', (int)$canton->canton_id)
            ->where('dignity_id', $dignityId)
            ->where('is_active', 1);
        if ($circumscription) {
            $listsBuilder->where('circumscription_id', (int)$circumscription->id);
        }
        $lists = $listsBuilder->get()->getResult();
        if (!$lists) {
            return redirect()->back()->with('error', $circumscription
                ? 'No hay listas de concejales para la circunscripción de esta JRV.'
                : 'No hay listas de concejales para el cantón de esta JRV.');
        }

        $validListIds = array_map(static fn($list) => (int)$list->id, $lists);
        $validVotes = [];
        foreach ($listVotes as $listId => $value) {
            $listId = (int)$listId;
            if (!in_array($listId, $validListIds, true)) {
                continue;
            }
            $votes = $this->normalizeInteger($value);
            if ($votes === null) {
                return redirect()->back()->with('error', 'Los votos por lista deben ser números enteros mayores o iguales a cero.');
            }
            $validVotes[$listId] = $votes;
        }
        if (!$validVotes) {
            return redirect()->back()->with('error', 'No hay listas válidas para registrar los votos.');
        }

        $totalListVotes = array_sum($validVotes);
        $sum = $totalListVotes + $blankVotes + $nullVotes;
        $errors = [];
        $warnings = [];

        if ($sum !== $totalVoters) {
            $diff = $sum - $totalVoters;
            $sign = $diff > 0 ? '+' : '';
            $errors[] = "Votos de listas + blancos + nulos = {$sum}, sufragantes = {$totalVoters} (diferencia {$sign}{$diff})";
        }
        if ($totalVoters > $registeredVoters) {
            $errors[] = "Sufragantes ({$totalVoters}) supera electores del acta ({$registeredVoters})";
        }
        if ((int)$station->numero_electores !== $registeredVoters) {
            $warnings[] = 'Electores del acta (' . $registeredVoters . ') no coincide con el distributivo (' . (int)$station->numero_electores . ')';
        }
        foreach ($validVotes as $votes) {
            if ($votes > $totalVoters) {
                $errors[] = 'Una lista tiene más votos que el total de sufragantes.';
                break;
            }
        }

        $consistency = [
            'status' => $errors ? 'error' : 'ok',
            'detail' => $errors || $warnings ? implode(' | ', array_merge($errors, $warnings)) : null,
            'warnings' => $warnings,
        ];

        $service = new \App\Libraries\ActaService();
        $userId = (int)session()->get('user_id');
        $currentMinute = $service->currentMinute($psId, $electionId, $dignityId);
        $hasResults = model('App\Models\ElectionListResultModel')
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

        $actaFile = $this->uploadedActaFile();
        $hasFile = $actaFile !== null;

        if (!$currentMinute && !$hasFile) {
            return redirect()->back()->with('error', 'Debe adjuntar el acta de esta dignidad.' . $this->actaUploadErrorDetail());
        }

        $ext = null;
        if ($hasFile) {
            $ext = strtolower($actaFile->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf']) || $actaFile->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'El acta debe ser JPG, PNG o PDF de máximo 10 MB.');
            }
        }

        $minuteModel = model('App\Models\UploadedMinuteModel');
        $newStatus = $consistency['status'] === 'error' ? 'con_novedad' : 'registrada';

        $this->db->transBegin();
        try {
            $versionsCount = $this->db->table('result_versions')
                ->where('polling_station_id', $psId)
                ->where('election_id', $electionId)
                ->where('dignity_id', $dignityId)
                ->countAllResults();

            if ($isCorrection && $versionsCount === 0) {
                $previousRows = model('App\Models\ElectionListResultModel')->forStation($psId, $electionId, $dignityId);
                $previousData = [];
                foreach ($previousRows as $row) {
                    $previousData[] = [
                        'candidate_list_id' => (int)$row->candidate_list_id,
                        'votes' => (int)$row->votes,
                        'blank_votes' => (int)$row->blank_votes,
                        'null_votes' => (int)$row->null_votes,
                        'total_voters' => (int)$row->total_voters,
                        'registered_voters' => (int)$row->registered_voters,
                    ];
                }

                $this->db->table('result_versions')->insert([
                    'polling_station_id' => $psId,
                    'election_id' => $electionId,
                    'dignity_id' => $dignityId,
                    'version' => 1,
                    'data' => json_encode(['list_results' => $previousData], JSON_UNESCAPED_UNICODE),
                    'reason' => 'Estado previo a la corrección',
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->table('election_list_results')
                ->where('polling_station_id', $psId)
                ->where('election_id', $electionId)
                ->where('dignity_id', $dignityId)
                ->delete();

            foreach ($validVotes as $listId => $votes) {
                $this->db->table('election_list_results')->insert([
                    'polling_station_id' => $psId,
                    'election_id' => $electionId,
                    'canton_id' => (int)$canton->canton_id,
                    'circumscription_id' => $circumscription ? (int)$circumscription->id : null,
                    'dignity_id' => $dignityId,
                    'candidate_list_id' => $listId,
                    'votes' => $votes,
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
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

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
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al guardar resultados de concejales: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo guardar: ' . $e->getMessage());
        }

        $this->logAudit('register_concejal_results', 'election_list_results', $psId, null, [
            'election_id' => $electionId,
            'dignity_id' => $dignityId,
            'lists' => count($validVotes),
            'total_list_votes' => $totalListVotes,
            'consistency' => $consistency['status'],
            'reason' => $reason ?: null,
        ]);

        $msg = 'Resultados de concejales registrados para ' . count($validVotes) . ' lista(s).';
        if ($consistency['status'] === 'error') {
            $msg .= ' Inconsistencia detectada: ' . $consistency['detail'];
        } elseif (!empty($consistency['warnings'])) {
            $msg .= ' Advertencia: ' . implode(' | ', $consistency['warnings']);
        }

        return redirect()->to(base_url('results/register'))
            ->with($consistency['status'] === 'error' ? 'warning' : 'success', $msg);
    }

    /**
     * Devuelve el archivo de acta válido.
     *
     * La vista usa dos inputs separados (cámara y galería) con nombres
     * distintos. Antes ambos se llamaban igual y PHP conservaba solo el
     * último, por lo que un input vacío anulaba la foto tomada con la cámara.
     */
    private function uploadedActaFile()
    {
        foreach (['acta_file', 'acta_file_camera', 'acta_file_gallery'] as $field) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && (int)$file->getSize() > 0) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Traduce el código de error de subida de PHP para poder diagnosticar
     * problemas de configuración del servidor (tamaño, permisos, tmp, etc.).
     */
    private function actaUploadErrorDetail(): string
    {
        foreach (['acta_file', 'acta_file_camera', 'acta_file_gallery'] as $field) {
            $file = $this->request->getFile($field);
            if (!$file) {
                continue;
            }

            $error = (int)$file->getError();
            if ($error === UPLOAD_ERR_OK) {
                continue;
            }

            switch ($error) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return ' El archivo supera el tamaño máximo permitido por el servidor (' . ini_get('upload_max_filesize') . ').';
                case UPLOAD_ERR_PARTIAL:
                    return ' La subida del archivo se interrumpió. Intente nuevamente.';
                case UPLOAD_ERR_NO_TMP_DIR:
                    return ' El servidor no tiene una carpeta temporal de subida configurada.';
                case UPLOAD_ERR_CANT_WRITE:
                    return ' El servidor no pudo guardar el archivo temporal (permisos de escritura).';
                case UPLOAD_ERR_EXTENSION:
                    return ' Una extensión de PHP bloqueó la subida del archivo.';
            }
        }

        return ' No se recibió ningún archivo. Vuelva a seleccionar la foto o el archivo.';
    }

    /**
     * Convierte valores como "050" en 50. Devuelve null si no es un
     * número entero válido (sin signos, decimales ni letras).
     */
    private function normalizeInteger($value): ?int
    {
        $raw = trim((string)$value);
        if ($raw === '') {
            return 0;
        }
        if (!preg_match('/^\d+$/', $raw)) {
            return null;
        }

        $raw = ltrim($raw, '0');

        return $raw === '' ? 0 : (int)$raw;
    }
}
