<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class RoleController extends BaseController
{
    public function index()
    {
        $roles = model('App\Models\RoleModel')->findAll();
        return jsonResponse($roles);
    }

    public function show($id = null)
    {
        $roleModel = model('App\Models\RoleModel');
        $role = $roleModel->find($id);
        if (!$role) return errorResponse('Rol no encontrado', 404);

        $role->permissions = $roleModel->getPermissions($id);
        return jsonResponse($role);
    }

    public function create()
    {
        if (!$this->validate(['name' => 'required|is_unique[roles.name]'])) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $roleModel = model('App\Models\RoleModel');
        $id = $roleModel->insert([
            'name' => $this->request->getVar('name'),
            'description' => $this->request->getVar('description'),
        ]);

        $permissions = $this->request->getVar('permissions');
        if (!empty($permissions)) {
            $db = \Config\Database::connect();
            $batch = [];
            foreach ($permissions as $permId) {
                $batch[] = ['role_id' => $id, 'permission_id' => $permId, 'created_at' => date('Y-m-d H:i:s')];
            }
            $db->table('role_permissions')->insertBatch($batch);
        }

        $this->logAudit('create_role', 'roles', $id);
        return jsonResponse(['id' => $id], 'Rol creado', 201);
    }

    public function update($id = null)
    {
        $roleModel = model('App\Models\RoleModel');
        if (!$roleModel->find($id)) return errorResponse('Rol no encontrado', 404);

        $data = [];
        if ($this->request->getVar('name')) $data['name'] = $this->request->getVar('name');
        if ($this->request->getVar('description') !== null) $data['description'] = $this->request->getVar('description');

        $roleModel->update($id, $data);

        $permissions = $this->request->getVar('permissions');
        if ($permissions !== null) {
            $db = \Config\Database::connect();
            $db->table('role_permissions')->where('role_id', $id)->delete();
            $batch = [];
            foreach ($permissions as $permId) {
                $batch[] = ['role_id' => $id, 'permission_id' => $permId, 'created_at' => date('Y-m-d H:i:s')];
            }
            if (!empty($batch)) $db->table('role_permissions')->insertBatch($batch);
        }

        $this->logAudit('update_role', 'roles', $id);
        return jsonResponse(null, 'Rol actualizado');
    }

    public function delete($id = null)
    {
        $roleModel = model('App\Models\RoleModel');
        if (!$roleModel->find($id)) return errorResponse('Rol no encontrado', 404);

        $db = \Config\Database::connect();
        $db->table('role_permissions')->where('role_id', $id)->delete();
        $roleModel->delete($id);

        $this->logAudit('delete_role', 'roles', $id);
        return jsonResponse(null, 'Rol eliminado');
    }

    public function permissions()
    {
        $permissions = model('App\Models\PermissionModel')->findAll();
        return jsonResponse($permissions);
    }
}
