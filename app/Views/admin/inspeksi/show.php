<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6">
  <a href="<?= base_url('admin/inspeksi') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <div>
    <h2><?= esc($inspeksi['judul_inspeksi']) ?></h2>
    <p class="text-muted text-sm"><?= esc($inspeksi['nama_gedung']) ?> — <?= esc($inspeksi['nama_ruangan']) ?> (Lt.<?= $inspeksi['lantai'] ?>)</p>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr;gap:1.5rem;align-items:start">
  <div>
    <div class="card mb-4">
      <div class="card-header"><h4>🔍 Detail Temuan</h4></div>
      <div class="card-body">
        <?php
        $kondisiCls   = ['baik'=>'badge-selesai','perlu_perbaikan'=>'badge-sedang','rusak_berat'=>'badge-ditolak'];
        $kondisiLabel = ['baik'=>'✅ Baik','perlu_perbaikan'=>'⚠️ Perlu Perbaikan','rusak_berat'=>'🔴 Rusak Berat'];
        ?>
        <div class="grid grid-2" style="gap:0.75rem;margin-bottom:1rem">
          <?php
          $details = [
            ['Petugas', $inspeksi['nama_petugas'].($inspeksi['jabatan'] ? ' ('.$inspeksi['jabatan'].')' : '')],
            ['Barang', $inspeksi['nama_barang'] ?? 'Umum Ruangan'],
            ['Tanggal', date('d M Y H:i', strtotime($inspeksi['created_at']))],
          ];
          foreach ($details as [$k,$v]): ?>
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted"><?= $k ?></div>
            <div class="font-semibold text-sm" style="margin-top:0.25rem"><?= esc($v) ?></div>
          </div>
          <?php endforeach; ?>
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted">Kondisi</div>
            <div style="margin-top:0.25rem">
              <span class="badge <?= $kondisiCls[$inspeksi['kondisi_temuan']] ?? '' ?>">
                <?= $kondisiLabel[$inspeksi['kondisi_temuan']] ?? '' ?>
              </span>
            </div>
          </div>
        </div>
        <div style="padding:1rem;background:var(--bg-input);border-radius:var(--radius-md);margin-bottom:0.75rem">
          <div class="text-xs text-muted mb-2">Deskripsi Temuan:</div>
          <p style="font-size:0.875rem;line-height:1.7"><?= nl2br(esc($inspeksi['deskripsi'])) ?></p>
        </div>
        <?php if ($inspeksi['rekomendasi']): ?>
        <div style="padding:1rem;background:var(--info-light);border-radius:var(--radius-md)">
          <div class="text-xs text-muted mb-2">💡 Rekomendasi:</div>
          <p style="font-size:0.875rem;line-height:1.7"><?= nl2br(esc($inspeksi['rekomendasi'])) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>

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

  <div>
    <div class="card">
      <div class="card-header"><h4>✅ Verifikasi Inspeksi</h4></div>
      <div class="card-body">
        <form action="<?= base_url('admin/inspeksi/'.$inspeksi['id_inspeksi'].'/verifikasi') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Catatan Verifikasi</label>
            <textarea name="catatan_verif" class="form-control" rows="4"
              placeholder="Catatan atau instruksi tindak lanjut..."><?= esc($inspeksi['catatan_verif'] ?? '') ?></textarea>
          </div>
          <input type="hidden" name="diverifikasi" value="1">
          <?php if (!$inspeksi['diverifikasi']): ?>
          <button type="submit" class="btn btn-primary btn-block">✅ Verifikasi</button>
          <button type="button" onclick="this.form.elements['diverifikasi'].value='0';this.form.submit()"
            class="btn btn-ghost btn-block" style="margin-top:0.5rem">↩ Tolak</button>
          <?php else: ?>
          <div style="padding:1rem;background:var(--success-light);border-radius:var(--radius-md);text-align:center;margin-bottom:1rem">
            <div style="font-size:2rem">✅</div>
            <div class="font-semibold" style="color:#059669">Sudah Terverifikasi</div>
            <div class="text-xs text-muted">Oleh: <?= esc($inspeksi['nama_verifikator'] ?? '-') ?></div>
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
