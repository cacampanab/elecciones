<?php $canVerify = has_permission('results.verify'); $canEditActa = has_permission('results.update') || has_permission('results.verify'); ?>
<div class="modal fade" id="editMinuteModal" tabindex="-1"><div class="modal-dialog modal-xl"><form method="post" action="<?= base_url('minutes/update') ?>" class="modal-content"><?= csrf_field() ?>
  <input type="hidden" name="minute_id" id="emMinuteId">
  <div class="modal-header">
    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar / Verificar Acta <span id="emTitle" class="text-muted fs-6"></span></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">
    <div id="emStatus" class="mb-2"></div>
    <div class="row g-3">
      <div class="col-md-5">
        <div class="border rounded p-2 text-center d-flex align-items-center justify-content-center" id="emActaBox" style="min-height:320px;background:#f8f9fa"></div>
        <div id="emActaInfo" class="small text-muted mt-2"></div>
      </div>
      <div class="col-md-7">
        <div id="emStation" class="mb-2"></div>
        <div id="emDignities" style="max-height:300px;overflow-y:auto"></div>
        <div class="row g-2 mt-3 border-top pt-3">
          <div class="col-6 col-md-3"><label class="form-label small mb-0">Blancos</label><input type="number" name="blank_votes" id="emBlank" class="form-control form-control-sm" min="0" step="1"></div>
          <div class="col-6 col-md-3"><label class="form-label small mb-0">Nulos</label><input type="number" name="null_votes" id="emNull" class="form-control form-control-sm" min="0" step="1"></div>
          <div class="col-6 col-md-3"><label class="form-label small mb-0">Sufragantes</label><input type="number" name="total_voters" id="emVoters" class="form-control form-control-sm" min="0" step="1"></div>
          <div class="col-6 col-md-3"><label class="form-label small mb-0">Electores</label><input type="number" name="registered_voters" id="emRegistered" class="form-control form-control-sm" min="0" step="1"></div>
        </div>
        <div class="row g-2 mt-2">
          <div class="col-md-6"><label class="form-label small mb-0">Motivo *</label><input type="text" name="reason" id="emReason" class="form-control form-control-sm" maxlength="255" placeholder="Motivo de la corrección / verificación"></div>
          <div class="col-md-6"><label class="form-label small mb-0">Observaciones</label><input type="text" name="notes" id="emNotes" class="form-control form-control-sm" maxlength="255" placeholder="Opcional"></div>
        </div>
        <?php if ($canVerify): ?>
        <div class="form-check form-switch mt-3">
          <input class="form-check-input" type="checkbox" name="is_verified" id="emVerified" value="1">
          <label class="form-check-label" for="emVerified"><strong>Acta verificada</strong> — los datos coinciden con la foto</label>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar cambios</button>
  </div>
</form></div></div>
