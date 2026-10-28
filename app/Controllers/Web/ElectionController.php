<?php

namespace App\Controllers\Web;

class ElectionController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $elections = model('App\Models\ElectionModel')->getActive();
        return $this->render('elections/index', compact('elections'), 'Elecciones');
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $electionModel = model('App\Models\ElectionModel');
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'election_date' => $this->request->getPost('election_date'),
            'election_type' => $this->request->getPost('election_type'),
            'status' => $this->request->getPost('status') ?? 'pendiente',
        ];

        if ($id) {
            $electionModel->update($id, $data);
            $this->logAudit('update_election', 'elections', $id);
        } else {
            $data['created_by'] = session()->get('user_id');
            $id = $electionModel->insert($data);
            $this->logAudit('create_election', 'elections', $id);
        }

        return redirect()->to(base_url('elections'))->with('success', 'Elección guardada');
    }

    public function delete($id)
    {
        model('App\Models\ElectionModel')->delete($id);
        $this->logAudit('delete_election', 'elections', $id);
        return redirect()->to(base_url('elections'))->with('success', 'Elección eliminada');
    }
}
