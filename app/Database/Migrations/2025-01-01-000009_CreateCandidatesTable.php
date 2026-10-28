<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCandidatesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'first_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'last_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'cedula' => ['type' => 'VARCHAR', 'constraint' => 13],
            'photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'dignity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'political_party_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'candidate_list_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'election_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('cedula');
        $this->forge->addForeignKey('dignity_id', 'dignities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('political_party_id', 'political_parties', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('candidate_list_id', 'candidate_lists', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('election_id', 'elections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('candidates');
    }

    public function down()
    {
        $this->forge->dropTable('candidates');
    }
}
