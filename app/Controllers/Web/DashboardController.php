<?php

namespace App\Controllers\Web;

use App\Libraries\ActaService;

class DashboardController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;

        if (session()->get('role') === 'Operador Electoral') {
            return redirect()->to(base_url('results/register'))->with('error', 'Acceso denegado');
        }

        $db = $this->db;
        if (!$this->schemaReady($db)) {
            return $this->render('dashboard/pending_migration', [], 'Dashboard');
        }

        $electionModel = model('App\Models\ElectionModel');
        $resultModel = model('App\Models\ElectionResultModel');

        $elections = $electionModel->getActive();
        $electionId = (int)($this->request->getGet('election_id') ?? 0);
        $validElectionIds = array_map(static fn($e) => (int)$e->id, $elections);

        if (!$electionId || !in_array($electionId, $validElectionIds, true)) {
            $current = $electionModel->getCurrent();
            $electionId = $current ? (int)$current->id : (int)($elections[0]->id ?? 0);
        }

        $currentElection = null;
        foreach ($elections as $election) {
            if ((int)$election->id === $electionId) {
                $currentElection = $election;
                break;
            }
        }

        $cantonOptions = [];
        if ($electionId) {
            $cantonOptions = $db->table('polling_stations')
                ->select('code_province, name_province, code_canton, name_canton, COUNT(*) AS total', false)
                ->where('election_id', $electionId)
                ->groupBy('code_province, name_province, code_canton, name_canton')
                ->orderBy('name_province', 'ASC')
                ->orderBy('name_canton', 'ASC')
                ->get()
                ->getResultArray();
        }

        $cantonFilter = trim((string)($this->request->getGet('canton') ?? ''));
        $selectedCanton = null;
        $showCantonProvince = count(array_unique(array_column($cantonOptions, 'name_province'))) > 1;
        foreach ($cantonOptions as $index => $canton) {
            $cantonValue = $canton['code_province'] . '|' . $canton['code_canton'];
            $cantonOptions[$index]['value'] = $cantonValue;
            $cantonOptions[$index]['label'] = $showCantonProvince
                ? $canton['name_province'] . ' / ' . $canton['name_canton']
                : $canton['name_canton'];

            if ($cantonValue === $cantonFilter) {
                $selectedCanton = $cantonOptions[$index];
            }
        }
        if (!$selectedCanton) {
            $cantonFilter = '';
        }

        $cantonWhere = '';
        $cantonParams = [];
        if ($selectedCanton) {
            $cantonWhere = ' AND ps.code_province = ? AND ps.code_canton = ?';
            $cantonParams = [$selectedCanton['code_province'], $selectedCanton['code_canton']];
        }

        $totalJrvsBuilder = $db->table('polling_stations')->where('election_id', $electionId);
        if ($selectedCanton) {
            $totalJrvsBuilder->where('code_province', $selectedCanton['code_province'])
                ->where('code_canton', $selectedCanton['code_canton']);
        }
        $totalJrvs = $electionId ? (int)$totalJrvsBuilder->countAllResults() : 0;

        $service = new ActaService();
        $allDignityIds = $electionId ? $service->enabledDignityIds($electionId) : [];
        $dignities = $allDignityIds
            ? model('App\Models\DignityModel')->whereIn('id', $allDignityIds)->orderBy('id', 'ASC')->findAll()
            : [];

        $dignityId = (int)($this->request->getGet('dignity_id') ?? 0);
        if ($dignityId && !in_array($dignityId, $allDignityIds, true)) {
            $dignityId = 0;
        }

        $scopeDignityIds = $dignityId ? [$dignityId] : $allDignityIds;
        $scopeCount = count($scopeDignityIds);
        $scopeDignities = [];
        $selectedDignity = null;
        foreach ($dignities as $dignity) {
            if (in_array((int)$dignity->id, $scopeDignityIds, true)) {
                $scopeDignities[] = $dignity;
            }
            if ((int)$dignity->id === $dignityId) {
                $selectedDignity = $dignity;
            }
        }

        $stats = [
            'total_jrvs' => $totalJrvs,
            'expected_actas' => $scopeCount ? $totalJrvs * $scopeCount : 0,
            'received_actas' => 0,
            'verified_actas' => 0,
            'inconsistent_actas' => 0,
            'complete_jrvs' => 0,
            'partial_jrvs' => 0,
            'no_acta_jrvs' => $totalJrvs,
            'with_acta_jrvs' => 0,
            'avance' => 0,
            'participacion' => 0,
            'total_voters' => 0,
            'total_electores' => 0,
            'scope_count' => $scopeCount,
        ];
        $statusCounts = ['registrada' => 0, 'con_novedad' => 0, 'verificada' => 0];
        $consistencyCounts = ['ok' => 0, 'error' => 0, 'sin_datos' => 0];
        $breakdownRows = [];
        $breakdownLabel = 'Provincia';
        $breakdownTitle = 'Avance por Provincia';
        $resultsByDignity = [];
        $pending = [];
        $pendingTotal = 0;
        $pendingLimit = 50;

        if ($electionId && $scopeCount && $totalJrvs) {
            $in = implode(',', array_fill(0, $scopeCount, '?'));
            $scopeParams = array_merge([$electionId], $scopeDignityIds);

            $actaStats = $db->query(
                "SELECT COUNT(*) AS actas,
                        COALESCE(SUM(um.status = 'verificada'), 0) AS verificadas,
                        COALESCE(SUM(um.consistency_status = 'error' OR um.status = 'con_novedad'), 0) AS inconsistentes
                 FROM uploaded_minutes um
                 JOIN polling_stations ps ON ps.id = um.polling_station_id AND ps.election_id = um.election_id
                 WHERE um.election_id = ? AND um.is_current = 1 AND um.dignity_id IN ($in) {$cantonWhere}",
                array_merge($scopeParams, $cantonParams)
            )->getRow();

            $stats['received_actas'] = (int)($actaStats->actas ?? 0);
            $stats['verified_actas'] = (int)($actaStats->verificadas ?? 0);
            $stats['inconsistent_actas'] = (int)($actaStats->inconsistentes ?? 0);
            $stats['avance'] = $stats['expected_actas'] > 0
                ? round(($stats['received_actas'] / $stats['expected_actas']) * 100, 2)
                : 0;

            $completeness = $db->query(
                "SELECT COALESCE(SUM(cnt = ?), 0) AS completas,
                        COALESCE(SUM(cnt > 0 AND cnt < ?), 0) AS incompletas,
                        COALESCE(SUM(cnt = 0), 0) AS sin_actas
                 FROM (
                     SELECT ps.id, COUNT(DISTINCT um.dignity_id) AS cnt
                     FROM polling_stations ps
                     LEFT JOIN uploaded_minutes um ON um.polling_station_id = ps.id
                         AND um.election_id = ps.election_id
                         AND um.is_current = 1
                         AND um.dignity_id IN ($in)
                     WHERE ps.election_id = ? {$cantonWhere}
                     GROUP BY ps.id
                 ) t",
                array_merge([$scopeCount, $scopeCount], $scopeDignityIds, [$electionId], $cantonParams)
            )->getRow();

            $stats['complete_jrvs'] = (int)($completeness->completas ?? 0);
            $stats['partial_jrvs'] = (int)($completeness->incompletas ?? 0);
            $stats['no_acta_jrvs'] = (int)($completeness->sin_actas ?? 0);
            $stats['with_acta_jrvs'] = max(0, $totalJrvs - $stats['no_acta_jrvs']);

            $participation = $db->query(
                "SELECT COALESCE(SUM(tv), 0) AS votantes, COALESCE(SUM(ev), 0) AS electores
                 FROM (
                     SELECT er.polling_station_id,
                            MAX(er.total_voters) AS tv,
                            MAX(er.registered_voters) AS ev
                     FROM election_results er
                     JOIN uploaded_minutes um ON um.polling_station_id = er.polling_station_id
                         AND um.election_id = er.election_id
                         AND um.dignity_id = er.dignity_id
                         AND um.is_current = 1
                     JOIN polling_stations ps ON ps.id = er.polling_station_id AND ps.election_id = er.election_id
                     WHERE er.election_id = ? AND er.dignity_id IN ($in) {$cantonWhere}
                     GROUP BY er.polling_station_id
                 ) t",
                array_merge($scopeParams, $cantonParams)
            )->getRow();

            $stats['total_voters'] = (int)($participation->votantes ?? 0);
            $stats['total_electores'] = (int)($participation->electores ?? 0);
            $stats['participacion'] = $stats['total_electores'] > 0
                ? round(($stats['total_voters'] / $stats['total_electores']) * 100, 2)
                : 0;

            $statusBuilder = $db->table('uploaded_minutes um')
                ->select('um.status, COUNT(*) AS total', false)
                ->join('polling_stations ps', 'ps.id = um.polling_station_id AND ps.election_id = um.election_id')
                ->where('um.election_id', $electionId)
                ->where('um.is_current', 1)
                ->whereIn('um.dignity_id', $scopeDignityIds);
            if ($selectedCanton) {
                $statusBuilder->where('ps.code_province', $selectedCanton['code_province'])
                    ->where('ps.code_canton', $selectedCanton['code_canton']);
            }
            $statusRows = $statusBuilder->groupBy('um.status')->get()->getResultArray();
            foreach ($statusRows as $row) {
                if (isset($statusCounts[$row['status']])) {
                    $statusCounts[$row['status']] = (int)$row['total'];
                }
            }

            $consistencyBuilder = $db->table('uploaded_minutes um')
                ->select('um.consistency_status, COUNT(*) AS total', false)
                ->join('polling_stations ps', 'ps.id = um.polling_station_id AND ps.election_id = um.election_id')
                ->where('um.election_id', $electionId)
                ->where('um.is_current', 1)
                ->whereIn('um.dignity_id', $scopeDignityIds);
            if ($selectedCanton) {
                $consistencyBuilder->where('ps.code_province', $selectedCanton['code_province'])
                    ->where('ps.code_canton', $selectedCanton['code_canton']);
            }
            $consistencyRows = $consistencyBuilder->groupBy('um.consistency_status')->get()->getResultArray();
            foreach ($consistencyRows as $row) {
                if (isset($consistencyCounts[$row['consistency_status']])) {
                    $consistencyCounts[$row['consistency_status']] = (int)$row['total'];
                }
            }

            $breakdownKey = $selectedCanton ? 'code_parish' : 'code_province';
            $breakdownName = $selectedCanton ? 'name_parish' : 'name_province';
            $breakdownLabel = $selectedCanton ? 'Parroquia' : 'Provincia';
            $breakdownTitle = $selectedCanton ? 'Avance por Parroquia' : 'Avance por Provincia';

            $breakdownBuilder = $db->table('polling_stations')
                ->select("{$breakdownKey}, {$breakdownName}, COUNT(*) AS total", false)
                ->where('election_id', $electionId)
                ->groupBy("{$breakdownKey}, {$breakdownName}")
                ->orderBy($breakdownName, 'ASC');
            if ($selectedCanton) {
                $breakdownBuilder->where('code_province', $selectedCanton['code_province'])
                    ->where('code_canton', $selectedCanton['code_canton']);
            }
            $breakdown = $breakdownBuilder->get()->getResultArray();

            $breakdownMinutesBuilder = $db->table('uploaded_minutes um')
                ->select("ps.{$breakdownKey} AS breakdown_key, um.dignity_id, COUNT(*) AS recibidas", false)
                ->join('polling_stations ps', 'ps.id = um.polling_station_id AND ps.election_id = um.election_id')
                ->where('um.election_id', $electionId)
                ->where('um.is_current', 1)
                ->whereIn('um.dignity_id', $scopeDignityIds)
                ->groupBy("ps.{$breakdownKey}, um.dignity_id");
            if ($selectedCanton) {
                $breakdownMinutesBuilder->where('ps.code_province', $selectedCanton['code_province'])
                    ->where('ps.code_canton', $selectedCanton['code_canton']);
            }
            $breakdownMinutes = $breakdownMinutesBuilder->get()->getResultArray();

            $breakdownMinuteMap = [];
            foreach ($breakdownMinutes as $row) {
                $breakdownMinuteMap[(string)$row['breakdown_key']][(int)$row['dignity_id']] = (int)$row['recibidas'];
            }

            foreach ($breakdown as $item) {
                $breakdownValue = (string)$item[$breakdownKey];
                $breakdownTotal = (int)$item['total'];
                $breakdownDignities = [];
                $breakdownReceived = 0;
                foreach ($scopeDignities as $dignity) {
                    $did = (int)$dignity->id;
                    $received = $breakdownMinuteMap[$breakdownValue][$did] ?? 0;
                    $breakdownDignities[$did] = [
                        'received' => $received,
                        'expected' => $breakdownTotal,
                        'avance' => $breakdownTotal > 0 ? round(($received / $breakdownTotal) * 100, 2) : 0,
                    ];
                    $breakdownReceived += $received;
                }
                $breakdownExpected = $breakdownTotal * $scopeCount;
                $breakdownRows[] = [
                    'code' => $breakdownValue,
                    'name' => $item[$breakdownName],
                    'total' => $breakdownTotal,
                    'received' => $breakdownReceived,
                    'expected' => $breakdownExpected,
                    'avance' => $breakdownExpected > 0 ? round(($breakdownReceived / $breakdownExpected) * 100, 2) : 0,
                    'dignities' => $breakdownDignities,
                ];
            }

            foreach ($scopeDignities as $dignity) {
                $did = (int)$dignity->id;
                $results = $resultModel->getResultsByDignity(
                    $electionId,
                    $did,
                    $selectedCanton['code_province'] ?? null,
                    $selectedCanton['code_canton'] ?? null
                );
                $totalVotes = 0;
                foreach ($results as $result) {
                    $totalVotes += (int)$result->total_votes;
                }

                $withResults = $db->query(
                    "SELECT COUNT(DISTINCT er.polling_station_id) AS total
                     FROM election_results er
                     JOIN uploaded_minutes um ON um.polling_station_id = er.polling_station_id
                         AND um.election_id = er.election_id
                         AND um.dignity_id = er.dignity_id
                         AND um.is_current = 1
                     JOIN polling_stations ps ON ps.id = er.polling_station_id AND ps.election_id = er.election_id
                     WHERE er.election_id = ? AND er.dignity_id = ? {$cantonWhere}",
                    array_merge([$electionId, $did], $cantonParams)
                )->getRow();

                $actaRow = $db->query(
                    "SELECT COUNT(*) AS actas,
                            COALESCE(SUM(um.status = 'verificada'), 0) AS verificadas,
                            COALESCE(SUM(um.consistency_status = 'error' OR um.status = 'con_novedad'), 0) AS inconsistencias
                     FROM uploaded_minutes um
                     JOIN polling_stations ps ON ps.id = um.polling_station_id AND ps.election_id = um.election_id
                     WHERE um.election_id = ? AND um.is_current = 1 AND um.dignity_id = ? {$cantonWhere}",
                    array_merge([$electionId, $did], $cantonParams)
                )->getRow();

                $resultsByDignity[] = [
                    'dignity' => $dignity,
                    'results' => $results,
                    'total_votes' => $totalVotes,
                    'received' => (int)($actaRow->actas ?? 0),
                    'verified' => (int)($actaRow->verificadas ?? 0),
                    'errors' => (int)($actaRow->inconsistencias ?? 0),
                    'with_results' => (int)($withResults->total ?? 0),
                ];
            }

            $pendingSql = "SELECT ps.id, ps.name_province, ps.name_canton, ps.name_parish, ps.name_precinct,
                                  ps.numero_junta, ps.genero,
                                  COUNT(DISTINCT um.dignity_id) AS recibidas,
                                  GROUP_CONCAT(DISTINCT CASE WHEN um.id IS NULL THEN d.name END ORDER BY d.id SEPARATOR ', ') AS faltantes,
                                  MAX(a.operators) AS operador
                           FROM polling_stations ps
                           JOIN election_dignities ed ON ed.election_id = ps.election_id AND ed.is_active = 1
                           JOIN dignities d ON d.id = ed.dignity_id
                           LEFT JOIN uploaded_minutes um ON um.polling_station_id = ps.id
                               AND um.election_id = ps.election_id
                               AND um.dignity_id = ed.dignity_id
                               AND um.is_current = 1
                           LEFT JOIN (
                               SELECT a.election_id, a.polling_station_id,
                                      GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') AS operators
                               FROM assignments a
                               JOIN users u ON u.id = a.user_id
                               WHERE a.is_active = 1
                               GROUP BY a.election_id, a.polling_station_id
                           ) a ON a.election_id = ps.election_id AND a.polling_station_id = ps.id
                           WHERE ps.election_id = ? AND ed.dignity_id IN ($in) {$cantonWhere}
                           GROUP BY ps.id, ps.name_province, ps.name_canton, ps.name_parish, ps.name_precinct, ps.numero_junta, ps.genero
                           HAVING COUNT(DISTINCT um.dignity_id) < ?
                           ORDER BY ps.name_province, ps.name_canton, ps.name_parish, ps.name_precinct, ps.numero_junta
                           LIMIT {$pendingLimit}";

            $pending = $db->query($pendingSql, array_merge($scopeParams, $cantonParams, [$scopeCount]))->getResult();

            $pendingCount = $db->query(
                "SELECT COUNT(*) AS total
                 FROM (
                     SELECT ps.id
                     FROM polling_stations ps
                     JOIN election_dignities ed ON ed.election_id = ps.election_id AND ed.is_active = 1
                     LEFT JOIN uploaded_minutes um ON um.polling_station_id = ps.id
                         AND um.election_id = ps.election_id
                         AND um.dignity_id = ed.dignity_id
                         AND um.is_current = 1
                     WHERE ps.election_id = ? AND ed.dignity_id IN ($in) {$cantonWhere}
                     GROUP BY ps.id
                     HAVING COUNT(DISTINCT um.dignity_id) < ?
                 ) t",
                array_merge($scopeParams, $cantonParams, [$scopeCount])
            )->getRow();
            $pendingTotal = (int)($pendingCount->total ?? 0);
        }

        return $this->render('dashboard/index', [
            'elections' => $elections,
            'currentElection' => $currentElection,
            'electionId' => $electionId,
            'dignities' => $dignities,
            'scopeDignities' => $scopeDignities,
            'dignityId' => $dignityId,
            'selectedDignity' => $selectedDignity,
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'consistencyCounts' => $consistencyCounts,
            'breakdownRows' => $breakdownRows,
            'breakdownLabel' => $breakdownLabel,
            'breakdownTitle' => $breakdownTitle,
            'cantonOptions' => $cantonOptions,
            'selectedCanton' => $selectedCanton,
            'cantonFilter' => $cantonFilter,
            'resultsByDignity' => $resultsByDignity,
            'pending' => $pending,
            'pendingTotal' => $pendingTotal,
            'pendingLimit' => $pendingLimit,
        ], 'Dashboard');
    }

    private function schemaReady($db): bool
    {
        return $db->tableExists('election_dignities')
            && $db->fieldExists('dignity_id', 'uploaded_minutes')
            && $db->fieldExists('consistency_status', 'uploaded_minutes')
            && $db->fieldExists('is_current', 'uploaded_minutes');
    }
}
