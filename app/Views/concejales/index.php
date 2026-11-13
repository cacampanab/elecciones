<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div>
    <h1 class="mb-1">Concejales</h1>
    <p class="text-muted mb-0 small">
      <?php if (!empty($circumscription)): ?>
        Circunscripción: <strong><?= esc($circumscription->name) ?></strong> ·
      <?php endif; ?>
      Listas, integrantes, votos por lista y distribución de escaños por el método D’Hondt.
    </p>
  </div>
  <form method="get" action="<?= base_url('concejales') ?>" class="d-flex flex-wrap gap-2 align-items-end">
    <div>
      <label class="form-label small mb-1">Elección</label>
      <select name="election_id" class="form-select form-select-sm" style="min-width:230px" onchange="this.form.submit()" <?= empty($elections) ? 'disabled' : '' ?>>
        <?php foreach ($elections as $item): ?>
          <option value="<?= (int)$item->id ?>" <?= (int)$item->id === (int)$electionId ? 'selected' : '' ?>><?= esc($item->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label small mb-1">Cantón</label>
      <select name="canton_id" class="form-select form-select-sm" style="min-width:210px" onchange="this.form.submit()" <?= empty($cantons) ? 'disabled' : '' ?>>
        <?php foreach ($cantons as $item): ?>
          <option value="<?= (int)$item->id ?>" <?= (int)$item->id === (int)$cantonId ? 'selected' : '' ?>><?= esc($item->province_name . ' / ' . $item->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if (!empty($usesCircumscriptions)): ?>
      <div>
        <label class="form-label small mb-1">Circunscripción</label>
        <select name="circumscription_id" class="form-select form-select-sm" style="min-width:250px" onchange="this.form.submit()">
          <?php foreach ($circumscriptions as $item): ?>
            <option value="<?= (int)$item->id ?>" <?= (int)$item->id === (int)$circumscriptionId ? 'selected' : '' ?>><?= esc($item->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label small mb-1">Dignidad</label>
        <input type="text" class="form-control form-control-sm" style="min-width:190px" value="<?= esc($dignity->name ?? '') ?>" disabled>
      </div>
    <?php else: ?>
      <div>
        <label class="form-label small mb-1">Dignidad</label>
        <select name="dignity_id" class="form-select form-select-sm" style="min-width:200px" onchange="this.form.submit()" <?= empty($dignities) ? 'disabled' : '' ?>>
          <?php foreach ($dignities as $item): ?>
            <option value="<?= (int)$item->id ?>" <?= (int)$item->id === (int)$dignityId ? 'selected' : '' ?>><?= esc($item->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>
  </form>
</div>

<?php if ((int)$seats <= 0): ?>
  <div class="alert alert-warning d-flex justify-content-between align-items-center">
    <span><strong>No hay escaños configurados</strong> para este cantón y dignidad.</span>
    <a class="btn btn-sm btn-warning" href="<?= base_url('election-seats?election_id=' . (int)$electionId) ?>">Configurar escaños</a>
  </div>
<?php endif; ?>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-person-plus-fill me-2"></i>Agregar integrante a una lista</span>
    <span class="badge bg-primary"><?= (int)$seats ?> escaños configurados</span>
  </div>
  <div class="card-body">
    <form method="post" action="<?= base_url('concejales/candidates/save') ?>" class="row g-2 align-items-end">
      <?= csrf_field() ?>
      <input type="hidden" name="election_id" value="<?= (int)$electionId ?>">
      <input type="hidden" name="canton_id" value="<?= (int)$cantonId ?>">
      <input type="hidden" name="dignity_id" value="<?= (int)$dignityId ?>">
      <input type="hidden" name="circumscription_id" value="<?= (int)($circumscriptionId ?? 0) ?>">

      <div class="col-md-3">
        <label class="form-label small">Partido / lista *</label>
        <select name="political_party_id" class="form-select form-select-sm" required>
          <option value="">Seleccione...</option>
          <?php foreach ($parties as $party): ?>
            <option value="<?= (int)$party->id ?>"><?= esc($party->acronym . ' - ' . $party->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Nombres *</label>
        <input type="text" name="first_name" class="form-control form-control-sm" required>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Apellidos *</label>
        <input type="text" name="last_name" class="form-control form-control-sm" required>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Cédula</label>
        <input type="text" name="cedula" class="form-control form-control-sm" maxlength="13">
      </div>
      <div class="col-md-1">
        <label class="form-label small">Orden</label>
        <input type="number" name="position" class="form-control form-control-sm" min="1" step="1" placeholder="Auto">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg me-1"></i>Agregar</button>
      </div>
    </form>
    <div class="form-text mt-2">Si deja el orden vacío, se agregará al final de la lista. La lista se crea automáticamente para el partido seleccionado.</div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-list-ol me-2"></i>Listas e integrantes</span>
    <span class="badge bg-secondary"><?= count($lists) ?> listas</span>
  </div>
  <div class="card-body">
    <?php if (!$lists): ?>
      <div class="text-center text-muted py-4">Todavía no hay listas para este cantón y dignidad. Agregue el primer integrante arriba.</div>
    <?php else: ?>
      <?php foreach ($lists as $list): ?>
        <div class="border rounded mb-3">
          <div class="bg-light px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <?php if (!empty($list->party_logo)): ?>
                <img src="<?= base_url($list->party_logo) ?>" alt="" style="height:28px;width:28px;object-fit:contain" class="me-2 rounded border bg-white p-1">
              <?php endif; ?>
              <strong>Lista <?= (int)$list->list_number ?> - <?= esc($list->party_acronym) ?></strong>
              <span class="text-muted small ms-2"><?= esc($list->party_name) ?></span>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <span class="badge bg-primary"><?= count($list->candidates) ?> integrantes</span>
              <span class="badge bg-dark"><?= number_format((int)$list->votes) ?> votos</span>
            </div>
          </div>
          <form method="post" action="<?= base_url('concejales/candidates/update') ?>" class="p-2">
            <?= csrf_field() ?>
            <input type="hidden" name="election_id" value="<?= (int)$electionId ?>">
            <input type="hidden" name="canton_id" value="<?= (int)$cantonId ?>">
            <input type="hidden" name="dignity_id" value="<?= (int)$dignityId ?>">
            <input type="hidden" name="circumscription_id" value="<?= (int)($circumscriptionId ?? 0) ?>">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-2">
                <thead class="table-light">
                  <tr>
                    <th style="width:80px">Orden</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Cédula</th>
                    <th style="width:90px"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$list->candidates): ?>
                    <tr><td colspan="5" class="text-center text-muted">Sin integrantes.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($list->candidates as $candidate): ?>
                    <tr>
                      <td><input type="number" name="candidates[<?= (int)$candidate->id ?>][position]" value="<?= (int)$candidate->position ?>" class="form-control form-control-sm" min="1" step="1" required></td>
                      <td><input type="text" name="candidates[<?= (int)$candidate->id ?>][first_name]" value="<?= esc($candidate->first_name, 'attr') ?>" class="form-control form-control-sm" required></td>
                      <td><input type="text" name="candidates[<?= (int)$candidate->id ?>][last_name]" value="<?= esc($candidate->last_name, 'attr') ?>" class="form-control form-control-sm" required></td>
                      <td><input type="text" name="candidates[<?= (int)$candidate->id ?>][cedula]" value="<?= esc($candidate->cedula, 'attr') ?>" class="form-control form-control-sm" maxlength="13"></td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-danger"
                           href="<?= base_url('concejales/candidates/delete/' . (int)$candidate->id . '?election_id=' . (int)$electionId . '&canton_id=' . (int)$cantonId . '&dignity_id=' . (int)$dignityId . '&circumscription_id=' . (int)($circumscriptionId ?? 0)) ?>"
                           onclick="return confirm('¿Eliminar este integrante de la lista?')">
                          <i class="bi bi-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-save me-1"></i>Guardar nombres y orden</button>
            </div>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-bar-chart-line-fill me-2"></i>Votos registrados en actas</span>
    <span class="badge bg-primary">D’Hondt</span>
  </div>
  <div class="card-body">
    <?php if (!$lists): ?>
      <div class="text-center text-muted py-4">Primero agregue listas e integrantes.</div>
    <?php else: ?>
      <div class="alert alert-info small py-2">
        Los votos se toman automáticamente de las actas registradas por JRV en <strong>Resultados → Registrar Resultados</strong>.
      </div>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Lista</th>
              <th>Partido</th>
              <th class="text-end" style="width:180px">Votos en actas</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lists as $list): ?>
              <tr>
                <td><span class="badge bg-dark"><?= (int)$list->list_number ?></span></td>
                <td><strong><?= esc($list->party_acronym) ?></strong> <span class="text-muted small"><?= esc($list->party_name) ?></span></td>
                <td class="text-end"><strong><?= number_format((int)$list->votes) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="text-end mt-3">
        <a class="btn btn-primary" href="<?= base_url('concejales?election_id=' . (int)$electionId . '&canton_id=' . (int)$cantonId . '&dignity_id=' . (int)$dignityId . '&circumscription_id=' . (int)($circumscriptionId ?? 0) . '&calcular=1') ?>">
          <i class="bi bi-arrow-clockwise me-1"></i>Recalcular escaños
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($calculation): ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-trophy-fill me-2"></i>Distribución de escaños - <?= esc($dignity->name) ?><?= !empty($circumscription) ? ' / ' . esc($circumscription->name) : '' ?></span>
      <span class="badge bg-primary"><?= (int)$calculation['seats'] ?> escaños</span>
    </div>
    <div class="card-body">
      <?php foreach ($calculation['warnings'] as $warning): ?>
        <div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><?= esc($warning) ?></div>
      <?php endforeach; ?>

      <?php if (!$calculation['rows']): ?>
        <div class="text-center text-muted py-4">No hay votos positivos para calcular.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Lista</th>
                <th class="text-end">Votos</th>
                <th class="text-end">Escaños</th>
                <th>Candidatos electos</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($calculation['rows'] as $row): ?>
                <tr class="<?= (int)$row['seats'] > 0 ? 'table-success' : '' ?>">
                  <td><strong>Lista <?= (int)$row['list_number'] ?></strong><br><span class="small text-muted"><?= esc($row['name']) ?></span></td>
                  <td class="text-end"><?= number_format((int)$row['votes']) ?></td>
                  <td class="text-end"><span class="badge bg-<?= (int)$row['seats'] > 0 ? 'primary' : 'secondary' ?> fs-6"><?= (int)$row['seats'] ?></span></td>
                  <td>
                    <?php if ($row['elected']): ?>
                      <ol class="mb-0 ps-3">
                        <?php foreach ($row['elected'] as $elected): ?>
                          <li><?= esc(trim($elected['first_name'] . ' ' . $elected['last_name'])) ?></li>
                        <?php endforeach; ?>
                      </ol>
                    <?php else: ?>
                      <span class="text-muted small">Sin escaños.</span>
                    <?php endif; ?>
                    <?php if ((int)$row['missing_candidates'] > 0): ?>
                      <div class="small text-danger mt-1">Faltan <?= (int)$row['missing_candidates'] ?> candidato(s) para completar sus escaños.</div>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <details class="mt-3">
          <summary class="small text-muted">Ver cocientes utilizados</summary>
          <div class="table-responsive mt-2">
            <table class="table table-sm table-bordered mb-0">
              <thead class="table-light">
                <tr><th>#</th><th>Lista</th><th class="text-end">Votos</th><th class="text-end">Divisor</th><th class="text-end">Cociente</th></tr>
              </thead>
              <tbody>
                <?php foreach ($calculation['quotients'] as $index => $quotient): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= esc($quotient['list_name']) ?></td>
                    <td class="text-end"><?= number_format((int)$quotient['votes']) ?></td>
                    <td class="text-end"><?= (int)$quotient['divisor'] ?></td>
                    <td class="text-end"><strong><?= number_format((float)$quotient['quotient'], 4) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </details>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header"><i class="bi bi-info-circle me-2"></i>Manual rápido</div>
  <div class="card-body small">
    <ol class="mb-0 ps-3">
      <li>Configure los escaños en <strong>Administración → Escaños</strong> y, si el cantón usa circunscripciones, en <strong>Administración → Circunscripciones</strong>.</li>
      <li>Seleccione elección, cantón, circunscripción (si aplica) y dignidad.</li>
      <li>Agregue los integrantes de cada lista en el orden de la papeleta.</li>
      <li>Registre las actas por JRV en <strong>Resultados → Registrar Resultados</strong>.</li>
      <li>Presione <strong>Recalcular escaños</strong>.</li>
      <li>D’Hondt reparte los escaños y los asigna en el orden de cada lista.</li>
      <li>Si una lista gana más escaños que candidatos tiene, el sistema muestra una alerta.</li>
    </ol>
  </div>
</div>
<?= $this->endSection() ?>
