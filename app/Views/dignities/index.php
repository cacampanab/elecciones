<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Dignidades</h1></div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i>Nueva Dignidad</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0 data-table">
      <thead class="table-light"><tr><th>#</th><th>Nombre</th><th>Slug</th><th>Nivel</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php $i=1; foreach ($dignities as $d): ?>
        <tr>
          <td><?= $i++ ?></td><td><strong><?= esc($d->name) ?></strong></td>
          <td><code><?= esc($d->slug) ?></code></td>
          <td><span class="badge bg-info"><?= ucfirst($d->level) ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick='edit(<?= json_encode($d) ?>)'><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="del(<?= $d->id ?>)"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="modal" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= base_url('dignities/save') ?>" class="modal-content"><?= csrf_field() ?><input type="hidden" name="id" id="fid">
  <div class="modal-header"><h5 class="modal-title" id="mt">Nueva Dignidad</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Nombre *</label><input type="text" name="name" id="f_name" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Slug *</label><input type="text" name="slug" id="f_slug" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Descripción</label><input type="text" name="description" id="f_desc" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Nivel</label><select name="level" id="f_level" class="form-select"><option value="nacional">Nacional</option><option value="provincial">Provincial</option><option value="cantonal">Cantonal</option><option value="parroquial">Parroquial</option></select></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
</form></div></div>
<script>
function resetForm() { document.getElementById('mt').textContent = 'Nueva Dignidad'; document.getElementById('fid').value = ''; document.querySelector('#modal form').reset(); }
function edit(d) { document.getElementById('mt').textContent = 'Editar Dignidad'; document.getElementById('fid').value = d.id; document.getElementById('f_name').value = d.name; document.getElementById('f_slug').value = d.slug; document.getElementById('f_desc').value = d.description||''; document.getElementById('f_level').value = d.level; new bootstrap.Modal(document.getElementById('modal')).show(); }
function del(id) { confirmDelete().then(r => { if(r.isConfirmed) window.location = '<?= base_url('dignities/delete') ?>/'+id; }); }
</script>
<?= $this->endSection() ?>
