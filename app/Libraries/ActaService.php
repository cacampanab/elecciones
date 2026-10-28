<?php

namespace App\Libraries;

class ActaService
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function enabledDignityIds(int $electionId): array
    {
        $rows = $this->db->table('election_dignities')
            ->select('dignity_id')
            ->where('election_id', $electionId)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        if ($rows) {
            return array_map('intval', array_column($rows, 'dignity_id'));
        }

        $fallback = $this->db->table('candidates')
            ->select('DISTINCT dignity_id', false)
            ->where('election_id', $electionId)
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($fallback, 'dignity_id'));
    }

    public function currentMinute(int $pollingStationId, int $electionId, int $dignityId)
    {
        return $this->db->table('uploaded_minutes')
            ->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->where('dignity_id', $dignityId)
            ->where('is_current', 1)
            ->get()
            ->getRow();
    }

    public function consistency(int $pollingStationId, int $electionId, int $dignityId): array
    {
        $rows = $this->db->table('election_results er')
            ->select('er.*, c.first_name, c.last_name')
            ->join('candidates c', 'c.id = er.candidate_id', 'left')
            ->where('er.polling_station_id', $pollingStationId)
            ->where('er.election_id', $electionId)
            ->where('er.dignity_id', $dignityId)
            ->get()
            ->getResultArray();

        if (!$rows) {
            return [
                'status' => 'sin_datos',
                'detail' => null,
                'errors' => [],
                'warnings' => [],
                'suma' => 0,
                'sufragantes' => 0,
                'electores_acta' => 0,
                'electores_distributivo' => 0,
            ];
        }

        $sumVotes = array_sum(array_map('intval', array_column($rows, 'votes')));
        $blank = max(array_map('intval', array_column($rows, 'blank_votes')));
        $null = max(array_map('intval', array_column($rows, 'null_votes')));
        $sufragantes = max(array_map('intval', array_column($rows, 'total_voters')));
        $electoresActa = max(array_map('intval', array_column($rows, 'registered_voters')));
        $suma = $sumVotes + $blank + $null;

        $station = $this->db->table('polling_stations')
            ->select('numero_electores')
            ->where('id', $pollingStationId)
            ->get()
            ->getRow();
        $electoresDistributivo = $station ? (int)$station->numero_electores : 0;

        $errors = [];
        $warnings = [];

        if ($suma !== $sufragantes) {
            $diff = $suma - $sufragantes;
            $sign = $diff > 0 ? '+' : '';
            $errors[] = "Votos + blancos + nulos = {$suma}, sufragantes = {$sufragantes} (diferencia {$sign}{$diff})";
        }

        if ($sufragantes > $electoresActa) {
            $errors[] = "Sufragantes ({$sufragantes}) supera electores del acta ({$electoresActa})";
        }

        if ($electoresActa !== $electoresDistributivo) {
            $warnings[] = "Electores del acta ({$electoresActa}) no coincide con el distributivo ({$electoresDistributivo})";
        }

        foreach ($rows as $row) {
            $votes = (int)$row['votes'];
            if ($votes > $sufragantes) {
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                $errors[] = "Candidato {$name}: {$votes} votos supera sufragantes ({$sufragantes})";
            }
        }

        $uniformFields = ['blank_votes', 'null_votes', 'total_voters', 'registered_voters'];
        foreach ($uniformFields as $field) {
            $values = array_unique(array_map('intval', array_column($rows, $field)));
            if (count($values) > 1) {
                $errors[] = "El campo {$field} no es uniforme entre candidatos";
            }
        }

        $crossDignity = $this->db->table('election_results')
            ->select('dignity_id, MAX(total_voters) AS total_voters')
            ->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->groupBy('dignity_id')
            ->get()
            ->getResultArray();
        if (count($crossDignity) > 1) {
            $totals = array_map('intval', array_column($crossDignity, 'total_voters'));
            if (max($totals) !== min($totals)) {
                $warnings[] = 'Los sufragantes no son uniformes entre dignidades (' . implode(', ', $totals) . ')';
            }
        }

        $detail = array_merge($errors, $warnings);

        return [
            'status' => $errors ? 'error' : 'ok',
            'detail' => $detail ? implode(' | ', $detail) : null,
            'errors' => $errors,
            'warnings' => $warnings,
            'suma' => $suma,
            'sufragantes' => $sufragantes,
            'electores_acta' => $electoresActa,
            'electores_distributivo' => $electoresDistributivo,
        ];
    }

    public function refreshMinute(int $minuteId): ?array
    {
        $minute = $this->db->table('uploaded_minutes')->where('id', $minuteId)->get()->getRow();
        if (!$minute) {
            return null;
        }

        $consistency = $this->consistency((int)$minute->polling_station_id, (int)$minute->election_id, (int)$minute->dignity_id);

        $status = $minute->status;
        if ($status !== 'verificada') {
            $status = $consistency['status'] === 'error' ? 'con_novedad' : 'registrada';
        }

        $this->db->table('uploaded_minutes')->where('id', $minuteId)->update([
            'status' => $status,
            'consistency_status' => $consistency['status'],
            'consistency_detail' => $consistency['detail'],
        ]);

        return $consistency;
    }

    public function snapshot(int $pollingStationId, int $electionId, int $dignityId, int $userId, ?string $reason = null): int
    {
        $count = $this->db->table('result_versions')
            ->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->where('dignity_id', $dignityId)
            ->countAllResults();

        $rows = $this->db->table('election_results')
            ->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->where('dignity_id', $dignityId)
            ->orderBy('candidate_id', 'ASC')
            ->get()
            ->getResultArray();

        $minute = $this->currentMinute($pollingStationId, $electionId, $dignityId);

        $data = [
            'results' => $rows,
            'consistency' => $this->consistency($pollingStationId, $electionId, $dignityId),
            'minute' => $minute ? [
                'id' => (int)$minute->id,
                'version' => (int)$minute->version,
                'filename' => $minute->filename,
                'status' => $minute->status,
            ] : null,
        ];

        $this->db->table('result_versions')->insert([
            'polling_station_id' => $pollingStationId,
            'election_id' => $electionId,
            'dignity_id' => $dignityId,
            'version' => $count + 1,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'reason' => $reason,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $count + 1;
    }

    public function updateStationStatus(int $pollingStationId, int $electionId): void
    {
        $enabled = $this->enabledDignityIds($electionId);
        if (!$enabled) {
            return;
        }

        $minutes = $this->db->table('uploaded_minutes')
            ->select('dignity_id')
            ->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->where('is_current', 1)
            ->get()
            ->getResultArray();
        $minuteDignities = array_map('intval', array_column($minutes, 'dignity_id'));

        $results = $this->db->table('election_results')
            ->select('dignity_id')
            ->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->groupBy('dignity_id')
            ->get()
            ->getResultArray();
        $resultDignities = array_map('intval', array_column($results, 'dignity_id'));

        $complete = count(array_diff($enabled, $minuteDignities)) === 0
            && count(array_diff($enabled, $resultDignities)) === 0;

        if ($complete) {
            $status = 'reportada';
        } elseif ($minuteDignities || $resultDignities) {
            $status = 'en_proceso';
        } else {
            return;
        }

        $this->db->table('polling_stations')->where('id', $pollingStationId)->update(['status' => $status]);
    }
}
