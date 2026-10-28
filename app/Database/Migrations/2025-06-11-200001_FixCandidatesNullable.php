<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixCandidatesNullable extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `candidates` DROP FOREIGN KEY `candidates_candidate_list_id_foreign`');
        $this->db->query('ALTER TABLE `candidates` MODIFY `candidate_list_id` INT UNSIGNED NULL');
        $this->db->query('ALTER TABLE `candidates` MODIFY `cedula` VARCHAR(13) NULL');
        $this->db->query('ALTER TABLE `candidates` ADD CONSTRAINT `candidates_candidate_list_id_foreign` FOREIGN KEY (`candidate_list_id`) REFERENCES `candidate_lists` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `candidates` DROP FOREIGN KEY `candidates_candidate_list_id_foreign`');
        $this->db->query('ALTER TABLE `candidates` MODIFY `candidate_list_id` INT UNSIGNED NOT NULL');
        $this->db->query('ALTER TABLE `candidates` MODIFY `cedula` VARCHAR(13) NOT NULL');
        $this->db->query('ALTER TABLE `candidates` ADD CONSTRAINT `candidates_candidate_list_id_foreign` FOREIGN KEY (`candidate_list_id`) REFERENCES `candidate_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
    }
}
