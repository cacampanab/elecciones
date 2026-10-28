<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class CandidateController extends BaseController
{
    public function index()
    {
        $electionId = $this->request->getVar('election_id');
        $candidates = model('App\Models\CandidateModel')->getWithRelations($electionId);
        return jsonResponse($candidates);
    }

    public function show($id = null)
    {
        $candidate = model('App\Models\CandidateModel')
            ->select('candidates.*, dignities.name as dignity_name, political_parties.name as party_name, political_parties.acronym as party_acronym, political_parties.list_number, political_parties.color as party_color')
            ->join('dignities', 'dignities.id = candidates.dignity_id')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->where('candidates.id', $id)
            ->first();
        if (!$candidate) return errorResponse('Candidato no encontrado', 404);
        return jsonResponse($candidate);
    }

    public function create()
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'cedula' => 'permit_empty|is_unique[candidates.cedula]',
            'dignity_id' => 'required|numeric',
            'political_party_id' => 'required|numeric',
            'candidate_list_id' => 'permit_empty|numeric',
            'election_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'first_name' => $this->request->getVar('first_name'),
            'last_name' => $this->request->getVar('last_name'),
            'cedula' => $this->request->getVar('cedula') ?: null,
            'dignity_id' => $this->request->getVar('dignity_id'),
            'political_party_id' => $this->request->getVar('political_party_id'),
            'candidate_list_id' => $this->request->getVar('candidate_list_id') ?: null,
            'election_id' => $this->request->getVar('election_id'),
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid()) {
            $newName = $photo->getRandomName();
            $photo->move('uploads/candidates', $newName);
            $data['photo'] = 'uploads/candidates/' . $newName;
        }

        $id = model('App\Models\CandidateModel')->insert($data);
        $this->logAudit('create_candidate', 'candidates', $id, null, $data);

        return jsonResponse(['id' => $id], 'Candidato creado', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\CandidateModel');
        if (!$model->find($id)) return errorResponse('Candidato no encontrado', 404);

        $data = [];
        foreach (['first_name', 'last_name', 'cedula', 'dignity_id', 'political_party_id', 'candidate_list_id', 'election_id'] as $field) {
            if ($this->request->getVar($field) !== null) $data[$field] = $this->request->getVar($field);
        }

        $model->update($id, $data);
        $this->logAudit('update_candidate', 'candidates', $id);

        return jsonResponse(null, 'Candidato actualizado');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\CandidateModel');
        if (!$model->find($id)) return errorResponse('Candidato no encontrado', 404);

        $model->delete($id);
        $this->logAudit('delete_candidate', 'candidates', $id);

        return jsonResponse(null, 'Candidato eliminado');
    }

    public function byElection($electionId = null)
    {
        $candidates = model('App\Models\CandidateModel')->getWithRelations($electionId);
        return jsonResponse($candidates);
    }

    public function byDignity($electionId = null, $dignityId = null)
    {
        $candidates = model('App\Models\CandidateModel')
            ->select('candidates.*, political_parties.name as party_name, political_parties.acronym as party_acronym, political_parties.list_number, political_parties.color as party_color')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->where('candidates.election_id', $electionId)
            ->where('candidates.dignity_id', $dignityId)
            ->findAll();
        return jsonResponse($candidates);
    }
}
