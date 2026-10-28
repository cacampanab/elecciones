<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParishesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'canton_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 6],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('canton_id', 'cantons', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('parishes');
    }

    public function down()
    {
        $this->forge->dropTable('parishes');
    }
}
