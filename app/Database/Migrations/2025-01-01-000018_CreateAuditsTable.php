<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 100],
            'table_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'record_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'old_values' => ['type' => 'JSON', 'null' => true],
            'new_values' => ['type' => 'JSON', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET_NULL', 'CASCADE');
        $this->forge->createTable('audits');
    }

    public function down()
    {
        $this->forge->dropTable('audits');
    }
}
