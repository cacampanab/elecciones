<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<?php
  $canUpload = has_permission('minutes.upload');
  $canRegister = has_permission('results.create') || has_permission('results.update');
  $canEditActa = has_permission('results.update') || has_permission('results.verify');
  $canDelete = has_permission('minutes.delete');

  $missingQuery = ['election_id' => $electionId, 'missing' => 1];
  if ($dignityId) $missingQuery['dignity_id'] = $dignityId;
  if ($search !== '') $missingQuery['q'] = $search;
  if ($provinceId) $missingQuery['province_id'] = $provinceId;
  if ($cantonId) $missingQuery['canton_id'] = $cantonId;
  if ($parishId) $missingQuery['parish_id'] = $parishId;
  if ($precinctId) $missingQuery['precinct_id'] = $precinctId;
  $missingUrl = base_url('minutes?' . http_build_query($missingQuery));
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
      <?php if ($missing): ?><input type="hidden" name="missing" value="1"><?php endif; ?>
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
        <a href="<?= $missingUrl ?>" class="btn btn-sm <?= $missing ? 'btn-warning' : 'btn-outline-warning' ?>" title="Solo JRV-dignidad sin acta"><i class="bi bi-exclamation-triangle"></i> Faltantes</a>
        <a href="<?= base_url('minutes') ?>" class="btn btn-sm btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
      </div>
      <div class="col-12">
        <div class="row g-2 mt-1">
          <div class="col-6 col-md-3">
            <label class="form-label small mb-0">Provincia</label>
            <select name="province_id" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">Todas</option>
              <?php foreach ($provinces as $province): ?>
                <option value="<?= (int)$province->id ?>" <?= (int)$provinceId === (int)$province->id ? 'selected' : '' ?>><?= esc($province->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label small mb-0">Cantón</label>
            <select name="canton_id" class="form-select form-select-sm" onchange="this.form.submit()" <?= empty($cantons) ? 'disabled' : '' ?>>
              <option value=""><?= empty($cantons) ? 'Seleccione provincia' : 'Todos' ?></option>
              <?php foreach ($cantons as $canton): ?>
                <option value="<?= (int)$canton->id ?>" <?= (int)$cantonId === (int)$canton->id ? 'selected' : '' ?>><?= esc($canton->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label small mb-0">Parroquia</label>
            <select name="parish_id" class="form-select form-select-sm" onchange="this.form.submit()" <?= empty($parishes) ? 'disabled' : '' ?>>
              <option value=""><?= empty($parishes) ? 'Seleccione cantón' : 'Todas' ?></option>
              <?php foreach ($parishes as $parish): ?>
                <option value="<?= (int)$parish->id ?>" <?= (int)$parishId === (int)$parish->id ? 'selected' : '' ?>><?= esc($parish->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label small mb-0">Recinto</label>
            <select name="precinct_id" class="form-select form-select-sm" onchange="this.form.submit()" <?= empty($precincts) ? 'disabled' : '' ?>>
              <option value=""><?= empty($precincts) ? 'Seleccione parroquia' : 'Todos' ?></option>
              <?php foreach ($precincts as $precinct): ?>
                <option value="<?= (int)$precinct->id ?>" <?= (int)$precinctId === (int)$precinct->id ? 'selected' : '' ?>><?= esc($precinct->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
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
                <?php if ($canRegister): ?>
                <a class="btn btn-sm btn-success" href="<?= base_url('results/register?polling_station_id=' . (int)$m->polling_station_id . '&dignity_id=' . (int)$m->dignity_id . '&election_id=' . (int)$m->election_id . '&return_to=' . urlencode($missingUrl)) ?>" title="Llenar acta"><i class="bi bi-pencil-square me-1"></i>Llenar acta</a>
                <?php endif; ?>
                <?php if ($canUpload): ?>
                <button class="btn btn-sm btn-outline-primary" onclick="openUploadFor(<?= (int)$m->polling_station_id ?>, <?= (int)$m->election_id ?>, <?= (int)$m->dignity_id ?>, <?= htmlspecialchars(json_encode(($m->numero_junta ?? 'JRV') . ' - ' . ($m->name_precinct ?? '')), ENT_QUOTES, 'UTF-8') ?>)" title="Subir acta faltante"><i class="bi bi-upload"></i></button>
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
    <div class="row g-2 mb-3">
      <div class="col-6">
        <label class="form-label small">Provincia</label>
        <select id="upProvince" class="form-select form-select-sm">
          <option value="">Seleccione</option>
          <?php foreach ($provinces as $province): ?>
          <option value="<?= (int)$province->id ?>"><?= esc($province->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6">
        <label class="form-label small">Cantón</label>
        <select id="upCanton" class="form-select form-select-sm"><option value="">Seleccione provincia</option></select>
      </div>
      <div class="col-6">
        <label class="form-label small">Parroquia</label>
        <select id="upParish" class="form-select form-select-sm"><option value="">Seleccione cantón</option></select>
      </div>
      <div class="col-6">
        <label class="form-label small">Recinto</label>
        <select id="upPrecinct" class="form-select form-select-sm"><option value="">Seleccione parroquia</option></select>
      </div>
    </div>
    <div class="mb-3"><label class="form-label">JRV *</label><select name="polling_station_id" id="stationSelect" class="form-select" required><option value="">Primero seleccione ubicación y elección</option></select></div>
    <div class="mb-3"><label class="form-label">Motivo del reemplazo</label><input type="text" name="reason" id="upReason" class="form-control" maxlength="255" placeholder="Obligatorio si reemplaza un acta existente"></div>
    <div class="mb-3"><label class="form-label">Observaciones</label><input type="text" name="notes" id="upNotes" class="form-control" maxlength="255" placeholder="Opcional"></div>
    <div class="mb-3"><label class="form-label">Archivo (PDF, JPG, PNG) *</label><input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Subir</button></div>
</form></div></div>

<script>
const minutesBase = '<?= base_url('') ?>';
let uploadPreset = false;

function fillUploadSelect(select, items, placeholder) {
  if (!select) return;
  select.innerHTML = '';
  const empty = document.createElement('option');
  empty.value = '';
  empty.textContent = placeholder;
  select.appendChild(empty);
  (Array.isArray(items) ? items : []).forEach(function (item) {
    const option = document.createElement('option');
    option.value = item.id;
    option.textContent = item.name;
    select.appendChild(option);
  });
}

function resetUploadLocation() {
  fillUploadSelect(document.getElementById('upCanton'), [], 'Seleccione provincia');
  fillUploadSelect(document.getElementById('upParish'), [], 'Seleccione cantón');
  fillUploadSelect(document.getElementById('upPrecinct'), [], 'Seleccione parroquia');
  fillUploadSelect(document.getElementById('stationSelect'), [], 'Seleccione ubicación y elección');
}

async function loadUploadDignities(electionId) {
  const sel = document.getElementById('upDignity');
  sel.innerHTML = '<option value="">Cargando...</option>';
  if (!electionId) { sel.innerHTML = '<option value="">Primero seleccione elección</option>'; return; }
  try {
    const r = await fetch(minutesBase + 'ajax/election-dignities/' + electionId);
    const d = await r.json();
    sel.innerHTML = '<option value="">Seleccione dignidad...</option>';
    d.forEach(x => {
      const option = document.createElement('option');
      option.value = x.id;
      option.textContent = x.name;
      sel.appendChild(option);
    });
  } catch { sel.innerHTML = '<option value="">Error</option>'; }
}

async function loadUploadCantons(provinceId) {
  const sel = document.getElementById('upCanton');
  fillUploadSelect(sel, [], 'Cargando...');
  fillUploadSelect(document.getElementById('upParish'), [], 'Seleccione cantón');
  fillUploadSelect(document.getElementById('upPrecinct'), [], 'Seleccione parroquia');
  fillUploadSelect(document.getElementById('stationSelect'), [], 'Seleccione ubicación y elección');
  if (!provinceId) { fillUploadSelect(sel, [], 'Seleccione provincia'); return; }
  try {
    const r = await fetch(minutesBase + 'ajax/cantons?province_id=' + provinceId);
    fillUploadSelect(sel, await r.json(), 'Seleccione cantón');
  } catch { fillUploadSelect(sel, [], 'Error al cargar'); }
}

async function loadUploadParishes(cantonId) {
  const sel = document.getElementById('upParish');
  fillUploadSelect(sel, [], 'Cargando...');
  fillUploadSelect(document.getElementById('upPrecinct'), [], 'Seleccione parroquia');
  fillUploadSelect(document.getElementById('stationSelect'), [], 'Seleccione ubicación y elección');
  if (!cantonId) { fillUploadSelect(sel, [], 'Seleccione cantón'); return; }
  try {
    const r = await fetch(minutesBase + 'ajax/parishes?canton_id=' + cantonId);
    fillUploadSelect(sel, await r.json(), 'Seleccione parroquia');
  } catch { fillUploadSelect(sel, [], 'Error al cargar'); }
}

async function loadUploadPrecincts(parishId) {
  const sel = document.getElementById('upPrecinct');
  fillUploadSelect(sel, [], 'Cargando...');
  fillUploadSelect(document.getElementById('stationSelect'), [], 'Seleccione ubicación y elección');
  if (!parishId) { fillUploadSelect(sel, [], 'Seleccione parroquia'); return; }
  try {
    const r = await fetch(minutesBase + 'ajax/precincts?parish_id=' + parishId);
    fillUploadSelect(sel, await r.json(), 'Seleccione recinto');
  } catch { fillUploadSelect(sel, [], 'Error al cargar'); }
}

async function loadUploadStations(precinctId) {
  const sel = document.getElementById('stationSelect');
  const electionId = document.getElementById('upElection').value;
  fillUploadSelect(sel, [], 'Cargando...');
  if (!precinctId || !electionId) {
    fillUploadSelect(sel, [], 'Seleccione elección y recinto');
    return;
  }
  try {
    const r = await fetch(minutesBase + 'ajax/stations-by-precinct?precinct_id=' + precinctId + '&election_id=' + electionId);
    const data = await r.json();
    sel.innerHTML = '';
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = 'Seleccione JRV...';
    sel.appendChild(empty);
    (Array.isArray(data) ? data : []).forEach(function (station) {
      const option = document.createElement('option');
      option.value = station.id;
      const genero = station.genero ? station.genero.charAt(0).toUpperCase() + station.genero.slice(1) : '';
      option.textContent = station.numero_junta + (genero ? ' (' + genero + ')' : '') + ' - ' + station.name_precinct;
      sel.appendChild(option);
    });
  } catch {
    fillUploadSelect(sel, [], 'Error al cargar');
  }
}

document.getElementById('upElection')?.addEventListener('change', function() {
  loadUploadDignities(this.value);
  const province = document.getElementById('upProvince');
  if (province) province.value = '';
  resetUploadLocation();
});

document.getElementById('upProvince')?.addEventListener('change', function() {
  loadUploadCantons(this.value);
});

document.getElementById('upCanton')?.addEventListener('change', function() {
  loadUploadParishes(this.value);
});

document.getElementById('upParish')?.addEventListener('change', function() {
  loadUploadPrecincts(this.value);
});

document.getElementById('upPrecinct')?.addEventListener('change', function() {
  loadUploadStations(this.value);
});

document.getElementById('uploadModal')?.addEventListener('show.bs.modal', function() {
  if (uploadPreset) {
    uploadPreset = false;
    return;
  }
  document.getElementById('uploadModal').querySelector('form').reset();
  resetUploadLocation();
});

async function openUploadFor(psId, electionId, dignityId, stationLabel) {
  uploadPreset = true;
  document.getElementById('upElection').value = electionId;
  await loadUploadDignities(electionId);
  document.getElementById('upDignity').value = dignityId;

  const stationSelect = document.getElementById('stationSelect');
  stationSelect.innerHTML = '';
  const option = document.createElement('option');
  option.value = psId;
  option.textContent = stationLabel || ('JRV ' + psId);
  stationSelect.appendChild(option);
  stationSelect.value = psId;

  document.getElementById('upReason').value = '';
  document.getElementById('upNotes').value = '';
  document.querySelector('#uploadModal input[type="file"]').value = '';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('uploadModal')).show();
}
function del(id) { confirmDelete('¿Eliminar esta acta?').then(r => { if(r.isConfirmed) window.location='<?= base_url('minutes/delete') ?>/'+id; }); }
</script>
<?php endif; ?>

<?= $this->include('minutes/_edit_modal') ?>
<?= $this->include('minutes/_edit_script') ?>
<?= $this->endSection() ?>
