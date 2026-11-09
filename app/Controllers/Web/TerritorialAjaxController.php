<?php

namespace App\Controllers\Web;

class TerritorialAjaxController extends BaseWebController
{
    public function cantons()
    {
        $provinceId = $this->request->getVar('province_id');
        if (!$provinceId) return $this->response->setJSON([]);
        $data = model('App\Models\CantonModel')->where('province_id', $provinceId)->findAll();
        return $this->response->setJSON($data);
    }

    public function parishes()
    {
        $cantonId = $this->request->getVar('canton_id');
        if (!$cantonId) return $this->response->setJSON([]);
        $data = model('App\Models\ParishModel')->where('canton_id', $cantonId)->findAll();
        return $this->response->setJSON($data);
    }

    public function precincts()
    {
        $parishId = $this->request->getVar('parish_id');
        if (!$parishId) return $this->response->setJSON([]);
        $data = model('App\Models\PrecinctModel')->where('parish_id', $parishId)->findAll();
        return $this->response->setJSON($data);
    }

    public function candidatesByDignity($electionId, $dignityId)
    {
        $builder = model('App\Models\CandidateModel')
            ->select('candidates.*, political_parties.name as party_name, political_parties.acronym as party_acronym, political_parties.list_number, political_parties.color as party_color')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->where('candidates.election_id', $electionId)
            ->where('candidates.dignity_id', $dignityId);

        $pollingStationId = (int)($this->request->getVar('polling_station_id') ?? 0);
        $dignity = model('App\Models\DignityModel')->find((int)$dignityId);
        if ($pollingStationId && $dignity && $dignity->level === 'cantonal') {
            $station = model('App\Models\PollingStationModel')->find($pollingStationId);
            if ($station) {
                $canton = $this->db->table('precincts pr')
                    ->select('c.id AS canton_id')
                    ->join('parishes pa', 'pa.id = pr.parish_id')
                    ->join('cantons c', 'c.id = pa.canton_id')
                    ->where('pr.id', (int)$station->precinct_id)
                    ->get()
                    ->getRow();
                if ($canton) {
                    $builder->where('candidates.canton_id', (int)$canton->canton_id);
                }
            }
        }

        return $this->response->setJSON($builder->findAll());
    }

