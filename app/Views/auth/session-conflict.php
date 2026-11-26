<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sesión activa - Sistema de Control Electoral</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; padding: 20px; }
    .login-card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
    .login-card .brand { text-align: center; margin-bottom: 24px; }
    .login-card .brand i { font-size: 3rem; color: #0d6efd; }
    .login-card .brand h2 { font-size: 1.35rem; font-weight: 700; margin-top: 10px; color: #1a1a2e; }
    .login-card .brand p { font-size: .85rem; color: #6c757d; margin: 0; }
    .login-card .btn { border-radius: 8px; padding: 10px; font-weight: 600; }
    .login-card .footer-text { text-align: center; margin-top: 20px; font-size: .8rem; color: #6c757d; }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="brand">
      <i class="bi bi-shield-exclamation"></i>
      <h2>Ya existe una sesión activa</h2>
      <p>El usuario <strong><?= esc($user->username) ?></strong> ya tiene una sesión abierta.</p>
    </div>

    <div class="alert alert-warning small">
      Si continúa, la sesión anterior se cerrará y en ese dispositivo se pedirá volver a iniciar sesión.
    </div>

    <ul class="list-group list-group-flush small mb-4">
      <li class="list-group-item d-flex justify-content-between">
        <span class="text-muted">Dispositivo</span>
        <span class="text-end"><?= esc($device ?: 'No identificado') ?></span>
      </li>
      <li class="list-group-item d-flex justify-content-between">
        <span class="text-muted">IP</span>
        <span><?= esc($active->ip_address ?: 'No registrada') ?></span>
      </li>
      <li class="list-group-item d-flex justify-content-between">
        <span class="text-muted">Última actividad</span>
        <span><?= esc(date('d/m/Y H:i', strtotime((string)$active->last_activity))) ?></span>
      </li>
    </ul>

    <form method="post" action="<?= base_url('auth/login/confirm') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="pending_token" value="<?= esc($pendingToken, 'attr') ?>">
      <button type="submit" class="btn btn-primary w-100 mb-2">
        <i class="bi bi-box-arrow-in-right me-1"></i>Cerrar la sesión anterior y continuar
      </button>
    </form>

    <a href="<?= base_url('login?cancel=1') ?>" class="btn btn-outline-secondary w-100">Cancelar</a>

    <div class="footer-text">© <?= date('Y') ?> - Consejo Nacional Electoral</div>
  </div>
</body>
</html>
