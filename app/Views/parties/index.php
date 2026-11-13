<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Partidos Políticos</h1></div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i>Nuevo Partido</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0 data-table">
      <thead class="table-light"><tr><th>#</th><th>Logo</th><th>Nombre</th><th>Siglas</th><th>Lista</th><th>Color</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php $i=1; foreach ($parties as $p): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td>
            <?php if (!empty($p->logo)): ?>
              <img src="<?= base_url($p->logo) ?>" alt="<?= esc($p->acronym, 'attr') ?>" style="height:36px;width:36px;object-fit:contain" class="rounded border bg-white p-1">
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
          <td><strong><?= esc($p->name) ?></strong></td>
          <td><span class="badge bg-dark"><?= esc($p->acronym) ?></span></td>
          <td><strong><?= $p->list_number ?></strong></td>
          <td><span class="badge" style="background:<?= esc($p->color ?: '#6c757d') ?>;color:#fff"><?= esc($p->color ?: '—') ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick="edit(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="del(<?= $p->id ?>)"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="modal" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= base_url('parties/save') ?>" enctype="multipart/form-data" class="modal-content"><?= csrf_field() ?><input type="hidden" name="id" id="fid">
  <div class="modal-header"><h5 class="modal-title" id="mt">Nuevo Partido</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Nombre *</label><input type="text" name="name" id="f_name" class="form-control" required></div>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Siglas *</label><input type="text" name="acronym" id="f_acronym" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">N° Lista *</label><input type="number" name="list_number" id="f_list" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Color</label><input type="color" name="color" id="f_color" class="form-control form-control-color"></div>
    </div>
    <div class="mt-3">
      <label class="form-label">Logo del partido</label>
      <div class="d-flex align-items-center gap-2 mb-2">
        <img id="f_logo_preview" src="" alt="" class="d-none rounded border bg-white p-1" style="height:56px;width:56px;object-fit:contain">
        <span id="f_logo_empty" class="text-muted small">Sin logo</span>
      </div>
      <input type="file" name="logo_file" id="f_logo_file" class="form-control" accept="image/png,image/jpeg,image/webp">
      <small class="text-muted">PNG, JPG o WEBP. Máximo 2 MB. Se usará en la pantalla de registro de votos.</small>
      <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="remove_logo" id="f_remove_logo" value="1">
        <label class="form-check-label small" for="f_remove_logo">Quitar logo actual</label>
      </div>
    </div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
</form></div></div>
<script>
const partyBase = '<?= base_url('') ?>';

function setLogoPreview(url) {
  const img = document.getElementById('f_logo_preview');
  const empty = document.getElementById('f_logo_empty');
  if (!img) return;
  if (url) {
    img.src = url;
    img.classList.remove('d-none');
    if (empty) empty.classList.add('d-none');
  } else {
    img.removeAttribute('src');
    img.classList.add('d-none');
    if (empty) empty.classList.remove('d-none');
  }
}

function resetForm() {
  document.getElementById('mt').textContent = 'Nuevo Partido';
  document.getElementById('fid').value = '';
  document.querySelector('#modal form').reset();
  setLogoPreview(null);
}

function edit(d) {
  document.getElementById('mt').textContent = 'Editar Partido';
  document.getElementById('fid').value = d.id;
  document.getElementById('f_name').value = d.name;
  document.getElementById('f_acronym').value = d.acronym;
  document.getElementById('f_list').value = d.list_number;
  document.getElementById('f_color').value = d.color || '#000000';
  document.getElementById('f_logo_file').value = '';
  document.getElementById('f_remove_logo').checked = false;
  setLogoPreview(d.logo ? partyBase + d.logo : null);
  new bootstrap.Modal(document.getElementById('modal')).show();
}

document.getElementById('f_logo_file')?.addEventListener('change', function() {
  const file = this.files && this.files[0];
  document.getElementById('f_remove_logo').checked = false;
  setLogoPreview(file ? URL.createObjectURL(file) : null);
});

function del(id) { confirmDelete().then(r => { if(r.isConfirmed) window.location = '<?= base_url('parties/delete') ?>/'+id; }); }
</script>
<?= $this->endSection() ?>
