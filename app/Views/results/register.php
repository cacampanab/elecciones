<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header"><h1>Registrar Resultados Electorales</h1></div>

<div class="row g-3">
  <!-- LEFT: Station selector -->
  <div class="col-md-4">
    <?php if (is_supervisor_or_admin()): ?>
    <!-- Admin/Supervisor: hierarchical filter -->
    <div class="card">
      <div class="card-header"><i class="bi bi-geo-alt me-2"></i>Seleccionar JRV</div>
      <div class="card-body">
        <div class="mb-2">
          <label class="form-label small">Elección</label>
          <select id="adminElection" class="form-select form-select-sm">
            <option value="">Seleccione...</option>
            <?php foreach ($elections as $e): ?><option value="<?= $e->id ?>"><?= esc($e->name) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Provincia</label>
          <select id="adminProvince" class="form-select form-select-sm">
            <option value="">Seleccione...</option>
            <?php foreach ($provinces as $p): ?><option value="<?= $p->id ?>"><?= esc($p->name) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Cantón</label>
          <select id="adminCanton" class="form-select form-select-sm" disabled>
            <option value="">Primero provincia</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Parroquia</label>
          <select id="adminParish" class="form-select form-select-sm" disabled>
            <option value="">Primero cantón</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Recinto</label>
          <select id="adminPrecinct" class="form-select form-select-sm" disabled>
            <option value="">Primero parroquia</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">JRV</label>
          <select id="adminStation" class="form-select form-select-sm" disabled>
            <option value="">Primero recinto</option>
          </select>
        </div>
      </div>
    </div>
    <?php else: ?>
    <!-- Operator: assigned stations list -->
    <div class="card">
      <div class="card-header"><i class="bi bi-building-check me-2"></i>Mis JRV Asignadas</div>
      <div class="list-group list-group-flush" style="max-height:500px;overflow-y:auto">
        <?php if (empty($myStations)): ?>
        <div class="list-group-item text-muted text-center small">No tiene JRV asignadas</div>
        <?php else: foreach ($myStations as $s): ?>
        <button type="button" class="list-group-item list-group-item-action station-btn"
          data-id="<?= $s->polling_station_id ?>"
          data-numero="<?= esc($s->numero_junta) ?>"
          data-genero="<?= esc($s->genero) ?>"
          data-province="<?= esc($s->name_province) ?>"
          data-canton="<?= esc($s->name_canton) ?>"
          data-parish="<?= esc($s->name_parish) ?>"
          data-precinct="<?= esc($s->name_precinct) ?>"
          data-electores="<?= $s->numero_electores ?>"
          data-eid="<?= $s->election_id ?>">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong>JRV <?= esc($s->numero_junta) ?></strong>
              <span class="badge" style="background:<?= ($s->genero ?? '') === 'femenina' ? '#e83e8c' : '#0d6efd' ?>;font-size:.6rem"><?= ucfirst($s->genero ?? '') ?></span><br>
              <small class="text-muted"><?= esc($s->name_precinct) ?></small>
            </div>
            <span class="badge bg-<?= $s->status === 'pendiente' ? 'danger' : ($s->status === 'asignada' ? 'warning' : ($s->status === 'en_proceso' ? 'info' : 'success')) ?> rounded-pill"><?= ucfirst(str_replace('_', ' ', $s->status)) ?></span>
          </div>
        </button>
        <?php endforeach; endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Registration form -->
  <div class="col-md-8" id="formCol">
    <div class="card" id="noSelection">
      <div class="card-body text-center text-muted py-5">
        <i class="bi bi-hand-index fs-1"></i><br>Seleccione una JRV para registrar resultados
      </div>
    </div>

    <div class="card" id="resultForm" style="display:none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span id="stationInfo"></span>
        <span id="stationStatus" class="badge"></span>
      </div>
      <div class="card-body">
        <form method="post" action="<?= base_url('results/save') ?>" enctype="multipart/form-data" id="resultFormInner">
          <?= csrf_field() ?>
          <input type="hidden" name="polling_station_id" id="psId">
          <input type="hidden" name="election_id" id="electionId">
          <input type="hidden" name="dignity_id" id="fDignityId">
          <input type="hidden" name="latitude" id="fLat">
          <input type="hidden" name="longitude" id="fLng">

          <div class="row g-2 mb-3">
            <div class="col-md-3">
              <label class="form-label small">Electores en el Acta *</label>
              <input type="number" inputmode="numeric" name="registered_voters" id="f_registered" class="form-control form-control-sm" required min="0" step="1"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                oninput="if(this.value) this.value = this.value.replace(/[^0-9]/g,'')">
              <small id="electoresDistributivo" class="text-muted" style="font-size:.7rem"></small>
            </div>
            <div class="col-md-3">
              <label class="form-label small">Total Sufragantes *</label>
              <input type="number" inputmode="numeric" name="total_voters" id="f_total_voters" class="form-control form-control-sm" required step="1"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                oninput="if(this.value) this.value = this.value.replace(/[^0-9]/g,'')">
            </div>
            <div class="col-md-3">
              <label class="form-label small">V. Blancos</label>
              <input type="number" inputmode="numeric" name="blank_votes" id="f_blank" class="form-control form-control-sm" value="0" min="0" step="1"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                oninput="if(this.value) this.value = this.value.replace(/[^0-9]/g,'')">
            </div>
            <div class="col-md-3">
              <label class="form-label small">V. Nulos</label>
              <input type="number" inputmode="numeric" name="null_votes" id="f_null" class="form-control form-control-sm" value="0" min="0" step="1"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                oninput="if(this.value) this.value = this.value.replace(/[^0-9]/g,'')">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold"><i class="bi bi-award me-1"></i>Dignidad *</label>
            <select id="selDignity" class="form-select" required>
              <option value="">Seleccione una JRV primero...</option>
            </select>
            <div id="dignityStatus" class="mt-2"></div>
          </div>

          <div id="candidatesContainer" class="mb-3" style="display:none">
            <label class="form-label fw-bold"><i class="bi bi-people me-1"></i>Votos por Candidato</label>
            <div id="candidatesList" class="border rounded p-2"></div>
          </div>

          <div class="row g-2 mb-3" id="correctionFields" style="display:none">
            <div class="col-md-6">
              <label class="form-label small">Motivo de la corrección *</label>
              <input type="text" name="reason" id="f_reason" class="form-control form-control-sm" maxlength="255" placeholder="Ej. Error en la suma del acta">
            </div>
            <div class="col-md-6">
              <label class="form-label small">Observaciones</label>
              <input type="text" name="notes" id="f_notes" class="form-control form-control-sm" maxlength="255" placeholder="Opcional">
            </div>
          </div>

          <hr>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold"><i class="bi bi-camera me-1"></i>Acta Electoral</label>
              <div class="border rounded p-3 text-center bg-light" id="uploadArea">
                <input type="file" name="acta_file" id="actaFile" accept="image/*,application/pdf,.pdf" capture="environment" class="d-none" onchange="previewActa(event)">
                <button type="button" class="btn btn-outline-primary mb-2" onclick="document.getElementById('actaFile').click()">
                  <i class="bi bi-camera-fill me-1"></i> Tomar Foto / Adjuntar
                </button>
                <button type="button" class="btn btn-outline-secondary mb-2" onclick="document.getElementById('actaFile').click()">
                  <i class="bi bi-folder me-1"></i> Galería
                </button>
                <div id="actaPreview" class="small text-muted">
                  <i class="bi bi-file-earmark-image fs-1 d-block mb-1"></i>
                  Haga clic para tomar foto o adjuntar archivo
                </div>
                <div id="actaInfo" class="small text-muted mt-1"></div>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold"><i class="bi bi-geo-alt me-1"></i>Geolocalización</label>
              <div class="border rounded p-3 text-center bg-light" id="geoStatus">
                <i class="bi bi-crosshair fs-1 d-block mb-1 text-secondary" id="geoIcon"></i>
                <div id="geoText" class="small text-muted">Obteniendo ubicación...</div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="getLocation()"><i class="bi bi-arrow-clockwise me-1"></i>Reintentar</button>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary mt-3 w-100 btn-lg">
            <i class="bi bi-save me-1"></i>Guardar Resultados
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
@media (max-width: 767.98px) {
  #resultFormInner input[type="number"] { font-size: 16px; min-height: 44px; }
  #resultFormInner .btn-lg { min-height: 48px; }
  #resultFormInner select { font-size: 16px; min-height: 44px; }
}
</style>

