<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div>
    <h1 class="mb-1">Dashboard Ejecutivo</h1>
    <?php if ($currentElection): ?>
      <div class="text-muted small">
        <?= esc($currentElection->name) ?>
        <span class="badge bg-secondary ms-2"><?= esc(ucfirst(str_replace('_', ' ', $currentElection->status))) ?></span>
        <?php if (!empty($currentElection->election_date)): ?>
          <span class="ms-2"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($currentElection->election_date)) ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
  <form method="get" action="<?= base_url('dashboard') ?>" class="d-flex flex-wrap gap-2 align-items-center">
    <select id="electionSelect" name="election_id" class="form-select form-select-sm" style="min-width:240px" onchange="document.getElementById('dignitySelect').value='0';this.form.submit()">
      <?php foreach ($elections as $election): ?>
        <option value="<?= (int)$election->id ?>" <?= (int)$election->id === (int)$electionId ? 'selected' : '' ?>>
          <?= esc($election->name) ?> (<?= esc($election->status) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <select id="dignitySelect" name="dignity_id" class="form-select form-select-sm" style="min-width:210px" onchange="this.form.submit()" <?= !$dignities ? 'disabled' : '' ?>>
      <option value="0">Todas las dignidades</option>
      <?php foreach ($dignities as $dignity): ?>
        <option value="<?= (int)$dignity->id ?>" <?= (int)$dignity->id === (int)$dignityId ? 'selected' : '' ?>><?= esc($dignity->name) ?></option>
      <?php endforeach; ?>
    </select>
    <noscript><button type="submit" class="btn btn-sm btn-primary">Aplicar</button></noscript>
  </form>
</div>

<?php if (!$elections): ?>
  <div class="alert alert-warning">No hay elecciones registradas.</div>
<?php elseif ($currentElection && !$dignities): ?>
  <div class="alert alert-info">Esta elección no tiene dignidades habilitadas. Configúrelas en Elecciones → Dignidades.</div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card border-start border-primary border-4 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3"><i class="bi bi-building-check"></i></div>
        <div><div class="stat-value"><?= number_format($stats['total_jrvs']) ?></div><div class="stat-label">Total JRV</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card border-start border-secondary border-4 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3"><i class="bi bi-collection"></i></div>
        <div><div class="stat-value"><?= number_format($stats['expected_actas']) ?></div><div class="stat-label">Actas esperadas</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card border-start border-success border-4 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon bg-success bg-opacity-10 text-success me-3"><i class="bi bi-file-earmark-check"></i></div>
        <div><div class="stat-value"><?= number_format($stats['received_actas']) ?></div><div class="stat-label">Actas recibidas</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card border-start border-info border-4 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon bg-info bg-opacity-10 text-info me-3"><i class="bi bi-percent"></i></div>
        <div><div class="stat-value"><?= number_format($stats['avance'], 2) ?>%</div><div class="stat-label">Avance de actas</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card border-start border-warning border-4 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3"><i class="bi bi-check2-square"></i></div>
        <div><div class="stat-value"><?= number_format($stats['complete_jrvs']) ?></div><div class="stat-label">JRV con todas las actas</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <a href="<?= base_url('inconsistencies?election_id=' . (int)$electionId . ($dignityId ? '&dignity_id=' . (int)$dignityId : '')) ?>" class="text-decoration-none text-body">
      <div class="card stat-card border-start border-danger border-4 h-100">
        <div class="card-body d-flex align-items-center">
          <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3"><i class="bi bi-exclamation-triangle"></i></div>
          <div><div class="stat-value text-danger"><?= number_format($stats['inconsistent_actas']) ?></div><div class="stat-label">Inconsistencias</div></div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-2 d-flex flex-wrap gap-4 align-items-center small">
    <span class="text-muted">Alcance:</span>
    <span class="badge bg-primary"><?= $selectedDignity ? esc($selectedDignity->name) : 'Todas las dignidades (' . count($dignities) . ')' ?></span>
    <span><i class="bi bi-people me-1"></i>Participación sobre actas con resultados: <strong class="text-primary"><?= number_format($stats['participacion'], 2) ?>%</strong></span>
    <span>Votantes: <strong><?= number_format($stats['total_voters']) ?></strong></span>
    <span>Electores: <strong><?= number_format($stats['total_electores']) ?></strong></span>
    <span>Verificadas: <strong class="text-success"><?= number_format($stats['verified_actas']) ?></strong></span>
    <span>Sin acta: <strong class="text-danger"><?= number_format($stats['no_acta_jrvs']) ?></strong> JRV</span>
  </div>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-geo-alt me-2"></i>Avance por Provincia</span>
        <span class="badge bg-light text-dark"><?= number_format($stats['received_actas']) ?> / <?= number_format($stats['expected_actas']) ?> actas</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Provincia</th>
                <th class="text-end">JRV</th>
                <?php foreach ($scopeDignities as $dignity): ?>
                  <th style="min-width:110px"><?= esc($dignity->name) ?></th>
                <?php endforeach; ?>
                <th style="min-width:150px">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($byProvince as $province): ?>
                <tr>
                  <td><strong><?= esc($province['name_province']) ?></strong></td>
                  <td class="text-end"><?= number_format($province['total']) ?></td>
                  <?php foreach ($scopeDignities as $dignity): $dd = $province['dignities'][(int)$dignity->id] ?? ['received' => 0, 'expected' => $province['total'], 'avance' => 0]; ?>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <small class="text-nowrap"><?= number_format($dd['received']) ?>/<?= number_format($dd['expected']) ?></small>
                        <div class="progress flex-grow-1" style="height:5px;min-width:45px">
                          <div class="progress-bar <?= $dd['avance'] >= 80 ? 'bg-success' : ($dd['avance'] >= 40 ? 'bg-warning' : 'bg-danger') ?>" style="width:<?= min(100, $dd['avance']) ?>%"></div>
                        </div>
                      </div>
                    </td>
                  <?php endforeach; ?>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-<?= $province['avance'] >= 80 ? 'success' : ($province['avance'] >= 40 ? 'warning text-dark' : 'danger') ?>"><?= number_format($province['avance'], 2) ?>%</span>
                      <small class="text-muted text-nowrap"><?= number_format($province['received']) ?>/<?= number_format($province['expected']) ?></small>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$byProvince): ?>
                <tr><td colspan="<?= 3 + count($scopeDignities) ?>" class="text-center text-muted py-4">Sin datos para el alcance seleccionado.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-pie-chart me-2"></i>Comparativo JRV</div>
      <div class="card-body">
        <div style="height:210px"><canvas id="jrvChart"></canvas></div>
        <div class="d-flex justify-content-between small text-muted mt-3">
          <span><i class="bi bi-circle-fill text-success me-1"></i>Todas: <strong><?= number_format($stats['complete_jrvs']) ?></strong></span>
          <span><i class="bi bi-circle-fill text-warning me-1"></i>Parciales: <strong><?= number_format($stats['partial_jrvs']) ?></strong></span>
          <span><i class="bi bi-circle-fill text-danger me-1"></i>Sin acta: <strong><?= number_format($stats['no_acta_jrvs']) ?></strong></span>
        </div>
      </div>
    </div>
    <div class="card mt-3">
      <div class="card-header"><i class="bi bi-shield-check me-2"></i>Consistencia de Actas</div>
      <div class="card-body">
        <div style="height:210px"><canvas id="consistencyChart"></canvas></div>
        <div class="d-flex justify-content-between small text-muted mt-3">
          <span>Registradas: <strong><?= number_format($statusCounts['registrada']) ?></strong></span>
          <span>Con novedad: <strong class="text-danger"><?= number_format($statusCounts['con_novedad']) ?></strong></span>
          <span>Verificadas: <strong class="text-success"><?= number_format($statusCounts['verificada']) ?></strong></span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($resultsByDignity)): ?>
