<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnableConcejalElectionDignities extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');
        $this->db->query(
            "INSERT IGNORE INTO election_dignities (election_id, dignity_id, is_active, created_at, updated_at)
             SELECT DISTINCT cl.election_id, cl.dignity_id, 1, ?, ?
             FROM candidate_lists cl
             JOIN dignities d ON d.id = cl.dignity_id
             WHERE d.slug IN ('concejal-urbano', 'concejal-rural')",
            [$now, $now]
        );
    }

    public function down()
    {
        // No se eliminan configuraciones existentes para no afectar elecciones en curso.
    }
}