<!-- Mobile-only modal: reuses the SAME #resultForm node, desktop flow untouched -->
<div class="modal fade" id="mobileRegisterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen-md-down modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Registrar resultados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="mobileModalBody"></div>
    </div>
  </div>
</div>

<script>
let currentPsId = null;
let currentElectionId = null;
let currentDignityId = null;
let currentMinute = null;
let hasExistingResults = false;
let currentDistributivo = 0;
let isLocked = false;

const isOperatorUser = <?= is_operator() ? 'true' : 'false' ?>;
const isSupervisorOrAdminUser = <?= is_supervisor_or_admin() ? 'true' : 'false' ?>;
const baseUrl = '<?= base_url('') ?>';

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
}

function statusLabel(status) {
  return {registrada: 'Registrada', con_novedad: 'Con novedad', verificada: 'Verificada'}[status] || status || 'Sin acta';
}

function setFormLocked(locked) {
  isLocked = locked;
  const form = document.getElementById('resultFormInner');
  if (!form) return;
  form.querySelectorAll('input, select, textarea, button').forEach(el => {
    if (el.type === 'hidden' || el.id === 'selDignity') return;
    el.disabled = locked;
  });
}

function renderDignityStatus(extraMessage = '') {
  const box = document.getElementById('dignityStatus');
  if (!box) return;
  if (!currentDignityId) { box.innerHTML = ''; return; }
  if (!currentMinute) {
    box.innerHTML = '<span class="badge bg-secondary">Sin acta registrada para esta dignidad</span>' +
      (extraMessage ? '<div class="small text-warning mt-1">' + escapeHtml(extraMessage) + '</div>' : '');
    return;
  }
  const classes = {registrada: 'bg-primary', con_novedad: 'bg-warning text-dark', verificada: 'bg-success'};
  let html = '<span class="badge ' + (classes[currentMinute.status] || 'bg-secondary') + '">' + statusLabel(currentMinute.status) + '</span> ';
  if (currentMinute.consistency_status === 'ok') {
    html += '<span class="badge bg-success-subtle text-success border border-success">Consistente</span>';
  } else if (currentMinute.consistency_status === 'error') {
    html += '<span class="badge bg-danger-subtle text-danger border border-danger">Inconsistente</span>';
  }
  if (currentMinute.consistency_detail) {
    html += '<div class="small text-danger mt-1">' + escapeHtml(currentMinute.consistency_detail) + '</div>';
  }
  if (currentMinute.notes) {
    html += '<div class="small text-muted mt-1">Obs.: ' + escapeHtml(currentMinute.notes) + '</div>';
  }
  if (extraMessage) {
    html += '<div class="small text-warning mt-1">' + escapeHtml(extraMessage) + '</div>';
  }
  box.innerHTML = html;
}

