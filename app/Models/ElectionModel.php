<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionModel extends Model
{
    protected $table = 'elections';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['name', 'description', 'election_date', 'election_type', 'status', 'created_by'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getActive()
    {
        return $this->where('status !=', 'anulada')->orderBy('election_date', 'DESC')->findAll();
    }

    public function getCurrent()
    {
        return $this->where('status', 'en_curso')
            ->orderBy('election_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }
}
