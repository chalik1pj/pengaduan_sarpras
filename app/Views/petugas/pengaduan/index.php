<?= $this->extend('layouts/petugas') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6" style="flex-wrap:wrap">
  <h2>📋 Pengaduan Saya</h2>
  <span class="badge badge-proses"><?= count($pengaduan) ?> pengaduan</span>
</div>

<!-- Filter -->
<div class="card mb-4">
  <div class="card-body" style="padding:1rem">
    <form method="GET" class="filter-bar flex-wrap">
      <select name="status" class="form-control" style="width:auto" onchange="this.form.submit()">
        <option value="">-- Semua Status --</option>
        <option value="2" <?= $filterStatus=='2'?'selected':'' ?>>Diverifikasi</option>
        <option value="3" <?= $filterStatus=='3'?'selected':'' ?>>Diproses</option>
        <option value="4" <?= $filterStatus=='4'?'selected':'' ?>>Selesai</option>
      </select>
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Cari judul pengaduan...">
      </div>
      <button type="submit" class="btn btn-outline btn-sm">🔍 Cari</button>
      <a href="<?= base_url('petugas/pengaduan') ?>" class="btn btn-ghost btn-sm">✕ Reset</a>
    </form>
  </div>
</div>

<!-- Tabel Pengaduan -->
<div class="card">
  <div class="card-body" style="padding:0">
    <?php if (empty($pengaduan)): ?>
    <div style="padding:3rem;text-align:center;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:0.75rem">📭</div>
      <h4>Tidak ada pengaduan ditugaskan</h4>
      <p class="text-sm">Belum ada pengaduan yang ditugaskan kepada Anda.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table" id="tblPengaduan">
        <thead>
          <tr>
            <th>Kode</th>
            <th>Judul</th>
            <th>Lokasi</th>
            <th>Mahasiswa</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $pCls = ['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
          $sCls = ['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
          foreach ($pengaduan as $item): ?>
          <tr>
            <td><strong class="text-xs text-primary-color"><?= esc($item['kode_pengaduan']) ?></strong></td>
            <td style="max-width:200px;vertical-align:middle"><strong class="truncate"><?= esc($item['judul']) ?></strong></td>
            <td class="text-sm"><?= esc($item['nama_gedung']) ?><br><span class="text-muted text-xs"><?= esc($item['nama_ruangan']) ?></span></td>
            <td class="text-sm"><?= esc($item['nama_mahasiswa']) ?></td>
            <td><span class="badge <?= $pCls[$item['prioritas']] ?? '' ?>"><?= ucfirst($item['prioritas']) ?></span></td>
            <td><span class="badge <?= $sCls[$item['nama_status']] ?? '' ?>"><?= esc($item['nama_status']) ?></span></td>
            <td class="text-xs text-muted"><?= date('d M Y', strtotime($item['tanggal_pengaduan'])) ?></td>
            <td>
              <div class="table-actions">
                <a href="<?= base_url('petugas/pengaduan/'.$item['id_pengaduan']) ?>" class="btn btn-primary btn-sm">📝 Laporan</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
