<?php

namespace App\Controllers\Web;

use App\Libraries\SessionService;

class AuthController extends BaseWebController
{
    public function loginForm()
    {
        if (session()->get('isLoggedIn')) {
            $target = session()->get('role') === 'Operador Electoral' ? 'results/register' : 'dashboard';
            return redirect()->to(base_url($target));
        }

        if ($this->request->getGet('cancel')) {
            session()->remove('pending_login');
        }

        return view('auth/login', [
            'reason' => $this->request->getGet('reason'),
        ]);
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

        $service = new SessionService();
        $active = $service->activeForUser((int)$user->id);

        if ($active) {
            $pendingToken = bin2hex(random_bytes(32));
            session()->set('pending_login', [
                'user_id' => (int)$user->id,
                'token' => $pendingToken,
                'at' => time(),
            ]);

            return view('auth/session-conflict', [
                'active' => $active[0],
                'pendingToken' => $pendingToken,
                'user' => $user,
                'device' => $service->deviceLabel($active[0]->user_agent ?? ''),
            ]);
        }

        return $this->finalizeLogin($user);
    }

    public function confirmLogin()
    {
        $pending = session()->get('pending_login');
        $token = (string)$this->request->getPost('pending_token');

        if (!is_array($pending)
            || empty($pending['user_id'])
            || empty($pending['token'])
            || empty($pending['at'])
            || (time() - (int)$pending['at']) > SessionService::PENDING_SECONDS
            || !hash_equals((string)$pending['token'], $token)) {
            session()->remove('pending_login');
            return redirect()->to(base_url('login'))->with('error', 'La confirmación expiró. Ingrese nuevamente.');
        }

        $user = $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', (int)$pending['user_id'])
            ->where('users.is_active', 1)
            ->first();

        if (!$user) {
            session()->remove('pending_login');
            return redirect()->to(base_url('login'))->with('error', 'El usuario no está disponible.');
        }

        $service = new SessionService();
        $service->revokeAllForUser((int)$user->id, (int)$user->id, 'other_login');
        session()->remove('pending_login');

        return $this->finalizeLogin($user);
    }

    public function cancelLogin()
    {
        session()->remove('pending_login');
        return redirect()->to(base_url('login'));
    }

    public function logout()
    {
        $service = new SessionService();
        $token = session()->get('session_token');

        if ($token) {
            $service->revokeToken((string)$token, (int)session()->get('user_id') ?: null, 'logout');
        }

        $this->logAudit('logout', 'users', session()->get('user_id'));
        session()->destroy();

        return redirect()->to(base_url('login?reason=logged_out'));
    }

    private function finalizeLogin($user)
    {
        $service = new SessionService();
        $service->cleanup();

        session()->regenerate(true);

        $created = $service->createSession(
            (int)$user->id,
            $this->request->getIPAddress(),
            (string)$this->request->getUserAgent()
        );

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
            'session_token' => $created['token'],
            'session_id' => $created['id'],
        ]);

        $this->logAudit('login', 'users', $user->id);

        $target = $user->role_name === 'Operador Electoral' ? 'results/register' : 'dashboard';
        return redirect()->to(base_url($target))->with('success', 'Bienvenido ' . $user->first_name);
    }
}
