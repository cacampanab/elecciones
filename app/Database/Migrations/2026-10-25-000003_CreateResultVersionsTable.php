<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResultVersionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'polling_station_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'election_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'dignity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'version' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'data' => ['type' => 'LONGTEXT'],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('polling_station_id', 'polling_stations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('election_id', 'elections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dignity_id', 'dignities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['polling_station_id', 'election_id', 'dignity_id']);
        $this->forge->createTable('result_versions');
    }

    public function down()
    {
        $this->forge->dropTable('result_versions');
    }
}
