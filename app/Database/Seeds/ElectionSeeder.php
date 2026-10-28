<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ElectionSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('elections')->insert([
            'name' => 'Elecciones Generales 2026',
            'description' => 'Elecciones Generales Ecuador 2026',
            'election_date' => '2026-08-09',
            'election_type' => 'nacional',
            'status' => 'en_curso',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
