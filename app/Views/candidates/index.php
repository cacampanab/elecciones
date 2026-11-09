<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Candidatos</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('candidates/template') ?>" class="btn btn-outline-success"><i class="bi bi-download me-1"></i>Plantilla</a>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-file-earmark-excel me-1"></i>Importar Excel</button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i>Nuevo Candidato</button>
  </div>
</div>
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" action="<?= base_url('candidates') ?>" class="row g-2 align-items-center">
      <div class="col-auto"><label class="col-form-label fw-bold"><i class="bi bi-funnel me-1"></i>Filtros</label></div>
      <div class="col-md-5">
        <select name="election_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Todas las elecciones</option>
          <?php foreach ($elections as $e): ?>
          <option value="<?= $e->id ?>" <?= (int)$filterElection === (int)$e->id ? 'selected' : '' ?>><?= esc($e->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <select name="dignity_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Todas las dignidades</option>
          <?php foreach ($dignities as $d): ?>
          <option value="<?= $d->id ?>" <?= (int)$filterDignity === (int)$d->id ? 'selected' : '' ?>><?= esc($d->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto"><span class="badge bg-primary fs-6"><?= count($candidates) ?> candidatos</span></div>
      <?php if ($filterElection || $filterDignity): ?>
      <div class="col-auto"><a href="<?= base_url('candidates') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Limpiar</a></div>
      <?php endif; ?>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0 data-table">
      <thead class="table-light"><tr><th>#</th><th>Nombres</th><th>Cédula</th><th>Partido</th><th>Dignidad</th><th>Cantón</th><th>Elección</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php if (empty($candidates)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No hay candidatos para los filtros seleccionados.</td></tr>
        <?php endif; ?>
        <?php $i=1; foreach ($candidates as $c): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><strong><?= esc($c->first_name . ' ' . $c->last_name) ?></strong></td>
          <td><?= esc($c->cedula) ?></td>
          <td><span class="badge bg-dark"><?= esc($c->party_acronym) ?></span> <?= esc($c->party_name) ?></td>
          <td><?= esc($c->dignity_name) ?></td>
          <td><?= $c->canton_name ? esc($c->canton_name) : '<span class="text-muted">—</span>' ?></td>
          <td><span class="small text-muted"><?= esc($c->election_name) ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick='edit(<?= json_encode($c) ?>)'><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="del(<?= $c->id ?>)"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="modal" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= base_url('candidates/save') ?>" class="modal-content"><?= csrf_field() ?><input type="hidden" name="id" id="fid">
  <div class="modal-header"><h5 class="modal-title" id="mt">Nuevo Candidato</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Nombres *</label><input type="text" name="first_name" id="f_fn" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Apellidos *</label><input type="text" name="last_name" id="f_ln" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Cédula</label><input type="text" name="cedula" id="f_ced" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Elección *</label><select name="election_id" id="f_eid" class="form-select" required><option value="">Seleccione</option><?php foreach ($elections as $e): ?><option value="<?= $e->id ?>"><?= esc($e->name) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-4"><label class="form-label">Dignidad *</label><select name="dignity_id" id="f_did" class="form-select" required><option value="">Seleccione</option><?php foreach ($dignities as $d): ?><option value="<?= $d->id ?>" data-slug="<?= esc($d->slug, 'attr') ?>"><?= esc($d->name) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><label class="form-label">Partido *</label><select name="political_party_id" id="f_pid" class="form-select" required><option value="">Seleccione</option><?php foreach ($parties as $p): ?><option value="<?= $p->id ?>"><?= esc($p->name) ?> (<?= esc($p->acronym) ?>)</option><?php endforeach; ?></select></div>
      <div class="col-12" id="cantonFields" style="display:none">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Provincia *</label>
            <select id="f_province" class="form-select">
              <option value="">Seleccione provincia...</option>
              <?php foreach ($provinces as $province): ?>
                <option value="<?= (int)$province->id ?>"><?= esc($province->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Cantón *</label>
            <select name="canton_id" id="f_canton" class="form-select" disabled>
              <option value="">Primero seleccione provincia</option>
            </select>
          </div>
        </div>
        <div class="form-text">Obligatorio para Alcalde. El cantón define el ámbito del candidato.</div>
      </div>
    </div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
</form></div></div>

<div class="modal fade" id="importModal" tabindex="-1"><div class="modal-dialog modal-lg"><form method="post" action="<?= base_url('candidates/import') ?>" enctype="multipart/form-data" class="modal-content"><?= csrf_field() ?>
  <div class="modal-header"><h5 class="modal-title"><i class="bi bi-file-earmark-excel me-1"></i>Importar Candidatos desde Excel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Elección *</label><select name="election_id" class="form-select" required><option value="">Seleccione una elección...</option><?php foreach ($elections as $e): ?><option value="<?= $e->id ?>"><?= esc($e->name) ?></option><?php endforeach; ?></select></div>
    <div class="mb-3"><label class="form-label">Archivo Excel *</label><input type="file" name="file" class="form-control" accept=".xlsx,.xls" required></div>
    <div class="alert alert-info small mb-2">
      <strong>Formato esperado (7 columnas, la fila 1 son encabezados):</strong>
      <div class="table-responsive mt-2">
        <table class="table table-sm table-bordered bg-white mb-0">
          <thead class="table-light"><tr><th>Dignidad</th><th>Partido</th><th>Nombres</th><th>Apellidos</th><th>Cédula</th><th>Provincia</th><th>Cantón</th></tr></thead>
          <tbody><tr><td>Alcalde</td><td>MPD</td><td>PEDRO</td><td>MARTINEZ</td><td></td><td>08</td><td>560</td></tr></tbody>
        </table>
      </div>
      <ul class="mb-0 mt-2 ps-3">
        <li><strong>Dignidad:</strong> nombre o slug (Presidente, Asambleísta Nacional, Prefecto, Alcalde, Concejal Urbano, Concejal Rural, Vocal Junta Parroquial).</li>
        <li><strong>Partido:</strong> sigla o nombre del partido (ej. ADN). Si no existe, se crea automáticamente.</li>
        <li><strong>Nombres</strong> y <strong>Apellidos:</strong> obligatorios.</li>
        <li><strong>Cédula:</strong> opcional; no puede repetirse.</li>
        <li><strong>Provincia y Cantón:</strong> obligatorios para Alcalde; pueden ser código o nombre.</li>
      </ul>
    </div>
    <a href="<?= base_url('candidates/template') ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i>Descargar plantilla de ejemplo</a>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i>Importar</button></div>
</form></div></div>

<script>
const candidateBaseUrl = '<?= base_url('') ?>';

function selectedDignitySlug() {
  const select = document.getElementById('f_did');
  const option = select.options[select.selectedIndex];
  return option ? (option.dataset.slug || '') : '';
}

function toggleCantonFields() {
  const isMayor = selectedDignitySlug() === 'alcalde';
  document.getElementById('cantonFields').style.display = isMayor ? 'block' : 'none';
  document.getElementById('f_province').required = isMayor;
  document.getElementById('f_canton').required = isMayor;
  if (!isMayor) {
    document.getElementById('f_province').value = '';
    document.getElementById('f_canton').innerHTML = '<option value="">Primero seleccione provincia</option>';
    document.getElementById('f_canton').disabled = true;
  }
}

async function loadCandidateCantons(selectedId = '') {
  const provinceId = document.getElementById('f_province').value;
  const cantonSelect = document.getElementById('f_canton');
  if (!provinceId) {
    cantonSelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
    cantonSelect.disabled = true;
    return;
  }
  cantonSelect.innerHTML = '<option value="">Cargando...</option>';
  cantonSelect.disabled = true;
  try {
    const response = await fetch(candidateBaseUrl + 'ajax/cantons?province_id=' + encodeURIComponent(provinceId));
    const data = await response.json();
    cantonSelect.innerHTML = '<option value="">Seleccione cantón...</option>';
    data.forEach(canton => {
      cantonSelect.innerHTML += '<option value="' + canton.id + '">' + canton.name + '</option>';
    });
    if (selectedId) cantonSelect.value = selectedId;
    cantonSelect.disabled = false;
  } catch (e) {
    cantonSelect.innerHTML = '<option value="">Error al cargar cantones</option>';
  }
}

function resetForm() {
  document.getElementById('mt').textContent = 'Nuevo Candidato';
  document.getElementById('fid').value = '';
  document.querySelector('#modal form').reset();
  document.getElementById('cantonFields').style.display = 'none';
  document.getElementById('f_province').required = false;
  document.getElementById('f_canton').required = false;
  document.getElementById('f_canton').innerHTML = '<option value="">Primero seleccione provincia</option>';
  document.getElementById('f_canton').disabled = true;
}

async function edit(c) {
  document.getElementById('mt').textContent = 'Editar Candidato';
  document.getElementById('fid').value = c.id;
  document.getElementById('f_fn').value = c.first_name;
  document.getElementById('f_ln').value = c.last_name;
  document.getElementById('f_ced').value = c.cedula;
  document.getElementById('f_eid').value = c.election_id;
  document.getElementById('f_did').value = c.dignity_id;
  document.getElementById('f_pid').value = c.political_party_id;

  const isMayor = c.dignity_slug === 'alcalde';
  document.getElementById('cantonFields').style.display = isMayor ? 'block' : 'none';
  document.getElementById('f_province').required = isMayor;
  document.getElementById('f_canton').required = isMayor;
  if (isMayor) {
    document.getElementById('f_province').value = c.province_id || '';
    await loadCandidateCantons(c.canton_id || '');
  }

  new bootstrap.Modal(document.getElementById('modal')).show();
}

function del(id) { confirmDelete().then(r => { if(r.isConfirmed) window.location = '<?= base_url('candidates/delete') ?>/'+id; }); }

document.getElementById('f_did')?.addEventListener('change', toggleCantonFields);
document.getElementById('f_province')?.addEventListener('change', function() { loadCandidateCantons(); });
</script>
<?= $this->endSection() ?>
