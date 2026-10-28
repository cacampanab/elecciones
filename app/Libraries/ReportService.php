<?php

namespace App\Libraries;

class ReportService
{
    private const COLORS = [
        '#1a237e', '#0d6efd', '#198754', '#dc3545', '#fd7e14',
        '#6f42c1', '#0dcaf0', '#20c997', '#ffc107', '#6c757d',
    ];

    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function build(int $electionId, int $dignityId, string $generatedBy = ''): array
    {
        $election = $this->db->table('elections')->where('id', $electionId)->get()->getRow();
        $dignity = $this->db->table('dignities')->where('id', $dignityId)->get()->getRow();

        if (!$election || !$dignity) {
            throw new \RuntimeException('La elección o la dignidad no existen.');
        }

        $hasData = $this->db->table('candidates')
                ->where('election_id', $electionId)
                ->where('dignity_id', $dignityId)
                ->countAllResults() > 0
            || $this->db->table('election_results')
                ->where('election_id', $electionId)
                ->where('dignity_id', $dignityId)
                ->countAllResults() > 0;

        if (!$hasData) {
            throw new \RuntimeException('No hay candidatos ni resultados para la elección y dignidad seleccionadas.');
        }

        $totalJrvs = (int)$this->db->table('polling_stations')
            ->where('election_id', $electionId)
            ->countAllResults();

        $candidateRows = $this->db->query(
            "SELECT c.id, c.first_name, c.last_name, c.cedula, c.is_active,
                    pp.id AS party_id, pp.name AS party_name,
                    pp.acronym AS party_acronym, pp.list_number, pp.color
             FROM candidates c
             JOIN political_parties pp ON pp.id = c.political_party_id
             WHERE c.election_id = ? AND c.dignity_id = ?
             ORDER BY pp.list_number ASC, c.last_name ASC, c.first_name ASC",
            [$electionId, $dignityId]
        )->getResult();

        $candidates = [];
        $candidateIndex = [];
        $colorIndex = 0;
        foreach ($candidateRows as $candidate) {
            $candidate->total_votes = 0;
            $candidate->color = $this->safeColor($candidate->color ?? null, $colorIndex++);
            $candidate->full_name = $this->fullName($candidate->first_name ?? '', $candidate->last_name ?? '');
            $candidates[] = $candidate;
            $candidateIndex[(int)$candidate->id] = $candidate;
        }

        $actaRows = $this->db->query(
            "SELECT um.id AS acta_id, um.status, um.consistency_status,
                    um.consistency_detail, um.notes, um.version,
                    um.created_at, um.updated_at, um.verified_at,
                    ps.id AS station_id, ps.code_province, ps.name_province,
                    ps.code_canton, ps.name_canton, ps.code_parish, ps.name_parish,
                    ps.code_precinct, ps.name_precinct, ps.numero_junta, ps.genero,
                    ps.numero_electores,
                    u.first_name AS uploader_first_name, u.last_name AS uploader_last_name,
                    er.candidate_id, er.votes, er.blank_votes, er.null_votes,
                    er.total_voters, er.registered_voters, er.is_verified
             FROM uploaded_minutes um
             JOIN polling_stations ps ON ps.id = um.polling_station_id
             LEFT JOIN users u ON u.id = um.uploaded_by
             LEFT JOIN election_results er
                    ON er.polling_station_id = um.polling_station_id
                   AND er.election_id = um.election_id
                   AND er.dignity_id = um.dignity_id
             WHERE um.election_id = ? AND um.dignity_id = ? AND um.is_current = 1
             ORDER BY ps.name_province, ps.name_canton, ps.name_parish,
                      ps.name_precinct, ps.numero_junta",
            [$electionId, $dignityId]
        )->getResult();

        $actasById = [];
        foreach ($actaRows as $row) {
            $actaId = (int)$row->acta_id;
            if (!isset($actasById[$actaId])) {
                $actasById[$actaId] = (object)[
                    'id' => $actaId,
                    'station_id' => (int)$row->station_id,
                    'code_province' => $row->code_province,
                    'name_province' => $row->name_province,
                    'code_canton' => $row->code_canton,
                    'name_canton' => $row->name_canton,
                    'code_parish' => $row->code_parish,
                    'name_parish' => $row->name_parish,
                    'code_precinct' => $row->code_precinct,
                    'name_precinct' => $row->name_precinct,
                    'numero_junta' => $row->numero_junta,
                    'genero' => $row->genero,
                    'numero_electores' => (int)$row->numero_electores,
                    'status' => $row->status,
                    'consistency_status' => $row->consistency_status,
                    'consistency_detail' => $row->consistency_detail,
                    'notes' => $row->notes,
                    'version' => (int)$row->version,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'verified_at' => $row->verified_at,
                    'uploaded_by' => $this->fullName($row->uploader_first_name ?? '', $row->uploader_last_name ?? ''),
                    'candidate_votes' => [],
                    'valid_votes' => 0,
                    'blank_votes' => 0,
                    'null_votes' => 0,
                    'total_voters' => 0,
                    'registered_voters' => 0,
                    'is_verified' => 0,
                    'has_results' => false,
                ];
            }

            $acta = $actasById[$actaId];
            if ($row->candidate_id !== null) {
                $candidateId = (int)$row->candidate_id;
                $votes = (int)$row->votes;

                $acta->candidate_votes[$candidateId] = ($acta->candidate_votes[$candidateId] ?? 0) + $votes;
                $acta->valid_votes += $votes;
                $acta->blank_votes = max($acta->blank_votes, (int)$row->blank_votes);
                $acta->null_votes = max($acta->null_votes, (int)$row->null_votes);
                $acta->total_voters = max($acta->total_voters, (int)$row->total_voters);
                $acta->registered_voters = max($acta->registered_voters, (int)$row->registered_voters);
                $acta->is_verified = max($acta->is_verified, (int)$row->is_verified);
                $acta->has_results = true;
            }
        }

        $actas = array_values($actasById);
        foreach ($actas as $acta) {
            $acta->registered_effective = $acta->registered_voters > 0
                ? $acta->registered_voters
                : $acta->numero_electores;
            $acta->total_cast = $acta->valid_votes + $acta->blank_votes + $acta->null_votes;
            $acta->difference = $acta->total_cast - $acta->total_voters;
            $acta->inconsistent = $acta->consistency_status === 'error' || $acta->status === 'con_novedad';
        }

        $resultActas = array_values(array_filter($actas, static fn($acta) => $acta->has_results));

        $totalValid = 0;
        $totalBlank = 0;
        $totalNull = 0;
        $totalVoters = 0;
        $totalRegistered = 0;
        $actasWithoutElectors = 0;

        foreach ($resultActas as $acta) {
            $totalValid += $acta->valid_votes;
            $totalBlank += $acta->blank_votes;
            $totalNull += $acta->null_votes;
            $totalVoters += $acta->total_voters;
            $totalRegistered += $acta->registered_effective;

            if ($acta->registered_effective <= 0) {
                $actasWithoutElectors++;
            }

            foreach ($acta->candidate_votes as $candidateId => $votes) {
                if (isset($candidateIndex[$candidateId])) {
                    $candidateIndex[$candidateId]->total_votes += $votes;
                }
            }
        }

        $results = array_values($candidateIndex);
        usort($results, static function ($a, $b) {
            if ((int)$a->total_votes !== (int)$b->total_votes) {
                return (int)$b->total_votes <=> (int)$a->total_votes;
            }

            $listA = (string)($a->list_number ?? '');
            $listB = (string)($b->list_number ?? '');
            if ($listA !== $listB) {
                return strcmp($listA, $listB);
            }

            return strcmp((string)$a->last_name, (string)$b->last_name);
        });

        $totalCast = $totalValid + $totalBlank + $totalNull;
        $maxVotes = 0;
        foreach ($results as $result) {
            $result->pct_valid = $totalValid > 0 ? round(($result->total_votes / $totalValid) * 100, 2) : 0;
            $result->pct_voters = $totalVoters > 0 ? round(($result->total_votes / $totalVoters) * 100, 2) : 0;
            $result->pct_cast = $totalCast > 0 ? round(($result->total_votes / $totalCast) * 100, 2) : 0;
            $maxVotes = max($maxVotes, (int)$result->total_votes);
        }

        $uploadedActas = count($actas);
        $actasWithResults = count($resultActas);
        $actasWithoutResults = max(0, $uploadedActas - $actasWithResults);
        $verifiedActas = 0;
        $noveltyActas = 0;
        $inconsistentActas = 0;
        $errorActas = 0;
        $okActas = 0;
        $sinDatosActas = 0;
        $jrvsWithActa = [];
        $inconsistentList = [];

        foreach ($actas as $acta) {
            if ($acta->status === 'verificada') {
                $verifiedActas++;
            }
            if ($acta->status === 'con_novedad') {
                $noveltyActas++;
            }
            if ($acta->inconsistent) {
                $inconsistentActas++;
                $inconsistentList[] = $acta;
            }
            if ($acta->consistency_status === 'error') {
                $errorActas++;
            }
            if ($acta->consistency_status === 'ok') {
                $okActas++;
            }
            if ($acta->consistency_status === 'sin_datos') {
                $sinDatosActas++;
            }

            $jrvsWithActa[(int)$acta->station_id] = true;
        }

        $withActaJrvs = count($jrvsWithActa);
        $withoutActaJrvs = max(0, $totalJrvs - $withActaJrvs);

        $resultsWithoutActaRow = $this->db->query(
            "SELECT COUNT(DISTINCT er.polling_station_id) AS total
             FROM election_results er
             WHERE er.election_id = ? AND er.dignity_id = ?
               AND NOT EXISTS (
                   SELECT 1 FROM uploaded_minutes um
                   WHERE um.polling_station_id = er.polling_station_id
                     AND um.election_id = er.election_id
                     AND um.dignity_id = er.dignity_id
                     AND um.is_current = 1
               )",
            [$electionId, $dignityId]
        )->getRow();
        $resultsWithoutActa = (int)($resultsWithoutActaRow->total ?? 0);

        $coverage = $totalJrvs > 0 ? round(($uploadedActas / $totalJrvs) * 100, 2) : 0;
        $participation = $totalRegistered > 0 ? round(($totalVoters / $totalRegistered) * 100, 2) : 0;
        $abstention = $totalRegistered - $totalVoters;
        $abstentionPct = $totalRegistered > 0 ? round(($abstention / $totalRegistered) * 100, 2) : 0;
        $difference = $totalCast - $totalVoters;

        $provinces = $this->buildProvinces($electionId, $actas);
        $inconsistencySummary = $this->buildInconsistencySummary($provinces, $inconsistentList);
        $notes = $this->buildNotes([
            'coverage' => $coverage,
            'verified' => $verifiedActas,
            'uploaded' => $uploadedActas,
            'expected' => $totalJrvs,
            'inconsistent' => $inconsistentActas,
            'results_without_acta' => $resultsWithoutActa,
            'actas_without_electors' => $actasWithoutElectors,
            'difference' => $difference,
            'abstention' => $abstention,
            'duplicated_actas' => max(0, $uploadedActas - $withActaJrvs),
        ]);

        return [
            'election' => $election,
            'dignity' => $dignity,
            'generated_at' => date('d/m/Y H:i:s'),
            'generated_by' => $generatedBy !== '' ? $generatedBy : 'N/D',
            'preliminary' => $coverage < 100
                || $uploadedActas > $totalJrvs
                || $verifiedActas < $uploadedActas
                || $inconsistentActas > 0
                || $resultsWithoutActa > 0,
            'total_jrvs' => $totalJrvs,
            'expected_actas' => $totalJrvs,
            'uploaded_actas' => $uploadedActas,
            'verified_actas' => $verifiedActas,
            'novelty_actas' => $noveltyActas,
            'inconsistent_actas' => $inconsistentActas,
            'error_actas' => $errorActas,
            'ok_actas' => $okActas,
            'sin_datos_actas' => $sinDatosActas,
            'actas_with_results' => $actasWithResults,
            'actas_without_results' => $actasWithoutResults,
            'results_without_acta' => $resultsWithoutActa,
            'with_acta_jrvs' => $withActaJrvs,
            'without_acta_jrvs' => $withoutActaJrvs,
            'coverage' => $coverage,
            'valid_votes' => $totalValid,
            'null_votes' => $totalNull,
            'blank_votes' => $totalBlank,
            'total_cast' => $totalCast,
            'total_voters' => $totalVoters,
            'total_registered' => $totalRegistered,
            'participation' => $participation,
            'abstention' => $abstention,
            'abstention_pct' => $abstentionPct,
            'difference' => $difference,
            'actas_without_electors' => $actasWithoutElectors,
            'candidates' => $candidates,
            'results' => $results,
            'max_votes' => $maxVotes,
            'provinces' => $provinces,
            'inconsistencies' => $inconsistentList,
            'inconsistency_summary' => $inconsistencySummary,
            'actas' => $actas,
            'notes' => $notes,
        ];
    }

