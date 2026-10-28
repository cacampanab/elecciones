<?php

namespace App\Controllers\Web;

use App\Libraries\ActaService;

class MinuteController extends BaseWebController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('minutes.read')) {
            return $redirect;
        }

        $electionId = (int)($this->request->getGet('election_id') ?? 0);
        $dignityId = (int)($this->request->getGet('dignity_id') ?? 0);
        $status = (string)($this->request->getGet('status') ?? '');
        $missing = (int)($this->request->getGet('missing') ?? 0);
        $search = trim((string)($this->request->getGet('q') ?? ''));

        $elections = model('App\Models\ElectionModel')->getActive();
        if (!$electionId) {
            $current = model('App\Models\ElectionModel')->getCurrent();
            $electionId = $current ? (int)$current->id : (int)($elections[0]->id ?? 0);
        }

        $service = new ActaService();
        $dignityIds = $electionId ? $service->enabledDignityIds($electionId) : [];
        $dignities = $dignityIds
            ? model('App\Models\DignityModel')->whereIn('id', $dignityIds)->orderBy('id', 'ASC')->findAll()
            : [];

        $assignedIds = null;
        if (is_operator()) {
            $rows = $this->db->table('assignments')
                ->select('polling_station_id')
                ->where('user_id', (int)session()->get('user_id'))
                ->where('is_active', 1)
                ->get()
                ->getResultArray();
            $assignedIds = array_map('intval', array_column($rows, 'polling_station_id'));
        }

        if ($missing && $electionId) {
            $minutes = $this->missingActas($electionId, $dignityId, $search, $assignedIds);
        } else {
            $builder = model('App\Models\UploadedMinuteModel')
                ->select('uploaded_minutes.*, d.name AS dignity_name, users.first_name, users.last_name, polling_stations.numero_junta, polling_stations.genero, polling_stations.name_province, polling_stations.name_canton, polling_stations.name_parish, polling_stations.name_precinct, elections.name as election_name')
                ->join('dignities d', 'd.id = uploaded_minutes.dignity_id', 'left')
                ->join('users', 'users.id = uploaded_minutes.uploaded_by', 'left')
                ->join('polling_stations', 'polling_stations.id = uploaded_minutes.polling_station_id')
                ->join('elections', 'elections.id = uploaded_minutes.election_id')
                ->where('uploaded_minutes.is_current', 1)
                ->orderBy('uploaded_minutes.created_at', 'DESC');

            if ($electionId) {
                $builder->where('uploaded_minutes.election_id', $electionId);
            }
            if ($dignityId) {
                $builder->where('uploaded_minutes.dignity_id', $dignityId);
            }
            if ($status === 'error') {
                $builder->where('uploaded_minutes.consistency_status', 'error');
            } elseif ($status !== '') {
                $builder->where('uploaded_minutes.status', $status);
            }
            if ($search !== '') {
                $builder->groupStart()
                    ->like('polling_stations.numero_junta', $search)
                    ->orLike('polling_stations.name_precinct', $search)
                    ->groupEnd();
            }
            if ($assignedIds !== null) {
                if (!$assignedIds) {
                    $builder->where('uploaded_minutes.id', -1);
                } else {
                    $builder->whereIn('uploaded_minutes.polling_station_id', $assignedIds);
                }
            }

            $minutes = $builder->findAll();
        }

        return $this->render('minutes/index', compact('minutes', 'elections', 'dignities', 'electionId', 'dignityId', 'status', 'missing', 'search'), 'Actas');
    }

    private function missingActas(int $electionId, int $dignityId, string $search, ?array $assignedIds): array
    {
        $sql = 'SELECT ps.*, ps.id AS polling_station_id, ed.dignity_id, d.name AS dignity_name, e.name AS election_name,
                       NULL AS id, NULL AS file_type, NULL AS file_size, NULL AS created_at, NULL AS original_name,
                       NULL AS first_name, NULL AS last_name, NULL AS status, NULL AS consistency_status
                FROM polling_stations ps
                JOIN election_dignities ed ON ed.election_id = ps.election_id AND ed.is_active = 1
                JOIN dignities d ON d.id = ed.dignity_id
                JOIN elections e ON e.id = ps.election_id
                LEFT JOIN uploaded_minutes um ON um.polling_station_id = ps.id
                    AND um.election_id = ps.election_id
                    AND um.dignity_id = ed.dignity_id
                    AND um.is_current = 1
                WHERE ps.election_id = ? AND um.id IS NULL';
        $params = [$electionId];

        if ($dignityId) {
            $sql .= ' AND ed.dignity_id = ?';
            $params[] = $dignityId;
        }
        if ($search !== '') {
            $sql .= ' AND (ps.numero_junta LIKE ? OR ps.name_precinct LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($assignedIds !== null) {
            if (!$assignedIds) {
                return [];
            }
            $placeholders = implode(',', array_fill(0, count($assignedIds), '?'));
            $sql .= " AND ps.id IN ($placeholders)";
            $params = array_merge($params, $assignedIds);
        }

        $sql .= ' ORDER BY ps.name_province, ps.name_canton, ps.name_parish, ps.name_precinct, ps.numero_junta LIMIT 500';

        return $this->db->query($sql, $params)->getResult();
    }

    public function upload()
    {
        if ($redirect = $this->requirePermission('minutes.upload')) {
            return $redirect;
        }

        $pollingStationId = (int)$this->request->getPost('polling_station_id');
        $electionId = (int)$this->request->getPost('election_id');
        $dignityId = (int)$this->request->getPost('dignity_id');
        $notes = trim((string)$this->request->getPost('notes'));
        $reason = trim((string)$this->request->getPost('reason'));

        if (!$pollingStationId || !$electionId || !$dignityId) {
            return redirect()->back()->with('error', 'Seleccione elección, JRV y dignidad.');
        }
        if ($redirect = $this->requireAssignment($pollingStationId, $electionId)) {
            return $redirect;
        }

        $service = new ActaService();
        if (!in_array($dignityId, $service->enabledDignityIds($electionId), true)) {
            return redirect()->back()->with('error', 'La dignidad no está habilitada para esta elección.');
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Archivo no válido');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return redirect()->back()->with('error', 'Solo JPG, PNG o PDF');
        }
        if ($file->getSize() > 10 * 1024 * 1024) {
            return redirect()->back()->with('error', 'El archivo excede 10MB');
        }

        $prev = $service->currentMinute($pollingStationId, $electionId, $dignityId);
        if ($prev && $prev->status === 'verificada' && !is_supervisor_or_admin()) {
            return redirect()->back()->with('error', 'El acta está verificada y no puede reemplazarse.');
        }
        if ($prev && is_operator() && $prev->status !== 'con_novedad') {
            return redirect()->back()->with('error', 'El acta ya fue registrada. Solo puede reemplazarla cuando tenga una novedad.');
        }
        if ($prev && $reason === '') {
            return redirect()->back()->with('error', 'Indique el motivo del reemplazo del acta.');
        }

        $model = model('App\Models\UploadedMinuteModel');
        $userId = (int)session()->get('user_id');
        $version = $prev ? ((int)$prev->version + 1) : 1;
        $consistency = $service->consistency($pollingStationId, $electionId, $dignityId);

        $this->db->transBegin();
        try {
            if ($prev) {
                $model->where('id', $prev->id)->set(['is_current' => 0])->update();
            }

            $newName = $file->getRandomName();
            $folder = FCPATH . 'uploads/minutes/' . $electionId . '/' . $pollingStationId;
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            $file->move($folder, $newName);

            $model->insert([
                'polling_station_id' => $pollingStationId,
                'election_id' => $electionId,
                'dignity_id' => $dignityId,
                'filename' => $newName,
                'original_name' => $file->getClientName(),
                'file_path' => $folder . '/' . $newName,
                'file_type' => strtolower($file->getExtension()),
                'file_size' => $file->getSize(),
                'version' => $version,
                'is_current' => 1,
                'status' => $consistency['status'] === 'error' ? 'con_novedad' : 'registrada',
                'notes' => $notes ?: null,
                'consistency_status' => $consistency['status'],
                'consistency_detail' => $consistency['detail'],
                'uploaded_by' => $userId,
            ]);

            if ($prev && $prev->status === 'verificada') {
                $this->db->table('election_results')
                    ->where('polling_station_id', $pollingStationId)
                    ->where('election_id', $electionId)
                    ->where('dignity_id', $dignityId)
                    ->update(['is_verified' => 0, 'verified_by' => null, 'verified_at' => null]);
            }

            $service->updateStationStatus($pollingStationId, $electionId);
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al subir acta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo subir el acta: ' . $e->getMessage());
        }

        $this->logAudit('upload_minute', 'uploaded_minutes', null, null, [
            'polling_station_id' => $pollingStationId,
            'election_id' => $electionId,
            'dignity_id' => $dignityId,
            'version' => $version,
            'reason' => $reason ?: null,
        ]);

        return redirect()->to(base_url('minutes'))->with('success', 'Acta subida correctamente (versión ' . $version . ').');
    }

    public function update()
    {
        if ($redirect = $this->requireAnyPermission(['results.update', 'results.verify'])) {
            return $redirect;
        }

        $minuteId = (int)$this->request->getPost('minute_id');
        $minute = model('App\Models\UploadedMinuteModel')->find($minuteId);
        if (!$minute) {
            return redirect()->to(base_url('minutes'))->with('error', 'Acta no encontrada');
        }

        $psId = (int)$minute->polling_station_id;
        $electionId = (int)$minute->election_id;
        $dignityId = (int)$minute->dignity_id;
        $userId = (int)session()->get('user_id');
        $reason = trim((string)$this->request->getPost('reason'));
        $notes = trim((string)$this->request->getPost('notes'));

        if ($redirect = $this->requireAssignment($psId, $electionId)) {
            return $redirect;
        }

        $canVerify = has_permission('results.verify');
        $isVerified = $canVerify && $this->request->getPost('is_verified') ? 1 : 0;

        if ($this->request->getPost('is_verified') && !$canVerify) {
            return redirect()->back()->with('error', 'No tiene permisos para verificar actas.');
        }
        if (is_operator() && $minute->status !== 'con_novedad') {
            return redirect()->back()->with('error', 'Solo puede corregir actas con novedad.');
        }
        if ($reason === '' && (is_operator() || $minute->status === 'verificada' || $minute->consistency_status === 'error')) {
            return redirect()->back()->with('error', 'Indique el motivo de la corrección o verificación.');
        }

        $service = new ActaService();
        $old = $this->snapshotResults($this->resultRowsForStation($psId, $electionId, $dignityId));

        $this->db->transBegin();
        try {
            $versionsCount = $this->db->table('result_versions')
                ->where('polling_station_id', $psId)
                ->where('election_id', $electionId)
                ->where('dignity_id', $dignityId)
                ->countAllResults();
            if ($versionsCount === 0) {
                $service->snapshot($psId, $electionId, $dignityId, $userId, 'Estado previo a la corrección');
            }

            $votes = $this->request->getPost('votes') ?: [];
            foreach ($votes as $resultId => $val) {
                $this->db->table('election_results')
                    ->where('id', (int)$resultId)
                    ->where('polling_station_id', $psId)
                    ->where('election_id', $electionId)
                    ->where('dignity_id', $dignityId)
                    ->update(['votes' => max(0, (int)$val)]);
            }

            $updateData = [
                'blank_votes' => max(0, (int)$this->request->getPost('blank_votes')),
                'null_votes' => max(0, (int)$this->request->getPost('null_votes')),
                'total_voters' => max(0, (int)$this->request->getPost('total_voters')),
                'registered_voters' => max(0, (int)$this->request->getPost('registered_voters')),
                'is_verified' => $isVerified,
                'verified_by' => $isVerified ? $userId : null,
                'verified_at' => $isVerified ? date('Y-m-d H:i:s') : null,
            ];
            $this->db->table('election_results')
                ->where('polling_station_id', $psId)
                ->where('election_id', $electionId)
                ->where('dignity_id', $dignityId)
                ->update($updateData);

            $consistency = $service->consistency($psId, $electionId, $dignityId);
            $newStatus = $isVerified
                ? 'verificada'
                : ($consistency['status'] === 'error' ? 'con_novedad' : 'registrada');

            $minuteUpdate = [
                'status' => $newStatus,
                'consistency_status' => $consistency['status'],
                'consistency_detail' => $consistency['detail'],
                'verified_by' => $isVerified ? $userId : null,
                'verified_at' => $isVerified ? date('Y-m-d H:i:s') : null,
            ];
            if ($notes !== '') {
                $minuteUpdate['notes'] = $notes;
            }
            model('App\Models\UploadedMinuteModel')->update($minuteId, $minuteUpdate);

            $service->updateStationStatus($psId, $electionId);
            $service->snapshot($psId, $electionId, $dignityId, $userId, $reason ?: ($isVerified ? 'Verificación' : 'Corrección'));

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al actualizar acta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }

        $new = $this->snapshotResults($this->resultRowsForStation($psId, $electionId, $dignityId));
        $this->logAudit('update_minute', 'election_results', $psId, $old, $new);

        $msg = 'Acta actualizada correctamente.';
        if ($consistency['status'] === 'error') {
            $msg .= ' Inconsistencia: ' . $consistency['detail'];
        }

        return redirect()->to(base_url('minutes'))->with($consistency['status'] === 'error' ? 'warning' : 'success', $msg);
    }

    private function resultRowsForStation(int $psId, int $electionId, int $dignityId): array
    {
        return $this->db->query(
            'SELECT er.id, er.votes, er.blank_votes, er.null_votes, er.total_voters, er.registered_voters, er.is_verified,
                    c.first_name, c.last_name, d.name AS dignity_name
             FROM election_results er
             JOIN candidates c ON c.id = er.candidate_id
             JOIN dignities d ON d.id = er.dignity_id
             WHERE er.polling_station_id = ? AND er.election_id = ? AND er.dignity_id = ?
             ORDER BY c.last_name, c.first_name',
            [$psId, $electionId, $dignityId]
        )->getResult();
    }

    private function snapshotResults(array $rows): array
    {
        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'result_id' => (int)$r->id,
                'dignidad' => $r->dignity_name,
                'candidato' => trim($r->first_name . ' ' . $r->last_name),
                'votos' => (int)$r->votes,
            ];
        }
        $first = $rows[0] ?? null;
        return [
            'resultados' => $results,
            'totales' => [
                'blancos' => $first ? (int)$first->blank_votes : 0,
                'nulos' => $first ? (int)$first->null_votes : 0,
                'sufragantes' => $first ? (int)$first->total_voters : 0,
                'electores' => $first ? (int)$first->registered_voters : 0,
            ],
            'verificada' => $first ? (int)$first->is_verified : 0,
        ];
    }

    public function preview($id)
    {
        if ($redirect = $this->requirePermission('minutes.read')) {
            return $redirect;
        }
        $minute = model('App\Models\UploadedMinuteModel')->find($id);
        if (!$minute) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if ($redirect = $this->requireAssignment((int)$minute->polling_station_id, (int)$minute->election_id)) {
            return $redirect;
        }

        $path = $minute->file_path;
        if (!file_exists($path)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $mime = mime_content_type($path);
        return $this->response->setContentType($mime)->setBody(file_get_contents($path));
    }

    public function download($id)
    {
        if ($redirect = $this->requirePermission('minutes.read')) {
            return $redirect;
        }
        $minute = model('App\Models\UploadedMinuteModel')->find($id);
        if (!$minute) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if ($redirect = $this->requireAssignment((int)$minute->polling_station_id, (int)$minute->election_id)) {
            return $redirect;
        }

        $path = $minute->file_path;
        if (!file_exists($path)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        return $this->response->download($path, null)->setFileName($minute->original_name);
    }

    public function delete($id)
    {
        if ($redirect = $this->requirePermission('minutes.delete')) {
            return $redirect;
        }

        $model = model('App\Models\UploadedMinuteModel');
        $minute = $model->find($id);
        if ($minute && file_exists($minute->file_path)) {
            unlink($minute->file_path);
        }
        $model->delete($id);
        $this->logAudit('delete_minute', 'uploaded_minutes', $id);
        return redirect()->to(base_url('minutes'))->with('success', 'Acta eliminada');
    }
}
