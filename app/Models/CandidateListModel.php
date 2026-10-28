<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateListModel extends Model
{
    protected $table = 'candidate_lists';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['election_id', 'political_party_id', 'dignity_id', 'name', 'is_active'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
