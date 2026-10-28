<?php

namespace App\Controllers\Web;

use App\Libraries\ActaService;

class InconsistencyController extends BaseWebController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('minutes.read')) {
            return $redirect;
        }

        $electionId = (int)($this->request->getGet('election_id') ?? 0);
        $dignityId = (int)($this->request->getGet('dignity_id') ?? 0);
        $search = trim((string)($this->request->getGet('q') ?? ''));

        $elections = model('App\Models\ElectionModel')->getActive();
        if (!$electionId) {
            $current = model('App\Models\ElectionModel')->getCurrent();
            $electionId = $current ? (int)$current->id : (int)($elections[0]->id ?? 0);
        }

        $service = new ActaService();
        $dignityIds = $electionId ? $service->enabledDignityIds($electionId) : [];
        $dignities = $dignityIds
            ? model('App\Models\DignityModel')->whereIn('id', $dignityIds)->orderBy('id', 'ASC')->findAll()
            : [];

        $builder = model('App\Models\UploadedMinuteModel')
            ->select('uploaded_minutes.*, d.name AS dignity_name, users.first_name, users.last_name, ps.numero_junta, ps.genero, ps.name_province, ps.name_canton, ps.name_parish, ps.name_precinct, e.name as election_name')
            ->join('dignities d', 'd.id = uploaded_minutes.dignity_id', 'left')
            ->join('users', 'users.id = uploaded_minutes.uploaded_by', 'left')
            ->join('polling_stations ps', 'ps.id = uploaded_minutes.polling_station_id')
            ->join('elections e', 'e.id = uploaded_minutes.election_id')
            ->where('uploaded_minutes.is_current', 1)
            ->groupStart()
                ->where('uploaded_minutes.consistency_status', 'error')
                ->orWhere('uploaded_minutes.status', 'con_novedad')
            ->groupEnd()
            ->orderBy('uploaded_minutes.updated_at', 'DESC');

        if ($electionId) {
            $builder->where('uploaded_minutes.election_id', $electionId);
        }
        if ($dignityId) {
            $builder->where('uploaded_minutes.dignity_id', $dignityId);
        }
        if ($search !== '') {
            $builder->groupStart()
                ->like('ps.numero_junta', $search)
                ->orLike('ps.name_precinct', $search)
                ->groupEnd();
        }
        if (is_operator()) {
            $assigned = $this->db->table('assignments')
                ->select('polling_station_id')
                ->where('user_id', (int)session()->get('user_id'))
                ->where('is_active', 1)
                ->get()
                ->getResultArray();
            $assignedIds = array_map('intval', array_column($assigned, 'polling_station_id'));
            if (!$assignedIds) {
                $builder->where('uploaded_minutes.id', -1);
            } else {
                $builder->whereIn('uploaded_minutes.polling_station_id', $assignedIds);
            }
        }

        $minutes = $builder->findAll();

        return $this->render('inconsistencies/index', compact('minutes', 'elections', 'dignities', 'electionId', 'dignityId', 'search'), 'Inconsistencias');
    }
}
