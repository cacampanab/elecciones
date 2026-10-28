<?php

namespace App\Controllers\Web;

class UserController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        $users = $this->userModel->getWithRole();
        $roles = model('App\Models\RoleModel')->findAll();
        return $this->render('users/index', compact('users', 'roles'), 'Usuarios');
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'cedula' => $this->request->getPost('cedula'),
            'phone' => $this->request->getPost('phone'),
            'role_id' => $this->request->getPost('role_id'),
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ];

        if ($password = $this->request->getPost('password')) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($id) {
            if (!isset($data['password'])) unset($data['password']);
            $this->userModel->update($id, $data);
            $this->logAudit('update_user', 'users', $id);
            return redirect()->to(base_url('users'))->with('success', 'Usuario actualizado');
        } else {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);
            $id = $this->userModel->insert($data);
            $this->logAudit('create_user', 'users', $id);
            return redirect()->to(base_url('users'))->with('success', 'Usuario creado');
        }
    }

    public function delete($id)
    {
        $this->userModel->delete($id);
        $this->logAudit('delete_user', 'users', $id);
        return redirect()->to(base_url('users'))->with('success', 'Usuario eliminado');
    }
}
