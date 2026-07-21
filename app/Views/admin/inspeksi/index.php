<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6" style="flex-wrap:wrap">
  <h2>🔍 Inspeksi Fasilitas</h2>
  <?php if ($belumVerif > 0): ?>
  <span class="badge badge-tinggi"><?= $belumVerif ?> belum diverifikasi</span>
  <?php endif; ?>
</div>

<!-- Filter -->
<div class="card mb-4">
  <div class="card-body" style="padding:1rem">
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
      <a href="<?= base_url('admin/inspeksi') ?>" class="btn btn-sm <?= $filterVerif===''?'btn-primary':'btn-ghost' ?>">Semua</a>
      <a href="<?= base_url('admin/inspeksi?verif=0') ?>" class="btn btn-sm <?= $filterVerif==='0'?'btn-primary':'btn-ghost' ?>">⏳ Belum Diverifikasi</a>
      <a href="<?= base_url('admin/inspeksi?verif=1') ?>" class="btn btn-sm <?= $filterVerif==='1'?'btn-primary':'btn-ghost' ?>">✅ Sudah Diverifikasi</a>
      <a href="<?= base_url('admin/inspeksi?kondisi=rusak_berat') ?>" class="btn btn-sm <?= $filterKondisi==='rusak_berat'?'btn-danger':'btn-ghost' ?>">🔴 Rusak Berat</a>
      <a href="<?= base_url('admin/inspeksi?kondisi=perlu_perbaikan') ?>" class="btn btn-sm <?= $filterKondisi==='perlu_perbaikan'?'btn-outline':'btn-ghost' ?>">⚠️ Perlu Perbaikan</a>
    </div>
  </div>
</div>

<?php if (empty($inspeksi)): ?>
<div class="card">
  <div class="card-body" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:0.75rem">🔍</div>
    <h4>Belum ada laporan inspeksi</h4>
    <p class="text-sm text-muted">Petugas belum mengirimkan laporan inspeksi fasilitas apapun.</p>
  </div>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:1rem">
  <?php
  $kondisiCls   = ['baik'=>'badge-selesai','perlu_perbaikan'=>'badge-sedang','rusak_berat'=>'badge-ditolak'];
  $kondisiLabel = ['baik'=>'✅ Baik','perlu_perbaikan'=>'⚠️ Perlu Perbaikan','rusak_berat'=>'🔴 Rusak Berat'];
  foreach ($inspeksi as $item): ?>
  <div class="card">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:0.75rem;margin-bottom:0.75rem">
        <div>
          <h4 style="margin-bottom:0.25rem"><?= esc($item['judul_inspeksi']) ?></h4>
          <p class="text-xs text-muted">
            Lokasi: <strong><?= esc($item['nama_gedung']) ?> — <?= esc($item['nama_ruangan']) ?></strong>
            <?php if ($item['nama_barang']): ?> | Barang: <?= esc($item['nama_barang']) ?><?php endif; ?><br>
            Petugas: <strong><?= esc($item['nama_petugas']) ?></strong>
            <?php if ($item['jabatan']): ?>(<?= esc($item['jabatan']) ?>)<?php endif; ?><br>
            <?= date('d M Y H:i', strtotime($item['created_at'])) ?>
          </p>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
          <span class="badge <?= $kondisiCls[$item['kondisi_temuan']] ?? '' ?>"><?= $kondisiLabel[$item['kondisi_temuan']] ?? '' ?></span>
          <?php if ($item['diverifikasi']): ?>
          <span class="badge badge-selesai">✅ Terverifikasi</span>
          <?php else: ?>
          <span class="badge badge-menunggu">⏳ Menunggu Verifikasi</span>
          <?php endif; ?>
        </div>
      </div>

      <p class="text-sm" style="line-height:1.7;margin-bottom:0.75rem"><?= nl2br(esc($item['deskripsi'])) ?></p>

      <?php if ($item['rekomendasi']): ?>
      <div style="padding:0.75rem;background:var(--info-light,#eff6ff);border-radius:var(--radius-md);margin-bottom:0.75rem">
        <div class="text-xs font-semibold text-muted mb-1">💡 Rekomendasi Petugas:</div>
        <p class="text-sm"><?= nl2br(esc($item['rekomendasi'])) ?></p>
      </div>
      <?php endif; ?>

      <?php if (!empty($item['fotos'])): ?>
      <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.75rem">
        <?php foreach ($item['fotos'] as $foto): ?>
        <a href="<?= base_url($foto['path_file']) ?>" target="_blank">
          <img src="<?= base_url($foto['path_file']) ?>" alt="Foto inspeksi"
            style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius-md);border:2px solid var(--border)">
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($item['diverifikasi'] && $item['catatan_verif']): ?>
      <div style="padding:0.75rem;background:var(--success-light);border-radius:var(--radius-md);margin-bottom:0.75rem">
        <div class="text-xs font-semibold" style="color:#059669;margin-bottom:0.25rem">Catatan Verifikasi (<?= esc($item['nama_verifikator']) ?>):</div>
        <p class="text-sm"><?= nl2br(esc($item['catatan_verif'])) ?></p>
      </div>
      <?php endif; ?>

      <!-- Form Verifikasi -->
      <form action="<?= base_url('admin/inspeksi/'.$item['id_inspeksi'].'/verifikasi') ?>" method="POST" style="border-top:1px solid var(--border);padding-top:0.75rem;margin-top:0.25rem">
        <?= csrf_field() ?>
        <div style="display:flex;gap:0.75rem;align-items:end;flex-wrap:wrap">
          <div style="flex:1;min-width:200px">
            <label class="form-label text-xs">Catatan Verifikasi</label>
            <textarea name="catatan_verif" class="form-control" rows="2"
              placeholder="Keterangan verifikasi atau instruksi tindak lanjut..."><?= $item['diverifikasi'] ? esc($item['catatan_verif']) : '' ?></textarea>
          </div>
          <div style="display:flex;flex-direction:column;gap:0.5rem">
            <input type="hidden" name="diverifikasi" value="1">
            <?php if (!$item['diverifikasi']): ?>
            <button type="submit" class="btn btn-primary btn-sm">✅ Verifikasi</button>
            <button type="button" onclick="this.form.elements['diverifikasi'].value='0';this.form.submit()" class="btn btn-ghost btn-sm">↩ Tolak</button>
            <?php else: ?>
            <button type="button" onclick="this.form.elements['diverifikasi'].value='0';this.form.submit()" class="btn btn-ghost btn-sm">↩ Batalkan Verifikasi</button>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
