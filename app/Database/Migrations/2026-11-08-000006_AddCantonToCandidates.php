<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCantonToCandidates extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE candidates ADD COLUMN canton_id INT UNSIGNED NULL AFTER election_id');
        $this->db->query('ALTER TABLE candidates ADD CONSTRAINT fk_candidates_canton FOREIGN KEY (canton_id) REFERENCES cantons(id) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE candidates ADD INDEX idx_candidates_election_dignity_canton (election_id, dignity_id, canton_id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE candidates DROP INDEX idx_candidates_election_dignity_canton');
        $this->db->query('ALTER TABLE candidates DROP FOREIGN KEY fk_candidates_canton');
        $this->db->query('ALTER TABLE candidates DROP COLUMN canton_id');
    }
}
