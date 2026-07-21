<?= $this->extend('layouts/petugas') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6">
  <a href="<?= base_url('petugas/inspeksi') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <div>
    <h2><?= esc($inspeksi['judul_inspeksi']) ?></h2>
    <p class="text-muted text-sm"><?= esc($inspeksi['nama_gedung']) ?> — <?= esc($inspeksi['nama_ruangan']) ?> (Lt.<?= $inspeksi['lantai'] ?>)</p>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr;gap:1.5rem;align-items:start">
  <div>
    <!-- Detail Inspeksi -->
    <div class="card mb-4">
      <div class="card-header"><h4>🔍 Detail Temuan</h4></div>
      <div class="card-body">
        <?php
        $kondisiCls   = ['baik'=>'badge-selesai','perlu_perbaikan'=>'badge-sedang','rusak_berat'=>'badge-ditolak'];
        $kondisiLabel = ['baik'=>'✅ Baik','perlu_perbaikan'=>'⚠️ Perlu Perbaikan','rusak_berat'=>'🔴 Rusak Berat'];
        ?>
        <div class="grid grid-2" style="gap:0.75rem;margin-bottom:1rem">
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted">Petugas</div>
            <div class="font-semibold text-sm" style="margin-top:0.25rem"><?= esc($inspeksi['nama_petugas']) ?></div>
          </div>
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted">Barang / Objek</div>
            <div class="font-semibold text-sm" style="margin-top:0.25rem"><?= esc($inspeksi['nama_barang'] ?? 'Umum Ruangan') ?></div>
          </div>
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted">Kondisi Temuan</div>
            <div style="margin-top:0.25rem"><span class="badge <?= $kondisiCls[$inspeksi['kondisi_temuan']] ?? '' ?>"><?= $kondisiLabel[$inspeksi['kondisi_temuan']] ?? '' ?></span></div>
          </div>
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted">Tanggal Inspeksi</div>
            <div class="font-semibold text-sm" style="margin-top:0.25rem"><?= date('d M Y H:i', strtotime($inspeksi['created_at'])) ?></div>
          </div>
        </div>
        <div style="padding:1rem;background:var(--bg-input);border-radius:var(--radius-md);margin-bottom:0.75rem">
          <div class="text-xs text-muted mb-2">Deskripsi Temuan:</div>
          <p style="font-size:0.875rem;line-height:1.7"><?= nl2br(esc($inspeksi['deskripsi'])) ?></p>
        </div>
        <?php if ($inspeksi['rekomendasi']): ?>
        <div style="padding:1rem;background:var(--info-light, #eff6ff);border-radius:var(--radius-md)">
          <div class="text-xs text-muted mb-2">💡 Rekomendasi Tindak Lanjut:</div>
          <p style="font-size:0.875rem;line-height:1.7"><?= nl2br(esc($inspeksi['rekomendasi'])) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Foto -->
    <?php if (!empty($fotos)): ?>
    <div class="card">
      <div class="card-header"><h4>📸 Foto Kondisi (<?= count($fotos) ?>)</h4></div>
      <div class="card-body">
        <div class="photo-gallery">
          <?php foreach ($fotos as $foto): ?>
          <div class="photo-item" data-src="<?= base_url($foto['path_file']) ?>">
            <img src="<?= base_url($foto['path_file']) ?>" alt="Foto inspeksi">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Status Verifikasi -->
  <div>
    <div class="card">
      <div class="card-header"><h4>📋 Status Verifikasi</h4></div>
      <div class="card-body">
        <?php if ($inspeksi['diverifikasi']): ?>
        <div style="text-align:center;padding:1rem">
          <div style="font-size:3rem;margin-bottom:0.5rem">✅</div>
          <div class="font-semibold" style="color:#059669">Inspeksi Terverifikasi</div>
          <div class="text-xs text-muted" style="margin-top:0.25rem">
            Oleh: <?= esc($inspeksi['nama_verifikator'] ?? '-') ?><br>
            <?= $inspeksi['waktu_verif'] ? date('d M Y H:i', strtotime($inspeksi['waktu_verif'])) : '' ?>
          </div>
          <?php if ($inspeksi['catatan_verif']): ?>
          <div style="margin-top:1rem;padding:0.75rem;background:var(--success-light);border-radius:var(--radius-md);text-align:left">
            <div class="text-xs font-semibold" style="color:#059669;margin-bottom:0.25rem">Catatan Admin:</div>
            <p class="text-sm"><?= nl2br(esc($inspeksi['catatan_verif'])) ?></p>
          </div>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:1rem">
          <div style="font-size:3rem;margin-bottom:0.5rem">⏳</div>
          <div class="font-semibold text-muted">Menunggu Verifikasi Admin</div>
          <p class="text-xs text-muted" style="margin-top:0.5rem">Admin akan segera meninjau laporan inspeksi Anda.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
  <button class="lightbox-close">✕</button>
  <img src="" id="lightboxImg" alt="Preview">
</div>

<?= $this->endSection() ?>
