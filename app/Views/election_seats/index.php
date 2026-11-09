<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div>
    <h1 class="mb-1">Escaños de Concejales</h1>
    <p class="text-muted mb-0 small">Configure cuántos concejales urbanos y rurales se eligen por cantón. Use 0 para dejar un valor sin configurar.</p>
  </div>
  <form method="get" action="<?= base_url('election-seats') ?>" class="d-flex gap-2 align-items-center">
    <label class="form-label mb-0 text-nowrap">Elección</label>
    <select name="election_id" class="form-select form-select-sm" style="min-width:260px" onchange="this.form.submit()" <?= empty($elections) ? 'disabled' : '' ?>>
      <?php foreach ($elections as $election): ?>
        <option value="<?= (int)$election->id ?>" <?= (int)$election->id === (int)$electionId ? 'selected' : '' ?>>
          <?= esc($election->name) ?> (<?= esc($election->status) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<?php if (!$elections): ?>
  <div class="alert alert-warning">No hay elecciones registradas.</div>
<?php elseif (!$selectedElection): ?>
  <div class="alert alert-warning">Seleccione una elección para configurar sus escaños.</div>
<?php elseif (!$dignities): ?>
  <div class="alert alert-warning">No están activas las dignidades Concejal Urbano y Concejal Rural.</div>
<?php elseif (!$cantons): ?>
  <div class="alert alert-warning">No hay cantones activos para configurar.</div>
<?php else: ?>
  <div class="alert alert-info small d-flex align-items-start gap-2">
    <i class="bi bi-info-circle mt-1"></i>
    <div>
      <strong>Importante:</strong> la cantidad de escaños debe coincidir con la papeleta oficial.
      Los concejales urbanos y rurales se calculan por separado; no se suman.
      En Esmeraldas, por ejemplo, se configuran 9 urbanos y 2 rurales.
    </div>
  </div>

  <form method="post" action="<?= base_url('election-seats/save') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="election_id" value="<?= (int)$electionId ?>">

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-grid-3x3-gap-fill me-2"></i><?= esc($selectedElection->name) ?></span>
        <span class="badge bg-primary"><?= count($cantons) ?> cantones</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="min-width:150px">Provincia</th>
                <th style="min-width:180px">Cantón</th>
                <?php foreach ($dignities as $dignity): ?>
                  <th class="text-center" style="min-width:170px"><?= esc($dignity->name) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cantons as $canton): ?>
                <tr>
                  <td><?= esc($canton->province_name) ?> <span class="text-muted small">(<?= esc($canton->province_code) ?>)</span></td>
                  <td><strong><?= esc($canton->name) ?></strong></td>
                  <?php foreach ($dignities as $dignity): $seats = (int)($seatMap[(int)$canton->id][(int)$dignity->id] ?? 0); ?>
                    <td class="text-center">
                      <input
                        type="number"
                        name="seats[<?= (int)$canton->id ?>][<?= (int)$dignity->id ?>]"
                        value="<?= $seats > 0 ? $seats : '' ?>"
                        class="form-control form-control-sm text-center mx-auto"
                        style="max-width:100px"
                        min="0"
                        max="100"
                        step="1"
                        placeholder="0"
                      >
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">0 o vacío = no configurado.</small>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar configuración</button>
      </div>
    </div>
  </form>
<?php endif; ?>
<?= $this->endSection() ?>
