<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Elecciones</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Elecciones</li></ol></nav></div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#electionModal" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i>Nueva Elección</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0 data-table">
      <thead class="table-light"><tr><th>#</th><th>Nombre</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php $i=1; foreach ($elections as $e): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><strong><?= esc($e->name) ?></strong></td>
          <td><?= date('d/m/Y', strtotime($e->election_date)) ?></td>
          <td><span class="badge bg-secondary"><?= ucfirst($e->election_type) ?></span></td>
          <td>
            <span class="badge bg-<?= $e->status === 'pendiente' ? 'danger' : ($e->status === 'en_curso' ? 'warning' : ($e->status === 'finalizada' ? 'success' : 'secondary')) ?>">
              <?= str_replace('_', ' ', ucfirst($e->status)) ?>
            </span>
          </td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick="edit(<?= htmlspecialchars(json_encode($e)) ?>)"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="del(<?= $e->id ?>)"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="electionModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= base_url('elections/save') ?>" class="modal-content">
      <?= csrf_field() ?><input type="hidden" name="id" id="eid">
      <div class="modal-header"><h5 class="modal-title" id="modalTitle">Nueva Elección</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Nombre *</label><input type="text" name="name" id="f_name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Descripción</label><textarea name="description" id="f_description" class="form-control" rows="2"></textarea></div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Fecha *</label><input type="date" name="election_date" id="f_date" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Tipo *</label><select name="election_type" id="f_type" class="form-select"><option value="nacional">Nacional</option><option value="provincial">Provincial</option><option value="cantonal">Cantonal</option><option value="parroquial">Parroquial</option></select></div>
          <div class="col-md-6"><label class="form-label">Estado</label><select name="status" id="f_status" class="form-select"><option value="pendiente">Pendiente</option><option value="en_curso">En Curso</option><option value="finalizada">Finalizada</option><option value="anulada">Anulada</option></select></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
    </form>
  </div>
</div>
<script>
function resetForm() { document.getElementById('modalTitle').textContent = 'Nueva Elección'; document.getElementById('eid').value = ''; document.querySelector('#electionModal form').reset(); }
function edit(e) {
  document.getElementById('modalTitle').textContent = 'Editar Elección';
  document.getElementById('eid').value = e.id; document.getElementById('f_name').value = e.name;
  document.getElementById('f_description').value = e.description || ''; document.getElementById('f_date').value = e.election_date;
  document.getElementById('f_type').value = e.election_type; document.getElementById('f_status').value = e.status;
  new bootstrap.Modal(document.getElementById('electionModal')).show();
}
function del(id) { confirmDelete('¿Eliminar esta elección?').then(r => { if (r.isConfirmed) window.location = '<?= base_url('elections/delete') ?>/' + id; }); }
</script>
<?= $this->endSection() ?>
