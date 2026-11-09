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
    protected $allowedFields = ['election_id', 'canton_id', 'dignity_id', 'seats'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function forElection(int $electionId): array
    {
        return $this->where('election_id', $electionId)->findAll();
    }

    public function mapForElection(int $electionId): array
    {
        $map = [];
        foreach ($this->forElection($electionId) as $row) {
            $map[(int)$row->canton_id][(int)$row->dignity_id] = (int)$row->seats;
        }

        return $map;
    }
}
