<?php

namespace App\Models;

use CodeIgniter\Model;

class PrecinctModel extends Model
{
    protected $table = 'precincts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['parish_id', 'code', 'name', 'address', 'latitude', 'longitude', 'is_active'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getByParish($parishId)
    {
        return $this->where('parish_id', $parishId)->findAll();
    }
}
