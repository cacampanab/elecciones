<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex justify-content-between align-items-center">
  <div><h1>Usuarios</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Usuarios</li></ol></nav></div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i>Nuevo Usuario</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 data-table">
        <thead class="table-light"><tr><th>#</th><th>Usuario</th><th>Nombres</th><th>Email</th><th>Rol</th><th>Cédula</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php $i=1; foreach ($users as $u): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><strong><?= esc($u->username) ?></strong></td>
            <td><?= esc($u->first_name . ' ' . $u->last_name) ?></td>
            <td><?= esc($u->email) ?></td>
            <td><span class="badge bg-info"><?= esc($u->role_name) ?></span></td>
            <td><?= esc($u->cedula) ?></td>
            <td><span class="badge bg-<?= $u->is_active ? 'success' : 'danger' ?>"><?= $u->is_active ? 'Activo' : 'Inactivo' ?></span></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= $u->id ?>)"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= base_url('users/save') ?>" class="modal-content">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="userId">
      <div class="modal-header"><h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Usuario *</label><input type="text" name="username" id="f_username" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" id="f_email" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Nombres *</label><input type="text" name="first_name" id="f_first_name" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Apellidos *</label><input type="text" name="last_name" id="f_last_name" class="form-control" required></div>
          <div class="col-md-4"><label class="form-label">Cédula</label><input type="text" name="cedula" id="f_cedula" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Teléfono</label><input type="text" name="phone" id="f_phone" class="form-control"></div>
          <div class="col-md-4">
            <label class="form-label">Rol *</label>
            <select name="role_id" id="f_role_id" class="form-select" required>
              <option value="">Seleccione...</option>
              <?php foreach ($roles as $r): ?>
              <option value="<?= $r->id ?>"><?= esc($r->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6"><label class="form-label">Contraseña <small id="passLabel">*</small></label><input type="password" name="password" id="f_password" class="form-control"></div>
          <div class="col-md-6">
            <label class="form-label">Estado</label>
            <select name="is_active" id="f_is_active" class="form-select">
              <option value="1">Activo</option><option value="0">Inactivo</option>
            </select>
          </div>
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
function resetForm() {
  document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
  document.getElementById('userId').value = '';
  document.getElementById('passLabel').textContent = '*';
  document.getElementById('f_password').required = true;
  document.querySelector('#userModal form').reset();
}
function editUser(u) {
  document.getElementById('modalTitle').textContent = 'Editar Usuario';
  document.getElementById('userId').value = u.id;
  document.getElementById('f_username').value = u.username;
  document.getElementById('f_email').value = u.email;
  document.getElementById('f_first_name').value = u.first_name;
  document.getElementById('f_last_name').value = u.last_name;
  document.getElementById('f_cedula').value = u.cedula || '';
  document.getElementById('f_phone').value = u.phone || '';
  document.getElementById('f_role_id').value = u.role_id;
  document.getElementById('f_is_active').value = u.is_active;
  document.getElementById('f_password').required = false;
  document.getElementById('passLabel').textContent = '(dejar vacío para no cambiar)';
  new bootstrap.Modal(document.getElementById('userModal')).show();
}
function deleteUser(id) {
  confirmDelete('¿Eliminar este usuario?').then(r => { if (r.isConfirmed) window.location = '<?= base_url('users/delete') ?>/' + id; });
}
</script>
<?= $this->endSection() ?>
