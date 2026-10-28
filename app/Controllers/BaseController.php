<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = ['jwt', 'audit', 'response', 'permission'];
    protected $db;
    protected $userModel;
    protected $auditModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        $this->userModel = model('App\Models\UserModel');
        $this->auditModel = model('App\Models\AuditModel');
    }

    protected function getUserId()
    {
        if (session()->get('user_id')) return session()->get('user_id');
        return $this->request->user->sub ?? null;
    }

    protected function logAudit($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null)
    {
        $userId = $this->getUserId();
        if ($userId) {
            $this->auditModel->log($userId, $action, $tableName, $recordId, $oldValues, $newValues);
        }
    }
}
