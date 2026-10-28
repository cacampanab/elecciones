<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDashboardIndexes extends Migration
{
    public function up()
    {
        $this->addIndex('election_results', 'idx_results_election_dignity_station', ['election_id', 'dignity_id', 'polling_station_id']);
        $this->addIndex('uploaded_minutes', 'idx_minutes_election_current_dignity', ['election_id', 'is_current', 'dignity_id']);
        $this->addIndex('polling_stations', 'idx_stations_election_province', ['election_id', 'name_province']);
    }

    public function down()
    {
        $this->addIndex('election_results', 'election_results_election_id_foreign', ['election_id']);
        $this->addIndex('uploaded_minutes', 'uploaded_minutes_election_id_foreign', ['election_id']);
        $this->addIndex('polling_stations', 'polling_stations_election_id_foreign', ['election_id']);

        $this->dropIndex('election_results', 'idx_results_election_dignity_station');
        $this->dropIndex('uploaded_minutes', 'idx_minutes_election_current_dignity');
        $this->dropIndex('polling_stations', 'idx_stations_election_province');
    }

    private function addIndex(string $table, string $name, array $columns): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        $columnList = '`' . implode('`, `', $columns) . '`';
        $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$columnList})");
    }

    private function dropIndex(string $table, string $name): void
    {
        if (!$this->indexExists($table, $name)) {
            return;
        }

        $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
    }

    private function indexExists(string $table, string $name): bool
    {
        return (bool)$this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name])->getRow();
    }
}
