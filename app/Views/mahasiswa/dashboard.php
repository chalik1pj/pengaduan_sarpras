<?= $this->extend('layouts/mahasiswa') ?>
<?= $this->section('content') ?>

<!-- Hero Welcome Card -->
<div class="dashboard-hero">
  <h2>Selamat Datang, <?= esc(session()->get('mahasiswa_nama')) ?>! 👋</h2>
  <p>NIM: <?= esc(session()->get('mahasiswa_nim')) ?> &nbsp;|&nbsp; <?= esc(session()->get('mahasiswa_prodi')) ?></p>
</div>

<!-- Stat Cards -->
<div class="grid grid-4 mb-6">
  <?php
  $statList = [
    ['label'=>'Total Pengaduan','val'=>$total,'icon'=>'📋','cls'=>'icon-primary'],
    ['label'=>'Menunggu','val'=>$stats[1]??0,'icon'=>'⏳','cls'=>'icon-warning'],
    ['label'=>'Diproses','val'=>($stats[2]??0)+($stats[3]??0),'icon'=>'🔧','cls'=>'icon-info'],
    ['label'=>'Selesai','val'=>$stats[4]??0,'icon'=>'✅','cls'=>'icon-accent'],
  ];
  foreach ($statList as $s):
  ?>
  <div class="stat-card">
    <div class="stat-icon <?= $s['cls'] ?>"><?= $s['icon'] ?></div>
    <div class="stat-value"><?= $s['val'] ?></div>
    <div class="stat-label"><?= $s['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Recent Complaints -->
<div class="card">
  <div class="card-header">
    <h3 class="text-sm font-semibold">📋 Pengaduan Terbaru</h3>
    <a href="<?= base_url('mahasiswa/pengaduan') ?>" class="btn btn-outline btn-sm">Lihat Semua</a>
  </div>
  <?php if (empty($recent)): ?>
  <div class="card-body">
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <h3>Belum ada pengaduan</h3>
      <p>Laporkan masalah fasilitas yang Anda temukan di kampus</p>
      <a href="<?= base_url('mahasiswa/pengaduan/buat') ?>" class="btn btn-primary">➕ Buat Pengaduan Pertama</a>
    </div>
  </div>
  <?php else: ?>
  <div class="table-wrap">
    <table id="recentTable">
      <thead><tr><th>Kode</th><th>Judul</th><th>Lokasi</th><th>Prioritas</th><th>Status</th><th>Tanggal</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($recent as $item): ?>
      <tr>
        <td><strong class="text-xs text-primary-color"><?= esc($item['kode_pengaduan']) ?></strong></td>
        <td style="max-width:200px; vertical-align: middle;"><strong class="truncate"><?= esc($item['judul']) ?></strong></td>
        <td class="text-sm"><?= esc($item['nama_gedung']) ?><br><span class="text-muted text-xs"><?= esc($item['nama_ruangan']) ?></span></td>
        <td><?php
          $pCls = ['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
          echo '<span class="badge '.($pCls[$item['prioritas']]??'').'">'.ucfirst($item['prioritas']).'</span>';
        ?></td>
        <td><?php
          $sCls = ['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
          echo '<span class="badge '.($sCls[$item['nama_status']]??'').'">'.esc($item['nama_status']).'</span>';
        ?></td>
        <td class="text-xs text-muted"><?= date('d M Y', strtotime($item['tanggal_pengaduan'])) ?></td>
        <td><a href="<?= base_url('mahasiswa/pengaduan/'.$item['id_pengaduan']) ?>" class="btn btn-ghost btn-sm">Detail →</a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<div class="mt-auto" style="margin-top:2rem;text-align:center">
  <a href="<?= base_url('mahasiswa/pengaduan/buat') ?>" class="btn btn-primary btn-lg">
    ➕ Buat Pengaduan Baru
  </a>
</div>

<?= $this->endSection() ?>
