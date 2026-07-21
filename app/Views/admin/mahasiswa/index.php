<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-4">
  <div><h2>Data Mahasiswa</h2><p class="text-muted text-sm"><?= count($items) ?> mahasiswa terdaftar</p></div>
</div>

<div class="card mb-4">
  <div class="card-body" style="padding:1rem">
    <form method="GET" class="filter-bar">
      <select name="status" class="form-control" style="width:auto" onchange="this.form.submit()">
        <option value="">-- Semua Status --</option>
        <option value="aktif" <?= $status==='aktif'?'selected':'' ?>>Aktif</option>
        <option value="nonaktif" <?= $status==='nonaktif'?'selected':'' ?>>Nonaktif</option>
      </select>
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Cari nama/NIM/email...">
      </div>
      <button type="submit" class="btn btn-outline btn-sm">🔍</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>NIM</th><th>Nama</th><th>Email</th><th>Program Studi</th><th>Angkatan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($items as $i => $item): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong class="text-primary-color"><?= esc($item['nim']) ?></strong></td>
        <td><?= esc($item['nama']) ?></td>
        <td class="text-sm text-muted"><?= esc($item['email']) ?></td>
        <td class="text-sm"><?= esc($item['program_studi']??'-') ?></td>
        <td><?= esc($item['angkatan']??'-') ?></td>
        <td><span class="badge <?= $item['status_akun']==='aktif'?'badge-aktif':'badge-nonaktif' ?>"><?= esc($item['status_akun']) ?></span></td>
        <td>
          <form method="POST" action="<?= base_url('admin/mahasiswa/toggle/'.$item['nim']) ?>" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm <?= $item['status_akun']==='aktif'?'btn-danger':'btn-accent' ?>">
              <?= $item['status_akun']==='aktif'?'🚫 Nonaktifkan':'✅ Aktifkan' ?>
            </button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
