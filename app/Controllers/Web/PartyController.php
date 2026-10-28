<?php

namespace App\Controllers\Web;

class PartyController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $parties = model('App\Models\PoliticalPartyModel')->where('is_active', 1)->findAll();
        return $this->render('parties/index', compact('parties'), 'Partidos');
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $model = model('App\Models\PoliticalPartyModel');
        $data = [
            'name' => $this->request->getPost('name'),
            'acronym' => $this->request->getPost('acronym'),
            'list_number' => $this->request->getPost('list_number'),
            'color' => $this->request->getPost('color'),
        ];
        if ($id) { $model->update($id, $data); $this->logAudit('update_party', 'political_parties', $id); }
        else { $id = $model->insert($data); $this->logAudit('create_party', 'political_parties', $id); }
        return redirect()->to(base_url('parties'))->with('success', 'Partido guardado');
    }

    public function delete($id)
    {
        model('App\Models\PoliticalPartyModel')->delete($id);
        $this->logAudit('delete_party', 'political_parties', $id);
        return redirect()->to(base_url('parties'))->with('success', 'Partido eliminado');
    }
}
