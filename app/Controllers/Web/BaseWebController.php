<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

abstract class BaseWebController extends BaseController
{
    protected function requireAuth()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Debe iniciar sesión');
        }
        return null;
    }

    protected function requireRole($role)
    {
        if (session()->get('role') !== $role) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tiene permisos para esta acción');
        }
        return null;
    }

    protected function requirePermission(string $permission)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (!has_permission($permission)) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tiene permisos para esta acción');
        }
        return null;
    }

    protected function requireAnyPermission(array $permissions)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        foreach ($permissions as $permission) {
            if (has_permission($permission)) {
                return null;
            }
        }
        return redirect()->to(base_url('dashboard'))->with('error', 'No tiene permisos para esta acción');
    }

    protected function requireAssignment(int $pollingStationId, int $electionId)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (!is_operator()) {
            return null;
        }
        $assigned = $this->db->table('assignments')
            ->where('user_id', (int)session()->get('user_id'))
            ->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->where('is_active', 1)
            ->countAllResults();
        if (!$assigned) {
            return redirect()->to(base_url('results/register'))->with('error', 'No tiene asignada esta JRV.');
        }
        return null;
    }

    protected function render($view, $data = [], $title = 'Sistema de Control Electoral')
    {
        $data['title'] = $title;
        return view($view, $data);
    }
}
