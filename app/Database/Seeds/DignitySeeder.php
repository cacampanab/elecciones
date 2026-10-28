<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DignitySeeder extends Seeder
{
    public function run()
    {
        $this->db->table('dignities')->insertBatch([
            ['name' => 'Presidente', 'slug' => 'presidente', 'level' => 'nacional', 'description' => 'Presidente de la República', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Asambleísta Nacional', 'slug' => 'asambleista-nacional', 'level' => 'nacional', 'description' => 'Asambleísta Nacional', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Prefecto', 'slug' => 'prefecto', 'level' => 'provincial', 'description' => 'Prefecto Provincial', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Alcalde', 'slug' => 'alcalde', 'level' => 'cantonal', 'description' => 'Alcalde Municipal', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Concejal Urbano', 'slug' => 'concejal-urbano', 'level' => 'cantonal', 'description' => 'Concejal Urbano', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Concejal Rural', 'slug' => 'concejal-rural', 'level' => 'cantonal', 'description' => 'Concejal Rural', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Vocal Junta Parroquial', 'slug' => 'vocal-junta-parroquial', 'level' => 'parroquial', 'description' => 'Vocal de Junta Parroquial', 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }
}
