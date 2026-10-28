<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateElectionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'election_date' => ['type' => 'DATE'],
            'election_type' => ['type' => 'ENUM', 'constraint' => ['nacional', 'provincial', 'cantonal', 'parroquial'], 'default' => 'nacional'],
            'status' => ['type' => 'ENUM', 'constraint' => ['pendiente', 'en_curso', 'finalizada', 'anulada'], 'default' => 'pendiente'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('elections');
    }

    public function down()
    {
        $this->forge->dropTable('elections');
    }
}
