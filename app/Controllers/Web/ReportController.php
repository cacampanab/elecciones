<?php

namespace App\Controllers\Web;

use App\Libraries\ActaService;
use App\Libraries\ReportExcel;
use App\Libraries\ReportService;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requirePermission('reports.generate');
        if ($redirect) return $redirect;

        $electionModel = model('App\Models\ElectionModel');
        $elections = $electionModel->getActive();

        $electionId = (int)($this->request->getGet('election_id') ?? 0);
        $validElectionIds = array_map(static fn($election) => (int)$election->id, $elections);
        if (!$electionId || !in_array($electionId, $validElectionIds, true)) {
            $current = $electionModel->getCurrent();
            $electionId = $current ? (int)$current->id : (int)($elections[0]->id ?? 0);
        }

        $dignityIds = $electionId ? (new ActaService())->enabledDignityIds($electionId) : [];
        $dignities = $dignityIds
            ? model('App\Models\DignityModel')->whereIn('id', $dignityIds)->orderBy('name', 'ASC')->findAll()
            : [];

        $dignityId = (int)($this->request->getGet('dignity_id') ?? 0);
        $validDignityIds = array_map(static fn($dignity) => (int)$dignity->id, $dignities);
        if ($dignityId && !in_array($dignityId, $validDignityIds, true)) {
            $dignityId = 0;
        }

        return $this->render('reports/index', compact(
            'elections',
            'dignities',
            'electionId',
            'dignityId'
        ), 'Reportes');
    }

    public function exportPdf()
    {
        $redirect = $this->requirePermission('reports.export_pdf');
        if ($redirect) return $redirect;

        [$electionId, $dignityId] = $this->reportIds();
        if (!$electionId || !$dignityId) {
            return redirect()->to(base_url('reports'))->with('error', 'Seleccione una elección y una dignidad.');
        }

        try {
            $report = (new ReportService())->build($electionId, $dignityId, $this->currentUserName());
            $html = view('reports/results_pdf', ['report' => $report]);

            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $this->addPageNumbers($dompdf);

            $dompdf->stream($this->fileName($report, 'pdf'), ['Attachment' => false]);
            exit;
        } catch (\Throwable $e) {
            log_message('error', 'Error generando PDF de resultados: ' . $e->getMessage());
            return redirect()->to(base_url('reports'))->with('error', $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $redirect = $this->requirePermission('reports.export_excel');
        if ($redirect) return $redirect;

        [$electionId, $dignityId] = $this->reportIds();
        if (!$electionId || !$dignityId) {
            return redirect()->to(base_url('reports'))->with('error', 'Seleccione una elección y una dignidad.');
        }

        try {
            $report = (new ReportService())->build($electionId, $dignityId, $this->currentUserName());
            $spreadsheet = (new ReportExcel())->build($report);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $this->fileName($report, 'xlsx') . '"');
            header('Cache-Control: max-age=0');

            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
            exit;
        } catch (\Throwable $e) {
            log_message('error', 'Error generando Excel de resultados: ' . $e->getMessage());
            return redirect()->to(base_url('reports'))->with('error', $e->getMessage());
        }
    }

    public function advance()
    {
        $redirect = $this->requirePermission('reports.export_pdf');
        if ($redirect) return $redirect;

        $electionId = (int)($this->request->getGet('election_id') ?? 0);
        if (!$electionId) {
            $current = model('App\Models\ElectionModel')->getCurrent();
            $electionId = $current ? (int)$current->id : null;
        }

        $db = \Config\Database::connect();
        $byProvince = $db->table('polling_stations ps')
            ->select('ps.name_province')
            ->select('COUNT(DISTINCT ps.id) AS total', false)
            ->select('COUNT(DISTINCT um.polling_station_id) AS reportadas', false)
            ->select('COUNT(um.id) AS actas', false)
            ->join('uploaded_minutes um', 'um.polling_station_id = ps.id AND um.election_id = ps.election_id AND um.is_current = 1', 'left')
            ->where('ps.election_id', $electionId)
            ->groupBy('ps.name_province')
            ->orderBy('ps.name_province')
            ->get()->getResult();

        $stats = ['total' => 0, 'reported' => 0, 'pending' => 0, 'actas' => 0];
        foreach ($byProvince as $province) {
            $province->total = (int)$province->total;
            $province->reportadas = (int)$province->reportadas;
            $province->actas = (int)$province->actas;
            $province->pendientes = $province->total - $province->reportadas;
            $province->avance = $province->total > 0 ? round(($province->reportadas / $province->total) * 100, 1) : 0;
            $stats['total'] += $province->total;
            $stats['reported'] += $province->reportadas;
            $stats['pending'] += $province->pendientes;
            $stats['actas'] += $province->actas;
        }

        $election = model('App\Models\ElectionModel')->find($electionId);

        $html = view('reports/advance_pdf', compact('election', 'stats', 'byProvince'));
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('avance_carga.pdf', ['Attachment' => false]);
        exit;
    }

    private function reportIds(): array
    {
        return [
            (int)($this->request->getGet('election_id') ?? 0),
            (int)($this->request->getGet('dignity_id') ?? 0),
        ];
    }

    private function currentUserName(): string
    {
        $name = trim((string)session()->get('first_name') . ' ' . (string)session()->get('last_name'));

        return $name !== '' ? $name : (string)(session()->get('username') ?: 'N/D');
    }

    private function fileName(array $report, string $extension): string
    {
        $dignity = $this->slug((string)($report['dignity']->name ?? 'reporte'));

        return 'resultados_' . $dignity . '_' . date('Ymd_His') . '.' . $extension;
    }

    private function slug(string $value): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = strtolower((string)$ascii);
        $ascii = preg_replace('/[^a-z0-9]+/', '_', $ascii);

        return trim((string)$ascii, '_') ?: 'reporte';
    }

    private function addPageNumbers(Dompdf $dompdf): void
    {
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');

        $canvas->page_text(
            690,
            567,
            'Página {PAGE_NUM} de {PAGE_COUNT}',
            $font,
            7.5,
            [0.45, 0.48, 0.55]
        );
    }
}
