<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCantonAndPositionForConcejales extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE candidate_lists ADD COLUMN canton_id INT UNSIGNED NULL AFTER political_party_id');
        $this->db->query('ALTER TABLE candidate_lists ADD CONSTRAINT fk_candidate_lists_canton FOREIGN KEY (canton_id) REFERENCES cantons(id) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE candidate_lists ADD UNIQUE KEY uq_candidate_lists_context (election_id, canton_id, dignity_id, political_party_id)');

        $this->db->query('ALTER TABLE candidates ADD COLUMN position INT UNSIGNED NULL AFTER candidate_list_id');
        $this->db->query('ALTER TABLE candidates ADD UNIQUE KEY uq_candidates_list_position (candidate_list_id, position)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE candidates DROP INDEX uq_candidates_list_position');
        $this->db->query('ALTER TABLE candidates DROP COLUMN position');

        $this->db->query('ALTER TABLE candidate_lists DROP INDEX uq_candidate_lists_context');
        $this->db->query('ALTER TABLE candidate_lists DROP FOREIGN KEY fk_candidate_lists_canton');
        $this->db->query('ALTER TABLE candidate_lists DROP COLUMN canton_id');
    }
}
