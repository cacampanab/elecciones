<?php

namespace App\Controllers\Web;

class AssignmentController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $electionId = (int)($this->request->getGet('election_id') ?? 0);

        $assignmentModel = model('App\Models\AssignmentModel');
        $assignmentModel
            ->select('assignments.*, users.username, users.first_name, users.last_name, polling_stations.numero_junta, polling_stations.genero, polling_stations.name_province, polling_stations.name_canton, polling_stations.name_parish, polling_stations.name_precinct, polling_stations.status, elections.name as election_name')
            ->join('users', 'users.id = assignments.user_id')
            ->join('polling_stations', 'polling_stations.id = assignments.polling_station_id')
            ->join('elections', 'elections.id = assignments.election_id');
        if ($electionId) {
            $assignmentModel->where('assignments.election_id', $electionId);
        }
        $assignments = $assignmentModel->findAll();

        $operators = $this->userModel->getByRole('Operador Electoral');
        $provinces = model('App\Models\ProvinceModel')->where('is_active', 1)->findAll();
        $elections = model('App\Models\ElectionModel')->getActive();

        return $this->render('assignments/index', compact('assignments', 'operators', 'provinces', 'elections', 'electionId'), 'Asignaciones');
    }

    public function save()
    {
        $psId = $this->request->getPost('polling_station_id');
        $data = [
            'user_id' => $this->request->getPost('user_id'),
            'polling_station_id' => $psId,
            'election_id' => $this->request->getPost('election_id'),
            'assignment_type' => $this->request->getPost('assignment_type') ?? 'operador',
            'created_by' => session()->get('user_id'),
        ];
        $id = model('App\Models\AssignmentModel')->insert($data);
        model('App\Models\PollingStationModel')->update($psId, ['status' => 'asignada']);
        $this->logAudit('create_assignment', 'assignments', $id);
        return redirect()->to(base_url('assignments'))->with('success', 'Asignación creada');
    }

    public function saveBatch()
    {
        $userId = $this->request->getPost('user_id');
        $electionId = $this->request->getPost('election_id');
        $assignmentType = $this->request->getPost('assignment_type') ?? 'operador';
        $stationIds = $this->request->getPost('station_ids') ?: [];

        if (!$userId || !$electionId || empty($stationIds)) {
            return redirect()->back()->with('error', 'Debe seleccionar un operador, una elección y al menos una JRV');
        }

        $model = model('App\Models\AssignmentModel');
        $psModel = model('App\Models\PollingStationModel');
        $created = 0;
        $skipped = 0;

        foreach ($stationIds as $psId) {
            $exists = $model->where('user_id', $userId)
                ->where('polling_station_id', $psId)
                ->where('election_id', $electionId)
                ->first();
            if ($exists) { $skipped++; continue; }

            $model->insert([
                'user_id' => $userId,
                'polling_station_id' => $psId,
                'election_id' => $electionId,
                'assignment_type' => $assignmentType,
                'created_by' => session()->get('user_id'),
            ]);
            $psModel->update($psId, ['status' => 'asignada']);
            $created++;
        }

        $this->logAudit('batch_assignments', 'assignments');

        $msg = "$created JRV asignadas correctamente";
        if ($skipped > 0) $msg .= " ($skipped ya estaban asignadas)";
        return redirect()->to(base_url('assignments'))->with('success', $msg);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        $newUserId = $this->request->getPost('user_id');
        $oldUserId = $this->request->getPost('old_user_id');
        $assignmentType = $this->request->getPost('assignment_type') ?? 'operador';

        $model = model('App\Models\AssignmentModel');
        $assignment = $model->find($id);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Asignación no encontrada');
        }

        $model->update($id, [
            'user_id' => $newUserId,
            'assignment_type' => $assignmentType,
        ]);

        $this->logAudit('update_assignment', 'assignments', $id);

        $newUser = $this->userModel->find($newUserId);
        return redirect()->to(base_url('assignments'))->with('success',
            'Operador cambiado: ' . esc($newUser->first_name . ' ' . $newUser->last_name) . ' asignado a JRV');
    }

    public function delete($id)
    {
        $assignmentModel = model('App\Models\AssignmentModel');
        $assignment = $assignmentModel->find($id);
        $psId = $assignment->polling_station_id ?? null;

        $assignmentModel->delete($id);

        // Check if other assignments exist for this JRV
        if ($psId) {
            $others = $assignmentModel->where('polling_station_id', $psId)->where('is_active', 1)->countAllResults();
            if ($others == 0) {
                model('App\Models\PollingStationModel')->update($psId, ['status' => 'pendiente']);
            }
        }

        $this->logAudit('delete_assignment', 'assignments', $id);
        return redirect()->to(base_url('assignments'))->with('success', 'Asignación eliminada');
    }
}
