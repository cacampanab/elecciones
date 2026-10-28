<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run()
    {
        $provinces = [
            ['code' => '01', 'name' => 'Azuay', 'capital' => 'Cuenca', 'region' => 'Sierra'],
            ['code' => '02', 'name' => 'Bolívar', 'capital' => 'Guaranda', 'region' => 'Sierra'],
            ['code' => '03', 'name' => 'Cañar', 'capital' => 'Azogues', 'region' => 'Sierra'],
            ['code' => '04', 'name' => 'Carchi', 'capital' => 'Tulcán', 'region' => 'Sierra'],
            ['code' => '05', 'name' => 'Cotopaxi', 'capital' => 'Latacunga', 'region' => 'Sierra'],
            ['code' => '06', 'name' => 'Chimborazo', 'capital' => 'Riobamba', 'region' => 'Sierra'],
            ['code' => '07', 'name' => 'El Oro', 'capital' => 'Machala', 'region' => 'Costa'],
            ['code' => '08', 'name' => 'Esmeraldas', 'capital' => 'Esmeraldas', 'region' => 'Costa'],
            ['code' => '09', 'name' => 'Guayas', 'capital' => 'Guayaquil', 'region' => 'Costa'],
            ['code' => '10', 'name' => 'Imbabura', 'capital' => 'Ibarra', 'region' => 'Sierra'],
            ['code' => '11', 'name' => 'Loja', 'capital' => 'Loja', 'region' => 'Sierra'],
            ['code' => '12', 'name' => 'Los Ríos', 'capital' => 'Babahoyo', 'region' => 'Costa'],
            ['code' => '13', 'name' => 'Manabí', 'capital' => 'Portoviejo', 'region' => 'Costa'],
            ['code' => '14', 'name' => 'Morona Santiago', 'capital' => 'Macas', 'region' => 'Amazonía'],
            ['code' => '15', 'name' => 'Napo', 'capital' => 'Tena', 'region' => 'Amazonía'],
            ['code' => '16', 'name' => 'Pastaza', 'capital' => 'Puyo', 'region' => 'Amazonía'],
            ['code' => '17', 'name' => 'Pichincha', 'capital' => 'Quito', 'region' => 'Sierra'],
            ['code' => '18', 'name' => 'Tungurahua', 'capital' => 'Ambato', 'region' => 'Sierra'],
            ['code' => '19', 'name' => 'Zamora Chinchipe', 'capital' => 'Zamora', 'region' => 'Amazonía'],
            ['code' => '20', 'name' => 'Galápagos', 'capital' => 'Puerto Baquerizo Moreno', 'region' => 'Insular'],
            ['code' => '21', 'name' => 'Sucumbíos', 'capital' => 'Nueva Loja', 'region' => 'Amazonía'],
            ['code' => '22', 'name' => 'Orellana', 'capital' => 'Francisco de Orellana', 'region' => 'Amazonía'],
            ['code' => '23', 'name' => 'Santo Domingo de los Tsáchilas', 'capital' => 'Santo Domingo', 'region' => 'Sierra'],
            ['code' => '24', 'name' => 'Santa Elena', 'capital' => 'Santa Elena', 'region' => 'Costa'],
            ['code' => '90', 'name' => 'Zonas no Delimitadas', 'capital' => 'N/A', 'region' => 'N/A'],
        ];

        $this->db->table('provinces')->insertBatch(array_map(function($p) {
            $p['created_at'] = date('Y-m-d H:i:s');
            return $p;
        }, $provinces));
    }
}
