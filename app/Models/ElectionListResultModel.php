<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionListResultModel extends Model
{
    protected $table = 'election_list_results';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = [
        'polling_station_id', 'election_id', 'canton_id', 'circumscription_id', 'dignity_id', 'candidate_list_id',
        'votes', 'blank_votes', 'null_votes', 'total_voters', 'registered_voters',
        'is_verified', 'verified_by', 'verified_at', 'registered_by', 'latitude', 'longitude',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function forStation(int $pollingStationId, int $electionId, int $dignityId): array
    {
        return $this->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->where('dignity_id', $dignityId)
            ->findAll();
    }

    public function totalsForContext(int $electionId, int $cantonId, int $dignityId, ?int $circumscriptionId = null): array
    {
        $builder = $this->select('candidate_list_id, SUM(votes) AS total_votes', false)
            ->where('election_id', $electionId)
            ->where('canton_id', $cantonId)
            ->where('dignity_id', $dignityId);

        if ($circumscriptionId !== null) {
            $builder->where('circumscription_id', $circumscriptionId);
        } else {
            $builder->where('circumscription_id', null);
        }

        $rows = $builder->groupBy('candidate_list_id')->findAll();

        $totals = [];
        foreach ($rows as $row) {
            $totals[(int)$row->candidate_list_id] = (int)$row->total_votes;
        }

        return $totals;
    }
}
