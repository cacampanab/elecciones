<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProvincesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 2],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'capital' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'region' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'population' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('provinces');
    }

    public function down()
    {
        $this->forge->dropTable('provinces');
    }
}
