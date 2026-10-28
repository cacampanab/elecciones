<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupervisorRole extends Migration
{
    public function up()
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        $viewer = $db->table('roles')->where('name', 'Visualizador / Supervisor')->get()->getRow();
        if ($viewer) {
            $db->table('roles')->where('id', $viewer->id)->update([
                'name' => 'Visualizador',
                'description' => 'Solo lectura y monitoreo',
                'updated_at' => $now,
            ]);
            $viewerId = (int)$viewer->id;
        } else {
            $existingViewer = $db->table('roles')->where('name', 'Visualizador')->get()->getRow();
            $viewerId = $existingViewer ? (int)$existingViewer->id : 3;
        }

        $supervisor = $db->table('roles')->where('name', 'Supervisor')->get()->getRow();
        if (!$supervisor) {
            $db->table('roles')->insert([
                'name' => 'Supervisor',
                'description' => 'Corrige y verifica actas',
                'created_at' => $now,
            ]);
            $supervisorId = (int)$db->insertID();
        } else {
            $supervisorId = (int)$supervisor->id;
        }

        $this->grant($supervisorId, [
            'results.read', 'results.create', 'results.update', 'results.verify',
            'minutes.read', 'minutes.upload',
            'audit.read',
            'reports.generate', 'reports.export_pdf', 'reports.export_excel',
            'dashboard.read', 'dashboard.statistics',
            'polling_stations.read', 'assignments.read',
            'candidates.read', 'dignities.read', 'elections.read', 'parties.read', 'lists.read',
            'provinces.read', 'cantons.read', 'parishes.read', 'precincts.read',
        ]);

        $this->grant($viewerId, [
            'results.read', 'minutes.read', 'dashboard.read',
            'reports.generate', 'reports.export_pdf', 'reports.export_excel', 'dashboard.statistics',
        ]);

        $operator = $db->table('roles')->where('name', 'Operador Electoral')->get()->getRow();
        if ($operator) {
            $this->grant((int)$operator->id, [
                'results.read', 'results.create', 'results.update',
                'minutes.read', 'minutes.upload',
                'polling_stations.read', 'assignments.read', 'dashboard.read',
            ]);
        }
    }

    private function grant(int $roleId, array $names): void
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');
        $permissions = $db->table('permissions')->whereIn('name', $names)->get()->getResult();
        foreach ($permissions as $permission) {
            $exists = $db->table('role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permission->id)
                ->countAllResults();
            if (!$exists) {
                $db->table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permission->id,
                    'created_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        $db = $this->db;
        $supervisor = $db->table('roles')->where('name', 'Supervisor')->get()->getRow();
        if ($supervisor) {
            $db->table('users')->where('role_id', $supervisor->id)->update(['role_id' => 3]);
            $db->table('roles')->where('id', $supervisor->id)->delete();
        }
        $viewer = $db->table('roles')->where('name', 'Visualizador')->get()->getRow();
        if ($viewer) {
            $db->table('roles')->where('id', $viewer->id)->update(['name' => 'Visualizador / Supervisor']);
        }
    }
}
