<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUploadedMinutesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'polling_station_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'election_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'filename' => ['type' => 'VARCHAR', 'constraint' => 255],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_type' => ['type' => 'VARCHAR', 'constraint' => 10],
            'file_size' => ['type' => 'INT', 'constraint' => 11],
            'version' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'is_current' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'uploaded_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('polling_station_id', 'polling_stations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('election_id', 'elections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('uploaded_minutes');
    }

    public function down()
    {
        $this->forge->dropTable('uploaded_minutes');
    }
}
