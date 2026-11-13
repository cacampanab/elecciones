<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionSeatModel extends Model
{
    protected $table = 'election_seats';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['election_id', 'canton_id', 'dignity_id', 'circumscription_id', 'seats'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function forElection(int $electionId): array
    {
        return $this->where('election_id', $electionId)->findAll();
    }

    /**
     * Escaños configurados a nivel de cantón. Los cantones que usan
     * circunscripciones se excluyen porque sus escaños se leen aparte.
     */
    public function mapForElection(int $electionId): array
    {
        $excluded = $this->cantonIdsWithCircumscriptions($electionId);
        $map = [];

        $rows = $this->where('election_id', $electionId)
            ->where('circumscription_id IS NULL', null, false)
            ->findAll();
        foreach ($rows as $row) {
            $cantonId = (int)$row->canton_id;
            if (in_array($cantonId, $excluded, true)) {
                continue;
            }
            $map[$cantonId][(int)$row->dignity_id] = (int)$row->seats;
        }

        return $map;
    }

    /**
     * Escaños por circunscripción: [circumscription_id => seats].
     */
    public function mapForCircumscriptions(int $electionId): array
    {
        $map = [];
        $rows = $this->where('election_id', $electionId)
            ->where('circumscription_id IS NOT NULL', null, false)
            ->findAll();
        foreach ($rows as $row) {
            $map[(int)$row->circumscription_id] = (int)$row->seats;
        }

        return $map;
    }

    public function forCircumscription(int $electionId, int $circumscriptionId): int
    {
        $row = $this->where('election_id', $electionId)
            ->where('circumscription_id', $circumscriptionId)
            ->first();

        return $row ? (int)$row->seats : 0;
    }

    public function cantonIdsWithCircumscriptions(int $electionId): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('election_circumscriptions')) {
            return [];
        }

        $rows = $db->table('election_circumscriptions')
            ->select('DISTINCT canton_id', false)
            ->where('election_id', $electionId)
            ->where('is_active', 1)
            ->get()
            ->getResult();

        return array_map(static fn($row) => (int)$row->canton_id, $rows);
    }
}
