<?= $this->extend('layouts/mahasiswa') ?>
<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
  <div>
    <h2>Riwayat Pengaduan</h2>
    <p class="text-muted text-sm">Semua pengaduan yang pernah Anda buat</p>
  </div>
  <a href="<?= base_url('mahasiswa/pengaduan/buat') ?>" class="btn btn-primary">➕ Buat Pengaduan</a>
</div>

<?php if (empty($items)): ?>
<div class="card">
  <div class="empty-state">
    <div class="empty-icon">📭</div>
    <h3>Belum ada pengaduan</h3>
    <p>Belum ada pengaduan yang Anda buat</p>
    <a href="<?= base_url('mahasiswa/pengaduan/buat') ?>" class="btn btn-primary">➕ Buat Pengaduan Pertama</a>
  </div>
</div>
<?php else: ?>
<div class="card">
  <div class="card-body" style="padding-top:0.5rem;padding-bottom:0.5rem">
    <div class="filter-bar">
      <span class="text-sm text-muted"><?= count($items) ?> pengaduan ditemukan</span>
      <div class="search-box ms-auto">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" id="searchInput" placeholder="Cari pengaduan..." oninput="initTableSearch('searchInput','pengaduanTable')">
      </div>
    </div>
  </div>
  <div class="table-wrap">
    <table id="pengaduanTable">
      <thead><tr><th>Kode</th><th>Judul</th><th>Lokasi</th><th>Kategori</th><th>Prioritas</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($items as $item): ?>
      <?php
        $sCls = ['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
        $pCls = ['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
      ?>
      <tr>
        <td><strong class="text-xs text-primary-color"><?= esc($item['kode_pengaduan']) ?></strong></td>
        <td style="max-width:200px"><strong class="truncate"><?= esc($item['judul']) ?></strong></td>
        <td class="text-sm"><?= esc($item['nama_gedung']) ?><br><span class="text-xs text-muted"><?= esc($item['nama_ruangan']) ?></span></td>
        <td class="text-sm"><?= esc($item['nama_kategori']) ?></td>
        <td><span class="badge <?= $pCls[$item['prioritas']]??'' ?>"><?= ucfirst($item['prioritas']) ?></span></td>
        <td><span class="badge <?= $sCls[$item['nama_status']]??'' ?>"><?= esc($item['nama_status']) ?></span></td>
        <td class="text-xs text-muted"><?= date('d M Y', strtotime($item['tanggal_pengaduan'])) ?></td>
        <td style="vertical-align: middle;">
          <div class="table-actions">
            <a href="<?= base_url('mahasiswa/pengaduan/'.$item['id_pengaduan']) ?>" class="btn btn-outline btn-sm">Detail</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
