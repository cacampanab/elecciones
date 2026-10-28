<?php

namespace App\Controllers\Web;

class AuthController extends BaseWebController
{
    public function loginForm()
    {
        if (session()->get('isLoggedIn')) {
            $target = session()->get('role') === 'Operador Electoral' ? 'results/register' : 'dashboard';
            return redirect()->to(base_url($target));
        }
        return view('auth/login');
    }

    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.username', $username)
            ->where('users.is_active', 1)
            ->first();

        if (!$user || !password_verify($password, $user->password)) {
            return redirect()->back()->with('error', 'Credenciales inválidas');
        }

        $this->userModel->update($user->id, ['last_login' => date('Y-m-d H:i:s')]);

        session()->set([
            'isLoggedIn' => true,
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'cedula' => $user->cedula,
            'role' => $user->role_name,
            'role_id' => $user->role_id,
        ]);

        $this->logAudit('login', 'users', $user->id);

        $target = $user->role_name === 'Operador Electoral' ? 'results/register' : 'dashboard';
        return redirect()->to(base_url($target))->with('success', 'Bienvenido ' . $user->first_name);
    }

    public function logout()
    {
        $this->logAudit('logout', 'users', session()->get('user_id'));
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Sesión cerrada');
    }
}
