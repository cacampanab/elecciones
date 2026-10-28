<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExcel
{
    private const PRIMARY = '1A237E';
    private const LIGHT = 'E8EAF6';
    private const BORDER = 'D0D5DD';
    private const RED = 'FDE8E8';
    private const ORANGE = 'FFF3CD';
    private const YELLOW = 'FFF8E1';

    public function build(array $report): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Sistema de Control Electoral')
            ->setTitle('Reporte de Resultados - ' . ($report['dignity']->name ?? ''))
            ->setDescription('Reporte de resultados electorales');
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $this->summarySheet($spreadsheet->getActiveSheet(), $report);
        $this->candidatesSheet($spreadsheet->createSheet(), $report);
        $this->distributionSheet($spreadsheet->createSheet(), $report);
        $this->provincesSheet($spreadsheet->createSheet(), $report);
        $this->actasSheet($spreadsheet->createSheet(), $report);
        $this->inconsistenciesSheet($spreadsheet->createSheet(), $report);
        $this->candidateByActaSheet($spreadsheet->createSheet(), $report);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function summarySheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Resumen');
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'REPORTE DE RESULTADOS ELECTORALES');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FF' . self::PRIMARY);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $this->text($sheet, 'A2', 'Elección: ' . ($report['election']->name ?? 'N/D') . ' | Dignidad: ' . ($report['dignity']->name ?? 'N/D'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setARGB('FF555555');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->section($sheet, 'A4:D4', 'DATOS GENERALES');

        $sheet->setCellValue('A5', 'Elección');
        $this->text($sheet, 'B5', $report['election']->name ?? 'N/D');
        $sheet->mergeCells('B5:D5');

        $sheet->setCellValue('A6', 'Dignidad');
        $this->text($sheet, 'B6', $report['dignity']->name ?? 'N/D');
        $sheet->mergeCells('B6:D6');

        $sheet->setCellValue('A7', 'Fecha de corte');
        $this->text($sheet, 'B7', $report['generated_at']);
        $sheet->mergeCells('B7:D7');

        $sheet->setCellValue('A8', 'Generado por');
        $this->text($sheet, 'B8', $report['generated_by']);
        $sheet->mergeCells('B8:D8');

        $sheet->setCellValue('A9', 'Estado del informe');
        $this->text($sheet, 'B9', $report['preliminary'] ? 'PRELIMINAR' : 'INFORME');
        $sheet->mergeCells('B9:D9');
        $sheet->getStyle('B9')->getFont()->setBold(true)->getColor()->setARGB($report['preliminary'] ? 'FFB02A37' : 'FF198754');

        $this->section($sheet, 'A11:D11', 'RESUMEN EJECUTIVO');

        $sheet->setCellValue('A12', 'Indicador');
        $sheet->setCellValue('B12', 'Valor');
        $sheet->mergeCells('C12:D12');
        $sheet->setCellValue('C12', 'Detalle');
        $this->headerStyle($sheet, 'A12:D12');

        $row = 13;
        $this->kpiRow($sheet, $row++, 'Total JRV', $report['total_jrvs'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Actas esperadas', $report['expected_actas'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Actas subidas', $report['uploaded_actas'], '#,##0', $report['uploaded_actas'] . ' de ' . $report['expected_actas']);
        $this->kpiRow($sheet, $row++, 'Cobertura de actas', $report['coverage'] / 100, '0.00%', $report['uploaded_actas'] . ' / ' . $report['expected_actas']);
        $this->kpiRow($sheet, $row++, 'Actas verificadas', $report['verified_actas'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Actas con novedad', $report['novelty_actas'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Actas con inconsistencia', $report['inconsistent_actas'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Actas con resultados', $report['actas_with_results'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Actas sin resultados', $report['actas_without_results'], '#,##0');
        $this->kpiRow($sheet, $row++, 'JRV con acta', $report['with_acta_jrvs'], '#,##0');
        $this->kpiRow($sheet, $row++, 'JRV sin acta', $report['without_acta_jrvs'], '#,##0');
        $this->kpiRow($sheet, $row++, 'JRV con resultados sin acta', $report['results_without_acta'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Votos válidos', $report['valid_votes'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Votos nulos', $report['null_votes'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Votos blancos', $report['blank_votes'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Total emitido', $report['total_cast'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Sufragantes', $report['total_voters'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Electores registrados', $report['total_registered'], '#,##0');
        $this->kpiRow($sheet, $row++, 'Participación', $report['participation'] / 100, '0.00%', $report['total_voters'] . ' / ' . $report['total_registered']);
        $this->kpiRow($sheet, $row++, 'Abstención', $report['abstention'], '#,##0', number_format($report['abstention_pct'], 2, ',', '.') . '%');
        $this->kpiRow($sheet, $row++, 'Diferencia emitidos - sufragantes', $report['difference'], '+#,##0;-#,##0;0');

        $winner = $report['results'][0] ?? null;
        $this->kpiRow(
            $sheet,
            $row++,
            'Primer lugar',
            $winner ? $winner->full_name : 'Sin datos',
            '@',
            $winner ? number_format($winner->total_votes, 0, ',', '.') . ' votos (' . number_format($winner->pct_valid, 2, ',', '.') . '%)' : ''
        );

        $row++;
        $this->section($sheet, 'A' . $row . ':D' . $row, 'OBSERVACIONES');
        $row++;

        foreach ($report['notes'] as $note) {
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $this->text($sheet, 'A' . $row, '- ' . $note);
            $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(34);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(28);
        $this->tableBorders($sheet, 'A5:D' . ($row - 1));
    }

    private function candidatesSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Candidatos');
        $headers = ['#', 'Candidato', 'Partido', 'Siglas', 'Lista', 'Votos', '% Válidos', '% Sufragantes', '% Total emitido', 'Activo'];
        $this->writeHeader($sheet, $headers);

        $row = 2;
        $index = 1;
        foreach ($report['results'] as $result) {
            $sheet->setCellValue('A' . $row, $index);
            $this->text($sheet, 'B' . $row, $result->full_name);
            $this->text($sheet, 'C' . $row, $result->party_name);
            $this->text($sheet, 'D' . $row, $result->party_acronym);
            $this->text($sheet, 'E' . $row, $result->list_number);
            $sheet->setCellValue('F' . $row, $result->total_votes);
            $sheet->setCellValue('G' . $row, $result->pct_valid / 100);
            $sheet->setCellValue('H' . $row, $result->pct_voters / 100);
            $sheet->setCellValue('I' . $row, $result->pct_cast / 100);
            $this->text($sheet, 'J' . $row, ((int)$result->is_active === 1) ? 'Sí' : 'No');

            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('0.00%');

            if ($index === 1 && (int)$result->total_votes > 0) {
                $this->fillRange($sheet, 'A' . $row . ':J' . $row, self::YELLOW);
                $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
            }

            $row++;
            $index++;
        }

        if ($row > 2) {
            $this->text($sheet, 'B' . $row, 'TOTAL');
            $sheet->setCellValue('F' . $row, $report['valid_votes']);
            $sheet->setCellValue('G' . $row, $report['valid_votes'] > 0 ? 1 : 0);
            $sheet->setCellValue('H' . $row, $report['total_voters'] > 0 ? $report['valid_votes'] / $report['total_voters'] : 0);
            $sheet->setCellValue('I' . $row, $report['total_cast'] > 0 ? $report['valid_votes'] / $report['total_cast'] : 0);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
            $this->fillRange($sheet, 'A' . $row . ':J' . $row, self::LIGHT);
        }

        $lastRow = max(1, $row);
        $this->styleTable($sheet, 'A2:J' . $lastRow);
        $sheet->setAutoFilter('A1:J' . $lastRow);
        $sheet->freezePane('A2');
        $this->setWidths($sheet, ['A' => 6, 'B' => 30, 'C' => 34, 'D' => 12, 'E' => 10, 'F' => 12, 'G' => 13, 'H' => 15, 'I' => 17, 'J' => 10]);
    }

    private function distributionSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Distribución');
        $this->section($sheet, 'A1:D1', 'DISTRIBUCIÓN DE VOTOS');

        $headers = ['Concepto', 'Votos', '% Total emitido', '% Sufragantes'];
        $this->writeHeader($sheet, $headers, 3);

        $rows = [
            ['Válidos', $report['valid_votes'], $report['total_cast'] > 0 ? $report['valid_votes'] / $report['total_cast'] : 0, $report['total_voters'] > 0 ? $report['valid_votes'] / $report['total_voters'] : 0],
            ['Nulos', $report['null_votes'], $report['total_cast'] > 0 ? $report['null_votes'] / $report['total_cast'] : 0, $report['total_voters'] > 0 ? $report['null_votes'] / $report['total_voters'] : 0],
            ['Blancos', $report['blank_votes'], $report['total_cast'] > 0 ? $report['blank_votes'] / $report['total_cast'] : 0, $report['total_voters'] > 0 ? $report['blank_votes'] / $report['total_voters'] : 0],
            ['Total emitido', $report['total_cast'], $report['total_cast'] > 0 ? 1 : 0, $report['total_voters'] > 0 ? $report['total_cast'] / $report['total_voters'] : 0],
            ['Sufragantes', $report['total_voters'], $report['total_cast'] > 0 ? $report['total_voters'] / $report['total_cast'] : 0, $report['total_voters'] > 0 ? 1 : 0],
        ];

        $row = 4;
        foreach ($rows as $values) {
            $sheet->setCellValue('A' . $row, $values[0]);
            $sheet->setCellValue('B' . $row, $values[1]);
            $sheet->setCellValue('C' . $row, $values[2]);
            $sheet->setCellValue('D' . $row, $values[3]);
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('0.00%');
            $row++;
        }

        $sheet->getStyle('A8:D8')->getFont()->setBold(true);
        $this->fillRange($sheet, 'A8:D8', self::LIGHT);

        $row += 2;
        $this->section($sheet, 'A' . $row . ':D' . $row, 'PARTICIPACIÓN');
        $row++;

        $sheet->setCellValue('A' . $row, 'Indicador');
        $sheet->setCellValue('B' . $row, 'Valor');
        $sheet->mergeCells('C' . $row . ':D' . $row);
        $sheet->setCellValue('C' . $row, 'Porcentaje');
        $this->headerStyle($sheet, 'A' . $row . ':D' . $row);
        $row++;

        $participationRows = [
            ['Electores registrados', $report['total_registered'], null],
            ['Sufragantes', $report['total_voters'], $report['total_registered'] > 0 ? $report['total_voters'] / $report['total_registered'] : 0],
            ['Participación', $report['total_voters'], $report['participation'] / 100],
            ['Abstención', $report['abstention'], $report['abstention_pct'] / 100],
            ['Diferencia emitidos - sufragantes', $report['difference'], null],
        ];

        foreach ($participationRows as $values) {
            $sheet->setCellValue('A' . $row, $values[0]);
            $sheet->setCellValue('B' . $row, $values[1]);
            if ($values[2] !== null) {
                $sheet->setCellValue('C' . $row, $values[2]);
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('0.00%');
            }
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('+#,##0;-#,##0;0');
            $row++;
        }

        $this->styleTable($sheet, 'A4:D8');
        $this->styleTable($sheet, 'A12:D' . ($row - 1));
        $this->setWidths($sheet, ['A' => 38, 'B' => 18, 'C' => 20, 'D' => 20]);
    }

    private function provincesSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Provincias');
        $headers = ['Provincia', 'JRV', 'Actas subidas', 'Cobertura', 'Actas con resultados', 'Válidos', 'Nulos', 'Blancos', 'Sufragantes', 'Electores', 'Participación', 'Inconsistencias'];
        $this->writeHeader($sheet, $headers);

        $row = 2;
        foreach ($report['provinces'] as $province) {
            $this->text($sheet, 'A' . $row, $province->name_province);
            $sheet->setCellValue('B' . $row, $province->total_jrvs);
            $sheet->setCellValue('C' . $row, $province->uploaded_actas);
            $sheet->setCellValue('D' . $row, $province->coverage / 100);
            $sheet->setCellValue('E' . $row, $province->actas_with_results);
            $sheet->setCellValue('F' . $row, $province->valid_votes);
            $sheet->setCellValue('G' . $row, $province->null_votes);
            $sheet->setCellValue('H' . $row, $province->blank_votes);
            $sheet->setCellValue('I' . $row, $province->total_voters);
            $sheet->setCellValue('J' . $row, $province->registered_voters);
            $sheet->setCellValue('K' . $row, $province->participation / 100);
            $sheet->setCellValue('L' . $row, $province->inconsistent_actas);

            $sheet->getStyle('B' . $row . ':C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle('E' . $row . ':J' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        if ($row > 2) {
            $this->text($sheet, 'A' . $row, 'TOTAL');
            $sheet->setCellValue('B' . $row, $report['total_jrvs']);
            $sheet->setCellValue('C' . $row, $report['uploaded_actas']);
            $sheet->setCellValue('D' . $row, $report['coverage'] / 100);
            $sheet->setCellValue('E' . $row, $report['actas_with_results']);
            $sheet->setCellValue('F' . $row, $report['valid_votes']);
            $sheet->setCellValue('G' . $row, $report['null_votes']);
            $sheet->setCellValue('H' . $row, $report['blank_votes']);
            $sheet->setCellValue('I' . $row, $report['total_voters']);
            $sheet->setCellValue('J' . $row, $report['total_registered']);
            $sheet->setCellValue('K' . $row, $report['participation'] / 100);
            $sheet->setCellValue('L' . $row, $report['inconsistent_actas']);
            $sheet->getStyle('B' . $row . ':C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle('E' . $row . ':J' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $row . ':L' . $row)->getFont()->setBold(true);
            $this->fillRange($sheet, 'A' . $row . ':L' . $row, self::LIGHT);
        }

        $lastRow = max(1, $row);
        $this->styleTable($sheet, 'A2:L' . $lastRow);
        $sheet->setAutoFilter('A1:L' . $lastRow);
        $sheet->freezePane('A2');
        $this->setWidths($sheet, ['A' => 26, 'B' => 9, 'C' => 13, 'D' => 11, 'E' => 19, 'F' => 13, 'G' => 11, 'H' => 11, 'I' => 13, 'J' => 12, 'K' => 13, 'L' => 14]);
    }

    private function actasSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Actas');
        $headers = [
            'Provincia', 'Cantón', 'Parroquia', 'Recinto', 'JRV', 'Género',
            'Estado acta', 'Consistencia', 'Detalle consistencia', 'Subida',
            'Registrada por', 'Electores', 'Sufragantes', 'Válidos', 'Nulos',
            'Blancos', 'Total emitido', 'Diferencia', 'Con resultados',
        ];
        $this->writeHeader($sheet, $headers);

        $row = 2;
        foreach ($report['actas'] as $acta) {
            $this->text($sheet, 'A' . $row, $acta->name_province);
            $this->text($sheet, 'B' . $row, $acta->name_canton);
            $this->text($sheet, 'C' . $row, $acta->name_parish);
            $this->text($sheet, 'D' . $row, $acta->name_precinct);
            $this->text($sheet, 'E' . $row, $acta->numero_junta);
            $this->text($sheet, 'F' . $row, $acta->genero);
            $this->text($sheet, 'G' . $row, $acta->status);
            $this->text($sheet, 'H' . $row, $acta->consistency_status);
            $this->text($sheet, 'I' . $row, $acta->consistency_detail);
            $this->text($sheet, 'J' . $row, $acta->created_at);
            $this->text($sheet, 'K' . $row, $acta->uploaded_by);
            $sheet->setCellValue('L' . $row, $acta->registered_effective);
            $sheet->setCellValue('M' . $row, $acta->total_voters);
            $sheet->setCellValue('N' . $row, $acta->valid_votes);
            $sheet->setCellValue('O' . $row, $acta->null_votes);
            $sheet->setCellValue('P' . $row, $acta->blank_votes);
            $sheet->setCellValue('Q' . $row, $acta->total_cast);
            $sheet->setCellValue('R' . $row, $acta->difference);
            $this->text($sheet, 'S' . $row, $acta->has_results ? 'Sí' : 'No');

            $sheet->getStyle('L' . $row . ':Q' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('R' . $row)->getNumberFormat()->setFormatCode('+#,##0;-#,##0;0');

            if ($acta->consistency_status === 'error') {
                $this->fillRange($sheet, 'A' . $row . ':S' . $row, self::RED);
            } elseif ($acta->status === 'con_novedad') {
                $this->fillRange($sheet, 'A' . $row . ':S' . $row, self::ORANGE);
            }

            $row++;
        }

        $lastRow = max(1, $row - 1);
        $this->styleTable($sheet, 'A2:S' . $lastRow);
        $sheet->setAutoFilter('A1:S' . $lastRow);
        $sheet->freezePane('A2');
        $this->setWidths($sheet, ['A' => 18, 'B' => 18, 'C' => 18, 'D' => 24, 'E' => 10, 'F' => 9, 'G' => 13, 'H' => 14, 'I' => 46, 'J' => 19, 'K' => 26, 'L' => 11, 'M' => 12, 'N' => 10, 'O' => 10, 'P' => 10, 'Q' => 13, 'R' => 11, 'S' => 13]);
    }

    private function inconsistenciesSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Inconsistencias');
        $headers = [
            'Provincia', 'Cantón', 'Parroquia', 'Recinto', 'JRV', 'Género',
            'Estado acta', 'Consistencia', 'Detalle', 'Notas', 'Subida',
            'Registrada por', 'Versión',
        ];
        $this->writeHeader($sheet, $headers);

        $row = 2;
        foreach ($report['inconsistencies'] as $acta) {
            $this->text($sheet, 'A' . $row, $acta->name_province);
            $this->text($sheet, 'B' . $row, $acta->name_canton);
            $this->text($sheet, 'C' . $row, $acta->name_parish);
            $this->text($sheet, 'D' . $row, $acta->name_precinct);
            $this->text($sheet, 'E' . $row, $acta->numero_junta);
            $this->text($sheet, 'F' . $row, $acta->genero);
            $this->text($sheet, 'G' . $row, $acta->status);
            $this->text($sheet, 'H' . $row, $acta->consistency_status);
            $this->text($sheet, 'I' . $row, $acta->consistency_detail);
            $this->text($sheet, 'J' . $row, $acta->notes);
            $this->text($sheet, 'K' . $row, $acta->created_at);
            $this->text($sheet, 'L' . $row, $acta->uploaded_by);
            $sheet->setCellValue('M' . $row, $acta->version);

            $this->fillRange($sheet, 'A' . $row . ':M' . $row, self::RED);
            $row++;
        }

        $lastRow = max(1, $row - 1);
        $this->styleTable($sheet, 'A2:M' . $lastRow);
        $sheet->setAutoFilter('A1:M' . $lastRow);
        $sheet->freezePane('A2');
        $this->setWidths($sheet, ['A' => 18, 'B' => 18, 'C' => 18, 'D' => 24, 'E' => 10, 'F' => 9, 'G' => 13, 'H' => 14, 'I' => 55, 'J' => 30, 'K' => 19, 'L' => 26, 'M' => 9]);
    }

    private function candidateByActaSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Candidatos por acta');
        $headers = [
            'Provincia', 'Cantón', 'Parroquia', 'Recinto', 'JRV', 'Género',
            'Estado acta', 'Consistencia', 'Subida', 'Electores', 'Sufragantes',
            'Válidos', 'Nulos', 'Blancos', 'Total emitido',
        ];

        $candidateColumns = [];
        foreach ($report['candidates'] as $index => $candidate) {
            $label = $candidate->full_name;
            if (!empty($candidate->party_acronym)) {
                $label .= ' (' . $candidate->party_acronym . ')';
            }
            $headers[] = $label;
            $candidateColumns[] = (int)$candidate->id;
        }

        $this->writeHeader($sheet, $headers);

        $row = 2;
        foreach ($report['actas'] as $acta) {
            $this->text($sheet, 'A' . $row, $acta->name_province);
            $this->text($sheet, 'B' . $row, $acta->name_canton);
            $this->text($sheet, 'C' . $row, $acta->name_parish);
            $this->text($sheet, 'D' . $row, $acta->name_precinct);
            $this->text($sheet, 'E' . $row, $acta->numero_junta);
            $this->text($sheet, 'F' . $row, $acta->genero);
            $this->text($sheet, 'G' . $row, $acta->status);
            $this->text($sheet, 'H' . $row, $acta->consistency_status);
            $this->text($sheet, 'I' . $row, $acta->created_at);
            $sheet->setCellValue('J' . $row, $acta->registered_effective);
            $sheet->setCellValue('K' . $row, $acta->total_voters);
            $sheet->setCellValue('L' . $row, $acta->valid_votes);
            $sheet->setCellValue('M' . $row, $acta->null_votes);
            $sheet->setCellValue('N' . $row, $acta->blank_votes);
            $sheet->setCellValue('O' . $row, $acta->total_cast);

            $col = 16;
            foreach ($candidateColumns as $candidateId) {
                $column = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($column . $row, $acta->candidate_votes[$candidateId] ?? 0);
                $col++;
            }

            $sheet->getStyle('J' . $row . ':O' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $lastRow = max(1, $row - 1);
        if ($row > 2) {
            $totalRow = $row;
            $this->text($sheet, 'A' . $totalRow, 'TOTAL');
            $sheet->setCellValue('J' . $totalRow, $report['total_registered']);
            $sheet->setCellValue('K' . $totalRow, $report['total_voters']);
            $sheet->setCellValue('L' . $totalRow, $report['valid_votes']);
            $sheet->setCellValue('M' . $totalRow, $report['null_votes']);
            $sheet->setCellValue('N' . $totalRow, $report['blank_votes']);
            $sheet->setCellValue('O' . $totalRow, $report['total_cast']);

            $col = 16;
            foreach ($report['results'] as $result) {
                $column = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($column . $totalRow, $result->total_votes);
                $col++;
            }

            $lastCol = Coordinate::stringFromColumnIndex(15 + max(1, count($candidateColumns)));
            $sheet->getStyle('A' . $totalRow . ':' . $lastCol . $totalRow)->getFont()->setBold(true);
            $this->fillRange($sheet, 'A' . $totalRow . ':' . $lastCol . $totalRow, self::LIGHT);
            $lastRow = $totalRow;
        }

        $lastCol = Coordinate::stringFromColumnIndex(15 + max(1, count($candidateColumns)));
        $this->styleTable($sheet, 'A2:' . $lastCol . $lastRow);
        $sheet->setAutoFilter('A1:' . $lastCol . $lastRow);
        $sheet->freezePane('A2');
        $widths = ['A' => 18, 'B' => 18, 'C' => 18, 'D' => 24, 'E' => 10, 'F' => 9, 'G' => 13, 'H' => 14, 'I' => 19, 'J' => 11, 'K' => 12, 'L' => 10, 'M' => 10, 'N' => 10, 'O' => 13];
        $this->setWidths($sheet, $widths);
        for ($i = 16; $i <= 15 + count($candidateColumns); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(16);
        }
    }

    private function text(Worksheet $sheet, string $coordinate, mixed $value): void
    {
        $sheet->setCellValueExplicit($coordinate, (string)$value, DataType::TYPE_STRING);
    }

    private function kpiRow(Worksheet $sheet, int $row, string $label, mixed $value, string $format, string $detail = ''): void
    {
        $this->text($sheet, 'A' . $row, $label);
        if ($format === '@') {
            $this->text($sheet, 'B' . $row, $value);
        } else {
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode($format);
        }
        $sheet->mergeCells('C' . $row . ':D' . $row);
        $this->text($sheet, 'C' . $row, $detail);
        $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
    }

    private function writeHeader(Worksheet $sheet, array $headers, int $row = 1): void
    {
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $this->text($sheet, $column . $row, $header);
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $this->headerStyle($sheet, 'A' . $row . ':' . $lastColumn . $row);
        $sheet->getRowDimension($row)->setRowHeight(28);
    }

    private function section(Worksheet $sheet, string $range, string $title): void
    {
        $sheet->mergeCells($range);
        $start = strstr($range, ':', true);
        $sheet->setCellValue($start, $title);
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . self::PRIMARY);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension((int)preg_replace('/\D+/', '', $start))->setRowHeight(22);
    }

    private function headerStyle(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . self::PRIMARY);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $this->tableBorders($sheet, $range);
    }

    private function styleTable(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF' . self::BORDER);
    }

    private function tableBorders(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF' . self::BORDER);
    }

    private function fillRange(Worksheet $sheet, string $range, string $color): void
    {
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . $color);
    }

    private function setWidths(Worksheet $sheet, array $widths): void
    {
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }
}
