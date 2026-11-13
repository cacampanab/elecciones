<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateElectionCircumscriptionParishesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'election_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'canton_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'circumscription_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'parish_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        // Una parroquia solo puede pertenecer a una circunscripción por elección.
        $this->forge->addUniqueKey(['election_id', 'parish_id'], 'uq_circumscription_parish');
        $this->forge->addForeignKey('election_id', 'elections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('canton_id', 'cantons', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('circumscription_id', 'election_circumscriptions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parish_id', 'parishes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('election_circumscription_parishes');
    }

    public function down()
    {
        $this->forge->dropTable('election_circumscription_parishes');
    }
}
