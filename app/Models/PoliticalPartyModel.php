<?php

namespace App\Models;

use CodeIgniter\Model;

class PoliticalPartyModel extends Model
{
    protected $table = 'political_parties';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['name', 'acronym', 'logo', 'list_number', 'color', 'is_active'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
