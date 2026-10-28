<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePollingStationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'precinct_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'election_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'code_province' => ['type' => 'VARCHAR', 'constraint' => 2],
            'name_province' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code_canton' => ['type' => 'VARCHAR', 'constraint' => 4],
            'name_canton' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code_parish' => ['type' => 'VARCHAR', 'constraint' => 6],
            'name_parish' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code_precinct' => ['type' => 'VARCHAR', 'constraint' => 10],
            'name_precinct' => ['type' => 'VARCHAR', 'constraint' => 255],
            'junta_femenina_inicio' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'junta_femenina_fin' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'junta_masculina_inicio' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'junta_masculina_fin' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'numero_junta' => ['type' => 'VARCHAR', 'constraint' => 20],
            'numero_electores' => ['type' => 'INT', 'constraint' => 11],
            'status' => ['type' => 'ENUM', 'constraint' => ['pendiente', 'en_proceso', 'reportada', 'revisada', 'aprobada'], 'default' => 'pendiente'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('precinct_id', 'precincts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('election_id', 'elections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('polling_stations');
    }

    public function down()
    {
        $this->forge->dropTable('polling_stations');
    }
}
