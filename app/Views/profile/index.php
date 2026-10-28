<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header"><h1>Mi Perfil</h1></div>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-person me-2"></i>Información Personal</div>
      <div class="card-body">
        <form method="post" action="<?= base_url('profile/update') ?>">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Nombres</label><input type="text" name="first_name" class="form-control" value="<?= esc(session()->get('first_name')) ?>" required></div>
          <div class="mb-3"><label class="form-label">Apellidos</label><input type="text" name="last_name" class="form-control" value="<?= esc(session()->get('last_name')) ?>" required></div>
          <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" value="<?= esc(session()->get('email')) ?>" disabled></div>
          <div class="mb-3"><label class="form-label">Teléfono</label><input type="text" name="phone" class="form-control" value="<?= esc(session()->get('phone') ?? '') ?>"></div>
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Actualizar Perfil</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-lock me-2"></i>Cambiar Contraseña</div>
      <div class="card-body">
        <form method="post" action="<?= base_url('profile/password') ?>">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Contraseña Actual</label><input type="password" name="current_password" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Nueva Contraseña</label><input type="password" name="new_password" class="form-control" required minlength="8"></div>
          <button class="btn btn-warning"><i class="bi bi-key me-1"></i>Cambiar Contraseña</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
