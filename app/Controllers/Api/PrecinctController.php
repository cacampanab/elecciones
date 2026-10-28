<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class PrecinctController extends BaseController
{
    public function index()
    {
        $parishId = $this->request->getVar('parish_id');
        if ($parishId) {
            $precincts = model('App\Models\PrecinctModel')->getByParish($parishId);
        } else {
            $precincts = model('App\Models\PrecinctModel')
                ->select('precincts.*, parishes.name as parish_name, cantons.name as canton_name, provinces.name as province_name')
                ->join('parishes', 'parishes.id = precincts.parish_id')
                ->join('cantons', 'cantons.id = parishes.canton_id')
                ->join('provinces', 'provinces.id = cantons.province_id')
                ->findAll();
        }
        return jsonResponse($precincts);
    }

    public function show($id = null)
    {
        $precinct = model('App\Models\PrecinctModel')->find($id);
        if (!$precinct) return errorResponse('Recinto no encontrado', 404);
        return jsonResponse($precinct);
    }

    public function create()
    {
        $rules = ['parish_id' => 'required|numeric', 'code' => 'required', 'name' => 'required'];
        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'parish_id' => $this->request->getVar('parish_id'),
            'code' => $this->request->getVar('code'),
            'name' => $this->request->getVar('name'),
            'address' => $this->request->getVar('address'),
            'latitude' => $this->request->getVar('latitude'),
            'longitude' => $this->request->getVar('longitude'),
        ];

        $id = model('App\Models\PrecinctModel')->insert($data);
        $this->logAudit('create_precinct', 'precincts', $id, null, $data);

        return jsonResponse(['id' => $id], 'Recinto creado', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\PrecinctModel');
        if (!$model->find($id)) return errorResponse('Recinto no encontrado', 404);

        $data = [];
        foreach (['parish_id', 'code', 'name', 'address', 'latitude', 'longitude'] as $field) {
            if ($this->request->getVar($field) !== null) $data[$field] = $this->request->getVar($field);
        }

        $model->update($id, $data);
        $this->logAudit('update_precinct', 'precincts', $id);

        return jsonResponse(null, 'Recinto actualizado');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\PrecinctModel');
        if (!$model->find($id)) return errorResponse('Recinto no encontrado', 404);

        $model->delete($id);
        $this->logAudit('delete_precinct', 'precincts', $id);

        return jsonResponse(null, 'Recinto eliminado');
    }
}
