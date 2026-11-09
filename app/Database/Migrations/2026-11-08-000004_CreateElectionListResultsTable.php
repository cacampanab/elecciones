<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateElectionListResultsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'polling_station_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'election_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'canton_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'dignity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'candidate_list_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'votes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'blank_votes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'null_votes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total_voters' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'registered_voters' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_verified' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'verified_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'verified_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'registered_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['polling_station_id', 'election_id', 'dignity_id', 'candidate_list_id'], 'uq_list_results_station');
        $this->forge->addForeignKey('polling_station_id', 'polling_stations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('election_id', 'elections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('canton_id', 'cantons', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dignity_id', 'dignities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('candidate_list_id', 'candidate_lists', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('verified_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('registered_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('election_list_results');
    }

    public function down()
    {
        $this->forge->dropTable('election_list_results');
    }
}