function renderActaPreview(minute) {
  const preview = document.getElementById('actaPreview');
  const info = document.getElementById('actaInfo');
  if (!preview || !info) return;
  if (!minute) {
    preview.innerHTML = '<i class="bi bi-file-earmark-image fs-1 d-block mb-1"></i>Haga clic para tomar foto o adjuntar archivo';
    info.innerHTML = '';
    return;
  }
  const ext = (minute.file_type || '').toLowerCase();
  if (['jpg', 'jpeg', 'png'].includes(ext)) {
    preview.innerHTML = '<img src="' + minute.web_path + '" class="img-fluid rounded" style="max-height:150px;cursor:pointer" onclick="window.open(this.src)">';
  } else {
    preview.innerHTML = '<i class="bi bi-filetype-pdf fs-1 d-block mb-1 text-danger"></i><span class="small">' + escapeHtml(minute.original_name) + '</span>';
  }
  info.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Acta v' + minute.version +
    ' - <a href="' + baseUrl + 'minutes/download/' + minute.id + '" target="_blank">Descargar</a></span>';
}

// Geolocation
function getLocation() {
  const geoIcon = document.getElementById('geoIcon'), geoText = document.getElementById('geoText');
  if (!navigator.geolocation) { geoText.innerHTML = '<span class="text-danger">No soportado</span>'; return; }
  geoIcon.className = 'bi bi-arrow-clockwise fs-1 d-block mb-1 text-warning';
  geoText.innerHTML = 'Obteniendo ubicación...';
  navigator.geolocation.getCurrentPosition(
    p => { document.getElementById('fLat').value = p.coords.latitude; document.getElementById('fLng').value = p.coords.longitude;
      geoIcon.className = 'bi bi-check-circle fs-1 d-block mb-1 text-success';
      geoText.innerHTML = '<span class="text-success">Capturada</span><br><small>'+p.coords.latitude.toFixed(6)+', '+p.coords.longitude.toFixed(6)+'</small>'; },
    e => { geoIcon.className = 'bi bi-exclamation-triangle fs-1 d-block mb-1 text-danger'; geoText.innerHTML = '<span class="text-danger">'+e.message+'</span>'; }
  );
}

