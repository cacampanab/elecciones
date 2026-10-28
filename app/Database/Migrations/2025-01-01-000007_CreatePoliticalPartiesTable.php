<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePoliticalPartiesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'acronym' => ['type' => 'VARCHAR', 'constraint' => 20],
            'logo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'list_number' => ['type' => 'INT', 'constraint' => 11],
            'color' => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('acronym');
        $this->forge->createTable('political_parties');
    }

    public function down()
    {
        $this->forge->dropTable('political_parties');
    }
}
