<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $adminRole = $db->table('roles')->where('name', 'Administrador General')->get()->getRow();

        $this->db->table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@elecciones.gob.ec',
            'password' => password_hash('Admin123!', PASSWORD_BCRYPT),
            'first_name' => 'Administrador',
            'last_name' => 'General',
            'cedula' => '0000000000001',
            'phone' => '0999999999',
            'role_id' => $adminRole->id,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
