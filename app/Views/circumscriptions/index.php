<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div>
    <h1 class="mb-1">Circunscripciones</h1>
    <p class="text-muted mb-0 small">Agrupe parroquias para elegir concejales por circunscripción. Aplica solo a Concejal Urbano y Concejal Rural.</p>
  </div>
  <a class="btn btn-outline-primary btn-sm" href="<?= base_url('election-seats?election_id=' . (int)$electionId) ?>">
    <i class="bi bi-grid-3x3-gap-fill me-1"></i>Configurar escaños
  </a>
</div>

<?php if (!$elections): ?>
  <div class="alert alert-warning">No hay elecciones registradas.</div>
<?php else: ?>
  <div class="card mb-3">
    <div class="card-body">
      <form method="get" action="<?= base_url('circumscriptions') ?>" class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label small">Elección</label>
          <select name="election_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach ($elections as $election): ?>
              <option value="<?= (int)$election->id ?>" <?= (int)$election->id === (int)$electionId ? 'selected' : '' ?>>
                <?= esc($election->name) ?> (<?= esc($election->status) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small">Provincia</label>
          <select name="province_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach ($provinces as $province): ?>
              <option value="<?= (int)$province->id ?>" <?= (int)$province->id === (int)$provinceId ? 'selected' : '' ?>>
                <?= esc($province->name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small">Cantón</label>
          <select name="canton_id" class="form-select form-select-sm" onchange="this.form.submit()" <?= empty($cantons) ? 'disabled' : '' ?>>
            <?php foreach ($cantons as $item): ?>
              <option value="<?= (int)$item->id ?>" <?= (int)$item->id === (int)$cantonId ? 'selected' : '' ?>>
                <?= esc($item->name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>
  </div>

  <?php if (!$canton): ?>
    <div class="alert alert-warning">Seleccione un cantón.</div>
  <?php elseif (!$dignities): ?>
    <div class="alert alert-warning">No están activas las dignidades Concejal Urbano y Concejal Rural.</div>
  <?php else: ?>
    <?php if ($circumscriptions && $unassignedParishes): ?>
      <div class="alert alert-warning">
        <strong>Parroquias sin circunscripción:</strong>
        <?= esc(implode(', ', array_map(static fn($parish) => $parish->name, $unassignedParishes))) ?>.
        Las JRV de esas parroquias no podrán registrar votos de concejales hasta que las asigne.
      </div>
    <?php endif; ?>

    <div class="alert alert-info small">
      <i class="bi bi-info-circle me-1"></i>
      Si este cantón no usa circunscripciones, deje la tabla vacía. El sistema seguirá calculando los escaños por cantón como hasta ahora.
      Una parroquia solo puede pertenecer a una circunscripción y un partido puede tener una lista en cada circunscripción.
    </div>

    <form method="post" action="<?= base_url('circumscriptions/save') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="election_id" value="<?= (int)$electionId ?>">
      <input type="hidden" name="province_id" value="<?= (int)$provinceId ?>">
      <input type="hidden" name="canton_id" value="<?= (int)$cantonId ?>">

      <?php
      $rows = [];
      foreach ($circumscriptions as $circumscription) {
          $rows[] = [
              'id' => (int)$circumscription->id,
              'code' => $circumscription->code,
              'name' => $circumscription->name,
              'dignity_id' => (int)$circumscription->dignity_id,
              'parishes' => $parishMap[(int)$circumscription->id] ?? [],
          ];
      }
      $defaultDignity = (int)($dignities[0]->id ?? 0);
      for ($i = 0; $i < 2; $i++) {
          $rows[] = ['id' => 0, 'code' => '', 'name' => '', 'dignity_id' => $defaultDignity, 'parishes' => []];
      }
      ?>

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="bi bi-diagram-3-fill me-2"></i><?= esc($canton->name) ?></span>
          <span class="badge bg-primary"><?= count($circumscriptions) ?> circunscripción(es)</span>
        </div>
        <div class="card-body">
          <?php foreach ($rows as $index => $row): ?>
            <div class="border rounded p-3 mb-3 <?= $row['id'] ? '' : 'bg-light' ?>">
              <div class="row g-2">
                <div class="col-md-2">
                  <label class="form-label small">Código</label>
                  <input type="text" name="rows[<?= $index ?>][code]" value="<?= esc($row['code'], 'attr') ?>" class="form-control form-control-sm" maxlength="20" placeholder="U1">
                </div>
                <div class="col-md-4">
                  <label class="form-label small">Nombre</label>
                  <input type="text" name="rows[<?= $index ?>][name]" value="<?= esc($row['name'], 'attr') ?>" class="form-control form-control-sm" maxlength="150" placeholder="Circunscripción Urbana 1">
                </div>
                <div class="col-md-3">
                  <label class="form-label small">Dignidad</label>
                  <select name="rows[<?= $index ?>][dignity_id]" class="form-select form-select-sm">
                    <?php foreach ($dignities as $dignity): ?>
                      <option value="<?= (int)$dignity->id ?>" <?= (int)$dignity->id === (int)$row['dignity_id'] ? 'selected' : '' ?>>
                        <?= esc($dignity->name) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                  <?php if ($row['id']): ?>
                    <span class="badge bg-secondary me-2"><?= (int)($seatMap[$row['id']] ?? 0) ?> escaños</span>
                    <a class="btn btn-sm btn-outline-danger ms-auto"
                       href="<?= base_url('circumscriptions/delete/' . (int)$row['id']) ?>"
                       onclick="return confirm('¿Eliminar esta circunscripción? Solo se permite si no tiene listas ni resultados.')">
                      <i class="bi bi-trash me-1"></i>Eliminar
                    </a>
                  <?php else: ?>
                    <span class="text-muted small">Nueva</span>
                  <?php endif; ?>
                </div>
              </div>
              <input type="hidden" name="rows[<?= $index ?>][id]" value="<?= (int)$row['id'] ?>">

              <div class="mt-3">
                <label class="form-label small mb-1">Parroquias de esta circunscripción</label>
                <div class="border rounded p-2" style="max-height:190px; overflow:auto">
                  <div class="row g-1">
                    <?php foreach ($parishes as $parish): ?>
                      <div class="col-md-4 col-6">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox"
                                 name="rows[<?= $index ?>][parishes][]"
                                 value="<?= (int)$parish->id ?>"
                                 id="p<?= $index ?>_<?= (int)$parish->id ?>"
                                 <?= in_array((int)$parish->id, $row['parishes'], true) ? 'checked' : '' ?>>
                          <label class="form-check-label small" for="p<?= $index ?>_<?= (int)$parish->id ?>">
                            <?= esc($parish->name) ?>
                          </label>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
          <small class="text-muted">Las filas vacías se ignoran. Para eliminar una circunscripción use el botón Eliminar.</small>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar circunscripciones</button>
        </div>
      </div>
    </form>
  <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
