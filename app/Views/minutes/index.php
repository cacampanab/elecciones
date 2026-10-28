<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<?php
  $canUpload = has_permission('minutes.upload');
  $canEditActa = has_permission('results.update') || has_permission('results.verify');
  $canDelete = has_permission('minutes.delete');
?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Actas Electorales</h1></div>
  <?php if ($canUpload): ?>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload me-1"></i>Subir Acta</button>
  <?php endif; ?>
</div>

<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" action="<?= base_url('minutes') ?>" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small mb-0">Elección</label>
        <select name="election_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($elections as $e): ?>
          <option value="<?= $e->id ?>" <?= (int)$electionId === (int)$e->id ? 'selected' : '' ?>><?= esc($e->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-0">Dignidad</label>
        <select name="dignity_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Todas</option>
          <?php foreach ($dignities as $d): ?>
          <option value="<?= $d->id ?>" <?= (int)$dignityId === (int)$d->id ? 'selected' : '' ?>><?= esc($d->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-0">Estado</label>
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Todos</option>
          <option value="registrada" <?= $status === 'registrada' ? 'selected' : '' ?>>Registradas</option>
          <option value="con_novedad" <?= $status === 'con_novedad' ? 'selected' : '' ?>>Con novedad</option>
          <option value="verificada" <?= $status === 'verificada' ? 'selected' : '' ?>>Verificadas</option>
          <option value="error" <?= $status === 'error' ? 'selected' : '' ?>>Inconsistentes</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-0">Buscar JRV / recinto</label>
        <input type="text" name="q" value="<?= esc($search) ?>" class="form-control form-control-sm" placeholder="Ej. 1345 o nombre del recinto">
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
        <a href="<?= base_url('minutes?election_id=' . $electionId . '&missing=1') ?>" class="btn btn-sm <?= $missing ? 'btn-warning' : 'btn-outline-warning' ?>" title="Solo JRV-dignidad sin acta"><i class="bi bi-exclamation-triangle"></i> Faltantes</a>
        <a href="<?= base_url('minutes') ?>" class="btn btn-sm btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if ($missing && count($minutes) >= 500): ?>
    <div class="alert alert-warning border-0 rounded-0 mb-0 py-2 small"><i class="bi bi-info-circle me-1"></i>Mostrando los primeros 500 registros faltantes. Use los filtros por dignidad o búsqueda para acotar el resultado.</div>
    <?php endif; ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0 data-table">
        <thead class="table-light">
          <tr>
            <th>JRV</th>
            <th>Dignidad</th>
            <th>Género</th>
            <th>Ubicación</th>
            <th>Elección</th>
            <th>Estado</th>
            <th>Consistencia</th>
            <th>Versión</th>
            <th>Subido por</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($minutes)): ?>
          <tr><td colspan="11" class="text-center text-muted py-4">No hay actas para los filtros seleccionados.</td></tr>
          <?php endif; ?>
          <?php foreach ($minutes as $m): ?>
          <?php $isMissing = empty($m->id); ?>
          <tr class="<?= $isMissing ? 'table-warning bg-opacity-25' : '' ?>">
            <td><strong><?= esc($m->numero_junta ?? '—') ?></strong></td>
            <td><span class="small"><?= esc($m->dignity_name ?? '—') ?></span></td>
            <td>
              <?php if (!empty($m->genero)): ?>
              <span class="badge" style="background:<?= $m->genero === 'femenina' ? '#e83e8c' : '#0d6efd' ?>">
                <i class="bi bi-<?= $m->genero === 'femenina' ? 'gender-female' : 'gender-male' ?>"></i> <?= ucfirst($m->genero) ?>
              </span>
              <?php endif; ?>
            </td>
            <td><small><?= esc(($m->name_province ?? '') . ' / ' . ($m->name_canton ?? '') . ' / ' . ($m->name_parish ?? '')) ?><br><?= esc($m->name_precinct ?? '') ?></small></td>
            <td><span class="small text-muted"><?= esc($m->election_name ?? '') ?></span></td>
            <td>
              <?php if ($isMissing): ?>
                <span class="badge bg-secondary">Sin acta</span>
              <?php else:
                $statusClasses = ['registrada' => 'bg-primary', 'con_novedad' => 'bg-warning text-dark', 'verificada' => 'bg-success'];
                $statusLabels = ['registrada' => 'Registrada', 'con_novedad' => 'Con novedad', 'verificada' => 'Verificada'];
              ?>
                <span class="badge <?= $statusClasses[$m->status] ?? 'bg-secondary' ?>"><?= $statusLabels[$m->status] ?? $m->status ?></span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($isMissing): ?>
                <span class="text-muted">—</span>
              <?php elseif (($m->consistency_status ?? '') === 'ok'): ?>
                <span class="badge bg-success-subtle text-success border border-success" title="Totales consistentes">Consistente</span>
              <?php elseif (($m->consistency_status ?? '') === 'error'): ?>
                <span class="badge bg-danger-subtle text-danger border border-danger" title="<?= esc($m->consistency_detail ?? '') ?>">Inconsistente</span>
              <?php else: ?>
                <span class="badge bg-light text-muted border">Sin datos</span>
              <?php endif; ?>
            </td>
            <td><?= $isMissing ? '—' : 'v' . (int)$m->version ?></td>
            <td><?= $isMissing ? '—' : esc(trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? ''))) ?></td>
            <td><?= $isMissing ? '—' : date('d/m/Y H:i', strtotime($m->created_at)) ?></td>
            <td class="text-nowrap">
              <?php if ($isMissing): ?>
                <?php if ($canUpload): ?>
                <button class="btn btn-sm btn-outline-primary" onclick="openUploadFor(<?= (int)$m->polling_station_id ?>, <?= (int)$m->election_id ?>, <?= (int)$m->dignity_id ?>)" title="Subir acta faltante"><i class="bi bi-upload"></i></button>
                <?php endif; ?>
              <?php else: ?>
                <a href="<?= base_url('minutes/preview') ?>/<?= $m->id ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Ver"><i class="bi bi-eye"></i></a>
                <a href="<?= base_url('minutes/download') ?>/<?= $m->id ?>" class="btn btn-sm btn-outline-success" title="Descargar"><i class="bi bi-download"></i></a>
                <?php if ($canEditActa && (!is_operator() || $m->status === 'con_novedad')): ?>
                <button class="btn btn-sm btn-outline-primary" onclick="editMinute(<?= $m->id ?>)" title="Editar / Verificar"><i class="bi bi-pencil"></i></button>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <button class="btn btn-sm btn-outline-danger" onclick="del(<?= $m->id ?>)" title="Eliminar"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($canUpload): ?>
