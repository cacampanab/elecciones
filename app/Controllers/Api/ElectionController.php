<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class ElectionController extends BaseController
{
    public function index()
    {
        $elections = model('App\Models\ElectionModel')->getActive();
        return jsonResponse($elections);
    }

    public function show($id = null)
    {
        $election = model('App\Models\ElectionModel')->find($id);
        if (!$election) return errorResponse('Elección no encontrada', 404);
        return jsonResponse($election);
    }

    public function create()
    {
        $rules = [
            'name' => 'required',
            'election_date' => 'required|valid_date',
            'election_type' => 'required|in_list[nacional,provincial,cantonal,parroquial]',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'description' => $this->request->getVar('description'),
            'election_date' => $this->request->getVar('election_date'),
            'election_type' => $this->request->getVar('election_type'),
            'status' => $this->request->getVar('status') ?? 'pendiente',
            'created_by' => $this->getUserId(),
        ];

        $id = model('App\Models\ElectionModel')->insert($data);
        $this->logAudit('create_election', 'elections', $id, null, $data);

        return jsonResponse(['id' => $id], 'Elección creada', 201);
    }

    public function update($id = null)
    {
        $electionModel = model('App\Models\ElectionModel');
        if (!$electionModel->find($id)) return errorResponse('Elección no encontrada', 404);

        $data = [];
        if ($this->request->getVar('name')) $data['name'] = $this->request->getVar('name');
        if ($this->request->getVar('description') !== null) $data['description'] = $this->request->getVar('description');
        if ($this->request->getVar('election_date')) $data['election_date'] = $this->request->getVar('election_date');
        if ($this->request->getVar('election_type')) $data['election_type'] = $this->request->getVar('election_type');
        if ($this->request->getVar('status')) $data['status'] = $this->request->getVar('status');

        $old = $electionModel->find($id);
        $electionModel->update($id, $data);
        $this->logAudit('update_election', 'elections', $id, (array)$old, $data);

        return jsonResponse(null, 'Elección actualizada');
    }

    public function delete($id = null)
    {
        $electionModel = model('App\Models\ElectionModel');
        if (!$electionModel->find($id)) return errorResponse('Elección no encontrada', 404);

        $old = $electionModel->find($id);
        $electionModel->delete($id);
        $this->logAudit('delete_election', 'elections', $id, (array)$old);

        return jsonResponse(null, 'Elección eliminada');
    }

    public function current()
    {
        $election = model('App\Models\ElectionModel')->getCurrent();
        if (!$election) return errorResponse('No hay elección en curso', 404);
        return jsonResponse($election);
    }
}
