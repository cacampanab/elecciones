<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateElectionListVotesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'election_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'canton_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'dignity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'candidate_list_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'votes' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'previous_votes' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['election_id', 'canton_id', 'dignity_id', 'candidate_list_id'], 'uq_list_votes_context');
        $this->forge->addForeignKey('election_id', 'elections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('canton_id', 'cantons', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dignity_id', 'dignities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('candidate_list_id', 'candidate_lists', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('election_list_votes');
    }

    public function down()
    {
        $this->forge->dropTable('election_list_votes');
    }
}
