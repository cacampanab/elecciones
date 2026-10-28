<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header"><h1>Resultados Electorales</h1></div>
<div class="card">
  <div class="card-body">
    <div class="row g-2">
      <div class="col-md-2">
        <label class="form-label small">Elección</label>
        <select id="fElection" class="form-select form-select-sm">
          <option value="">Seleccione...</option>
          <?php foreach ($elections as $e): ?><option value="<?= $e->id ?>"><?= esc($e->name) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Dignidad</label>
        <select id="fDignity" class="form-select form-select-sm">
          <option value="">Seleccione...</option>
          <?php foreach ($dignities as $d): ?><option value="<?= $d->id ?>"><?= esc($d->name) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Provincia</label>
        <select id="fProvince" class="form-select form-select-sm" onchange="loadCantons()">
          <option value="">Seleccione...</option>
          <?php
          $seen = [];
          foreach ($provinces as $p):
            $code = (int)$p->code;
            if (in_array($code, $seen)) continue;
            $seen[] = $code;
          ?>
          <option value="<?= $code ?>"><?= esc($p->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Cantón</label>
        <select id="fCanton" class="form-select form-select-sm" onchange="loadParishes()" disabled>
          <option value="">Primero provincia</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Parroquia</label>
        <select id="fParish" class="form-select form-select-sm" onchange="loadPrecincts()" disabled>
          <option value="">Primero cantón</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Recinto</label>
        <select id="fPrecinct" class="form-select form-select-sm" onchange="loadStations()" disabled>
          <option value="">Primero parroquia</option>
        </select>
      </div>
    </div>
    <div class="row g-2 mt-1">
      <div class="col-md-4">
        <label class="form-label small">JRV <small class="text-muted">(opcional)</small></label>
        <select id="fStation" class="form-select form-select-sm" disabled>
          <option value="">Todas</option>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary btn-sm w-100" onclick="loadResults()"><i class="bi bi-search me-1"></i> Consultar</button>
      </div>
    </div>
  </div>
</div>
<div id="resultsContainer"></div>

<script>
async function loadCantons() {
  const sel = document.getElementById('fCanton');
  sel.innerHTML = '<option value="">Cargando...</option>'; sel.disabled = true;
  document.getElementById('fParish').innerHTML = '<option value="">Primero cantón</option>'; document.getElementById('fParish').disabled = true;
  document.getElementById('fPrecinct').innerHTML = '<option value="">Primero parroquia</option>'; document.getElementById('fPrecinct').disabled = true;
  document.getElementById('fStation').innerHTML = '<option value="">Todas</option>'; document.getElementById('fStation').disabled = true;
  const pc = document.getElementById('fProvince').value;
  if (!pc) { sel.innerHTML = '<option value="">Seleccione provincia</option>'; return; }
  const r = await fetch('<?= base_url('ajax/cantons-by-province') ?>?province_code=' + pc);
  const d = await r.json();
  sel.innerHTML = '<option value="">Seleccione cantón...</option>';
  d.forEach(c => { sel.innerHTML += '<option value="'+c.code+'">'+c.name+'</option>'; });
  sel.disabled = false;
}

async function loadParishes() {
  const sel = document.getElementById('fParish');
  sel.innerHTML = '<option value="">Cargando...</option>'; sel.disabled = true;
  document.getElementById('fPrecinct').innerHTML = '<option value="">Primero parroquia</option>'; document.getElementById('fPrecinct').disabled = true;
  document.getElementById('fStation').innerHTML = '<option value="">Todas</option>'; document.getElementById('fStation').disabled = true;
  const cc = document.getElementById('fCanton').value;
  if (!cc) { sel.innerHTML = '<option value="">Seleccione cantón</option>'; return; }
  const r = await fetch('<?= base_url('ajax/parishes-by-canton') ?>?canton_code=' + cc + '&election_id=' + document.getElementById('fElection').value);
  const d = await r.json();
  sel.innerHTML = '<option value="">Seleccione parroquia...</option>';
  d.forEach(p => { sel.innerHTML += '<option value="'+p.code+'">'+p.name+'</option>'; });
  sel.disabled = false;
}

async function loadPrecincts() {
  const sel = document.getElementById('fPrecinct');
  sel.innerHTML = '<option value="">Cargando...</option>'; sel.disabled = true;
  document.getElementById('fStation').innerHTML = '<option value="">Todas</option>'; document.getElementById('fStation').disabled = true;
  const pc = document.getElementById('fParish').value;
  if (!pc) { sel.innerHTML = '<option value="">Seleccione parroquia</option>'; return; }
  const r = await fetch('<?= base_url('ajax/precincts-by-parish') ?>?parish_code=' + pc + '&election_id=' + document.getElementById('fElection').value);
  const d = await r.json();
  sel.innerHTML = '<option value="">Seleccione recinto...</option>';
  d.forEach(p => { sel.innerHTML += '<option value="'+p.id+'">'+p.name+'</option>'; });
  sel.disabled = false;
}

async function loadStations() {
  const sel = document.getElementById('fStation');
  sel.innerHTML = '<option value="">Cargando...</option>'; sel.disabled = true;
  const pid = document.getElementById('fPrecinct').value;
  const eid = document.getElementById('fElection').value;
  if (!pid || !eid) { sel.innerHTML = '<option value="">Todas</option>'; sel.disabled = false; return; }
  const r = await fetch('<?= base_url('ajax/stations-by-precinct') ?>?precinct_id=' + pid + '&election_id=' + eid);
  const d = await r.json();
  sel.innerHTML = '<option value="">Todas</option>';
  d.forEach(s => { sel.innerHTML += '<option value="'+s.id+'">'+s.numero_junta+' ('+(s.genero?s.genero.charAt(0).toUpperCase()+s.genero.slice(1):'')+')</option>'; });
  sel.disabled = false;
}

async function loadResults() {
  const eid = document.getElementById('fElection').value, did = document.getElementById('fDignity').value;
  if (!eid || !did) { alert('Seleccione elección y dignidad'); return; }
  const psid = document.getElementById('fStation').value;
  let url = '<?= base_url('ajax/results-by-dignity') ?>/'+eid+'/'+did;
  if (psid) url += '?polling_station_id='+psid;
  const r = await fetch(url); const json = await r.json();
  const data = json.results || [];
  let html = '<div class="card mt-3"><div class="card-header d-flex justify-content-between align-items-center">';
  html += '<span><strong>Resultados</strong></span>';
  html += '<button class="btn btn-sm btn-success" onclick="window.open(\'<?= base_url('reports/export-pdf') ?>?election_id='+eid+'&dignity_id='+did+'\',\'_blank\')"><i class="bi bi-file-pdf me-1"></i> PDF</button></div></div>';
  html += '<div class="card-body p-0"><table class="table table-hover mb-0"><thead class="table-light"><tr>';
  html += '<th>#</th><th>Candidato</th><th>Partido</th><th>Votos</th>';
  html += '</tr></thead><tbody>';
  let i = 1, total = 0;
  data.forEach(c => {
    html += '<tr><td>'+(i++)+'</td><td><strong>'+c.first_name+' '+c.last_name+'</strong></td>';
    html += '<td><span class="badge bg-dark">'+(c.party_acronym||'')+'</span> '+(c.party_name||'')+'</td>';
    html += '<td class="fw-bold fs-5">'+new Intl.NumberFormat('es-EC').format(c.total_votes || c.votes || 0)+'</td></tr>';
    total += parseInt(c.total_votes || c.votes || 0);
  });
  if (json.total_voters > 0) {
    html += '<tr class="table-info"><td colspan="4" class="text-center fw-bold py-2">';
    html += '<span class="me-4"><i class="bi bi-file-text me-1"></i>Blancos: <strong>'+ (json.total_blank||0) +'</strong></span>';
    html += '<span class="me-4"><i class="bi bi-file-x me-1"></i>Nulos: <strong>'+ (json.total_null||0) +'</strong></span>';
    html += '<span><i class="bi bi-people me-1"></i>Sufragantes: <strong>'+ (json.total_voters||0) +'</strong></span>';
    html += '</td></tr>';
  }
  html += '<tr class="table-primary fw-bold"><td colspan="3">Total Votos Válidos</td><td>'+new Intl.NumberFormat('es-EC').format(total)+'</td></tr>';
  html += '</tbody></table></div></div>';
  if (data.length) {
    var labels = [], values = [], colors = [];
    data.forEach(c => {
      labels.push((c.first_name||'').split(' ')[0] + ' ' + (c.last_name||'').split(' ')[0]);
      values.push(parseInt(c.total_votes || c.votes || 0));
      colors.push(c.color || '#0d6efd');
    });
    html += '<div class="card mt-2"><div class="card-header"><i class="bi bi-bar-chart-fill me-2"></i>Distribución de Votos</div><div class="card-body"><canvas id="resultsChart" height="220"></canvas></div></div>';
    document.getElementById('resultsContainer').innerHTML = html;
    if (window.myChart) window.myChart.destroy();
    var ctx = document.getElementById('resultsChart').getContext('2d');
    var gradColors = colors.map(c => { var g = ctx.createLinearGradient(0,0,0,200); g.addColorStop(0,c); g.addColorStop(1,c+'88'); return g; });
    window.myChart = new Chart(ctx, {
      type: 'bar',
      data: { labels: labels, datasets: [{ label: 'Votos', data: values, backgroundColor: gradColors, borderColor: colors, borderWidth: 2, borderRadius: 4 }] },
      options: {
        responsive: true, animation: { duration: 800, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: function(c) { return c.raw + ' votos'; } } }
        },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#e9ecef' } },
          x: { ticks: { font: { size: 11, weight: 'bold' } }, grid: { display: false } }
        },
        layout: { padding: { top: 10 } }
      }
    });
  } else {
    html = '<div class="card mt-3"><div class="card-body text-center text-muted py-4">No hay resultados registrados</div></div>';
    document.getElementById('resultsContainer').innerHTML = html;
  }
}
</script>
<?= $this->endSection() ?>
