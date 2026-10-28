<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedElectionDignities extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');
        $this->db->query(
            "INSERT IGNORE INTO election_dignities (election_id, dignity_id, is_active, created_at, updated_at)
             SELECT DISTINCT election_id, dignity_id, 1, ?, ? FROM candidates",
            [$now, $now]
        );
    }

    public function down()
    {
        $this->db->table('election_dignities')->truncate();
    }
}
