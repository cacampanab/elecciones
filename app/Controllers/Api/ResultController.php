<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class ResultController extends BaseController
{
    public function index()
    {
        $electionId = $this->request->getVar('election_id');
        $dignityId = $this->request->getVar('dignity_id');

        $model = model('App\Models\ElectionResultModel');
        $builder = $model
            ->select('election_results.*, candidates.first_name, candidates.last_name, political_parties.name as party_name, political_parties.acronym as party_acronym, polling_stations.numero_junta, polling_stations.name_province, polling_stations.name_canton, polling_stations.name_parish, polling_stations.name_precinct')
            ->join('candidates', 'candidates.id = election_results.candidate_id')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->join('polling_stations', 'polling_stations.id = election_results.polling_station_id');

        if ($electionId) $builder->where('election_results.election_id', $electionId);
        if ($dignityId) $builder->where('election_results.dignity_id', $dignityId);

        return jsonResponse($builder->findAll());
    }

    public function show($id = null)
    {
        $result = model('App\Models\ElectionResultModel')->find($id);
        if (!$result) return errorResponse('Resultado no encontrado', 404);
        return jsonResponse($result);
    }

    public function create()
    {
        $rules = [
            'polling_station_id' => 'required|numeric',
            'candidate_id' => 'required|numeric',
            'election_id' => 'required|numeric',
            'dignity_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $pollingStation = model('App\Models\PollingStationModel')->find($this->request->getVar('polling_station_id'));

        $votes = (int)($this->request->getVar('votes') ?? 0);
        $blankVotes = (int)($this->request->getVar('blank_votes') ?? 0);
        $nullVotes = (int)($this->request->getVar('null_votes') ?? 0);
        $totalVoters = (int)($this->request->getVar('total_voters') ?? 0);
        $registeredVoters = (int)($this->request->getVar('registered_voters') ?? ($pollingStation->numero_electores ?? 0));

        if ($totalVoters > $registeredVoters) {
            return errorResponse('El total de sufragantes no puede exceder los electores registrados', 400);
        }

        if (($votes + $blankVotes + $nullVotes) > $totalVoters) {
            return errorResponse('La suma de votos no puede exceder el total de sufragantes', 400);
        }

        $existing = model('App\Models\ElectionResultModel')
            ->where('polling_station_id', $this->request->getVar('polling_station_id'))
            ->where('candidate_id', $this->request->getVar('candidate_id'))
            ->first();

        if ($existing) {
            return errorResponse('Ya existe un resultado para este candidato en esta JRV', 400);
        }

        $data = [
            'polling_station_id' => $this->request->getVar('polling_station_id'),
            'candidate_id' => $this->request->getVar('candidate_id'),
            'election_id' => $this->request->getVar('election_id'),
            'dignity_id' => $this->request->getVar('dignity_id'),
            'votes' => $votes,
            'blank_votes' => $blankVotes,
            'null_votes' => $nullVotes,
            'total_voters' => $totalVoters,
            'registered_voters' => $registeredVoters,
            'registered_by' => $this->getUserId(),
            'latitude' => $this->request->getVar('latitude'),
            'longitude' => $this->request->getVar('longitude'),
        ];

        $id = model('App\Models\ElectionResultModel')->insert($data);

        model('App\Models\PollingStationModel')->update($this->request->getVar('polling_station_id'), [
            'status' => 'reportada'
        ]);

        $this->logAudit('create_result', 'election_results', $id, null, $data);
        return jsonResponse(['id' => $id], 'Resultado registrado', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\ElectionResultModel');
        if (!$model->find($id)) return errorResponse('Resultado no encontrado', 404);

        $data = [];
        foreach (['votes', 'blank_votes', 'null_votes', 'total_voters', 'registered_voters'] as $field) {
            if ($this->request->getVar($field) !== null) $data[$field] = (int)$this->request->getVar($field);
        }

        $old = $model->find($id);
        $model->update($id, $data);
        $this->logAudit('update_result', 'election_results', $id, (array)$old, $data);

        return jsonResponse(null, 'Resultado actualizado');
    }

    public function verify($id = null)
    {
        $model = model('App\Models\ElectionResultModel');
        if (!$model->find($id)) return errorResponse('Resultado no encontrado', 404);

        $model->update($id, [
            'is_verified' => 1,
            'verified_by' => $this->getUserId(),
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logAudit('verify_result', 'election_results', $id);
        return jsonResponse(null, 'Resultado verificado');
    }

    public function byPollingStation($pollingStationId = null)
    {
        $electionId = $this->request->getVar('election_id');
        $results = model('App\Models\ElectionResultModel')->getResultsByPollingStation($electionId, $pollingStationId);
        return jsonResponse($results);
    }

    public function byDignity($electionId = null, $dignityId = null)
    {
        $results = model('App\Models\ElectionResultModel')->getResultsByDignity($electionId, $dignityId);
        return jsonResponse($results);
    }

    public function byProvince($electionId = null, $dignityId = null, $provinceCode = null)
    {
        $results = model('App\Models\ElectionResultModel')->getResultsByProvince($electionId, $dignityId, $provinceCode);
        return jsonResponse($results);
    }

    public function delete($id = null)
    {
        $model = model('App\Models\ElectionResultModel');
        if (!$model->find($id)) return errorResponse('Resultado no encontrado', 404);

        $model->delete($id);
        $this->logAudit('delete_result', 'election_results', $id);

        return jsonResponse(null, 'Resultado eliminado');
    }
}
