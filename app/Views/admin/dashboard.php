<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!-- include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

<!-- Stat Cards -->
<div class="grid grid-4 mb-6">
  <?php $statCards = [
    ['label'=>'Total Pengaduan','val'=>$stats['total'],'icon'=>'📋','cls'=>'icon-primary'],
    ['label'=>'Menunggu','val'=>$stats['menunggu'],'icon'=>'⏳','cls'=>'icon-warning'],
    ['label'=>'Sedang Diproses','val'=>$stats['diproses'],'icon'=>'🔧','cls'=>'icon-info'],
    ['label'=>'Selesai','val'=>$stats['selesai'],'icon'=>'✅','cls'=>'icon-accent'],
  ]; ?>
  <?php foreach ($statCards as $card): ?>
  <div class="stat-card">
    <div class="stat-icon <?= $card['cls'] ?>"><?= $card['icon'] ?></div>
    <div class="stat-value"><?= $card['val'] ?></div>
    <div class="stat-label"><?= $card['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Additional stats row -->
<div class="grid grid-3 mb-6">
  <div class="stat-card">
    <div class="stat-icon icon-danger">❌</div>
    <div class="stat-value"><?= $stats['ditolak'] ?></div>
    <div class="stat-label">Ditolak</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon icon-primary">👥</div>
    <div class="stat-value"><?= $totalMhs ?></div>
    <div class="stat-label">Total Mahasiswa</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon icon-accent">👮</div>
    <div class="stat-value"><?= $totalPetugas ?></div>
    <div class="stat-label">Petugas Aktif</div>
  </div>
</div>

<div class="grid grid-2 mb-6" style="gap:1.5rem">
  <!-- Chart Per Gedung -->
  <div class="card">
    <div class="card-header"><h4>🏢 Pengaduan per Gedung</h4></div>
    <div class="card-body" style="height:250px">
      <canvas id="chartGedung"></canvas>
    </div>
  </div>
  <!-- Chart Status Donut -->
  <div class="card">
    <div class="card-header"><h4>📊 Status Pengaduan</h4></div>
    <div class="card-body" style="height:250px">
      <canvas id="chartStatus"></canvas>
    </div>
  </div>
</div>

<!-- Recent -->
<div class="card">
  <div class="card-header">
    <h4>📋 Pengaduan Terbaru</h4>
    <a href="<?= base_url('admin/pengaduan') ?>" class="btn btn-outline btn-sm">Lihat Semua</a>
  </div>
  <?php if (empty($recent)): ?>
  <div class="empty-state"><div class="empty-icon">📭</div><p>Belum ada pengaduan</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Mahasiswa</th><th>Judul</th><th>Lokasi</th><th>Prioritas</th><th>Status</th><th>Tanggal</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($recent as $item):
        $sCls=['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
        $pCls=['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
      ?>
      <tr>
        <td><strong class="text-xs text-primary-color"><?= esc($item['kode_pengaduan']) ?></strong></td>
        <td class="text-sm"><?= esc($item['nama_mahasiswa']) ?><br><span class="text-xs text-muted"><?= esc($item['nim']) ?></span></td>
        <td style="max-width:180px"><strong class="truncate"><?= esc($item['judul']) ?></strong></td>
        <td class="text-sm"><?= esc($item['nama_gedung']) ?><br><span class="text-xs text-muted"><?= esc($item['nama_ruangan']) ?></span></td>
        <td><span class="badge <?= $pCls[$item['prioritas']]??'' ?>"><?= ucfirst($item['prioritas']) ?></span></td>
        <td><span class="badge <?= $sCls[$item['nama_status']]??'' ?>"><?= esc($item['nama_status']) ?></span></td>
        <td class="text-xs text-muted"><?= date('d M Y', strtotime($item['tanggal_pengaduan'])) ?></td>
        <td><a href="<?= base_url('admin/pengaduan/'.$item['id_pengaduan']) ?>" class="btn btn-ghost btn-sm">Detail</a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Bar chart per gedung
  const gedungLabels = <?= json_encode(array_column($byGedung,'nama_gedung')) ?>;
  const gedungData   = <?= json_encode(array_column($byGedung,'total')) ?>;
  createBarChart('chartGedung', gedungLabels, gedungData, 'Pengaduan');

  // Donut status
  const statusLabels = ['Menunggu','Diverifikasi','Diproses','Selesai','Ditolak'];
  const statusData   = [<?= $stats['menunggu'] ?>,<?= $stats['diverifikasi'] ?>,<?= $stats['diproses'] ?>,<?= $stats['selesai'] ?>,<?= $stats['ditolak'] ?>];
  createDonutChart('chartStatus', statusLabels, statusData);
});
</script>
<?= $this->endSection() ?>
