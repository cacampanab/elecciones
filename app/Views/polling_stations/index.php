<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Juntas Receptoras del Voto</h1></div>
  <div class="d-flex gap-2">
    <?php if (session()->get('role') === 'Administrador General'): ?>
    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal" <?= $selectedElection ? '' : 'disabled' ?>><i class="bi bi-trash me-1"></i>Borrar JRV</button>
    <?php endif; ?>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal" <?= $selectedElection ? '' : 'disabled' ?>><i class="bi bi-file-earmark-excel me-1"></i>Importar Excel</button>
  </div>
</div>
<div class="card">
  <div class="card-body pb-0">
    <form method="get" action="<?= base_url('polling-stations') ?>" class="row g-2 align-items-center mb-3 pb-2 border-bottom">
      <div class="col-auto"><label class="col-form-label fw-bold"><i class="bi bi-vote-yes me-1"></i>Elección</label></div>
      <div class="col-md-6 col-lg-5">
        <select name="election_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php if (empty($elections)): ?><option value="">No hay elecciones</option><?php endif; ?>
          <?php foreach ($elections as $e): ?>
          <option value="<?= $e->id ?>" <?= (int)$selectedElection === (int)$e->id ? 'selected' : '' ?>><?= esc($e->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto"><span class="badge bg-primary fs-6"><?= count($stations) ?> JRV</span></div>
    </form>
    <div class="row g-1 mb-2">
      <div class="col-md-3"><input type="text" id="fProvincia" class="form-control form-control-sm" placeholder="Provincia" onkeyup="filtroRapido()"></div>
      <div class="col-md-3"><input type="text" id="fCanton" class="form-control form-control-sm" placeholder="Cantón" onkeyup="filtroRapido()"></div>
      <div class="col-md-3"><input type="text" id="fParroquia" class="form-control form-control-sm" placeholder="Parroquia" onkeyup="filtroRapido()"></div>
      <div class="col-md-3"><input type="text" id="fRecinto" class="form-control form-control-sm" placeholder="Recinto" onkeyup="filtroRapido()"></div>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" id="psTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Provincia</th>
            <th>Cantón</th>
            <th>Parroquia</th>
            <th>Recinto</th>
            <th>JRV</th>
            <th>Género</th>
            <th>Electores</th>
            <th>Votos</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($stations)): ?>
          <tr><td colspan="11" class="text-center text-muted py-4">No hay JRV para la elección seleccionada. Use "Importar Excel" para cargarlas.</td></tr>
          <?php endif; ?>
          <?php $i=1; foreach ($stations as $s): ?>
          <tr class="<?= $s->has_results ? 'table-success' : '' ?>">
            <td><?= $i++ ?></td>
            <td><?= esc($s->name_province) ?></td>
            <td><?= esc($s->name_canton) ?></td>
            <td><?= esc($s->name_parish) ?></td>
            <td><strong><?= esc($s->name_precinct) ?></strong></td>
            <td><strong><?= esc($s->numero_junta) ?></strong></td>
            <td>
              <span class="badge" style="background:<?= $s->genero === 'femenina' ? '#e83e8c' : '#0d6efd' ?>">
                <i class="bi bi-<?= $s->genero === 'femenina' ? 'gender-female' : 'gender-male' ?> me-1"></i><?= ucfirst($s->genero) ?>
              </span>
            </td>
            <td><?= $s->numero_electores ?></td>
            <td>
              <?php if ($s->has_results): ?>
              <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i><?= number_format($s->total_votes) ?> votos</span>
              <?php else: ?>
              <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge bg-<?= $s->status === 'pendiente' ? 'danger' : ($s->status === 'asignada' ? 'warning' : ($s->status === 'en_proceso' ? 'info' : ($s->status === 'reportada' ? 'success' : ($s->status === 'revisada' ? 'primary' : 'secondary')))) ?>">
                <?= ucfirst(str_replace('_', ' ', $s->status)) ?>
              </span>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-primary" onclick="editStation(<?= htmlspecialchars(json_encode($s)) ?>)"><i class="bi bi-pencil"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= base_url('polling-stations/update') ?>" class="modal-content"><?= csrf_field() ?><input type="hidden" name="id" id="editId">
  <div class="modal-header"><h5 class="modal-title">Editar JRV</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Provincia</label><input type="text" id="editProvince" class="form-control" readonly></div>
      <div class="col-md-6"><label class="form-label">Cantón</label><input type="text" id="editCanton" class="form-control" readonly></div>
      <div class="col-md-6"><label class="form-label">Parroquia</label><input type="text" id="editParish" class="form-control" readonly></div>
      <div class="col-md-6"><label class="form-label">Recinto</label><input type="text" id="editPrecinct" class="form-control" readonly></div>
      <div class="col-md-4"><label class="form-label">JRV</label><input type="text" id="editJunta" class="form-control" readonly></div>
      <div class="col-md-4"><label class="form-label">Género</label><input type="text" id="editGenero" class="form-control" readonly></div>
      <div class="col-md-4"><label class="form-label">N° Electores</label><input type="number" name="numero_electores" id="editElectores" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Estado</label><select name="status" id="editStatus" class="form-select"><option value="pendiente">Pendiente</option><option value="asignada">Asignada</option><option value="en_proceso">En Proceso</option><option value="reportada">Reportada</option><option value="revisada">Revisada</option><option value="aprobada">Aprobada</option></select></div>
    </div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar Cambios</button></div>
