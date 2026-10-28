<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function resultsByProvince($electionId = null)
    {
        $dignityId = $this->request->getVar('dignity_id');
        if (!$electionId || !$dignityId) return errorResponse('Parámetros requeridos: election_id, dignity_id', 400);

        $db = \Config\Database::connect();
        $results = $db->table('election_results')
            ->select('polling_stations.code_province, polling_stations.name_province, candidates.first_name, candidates.last_name, political_parties.name as party_name, political_parties.acronym as party_acronym, SUM(election_results.votes) as total_votes')
            ->join('polling_stations', 'polling_stations.id = election_results.polling_station_id')
            ->join('candidates', 'candidates.id = election_results.candidate_id')
            ->join('political_parties', 'political_parties.id = candidates.political_party_id')
            ->where('election_results.election_id', $electionId)
            ->where('election_results.dignity_id', $dignityId)
            ->groupBy('polling_stations.code_province, polling_stations.name_province, election_results.candidate_id')
            ->orderBy('polling_stations.name_province, total_votes', 'DESC')
            ->get()->getResult();

        return jsonResponse($results);
    }

    public function exportPdf($type = null)
    {
        $electionId = $this->request->getVar('election_id');
        $dignityId = $this->request->getVar('dignity_id');

        $election = model('App\Models\ElectionModel')->find($electionId);
        $dignity = model('App\Models\DignityModel')->find($dignityId);

        $results = [];
        if ($electionId && $dignityId) {
            $results = model('App\Models\ElectionResultModel')->getResultsByDignity($electionId, $dignityId);
        }

        $html = view('reports/results_pdf', [
            'election' => $election,
            'dignity' => $dignity,
            'results' => $results,
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('resultados_' . ($election->name ?? '') . '.pdf', ['Attachment' => false]);
    }

    public function exportExcel($type = null)
    {
        $electionId = $this->request->getVar('election_id');
        $dignityId = $this->request->getVar('dignity_id');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Candidato');
        $sheet->setCellValue('B1', 'Partido');
        $sheet->setCellValue('C1', 'Lista');
        $sheet->setCellValue('D1', 'Votos');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        if ($electionId && $dignityId) {
            $results = model('App\Models\ElectionResultModel')->getResultsByDignity($electionId, $dignityId);
            $row = 2;
            foreach ($results as $r) {
                $sheet->setCellValue("A$row", $r->first_name . ' ' . $r->last_name);
                $sheet->setCellValue("B$row", $r->party_name);
                $sheet->setCellValue("C$row", $r->list_number);
                $sheet->setCellValue("D$row", $r->total_votes);
                $row++;
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="resultados.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function advanceReport()
    {
        $electionId = $this->request->getVar('election_id');
        if (!$electionId) {
            $current = model('App\Models\ElectionModel')->getCurrent();
            $electionId = $current ? $current->id : null;
        }

        $html = view('reports/advance_pdf', [
            'election' => model('App\Models\ElectionModel')->find($electionId),
            'stats' => model('App\Models\PollingStationModel')->getStats(),
            'byProvince' => $this->getProvinceStats($electionId),
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('avance_carga.pdf', ['Attachment' => false]);
    }

    private function getProvinceStats($electionId)
    {
        $db = \Config\Database::connect();
        return $db->table('polling_stations')
            ->select('name_province, COUNT(*) as total, SUM(CASE WHEN status IN ("reportada","revisada","aprobada") THEN 1 ELSE 0 END) as reportadas')
            ->where('election_id', $electionId)
            ->groupBy('name_province')
            ->orderBy('name_province')
            ->get()->getResult();
    }
}
