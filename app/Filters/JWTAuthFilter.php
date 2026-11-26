<?php

namespace App\Filters;

use App\Libraries\SessionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');

        if (empty($header)) {
            return $this->unauthorized('Token no proporcionado', 'unauthorized');
        }

        $token = trim(str_replace('Bearer ', '', $header));

        try {
            $key = getenv('JWT_SECRET_KEY') ?: 'elecciones2026_secure_jwt_secret_key_ec';
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Exception $e) {
            return $this->unauthorized('Token inválido o expirado', 'unauthorized');
        }

        $userId = (int)($decoded->sub ?? 0);
        if ($userId <= 0) {
            return $this->unauthorized('Token inválido o expirado', 'unauthorized');
        }

        $service = new SessionService();
        $row = null;

        if (isset($decoded->sid) && (int)$decoded->sid > 0) {
            $row = $service->findById((int)$decoded->sid);
        } else {
            // JWT emitido antes de activar el control: se adopta la sesión.
            $hash = SessionService::hash($token);
            $row = $service->findByHash($hash);

            if (!$row) {
                if (!$service->userIsActive($userId)) {
                    return $this->unauthorized('Usuario no disponible', 'session_expired');
                }
                $rowId = $service->adoptHash(
                    $userId,
                    $hash,
                    $request->getIPAddress(),
                    (string)$request->getUserAgent()
                );
                $row = $service->findById($rowId);
            }
        }

        if (!$row || (int)$row->user_id !== $userId) {
            return $this->unauthorized('Sesión no válida', 'session_expired');
        }

        $reason = $service->validate($row, $userId);
        if ($reason !== null) {
            return $this->unauthorized('La sesión expiró o fue cerrada. Inicie sesión nuevamente.', 'session_expired');
        }

        $service->touch($row);

        $request->user = $decoded;
        $request->sessionRow = $row;

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function unauthorized(string $message, string $code)
    {
        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'success' => false,
                'code' => $code,
                'message' => $message,
            ]);
    }
}
