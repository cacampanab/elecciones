<?php

namespace App\Controllers\Web;

class DignityController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $dignities = model('App\Models\DignityModel')->where('is_active', 1)->findAll();
        return $this->render('dignities/index', compact('dignities'), 'Dignidades');
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $model = model('App\Models\DignityModel');
        $data = [
            'name' => $this->request->getPost('name'),
            'slug' => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
            'level' => $this->request->getPost('level') ?? 'nacional',
        ];
        if ($id) { $model->update($id, $data); $this->logAudit('update_dignity', 'dignities', $id); }
        else { $id = $model->insert($data); $this->logAudit('create_dignity', 'dignities', $id); }
        return redirect()->to(base_url('dignities'))->with('success', 'Dignidad guardada');
    }

    public function delete($id)
    {
        model('App\Models\DignityModel')->delete($id);
        $this->logAudit('delete_dignity', 'dignities', $id);
        return redirect()->to(base_url('dignities'))->with('success', 'Dignidad eliminada');
    }
}