// Acta preview
function previewActa(event) {
  const file = event.target.files[0], preview = document.getElementById('actaPreview'), info = document.getElementById('actaInfo');
  if (!file) return;
  info.textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
  if (file.type.startsWith('image/')) {
    const r = new FileReader();
    r.onload = e => { preview.innerHTML = '<img src="'+e.target.result+'" class="img-fluid rounded" style="max-height:150px">'; };
    r.readAsDataURL(file);
  } else {
    preview.innerHTML = '<i class="bi bi-filetype-pdf fs-1 d-block mb-1 text-danger"></i><span class="small">PDF: '+file.name+'</span>';
  }
}

// ---- Mobile modal: reuse the SAME form node (desktop flow untouched) ----
const isMobileView = () => window.matchMedia('(max-width: 767.98px)').matches;
function openMobileModal() {
  const form = document.getElementById('resultForm');
  const body = document.getElementById('mobileModalBody');
  if (!form || !body) return;
  if (form.parentElement !== body) body.appendChild(form);
  const el = document.getElementById('mobileRegisterModal');
  if (el) bootstrap.Modal.getOrCreateInstance(el).show();
}
document.getElementById('mobileRegisterModal')?.addEventListener('hidden.bs.modal', function() {
  const form = document.getElementById('resultForm');
  const home = document.getElementById('formCol');
  if (form && home && form.parentElement !== home) home.appendChild(form);
});

// Select station
async function selectStation(psId, numero, genero, province, canton, parish, precinct, electores, eid) {
  currentPsId = psId; currentElectionId = eid; currentDignityId = null; currentMinute = null; hasExistingResults = false;
  currentDistributivo = parseInt(electores || '0', 10) || 0;
  document.getElementById('electoresDistributivo').textContent = currentDistributivo ? 'Distributivo: ' + currentDistributivo : '';
  document.getElementById('stationInfo').innerHTML = '<strong>JRV ' + numero + '</strong> ' +
    '<span class="badge" style="background:' + (genero === 'femenina' ? '#e83e8c' : '#0d6efd') + '">' + (genero ? genero.charAt(0).toUpperCase()+genero.slice(1) : '') + '</span>' +
    ' &middot; ' + province + ' / ' + canton + ' / ' + parish + ' / ' + precinct;
  document.getElementById('psId').value = psId; document.getElementById('electionId').value = eid;
  document.getElementById('fDignityId').value = '';
  document.getElementById('f_registered').value = electores;
  document.getElementById('f_total_voters').value = ''; document.getElementById('f_blank').value = 0; document.getElementById('f_null').value = 0;
  document.getElementById('f_reason').value = ''; document.getElementById('f_notes').value = '';
  document.getElementById('correctionFields').style.display = 'none';
  document.getElementById('f_reason').required = false;
  document.getElementById('candidatesContainer').style.display = 'none';
  document.getElementById('candidatesList').innerHTML = '';
  document.getElementById('dignityStatus').innerHTML = '';
  document.getElementById('resultForm').style.display = 'block'; document.getElementById('noSelection').style.display = 'none';
  setFormLocked(false);
  renderActaPreview(null);
  getLocation();
  await loadDignities(eid);
  if (isMobileView()) openMobileModal();
}

async function loadDignities(electionId) {
  const sel = document.getElementById('selDignity');
  sel.innerHTML = '<option value="">Cargando...</option>';
  sel.disabled = true;
  try {
    const r = await fetch(baseUrl + 'ajax/election-dignities/' + electionId);
    const data = await r.json();
    sel.innerHTML = '<option value="">Seleccione dignidad...</option>';
    data.forEach(d => { sel.innerHTML += '<option value="' + d.id + '">' + escapeHtml(d.name) + '</option>'; });
    sel.disabled = false;
    if (data.length === 1) {
      sel.value = data[0].id;
      await onDignityChange();
    }
  } catch {
    sel.innerHTML = '<option value="">Error al cargar dignidades</option>';
  }
}

