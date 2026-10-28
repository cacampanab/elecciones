<?php

namespace App\Controllers\Web;

class RoleController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $roleModel = model('App\Models\RoleModel');
        $roles = $roleModel->findAll();
        $allPermissions = model('App\Models\PermissionModel')->findAll();

        $db = \Config\Database::connect();
        $permissions = $db->table('role_permissions')
            ->select('role_permissions.*, permissions.name, permissions.description')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->get()->getResult();

        return $this->render('roles/index', compact('roles', 'allPermissions', 'permissions'), 'Roles');
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $roleModel = model('App\Models\RoleModel');
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ];

        if ($id) {
            $roleModel->update($id, $data);
        } else {
            $id = $roleModel->insert($data);
        }

        $db = \Config\Database::connect();
        $db->table('role_permissions')->where('role_id', $id)->delete();
        $perms = $this->request->getPost('permissions') ?: [];
        $batch = [];
        foreach ($perms as $p) {
            $batch[] = ['role_id' => $id, 'permission_id' => $p, 'created_at' => date('Y-m-d H:i:s')];
        }
        if (!empty($batch)) $db->table('role_permissions')->insertBatch($batch);

        $this->logAudit('save_role', 'roles', $id);
        return redirect()->to(base_url('roles'))->with('success', 'Rol guardado');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $db->table('role_permissions')->where('role_id', $id)->delete();
        model('App\Models\RoleModel')->delete($id);
        $this->logAudit('delete_role', 'roles', $id);
        return redirect()->to(base_url('roles'))->with('success', 'Rol eliminado');
    }
}
