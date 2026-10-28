<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrecinctsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'parish_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 10],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'address' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('parish_id', 'parishes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('precincts');
    }

    public function down()
    {
        $this->forge->dropTable('precincts');
    }
}
