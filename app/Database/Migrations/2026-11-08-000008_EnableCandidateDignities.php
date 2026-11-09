<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnableCandidateDignities extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');
        $this->db->query(
            "INSERT INTO election_dignities (election_id, dignity_id, is_active, created_at, updated_at)
             SELECT DISTINCT election_id, dignity_id, 1, ?, ?
             FROM candidates
             ON DUPLICATE KEY UPDATE is_active = 1, updated_at = VALUES(updated_at)",
            [$now, $now]
        );
    }

    public function down()
    {
        // No se desactivan dignidades existentes.
    }
}