    private function buildProvinces(int $electionId, array $actas): array
    {
        $rows = $this->db->table('polling_stations')
            ->select('code_province, name_province, COUNT(*) AS total_jrvs', false)
            ->where('election_id', $electionId)
            ->groupBy('code_province, name_province')
            ->orderBy('name_province', 'ASC')
            ->get()
            ->getResult();

        $provinces = [];
        foreach ($rows as $row) {
            $provinces[(string)$row->code_province] = (object)[
                'code_province' => $row->code_province,
                'name_province' => $row->name_province,
                'total_jrvs' => (int)$row->total_jrvs,
                'uploaded_actas' => 0,
                'actas_with_results' => 0,
                'valid_votes' => 0,
                'blank_votes' => 0,
                'null_votes' => 0,
                'total_voters' => 0,
                'registered_voters' => 0,
                'inconsistent_actas' => 0,
                'actas_without_electors' => 0,
                'coverage' => 0,
                'participation' => 0,
            ];
        }

        foreach ($actas as $acta) {
            $key = (string)$acta->code_province;
            if (!isset($provinces[$key])) {
                $provinces[$key] = (object)[
                    'code_province' => $acta->code_province,
                    'name_province' => $acta->name_province ?? 'N/D',
                    'total_jrvs' => 0,
                    'uploaded_actas' => 0,
                    'actas_with_results' => 0,
                    'valid_votes' => 0,
                    'blank_votes' => 0,
                    'null_votes' => 0,
                    'total_voters' => 0,
                    'registered_voters' => 0,
                    'inconsistent_actas' => 0,
                    'actas_without_electors' => 0,
                    'coverage' => 0,
                    'participation' => 0,
                ];
            }

            $province = $provinces[$key];
            $province->uploaded_actas++;

            if ($acta->inconsistent) {
                $province->inconsistent_actas++;
            }

            if ($acta->has_results) {
                $province->actas_with_results++;
                $province->valid_votes += $acta->valid_votes;
                $province->blank_votes += $acta->blank_votes;
                $province->null_votes += $acta->null_votes;
                $province->total_voters += $acta->total_voters;
                $province->registered_voters += $acta->registered_effective;

                if ($acta->registered_effective <= 0) {
                    $province->actas_without_electors++;
                }
            }
        }

        foreach ($provinces as $province) {
            $province->coverage = $province->total_jrvs > 0
                ? round(($province->uploaded_actas / $province->total_jrvs) * 100, 2)
                : 0;
            $province->participation = $province->registered_voters > 0
                ? round(($province->total_voters / $province->registered_voters) * 100, 2)
                : 0;
        }

        $provinces = array_values($provinces);
        usort($provinces, static fn($a, $b) => strcmp((string)$a->name_province, (string)$b->name_province));

        return $provinces;
    }

