<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class DignityController extends BaseController
{
    public function index()
    {
        $dignities = model('App\Models\DignityModel')->where('is_active', 1)->findAll();
        return jsonResponse($dignities);
    }

    public function show($id = null)
    {
        $dignity = model('App\Models\DignityModel')->find($id);
        if (!$dignity) return errorResponse('Dignidad no encontrada', 404);
        return jsonResponse($dignity);
    }

    public function create()
    {
        if (!$this->validate(['name' => 'required', 'slug' => 'required|is_unique[dignities.slug]'])) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'slug' => $this->request->getVar('slug'),
            'description' => $this->request->getVar('description'),
            'level' => $this->request->getVar('level') ?? 'nacional',
        ];

        $id = model('App\Models\DignityModel')->insert($data);
        $this->logAudit('create_dignity', 'dignities', $id, null, $data);

        return jsonResponse(['id' => $id], 'Dignidad creada', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\DignityModel');
        if (!$model->find($id)) return errorResponse('Dignidad no encontrada', 404);

        $data = [];
        if ($this->request->getVar('name')) $data['name'] = $this->request->getVar('name');
        if ($this->request->getVar('slug')) $data['slug'] = $this->request->getVar('slug');
        if ($this->request->getVar('description') !== null) $data['description'] = $this->request->getVar('description');
        if ($this->request->getVar('level')) $data['level'] = $this->request->getVar('level');
        if ($this->request->getVar('is_active') !== null) $data['is_active'] = $this->request->getVar('is_active');

        $model->update($id, $data);
        $this->logAudit('update_dignity', 'dignities', $id);

        return jsonResponse(null, 'Dignidad actualizada');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\DignityModel');
        if (!$model->find($id)) return errorResponse('Dignidad no encontrada', 404);

        $model->delete($id);
        $this->logAudit('delete_dignity', 'dignities', $id);

        return jsonResponse(null, 'Dignidad eliminada');
    }
}