async function onDignityChange() {
  const sel = document.getElementById('selDignity');
  const dignityId = parseInt(sel.value || '0', 10);
  currentDignityId = dignityId || null;
  currentMinute = null; hasExistingResults = false;
  document.getElementById('fDignityId').value = dignityId || '';
  document.getElementById('correctionFields').style.display = 'none';
  document.getElementById('f_reason').value = ''; document.getElementById('f_notes').value = '';
  document.getElementById('f_reason').required = false;
  setFormLocked(false);

  const container = document.getElementById('candidatesContainer');
  const list = document.getElementById('candidatesList');
  const box = document.getElementById('dignityStatus');
  box.innerHTML = '';

  if (!dignityId || !currentElectionId || !currentPsId) {
    container.style.display = 'none';
    list.innerHTML = '';
    renderActaPreview(null);
    return;
  }

  list.innerHTML = '<div class="text-center py-2 text-muted">Cargando candidatos...</div>';
  container.style.display = 'block';

  try {
    const [rCand, rExist, rMinute] = await Promise.all([
      fetch(baseUrl + 'ajax/candidates-by-dignity/' + currentElectionId + '/' + dignityId),
      fetch(baseUrl + 'ajax/station-results/' + currentPsId + '/' + dignityId),
      fetch(baseUrl + 'ajax/station-current-acta/' + currentPsId + '?dignity_id=' + dignityId)
    ]);
    const candRaw = await rCand.json();
    const candidates = Array.isArray(candRaw) ? candRaw : (candRaw.data || []);
    const existing = await rExist.json();
    const minute = await rMinute.json();

    currentMinute = (minute && minute.id) ? minute : null;
    hasExistingResults = Array.isArray(existing) && existing.length > 0;

    const votesMap = {};
    (Array.isArray(existing) ? existing : []).forEach(r => votesMap[r.candidate_id] = r);

    if (!candidates.length) {
      list.innerHTML = '<div class="text-center py-2 text-muted">No hay candidatos para esta dignidad</div>';
    } else {
      list.innerHTML = candidates.map(c => {
        const saved = votesMap[c.id];
        const val = saved ? saved.votes : 0;
        return '<div class="row g-1 align-items-center mb-1 py-1 border-bottom">' +
          '<div class="col-1 text-center"><span class="badge" style="background:' + (c.party_color || '#6c757d') + '">' + escapeHtml(c.list_number || '?') + '</span></div>' +
          '<div class="col-6"><span class="small">' + escapeHtml(c.first_name) + ' ' + escapeHtml(c.last_name) + '</span> <small class="text-muted">(' + escapeHtml(c.party_acronym) + ')</small>' +
          (saved ? ' <span class="badge bg-success ms-1" style="font-size:.6rem">guardado</span>' : '') + '</div>' +
          '<div class="col-3"><input type="number" inputmode="numeric" name="votes[' + c.id + ']" class="form-control form-control-sm vote-input" placeholder="0" value="' + val + '" min="0" step="1" onkeypress="return event.charCode>=48&&event.charCode<=57" oninput="if(this.value)this.value=this.value.replace(/[^0-9]/g,\'\')"></div>' +
          '<div class="col-2"><input type="hidden" name="dignity_ids[' + c.id + ']" value="' + c.dignity_id + '"></div>' +
          '</div>';
      }).join('');
    }

    if (hasExistingResults) {
      const first = existing[0];
      document.getElementById('f_total_voters').value = first.total_voters || 0;
      document.getElementById('f_blank').value = first.blank_votes || 0;
      document.getElementById('f_null').value = first.null_votes || 0;
      document.getElementById('f_registered').value = first.registered_voters || 0;
    } else {
      document.getElementById('f_total_voters').value = '';
      document.getElementById('f_blank').value = 0;
      document.getElementById('f_null').value = 0;
      document.getElementById('f_registered').value = currentDistributivo || '';
    }

    const isCorrection = !!(currentMinute || hasExistingResults);
    document.getElementById('correctionFields').style.display = isCorrection ? 'flex' : 'none';
    document.getElementById('f_reason').required = isCorrection;
    if (currentMinute && currentMinute.notes) {
      document.getElementById('f_notes').value = currentMinute.notes;
    }

    let lock = false;
    let message = '';
    if (currentMinute) {
      if (currentMinute.status === 'verificada') {
        if (!isSupervisorOrAdminUser) {
          lock = true;
          message = 'Acta verificada. Solo un supervisor o administrador puede modificarla.';
        } else {
          message = 'Acta verificada: al guardar cambios se desverificará y se creará una nueva versión.';
        }
      } else if (isOperatorUser && currentMinute.status !== 'con_novedad') {
        lock = true;
        message = 'Acta ya registrada. Solo puede corregirla cuando tenga una novedad.';
      } else if (currentMinute.status === 'con_novedad') {
        message = 'Acta con novedad: puede corregirla indicando el motivo.';
      }
    }

    setFormLocked(lock);
    renderDignityStatus(message);
    renderActaPreview(currentMinute);
  } catch (e) {
    list.innerHTML = '<div class="text-center py-2 text-danger">Error al cargar los datos de la dignidad</div>';
  }
}

