<?php

namespace App\Filters;

use App\Libraries\SessionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionGuardFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return null;
        }

        $userId = (int)$session->get('user_id');
        $service = new SessionService();
        $token = $session->get('session_token');

        // Adopta sesiones abiertas antes de activar este control.
        if (!$token) {
            $lastRegenerate = (int)$session->get('__ci_last_regenerate');
            if ($lastRegenerate > 0 && $lastRegenerate < time() - SessionService::IDLE_SECONDS) {
                $session->destroy();
                return $this->deny($request, 'expired');
            }

            if (!$service->userIsActive($userId)) {
                $session->destroy();
                return $this->deny($request, 'user_inactive');
            }

            $created = $service->createSession(
                $userId,
                $request->getIPAddress(),
                (string)$request->getUserAgent()
            );
            $session->set('session_token', $created['token']);
            $session->set('session_id', $created['id']);

            return null;
        }

        $row = $service->findByToken((string)$token);
        if (!$row || (int)$row->user_id !== $userId) {
            $session->destroy();
            return $this->deny($request, 'session_closed');
        }

        $reason = $service->validate($row, $userId);
        if ($reason !== null) {
            $session->destroy();
            return $this->deny($request, $reason);
        }

        $service->touch($row);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function deny(RequestInterface $request, string $reason)
    {
        $path = trim($request->getPath(), '/');
        $wantsJson = $request->isAJAX()
            || str_starts_with($path, 'ajax/')
            || str_contains($request->getHeaderLine('Accept'), 'application/json');

        if ($wantsJson) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'code' => 'session_expired',
                    'message' => 'La sesión ya no está activa. Vuelva a iniciar sesión.',
                    'reason' => $reason,
                ]);
        }

        $map = [
            'other_login' => 'other_login',
            'admin' => 'admin',
            'expired' => 'expired',
            'user_inactive' => 'user_inactive',
            'session_closed' => 'session_closed',
            'invalid' => 'session_closed',
            'revoked' => 'session_closed',
            'replaced' => 'other_login',
            'logout' => 'session_closed',
        ];
        $safe = $map[$reason] ?? 'session_closed';

        return redirect()->to(base_url('login?reason=' . urlencode($safe)));
    }
}
