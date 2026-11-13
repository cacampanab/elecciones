<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionListVoteModel extends Model
{
    protected $table = 'election_list_votes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = [
        'election_id', 'canton_id', 'circumscription_id', 'dignity_id', 'candidate_list_id',
        'votes', 'previous_votes', 'created_by',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function mapForContext(int $electionId, int $cantonId, int $dignityId, ?int $circumscriptionId = null): array
    {
        $builder = $this->where('election_id', $electionId)
            ->where('canton_id', $cantonId)
            ->where('dignity_id', $dignityId);

        if ($circumscriptionId !== null) {
            $builder->where('circumscription_id', $circumscriptionId);
        } else {
            $builder->where('circumscription_id', null);
        }

        $map = [];
        foreach ($builder->findAll() as $row) {
            $map[(int)$row->candidate_list_id] = [
                'votes' => (int)$row->votes,
                'previous_votes' => (int)$row->previous_votes,
            ];
        }

        return $map;
    }
}
