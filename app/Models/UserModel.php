<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $protectFields = false;
    protected $allowedFields = ['username', 'email', 'password', 'first_name', 'last_name', 'cedula', 'phone', 'role_id', 'is_active', 'last_login'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'username' => 'required|is_unique[users.username,id,{id}]|min_length[3]|max_length[50]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'cedula'   => 'permit_empty|is_unique[users.cedula,id,{id}]',
    ];

    public function getWithRole()
    {
        return $this->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->findAll();
    }

    public function getByRole($roleName)
    {
        return $this->select('users.*')
            ->join('roles', 'roles.id = users.role_id')
            ->where('roles.name', $roleName)
            ->findAll();
    }
}
