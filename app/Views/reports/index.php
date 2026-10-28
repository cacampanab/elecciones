<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header">
  <div>
    <h1>Reportes</h1>
    <p class="text-muted mb-0">Genere el informe de resultados por elección y dignidad.</p>
  </div>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start">
  <i class="bi bi-info-circle-fill mt-1"></i>
  <div>
    <strong>Contenido del reporte:</strong> el PDF incluye resumen ejecutivo, resultados, distribución de votos, participación, control de actas, provincias y resumen de inconsistencias. El Excel incluye el detalle completo, incluidas las actas y los candidatos por acta.
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-file-earmark-bar-graph me-2"></i>Reporte de Resultados</div>
      <div class="card-body">
        <form method="get" action="<?= base_url('reports/export-pdf') ?>" target="_blank">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Elección</label>
              <select name="election_id" class="form-select" required onchange="window.location.href='<?= base_url('reports') ?>?election_id=' + encodeURIComponent(this.value)">
                <option value="">Seleccione</option>
                <?php foreach ($elections as $election): ?>
                  <option value="<?= (int)$election->id ?>" <?= (int)$election->id === (int)$electionId ? 'selected' : '' ?>><?= esc($election->name) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Dignidad</label>
              <select name="dignity_id" class="form-select" required <?= $dignities ? '' : 'disabled' ?>>
                <option value="">Seleccione</option>
                <?php foreach ($dignities as $dignity): ?>
                  <option value="<?= (int)$dignity->id ?>" <?= (int)$dignity->id === (int)$dignityId ? 'selected' : '' ?>><?= esc($dignity->name) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if (!$dignities): ?>
              <div class="col-12">
                <div class="alert alert-warning mb-0 py-2">
                  La elección seleccionada no tiene dignidades habilitadas para reportes.
                </div>
              </div>
            <?php endif; ?>
            <div class="col-md-6">
              <button type="submit" class="btn btn-danger w-100" <?= $dignities ? '' : 'disabled' ?>>
                <i class="bi bi-file-pdf me-1"></i>Exportar PDF
              </button>
            </div>
            <div class="col-md-6">
              <button type="submit" formaction="<?= base_url('reports/export-excel') ?>" class="btn btn-success w-100" <?= $dignities ? '' : 'disabled' ?>>
                <i class="bi bi-file-excel me-1"></i>Exportar Excel
              </button>
            </div>
          </div>
        </form>

        <div class="mt-3 small text-muted">
          <i class="bi bi-shield-check me-1"></i>
          Los reportes incluyen únicamente actas vigentes con resultados registrados.
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-graph-up me-2"></i>Avance de Carga</div>
      <div class="card-body d-flex flex-column">
        <p class="text-muted">Cobertura de JRV y actas vigentes recibidas por provincia.</p>
        <div class="mt-auto">
          <a href="<?= base_url('reports/advance' . ($electionId ? '?election_id=' . (int)$electionId : '')) ?>" target="_blank" class="btn btn-info w-100">
            <i class="bi bi-file-pdf me-1"></i>Exportar Avance PDF
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
