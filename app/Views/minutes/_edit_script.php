<script>
const canVerifyUser = <?= has_permission('results.verify') ? 'true' : 'false' ?>;
const canEditUser = <?= (has_permission('results.update') || has_permission('results.verify')) ? 'true' : 'false' ?>;
const isOperatorUser = <?= is_operator() ? 'true' : 'false' ?>;

async function editMinute(id) {
  document.getElementById('emMinuteId').value = id;
  document.getElementById('emTitle').textContent = '#' + id;
  document.getElementById('emStatus').innerHTML = '';
  document.getElementById('emActaBox').innerHTML = '<div class="text-muted">Cargando...</div>';
  document.getElementById('emActaInfo').innerHTML = '';
  document.getElementById('emStation').innerHTML = '';
  document.getElementById('emDignities').innerHTML = '<div class="text-muted">Cargando...</div>';
  document.getElementById('emReason').value = '';
  document.getElementById('emNotes').value = '';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('editMinuteModal')).show();
  try {
    const r = await fetch('<?= base_url('ajax/minute-data') ?>/' + id);
    const d = await r.json();
    if (d.error) { document.getElementById('emDignities').innerHTML = '<div class="alert alert-danger">'+d.error+'</div>'; return; }

    const m = d.minute || {};
    if (m.is_image) {
      document.getElementById('emActaBox').innerHTML = '<img src="'+m.web_path+'" class="img-fluid rounded" style="max-height:420px;cursor:zoom-in" onclick="window.open(this.src)">';
    } else {
      document.getElementById('emActaBox').innerHTML = '<div class="text-center"><i class="bi bi-filetype-pdf display-1 text-danger"></i><div class="mt-2"><a href="'+m.web_path+'" target="_blank" class="btn btn-sm btn-outline-danger">Abrir PDF</a></div></div>';
    }
    document.getElementById('emActaInfo').innerHTML = '<strong>'+m.original_name+'</strong><br>Versión '+m.version+' &middot; '+(m.file_size/1024).toFixed(1)+' KB &middot; <a href="<?= base_url('minutes/download') ?>/'+m.id+'" target="_blank">Descargar</a>';

    const statusClasses = {registrada: 'bg-primary', con_novedad: 'bg-warning text-dark', verificada: 'bg-success'};
    const statusLabels = {registrada: 'Registrada', con_novedad: 'Con novedad', verificada: 'Verificada'};
    let statusHtml = '<span class="badge ' + (statusClasses[m.status] || 'bg-secondary') + '">' + (statusLabels[m.status] || m.status || '') + '</span> ';
    if (m.consistency_status === 'ok') statusHtml += '<span class="badge bg-success-subtle text-success border border-success">Consistente</span>';
    else if (m.consistency_status === 'error') statusHtml += '<span class="badge bg-danger-subtle text-danger border border-danger">Inconsistente</span>';
    if (m.consistency_detail) statusHtml += '<div class="small text-danger mt-1">'+m.consistency_detail+'</div>';
    if (isOperatorUser) statusHtml += '<div class="small text-muted mt-1">Como operador solo puede corregir actas con novedad e indicar el motivo.</div>';
    document.getElementById('emStatus').innerHTML = statusHtml;

    const s = d.station || {};
    document.getElementById('emStation').innerHTML = '<div class="alert alert-light border mb-0 py-2"><strong>JRV '+(s.numero_junta||'—')+'</strong> '+
      (s.genero ? '<span class="badge" style="background:'+(s.genero==='femenina'?'#e83e8c':'#0d6efd')+'">'+s.genero+'</span> ' : '')+
      '<small class="text-muted">'+(s.name_province||'')+' / '+(s.name_canton||'')+' / '+(s.name_parish||'')+' / '+(s.name_precinct||'')+'</small>'+
      (d.dignity ? '<div class="mt-1"><strong>Dignidad:</strong> '+d.dignity.name+'</div>' : '')+'</div>';

    let html = '';
    (d.dignities||[]).forEach(dig => {
      html += '<div class="card mb-2"><div class="card-header py-1 bg-secondary bg-opacity-10"><strong>'+dig.name+'</strong></div><div class="card-body py-2">';
      dig.candidates.forEach(c => {
        html += '<div class="row g-1 align-items-center mb-1">'
          + '<div class="col-1 text-center"><span class="badge" style="background:'+(c.party_color||'#6c757d')+'">'+(c.list_number||'?')+'</span></div>'
          + '<div class="col-8"><span class="small">'+c.first_name+' '+c.last_name+' <span class="text-muted">('+(c.party_acronym||'')+')</span></span></div>'
          + '<div class="col-3"><input type="number" class="form-control form-control-sm" name="votes['+c.result_id+']" value="'+c.votes+'" min="0" step="1" '+(canEditUser?'':'disabled')+'></div>'
          + '</div>';
      });
      html += '</div></div>';
    });
    document.getElementById('emDignities').innerHTML = html || '<div class="alert alert-warning mb-0">Sin resultados registrados para esta dignidad.</div>';

    const t = d.totals || {};
    document.getElementById('emBlank').value = t.blank_votes || 0;
    document.getElementById('emNull').value = t.null_votes || 0;
    document.getElementById('emVoters').value = t.total_voters || 0;
    document.getElementById('emRegistered').value = t.registered_voters || 0;
    const verified = document.getElementById('emVerified');
    if (verified) {
      verified.checked = m.status === 'verificada';
      verified.disabled = !canVerifyUser;
    }
    document.getElementById('emNotes').value = m.notes || '';
    document.getElementById('emReason').required = isOperatorUser || m.status === 'verificada' || m.consistency_status === 'error';
  } catch (e) {
    document.getElementById('emDignities').innerHTML = '<div class="alert alert-danger">Error al cargar los datos</div>';
  }
}
</script>
