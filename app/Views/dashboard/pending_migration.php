<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header">
  <h1>Dashboard Ejecutivo</h1>
</div>

<div class="alert alert-warning">
  <h5 class="alert-heading"><i class="bi bi-database-gear me-2"></i>Base de datos pendiente de migración</h5>
  <p>El dashboard por dignidad necesita las migraciones nuevas. Aplícalas antes de continuar para habilitar actas por JRV y dignidad, consistencia y versionado.</p>
  <?php if (is_admin()): ?>
    <form method="post" action="<?= base_url('system/migrate') ?>" onsubmit="return confirm('¿Aplicar las migraciones pendientes de la base de datos?');">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-warning"><i class="bi bi-database-gear me-1"></i>Migrar base de datos</button>
    </form>
  <?php else: ?>
    <p class="mb-0">Solicite al administrador que aplique las migraciones pendientes.</p>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
