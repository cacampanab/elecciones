<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateModel extends Model
{
    protected $table = 'candidates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['first_name', 'last_name', 'cedula', 'photo', 'dignity_id', 'political_party_id', 'candidate_list_id', 'position', 'election_id', 'canton_id', 'is_active'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getWithRelations($electionId = null, $dignityId = null)
    {
        $builder = $this->select('candidates.*, dignities.name as dignity_name, dignities.slug as dignity_slug, political_parties.name as party_name, political_parties.acronym as party_acronym, political_parties.list_number as list_number, political_parties.color as party_color, elections.name as election_name, cantons.name as canton_name, provinces.name as province_name, provinces.id as province_id')
            ->join('dignities', 'dignities.id = candidates.dignity_id')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->join('elections', 'elections.id = candidates.election_id')
            ->join('cantons', 'cantons.id = candidates.canton_id', 'left')
            ->join('provinces', 'provinces.id = cantons.province_id', 'left');
        if ($electionId) {
            $builder->where('candidates.election_id', $electionId);
        }
        if ($dignityId) {
            $builder->where('candidates.dignity_id', $dignityId);
        }
        return $builder->findAll();
    }
}
