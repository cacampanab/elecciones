<?php

namespace App\Controllers\Web;

class ProfileController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        return $this->render('profile/index', [], 'Mi Perfil');
    }

    public function update()
    {
        $userId = session()->get('user_id');
        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
        ];
        $this->userModel->update($userId, $data);
        session()->set(array_intersect_key($data, array_flip(['first_name', 'last_name'])));

        $this->logAudit('update_profile', 'users', $userId);
        return redirect()->to(base_url('profile'))->with('success', 'Perfil actualizado');
    }

    public function password()
    {
        $userId = session()->get('user_id');
        $current = $this->request->getPost('current_password');
        $newPass = $this->request->getPost('new_password');

        $user = $this->userModel->find($userId);
        if (!password_verify($current, $user->password)) {
            return redirect()->back()->with('error', 'Contraseña actual incorrecta');
        }

        $this->userModel->update($userId, ['password' => password_hash($newPass, PASSWORD_BCRYPT)]);
        $this->logAudit('change_password', 'users', $userId);
        return redirect()->to(base_url('profile'))->with('success', 'Contraseña cambiada');
    }
}