// ---- Operator station buttons ----
document.querySelectorAll('.station-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.station-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    selectStation(this.dataset.id, this.dataset.numero, this.dataset.genero, this.dataset.province,
      this.dataset.canton, this.dataset.parish, this.dataset.precinct, this.dataset.electores, this.dataset.eid);
  });
});

// ---- Admin hierarchical selectors ----
<?php if (is_supervisor_or_admin()): ?>
['adminProvince','adminCanton','adminParish','adminPrecinct'].forEach((id,i) => {
  document.getElementById(id)?.addEventListener('change', async function() {
    const next = ['adminCanton','adminParish','adminPrecinct','adminStation'][i];
    const endpoint = ['ajax/cantons','ajax/parishes','ajax/precincts','ajax/stations-by-precinct'][i];
    const sel = document.getElementById(next);
    sel.innerHTML = '<option value="">Cargando...</option>'; sel.disabled = true;
    for (let j=i+2; j<=4; j++) {
      const s = document.getElementById(['adminCanton','adminParish','adminPrecinct','adminStation'][j]);
      if (s) { s.innerHTML = '<option value="">Primero anterior</option>'; s.disabled = true; }
    }
    if (!this.value) { sel.innerHTML = '<option value="">Seleccione primero</option>'; return; }
    let url = '<?= base_url('') ?>' + endpoint + '?';
    if (i===0) url += 'province_id='+this.value;
    else if (i===1) url += 'canton_id='+this.value;
    else if (i===2) url += 'parish_id='+this.value;
    else if (i===3) url += 'precinct_id='+this.value+'&election_id='+document.getElementById('adminElection').value;
    const r = await fetch(url); const d = await r.json();
    sel.innerHTML = '<option value="">Seleccione...</option>';
    d.forEach(item => { sel.innerHTML += '<option value="'+item.id+'">'+(item.numero_junta ? item.numero_junta+' ('+(item.genero?item.genero.charAt(0).toUpperCase()+item.genero.slice(1):'')+') - '+item.name_precinct : item.name)+'</option>'; });
    sel.disabled = false;
  });
});
document.getElementById('adminStation')?.addEventListener('change', function() {
  if (!this.value) return;
  const opt = this.options[this.selectedIndex];
  // We need to fetch station details
  fetch('<?= base_url('') ?>ajax/stations-by-precinct?precinct_id='+document.getElementById('adminPrecinct').value+'&election_id='+document.getElementById('adminElection').value)
    .then(r=>r.json()).then(d => {
      const ps = d.find(s => s.id == this.value);
      if (ps) selectStation(ps.id, ps.numero_junta, ps.genero, ps.name_province, ps.name_canton, ps.name_parish, ps.name_precinct, ps.numero_electores, ps.election_id);
    });
});
<?php endif; ?>

document.getElementById('selDignity')?.addEventListener('change', onDignityChange);

document.getElementById('resultFormInner')?.addEventListener('submit', function(e) {
  if (!currentDignityId) {
    e.preventDefault();
    alert('Seleccione la dignidad.');
    return;
  }
  if (isLocked) {
    e.preventDefault();
    alert('El acta está bloqueada para edición.');
  }
});

document.getElementById('uploadArea')?.addEventListener('dragover', e => { e.preventDefault(); e.target.classList.add('border-primary'); });
document.getElementById('uploadArea')?.addEventListener('dragleave', e => { e.target.classList.remove('border-primary'); });
document.getElementById('uploadArea')?.addEventListener('drop', e => {
  e.preventDefault(); e.target.classList.remove('border-primary');
  if (e.dataTransfer.files.length) { document.getElementById('actaFile').files = e.dataTransfer.files; previewActa({target:{files:e.dataTransfer.files}}); }
});
getLocation();
</script>
<?= $this->endSection() ?>