    private function buildInconsistencySummary(array $provinces, array $inconsistentList): array
    {
        $byProvince = [];
        foreach ($provinces as $province) {
            $byProvince[(string)$province->code_province] = (object)[
                'code_province' => $province->code_province,
                'name_province' => $province->name_province,
                'error' => 0,
                'novelty' => 0,
                'total' => 0,
            ];
        }

        $error = 0;
        $novelty = 0;

        foreach ($inconsistentList as $acta) {
            $key = (string)$acta->code_province;
            if (!isset($byProvince[$key])) {
                $byProvince[$key] = (object)[
                    'code_province' => $acta->code_province,
                    'name_province' => $acta->name_province ?? 'N/D',
                    'error' => 0,
                    'novelty' => 0,
                    'total' => 0,
                ];
            }

            if ($acta->consistency_status === 'error') {
                $error++;
                $byProvince[$key]->error++;
            }
            if ($acta->status === 'con_novedad') {
                $novelty++;
                $byProvince[$key]->novelty++;
            }

            $byProvince[$key]->total++;
        }

        $byProvince = array_values(array_filter($byProvince, static fn($province) => $province->total > 0));
        usort($byProvince, static fn($a, $b) => strcmp((string)$a->name_province, (string)$b->name_province));

        return [
            'error' => $error,
            'novelty' => $novelty,
            'total' => count($inconsistentList),
            'by_province' => $byProvince,
        ];
    }

