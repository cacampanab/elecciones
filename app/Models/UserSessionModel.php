<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSessionModel extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = [
        'user_id', 'token_hash', 'ip_address', 'user_agent',
        'created_at', 'last_activity', 'revoked_at', 'revoked_by', 'revoke_reason',
    ];
    protected $useTimestamps = false;

    public function findByHash(string $hash)
    {
        return $this->where('token_hash', $hash)->first();
    }

    public function activeForUser(int $userId, int $idleSeconds, ?int $excludeId = null): array
    {
        $cutoff = date('Y-m-d H:i:s', time() - $idleSeconds);

        $builder = $this->where('user_id', $userId)
            ->where('revoked_at', null)
            ->where('last_activity >=', $cutoff)
            ->orderBy('last_activity', 'DESC');

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->findAll();
    }

    public function touch(int $id, string $at): void
    {
        $this->update($id, ['last_activity' => $at]);
    }

    public function revokeById(int $id, ?int $revokedBy, string $reason, string $at): void
    {
        $this->update($id, [
            'revoked_at' => $at,
            'revoked_by' => $revokedBy,
            'revoke_reason' => $reason,
        ]);
    }

    public function revokeAllForUser(int $userId, ?int $revokedBy, string $reason, string $at): void
    {
        $this->where('user_id', $userId)
            ->where('revoked_at', null)
            ->set([
                'revoked_at' => $at,
                'revoked_by' => $revokedBy,
                'revoke_reason' => $reason,
            ])
            ->update();
    }

    public function cleanup(string $before): void
    {
        $this->groupStart()
            ->where('last_activity <', $before)
            ->orGroupStart()
                ->where('revoked_at IS NOT NULL', null, false)
                ->where('revoked_at <', $before)
            ->groupEnd()
        ->groupEnd()
        ->delete();
    }
}
