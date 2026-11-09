<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillAlcaldeCantonFromResults extends Migration
{
    public function up()
    {
        // Completa el cantón de alcaldes antiguos cuando todos sus resultados
        // pertenecen a un único cantón. Los casos ambiguos se dejan sin tocar.
        $this->db->query(
            "UPDATE candidates c
             JOIN (
                 SELECT er.candidate_id, MIN(ca.id) AS canton_id
                 FROM election_results er
                 JOIN polling_stations ps ON ps.id = er.polling_station_id
                 JOIN precincts pr ON pr.id = ps.precinct_id
                 JOIN parishes pa ON pa.id = pr.parish_id
                 JOIN cantons ca ON ca.id = pa.canton_id
                 JOIN dignities d ON d.id = er.dignity_id
                 WHERE d.slug = 'alcalde'
                 GROUP BY er.candidate_id
                 HAVING COUNT(DISTINCT ca.id) = 1
             ) t ON t.candidate_id = c.id
             JOIN dignities d2 ON d2.id = c.dignity_id
             SET c.canton_id = t.canton_id
             WHERE c.canton_id IS NULL
               AND d2.slug = 'alcalde'"
        );
    }

    public function down()
    {
        // No se revierte la asignación automática de cantón.
    }
}
