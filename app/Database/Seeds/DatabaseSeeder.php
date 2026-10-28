<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('RoleSeeder');
        $this->call('PermissionSeeder');
        $this->call('UserSeeder');
        $this->call('DignitySeeder');
        $this->call('ProvinceSeeder');
    }
}
