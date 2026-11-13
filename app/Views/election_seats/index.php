<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div>
    <h1 class="mb-1">Escaños de Concejales</h1>
    <p class="text-muted mb-0 small">Configure cuántos concejales se eligen por cantón o por circunscripción. Use 0 para dejar un valor sin configurar.</p>
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
      Si el cantón usa circunscripciones, configure los escaños por circunscripción (por ejemplo, Esmeraldas: Urbana 1 = 5, Urbana 2 = 4, Rural = 2).
    </div>
  </div>

  <form method="post" action="<?= base_url('election-seats/save') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="election_id" value="<?= (int)$electionId ?>">

    <div class="card mb-3">
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
                  <?php if (isset($cantonsWithCircumscriptions[(int)$canton->id])): ?>
                    <td colspan="<?= count($dignities) ?>" class="text-center">
                      <span class="badge bg-info text-dark"><i class="bi bi-diagram-2-fill me-1"></i>Se configura por circunscripción</span>
                      <a class="small ms-2" href="#circunscripciones">Ver detalle</a>
                    </td>
                  <?php else: ?>
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
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php if ($circumscriptions): ?>
      <?php
      $dignityNames = [];
      foreach ($dignities as $dignity) {
          $dignityNames[(int)$dignity->id] = $dignity->name;
      }
      $cantonMap = [];
      foreach ($cantons as $item) {
          $cantonMap[(int)$item->id] = $item;
      }
      ?>
      <div class="card mb-3" id="circunscripciones">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="bi bi-diagram-2-fill me-2"></i>Escaños por circunscripción</span>
          <span class="badge bg-secondary"><?= count($circumscriptions) ?> circunscripciones</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Provincia</th>
                  <th>Cantón</th>
                  <th>Circunscripción</th>
                  <th>Dignidad</th>
                  <th class="text-center" style="width:150px">Escaños</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($circumscriptions as $circumscription): ?>
                  <?php $canton = $cantonMap[(int)$circumscription->canton_id] ?? null; ?>
                  <tr>
                    <td><?= esc($canton->province_name ?? '') ?></td>
                    <td><?= esc($canton->name ?? '') ?></td>
                    <td><strong><?= esc($circumscription->name) ?></strong> <span class="text-muted small">(<?= esc($circumscription->code) ?>)</span></td>
                    <td><?= esc($dignityNames[(int)$circumscription->dignity_id] ?? '') ?></td>
                    <td class="text-center">
                      <input
                        type="number"
                        name="circ_seats[<?= (int)$circumscription->id ?>]"
                        value="<?= (int)($circSeatMap[(int)$circumscription->id] ?? 0) > 0 ? (int)$circSeatMap[(int)$circumscription->id] : '' ?>"
                        class="form-control form-control-sm text-center mx-auto"
                        style="max-width:100px"
                        min="0"
                        max="100"
                        step="1"
                        placeholder="0"
                      >
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">0 o vacío = no configurado.</small>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar configuración</button>
      </div>
    </div>
  </form>
<?php endif; ?>
<?= $this->endSection() ?>
