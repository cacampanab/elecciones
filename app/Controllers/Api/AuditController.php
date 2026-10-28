<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class AuditController extends BaseController
{
    public function index()
    {
        $page = (int)($this->request->getVar('page') ?? 1);
        $perPage = (int)($this->request->getVar('per_page') ?? 50);
        $userId = $this->request->getVar('user_id');
        $action = $this->request->getVar('action');

        $model = model('App\Models\AuditModel');
        $builder = $model
            ->select('audits.*, users.username, users.first_name, users.last_name')
            ->join('users', 'users.id = audits.user_id', 'left');

        if ($userId) $builder->where('audits.user_id', $userId);
        if ($action) $builder->like('audits.action', $action);

        $total = $builder->countAllResults(false);
        $audits = $builder->orderBy('audits.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->findAll();

        return jsonResponse([
            'data' => $audits,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ]);
    }

    public function show($id = null)
    {
        $audit = model('App\Models\AuditModel')
            ->select('audits.*, users.username, users.first_name, users.last_name')
            ->join('users', 'users.id = audits.user_id', 'left')
            ->where('audits.id', $id)
            ->first();
        if (!$audit) return errorResponse('Registro no encontrado', 404);
        return jsonResponse($audit);
    }
}
