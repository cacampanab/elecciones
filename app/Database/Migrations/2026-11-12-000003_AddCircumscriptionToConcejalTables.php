<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCircumscriptionToConcejalTables extends Migration
{
    public function up()
    {
        // Escaños por circunscripción (NULL = configuración por cantón, como antes).
        $this->db->query('ALTER TABLE election_seats ADD COLUMN circumscription_id INT UNSIGNED NULL AFTER canton_id');
        // La nueva clave conserva election_id al inicio, por lo que puede
        // sostener la llave foránea mientras se elimina la clave anterior.
        $this->db->query('ALTER TABLE election_seats ADD UNIQUE KEY uq_election_seats_scope (election_id, canton_id, dignity_id, circumscription_id)');
        $this->db->query('ALTER TABLE election_seats DROP INDEX election_id_canton_id_dignity_id');
        $this->db->query('ALTER TABLE election_seats ADD CONSTRAINT fk_election_seats_circumscription FOREIGN KEY (circumscription_id) REFERENCES election_circumscriptions(id) ON DELETE CASCADE ON UPDATE CASCADE');

        // Listas por circunscripción. El mismo partido puede tener una lista en cada circunscripción.
        $this->db->query('ALTER TABLE candidate_lists ADD COLUMN circumscription_id INT UNSIGNED NULL AFTER canton_id');
        $this->db->query('ALTER TABLE candidate_lists ADD UNIQUE KEY uq_candidate_lists_scope (election_id, canton_id, dignity_id, circumscription_id, political_party_id)');
        $this->db->query('ALTER TABLE candidate_lists DROP INDEX uq_candidate_lists_context');
        $this->db->query('ALTER TABLE candidate_lists ADD CONSTRAINT fk_candidate_lists_circumscription FOREIGN KEY (circumscription_id) REFERENCES election_circumscriptions(id) ON DELETE SET NULL ON UPDATE CASCADE');

        // Resultados de listas por circunscripción.
        $this->db->query('ALTER TABLE election_list_results ADD COLUMN circumscription_id INT UNSIGNED NULL AFTER canton_id');
        $this->db->query('ALTER TABLE election_list_results ADD CONSTRAINT fk_election_list_results_circumscription FOREIGN KEY (circumscription_id) REFERENCES election_circumscriptions(id) ON DELETE SET NULL ON UPDATE CASCADE');

        // Votos manuales de listas (desempate) por circunscripción.
        $this->db->query('ALTER TABLE election_list_votes ADD COLUMN circumscription_id INT UNSIGNED NULL AFTER canton_id');
        $this->db->query('ALTER TABLE election_list_votes ADD CONSTRAINT fk_election_list_votes_circumscription FOREIGN KEY (circumscription_id) REFERENCES election_circumscriptions(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE election_list_votes DROP FOREIGN KEY fk_election_list_votes_circumscription');
        $this->db->query('ALTER TABLE election_list_votes DROP COLUMN circumscription_id');

        $this->db->query('ALTER TABLE election_list_results DROP FOREIGN KEY fk_election_list_results_circumscription');
        $this->db->query('ALTER TABLE election_list_results DROP COLUMN circumscription_id');

        $this->db->query('ALTER TABLE candidate_lists DROP FOREIGN KEY fk_candidate_lists_circumscription');
        $this->db->query('ALTER TABLE candidate_lists ADD UNIQUE KEY uq_candidate_lists_context (election_id, canton_id, dignity_id, political_party_id)');
        $this->db->query('ALTER TABLE candidate_lists DROP INDEX uq_candidate_lists_scope');
        $this->db->query('ALTER TABLE candidate_lists DROP COLUMN circumscription_id');

        $this->db->query('ALTER TABLE election_seats DROP FOREIGN KEY fk_election_seats_circumscription');
        $this->db->query('ALTER TABLE election_seats ADD UNIQUE KEY election_id_canton_id_dignity_id (election_id, canton_id, dignity_id)');
        $this->db->query('ALTER TABLE election_seats DROP INDEX uq_election_seats_scope');
        $this->db->query('ALTER TABLE election_seats DROP COLUMN circumscription_id');
    }
}
