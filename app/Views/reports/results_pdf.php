<?php
$election = $report['election'];
$dignity = $report['dignity'];
$results = $report['results'];
$fmt = static fn($value) => number_format((float)$value, 0, ',', '.');
$pct = static fn($value) => number_format((float)$value, 2, ',', '.') . '%';
$maxVotes = max(1, (int)$report['max_votes']);
$maxCast = max(1, (int)$report['total_cast']);
$electionDate = !empty($election->election_date) ? date('d/m/Y', strtotime((string)$election->election_date)) : 'N/D';
$distribution = [
    [
        'label' => 'Válidos',
        'votes' => (int)$report['valid_votes'],
        'width' => (int)$report['total_cast'] > 0 ? round(((int)$report['valid_votes'] / (int)$report['total_cast']) * 100, 2) : 0,
        'share' => (int)$report['total_cast'] > 0 ? ((int)$report['valid_votes'] / (int)$report['total_cast']) * 100 : 0,
        'color' => '#198754',
    ],
    [
        'label' => 'Nulos',
        'votes' => (int)$report['null_votes'],
        'width' => (int)$report['total_cast'] > 0 ? round(((int)$report['null_votes'] / (int)$report['total_cast']) * 100, 2) : 0,
        'share' => (int)$report['total_cast'] > 0 ? ((int)$report['null_votes'] / (int)$report['total_cast']) * 100 : 0,
        'color' => '#dc3545',
    ],
    [
        'label' => 'Blancos',
        'votes' => (int)$report['blank_votes'],
        'width' => (int)$report['total_cast'] > 0 ? round(((int)$report['blank_votes'] / (int)$report['total_cast']) * 100, 2) : 0,
        'share' => (int)$report['total_cast'] > 0 ? ((int)$report['blank_votes'] / (int)$report['total_cast']) * 100 : 0,
        'color' => '#6c757d',
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte de Resultados - <?= esc($dignity->name) ?></title>
<style>
@page { margin: 22pt 24pt 28pt 24pt; }
* { box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5pt; color: #2d3748; line-height: 1.35; }
h1 { margin: 0; font-size: 18pt; line-height: 1.1; color: #1a237e; letter-spacing: -0.2pt; }
h2 { margin: 0 0 7pt; padding-bottom: 3pt; border-bottom: 1pt solid #1a237e; color: #1a237e; font-size: 10.5pt; text-transform: uppercase; letter-spacing: 0.4pt; page-break-after: avoid; }
h3 { margin: 0 0 5pt; color: #2d3748; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.3pt; }
p { margin: 0 0 5pt; }
strong { color: #1a237e; }
.topline { height: 4pt; background: #1a237e; margin-bottom: 10pt; }
.report-header { width: 100%; border-collapse: collapse; margin-bottom: 7pt; }
.report-header td { vertical-align: top; border: 0; padding: 0; }
.header-left { width: 65%; padding-right: 18pt !important; }
.header-right { width: 35%; text-align: right; }
.eyebrow { color: #718096; font-size: 6.8pt; letter-spacing: 1.1pt; text-transform: uppercase; margin-bottom: 4pt; }
.subtitle { margin-top: 4pt; color: #4a5568; font-size: 10pt; }
.status-badge { display: inline-block; padding: 3pt 7pt; border: 0.6pt solid #d9c26a; background: #fff8e1; color: #8a6d3b; font-size: 6.8pt; font-weight: bold; letter-spacing: 0.4pt; text-transform: uppercase; }
.status-badge.is-final { border-color: #a9d6ae; background: #e8f5e9; color: #1b5e20; }
.meta { margin-top: 5pt; color: #718096; font-size: 6.8pt; line-height: 1.45; }
.meta strong { color: #2d3748; font-size: 7.2pt; }
.section { margin-top: 11pt; }
.kpi-table { width: 100%; border-collapse: separate; border-spacing: 4pt; margin: 0 -4pt; }
.kpi-table td { width: 25%; vertical-align: top; padding: 0; }
.kpi { background: #f7f8fc; border: 0.6pt solid #dde3ee; border-left: 3pt solid #1a237e; padding: 6pt 7pt; min-height: 38pt; }
.kpi .label { color: #5a6275; font-size: 6.7pt; letter-spacing: 0.3pt; text-transform: uppercase; }
.kpi .value { color: #1a237e; font-size: 13pt; font-weight: bold; margin-top: 2pt; line-height: 1.1; }
.kpi .hint { color: #718096; font-size: 6.6pt; margin-top: 1pt; }
.bar-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.bar-table td { vertical-align: middle; padding: 3.5pt 0; border-bottom: 0.5pt solid #edf0f6; }
.bar-table tr:last-child td { border-bottom: 0; }
.bar-label { padding-right: 8pt !important; font-size: 7.4pt; }
.bar-label .sub { display: block; color: #718096; font-size: 6.5pt; margin-top: 1pt; }
.bar-cell { padding-right: 8pt !important; }
.track { width: 100%; height: 12pt; background: #edf0f7; border: 0.5pt solid #dbe2ef; }
.bar { height: 11pt; background: #1a237e; }
.bar-value { width: 20%; text-align: right; padding-left: 8pt !important; font-size: 7.4pt; white-space: nowrap; }
.bar-value .sub { display: block; color: #718096; font-size: 6.5pt; margin-top: 1pt; }
.data-table { width: 100%; border-collapse: collapse; margin-top: 4pt; }
.data-table th { padding: 4pt 4pt; background: #1a237e; color: #ffffff; font-size: 6.6pt; font-weight: bold; text-align: left; text-transform: uppercase; letter-spacing: 0.2pt; vertical-align: middle; }
.data-table td { padding: 3.5pt 4pt; border-bottom: 0.5pt solid #e2e8f0; font-size: 7.2pt; vertical-align: top; }
.data-table tbody tr:nth-child(even) td { background: #fafbfd; }
.data-table tbody tr.rank-1 td { background: #fff8e1; font-weight: bold; }
.data-table tfoot td { padding: 4pt; background: #eef1f8; border-top: 1pt solid #1a237e; border-bottom: 0; font-weight: bold; font-size: 7.2pt; }
.data-table.compact td { padding: 3pt 4pt; }
.data-table.provinces td, .data-table.provinces th { padding: 3pt 3pt; font-size: 6.7pt; }
.right { text-align: right !important; }
.center { text-align: center !important; }
.muted { color: #718096; }
.two-col { width: 100%; border-collapse: collapse; }
.two-col > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }
.two-col > tbody > tr > td:first-child { padding-right: 8pt; }
.two-col > tbody > tr > td:last-child { padding-left: 8pt; }
.panel { background: #fbfcff; border: 0.6pt solid #dde3ee; padding: 7pt 8pt; }
.summary-list { width: 100%; border-collapse: collapse; }
.summary-list td { padding: 3pt 0; border-bottom: 0.5pt dashed #e2e8f0; font-size: 7.3pt; }
.summary-list tr:last-child td { border-bottom: 0; }
.summary-list td:last-child { text-align: right; font-weight: bold; color: #1a237e; }
.summary-cards { width: 100%; border-collapse: separate; border-spacing: 4pt; margin: 0 -4pt 4pt; }
.summary-cards td { width: 33.33%; padding: 0; vertical-align: top; }
.summary-card { padding: 6pt 7pt; background: #f7f8fc; border: 0.6pt solid #dde3ee; border-left: 3pt solid #1a237e; }
.summary-card .label { color: #5a6275; font-size: 6.6pt; text-transform: uppercase; letter-spacing: 0.3pt; }
.summary-card .value { color: #1a237e; font-size: 13pt; font-weight: bold; margin-top: 2pt; }
.summary-card.error { border-left-color: #dc3545; }
.summary-card.error .value { color: #b02a37; }
.summary-card.novelty { border-left-color: #fd7e14; }
.summary-card.novelty .value { color: #a85b0b; }
.empty { padding: 10pt; background: #f7f8fc; border: 0.6pt solid #dde3ee; color: #718096; text-align: center; font-size: 7.5pt; }
.note-list { margin: 0; padding-left: 12pt; }
.note-list li { margin-bottom: 3pt; font-size: 7.4pt; }
.method { margin-top: 6pt; padding: 6pt 8pt; background: #f7f8fc; border-left: 2pt solid #1a237e; color: #4a5568; font-size: 7pt; }
.footer { margin-top: 12pt; padding-top: 5pt; border-top: 0.5pt solid #cbd5e0; color: #718096; font-size: 6.6pt; text-align: center; }
.avoid { page-break-inside: avoid; }
</style>
</head>
<body>
<div class="topline"></div>

<table class="report-header">
<tbody>
<tr>
<td class="header-left">
<div class="eyebrow">Sistema de Control Electoral</div>
<h1>Reporte de Resultados</h1>
<div class="subtitle"><strong><?= esc($election->name ?? 'N/D') ?></strong> &nbsp;·&nbsp; <?= esc($dignity->name ?? 'N/D') ?></div>
</td>
<td class="header-right">
<div class="status-badge <?= $report['preliminary'] ? '' : 'is-final' ?>"><?= $report['preliminary'] ? 'Informe preliminar' : 'Informe final' ?></div>
<div class="meta">Fecha de elección<br><strong><?= esc($electionDate) ?></strong></div>
<div class="meta">Fecha de corte<br><strong><?= esc($report['generated_at']) ?></strong></div>
<div class="meta">Generado por<br><strong><?= esc($report['generated_by']) ?></strong></div>
</td>
</tr>
</tbody>
</table>

<table class="kpi-table">
<tbody>
<tr>
<td><div class="kpi"><div class="label">Actas subidas</div><div class="value"><?= $fmt($report['uploaded_actas']) ?></div><div class="hint">de <?= $fmt($report['expected_actas']) ?> esperadas</div></div></td>
<td><div class="kpi"><div class="label">Cobertura</div><div class="value"><?= $pct($report['coverage']) ?></div><div class="hint">sobre JRV registradas</div></div></td>
<td><div class="kpi"><div class="label">Actas con resultados</div><div class="value"><?= $fmt($report['actas_with_results']) ?></div><div class="hint">de <?= $fmt($report['uploaded_actas']) ?> actas vigentes</div></div></td>
<td><div class="kpi"><div class="label">Participación</div><div class="value"><?= $pct($report['participation']) ?></div><div class="hint"><?= $fmt($report['total_voters']) ?> de <?= $fmt($report['total_registered']) ?> electores</div></div></td>
</tr>
<tr>
<td><div class="kpi"><div class="label">Votos válidos</div><div class="value"><?= $fmt($report['valid_votes']) ?></div><div class="hint"><?= $pct($report['total_cast'] > 0 ? ($report['valid_votes'] / $report['total_cast']) * 100 : 0) ?> del total emitido</div></div></td>
<td><div class="kpi"><div class="label">Votos nulos</div><div class="value"><?= $fmt($report['null_votes']) ?></div><div class="hint"><?= $pct($report['total_cast'] > 0 ? ($report['null_votes'] / $report['total_cast']) * 100 : 0) ?> del total emitido</div></div></td>
<td><div class="kpi"><div class="label">Votos blancos</div><div class="value"><?= $fmt($report['blank_votes']) ?></div><div class="hint"><?= $pct($report['total_cast'] > 0 ? ($report['blank_votes'] / $report['total_cast']) * 100 : 0) ?> del total emitido</div></div></td>
<td><div class="kpi"><div class="label">Total emitido</div><div class="value"><?= $fmt($report['total_cast']) ?></div><div class="hint">válidos + nulos + blancos</div></div></td>
</tr>
</tbody>
</table>

<div class="section">
<h2>Resultados por candidato</h2>
<?php if ($results): ?>
<table class="bar-table">
<colgroup><col style="width: 37%"><col style="width: 48%"><col style="width: 15%"></colgroup>
<tbody>
<?php foreach ($results as $result): ?>
<?php $barWidth = $maxVotes > 0 ? min(100, round(((int)$result->total_votes / $maxVotes) * 100, 2)) : 0; ?>
<tr>
<td class="bar-label">
<strong><?= esc($result->full_name) ?></strong>
<span class="sub"><?= esc($result->party_name) ?><?= !empty($result->party_acronym) ? ' (' . esc($result->party_acronym) . ')' : '' ?> · Lista <?= esc($result->list_number ?: 'N/D') ?></span>
</td>
<td class="bar-cell"><div class="track"><div class="bar" style="width: <?= $barWidth ?>%; background: <?= esc($result->color, 'attr') ?>;"></div></div></td>
<td class="bar-value">
<?= $fmt($result->total_votes) ?> votos
<span class="sub"><?= $pct($result->pct_valid) ?> válidos</span>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<table class="data-table">
<thead>
<tr>
<th style="width: 5%">#</th>
<th style="width: 33%">Candidato</th>
<th style="width: 29%">Partido / lista</th>
<th class="right" style="width: 11%">Votos</th>
<th class="right" style="width: 11%">% válidos</th>
<th class="right" style="width: 11%">% sufragantes</th>
</tr>
</thead>
<tbody>
<?php foreach ($results as $index => $result): ?>
<tr class="<?= $index === 0 && (int)$result->total_votes > 0 ? 'rank-1' : '' ?>">
<td><?= $index + 1 ?></td>
<td><?= esc($result->full_name) ?></td>
<td><?= esc($result->party_name) ?><?= !empty($result->party_acronym) ? ' (' . esc($result->party_acronym) . ')' : '' ?> · <?= esc($result->list_number ?: 'N/D') ?></td>
<td class="right"><?= $fmt($result->total_votes) ?></td>
<td class="right"><?= $pct($result->pct_valid) ?></td>
<td class="right"><?= $pct($result->pct_voters) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
<td colspan="3">TOTAL VOTOS VÁLIDOS</td>
<td class="right"><?= $fmt($report['valid_votes']) ?></td>
<td class="right"><?= $pct($report['valid_votes'] > 0 ? 100 : 0) ?></td>
<td class="right"><?= $pct($report['total_voters'] > 0 ? ($report['valid_votes'] / $report['total_voters']) * 100 : 0) ?></td>
</tr>
</tfoot>
</table>
<?php else: ?>
<div class="empty">No hay candidatos registrados para la elección y dignidad seleccionadas.</div>
<?php endif; ?>
</div>

<div class="section avoid">
<h2>Distribución de votos y participación</h2>
<table class="two-col">
<tbody>
<tr>
<td>
<h3>Composición del total emitido</h3>
<?php if ($report['total_cast'] > 0): ?>
<table class="bar-table">
<colgroup><col style="width: 32%"><col style="width: 48%"><col style="width: 20%"></colgroup>
<tbody>
<?php foreach ($distribution as $item): ?>
<tr>
<td class="bar-label"><strong><?= esc($item['label']) ?></strong></td>
<td class="bar-cell"><div class="track"><div class="bar" style="width: <?= $item['width'] ?>%; background: <?= $item['color'] ?>;"></div></div></td>
<td class="bar-value"><?= $fmt($item['votes']) ?><span class="sub"><?= $pct($item['share']) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="method">Total emitido: <strong><?= $fmt($report['total_cast']) ?></strong> votos. Incluye válidos, nulos y blancos de las actas vigentes con resultados.</div>
<?php else: ?>
<div class="empty">No hay votos registrados en las actas vigentes para esta dignidad.</div>
<?php endif; ?>
</td>
<td>
<h3>Participación ciudadana</h3>
<div class="panel">
<table class="summary-list">
<tbody>
<tr><td>Electores registrados</td><td><?= $fmt($report['total_registered']) ?></td></tr>
<tr><td>Sufragantes</td><td><?= $fmt($report['total_voters']) ?></td></tr>
<tr><td>Participación</td><td><?= $pct($report['participation']) ?></td></tr>
<tr><td>Abstención</td><td><?= $fmt($report['abstention']) ?> (<?= $pct($report['abstention_pct']) ?>)</td></tr>
<tr><td>Total emitido</td><td><?= $fmt($report['total_cast']) ?></td></tr>
<tr><td>Diferencia emitidos - sufragantes</td><td><?= $report['difference'] > 0 ? '+' : '' ?><?= $fmt($report['difference']) ?></td></tr>
</tbody>
</table>
</div>
</td>
</tr>
</tbody>
</table>
</div>

<div class="section avoid">
<h2>Control de actas</h2>
<table class="data-table compact">
<thead>
<tr>
<th>Indicador</th>
<th class="right">Valor</th>
<th>Indicador</th>
<th class="right">Valor</th>
</tr>
</thead>
<tbody>
<tr><td>Actas subidas</td><td class="right"><?= $fmt($report['uploaded_actas']) ?></td><td>Actas esperadas</td><td class="right"><?= $fmt($report['expected_actas']) ?></td></tr>
<tr><td>Cobertura de actas</td><td class="right"><?= $pct($report['coverage']) ?></td><td>Actas verificadas</td><td class="right"><?= $fmt($report['verified_actas']) ?></td></tr>
<tr><td>Actas con novedad</td><td class="right"><?= $fmt($report['novelty_actas']) ?></td><td>Actas con inconsistencia</td><td class="right"><?= $fmt($report['inconsistent_actas']) ?></td></tr>
<tr><td>Consistencia OK</td><td class="right"><?= $fmt($report['ok_actas']) ?></td><td>Consistencia con error</td><td class="right"><?= $fmt($report['error_actas']) ?></td></tr>
<tr><td>Actas sin datos</td><td class="right"><?= $fmt($report['sin_datos_actas']) ?></td><td>Actas con resultados</td><td class="right"><?= $fmt($report['actas_with_results']) ?></td></tr>
<tr><td>Actas sin resultados</td><td class="right"><?= $fmt($report['actas_without_results']) ?></td><td>JRV con acta</td><td class="right"><?= $fmt($report['with_acta_jrvs']) ?></td></tr>
<tr><td>JRV sin acta</td><td class="right"><?= $fmt($report['without_acta_jrvs']) ?></td><td>Resultados sin acta vigente</td><td class="right"><?= $fmt($report['results_without_acta']) ?></td></tr>
<?php if ($report['actas_without_electors'] > 0): ?>
<tr><td>Actas sin electores</td><td class="right"><?= $fmt($report['actas_without_electors']) ?></td><td class="muted">No aportan al denominador</td><td class="right">—</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<div class="section">
<h2>Resultados por provincia</h2>
<?php if ($report['provinces']): ?>
<table class="data-table provinces">
<thead>
<tr>
<th>Provincia</th>
<th class="right">JRV</th>
<th class="right">Actas</th>
<th class="right">Cobertura</th>
<th class="right">Con result.</th>
<th class="right">Válidos</th>
<th class="right">Nulos</th>
<th class="right">Blancos</th>
<th class="right">Participación</th>
<th class="right">Inconsist.</th>
</tr>
</thead>
<tbody>
<?php foreach ($report['provinces'] as $province): ?>
<tr>
<td><?= esc($province->name_province) ?></td>
<td class="right"><?= $fmt($province->total_jrvs) ?></td>
<td class="right"><?= $fmt($province->uploaded_actas) ?></td>
<td class="right"><?= $pct($province->coverage) ?></td>
<td class="right"><?= $fmt($province->actas_with_results) ?></td>
<td class="right"><?= $fmt($province->valid_votes) ?></td>
<td class="right"><?= $fmt($province->null_votes) ?></td>
<td class="right"><?= $fmt($province->blank_votes) ?></td>
<td class="right"><?= $pct($province->participation) ?></td>
<td class="right"><?= $fmt($province->inconsistent_actas) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
<td>TOTAL</td>
<td class="right"><?= $fmt($report['total_jrvs']) ?></td>
<td class="right"><?= $fmt($report['uploaded_actas']) ?></td>
<td class="right"><?= $pct($report['coverage']) ?></td>
<td class="right"><?= $fmt($report['actas_with_results']) ?></td>
<td class="right"><?= $fmt($report['valid_votes']) ?></td>
<td class="right"><?= $fmt($report['null_votes']) ?></td>
<td class="right"><?= $fmt($report['blank_votes']) ?></td>
<td class="right"><?= $pct($report['participation']) ?></td>
<td class="right"><?= $fmt($report['inconsistent_actas']) ?></td>
</tr>
</tfoot>
</table>
<p class="muted" style="margin-top: 4pt; font-size: 6.6pt;">La cobertura se calcula sobre las JRV registradas. La participación se calcula sobre los electores de las actas con resultados.</p>
<?php else: ?>
<div class="empty">No hay provincias registradas para la elección seleccionada.</div>
<?php endif; ?>
</div>

<div class="section avoid">
<h2>Resumen de inconsistencias</h2>
<table class="summary-cards">
<tbody>
<tr>
<td><div class="summary-card"><div class="label">Actas con inconsistencia</div><div class="value"><?= $fmt($report['inconsistency_summary']['total']) ?></div></div></td>
<td><div class="summary-card error"><div class="label">Errores de consistencia</div><div class="value"><?= $fmt($report['inconsistency_summary']['error']) ?></div></div></td>
<td><div class="summary-card novelty"><div class="label">Actas con novedad</div><div class="value"><?= $fmt($report['inconsistency_summary']['novelty']) ?></div></div></td>
</tr>
</tbody>
</table>

<?php if ($report['inconsistency_summary']['by_province']): ?>
<table class="data-table compact">
<thead>
<tr><th>Provincia</th><th class="right">Errores</th><th class="right">Novedades</th><th class="right">Total</th></tr>
</thead>
<tbody>
<?php foreach ($report['inconsistency_summary']['by_province'] as $item): ?>
<tr>
<td><?= esc($item->name_province) ?></td>
<td class="right"><?= $fmt($item->error) ?></td>
<td class="right"><?= $fmt($item->novelty) ?></td>
<td class="right"><?= $fmt($item->total) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<div class="empty">No se registraron actas con inconsistencia o novedad en las actas vigentes.</div>
<?php endif; ?>
<p class="muted" style="margin-top: 4pt; font-size: 6.6pt;">Este apartado presenta un resumen. El detalle completo de las actas inconsistentes se incluye en la hoja “Inconsistencias” del archivo Excel.</p>
</div>

<div class="section">
<h2>Observaciones y notas metodológicas</h2>
<?php if ($report['notes']): ?>
<ul class="note-list">
<?php foreach ($report['notes'] as $note): ?>
<li><?= esc($note) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<div class="method">
<strong>Alcance del informe:</strong> los totales incluyen únicamente actas vigentes con resultados registrados. La participación se calcula sobre los electores registrados en esas actas; cuando el acta no tiene electores registrados se utiliza el padrón de la JRV. La diferencia entre votos emitidos y sufragantes se presenta como control de consistencia.
<?php if ($report['preliminary']): ?>
<br><br><strong>Informe preliminar:</strong> los resultados pueden variar mientras existan actas pendientes, inconsistencias o procesos de verificación en curso.
<?php endif; ?>
</div>
</div>

<div class="footer">
Sistema de Control Electoral · Reporte generado automáticamente el <?= esc($report['generated_at']) ?> · Fuente: actas vigentes y resultados registrados
</div>
</body>
</html>
