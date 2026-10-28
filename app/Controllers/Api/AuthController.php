<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $user = $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.username', $username)
            ->where('users.is_active', 1)
            ->first();

        if (!$user || !password_verify($password, $user->password)) {
            return errorResponse('Credenciales inválidas', 401);
        }

        $this->userModel->update($user->id, ['last_login' => date('Y-m-d H:i:s')]);

        $token = generateJWT($user);
        $refreshToken = generateRefreshToken();

        $refreshModel = model('App\Models\RefreshTokenModel');
        $refreshModel->insert([
            'user_id' => $user->id,
            'token' => password_hash($refreshToken, PASSWORD_BCRYPT),
            'expires_at' => date('Y-m-d H:i:s', time() + 2592000),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logAudit('login', 'users', $user->id);

        return jsonResponse([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'cedula' => $user->cedula,
                'role' => $user->role_name,
                'role_id' => $user->role_id,
            ],
        ], 'Login exitoso');
    }

    public function refresh()
    {
        $rules = ['refresh_token' => 'required'];
        if (!$this->validate($rules)) {
            return errorResponse('Token requerido', 400);
        }

        $refreshToken = $this->request->getVar('refresh_token');
        $refreshModel = model('App\Models\RefreshTokenModel');
        $tokens = $refreshModel->where('revoked', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $validToken = null;
        foreach ($tokens as $rt) {
            if (password_verify($refreshToken, $rt->token)) {
                $validToken = $rt;
                break;
            }
        }

        if (!$validToken) {
            return errorResponse('Refresh token inválido o expirado', 401);
        }

        $user = $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $validToken->user_id)
            ->first();

        if (!$user || !$user->is_active) {
            return errorResponse('Usuario no disponible', 401);
        }

        $newToken = generateJWT($user);
        $newRefreshToken = generateRefreshToken();

        $refreshModel->update($validToken->id, ['revoked' => 1]);
        $refreshModel->insert([
            'user_id' => $user->id,
            'token' => password_hash($newRefreshToken, PASSWORD_BCRYPT),
            'expires_at' => date('Y-m-d H:i:s', time() + 2592000),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return jsonResponse([
            'token' => $newToken,
            'refresh_token' => $newRefreshToken,
        ], 'Token renovado');
    }

    public function me()
    {
        $userId = $this->getUserId();
        $user = $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $userId)
            ->first();

        if (!$user) {
            return errorResponse('Usuario no encontrado', 404);
        }

        $roleModel = model('App\Models\RoleModel');
        $permissions = $roleModel->getPermissions($user->role_id);

        return jsonResponse([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'cedula' => $user->cedula,
                'phone' => $user->phone,
                'role' => $user->role_name,
                'role_id' => $user->role_id,
                'is_active' => $user->is_active,
                'last_login' => $user->last_login,
            ],
            'permissions' => array_map(fn($p) => $p->name, $permissions),
        ]);
    }

    public function logout()
    {
        $userId = $this->getUserId();
        $refreshModel = model('App\Models\RefreshTokenModel');
        $refreshModel->where('user_id', $userId)->set(['revoked' => 1])->update();

        $this->logAudit('logout', 'users', $userId);
        return jsonResponse(null, 'Sesión cerrada exitosamente');
    }

    public function updateProfile()
    {
        $userId = $this->getUserId();
        $rules = [
            'first_name' => 'permit_empty|min_length[2]|max_length[100]',
            'last_name' => 'permit_empty|min_length[2]|max_length[100]',
            'phone' => 'permit_empty|max_length[20]',
            'current_password' => 'permit_empty',
            'new_password' => 'permit_empty|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $data = [];
        if ($this->request->getVar('first_name')) $data['first_name'] = $this->request->getVar('first_name');
        if ($this->request->getVar('last_name')) $data['last_name'] = $this->request->getVar('last_name');
        if ($this->request->getVar('phone')) $data['phone'] = $this->request->getVar('phone');

        if ($this->request->getVar('current_password') && $this->request->getVar('new_password')) {
            $user = $this->userModel->find($userId);
            if (!password_verify($this->request->getVar('current_password'), $user->password)) {
                return errorResponse('Contraseña actual incorrecta', 400);
            }
            $data['password'] = password_hash($this->request->getVar('new_password'), PASSWORD_BCRYPT);
        }

        if (!empty($data)) {
            $this->userModel->update($userId, $data);
            $this->logAudit('update_profile', 'users', $userId);
        }

        return jsonResponse(null, 'Perfil actualizado');
    }
}