<div class="modal fade" id="uploadModal" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= base_url('minutes/upload') ?>" enctype="multipart/form-data" class="modal-content"><?= csrf_field() ?>
  <div class="modal-header"><h5 class="modal-title">Subir Acta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Elección *</label><select name="election_id" id="upElection" class="form-select" required><option value="">Seleccione</option><?php foreach ($elections as $e): ?><option value="<?= $e->id ?>"><?= esc($e->name) ?></option><?php endforeach; ?></select></div>
    <div class="mb-3"><label class="form-label">Dignidad *</label><select name="dignity_id" id="upDignity" class="form-select" required><option value="">Primero seleccione elección</option></select></div>
    <div class="mb-3"><label class="form-label">JRV *</label><select name="polling_station_id" id="stationSelect" class="form-select" required><option value="">Primero seleccione elección</option></select></div>
    <div class="mb-3"><label class="form-label">Motivo del reemplazo</label><input type="text" name="reason" id="upReason" class="form-control" maxlength="255" placeholder="Obligatorio si reemplaza un acta existente"></div>
    <div class="mb-3"><label class="form-label">Observaciones</label><input type="text" name="notes" id="upNotes" class="form-control" maxlength="255" placeholder="Opcional"></div>
    <div class="mb-3"><label class="form-label">Archivo (PDF, JPG, PNG) *</label><input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Subir</button></div>
</form></div></div>

<script>
async function loadUploadDignities(electionId) {
  const sel = document.getElementById('upDignity');
  sel.innerHTML = '<option value="">Cargando...</option>';
  if (!electionId) { sel.innerHTML = '<option value="">Primero seleccione elección</option>'; return; }
  try {
    const r = await fetch('<?= base_url('ajax/election-dignities') ?>/' + electionId);
    const d = await r.json();
    sel.innerHTML = '<option value="">Seleccione dignidad...</option>';
    d.forEach(x => { sel.innerHTML += '<option value="' + x.id + '">' + x.name + '</option>'; });
  } catch { sel.innerHTML = '<option value="">Error</option>'; }
}

async function loadUploadStations(electionId) {
  const sel = document.getElementById('stationSelect');
  sel.innerHTML = '<option value="">Cargando...</option>';
  if (!electionId) { sel.innerHTML = '<option value="">Primero seleccione elección</option>'; return; }
  try {
    const r = await fetch('<?= base_url('ajax/stations-by-election') ?>?election_id=' + electionId);
    const d = await r.json();
    sel.innerHTML = '<option value="">Seleccione JRV...</option>';
    d.forEach(s => { sel.innerHTML += '<option value="' + s.id + '">' + s.numero_junta + ' (' + (s.genero ? s.genero.charAt(0).toUpperCase() + s.genero.slice(1) : '') + ') - ' + s.name_precinct + '</option>'; });
  } catch { sel.innerHTML = '<option value="">Error</option>'; }
}

document.getElementById('upElection')?.addEventListener('change', function() {
  loadUploadDignities(this.value);
  loadUploadStations(this.value);
});

async function openUploadFor(psId, electionId, dignityId) {
  const election = document.getElementById('upElection');
  election.value = electionId;
  await Promise.all([loadUploadDignities(electionId), loadUploadStations(electionId)]);
  document.getElementById('stationSelect').value = psId;
  document.getElementById('upDignity').value = dignityId;
  document.getElementById('upReason').value = '';
  document.getElementById('upNotes').value = '';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('uploadModal')).show();
}
function del(id) { confirmDelete('¿Eliminar esta acta?').then(r => { if(r.isConfirmed) window.location='<?= base_url('minutes/delete') ?>/'+id; }); }
</script>
<?php endif; ?>

<?= $this->include('minutes/_edit_modal') ?>
<?= $this->include('minutes/_edit_script') ?>
<?= $this->endSection() ?>
