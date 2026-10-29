<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Sistema de Control Electoral') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.7.1/dist/libs/tom-select/dist/css/tom-select.bootstrap5.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    :root {
      --sidebar-bg: #1a1f2e;
      --sidebar-hover: #262d3f;
      --sidebar-active: #0d6efd;
      --topbar-height: 60px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f6f9;
      overflow-x: hidden;
    }

    .wrapper {
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 260px;
      background: var(--sidebar-bg);
      color: #c2c7d0;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      z-index: 1000;
      transition: transform .3s ease;
      overflow-y: auto;
    }

    .sidebar.hidden {
      transform: translateX(-260px);
    }

    .sidebar-brand {
      padding: 18px 20px;
      font-size: 1.1rem;
      font-weight: 700;
      border-bottom: 1px solid rgba(255, 255, 255, .05);
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sidebar-brand i {
      font-size: 1.6rem;
      color: #0d6efd;
    }

    .sidebar .nav-link {
      color: #c2c7d0;
      padding: 10px 20px;
      font-size: .9rem;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all .2s;
      text-decoration: none;
      border-left: 3px solid transparent;
    }

    .sidebar .nav-link:hover {
      background: var(--sidebar-hover);
      color: #fff;
    }

    .sidebar .nav-link.active {
      background: var(--sidebar-hover);
      color: #fff;
      border-left-color: var(--sidebar-active);
    }

    .sidebar .nav-link i {
      width: 20px;
      text-align: center;
      font-size: 1.1rem;
    }

    .sidebar .nav-header {
      padding: 15px 20px 5px;
      font-size: .7rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #6c757d;
    }

    .sidebar .collapse .nav-link {
      padding-left: 53px;
      font-size: .85rem;
    }

    .main-content {
      margin-left: 260px;
      flex: 1;
      min-height: 100vh;
      transition: margin-left .3s ease;
    }

    .main-content.expanded {
      margin-left: 0;
    }

    .topbar {
      background: #fff;
      height: var(--topbar-height);
      display: flex;
      align-items: center;
      padding: 0 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
      position: sticky;
      top: 0;
      z-index: 999;
    }

    .topbar .toggle-btn {
      cursor: pointer;
      font-size: 1.3rem;
      color: #6c757d;
      border: none;
      background: none;
      padding: 5px;
    }

    .topbar .toggle-btn:hover {
      color: #343a40;
    }

    .topbar .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-left: auto;
    }

    .topbar .user-info .avatar {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: #0d6efd;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: .85rem;
    }

    .content-wrapper {
      padding: 20px;
    }

    .content-header {
      margin-bottom: 20px;
    }

    .content-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #343a40;
      margin: 0;
    }

    .content-header .breadcrumb {
      background: none;
      padding: 0;
      margin: 5px 0 0;
      font-size: .85rem;
    }

    .card {
      border: none;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
      margin-bottom: 20px;
    }

    .card-header {
      background: #fff;
      border-bottom: 1px solid #e9ecef;
      padding: 15px 20px;
      font-weight: 600;
    }

    .stat-card {
      transition: transform .2s;
      cursor: default;
    }

    .stat-card:hover {
      transform: translateY(-3px);
    }

    .stat-card .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
    }

    .stat-card .stat-value {
      font-size: 1.8rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .stat-card .stat-label {
      font-size: .85rem;
      color: #6c757d;
    }

    .table th {
      font-weight: 600;
      color: #495057;
      border-top: none;
    }

    .btn {
      font-size: .875rem;
    }

    .btn-sm {
      font-size: .8rem;
    }

    .badge {
      font-weight: 500;
    }

    .progress {
      height: 8px;
    }

    .footer {
      text-align: center;
      padding: 15px 0;
      color: #6c757d;
      font-size: .85rem;
      border-top: 1px solid #e9ecef;
      margin-top: 20px;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-260px);
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }
    }

    .swal2-popup {
      font-size: .85rem;
    }

    .page-enter {
      animation: fadeIn .3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body>
  <div class="wrapper">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <i class="bi bi-ballot-check-fill"></i>
        <span>Control Electoral</span>
      </div>
      <nav class="mt-2">
        <?php if (session()->get('role') !== 'Operador Electoral'): ?>
          <div class="nav-header">Principal</div>
          <a href="<?= base_url('dashboard') ?>" class="nav-link <?= current_url() === base_url('dashboard') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
          </a>
        <?php endif; ?>

        <?php if (session()->get('role') === 'Administrador General'): ?>
          <div class="nav-header">Administración</div>
          <a href="<?= base_url('users') ?>" class="nav-link <?= strpos(current_url(), '/users') !== false ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Usuarios
          </a>
          <a href="<?= base_url('roles') ?>" class="nav-link <?= strpos(current_url(), '/roles') !== false ? 'active' : '' ?>">
            <i class="bi bi-shield-check"></i> Roles
          </a>
          <a href="<?= base_url('elections') ?>" class="nav-link <?= strpos(current_url(), '/elections') !== false ? 'active' : '' ?>">
            <i class="bi bi-vote-yes"></i> Elecciones
          </a>
          <a href="<?= base_url('dignities') ?>" class="nav-link <?= strpos(current_url(), '/dignities') !== false ? 'active' : '' ?>">
            <i class="bi bi-award-fill"></i> Dignidades
          </a>
          <a href="<?= base_url('parties') ?>" class="nav-link <?= strpos(current_url(), '/parties') !== false ? 'active' : '' ?>">
            <i class="bi bi-building"></i> Partidos
          </a>
          <a href="<?= base_url('candidates') ?>" class="nav-link <?= strpos(current_url(), '/candidates') !== false ? 'active' : '' ?>">
            <i class="bi bi-person-badge-fill"></i> Candidatos
          </a>
        <?php endif; ?>

        <?php if (session()->get('role') === 'Administrador General'): ?>
          <div class="nav-header">Territorial</div>
          <a href="<?= base_url('territorial') ?>" class="nav-link <?= strpos(current_url(), '/territorial') !== false ? 'active' : '' ?>">
            <i class="bi bi-geo-alt-fill"></i> División Territorial
          </a>
          <a href="<?= base_url('polling-stations') ?>" class="nav-link <?= strpos(current_url(), '/polling-stations') !== false ? 'active' : '' ?>">
            <i class="bi bi-building-check"></i> JRV
          </a>
          <a href="<?= base_url('assignments') ?>" class="nav-link <?= strpos(current_url(), '/assignments') !== false ? 'active' : '' ?>">
            <i class="bi bi-person-plus-fill"></i> Asignaciones
          </a>
        <?php endif; ?>

        <div class="nav-header">Resultados</div>
        <?php if (session()->get('role') === 'Operador Electoral'): ?>
          <a href="<?= base_url('results/register') ?>" class="nav-link <?= strpos(current_url(), '/results/register') !== false ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-plus-fill"></i> Registrar Resultados
          </a>
        <?php endif; ?>
        <?php if (session()->get('role') !== 'Operador Electoral'): ?>
          <a href="<?= base_url('results') ?>" class="nav-link <?= strpos(current_url(), '/results') !== false && strpos(current_url(), '/register') === false ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-fill"></i> Resultados
          </a>
        <?php endif; ?>
        <a href="<?= base_url('minutes') ?>" class="nav-link <?= strpos(current_url(), '/minutes') !== false ? 'active' : '' ?>">
          <i class="bi bi-file-earmark-image-fill"></i> Actas
        </a>
        <?php if (has_permission('minutes.read')):
          $incBadge = 0;
          try {
            $incBuilder = db_connect()->table('uploaded_minutes')
              ->where('is_current', 1)
              ->groupStart()
              ->where('consistency_status', 'error')
              ->orWhere('status', 'con_novedad')
              ->groupEnd();
            $incElectionId = (int)($electionId ?? 0);
            if (!$incElectionId) {
              $incCurrent = model('App\Models\ElectionModel')->getCurrent();
              $incElectionId = $incCurrent ? (int)$incCurrent->id : 0;
            }
            if ($incElectionId) {
              $incBuilder->where('election_id', $incElectionId);
            }
            if (is_operator()) {
              $incAssigned = array_column(db_connect()->table('assignments')
                ->select('polling_station_id')
                ->where('user_id', (int)session()->get('user_id'))
                ->where('is_active', 1)
                ->get()->getResultArray(), 'polling_station_id');
              if ($incAssigned) {
                $incBuilder->whereIn('polling_station_id', $incAssigned);
              } else {
                $incBuilder->where('id', -1);
              }
            }
            $incBadge = (int)$incBuilder->countAllResults();
          } catch (\Throwable $e) {
            $incBadge = 0;
          }
        ?>
          <a href="<?= base_url('inconsistencies') ?>" class="nav-link <?= strpos(current_url(), '/inconsistencies') !== false ? 'active' : '' ?>">
            <i class="bi bi-exclamation-triangle-fill"></i> Inconsistencias
            <?php if ($incBadge > 0): ?><span class="badge bg-danger rounded-pill ms-auto"><?= $incBadge ?></span><?php endif; ?>
          </a>
        <?php endif; ?>
        <?php if (session()->get('role') !== 'Operador Electoral'): ?>
          <a href="<?= base_url('reports') ?>" class="nav-link <?= strpos(current_url(), '/reports') !== false ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-pdf-fill"></i> Reportes
          </a>
        <?php endif; ?>

        <?php if (session()->get('role') === 'Administrador General'): ?>
          <div class="nav-header">Sistema</div>
          <a href="<?= base_url('audit') ?>" class="nav-link <?= strpos(current_url(), '/audit') !== false ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i> Auditoría
          </a>
          <form method="post" action="<?= base_url('system/migrate') ?>" class="px-3 pt-2" onsubmit="return confirm('¿Aplicar las migraciones pendientes de la base de datos?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-warning w-100"><i class="bi bi-database-gear me-1"></i>Migrar base de datos</button>
          </form>
        <?php endif; ?>
      </nav>
    </aside>

    <div class="main-content" id="mainContent">
      <header class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="user-info">
          <span class="text-muted small"><?= esc(session()->get('first_name') . ' ' . session()->get('last_name')) ?></span>
          <div class="dropdown">
            <button class="btn p-0" data-bs-toggle="dropdown">
              <div class="avatar"><?= substr(session()->get('first_name'), 0, 1) . substr(session()->get('last_name'), 0, 1) ?></div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
            </ul>
          </div>
        </div>
      </header>

      <div class="content-wrapper page-enter">
        <?= view('partials/alerts') ?>
        <?= $this->renderSection('content') ?>
      </div>

      <footer class="footer">
        <small>&copy; <?= date('Y') ?> Sistema de Control Electoral. Todos los derechos reservados.</small>
      </footer>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.7.1/dist/libs/tom-select/dist/js/tom-select.base.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('show');
      document.getElementById('mainContent').classList.toggle('expanded');
    }
    $(document).ready(function() {
      $('.data-table').DataTable({
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 25
      });
      $('[data-bs-toggle="tooltip"]').tooltip();
    });

    function confirmDelete(msg) {
      return Swal.fire({
        title: '¿Confirmar?',
        text: msg || '¿Está seguro de eliminar este registro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      });
    }

    function showAlert(icon, title, text) {
      Swal.fire({
        icon,
        title,
        text,
        timer: 3000,
        showConfirmButton: false
      });
    }
  </script>
</body>

</html>