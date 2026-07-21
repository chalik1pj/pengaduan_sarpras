<?= $this->extend('layouts/petugas') ?>
<?= $this->section('content') ?>

<!-- Stats Cards -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem">
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">📋</div>
    <div class="stat-value"><?= $totalDitugaskan ?></div>
    <div class="stat-label">Total Ditugaskan</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">🔧</div>
    <div class="stat-value"><?= $totalDiproses ?></div>
    <div class="stat-label">Sedang Diproses</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#059669)">✅</div>
    <div class="stat-value"><?= $totalSelesai ?></div>
    <div class="stat-label">Selesai</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)">📝</div>
    <div class="stat-value"><?= $totalLaporan ?></div>
    <div class="stat-label">Laporan Dikirim</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,#ef4444,#b91c1c)">🔍</div>
    <div class="stat-value"><?= $totalInspeksi ?></div>
    <div class="stat-label">Inspeksi Dilakukan</div>
  </div>
</div>

<!-- Pengaduan Terbaru Ditugaskan -->
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
    <h4>📋 Pengaduan Terbaru Yang Ditugaskan</h4>
    <a href="<?= base_url('petugas/pengaduan') ?>" class="btn btn-ghost btn-sm">Lihat Semua →</a>
  </div>
  <div class="card-body" style="padding:0">
    <?php if (empty($pengaduanDitugaskan)): ?>
    <div style="padding:2rem;text-align:center;color:var(--text-muted)">
      <div style="font-size:2.5rem;margin-bottom:0.5rem">📭</div>
      <p>Belum ada pengaduan yang ditugaskan kepada Anda.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Kode</th>
            <th>Judul</th>
            <th>Lokasi</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $pCls = ['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
          $sCls = ['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
          foreach ($pengaduanDitugaskan as $item): ?>
          <tr>
            <td><strong class="text-xs text-primary-color"><?= esc($item['kode_pengaduan']) ?></strong></td>
            <td style="max-width:200px;vertical-align:middle"><strong class="truncate"><?= esc($item['judul']) ?></strong></td>
            <td class="text-sm"><?= esc($item['nama_gedung']) ?><br><span class="text-muted text-xs"><?= esc($item['nama_ruangan']) ?></span></td>
            <td><span class="badge <?= $pCls[$item['prioritas']] ?? '' ?>"><?= ucfirst($item['prioritas']) ?></span></td>
            <td><span class="badge <?= $sCls[$item['nama_status']] ?? '' ?>"><?= esc($item['nama_status']) ?></span></td>
            <td>
              <div class="table-actions">
                <a href="<?= base_url('petugas/pengaduan/'.$item['id_pengaduan']) ?>" class="btn btn-ghost btn-sm">Detail →</a>
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

<!-- Quick Actions -->
<div class="grid grid-2" style="gap:1rem;margin-top:1.5rem">
  <a href="<?= base_url('petugas/pengaduan') ?>" class="card" style="display:block;text-decoration:none;transition:transform 0.2s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
    <div class="card-body" style="display:flex;align-items:center;gap:1rem">
      <div style="font-size:2.5rem">📋</div>
      <div>
        <div class="font-semibold">Kelola Pengaduan</div>
        <div class="text-sm text-muted">Lihat & kirim laporan progres</div>
      </div>
    </div>
  </a>
  <a href="<?= base_url('petugas/inspeksi/buat') ?>" class="card" style="display:block;text-decoration:none;transition:transform 0.2s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
    <div class="card-body" style="display:flex;align-items:center;gap:1rem">
      <div style="font-size:2.5rem">🔍</div>
      <div>
        <div class="font-semibold">Buat Inspeksi Fasilitas</div>
        <div class="text-sm text-muted">Laporkan kondisi ruangan/barang</div>
      </div>
    </div>
  </a>
</div>

<?= $this->endSection() ?>
