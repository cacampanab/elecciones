<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $electionId = $this->request->getVar('election_id');

        if (!$electionId) {
            $current = model('App\Models\ElectionModel')->getCurrent();
            $electionId = $current ? $current->id : null;
        }

        if (!$electionId) {
            return jsonResponse([
                'total_jrv' => 0,
                'jrv_reportadas' => 0,
                'jrv_pendientes' => 0,
                'porcentaje_avance' => 0,
                'total_actas' => 0,
                'total_electores' => 0,
                'total_votantes' => 0,
                'participacion' => 0,
                'jrv_por_estado' => [],
                'resultados_por_dignidad' => [],
            ]);
        }

        $psModel = model('App\Models\PollingStationModel');
        $resultModel = model('App\Models\ElectionResultModel');

        $totalJrv = $psModel->where('election_id', $electionId)->countAllResults();
        $reportadas = $psModel->where('election_id', $electionId)->whereIn('status', ['reportada', 'revisada', 'aprobada'])->countAllResults();
        $pendientes = $psModel->where('election_id', $electionId)->where('status', 'pendiente')->countAllResults();
        $enProceso = $psModel->where('election_id', $electionId)->where('status', 'en_proceso')->countAllResults();

        $avance = $totalJrv > 0 ? round(($reportadas / $totalJrv) * 100, 2) : 0;

        $totalElectores = $psModel->selectSum('numero_electores')->where('election_id', $electionId)->get()->getRow()->numero_electores ?? 0;

        $totalVotantes = $resultModel->select('SUM(DISTINCT election_results.total_voters) as total')
            ->where('election_results.election_id', $electionId)
            ->get()->getRow()->total ?? 0;

        $participacion = $totalElectores > 0 ? round(($totalVotantes / $totalElectores) * 100, 2) : 0;

        $jrvPorEstado = $psModel
            ->select('status, COUNT(*) as total')
            ->where('election_id', $electionId)
            ->groupBy('status')
            ->findAll();

        $dignities = model('App\Models\DignityModel')->where('is_active', 1)->findAll();
        $resultadosPorDignidad = [];

        foreach ($dignities as $dignity) {
            $results = $resultModel->getResultsByDignity($electionId, $dignity->id);
            if (!empty($results)) {
                $resultadosPorDignidad[] = [
                    'dignidad' => $dignity->name,
                    'candidatos' => $results,
                ];
            }
        }

        return jsonResponse([
            'total_jrv' => (int)$totalJrv,
            'jrv_reportadas' => (int)$reportadas,
            'jrv_pendientes' => (int)$pendientes,
            'jrv_en_proceso' => (int)$enProceso,
            'porcentaje_avance' => $avance,
            'total_electores' => (int)$totalElectores,
            'total_votantes' => (int)$totalVotantes,
            'participacion' => $participacion,
            'jrv_por_estado' => $jrvPorEstado,
            'resultados_por_dignidad' => $resultadosPorDignidad,
        ]);
    }

    public function byProvince()
    {
        $electionId = $this->request->getVar('election_id');
        if (!$electionId) {
            $current = model('App\Models\ElectionModel')->getCurrent();
            $electionId = $current ? $current->id : null;
        }

        $db = \Config\Database::connect();
        $results = $db->table('polling_stations')
            ->select('code_province, name_province, COUNT(*) as total, SUM(CASE WHEN status IN ("reportada","revisada","aprobada") THEN 1 ELSE 0 END) as reportadas, SUM(numero_electores) as electores')
            ->where('election_id', $electionId)
            ->groupBy('code_province, name_province')
            ->orderBy('name_province')
            ->get()->getResult();

        foreach ($results as $r) {
            $r->avance = $r->total > 0 ? round(($r->reportadas / $r->total) * 100, 2) : 0;
        }

        return jsonResponse($results);
    }

    public function participation()
    {
        $electionId = $this->request->getVar('election_id');
        if (!$electionId) {
            $current = model('App\Models\ElectionModel')->getCurrent();
            $electionId = $current ? $current->id : null;
        }

        $db = \Config\Database::connect();
        $results = $db->table('election_results')
            ->select('polling_stations.code_province, polling_stations.name_province, SUM(DISTINCT election_results.total_voters) as votantes, MAX(polling_stations.numero_electores) as electores')
            ->join('polling_stations', 'polling_stations.id = election_results.polling_station_id')
            ->where('election_results.election_id', $electionId)
            ->groupBy('polling_stations.code_province, polling_stations.name_province')
            ->get()->getResult();

        foreach ($results as $r) {
            $r->participacion = $r->electores > 0 ? round(($r->votantes / $r->electores) * 100, 2) : 0;
        }

        return jsonResponse($results);
    }

    public function pendingStations()
    {
        $electionId = $this->request->getVar('election_id');
        if (!$electionId) {
            $current = model('App\Models\ElectionModel')->getCurrent();
            $electionId = $current ? $current->id : null;
        }

        $pending = model('App\Models\PollingStationModel')
            ->select('polling_stations.*, users.first_name, users.last_name, users.username')
            ->join('assignments', 'assignments.polling_station_id = polling_stations.id AND assignments.election_id = polling_stations.election_id', 'left')
            ->join('users', 'users.id = assignments.user_id', 'left')
            ->where('polling_stations.election_id', $electionId)
            ->where('polling_stations.status !=', 'aprobada')
            ->orderBy('polling_stations.status', 'ASC')
            ->orderBy('polling_stations.name_province', 'ASC')
            ->findAll();

        return jsonResponse($pending);
    }
}
