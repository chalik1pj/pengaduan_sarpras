<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6">
  <a href="<?= base_url('admin/laporan') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <h2>📝 Detail Laporan Petugas</h2>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr;gap:1.5rem;align-items:start">
  <div>
    <div class="card mb-4">
      <div class="card-header"><h4>📋 Info Laporan</h4></div>
      <div class="card-body">
        <div class="grid grid-2" style="gap:0.75rem;margin-bottom:1rem">
          <?php
          $details = [
            ['Pengaduan',      $laporan['kode_pengaduan'].' — '.$laporan['judul_pengaduan']],
            ['Petugas',        $laporan['nama_petugas'].($laporan['jabatan'] ? ' ('.$laporan['jabatan'].')' : '')],
            ['Status Laporan', ucfirst($laporan['status_laporan'])],
            ['Tanggal',        date('d M Y H:i', strtotime($laporan['created_at']))],
          ];
          foreach ($details as [$k,$v]): ?>
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted"><?= $k ?></div>
            <div class="font-semibold text-sm" style="margin-top:0.25rem"><?= esc($v) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="padding:1rem;background:var(--bg-input);border-radius:var(--radius-md)">
          <div class="text-xs text-muted mb-2">Judul Laporan:</div>
          <div class="font-semibold"><?= esc($laporan['judul_laporan']) ?></div>
        </div>
        <div style="padding:1rem;background:var(--bg-input);border-radius:var(--radius-md);margin-top:0.75rem">
          <div class="text-xs text-muted mb-2">Deskripsi Progres:</div>
          <p style="font-size:0.875rem;line-height:1.7"><?= nl2br(esc($laporan['deskripsi'])) ?></p>
        </div>
      </div>
    </div>

    <?php if (!empty($laporan['fotos'])): ?>
    <div class="card">
      <div class="card-header"><h4>📸 Foto Bukti (<?= count($laporan['fotos']) ?>)</h4></div>
      <div class="card-body">
        <div class="photo-gallery">
          <?php foreach ($laporan['fotos'] as $foto): ?>
          <div class="photo-item" data-src="<?= base_url($foto['path_file']) ?>">
            <img src="<?= base_url($foto['path_file']) ?>" alt="Foto laporan">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><h4>✅ Verifikasi</h4></div>
      <div class="card-body">
        <form action="<?= base_url('admin/laporan/'.$laporan['id_laporan'].'/verifikasi') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Catatan Verifikasi</label>
            <textarea name="catatan_verif" class="form-control" rows="4"
              placeholder="Catatan atau instruksi tindak lanjut..."><?= esc($laporan['catatan_verif'] ?? '') ?></textarea>
          </div>
          <input type="hidden" name="diverifikasi" value="1">
          <?php if (!$laporan['diverifikasi']): ?>
          <button type="submit" class="btn btn-primary btn-block">✅ Verifikasi & Terima</button>
          <button type="button" onclick="this.form.elements['diverifikasi'].value='0';this.form.submit()"
            class="btn btn-ghost btn-block" style="margin-top:0.5rem">↩ Tolak / Reset</button>
          <?php else: ?>
          <div style="padding:1rem;background:var(--success-light);border-radius:var(--radius-md);text-align:center;margin-bottom:1rem">
            <div style="font-size:2rem">✅</div>
            <div class="font-semibold" style="color:#059669">Sudah Terverifikasi</div>
            <div class="text-xs text-muted">Oleh: <?= esc($laporan['nama_verifikator'] ?? '-') ?></div>
          </div>
          <button type="button" onclick="this.form.elements['diverifikasi'].value='0';this.form.submit()"
            class="btn btn-ghost btn-block">↩ Batalkan Verifikasi</button>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="lightbox" id="lightbox">
  <button class="lightbox-close">✕</button>
  <img src="" id="lightboxImg" alt="Preview">
</div>

<?= $this->endSection() ?>
