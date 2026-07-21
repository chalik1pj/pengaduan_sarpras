<?= $this->extend('layouts/petugas') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6" style="flex-wrap:wrap">
  <h2>🔍 Inspeksi Fasilitas</h2>
  <a href="<?= base_url('petugas/inspeksi/buat') ?>" class="btn btn-primary btn-sm">➕ Buat Inspeksi Baru</a>
</div>

<?php if (empty($inspeksi)): ?>
<div class="card">
  <div class="card-body" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:0.75rem">🔍</div>
    <h4>Belum ada inspeksi</h4>
    <p class="text-sm text-muted">Mulai laporkan kondisi fasilitas di ruangan kampus.</p>
    <a href="<?= base_url('petugas/inspeksi/buat') ?>" class="btn btn-primary" style="margin-top:1rem">➕ Buat Inspeksi Pertama</a>
  </div>
</div>
<?php else: ?>
<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem">
  <?php
  $kondisiCls = ['baik'=>'badge-selesai','perlu_perbaikan'=>'badge-sedang','rusak_berat'=>'badge-ditolak'];
  $kondisiIcon = ['baik'=>'✅','perlu_perbaikan'=>'⚠️','rusak_berat'=>'🔴'];
  $kondisiLabel = ['baik'=>'Baik','perlu_perbaikan'=>'Perlu Perbaikan','rusak_berat'=>'Rusak Berat'];
  foreach ($inspeksi as $item): ?>
  <div class="card" style="display:flex;flex-direction:column">
    <?php if (!empty($item['fotos'])): ?>
    <img src="<?= base_url($item['fotos'][0]['path_file']) ?>" alt="Foto inspeksi"
      style="width:100%;height:180px;object-fit:cover;border-radius:var(--radius-lg) var(--radius-lg) 0 0">
    <?php else: ?>
    <div style="width:100%;height:120px;background:var(--bg-input);border-radius:var(--radius-lg) var(--radius-lg) 0 0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--text-muted)">🏢</div>
    <?php endif; ?>
    <div class="card-body" style="flex:1">
      <div style="display:flex;justify-content:space-between;align-items:start;gap:0.5rem;margin-bottom:0.5rem;flex-wrap:wrap">
        <span class="badge <?= $kondisiCls[$item['kondisi_temuan']] ?? '' ?>">
          <?= $kondisiIcon[$item['kondisi_temuan']] ?? '' ?> <?= $kondisiLabel[$item['kondisi_temuan']] ?? '' ?>
        </span>
        <?php if ($item['diverifikasi']): ?>
        <span class="badge badge-selesai text-xs">✅ Terverifikasi</span>
        <?php else: ?>
        <span class="badge badge-menunggu text-xs">⏳ Menunggu</span>
        <?php endif; ?>
      </div>
      <h4 style="font-size:0.95rem;margin-bottom:0.25rem"><?= esc($item['judul_inspeksi']) ?></h4>
      <p class="text-xs text-muted"><?= esc($item['nama_gedung']) ?> – <?= esc($item['nama_ruangan']) ?></p>
      <?php if ($item['nama_barang']): ?>
      <p class="text-xs text-muted">Barang: <?= esc($item['nama_barang']) ?></p>
      <?php endif; ?>
      <p class="text-xs text-muted" style="margin-top:0.25rem"><?= date('d M Y H:i', strtotime($item['created_at'])) ?></p>
    </div>
    <div class="card-body" style="padding-top:0;border-top:1px solid var(--border)">
      <a href="<?= base_url('petugas/inspeksi/'.$item['id_inspeksi']) ?>" class="btn btn-ghost btn-sm btn-block">Lihat Detail →</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