</form></div></div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1"><div class="modal-dialog modal-lg"><form method="post" action="<?= base_url('polling-stations/import') ?>" enctype="multipart/form-data" class="modal-content" id="importForm"><?= csrf_field() ?>
  <div class="modal-header"><h5 class="modal-title">Importar JRV desde Excel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Elección</label>
      <input type="hidden" name="election_id" value="<?= (int)$selectedElection ?>">
      <input type="text" class="form-control" value="<?= esc($selectedElectionName) ?>" readonly>
      <div class="form-text">Se importará a la elección seleccionada arriba.</div>
    </div>
    <div class="mb-3"><label class="form-label">Archivo Excel *</label><input type="file" name="file" class="form-control" accept=".xlsx,.xls" required></div>
    <?php if (session()->get('role') === 'Administrador General'): ?>
    <div class="form-check form-switch mb-3">
      <input class="form-check-input" type="checkbox" name="replace" value="1" id="impReplace">
      <label class="form-check-label text-danger" for="impReplace"><strong>Eliminar todas las JRV de esta elección antes de importar</strong></label>
      <div class="form-text text-danger">Borra las JRV actuales de la elección seleccionada junto con sus asignaciones, resultados y actas. Úselo para reemplazar los datos de ejemplo.</div>
    </div>
    <?php endif; ?>
    <div class="alert alert-info small mb-0">
      <strong>Formato esperado (14 columnas):</strong><br>
      <code>código_provincia | nombre_provincia | código_cantón | nombre_cantón | código_parroquia | nombre_parroquia | código_recinto | nombre_recinto | junta_femenina_inicio | junta_femenina_fin | junta_masculina_inicio | junta_masculina_fin | número_junta (general) | número_electores</code>
    </div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i>Importar</button></div>
</form></div></div>

<!-- Delete All Modal -->
<?php if (session()->get('role') === 'Administrador General'): ?>
<div class="modal fade" id="deleteAllModal" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= base_url('polling-stations/delete-all') ?>" class="modal-content" id="deleteAllForm"><?= csrf_field() ?>
  <div class="modal-header"><h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Borrar JRV de una elección</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="alert alert-danger small">
      <strong>Atención:</strong> se eliminarán <strong>todas las JRV</strong> de la elección seleccionada, junto con sus asignaciones, resultados registrados y actas subidas. Esta acción <strong>no se puede deshacer</strong>.
    </div>
    <label class="form-label">Elección</label>
    <input type="hidden" name="election_id" value="<?= (int)$selectedElection ?>">
    <input type="text" class="form-control" value="<?= esc($selectedElectionName) ?>" readonly>
    <div class="form-text">Solo se eliminarán las JRV de esta elección.</div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Eliminar JRV</button></div>
</form></div></div>
<?php endif; ?>

<script>
function filtroRapido() {
  var p = $('#fProvincia').val().toLowerCase(), c = $('#fCanton').val().toLowerCase();
  var pa = $('#fParroquia').val().toLowerCase(), r = $('#fRecinto').val().toLowerCase();
  $('#psTable tbody tr').each(function() {
    var show = true;
    if (p && $(this).find('td:eq(1)').text().toLowerCase().indexOf(p) < 0) show = false;
    if (c && $(this).find('td:eq(2)').text().toLowerCase().indexOf(c) < 0) show = false;
    if (pa && $(this).find('td:eq(3)').text().toLowerCase().indexOf(pa) < 0) show = false;
    if (r && $(this).find('td:eq(4)').text().toLowerCase().indexOf(r) < 0) show = false;
    $(this).toggle(show);
  });
}
function editStation(s) {
  document.getElementById('editId').value = s.id;
  document.getElementById('editProvince').value = s.name_province;
  document.getElementById('editCanton').value = s.name_canton;
  document.getElementById('editParish').value = s.name_parish;
  document.getElementById('editPrecinct').value = s.name_precinct;
  document.getElementById('editJunta').value = s.numero_junta;
  document.getElementById('editGenero').value = s.genero ? s.genero.charAt(0).toUpperCase() + s.genero.slice(1) : '';
  document.getElementById('editElectores').value = s.numero_electores;
  document.getElementById('editStatus').value = s.status;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}

document.getElementById('importForm')?.addEventListener('submit', function(e) {
  if (document.getElementById('impReplace')?.checked) {
    e.preventDefault();
    confirmDelete('Se eliminarán TODAS las JRV de la elección seleccionada y luego se importará el archivo. ¿Continuar?')
      .then(r => { if (r.isConfirmed) this.submit(); });
  }
});

document.getElementById('deleteAllForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  confirmDelete('¿Eliminar TODAS las JRV de la elección seleccionada? Esta acción no se puede deshacer.')
    .then(r => { if (r.isConfirmed) this.submit(); });
});
</script>
<?= $this->endSection() ?>
