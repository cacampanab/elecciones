<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class UserController extends BaseController
{
    public function index()
    {
        $users = $this->userModel->getWithRole();
        return jsonResponse($users);
    }

    public function show($id = null)
    {
        $user = $this->userModel->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $id)
            ->first();
        if (!$user) return errorResponse('Usuario no encontrado', 404);
        return jsonResponse($user);
    }

    public function create()
    {
        $rules = [
            'username' => 'required|is_unique[users.username]|min_length[3]|max_length[50]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'role_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getVar('username'),
            'email' => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
            'first_name' => $this->request->getVar('first_name'),
            'last_name' => $this->request->getVar('last_name'),
            'cedula' => $this->request->getVar('cedula'),
            'phone' => $this->request->getVar('phone'),
            'role_id' => $this->request->getVar('role_id'),
            'is_active' => $this->request->getVar('is_active') ?? 1,
        ];

        $id = $this->userModel->insert($data);
        $this->logAudit('create_user', 'users', $id, null, $data);

        return jsonResponse(['id' => $id], 'Usuario creado', 201);
    }

    public function update($id = null)
    {
        if (!$this->userModel->find($id)) return errorResponse('Usuario no encontrado', 404);

        $data = [];
        if ($this->request->getVar('username')) $data['username'] = $this->request->getVar('username');
        if ($this->request->getVar('email')) $data['email'] = $this->request->getVar('email');
        if ($this->request->getVar('first_name')) $data['first_name'] = $this->request->getVar('first_name');
        if ($this->request->getVar('last_name')) $data['last_name'] = $this->request->getVar('last_name');
        if ($this->request->getVar('cedula')) $data['cedula'] = $this->request->getVar('cedula');
        if ($this->request->getVar('phone')) $data['phone'] = $this->request->getVar('phone');
        if ($this->request->getVar('role_id')) $data['role_id'] = $this->request->getVar('role_id');
        if ($this->request->getVar('password')) $data['password'] = password_hash($this->request->getVar('password'), PASSWORD_BCRYPT);
        if ($this->request->getVar('is_active') !== null) $data['is_active'] = $this->request->getVar('is_active');

        $oldValues = $this->userModel->find($id);
        $this->userModel->update($id, $data);
        $this->logAudit('update_user', 'users', $id, (array)$oldValues, $data);

        return jsonResponse(null, 'Usuario actualizado');
    }

    public function delete($id = null)
    {
        if (!$this->userModel->find($id)) return errorResponse('Usuario no encontrado', 404);

        $oldValues = $this->userModel->find($id);
        $this->userModel->delete($id);
        $this->logAudit('delete_user', 'users', $id, (array)$oldValues);

        return jsonResponse(null, 'Usuario eliminado');
    }

    public function operators()
    {
        $users = $this->userModel->getByRole('Operador Electoral');
        return jsonResponse($users);
    }

    public function supervisors()
    {
        $users = $this->userModel->getByRole('Supervisor');
        return jsonResponse($users);
    }
}
