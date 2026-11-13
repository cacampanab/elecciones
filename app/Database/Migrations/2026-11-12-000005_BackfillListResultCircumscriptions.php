<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillListResultCircumscriptions extends Migration
{
    public function up()
    {
        // Completa la circunscripción de los resultados de listas ya
        // registrados, usando la parroquia de la JRV. Solo aplica cuando la
        // dignidad del resultado coincide con la dignidad de la circunscripción.
        $this->db->query(
            "UPDATE election_list_results elr
             JOIN polling_stations ps ON ps.id = elr.polling_station_id
             JOIN precincts pr ON pr.id = ps.precinct_id
             JOIN election_circumscription_parishes ecp
                  ON ecp.election_id = elr.election_id
                 AND ecp.parish_id = pr.parish_id
             JOIN election_circumscriptions ec
                  ON ec.id = ecp.circumscription_id
                 AND ec.dignity_id = elr.dignity_id
             SET elr.circumscription_id = ec.id
             WHERE elr.circumscription_id IS NULL"
        );
    }

    public function down()
    {
        // No se revierte: los resultados conservan la circunscripción calculada.
    }
}
