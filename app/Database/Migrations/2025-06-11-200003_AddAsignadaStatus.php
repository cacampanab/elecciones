<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAsignadaStatus extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE polling_stations MODIFY status ENUM("pendiente","asignada","en_proceso","reportada","revisada","aprobada") DEFAULT "pendiente"');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE polling_stations MODIFY status ENUM("pendiente","en_proceso","reportada","revisada","aprobada") DEFAULT "pendiente"');
    }
}
