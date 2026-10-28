<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class CantonController extends BaseController
{
    public function index()
    {
        $provinceId = $this->request->getVar('province_id');
        if ($provinceId) {
            $cantons = model('App\Models\CantonModel')->getByProvince($provinceId);
        } else {
            $cantons = model('App\Models\CantonModel')
                ->select('cantons.*, provinces.name as province_name')
                ->join('provinces', 'provinces.id = cantons.province_id')
                ->findAll();
        }
        return jsonResponse($cantons);
    }

    public function show($id = null)
    {
        $canton = model('App\Models\CantonModel')
            ->select('cantons.*, provinces.name as province_name')
            ->join('provinces', 'provinces.id = cantons.province_id')
            ->where('cantons.id', $id)
            ->first();
        if (!$canton) return errorResponse('Cantón no encontrado', 404);
        return jsonResponse($canton);
    }

    public function create()
    {
        $rules = ['province_id' => 'required|numeric', 'code' => 'required', 'name' => 'required'];
        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'province_id' => $this->request->getVar('province_id'),
            'code' => $this->request->getVar('code'),
            'name' => $this->request->getVar('name'),
        ];

        $id = model('App\Models\CantonModel')->insert($data);
        $this->logAudit('create_canton', 'cantons', $id, null, $data);

        return jsonResponse(['id' => $id], 'Cantón creado', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\CantonModel');
        if (!$model->find($id)) return errorResponse('Cantón no encontrado', 404);

        $data = [];
        if ($this->request->getVar('province_id')) $data['province_id'] = $this->request->getVar('province_id');
        if ($this->request->getVar('code')) $data['code'] = $this->request->getVar('code');
        if ($this->request->getVar('name')) $data['name'] = $this->request->getVar('name');

        $model->update($id, $data);
        $this->logAudit('update_canton', 'cantons', $id);

        return jsonResponse(null, 'Cantón actualizado');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\CantonModel');
        if (!$model->find($id)) return errorResponse('Cantón no encontrado', 404);

        $model->delete($id);
        $this->logAudit('delete_canton', 'cantons', $id);

        return jsonResponse(null, 'Cantón eliminado');
    }
}
