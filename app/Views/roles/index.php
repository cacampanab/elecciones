<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Roles y Permisos</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Roles</li></ol></nav></div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal" onclick="resetRoleForm()"><i class="bi bi-plus-lg me-1"></i>Nuevo Rol</button>
</div>
<div class="row g-3">
  <?php foreach ($roles as $r): ?>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-shield me-2"></i><?= esc($r->name) ?></span>
        <div>
          <button class="btn btn-sm btn-outline-primary" onclick="editRole(<?= $r->id ?>)"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteRole(<?= $r->id ?>)"><i class="bi bi-trash"></i></button>
        </div>
      </div>
      <div class="card-body">
        <p class="text-muted small"><?= esc($r->description) ?></p>
        <div class="d-flex flex-wrap gap-1">
          <?php
          $rolePerms = array_filter($permissions, fn($p) => $p->role_id == $r->id);
          foreach (array_slice($rolePerms, 0, 5) as $rp): ?>
          <span class="badge bg-light text-dark border"><?= esc($rp->description ?? $rp->name) ?></span>
          <?php endforeach; ?>
          <?php if (count($rolePerms) > 5): ?>
          <span class="badge bg-secondary">+<?= count($rolePerms)-5 ?> más</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="modal fade" id="roleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= base_url('roles/save') ?>" class="modal-content">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="roleId">
      <div class="modal-header"><h5 class="modal-title" id="roleModalTitle">Nuevo Rol</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6"><label class="form-label">Nombre *</label><input type="text" name="name" id="f_name" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Descripción</label><input type="text" name="description" id="f_description" class="form-control"></div>
        </div>
        <label class="form-label fw-bold">Permisos</label>
        <div class="row g-2" style="max-height:400px;overflow-y:auto">
          <?php $group = ''; foreach ($allPermissions as $p):
            $prefix = explode('.', $p->name)[0];
            if ($prefix !== $group): $group = $prefix; ?>
            <div class="col-12 mt-2"><strong class="text-primary small text-uppercase"><?= esc($group) ?></strong></div>
          <?php endif; ?>
          <div class="col-md-4 col-6">
            <div class="form-check">
              <input type="checkbox" name="permissions[]" value="<?= $p->id ?>" class="form-check-input perm-check" id="perm_<?= $p->id ?>">
              <label class="form-check-label small" for="perm_<?= $p->id ?>"><?= esc($p->description ?? $p->name) ?></label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
function resetRoleForm() {
  document.getElementById('roleModalTitle').textContent = 'Nuevo Rol';
  document.getElementById('roleId').value = '';
  document.querySelector('#roleModal form').reset();
  document.querySelectorAll('.perm-check').forEach(c => c.checked = false);
}
function editRole(id) {
  document.getElementById('roleModalTitle').textContent = 'Editar Rol';
  document.getElementById('roleId').value = id;
  fetch('<?= base_url('api/roles') ?>/' + id).then(r => r.json()).then(d => {
    const data = d.data;
    document.getElementById('f_name').value = data.name;
    document.getElementById('f_description').value = data.description || '';
    document.querySelectorAll('.perm-check').forEach(c => c.checked = false);
    (data.permissions || []).forEach(p => { const el = document.getElementById('perm_' + p.id); if (el) el.checked = true; });
    new bootstrap.Modal(document.getElementById('roleModal')).show();
  });
}
function deleteRole(id) {
  confirmDelete('¿Eliminar este rol?').then(r => { if (r.isConfirmed) window.location = '<?= base_url('roles/delete') ?>/' + id; });
}
</script>
<?= $this->endSection() ?>