<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-trophy me-2"></i>Resultados por Dignidad</span>
    <span class="badge bg-warning text-dark">Provisional</span>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <?php foreach ($resultsByDignity as $rd): $top = $rd['results'][0] ?? null; $totalV = (int)$rd['total_votes']; $coverage = $stats['total_jrvs'] > 0 ? round(($rd['with_results'] / $stats['total_jrvs']) * 100, 2) : 0; ?>
        <div class="col-md-6 col-xl-4">
          <div class="card border h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold text-primary mb-0"><?= esc($rd['dignity']->name) ?></h6>
                <?php if ($rd['errors'] > 0): ?>
                  <span class="badge bg-danger"><?= number_format($rd['errors']) ?> con inconsistencia</span>
                <?php endif; ?>
              </div>
              <?php if ($top): ?>
                <div class="fs-3 fw-bold" style="color:<?= esc($top->color ?: '#0d6efd') ?>"><?= number_format((int)$top->total_votes) ?></div>
                <div class="small"><?= esc(trim(($top->first_name ?? '') . ' ' . ($top->last_name ?? ''))) ?></div>
                <div class="mb-2"><span class="badge bg-dark"><?= esc($top->party_acronym ?? '') ?></span></div>
                <div class="progress mb-2" style="height:5px">
                  <?php foreach ($rd['results'] as $result): $pct = $totalV > 0 ? round(((int)$result->total_votes / $totalV) * 100) : 0; ?>
                    <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= esc($result->color ?: '#0d6efd') ?>" title="<?= esc(trim(($result->first_name ?? '') . ' ' . ($result->last_name ?? ''))) ?>: <?= $pct ?>%"></div>
                  <?php endforeach; ?>
                </div>
                <div class="small text-muted mb-3"><?= count($rd['results']) ?> candidatos · <?= number_format($totalV) ?> votos de candidatos</div>
              <?php else: ?>
                <div class="text-muted small mb-3">Sin resultados cargados.</div>
              <?php endif; ?>
              <div class="d-flex justify-content-between small">
                <span class="text-muted">Cobertura</span>
                <strong><?= number_format($rd['with_results']) ?>/<?= number_format($stats['total_jrvs']) ?> JRV (<?= number_format($coverage, 2) ?>%)</strong>
              </div>
              <div class="d-flex justify-content-between small">
                <span class="text-muted">Actas</span>
                <strong><?= number_format($rd['received']) ?> recibidas · <?= number_format($rd['verified']) ?> verificadas</strong>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card mt-3">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <span><i class="bi bi-exclamation-circle me-2"></i>JRV con Actas Pendientes</span>
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-danger"><?= number_format($pendingTotal) ?> JRV</span>
      <a class="btn btn-sm btn-outline-primary" href="<?= base_url('minutes?missing=1&election_id=' . (int)$electionId . ($dignityId ? '&dignity_id=' . (int)$dignityId : '')) ?>">Ver todas</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>#</th><th>Provincia</th><th>Cantón</th><th>Parroquia</th><th>Recinto</th><th>JRV</th><th>Faltantes</th><th>Operador</th></tr>
        </thead>
        <tbody>
          <?php $i = 1; foreach ($pending as $row): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= esc($row->name_province) ?></td>
              <td><?= esc($row->name_canton) ?></td>
              <td><?= esc($row->name_parish) ?></td>
              <td><?= esc($row->name_precinct) ?></td>
              <td><strong><?= esc($row->numero_junta) ?></strong> <?php if (!empty($row->genero)): ?><span class="badge bg-light text-dark"><?= esc($row->genero) ?></span><?php endif; ?></td>
              <td>
                <?php foreach (array_filter(array_map('trim', explode(',', (string)($row->faltantes ?? '')))) as $missing): ?>
                  <span class="badge bg-warning text-dark me-1"><?= esc($missing) ?></span>
                <?php endforeach; ?>
              </td>
              <td><?= esc($row->operador ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$pending): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No hay JRV pendientes para el alcance seleccionado.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($pendingTotal > count($pending)): ?>
    <div class="card-footer small text-muted text-end">Mostrando las primeras <?= number_format($pendingLimit) ?> de <?= number_format($pendingTotal) ?> JRV. <a href="<?= base_url('minutes?missing=1&election_id=' . (int)$electionId . ($dignityId ? '&dignity_id=' . (int)$dignityId : '')) ?>">Ver listado completo</a></div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var jrvEl = document.getElementById('jrvChart');
  if (jrvEl) {
    new Chart(jrvEl, {
      type: 'doughnut',
      data: {
        labels: ['Todas las actas', 'Parciales', 'Sin acta'],
        datasets: [{
          data: [<?= (int)$stats['complete_jrvs'] ?>, <?= (int)$stats['partial_jrvs'] ?>, <?= (int)$stats['no_acta_jrvs'] ?>],
          backgroundColor: ['#198754', '#ffc107', '#dc3545'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
      }
    });
  }

  var consistencyEl = document.getElementById('consistencyChart');
  if (consistencyEl) {
    new Chart(consistencyEl, {
      type: 'doughnut',
      data: {
        labels: ['Consistentes', 'Con error', 'Sin datos'],
        datasets: [{
          data: [<?= (int)$consistencyCounts['ok'] ?>, <?= (int)$consistencyCounts['error'] ?>, <?= (int)$consistencyCounts['sin_datos'] ?>],
          backgroundColor: ['#198754', '#dc3545', '#adb5bd'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
      }
    });
  }
});
</script>
<?= $this->endSection() ?>
