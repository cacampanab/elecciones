<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSessionToRefreshTokens extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE refresh_tokens ADD COLUMN session_id BIGINT UNSIGNED NULL AFTER user_id');
        $this->db->query('ALTER TABLE refresh_tokens ADD KEY idx_refresh_tokens_session (session_id)');
        $this->db->query('ALTER TABLE refresh_tokens ADD CONSTRAINT fk_refresh_tokens_session FOREIGN KEY (session_id) REFERENCES user_sessions(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE refresh_tokens DROP FOREIGN KEY fk_refresh_tokens_session');
        $this->db->query('ALTER TABLE refresh_tokens DROP INDEX idx_refresh_tokens_session');
        $this->db->query('ALTER TABLE refresh_tokens DROP COLUMN session_id');
    }
}
