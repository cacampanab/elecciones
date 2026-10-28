<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!isset($request->user)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'No autenticado']);
        }

        if (!empty($arguments)) {
            $userRole = $request->user->role ?? null;
            if (!in_array($userRole, $arguments)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['success' => false, 'message' => 'No tiene permisos para esta acción']);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
