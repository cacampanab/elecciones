<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $roles = [
            ['name' => 'Administrador General', 'description' => 'Acceso completo al sistema'],
            ['name' => 'Operador Electoral', 'description' => 'Registro de resultados en JRV asignadas'],
            ['name' => 'Visualizador', 'description' => 'Solo lectura y monitoreo'],
            ['name' => 'Supervisor', 'description' => 'Corrige y verifica actas'],
        ];
        foreach ($roles as $role) {
            $exists = $this->db->table('roles')->where('name', $role['name'])->countAllResults();
            if (!$exists) {
                $this->db->table('roles')->insert($role + ['created_at' => $now]);
            }
        }
    }
}
