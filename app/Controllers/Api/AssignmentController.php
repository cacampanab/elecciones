<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class AssignmentController extends BaseController
{
    public function index()
    {
        $electionId = $this->request->getVar('election_id');
        $assignmentModel = model('App\Models\AssignmentModel');

        $builder = $assignmentModel
            ->select('assignments.*, users.username, users.first_name, users.last_name, users.cedula, polling_stations.numero_junta, polling_stations.name_province, polling_stations.name_canton, polling_stations.name_parish, polling_stations.name_precinct')
            ->join('users', 'users.id = assignments.user_id')
            ->join('polling_stations', 'polling_stations.id = assignments.polling_station_id');

        if ($electionId) {
            $builder->where('assignments.election_id', $electionId);
        }

        $assignments = $builder->findAll();
        return jsonResponse($assignments);
    }

    public function show($id = null)
    {
        $assignment = model('App\Models\AssignmentModel')
            ->select('assignments.*, users.username, users.first_name, users.last_name, polling_stations.*')
            ->join('users', 'users.id = assignments.user_id')
            ->join('polling_stations', 'polling_stations.id = assignments.polling_station_id')
            ->where('assignments.id', $id)
            ->first();
        if (!$assignment) return errorResponse('Asignación no encontrada', 404);
        return jsonResponse($assignment);
    }

    public function create()
    {
        $rules = [
            'user_id' => 'required|numeric',
            'polling_station_id' => 'required|numeric',
            'election_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'user_id' => $this->request->getVar('user_id'),
            'polling_station_id' => $this->request->getVar('polling_station_id'),
            'election_id' => $this->request->getVar('election_id'),
            'assignment_type' => $this->request->getVar('assignment_type') ?? 'operador',
            'created_by' => $this->getUserId(),
        ];

        $id = model('App\Models\AssignmentModel')->insert($data);
        $this->logAudit('create_assignment', 'assignments', $id, null, $data);

        return jsonResponse(['id' => $id], 'Asignación creada', 201);
    }

    public function createBatch()
    {
        $assignments = $this->request->getVar('assignments');
        if (empty($assignments) || !is_array($assignments)) {
            return errorResponse('Lista de asignaciones requerida', 400);
        }

        $model = model('App\Models\AssignmentModel');
        $created = 0;

        foreach ($assignments as $assign) {
            if (!isset($assign['user_id'], $assign['polling_station_id'], $assign['election_id'])) continue;

            $data = [
                'user_id' => $assign['user_id'],
                'polling_station_id' => $assign['polling_station_id'],
                'election_id' => $assign['election_id'],
                'assignment_type' => $assign['assignment_type'] ?? 'operador',
                'created_by' => $this->getUserId(),
            ];

            $model->insert($data);
            $created++;
        }

        $this->logAudit('batch_assignments', 'assignments', null, null, ['created' => $created]);
        return jsonResponse(['created' => $created], "$created asignaciones creadas", 201);
    }

    public function delete($id = null)
    {
        $model = model('App\Models\AssignmentModel');
        if (!$model->find($id)) return errorResponse('Asignación no encontrada', 404);

        $model->delete($id);
        $this->logAudit('delete_assignment', 'assignments', $id);

        return jsonResponse(null, 'Asignación eliminada');
    }

    public function myAssignments()
    {
        $userId = $this->getUserId();
        $electionId = $this->request->getVar('election_id');

        $assignments = model('App\Models\AssignmentModel')->getUserAssignments($userId, $electionId);
        return jsonResponse($assignments);
    }
}
