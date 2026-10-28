<?= $this->extend('layouts/master') ?>
<?= $this->section('content') ?>
<div class="content-header"><h1>División Territorial</h1></div>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Provincias</div>
      <div class="card-body">
        <form method="post" action="<?= base_url('territorial/save-province') ?>" class="row g-2 mb-3">
          <?= csrf_field() ?>
          <div class="col-4"><input type="text" name="code" class="form-control form-control-sm" placeholder="Código" required></div>
          <div class="col-6"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nombre" required></div>
          <div class="col-2"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus"></i></button></div>
        </form>
        <div style="max-height:400px;overflow-y:auto">
          <table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr><th>Código</th><th>Provincia</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($provinces as $p): ?>
              <tr>
                <td><code><?= esc($p->code) ?></code></td>
                <td><strong><?= esc($p->name) ?></strong></td>
                <td><button class="btn btn-sm btn-outline-danger py-0" onclick="delProvince(<?= $p->id ?>)"><i class="bi bi-x"></i></button></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Cantones</div>
      <div class="card-body">
        <form method="post" action="<?= base_url('territorial/save-canton') ?>" class="row g-2 mb-3">
          <?= csrf_field() ?>
          <div class="col-4">
            <select name="province_id" class="form-select form-select-sm" required>
              <option value="">Provincia</option>
              <?php foreach ($provinces as $p): ?><option value="<?= $p->id ?>"><?= esc($p->name) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-3"><input type="text" name="code" class="form-control form-control-sm" placeholder="Código" required></div>
          <div class="col-3"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nombre" required></div>
          <div class="col-2"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus"></i></button></div>
        </form>
        <div style="max-height:400px;overflow-y:auto">
          <table class="table table-sm table-hover mb-0 data-table">
            <thead class="table-light"><tr><th>Provincia</th><th>Código</th><th>Cantón</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($cantons as $c): ?>
              <tr>
                <td><small><?= esc($c->province_name) ?></small></td>
                <td><code><?= esc($c->code) ?></code></td>
                <td><?= esc($c->name) ?></td>
                <td><button class="btn btn-sm btn-outline-danger py-0" onclick="delCanton(<?= $c->id ?>)"><i class="bi bi-x"></i></button></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
function delProvince(id) { confirmDelete().then(r => { if(r.isConfirmed) window.location='<?= base_url('territorial/delete-province') ?>/'+id; }); }
function delCanton(id) { confirmDelete().then(r => { if(r.isConfirmed) window.location='<?= base_url('territorial/delete-canton') ?>/'+id; }); }
</script>
<?= $this->endSection() ?>