    public function concejalListsByStation($psId, $dignityId)
    {
        $station = model('App\Models\PollingStationModel')->find((int)$psId);
        if (!$station) {
            return $this->response->setJSON(['lists' => [], 'totals' => null]);
        }

        $canton = $this->db->table('precincts pr')
            ->select('c.id AS canton_id')
            ->join('parishes pa', 'pa.id = pr.parish_id')
            ->join('cantons c', 'c.id = pa.canton_id')
            ->where('pr.id', (int)$station->precinct_id)
            ->get()
            ->getRow();

        if (!$canton) {
            return $this->response->setJSON(['lists' => [], 'totals' => null]);
        }

        $lists = $this->db->table('candidate_lists cl')
            ->select('cl.*, p.name AS party_name, p.acronym AS party_acronym, p.list_number, p.color AS party_color')
            ->join('political_parties p', 'p.id = cl.political_party_id')
            ->where('cl.election_id', (int)$station->election_id)
            ->where('cl.canton_id', (int)$canton->canton_id)
            ->where('cl.dignity_id', (int)$dignityId)
            ->where('cl.is_active', 1)
            ->orderBy('p.list_number', 'ASC')
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResult();

        $results = model('App\Models\ElectionListResultModel')
            ->forStation((int)$psId, (int)$station->election_id, (int)$dignityId);
        $existing = [];
        $totals = null;
        foreach ($results as $result) {
            $existing[(int)$result->candidate_list_id] = (int)$result->votes;
            if ($totals === null) {
                $totals = [
                    'blank_votes' => (int)$result->blank_votes,
                    'null_votes' => (int)$result->null_votes,
                    'total_voters' => (int)$result->total_voters,
                    'registered_voters' => (int)$result->registered_voters,
                    'has_results' => true,
                ];
            }
        }

        foreach ($lists as $list) {
            $list->votes = $existing[(int)$list->id] ?? 0;
            $list->candidates = $this->db->table('candidates')
                ->where('candidate_list_id', (int)$list->id)
                ->where('is_active', 1)
                ->orderBy('position', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResult();
        }

        return $this->response->setJSON([
            'lists' => $lists,
            'totals' => $totals,
        ]);
    }

    public function stationsByPrecinct()
    {
        $precinctId = $this->request->getVar('precinct_id');
        $electionId = $this->request->getVar('election_id');
        if (!$precinctId) return $this->response->setJSON([]);
        $model = model('App\Models\PollingStationModel');
        if ($electionId) $model->where('election_id', $electionId);
        $data = $model->where('precinct_id', $precinctId)->findAll();
        // Check which are already assigned
        if ($electionId && !empty($data)) {
            $psIds = array_column($data, 'id');
            $db = \Config\Database::connect();
            $assigned = $db->table('assignments')
                ->select('polling_station_id')
                ->whereIn('polling_station_id', $psIds)
                ->where('election_id', $electionId)
                ->where('is_active', 1)
                ->groupBy('polling_station_id')
                ->get()->getResult();
            $assignedIds = array_column($assigned, 'polling_station_id');
            foreach ($data as $s) {
                $s->assigned = in_array($s->id, $assignedIds);
            }
        }
        return $this->response->setJSON($data);
    }

    public function stationCurrentActa($psId)
    {
        $dignityId = (int)($this->request->getVar('dignity_id') ?? 0);
        $builder = model('App\Models\UploadedMinuteModel')
            ->where('polling_station_id', $psId)
            ->where('is_current', 1);
        if ($dignityId > 0) {
            $builder->where('dignity_id', $dignityId);
        }
        $data = $builder->orderBy('version', 'DESC')->first();
        if ($data) {
            $base = str_replace('\\', '/', FCPATH);
            $data->web_path = str_replace($base, '/', str_replace('\\', '/', $data->file_path));
        }
        return $this->response->setJSON($data ?: null);
    }

    public function electionDignities($electionId)
    {
        $service = new \App\Libraries\ActaService();
        $ids = $service->enabledDignityIds((int)$electionId);
        if (!$ids) {
            return $this->response->setJSON([]);
        }
        $rows = model('App\Models\DignityModel')
            ->whereIn('id', $ids)
            ->orderBy('id', 'ASC')
            ->findAll();
        return $this->response->setJSON($rows);
    }

    public function stationResults($psId, $dignityId)
    {
        $model = model('App\Models\ElectionResultModel')
            ->select('election_results.*, candidates.first_name, candidates.last_name')
            ->join('candidates', 'candidates.id = election_results.candidate_id')
            ->where('election_results.polling_station_id', $psId);
        if ($dignityId > 0) {
            $model->where('election_results.dignity_id', $dignityId);
        }
        $data = $model->findAll();
        return $this->response->setJSON($data);
    }

    public function auditLogs()
    {
        $page = (int)($this->request->getVar('page') ?? 1);
        $action = $this->request->getVar('action');
        $perPage = 50;
        $model = model('App\Models\AuditModel');
        $builder = $model->select('audits.*, users.username, users.first_name, users.last_name')
            ->join('users', 'users.id = audits.user_id', 'left');
        if ($action) $builder->like('audits.action', $action);
        $total = $builder->countAllResults(false);
        $data = $builder->orderBy('audits.created_at', 'DESC')->limit($perPage, ($page-1)*$perPage)->findAll();
        return $this->response->setJSON(['data' => $data, 'total' => $total, 'page' => $page, 'total_pages' => ceil($total/$perPage)]);
    }

    public function stationsByElection()
    {
        $electionId = $this->request->getVar('election_id');
        if (!$electionId) return $this->response->setJSON([]);
        $builder = model('App\Models\PollingStationModel')
            ->select('id, numero_junta, genero, name_precinct')
            ->where('election_id', $electionId);
        if (is_operator()) {
            $assigned = db_connect()->table('assignments')
                ->select('polling_station_id')
                ->where('user_id', (int)session()->get('user_id'))
                ->where('is_active', 1)
                ->get()
                ->getResultArray();
            $ids = array_map('intval', array_column($assigned, 'polling_station_id'));
            if (!$ids) return $this->response->setJSON([]);
            $builder->whereIn('id', $ids);
        }
        $data = $builder
            ->orderBy('name_province')
            ->orderBy('name_canton')
            ->orderBy('name_parish')
            ->orderBy('name_precinct')
            ->orderBy('numero_junta')
            ->findAll();
        return $this->response->setJSON($data);
    }

    public function stationsByLocation()
    {
        $electionId = $this->request->getVar('election_id');
        $provinceCode = $this->request->getVar('province_code');
        $cantonCode = $this->request->getVar('canton_code');
        $parishCode = $this->request->getVar('parish_code');

        $model = model('App\Models\PollingStationModel')->where('election_id', $electionId);
        if ($provinceCode) $model->where('code_province', (int)$provinceCode);
        if ($cantonCode) $model->where('code_canton', (int)$cantonCode);
        if ($parishCode) $model->where('code_parish', (int)$parishCode);

        $data = $model->orderBy('name_province')->orderBy('name_canton')->orderBy('name_parish')->orderBy('numero_junta')->findAll();

        // Add acta flag
        $psIds = array_column($data, 'id');
        if (!empty($psIds)) {
            $db = \Config\Database::connect();
            $withActa = $db->table('uploaded_minutes')
                ->select('polling_station_id')
                ->whereIn('polling_station_id', $psIds)
                ->where('is_current', 1)
                ->groupBy('polling_station_id')
                ->get()->getResult();
            $actaIds = array_column($withActa, 'polling_station_id');
            foreach ($data as $s) {
                $s->has_acta = in_array($s->id, $actaIds);
            }
        }
        return $this->response->setJSON($data);
    }

    public function cantonsByProvince()
    {
        $provinceCode = $this->request->getVar('province_code');
        if (!$provinceCode) return $this->response->setJSON([]);
        $electionId = $this->request->getVar('election_id');
        $db = \Config\Database::connect();
        $sql = 'SELECT DISTINCT c.code, c.name FROM cantons c JOIN polling_stations ps ON ps.code_canton = c.code WHERE ps.code_province = ?';
        $params = [(int)$provinceCode];
        if ($electionId) { $sql .= ' AND ps.election_id = ?'; $params[] = $electionId; }
        $sql .= ' ORDER BY c.name';
        $data = $db->query($sql, $params)->getResult();
        return $this->response->setJSON($data);
    }

    public function parishesByCanton()
    {
        $cantonCode = $this->request->getVar('canton_code');
        if (!$cantonCode) return $this->response->setJSON([]);
        $electionId = $this->request->getVar('election_id');
        $db = \Config\Database::connect();
        $sql = 'SELECT DISTINCT p.code, p.name FROM parishes p JOIN polling_stations ps ON ps.code_parish = p.code WHERE ps.code_canton = ?';
        $params = [(int)$cantonCode];
        if ($electionId) { $sql .= ' AND ps.election_id = ?'; $params[] = $electionId; }
        $sql .= ' ORDER BY p.name';
        $data = $db->query($sql, $params)->getResult();
        return $this->response->setJSON($data);
    }

    public function precinctsByCanton()
    {
        $cantonCode = $this->request->getVar('canton_code');
        if (!$cantonCode) return $this->response->setJSON([]);
        $electionId = $this->request->getVar('election_id');
        $db = \Config\Database::connect();
        $sql = 'SELECT DISTINCT pr.id, pr.code, pr.name FROM precincts pr JOIN polling_stations ps ON ps.code_precinct = pr.code WHERE ps.code_canton = ?';
        $params = [(int)$cantonCode];
        if ($electionId) { $sql .= ' AND ps.election_id = ?'; $params[] = $electionId; }
        $sql .= ' ORDER BY pr.name';
        $data = $db->query($sql, $params)->getResult();
        return $this->response->setJSON($data);
    }

    public function precinctsByParish()
    {
        $parishCode = $this->request->getVar('parish_code');
        if (!$parishCode) return $this->response->setJSON([]);
        $electionId = $this->request->getVar('election_id');
        $db = \Config\Database::connect();
        $sql = 'SELECT DISTINCT pr.id, pr.code, pr.name FROM precincts pr JOIN polling_stations ps ON ps.code_precinct = pr.code WHERE ps.code_parish = ?';
        $params = [(int)$parishCode];
        if ($electionId) { $sql .= ' AND ps.election_id = ?'; $params[] = $electionId; }
        $sql .= ' ORDER BY pr.name';
        $data = $db->query($sql, $params)->getResult();
        return $this->response->setJSON($data);
    }

    public function resultsByDignity($electionId, $dignityId)
    {
        $psId = $this->request->getVar('polling_station_id');
        $model = model('App\Models\ElectionResultModel');
        $dignity = model('App\Models\DignityModel')->find((int)$dignityId);
        $isCantonal = $dignity && $dignity->level === 'cantonal';
        $stationCantonId = null;

        if ($psId) {
            $dataBuilder = $model
                ->select('election_results.*, candidates.first_name, candidates.last_name, political_parties.name as party_name, political_parties.acronym as party_acronym, political_parties.list_number, political_parties.color')
                ->join('candidates', 'candidates.id = election_results.candidate_id')
                ->join('political_parties', 'political_parties.id = candidates.political_party_id')
                ->where('election_results.election_id', $electionId)
                ->where('election_results.dignity_id', $dignityId)
                ->where('election_results.polling_station_id', $psId);

            if ($isCantonal) {
                $station = model('App\Models\PollingStationModel')->find((int)$psId);
                if ($station) {
                    $canton = $this->db->table('precincts pr')
                        ->select('c.id AS canton_id')
                        ->join('parishes pa', 'pa.id = pr.parish_id')
                        ->join('cantons c', 'c.id = pa.canton_id')
                        ->where('pr.id', (int)$station->precinct_id)
                        ->get()
                        ->getRow();
                    if ($canton) {
                        $stationCantonId = (int)$canton->canton_id;
                        $dataBuilder->where('candidates.canton_id', $stationCantonId);
                    }
                }
            }

            $data = $dataBuilder->findAll();
            // Add acta info
            $minute = model('App\Models\UploadedMinuteModel')
                ->where('polling_station_id', $psId)
                ->where('dignity_id', $dignityId)
                ->where('is_current', 1)
                ->first();
            // Get aggregate for this station, limited to the canton when applicable.
            $aggBuilder = $model
                ->select('MAX(blank_votes) as total_blank, MAX(null_votes) as total_null, MAX(total_voters) as total_voters')
                ->where('election_results.election_id', $electionId)
                ->where('election_results.dignity_id', $dignityId)
                ->where('election_results.polling_station_id', $psId);
            if ($isCantonal && $stationCantonId) {
                $aggBuilder->join('candidates c', 'c.id = election_results.candidate_id')
                    ->where('c.canton_id', $stationCantonId);
            }
            $agg = $aggBuilder->get()->getRow();
        } else {
            $provinceCode = trim((string)($this->request->getVar('province_code') ?? ''));
            $cantonCode = trim((string)($this->request->getVar('canton_code') ?? ''));

            $builder = $model
                ->select('candidates.first_name, candidates.last_name, political_parties.name as party_name, political_parties.acronym as party_acronym, political_parties.list_number, political_parties.color, SUM(election_results.votes) as total_votes')
                ->join('candidates', 'candidates.id = election_results.candidate_id')
                ->join('political_parties', 'political_parties.id = candidates.political_party_id')
                ->where('election_results.election_id', $electionId)
                ->where('election_results.dignity_id', $dignityId);

            $cantonId = null;
            if ($cantonCode !== '') {
                $cantonRow = $this->db->table('cantons')->select('id')->where('code', $cantonCode)->get()->getRow();
                $cantonId = $cantonRow ? (int)$cantonRow->id : null;
            }

            if ($provinceCode !== '' || $cantonCode !== '') {
                $builder->join('polling_stations ps', 'ps.id = election_results.polling_station_id');
                if ($provinceCode !== '') {
                    $builder->where('ps.code_province', $provinceCode);
                }
                if ($cantonCode !== '') {
                    $builder->where('ps.code_canton', $cantonCode);
                }
            }

            if ($isCantonal && $cantonId) {
                $builder->where('candidates.canton_id', $cantonId);
            }

            $data = $builder
                ->groupBy('election_results.candidate_id')
                ->orderBy('total_votes', 'DESC')
                ->findAll();

            // Get aggregated blank/null/total_voters across the selected scope.
            $db = \Config\Database::connect();
            $aggSql = 'SELECT SUM(t.blank) as total_blank, SUM(t.nullv) as total_null, SUM(t.voters) as total_voters
                       FROM (
                           SELECT MAX(er.blank_votes) as blank, MAX(er.null_votes) as nullv, MAX(er.total_voters) as voters
                           FROM election_results er';
            $aggParams = [$electionId, $dignityId];

            if ($provinceCode !== '' || $cantonCode !== '') {
                $aggSql .= ' JOIN polling_stations ps ON ps.id = er.polling_station_id';
            }
            if ($isCantonal && $cantonId) {
                $aggSql .= ' JOIN candidates c ON c.id = er.candidate_id';
            }

            $aggSql .= ' WHERE er.election_id = ? AND er.dignity_id = ?';

            if ($isCantonal && $cantonId) {
                $aggSql .= ' AND c.canton_id = ?';
                $aggParams[] = $cantonId;
            }
            if ($provinceCode !== '') {
                $aggSql .= ' AND ps.code_province = ?';
                $aggParams[] = $provinceCode;
            }
            if ($cantonCode !== '') {
                $aggSql .= ' AND ps.code_canton = ?';
                $aggParams[] = $cantonCode;
            }

            $aggSql .= ' GROUP BY er.polling_station_id) t';
            $agg = $db->query($aggSql, $aggParams)->getRow();
        }
        return $this->response->setJSON([
            'results' => $data,
            'total_blank' => ($agg->total_blank ?? 0),
            'total_null' => ($agg->total_null ?? 0),
            'total_voters' => ($agg->total_voters ?? 0),
            'has_acta' => isset($minute) && $minute ? true : false,
            'acta_id' => isset($minute) && $minute ? $minute->id : null,
        ]);
    }

    public function minuteData($minuteId)
    {
        $minute = model('App\Models\UploadedMinuteModel')->find($minuteId);
        if (!$minute) {
            return $this->response->setJSON(['error' => 'Acta no encontrada']);
        }

        $psId = $minute->polling_station_id;
        $electionId = $minute->election_id;
        $station = model('App\Models\PollingStationModel')->find($psId);

        $base = str_replace('\\', '/', FCPATH);
        $minute->web_path = str_replace($base, '/', str_replace('\\', '/', $minute->file_path));
        $minute->is_image = in_array(strtolower($minute->file_type), ['jpg', 'jpeg', 'png']);

        $dignity = model('App\Models\DignityModel')->find($minute->dignity_id);

        $db = \Config\Database::connect();
        $rows = $db->query(
            'SELECT er.*, c.first_name, c.last_name, d.name AS dignity_name,
                    pp.acronym AS party_acronym, pp.list_number, pp.color AS party_color
             FROM election_results er
             JOIN candidates c ON c.id = er.candidate_id
             JOIN dignities d ON d.id = er.dignity_id
             LEFT JOIN political_parties pp ON pp.id = c.political_party_id
             WHERE er.polling_station_id = ? AND er.election_id = ? AND er.dignity_id = ?
             ORDER BY c.last_name, c.first_name',
            [$psId, $electionId, $minute->dignity_id]
        )->getResult();

        $dignities = [];
        foreach ($rows as $r) {
            if (!isset($dignities[$r->dignity_id])) {
                $dignities[$r->dignity_id] = ['id' => $r->dignity_id, 'name' => $r->dignity_name, 'candidates' => []];
            }
            $dignities[$r->dignity_id]['candidates'][] = [
                'result_id' => (int)$r->id,
                'candidate_id' => (int)$r->candidate_id,
                'first_name' => $r->first_name,
                'last_name' => $r->last_name,
                'party_acronym' => $r->party_acronym,
                'list_number' => $r->list_number,
                'party_color' => $r->party_color,
                'votes' => (int)$r->votes,
            ];
        }

        $first = $rows[0] ?? null;
        return $this->response->setJSON([
            'minute' => $minute,
            'station' => $station,
            'dignity' => $dignity,
            'dignities' => array_values($dignities),
            'totals' => [
                'blank_votes' => $first ? (int)$first->blank_votes : 0,
                'null_votes' => $first ? (int)$first->null_votes : 0,
                'total_voters' => $first ? (int)$first->total_voters : 0,
                'registered_voters' => $first ? (int)$first->registered_voters : 0,
                'is_verified' => $first ? (int)$first->is_verified : 0,
            ],
        ]);
    }
}
