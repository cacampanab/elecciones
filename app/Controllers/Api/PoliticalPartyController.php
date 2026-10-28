<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class PoliticalPartyController extends BaseController
{
    public function index()
    {
        $parties = model('App\Models\PoliticalPartyModel')->where('is_active', 1)->findAll();
        return jsonResponse($parties);
    }

    public function show($id = null)
    {
        $party = model('App\Models\PoliticalPartyModel')->find($id);
        if (!$party) return errorResponse('Partido no encontrado', 404);
        return jsonResponse($party);
    }

    public function create()
    {
        $rules = [
            'name' => 'required',
            'acronym' => 'required|is_unique[political_parties.acronym]',
            'list_number' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'acronym' => $this->request->getVar('acronym'),
            'list_number' => $this->request->getVar('list_number'),
            'color' => $this->request->getVar('color'),
        ];

        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid()) {
            $newName = $logo->getRandomName();
            $logo->move('uploads/parties', $newName);
            $data['logo'] = 'uploads/parties/' . $newName;
        }

        $id = model('App\Models\PoliticalPartyModel')->insert($data);
        $this->logAudit('create_party', 'political_parties', $id, null, $data);

        return jsonResponse(['id' => $id], 'Partido creado', 201);
    }

    public function update($id = null)
    {
        $model = model('App\Models\PoliticalPartyModel');
        if (!$model->find($id)) return errorResponse('Partido no encontrado', 404);

        $data = [];
        if ($this->request->getVar('name')) $data['name'] = $this->request->getVar('name');
        if ($this->request->getVar('acronym')) $data['acronym'] = $this->request->getVar('acronym');
        if ($this->request->getVar('list_number') !== null) $data['list_number'] = $this->request->getVar('list_number');
        if ($this->request->getVar('color')) $data['color'] = $this->request->getVar('color');

        $model->update($id, $data);
        $this->logAudit('update_party', 'political_parties', $id);

        return jsonResponse(null, 'Partido actualizado');
    }

    public function delete($id = null)
    {
        $model = model('App\Models\PoliticalPartyModel');
        if (!$model->find($id)) return errorResponse('Partido no encontrado', 404);

        $model->delete($id);
        $this->logAudit('delete_party', 'political_parties', $id);

        return jsonResponse(null, 'Partido eliminado');
    }
}
