<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Users
            ['name' => 'users.create', 'description' => 'Crear usuarios'],
            ['name' => 'users.read', 'description' => 'Ver usuarios'],
            ['name' => 'users.update', 'description' => 'Editar usuarios'],
            ['name' => 'users.delete', 'description' => 'Eliminar usuarios'],
            // Roles
            ['name' => 'roles.create', 'description' => 'Crear roles'],
            ['name' => 'roles.read', 'description' => 'Ver roles'],
            ['name' => 'roles.update', 'description' => 'Editar roles'],
            ['name' => 'roles.delete', 'description' => 'Eliminar roles'],
            // Elections
            ['name' => 'elections.create', 'description' => 'Crear elecciones'],
            ['name' => 'elections.read', 'description' => 'Ver elecciones'],
            ['name' => 'elections.update', 'description' => 'Editar elecciones'],
            ['name' => 'elections.delete', 'description' => 'Eliminar elecciones'],
            // Dignities
            ['name' => 'dignities.create', 'description' => 'Crear dignidades'],
            ['name' => 'dignities.read', 'description' => 'Ver dignidades'],
            ['name' => 'dignities.update', 'description' => 'Editar dignidades'],
            ['name' => 'dignities.delete', 'description' => 'Eliminar dignidades'],
            // Political Parties
            ['name' => 'parties.create', 'description' => 'Crear partidos'],
            ['name' => 'parties.read', 'description' => 'Ver partidos'],
            ['name' => 'parties.update', 'description' => 'Editar partidos'],
            ['name' => 'parties.delete', 'description' => 'Eliminar partidos'],
            // Candidate Lists
            ['name' => 'lists.create', 'description' => 'Crear listas'],
            ['name' => 'lists.read', 'description' => 'Ver listas'],
            ['name' => 'lists.update', 'description' => 'Editar listas'],
            ['name' => 'lists.delete', 'description' => 'Eliminar listas'],
            // Candidates
            ['name' => 'candidates.create', 'description' => 'Crear candidatos'],
            ['name' => 'candidates.read', 'description' => 'Ver candidatos'],
            ['name' => 'candidates.update', 'description' => 'Editar candidatos'],
            ['name' => 'candidates.delete', 'description' => 'Eliminar candidatos'],
            // Territorial Division
            ['name' => 'provinces.create', 'description' => 'Crear provincias'],
            ['name' => 'provinces.read', 'description' => 'Ver provincias'],
            ['name' => 'provinces.update', 'description' => 'Editar provincias'],
            ['name' => 'provinces.delete', 'description' => 'Eliminar provincias'],
            ['name' => 'cantons.create', 'description' => 'Crear cantones'],
            ['name' => 'cantons.read', 'description' => 'Ver cantones'],
            ['name' => 'cantons.update', 'description' => 'Editar cantones'],
            ['name' => 'cantons.delete', 'description' => 'Eliminar cantones'],
            ['name' => 'parishes.create', 'description' => 'Crear parroquias'],
            ['name' => 'parishes.read', 'description' => 'Ver parroquias'],
            ['name' => 'parishes.update', 'description' => 'Editar parroquias'],
            ['name' => 'parishes.delete', 'description' => 'Eliminar parroquias'],
            // Precincts & Polling Stations
            ['name' => 'precincts.create', 'description' => 'Crear recintos'],
            ['name' => 'precincts.read', 'description' => 'Ver recintos'],
            ['name' => 'precincts.update', 'description' => 'Editar recintos'],
            ['name' => 'precincts.delete', 'description' => 'Eliminar recintos'],
            ['name' => 'polling_stations.create', 'description' => 'Crear JRV'],
            ['name' => 'polling_stations.read', 'description' => 'Ver JRV'],
            ['name' => 'polling_stations.update', 'description' => 'Editar JRV'],
            ['name' => 'polling_stations.delete', 'description' => 'Eliminar JRV'],
            ['name' => 'polling_stations.import', 'description' => 'Importar JRV desde Excel'],
            // Assignments
            ['name' => 'assignments.create', 'description' => 'Asignar usuarios'],
            ['name' => 'assignments.read', 'description' => 'Ver asignaciones'],
            ['name' => 'assignments.update', 'description' => 'Editar asignaciones'],
            ['name' => 'assignments.delete', 'description' => 'Eliminar asignaciones'],
            // Results
            ['name' => 'results.create', 'description' => 'Registrar resultados'],
            ['name' => 'results.read', 'description' => 'Ver resultados'],
            ['name' => 'results.update', 'description' => 'Editar resultados'],
            ['name' => 'results.verify', 'description' => 'Verificar resultados'],
            ['name' => 'results.delete', 'description' => 'Eliminar resultados'],
            // Minutes
            ['name' => 'minutes.upload', 'description' => 'Subir actas'],
            ['name' => 'minutes.read', 'description' => 'Ver actas'],
            ['name' => 'minutes.delete', 'description' => 'Eliminar actas'],
            // Reports
            ['name' => 'reports.generate', 'description' => 'Generar reportes'],
            ['name' => 'reports.export_pdf', 'description' => 'Exportar PDF'],
            ['name' => 'reports.export_excel', 'description' => 'Exportar Excel'],
            // Dashboard
            ['name' => 'dashboard.read', 'description' => 'Ver dashboard'],
            ['name' => 'dashboard.statistics', 'description' => 'Ver estadísticas'],
            // Audit
            ['name' => 'audit.read', 'description' => 'Ver auditoría'],
            // Configuration
            ['name' => 'config.read', 'description' => 'Ver configuración'],
            ['name' => 'config.update', 'description' => 'Actualizar configuración'],
        ];

        $this->db->table('permissions')->insertBatch(array_map(function($p) {
            $p['created_at'] = date('Y-m-d H:i:s');
            return $p;
        }, $permissions));

        $db = \Config\Database::connect();
        $adminRole = $db->table('roles')->where('name', 'Administrador General')->get()->getRow();
        $operatorRole = $db->table('roles')->where('name', 'Operador Electoral')->get()->getRow();
        $viewerRole = $db->table('roles')->where('name', 'Visualizador')->get()->getRow();
        $supervisorRole = $db->table('roles')->where('name', 'Supervisor')->get()->getRow();

        $allPermissions = $db->table('permissions')->get()->getResult();
        $adminPerms = [];
        foreach ($allPermissions as $p) {
            $adminPerms[] = ['role_id' => $adminRole->id, 'permission_id' => $p->id, 'created_at' => date('Y-m-d H:i:s')];
        }
        $this->db->table('role_permissions')->insertBatch($adminPerms);

        $operatorPerms = $db->table('permissions')
            ->whereIn('name', [
                'polling_stations.read', 'assignments.read', 'results.create', 'results.read',
                'results.update', 'minutes.upload', 'minutes.read', 'dashboard.read'
            ])->get()->getResult();
        $opPerms = [];
        foreach ($operatorPerms as $p) {
            $opPerms[] = ['role_id' => $operatorRole->id, 'permission_id' => $p->id, 'created_at' => date('Y-m-d H:i:s')];
        }
        $this->db->table('role_permissions')->insertBatch($opPerms);

        $viewerPerms = $db->table('permissions')
            ->whereIn('name', [
                'dashboard.read', 'dashboard.statistics', 'results.read', 'minutes.read', 'polling_stations.read',
                'assignments.read', 'reports.generate', 'reports.export_pdf', 'reports.export_excel',
                'candidates.read', 'parties.read', 'elections.read', 'dignities.read',
                'provinces.read', 'cantons.read', 'parishes.read', 'precincts.read'
            ])->get()->getResult();
        $vPerms = [];
        foreach ($viewerPerms as $p) {
            $vPerms[] = ['role_id' => $viewerRole->id, 'permission_id' => $p->id, 'created_at' => date('Y-m-d H:i:s')];
        }
        $this->db->table('role_permissions')->insertBatch($vPerms);

        $supervisorPerms = $db->table('permissions')
            ->whereIn('name', [
                'results.read', 'results.create', 'results.update', 'results.verify',
                'minutes.read', 'minutes.upload', 'audit.read',
                'reports.generate', 'reports.export_pdf', 'reports.export_excel',
                'dashboard.read', 'dashboard.statistics',
                'polling_stations.read', 'assignments.read',
                'candidates.read', 'dignities.read', 'elections.read', 'parties.read', 'lists.read',
                'provinces.read', 'cantons.read', 'parishes.read', 'precincts.read'
            ])->get()->getResult();
        $sPerms = [];
        foreach ($supervisorPerms as $p) {
            $sPerms[] = ['role_id' => $supervisorRole->id, 'permission_id' => $p->id, 'created_at' => date('Y-m-d H:i:s')];
        }
        $this->db->table('role_permissions')->insertBatch($sPerms);
    }
}
