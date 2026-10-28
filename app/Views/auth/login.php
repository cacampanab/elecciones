<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Control Electoral</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
    .login-card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
    .login-card .brand { text-align: center; margin-bottom: 30px; }
    .login-card .brand i { font-size: 3rem; color: #0d6efd; }
    .login-card .brand h2 { font-size: 1.4rem; font-weight: 700; margin-top: 10px; color: #1a1a2e; }
    .login-card .brand p { font-size: .85rem; color: #6c757d; margin: 0; }
    .login-card .form-control { border-radius: 8px; padding: 10px 15px; }
    .login-card .btn-primary { border-radius: 8px; padding: 10px; font-weight: 600; }
    .login-card .footer-text { text-align: center; margin-top: 20px; font-size: .8rem; color: #6c757d; }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="brand">
      <i class="bi bi-ballot-check-fill"></i>
      <h2>Sistema de Control Electoral</h2>
      <p>Ingrese sus credenciales para acceder</p>
    </div>
    <?php if (session()->has('error')): ?>
      <div class="alert alert-danger py-2 small"><?= esc(session('error')) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('auth/login') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label small fw-medium">Usuario</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" name="username" class="form-control" placeholder="Nombre de usuario" required autofocus>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label small fw-medium">Contraseña</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>
    <div class="footer-text">© <?= date('Y') ?> - Consejo Nacional Electoral</div>
  </div>
</body>
</html>
