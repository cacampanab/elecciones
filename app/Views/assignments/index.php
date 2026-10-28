<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Asignaciones</h1></div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal" onclick="resetAssignForm()"><i class="bi bi-plus-lg me-1"></i>Asignar Operador</button>
</div>
<div class="card">
  <div class="card-body pb-0">
    <div class="row g-1 mb-2">
      <div class="col-6 col-md-3"><input type="text" id="filterOperador" class="form-control form-control-sm" placeholder="Operador" onkeyup="filterTable()"></div>
      <div class="col-6 col-md-3">
        <select id="filterElection" class="form-select form-select-sm" onchange="filterTable()">
          <option value="">Todas las elecciones</option>
          <?php foreach ($elections as $e): ?><option value="<?= esc($e->name) ?>"><?= esc($e->name) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <select id="filterStatus" class="form-select form-select-sm" onchange="filterTable()">
          <option value="">Todos los estados</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Asignada">Asignada</option>
          <option value="En proceso">En proceso</option>
          <option value="Reportada">Reportada</option>
          <option value="Revisada">Revisada</option>
          <option value="Aprobada">Aprobada</option>
        </select>
      </div>
      <div class="col-6 col-md-3"><button class="btn btn-sm btn-outline-secondary w-100" onclick="clearFilters()"><i class="bi bi-x-circle me-1"></i>Limpiar</button></div>
      <div class="col-6 col-md-3"><input type="text" id="filterProvincia" class="form-control form-control-sm" placeholder="Provincia" onkeyup="filterTable()"></div>
      <div class="col-6 col-md-3"><input type="text" id="filterCanton" class="form-control form-control-sm" placeholder="Cantón" onkeyup="filterTable()"></div>
      <div class="col-6 col-md-3"><input type="text" id="filterParroquia" class="form-control form-control-sm" placeholder="Parroquia" onkeyup="filterTable()"></div>
      <div class="col-6 col-md-3"><input type="text" id="filterRecinto" class="form-control form-control-sm" placeholder="Recinto" onkeyup="filterTable()"></div>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0 data-table" id="assignTable">
      <thead class="table-light"><tr><th>#</th><th>Operador</th><th>JRV</th><th>Género</th><th>Provincia</th><th>Cantón</th><th>Parroquia</th><th>Recinto</th><th>Elección</th><th>Tipo</th><th>Estado JRV</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php $i=1; foreach ($assignments as $a): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><strong><?= esc($a->first_name . ' ' . $a->last_name) ?></strong><br><small class="text-muted">@<?= esc($a->username) ?></small></td>
          <td><strong><?= esc($a->numero_junta) ?></strong></td>
          <td>
            <span class="badge" style="background:<?= ($a->genero ?? '') === 'femenina' ? '#e83e8c' : '#0d6efd' ?>">
              <?= ucfirst($a->genero ?? '') ?>
            </span>
          </td>
          <td><?= esc($a->name_province) ?></td>
          <td><?= esc($a->name_canton) ?></td>
          <td><?= esc($a->name_parish) ?></td>
          <td><?= esc($a->name_precinct) ?></td>
          <td><span class="small text-muted"><?= esc($a->election_name ?? '') ?></span></td>
          <td><span class="badge bg-info"><?= esc($a->assignment_type) ?></span></td>
          <td><span class="badge bg-<?= $a->status === 'pendiente' ? 'danger' : ($a->status === 'asignada' ? 'warning' : ($a->status === 'reportada' ? 'success' : 'secondary')) ?>"><?= ucfirst(str_replace('_', ' ', $a->status ?? '—')) ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editAssign(<?= htmlspecialchars(json_encode($a)) ?>)" title="Cambiar operador"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="del(<?= $a->id ?>)" title="Eliminar asignación"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="assignModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= base_url('assignments/save-batch') ?>" class="modal-content">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title">Asignar Operador a JRV</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Operador *</label>
            <select name="user_id" id="newUserId" class="form-select" required>
              <option value="">Seleccione operador...</option>
              <?php foreach ($operators as $o): ?>
              <option value="<?= $o->id ?>"><?= esc($o->first_name . ' ' . $o->last_name) ?> (<?= esc($o->cedula) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Elección *</label>
            <select name="election_id" id="selElection" class="form-select" required>
              <option value="">Seleccione...</option>
              <?php foreach ($elections as $e): ?>
              <option value="<?= $e->id ?>"><?= esc($e->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipo</label>
            <select name="assignment_type" class="form-select">
              <option value="operador">Operador</option>
              <option value="coordinador">Coordinador</option>
              <option value="supervisor">Supervisor</option>
            </select>
          </div>
        </div>

        <hr>
        <h6 class="fw-bold">Seleccionar JRV por ubicación</h6>
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label small">Provincia</label>
            <select id="selProvince" class="form-select form-select-sm">
              <option value="">Seleccione...</option>
              <?php foreach ($provinces as $p): ?>
              <option value="<?= $p->id ?>"><?= esc($p->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Cantón</label>
            <select id="selCanton" class="form-select form-select-sm" disabled>
              <option value="">Primero seleccione provincia</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Parroquia</label>
            <select id="selParish" class="form-select form-select-sm" disabled>
              <option value="">Primero seleccione cantón</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Recinto</label>
            <select id="selPrecinct" class="form-select form-select-sm" disabled>
              <option value="">Primero seleccione parroquia</option>
            </select>
          </div>
        </div>

        <div id="jrvListContainer" class="mt-3" style="display:none">
          <label class="form-label fw-bold">
            JRV disponibles <small class="text-muted">(marque las que desea asignar)</small>
          </label>
          <div id="jrvCheckboxList" class="border rounded p-2" style="max-height:200px;overflow-y:auto"></div>
          <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllJRV(true)">Seleccionar Todas</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllJRV(false)">Deseleccionar Todas</button>
            <span id="selectedCount" class="ms-2 badge bg-primary">0 seleccionadas</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="btnAssign"><i class="bi bi-check-lg me-1"></i>Asignar <span id="assignCount">0</span> JRV</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Assignment Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= base_url('assignments/update') ?>" class="modal-content">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="assignId">
      <input type="hidden" name="old_user_id" id="oldUser">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Cambiar Operador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="editInfo"></div>
        <div class="mb-3">
          <label class="form-label">Nuevo Operador *</label>
          <select name="user_id" id="eUser" class="form-select" required>
            <option value="">Seleccione operador...</option>
            <?php foreach ($operators as $o): ?>
            <option value="<?= $o->id ?>"><?= esc($o->first_name . ' ' . $o->last_name) ?> (<?= esc($o->cedula) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Tipo</label>
          <select name="assignment_type" id="eType" class="form-select">
            <option value="operador">Operador</option>
            <option value="coordinador">Coordinador</option>
            <option value="supervisor">Supervisor</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
function editAssign(a) {
  document.getElementById('modalTitle').textContent = 'Cambiar Operador - JRV ' + a.numero_junta;
  document.getElementById('assignId').value = a.assignment_id;
  document.getElementById('eUser').value = a.user_id;
  document.getElementById('eType').value = a.assignment_type;
  document.getElementById('oldUser').value = a.user_id;
  // Show current assignment info
  document.getElementById('editInfo').innerHTML =
    '<div class="alert alert-info py-2 small"><strong>JRV ' + a.numero_junta + '</strong> (' + (a.genero||'') + ') - ' + a.name_precinct +
    '<br>Operador actual: <strong>' + a.first_name + ' ' + a.last_name + '</strong></div>';
  new bootstrap.Modal(document.getElementById('editModal')).show();
}

function resetAssignForm() {
  document.querySelector('#assignModal form').reset();
  document.getElementById('selCanton').innerHTML = '<option value="">Primero seleccione provincia</option>';
  document.getElementById('selCanton').disabled = true;
  document.getElementById('selParish').innerHTML = '<option value="">Primero seleccione cantón</option>';
  document.getElementById('selParish').disabled = true;
  document.getElementById('selPrecinct').innerHTML = '<option value="">Primero seleccione parroquia</option>';
  document.getElementById('selPrecinct').disabled = true;
  document.getElementById('jrvListContainer').style.display = 'none';
  document.getElementById('jrvCheckboxList').innerHTML = '';
  updateCount();
}

document.getElementById('selProvince')?.addEventListener('change', async function() {
  const sel = document.getElementById('selCanton');
  sel.innerHTML = '<option value="">Cargando...</option>';
  sel.disabled = true;
  document.getElementById('selParish').innerHTML = '<option value="">Primero seleccione cantón</option>';
  document.getElementById('selParish').disabled = true;
  document.getElementById('selPrecinct').innerHTML = '<option value="">Primero seleccione parroquia</option>';
  document.getElementById('selPrecinct').disabled = true;
  document.getElementById('jrvListContainer').style.display = 'none';
  if (!this.value) { sel.innerHTML = '<option value="">Seleccione provincia primero</option>'; return; }
  const r = await fetch('<?= base_url('ajax/cantons') ?>?province_id=' + this.value);
  const d = await r.json();
  sel.innerHTML = '<option value="">Seleccione cantón...</option>';
  d.forEach(c => { sel.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>'; });
  sel.disabled = false;
});

document.getElementById('selCanton')?.addEventListener('change', async function() {
  const sel = document.getElementById('selParish');
  sel.innerHTML = '<option value="">Cargando...</option>';
  sel.disabled = true;
  document.getElementById('selPrecinct').innerHTML = '<option value="">Primero seleccione parroquia</option>';
  document.getElementById('selPrecinct').disabled = true;
  document.getElementById('jrvListContainer').style.display = 'none';
  if (!this.value) { sel.innerHTML = '<option value="">Seleccione cantón primero</option>'; return; }
  const r = await fetch('<?= base_url('ajax/parishes') ?>?canton_id=' + this.value);
  const d = await r.json();
  sel.innerHTML = '<option value="">Seleccione parroquia...</option>';
  d.forEach(p => { sel.innerHTML += '<option value="' + p.id + '">' + p.name + '</option>'; });
  sel.disabled = false;
});

document.getElementById('selParish')?.addEventListener('change', async function() {
  const sel = document.getElementById('selPrecinct');
  sel.innerHTML = '<option value="">Cargando...</option>';
  sel.disabled = true;
  document.getElementById('jrvListContainer').style.display = 'none';
  if (!this.value) { sel.innerHTML = '<option value="">Seleccione parroquia primero</option>'; return; }
  const r = await fetch('<?= base_url('ajax/precincts') ?>?parish_id=' + this.value);
  const d = await r.json();
  sel.innerHTML = '<option value="">Seleccione recinto...</option>';
  d.forEach(p => { sel.innerHTML += '<option value="' + p.id + '">' + p.name + '</option>'; });
  sel.disabled = false;
});

document.getElementById('selElection')?.addEventListener('change', function() {
  document.getElementById('jrvListContainer').style.display = 'none';
  document.getElementById('jrvCheckboxList').innerHTML = '';
  updateCount();
});

document.getElementById('selPrecinct')?.addEventListener('change', async function() {
  const container = document.getElementById('jrvListContainer');
  const list = document.getElementById('jrvCheckboxList');
  const electionId = document.getElementById('selElection').value;
  if (!this.value || !electionId) {
    if (!electionId) alert('Primero seleccione una elección');
    container.style.display = 'none';
    return;
  }
  list.innerHTML = '<div class="text-center py-3 text-muted">Cargando JRV...</div>';
  container.style.display = 'block';
  const r = await fetch('<?= base_url('ajax/stations-by-precinct') ?>?precinct_id=' + this.value + '&election_id=' + electionId);
  const d = await r.json();
  if (!d.length) { list.innerHTML = '<div class="text-center py-3 text-muted">No hay JRV en este recinto para la elección seleccionada</div>'; updateCount(); return; }
  list.innerHTML = d.map(s => {
    const isAssigned = s.assigned;
    return `
    <div class="form-check form-check-inline ${isAssigned ? 'opacity-50' : ''}">
      <input type="checkbox" name="station_ids[]" value="${s.id}" class="form-check-input jrv-check" id="jrv_${s.id}"
        onchange="updateCount()" ${isAssigned ? 'disabled' : ''}>
      <label class="form-check-label small ${isAssigned ? 'text-decoration-line-through' : ''}" for="jrv_${s.id}">
        <span class="badge" style="background:${s.genero === 'femenina' ? '#e83e8c' : '#0d6efd'}">${s.numero_junta}</span>
        ${s.genero ? s.genero.charAt(0).toUpperCase() + s.genero.slice(1) : ''}
        ${isAssigned ? '<span class="badge bg-success ms-1">✓ Asignada</span>' : ''}
        <small class="text-muted">(${s.numero_electores} electores)</small>
      </label>
    </div>`; }).join('');
  updateCount();
});

function selectAllJRV(select) {
  document.querySelectorAll('.jrv-check:not(:disabled)').forEach(c => c.checked = select);
  updateCount();
}

function updateCount() {
  const count = document.querySelectorAll('.jrv-check:checked').length;
  document.getElementById('selectedCount').textContent = count + ' seleccionadas';
  document.getElementById('assignCount').textContent = count;
}

function escapeRegex(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
function filterTable() {
  var table = $('#assignTable').DataTable();
  table.column(1).search($('#filterOperador').val());
  table.column(4).search($('#filterProvincia').val());
  table.column(5).search($('#filterCanton').val());
  table.column(6).search($('#filterParroquia').val());
  table.column(7).search($('#filterRecinto').val());
  var el = $('#filterElection').val();
  table.column(8).search(el ? '^' + escapeRegex(el) + '$' : '', true, false);
  var st = $('#filterStatus').val();
  table.column(10).search(st ? '^' + escapeRegex(st) + '$' : '', true, false);
  table.draw();
}
function clearFilters() {
  $('#filterOperador, #filterProvincia, #filterCanton, #filterParroquia, #filterRecinto').val('');
  $('#filterElection, #filterStatus').val('');
  filterTable();
}
function del(id) { confirmDelete().then(r => { if(r.isConfirmed) window.location='<?= base_url('assignments/delete') ?>/'+id; }); }
</script>
<?= $this->endSection() ?>
