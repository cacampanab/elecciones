<?php

namespace App\Models;

use CodeIgniter\Model;

class ParishModel extends Model
{
    protected $table = 'parishes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['canton_id', 'code', 'name', 'is_active'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getByCanton($cantonId)
    {
        return $this->where('canton_id', $cantonId)->findAll();
    }
}
