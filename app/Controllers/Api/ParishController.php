<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class ParishController extends BaseController
{
    public function index()
    {
        $cantonId = $this->request->getVar('canton_id');
        if ($cantonId) {
            $parishes = model('App\Models\ParishModel')->getByCanton($cantonId);
        } else {
            $parishes = model('App\Models\ParishModel')
                ->select('parishes.*, cantons.name as canton_name, provinces.name as province_name')
                ->join('cantons', 'cantons.id = parishes.canton_id')
                ->join('provinces', 'provinces.id = cantons.province_id')
                ->findAll();
        }
        return jsonResponse($parishes);
    }

    public function show($id = null)
    {
        $parish = model('App\Models\ParishModel')->find($id);
        if (!$parish) return errorResponse('Parroquia no encontrada', 404);
        return jsonResponse($parish);
    }

    public function create()
    {
        $rules = ['canton_id' => 'required|numeric', 'code' => 'required', 'name' => 'required'];
        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'canton_id' => $this->request->getVar('canton_id'),
            'code' => $this->request->getVar('code'),
            'name' => $this->request->getVar('name'),
        ];

        $id = model('App\Models\ParishModel')->insert($data);
        $this->logAudit('create_parish', 'parishes', $id, null, $data);

        return jsonResponse(['id' => $id], 'Parroquia creada', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\ParishModel');
        if (!$model->find($id)) return errorResponse('Parroquia no encontrada', 404);

        $data = [];
        if ($this->request->getVar('canton_id')) $data['canton_id'] = $this->request->getVar('canton_id');
        if ($this->request->getVar('code')) $data['code'] = $this->request->getVar('code');
        if ($this->request->getVar('name')) $data['name'] = $this->request->getVar('name');

        $model->update($id, $data);
        $this->logAudit('update_parish', 'parishes', $id);

        return jsonResponse(null, 'Parroquia actualizada');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\ParishModel');
        if (!$model->find($id)) return errorResponse('Parroquia no encontrada', 404);

        $model->delete($id);
        $this->logAudit('delete_parish', 'parishes', $id);

        return jsonResponse(null, 'Parroquia eliminada');
    }
}