    private function buildNotes(array $data): array
    {
        $notes = [];

        if ($data['coverage'] < 100) {
            $notes[] = 'Cobertura de actas: ' . $this->num($data['uploaded']) . ' de ' . $this->num($data['expected'])
                . ' (' . $this->num($data['coverage'], 2) . '%).';
        }

        if ($data['verified'] < $data['uploaded']) {
            $notes[] = 'Actas verificadas: ' . $this->num($data['verified']) . ' de ' . $this->num($data['uploaded']) . '.';
        }

        if ($data['inconsistent'] > 0) {
            $notes[] = 'Actas con inconsistencia o novedad: ' . $this->num($data['inconsistent'])
                . '. El detalle completo se incluye en el archivo Excel.';
        }

        if ($data['results_without_acta'] > 0) {
            $notes[] = 'JRV con resultados sin acta vigente: ' . $this->num($data['results_without_acta'])
                . '. Sus votos no forman parte de los totales oficiales de este informe.';
        }

        if ($data['actas_without_electors'] > 0) {
            $notes[] = 'Actas con resultados sin electores registrados: ' . $this->num($data['actas_without_electors'])
                . '. No aportan al denominador de participación.';
        }

        if ($data['difference'] !== 0) {
            $sign = $data['difference'] > 0 ? '+' : '';
            $notes[] = 'Diferencia entre votos emitidos y sufragantes: ' . $sign . $this->num($data['difference']) . '.';
        }

        if ($data['abstention'] < 0) {
            $notes[] = 'La abstención calculada es negativa porque los sufragantes superan a los electores registrados.';
        }

        if ($data['duplicated_actas'] > 0) {
            $notes[] = 'Actas vigentes duplicadas por JRV: ' . $this->num($data['duplicated_actas']) . '.';
        }

        if (!$notes) {
            $notes[] = 'No se detectaron observaciones en la cobertura, verificación o consistencia de las actas procesadas.';
        }

        return $notes;
    }

    private function safeColor(?string $color, int $index): string
    {
        if ($color && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1) {
            return strtoupper($color);
        }

        return self::COLORS[$index % count(self::COLORS)];
    }

    private function fullName(?string $firstName, ?string $lastName): string
    {
        return trim(trim((string)$firstName) . ' ' . trim((string)$lastName));
    }

    private function num(float|int $value, int $decimals = 0): string
    {
        return number_format((float)$value, $decimals, ',', '.');
    }
}
