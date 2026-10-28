<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class CandidateListController extends BaseController
{
    public function index()
    {
        $lists = model('App\Models\CandidateListModel')
            ->select('candidate_lists.*, elections.name as election_name, political_parties.name as party_name, political_parties.acronym as party_acronym, dignities.name as dignity_name')
            ->join('elections', 'elections.id = candidate_lists.election_id')
            ->join('political_parties', 'political_parties.id = candidate_lists.political_party_id')
            ->join('dignities', 'dignities.id = candidate_lists.dignity_id')
            ->findAll();
        return jsonResponse($lists);
    }

    public function show($id = null)
    {
        $list = model('App\Models\CandidateListModel')->find($id);
        if (!$list) return errorResponse('Lista no encontrada', 404);
        return jsonResponse($list);
    }

    public function create()
    {
        $rules = [
            'election_id' => 'required|numeric',
            'political_party_id' => 'required|numeric',
            'dignity_id' => 'required|numeric',
            'name' => 'required',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'election_id' => $this->request->getVar('election_id'),
            'political_party_id' => $this->request->getVar('political_party_id'),
            'dignity_id' => $this->request->getVar('dignity_id'),
            'name' => $this->request->getVar('name'),
        ];

        $id = model('App\Models\CandidateListModel')->insert($data);
        $this->logAudit('create_list', 'candidate_lists', $id, null, $data);

        return jsonResponse(['id' => $id], 'Lista creada', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\CandidateListModel');
        if (!$model->find($id)) return errorResponse('Lista no encontrada', 404);

        $data = [];
        if ($this->request->getVar('election_id')) $data['election_id'] = $this->request->getVar('election_id');
        if ($this->request->getVar('political_party_id')) $data['political_party_id'] = $this->request->getVar('political_party_id');
        if ($this->request->getVar('dignity_id')) $data['dignity_id'] = $this->request->getVar('dignity_id');
        if ($this->request->getVar('name')) $data['name'] = $this->request->getVar('name');

        $model->update($id, $data);
        $this->logAudit('update_list', 'candidate_lists', $id);

        return jsonResponse(null, 'Lista actualizada');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\CandidateListModel');
        if (!$model->find($id)) return errorResponse('Lista no encontrada', 404);

        $model->delete($id);
        $this->logAudit('delete_list', 'candidate_lists', $id);

        return jsonResponse(null, 'Lista eliminada');
    }

    public function byElection($electionId = null)
    {
        $lists = model('App\Models\CandidateListModel')
            ->select('candidate_lists.*, political_parties.name as party_name, political_parties.acronym as party_acronym, political_parties.list_number, dignities.name as dignity_name')
            ->join('political_parties', 'political_parties.id = candidate_lists.political_party_id')
            ->join('dignities', 'dignities.id = candidate_lists.dignity_id')
            ->where('candidate_lists.election_id', $electionId)
            ->findAll();
        return jsonResponse($lists);
    }
}
