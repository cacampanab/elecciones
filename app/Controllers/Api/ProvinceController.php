<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class ProvinceController extends BaseController
{
    public function index()
    {
        $provinces = model('App\Models\ProvinceModel')->where('is_active', 1)->findAll();
        return jsonResponse($provinces);
    }

    public function show($id = null)
    {
        $province = model('App\Models\ProvinceModel')->find($id);
        if (!$province) return errorResponse('Provincia no encontrada', 404);
        return jsonResponse($province);
    }

    public function create()
    {
        if (!$this->validate(['code' => 'required|is_unique[provinces.code]', 'name' => 'required'])) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'code' => $this->request->getVar('code'),
            'name' => $this->request->getVar('name'),
            'capital' => $this->request->getVar('capital'),
            'region' => $this->request->getVar('region'),
        ];

        $id = model('App\Models\ProvinceModel')->insert($data);
        $this->logAudit('create_province', 'provinces', $id, null, $data);

        return jsonResponse(['id' => $id], 'Provincia creada', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\ProvinceModel');
        if (!$model->find($id)) return errorResponse('Provincia no encontrada', 404);

        $data = [];
        if ($this->request->getVar('code')) $data['code'] = $this->request->getVar('code');
        if ($this->request->getVar('name')) $data['name'] = $this->request->getVar('name');
        if ($this->request->getVar('capital') !== null) $data['capital'] = $this->request->getVar('capital');
        if ($this->request->getVar('region') !== null) $data['region'] = $this->request->getVar('region');

        $model->update($id, $data);
        $this->logAudit('update_province', 'provinces', $id);

        return jsonResponse(null, 'Provincia actualizada');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\ProvinceModel');
        if (!$model->find($id)) return errorResponse('Provincia no encontrada', 404);

        $model->delete($id);
        $this->logAudit('delete_province', 'provinces', $id);

        return jsonResponse(null, 'Provincia eliminada');
    }

    public function withCantons()
    {
        $provinces = model('App\Models\ProvinceModel')
            ->where('is_active', 1)
            ->findAll();

        $cantonModel = model('App\Models\CantonModel');
        foreach ($provinces as $province) {
            $province->cantons = $cantonModel->getByProvince($province->id);
        }

        return jsonResponse($provinces);
    }
}
