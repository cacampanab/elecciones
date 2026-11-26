<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSessionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'last_activity' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'revoked_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'revoke_reason' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash', 'uq_user_sessions_token');
        $this->forge->addKey(['user_id', 'revoked_at', 'last_activity'], false, false, 'idx_user_sessions_user');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('revoked_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('user_sessions');
    }

    public function down()
    {
        $this->forge->dropTable('user_sessions');
    }
}
