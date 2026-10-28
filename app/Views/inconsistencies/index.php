<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<?php
  $canEditActa = has_permission('results.update') || has_permission('results.verify');
?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div>
    <h1>Inconsistencias</h1>
    <p class="text-muted mb-0">Actas con diferencias entre votos, blancos, nulos y sufragantes.</p>
  </div>
  <a href="<?= base_url('minutes') ?>" class="btn btn-outline-secondary"><i class="bi bi-card-list me-1"></i>Todas las actas</a>
</div>

<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" action="<?= base_url('inconsistencies') ?>" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small mb-0">Elección</label>
        <select name="election_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($elections as $e): ?>
          <option value="<?= $e->id ?>" <?= (int)$electionId === (int)$e->id ? 'selected' : '' ?>><?= esc($e->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-0">Dignidad</label>
        <select name="dignity_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Todas</option>
          <?php foreach ($dignities as $d): ?>
          <option value="<?= $d->id ?>" <?= (int)$dignityId === (int)$d->id ? 'selected' : '' ?>><?= esc($d->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small mb-0">Buscar JRV / recinto</label>
        <input type="text" name="q" value="<?= esc($search) ?>" class="form-control form-control-sm" placeholder="Ej. 1345 o nombre del recinto">
      </div>
      <div class="col-md-1">
        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i></button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>JRV</th>
            <th>Dignidad</th>
            <th>Ubicación</th>
            <th>Estado</th>
            <th>Detalle de la inconsistencia</th>
            <th>Versión</th>
            <th>Actualizada</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($minutes)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No hay inconsistencias para los filtros seleccionados.</td></tr>
          <?php endif; ?>
          <?php foreach ($minutes as $m): ?>
          <tr>
            <td><strong><?= esc($m->numero_junta ?? '—') ?></strong></td>
            <td><span class="small"><?= esc($m->dignity_name ?? '—') ?></span></td>
            <td><small><?= esc(($m->name_province ?? '') . ' / ' . ($m->name_canton ?? '') . ' / ' . ($m->name_parish ?? '')) ?><br><?= esc($m->name_precinct ?? '') ?></small></td>
            <td>
              <?php if ($m->status === 'verificada'): ?>
                <span class="badge bg-success">Verificada con observación</span>
              <?php else: ?>
                <span class="badge bg-warning text-dark">Con novedad</span>
              <?php endif; ?>
            </td>
            <td><span class="small text-danger"><?= esc($m->consistency_detail ?? 'Sin detalle') ?></span></td>
            <td>v<?= (int)$m->version ?></td>
            <td><?= date('d/m/Y H:i', strtotime($m->updated_at ?? $m->created_at)) ?></td>
            <td class="text-nowrap">
              <a href="<?= base_url('minutes/preview') ?>/<?= $m->id ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Ver"><i class="bi bi-eye"></i></a>
              <a href="<?= base_url('minutes/download') ?>/<?= $m->id ?>" class="btn btn-sm btn-outline-success" title="Descargar"><i class="bi bi-download"></i></a>
              <?php if ($canEditActa): ?>
              <button class="btn btn-sm btn-outline-primary" onclick="editMinute(<?= $m->id ?>)" title="Corregir"><i class="bi bi-pencil"></i> Corregir</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->include('minutes/_edit_modal') ?>
<?= $this->include('minutes/_edit_script') ?>
<?= $this->endSection() ?>
