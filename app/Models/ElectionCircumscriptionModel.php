<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionCircumscriptionModel extends Model
{
    protected $table = 'election_circumscriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = [
        'election_id', 'canton_id', 'dignity_id', 'code', 'name', 'is_active',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function forElection(int $electionId): array
    {
        return $this->where('election_id', $electionId)
            ->where('is_active', 1)
            ->orderBy('canton_id', 'ASC')
            ->orderBy('code', 'ASC')
            ->findAll();
    }

    public function forCanton(int $electionId, int $cantonId): array
    {
        return $this->where('election_id', $electionId)
            ->where('canton_id', $cantonId)
            ->where('is_active', 1)
            ->orderBy('code', 'ASC')
            ->findAll();
    }

    public function mapById(int $electionId): array
    {
        $map = [];
        foreach ($this->forElection($electionId) as $row) {
            $map[(int)$row->id] = $row;
        }

        return $map;
    }

    public function cantonIdsForElection(int $electionId): array
    {
        $rows = $this->select('DISTINCT canton_id', false)
            ->where('election_id', $electionId)
            ->where('is_active', 1)
            ->findAll();

        return array_map(static fn($row) => (int)$row->canton_id, $rows);
    }
}
