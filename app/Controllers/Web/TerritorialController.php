<?php

namespace App\Controllers\Web;

class TerritorialController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        $provinces = model('App\Models\ProvinceModel')->where('is_active', 1)->findAll();
        $cantons = model('App\Models\CantonModel')
            ->select('cantons.*, provinces.name as province_name')
            ->join('provinces', 'provinces.id = cantons.province_id')
            ->findAll();
        return $this->render('territorial/index', compact('provinces', 'cantons'), 'División Territorial');
    }

    public function saveProvince()
    {
        $data = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
        ];
        $id = model('App\Models\ProvinceModel')->insert($data);
        $this->logAudit('create_province', 'provinces', $id);
        return redirect()->to(base_url('territorial'))->with('success', 'Provincia creada');
    }

    public function deleteProvince($id)
    {
        model('App\Models\ProvinceModel')->delete($id);
        $this->logAudit('delete_province', 'provinces', $id);
        return redirect()->to(base_url('territorial'))->with('success', 'Provincia eliminada');
    }

    public function saveCanton()
    {
        $data = [
            'province_id' => $this->request->getPost('province_id'),
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
        ];
        $id = model('App\Models\CantonModel')->insert($data);
        $this->logAudit('create_canton', 'cantons', $id);
        return redirect()->to(base_url('territorial'))->with('success', 'Cantón creado');
    }

    public function deleteCanton($id)
    {
        model('App\Models\CantonModel')->delete($id);
        $this->logAudit('delete_canton', 'cantons', $id);
        return redirect()->to(base_url('territorial'))->with('success', 'Cantón eliminado');
    }
}
