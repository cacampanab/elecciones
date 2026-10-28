<?php

namespace App\Controllers\Web;

class AuditController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $r = $this->requireRole('Administrador General');
        if ($r) return $r;

        return $this->render('audit/index', [], 'Auditoría');
    }
}
