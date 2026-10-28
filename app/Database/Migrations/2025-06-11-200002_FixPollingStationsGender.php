<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixPollingStationsGender extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->query('DELETE FROM polling_stations');
        $this->db->query('ALTER TABLE `polling_stations` ADD `genero` ENUM("femenina","masculina") NULL AFTER `name_precinct`');
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `polling_stations` DROP COLUMN `genero`');
    }
}
