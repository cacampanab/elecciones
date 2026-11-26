<?php

namespace App\Libraries;

use App\Models\UserSessionModel;

class SessionService
{
    /** Minutos de inactividad permitidos (30 minutos). */
    public const IDLE_SECONDS = 1800;

    /** Vigencia de la confirmación de reemplazo de sesión. */
    public const PENDING_SECONDS = 300;

    /** Días que se conservan las sesiones cerradas antes de limpiarlas. */
    private const KEEP_DAYS = 30;

    protected UserSessionModel $sessions;
    protected $db;

    public function __construct()
    {
        $this->sessions = model(UserSessionModel::class);
        $this->db = db_connect();
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function createSession(int $userId, ?string $ip, ?string $userAgent): array
    {
        $token = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');

        $id = $this->sessions->insert([
            'user_id' => $userId,
            'token_hash' => self::hash($token),
            'ip_address' => $ip,
            'user_agent' => $this->limitUserAgent($userAgent),
            'created_at' => $now,
            'last_activity' => $now,
        ]);

        if (!$id) {
            throw new \RuntimeException('No se pudo crear la sesión.');
        }

        return ['id' => (int)$id, 'token' => $token, 'created_at' => $now];
    }

    /**
     * Adopta un token existente (por ejemplo, un JWT emitido antes de
     * activar el control de sesiones) guardando su hash.
     */
    public function adoptHash(int $userId, string $hash, ?string $ip, ?string $userAgent): int
    {
        $now = date('Y-m-d H:i:s');

        try {
            $id = $this->sessions->insert([
                'user_id' => $userId,
                'token_hash' => $hash,
                'ip_address' => $ip,
                'user_agent' => $this->limitUserAgent($userAgent),
                'created_at' => $now,
                'last_activity' => $now,
            ]);
        } catch (\Throwable $e) {
            $existing = $this->findByHash($hash);
            if ($existing) {
                return (int)$existing->id;
            }
            throw $e;
        }

        if (!$id) {
            throw new \RuntimeException('No se pudo adoptar la sesión.');
        }

        return (int)$id;
    }

    public function findById(int $id)
    {
        return $this->sessions->find($id);
    }

    public function findByToken(string $token)
    {
        return $this->sessions->findByHash(self::hash($token));
    }

    public function findByHash(string $hash)
    {
        return $this->sessions->findByHash($hash);
    }

    public function activeForUser(int $userId): array
    {
        return $this->sessions->activeForUser($userId, self::IDLE_SECONDS);
    }

    public function userIsActive(int $userId): bool
    {
        $user = $this->db->table('users')
            ->select('id, is_active, deleted_at')
            ->where('id', $userId)
            ->get()
            ->getRow();

        return $user && (int)$user->is_active === 1 && $user->deleted_at === null;
    }

    /**
     * Devuelve null si la sesión es válida; si no, el motivo del cierre.
     */
    public function validate($session, int $userId): ?string
    {
        if (!$session || (int)$session->user_id !== $userId) {
            return 'invalid';
        }
        if ($session->revoked_at !== null) {
            return $session->revoke_reason ?: 'revoked';
        }
        if (strtotime((string)$session->last_activity) < time() - self::IDLE_SECONDS) {
            $this->revokeById((int)$session->id, null, 'expired');
            return 'expired';
        }
        if (!$this->userIsActive($userId)) {
            $this->revokeById((int)$session->id, null, 'user_inactive');
            return 'user_inactive';
        }

        return null;
    }

    public function touch($session): void
    {
        if (!$session) {
            return;
        }

        $last = strtotime((string)$session->last_activity);
        if ($last !== false && $last <= time() - 60) {
            $this->sessions->touch((int)$session->id, date('Y-m-d H:i:s'));
        }
    }

    public function revokeById(int $id, ?int $revokedBy, string $reason): void
    {
        $this->sessions->revokeById($id, $revokedBy, $reason, date('Y-m-d H:i:s'));
    }

    public function revokeToken(string $token, ?int $revokedBy, string $reason): void
    {
        $session = $this->findByToken($token);
        if ($session) {
            $this->revokeById((int)$session->id, $revokedBy, $reason);
        }
    }

    public function revokeAllForUser(int $userId, ?int $revokedBy, string $reason): void
    {
        $this->sessions->revokeAllForUser($userId, $revokedBy, $reason, date('Y-m-d H:i:s'));
    }

    public function cleanup(): void
    {
        $before = date('Y-m-d H:i:s', time() - (self::KEEP_DAYS * 86400));
        $this->sessions->cleanup($before);
    }

    public function info($session): array
    {
        if (!$session) {
            return [];
        }

        return [
            'id' => (int)$session->id,
            'ip_address' => $session->ip_address,
            'device' => $this->deviceLabel($session->user_agent),
            'user_agent' => $session->user_agent,
            'created_at' => $session->created_at,
            'last_activity' => $session->last_activity,
        ];
    }

    public function deviceLabel(?string $userAgent): string
    {
        $ua = (string)$userAgent;
        $os = 'Dispositivo';

        if (stripos($ua, 'iPhone') !== false) {
            $os = 'iPhone';
        } elseif (stripos($ua, 'iPad') !== false) {
            $os = 'iPad';
        } elseif (stripos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($ua, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($ua, 'Macintosh') !== false) {
            $os = 'Mac';
        } elseif (stripos($ua, 'Linux') !== false) {
            $os = 'Linux';
        }

        $browser = '';
        if (stripos($ua, 'Edg/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'Safari') !== false) {
            $browser = 'Safari';
        }

        return $browser ? $os . ' / ' . $browser : $os;
    }

    private function limitUserAgent(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        return mb_substr($userAgent, 0, 255);
    }
}
