<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionCircumscriptionParishModel extends Model
{
    protected $table = 'election_circumscription_parishes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = [
        'election_id', 'canton_id', 'circumscription_id', 'parish_id',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function forCircumscription(int $circumscriptionId): array
    {
        $rows = $this->where('circumscription_id', $circumscriptionId)
            ->orderBy('parish_id', 'ASC')
            ->findAll();

        return array_map(static fn($row) => (int)$row->parish_id, $rows);
    }

    public function byParish(int $electionId, int $parishId)
    {
        return $this->where('election_id', $electionId)
            ->where('parish_id', $parishId)
            ->first();
    }

    public function parishMap(int $electionId): array
    {
        $map = [];
        foreach ($this->where('election_id', $electionId)->findAll() as $row) {
            $map[(int)$row->parish_id] = (int)$row->circumscription_id;
        }

        return $map;
    }
}
