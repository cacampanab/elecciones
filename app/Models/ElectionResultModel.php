<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionResultModel extends Model
{
    protected $table = 'election_results';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = [
        'polling_station_id', 'candidate_id', 'election_id', 'dignity_id', 'votes',
        'blank_votes', 'null_votes', 'total_voters', 'registered_voters',
        'is_verified', 'verified_by', 'verified_at', 'registered_by', 'latitude', 'longitude'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getResultsByDignity($electionId, $dignityId)
    {
        return $this->select('candidates.*, political_parties.name as party_name, political_parties.acronym as party_acronym, political_parties.list_number, political_parties.color, SUM(election_results.votes) as total_votes')
            ->join('candidates', 'candidates.id = election_results.candidate_id')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->where('election_results.election_id', $electionId)
            ->where('election_results.dignity_id', $dignityId)
            ->groupBy('election_results.candidate_id')
            ->orderBy('total_votes', 'DESC')
            ->findAll();
    }

    public function getResultsByProvince($electionId, $dignityId, $provinceCode)
    {
        return $this->select('candidates.*, political_parties.name as party_name, political_parties.acronym as party_acronym, SUM(election_results.votes) as total_votes')
            ->join('polling_stations', 'polling_stations.id = election_results.polling_station_id')
            ->join('candidates', 'candidates.id = election_results.candidate_id')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->where('election_results.election_id', $electionId)
            ->where('election_results.dignity_id', $dignityId)
            ->where('polling_stations.code_province', $provinceCode)
            ->groupBy('election_results.candidate_id')
            ->orderBy('total_votes', 'DESC')
            ->findAll();
    }

    public function getResultsByPollingStation($electionId, $pollingStationId)
    {
        return $this->select('election_results.*, candidates.first_name, candidates.last_name, political_parties.name as party_name, political_parties.acronym as party_acronym')
            ->join('candidates', 'candidates.id = election_results.candidate_id')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->where('election_results.election_id', $electionId)
            ->where('election_results.polling_station_id', $pollingStationId)
            ->findAll();
    }
}
