<?php $fmt = static fn($value) => number_format((float)$value, 0, ',', '.'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin: 16mm 14mm 18mm 14mm; }
* { box-sizing: border-box; }
body { margin: 0; font-family: "DejaVu Sans", sans-serif; font-size: 8pt; color: #2d3748; line-height: 1.35; }
.header { border-bottom: 1.4pt solid #1a237e; padding-bottom: 8pt; margin-bottom: 14pt; }
.eyebrow { color: #1a237e; font-size: 6.8pt; font-weight: bold; letter-spacing: 1.4pt; text-transform: uppercase; }
h1 { margin: 3pt 0 4pt; color: #1a237e; font-size: 18pt; line-height: 1.1; }
.subtitle { color: #4a5568; font-size: 8pt; }
.meta { margin-top: 4pt; color: #718096; font-size: 6.8pt; }
.meta strong { color: #2d3748; }
.kpi-table { width: 100%; border-collapse: separate; border-spacing: 4pt; margin: 0 -4pt 10pt; }
.kpi-table td { width: 25%; vertical-align: top; padding: 0; }
.kpi { background: #f7f8fc; border: 0.6pt solid #dde3ee; border-left: 3pt solid #1a237e; padding: 6pt 7pt; min-height: 38pt; }
.kpi .label { color: #5a6275; font-size: 6.7pt; letter-spacing: 0.3pt; text-transform: uppercase; }
.kpi .value { color: #1a237e; font-size: 14pt; font-weight: bold; margin-top: 2pt; line-height: 1.1; }
.kpi .hint { color: #718096; font-size: 6.6pt; margin-top: 1pt; }
h2 { margin: 0 0 6pt; color: #1a237e; font-size: 10pt; text-transform: uppercase; letter-spacing: 0.5pt; }
table.data { width: 100%; border-collapse: collapse; }
table.data th { background: #1a237e; color: #fff; padding: 6pt 7pt; font-size: 6.8pt; text-align: left; text-transform: uppercase; letter-spacing: 0.3pt; }
table.data td { padding: 5pt 7pt; border-bottom: 0.5pt solid #e2e8f0; font-size: 7.6pt; }
table.data tbody tr:nth-child(even) { background: #f7f8fc; }
table.data tfoot td { padding: 6pt 7pt; background: #eef1f8; border-top: 1pt solid #1a237e; color: #1a237e; font-weight: bold; font-size: 7.8pt; }
.right { text-align: right; }
.note { margin-top: 9pt; color: #718096; font-size: 6.8pt; line-height: 1.45; }
.footer { margin-top: 16pt; padding-top: 6pt; border-top: 0.5pt solid #dde3ee; color: #718096; font-size: 6.5pt; text-align: center; }
</style>
</head>
<body>
<div class="header">
    <div class="eyebrow">Sistema de Control Electoral</div>
    <h1>Avance de Carga de Actas</h1>
    <div class="subtitle">Elección: <strong><?= esc($election->name ?? 'N/D') ?></strong></div>
    <div class="meta">Generado: <strong><?= date('d/m/Y H:i') ?></strong> · Fuente: actas vigentes registradas en el sistema</div>
</div>

<table class="kpi-table">
<tr>
    <td><div class="kpi"><div class="label">Total JRV</div><div class="value"><?= $fmt($stats['total'] ?? 0) ?></div><div class="hint">Juntas receptoras del voto</div></div></td>
    <td><div class="kpi"><div class="label">JRV con actas</div><div class="value"><?= $fmt($stats['reported'] ?? 0) ?></div><div class="hint">Con al menos un acta vigente</div></div></td>
    <td><div class="kpi"><div class="label">Pendientes</div><div class="value"><?= $fmt($stats['pending'] ?? 0) ?></div><div class="hint">Sin actas registradas</div></div></td>
    <td><div class="kpi"><div class="label">Actas recibidas</div><div class="value"><?= $fmt($stats['actas'] ?? 0) ?></div><div class="hint">Actas vigentes de todas las dignidades</div></div></td>
</tr>
</table>

<h2>Avance por Provincia</h2>
<table class="data">
<thead>
<tr>
    <th>Provincia</th>
    <th class="right">Total JRV</th>
    <th class="right">JRV con actas</th>
    <th class="right">Pendientes</th>
    <th class="right">Actas recibidas</th>
    <th class="right">Cobertura JRV</th>
</tr>
</thead>
<tbody>
<?php if (empty($byProvince)): ?>
<tr><td colspan="6" class="right">No hay juntas registradas para esta elección.</td></tr>
<?php else: ?>
<?php foreach ($byProvince as $p): ?>
<tr>
    <td><?= esc($p->name_province) ?></td>
    <td class="right"><?= $fmt($p->total) ?></td>
    <td class="right"><?= $fmt($p->reportadas) ?></td>
    <td class="right"><?= $fmt($p->pendientes) ?></td>
    <td class="right"><?= $fmt($p->actas) ?></td>
    <td class="right"><?= number_format((float)$p->avance, 2, ',', '.') ?>%</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
<?php if (!empty($byProvince)): ?>
<tfoot>
<tr>
    <td>TOTAL</td>
    <td class="right"><?= $fmt($stats['total'] ?? 0) ?></td>
    <td class="right"><?= $fmt($stats['reported'] ?? 0) ?></td>
    <td class="right"><?= $fmt($stats['pending'] ?? 0) ?></td>
    <td class="right"><?= $fmt($stats['actas'] ?? 0) ?></td>
    <td class="right"><?= ($stats['total'] ?? 0) > 0 ? number_format(($stats['reported'] / $stats['total']) * 100, 2, ',', '.') : '0,00' ?>%</td>
</tr>
</tfoot>
<?php endif; ?>
</table>

<div class="note">
    La cobertura mide las JRV con al menos un acta vigente sobre el total de JRV de la elección.
    Las actas recibidas incluyen todas las dignidades y pueden superar el total de JRV.
</div>

<div class="footer">Sistema de Control Electoral · Reporte generado el <?= date('d/m/Y H:i:s') ?></div>
</body>
</html>
