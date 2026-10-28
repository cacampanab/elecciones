<?php

namespace App\Models;

use CodeIgniter\Model;

class CantonModel extends Model
{
    protected $table = 'cantons';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['province_id', 'code', 'name', 'is_active'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getByProvince($provinceId)
    {
        return $this->where('province_id', $provinceId)->findAll();
    }
}
