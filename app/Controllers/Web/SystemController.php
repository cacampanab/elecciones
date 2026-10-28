<?php

namespace App\Controllers\Web;

class SystemController extends BaseWebController
{
    public function migrate()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (!is_admin()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Solo el administrador puede aplicar migraciones.');
        }

        try {
            \Config\Services::migrations()->latest();
            $this->logAudit('run_migrations', 'system');
            return redirect()->to(base_url('dashboard'))->with('success', 'Migraciones aplicadas correctamente.');
        } catch (\Throwable $e) {
            log_message('error', 'Error al aplicar migraciones: ' . $e->getMessage());
            return redirect()->to(base_url('dashboard'))->with('error', 'No se pudieron aplicar las migraciones: ' . $e->getMessage());
        }
    }
}
