<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDignityAndStatusToUploadedMinutes extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE uploaded_minutes
            ADD COLUMN dignity_id INT UNSIGNED NULL AFTER election_id,
            ADD COLUMN status ENUM('registrada','con_novedad','verificada') NOT NULL DEFAULT 'registrada' AFTER is_current,
            ADD COLUMN notes VARCHAR(255) NULL AFTER status,
            ADD COLUMN verified_by INT UNSIGNED NULL AFTER notes,
            ADD COLUMN verified_at DATETIME NULL AFTER verified_by,
            ADD COLUMN consistency_status ENUM('ok','error','sin_datos') NOT NULL DEFAULT 'sin_datos' AFTER verified_at,
            ADD COLUMN consistency_detail TEXT NULL AFTER consistency_status");

        $this->db->query("UPDATE uploaded_minutes um
            JOIN (
                SELECT polling_station_id, election_id, MIN(dignity_id) AS dignity_id
                FROM election_results
                GROUP BY polling_station_id, election_id
            ) er ON er.polling_station_id = um.polling_station_id AND er.election_id = um.election_id
            SET um.dignity_id = er.dignity_id
            WHERE um.dignity_id IS NULL");

        $this->db->query("UPDATE uploaded_minutes
            SET dignity_id = (SELECT id FROM dignities WHERE slug = 'prefecto' LIMIT 1)
            WHERE dignity_id IS NULL");

        $this->db->query("ALTER TABLE uploaded_minutes MODIFY dignity_id INT UNSIGNED NOT NULL");

        $this->db->query("UPDATE uploaded_minutes um
            LEFT JOIN (
                SELECT polling_station_id, election_id, dignity_id,
                       SUM(votes) + MAX(blank_votes) + MAX(null_votes) AS suma,
                       MAX(total_voters) AS sufragantes
                FROM election_results
                GROUP BY polling_station_id, election_id, dignity_id
            ) t ON t.polling_station_id = um.polling_station_id
                AND t.election_id = um.election_id
                AND t.dignity_id = um.dignity_id
            SET um.consistency_status = CASE
                    WHEN t.suma IS NULL THEN 'sin_datos'
                    WHEN t.suma = t.sufragantes THEN 'ok'
                    ELSE 'error'
                END,
                um.consistency_detail = CASE
                    WHEN t.suma IS NULL THEN NULL
                    WHEN t.suma = t.sufragantes THEN NULL
                    ELSE CONCAT('Suma ', t.suma, ' vs sufragantes ', t.sufragantes, ' (diferencia ', (t.suma - t.sufragantes), ')')
                END");

        $this->db->query("UPDATE uploaded_minutes SET status = 'con_novedad' WHERE consistency_status = 'error'");

        $this->db->query("ALTER TABLE uploaded_minutes
            ADD INDEX idx_minutes_station_dignity (polling_station_id, election_id, dignity_id, is_current),
            ADD CONSTRAINT fk_minutes_dignity FOREIGN KEY (dignity_id) REFERENCES dignities(id) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT fk_minutes_verified_by FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE uploaded_minutes
            DROP FOREIGN KEY fk_minutes_dignity,
            DROP FOREIGN KEY fk_minutes_verified_by,
            DROP INDEX idx_minutes_station_dignity,
            DROP COLUMN dignity_id,
            DROP COLUMN status,
            DROP COLUMN notes,
            DROP COLUMN verified_by,
            DROP COLUMN verified_at,
            DROP COLUMN consistency_status,
            DROP COLUMN consistency_detail");
    }
}
