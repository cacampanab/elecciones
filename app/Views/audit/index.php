<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header"><h1>Auditoría</h1></div>
<div class="card">
  <div class="card-body">
    <div class="row g-2 mb-3">
      <div class="col-md-4"><input type="text" id="filterAction" class="form-control" placeholder="Buscar acción..." onkeyup="loadAudits()"></div>
      <div class="col-md-2"><button class="btn btn-primary w-100" onclick="loadAudits()"><i class="bi bi-search me-1"></i> Buscar</button></div>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0" id="auditTable">
      <thead class="table-light"><tr><th>Usuario</th><th>Acción</th><th>Tabla</th><th>ID</th><th>IP</th><th>Fecha</th></tr></thead>
      <tbody id="auditBody"></tbody>
    </table>
  </div>
  <div class="card-footer d-flex justify-content-between align-items-center">
    <small id="auditInfo" class="text-muted"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="auditPagination"></ul></nav>
  </div>
</div>
<script>
let auditPage = 1;
async function loadAudits() {
  const action = document.getElementById('filterAction').value;
  const r = await fetch('<?= base_url('ajax/audit-logs') ?>?page='+auditPage+'&action='+encodeURIComponent(action));
  const d = await r.json();
  let html = '';
  (d.data||[]).forEach(a => {
    html += '<tr><td><strong>'+ (a.first_name||a.username) +'</strong></td>';
    html += '<td><span class="badge bg-info">'+a.action+'</span></td>';
    html += '<td>'+(a.table_name||'-')+'</td><td>'+(a.record_id||'-')+'</td>';
    html += '<td><small>'+a.ip_address+'</small></td>';
    html += '<td>'+new Date(a.created_at).toLocaleString('es-EC')+'</td></tr>';
  });
  document.getElementById('auditBody').innerHTML = html || '<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>';
  document.getElementById('auditInfo').textContent = (d.total||0) + ' registros (pág ' + d.page + '/' + d.total_pages + ')';
  let pagHtml = '';
  for (let i=1; i<=(d.total_pages||1); i++) {
    pagHtml += '<li class="page-item '+(i===auditPage?'active':'')+'"><a class="page-link" href="#" onclick="auditPage='+i+';loadAudits();return false">'+i+'</a></li>';
  }
  document.getElementById('auditPagination').innerHTML = pagHtml;
}
loadAudits();
</script>
<?= $this->endSection() ?>
