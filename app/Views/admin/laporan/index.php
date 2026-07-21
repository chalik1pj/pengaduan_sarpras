<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6" style="flex-wrap:wrap">
  <h2>📝 Laporan Progres Petugas</h2>
  <?php if ($belumVerif > 0): ?>
  <span class="badge badge-tinggi"><?= $belumVerif ?> belum diverifikasi</span>
  <?php endif; ?>
</div>

<!-- Filter -->
<div class="card mb-4">
  <div class="card-body" style="padding:1rem">
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
      <a href="<?= base_url('admin/laporan') ?>" class="btn btn-sm <?= $filterVerif===''?'btn-primary':'btn-ghost' ?>">Semua</a>
      <a href="<?= base_url('admin/laporan?verif=0') ?>" class="btn btn-sm <?= $filterVerif==='0'?'btn-primary':'btn-ghost' ?>">⏳ Belum Diverifikasi</a>
      <a href="<?= base_url('admin/laporan?verif=1') ?>" class="btn btn-sm <?= $filterVerif==='1'?'btn-primary':'btn-ghost' ?>">✅ Sudah Diverifikasi</a>
    </div>
  </div>
</div>

<?php if (empty($laporan)): ?>
<div class="card">
  <div class="card-body" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:0.75rem">📝</div>
    <h4>Belum ada laporan</h4>
    <p class="text-sm text-muted">Petugas belum mengirimkan laporan progres apapun.</p>
  </div>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:1rem">
  <?php foreach ($laporan as $lap): ?>
  <div class="card">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:0.75rem;margin-bottom:0.75rem">
        <div>
          <h4 style="margin-bottom:0.25rem"><?= esc($lap['judul_laporan']) ?></h4>
          <p class="text-xs text-muted">
            Pengaduan: <strong><?= esc($lap['kode_pengaduan']) ?></strong> — <?= esc($lap['judul_pengaduan']) ?><br>
            Petugas: <strong><?= esc($lap['nama_petugas']) ?></strong>
            <?php if ($lap['jabatan']): ?>(<?= esc($lap['jabatan']) ?>)<?php endif; ?><br>
            <?= date('d M Y H:i', strtotime($lap['created_at'])) ?>
          </p>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
          <span class="badge <?= $lap['status_laporan']==='selesai'?'badge-selesai':'badge-proses' ?>">
            <?= ucfirst($lap['status_laporan']) ?>
          </span>
          <?php if ($lap['diverifikasi']): ?>
          <span class="badge badge-selesai">✅ Terverifikasi</span>
          <?php else: ?>
          <span class="badge badge-menunggu">⏳ Menunggu Verifikasi</span>
          <?php endif; ?>
        </div>
      </div>

      <p class="text-sm" style="line-height:1.7;margin-bottom:0.75rem"><?= nl2br(esc($lap['deskripsi'])) ?></p>

      <?php if (!empty($lap['fotos'])): ?>
      <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.75rem">
        <?php foreach ($lap['fotos'] as $foto): ?>
        <a href="<?= base_url($foto['path_file']) ?>" target="_blank">
          <img src="<?= base_url($foto['path_file']) ?>" alt="Foto laporan"
            style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius-md);border:2px solid var(--border)">
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($lap['diverifikasi'] && $lap['catatan_verif']): ?>
      <div style="padding:0.75rem;background:var(--success-light);border-radius:var(--radius-md);margin-bottom:0.75rem">
        <div class="text-xs font-semibold" style="color:#059669;margin-bottom:0.25rem">Catatan Verifikasi (<?= esc($lap['nama_verifikator']) ?>):</div>
        <p class="text-sm"><?= nl2br(esc($lap['catatan_verif'])) ?></p>
      </div>
      <?php endif; ?>

      <!-- Form Verifikasi -->
      <form action="<?= base_url('admin/laporan/'.$lap['id_laporan'].'/verifikasi') ?>" method="POST" style="border-top:1px solid var(--border);padding-top:0.75rem;margin-top:0.25rem">
        <?= csrf_field() ?>
        <div style="display:flex;gap:0.75rem;align-items:end;flex-wrap:wrap">
          <div style="flex:1;min-width:200px">
            <label class="form-label text-xs">Catatan Verifikasi</label>
            <textarea name="catatan_verif" class="form-control" rows="2"
              placeholder="Keterangan verifikasi atau instruksi lanjutan..."><?= $lap['diverifikasi'] ? esc($lap['catatan_verif']) : '' ?></textarea>
          </div>
          <div style="display:flex;flex-direction:column;gap:0.5rem">
            <input type="hidden" name="diverifikasi" value="1">
            <?php if (!$lap['diverifikasi']): ?>
            <button type="submit" class="btn btn-primary btn-sm">✅ Verifikasi & Terima</button>
            <button type="button" onclick="this.form.elements['diverifikasi'].value='0';this.form.submit()" class="btn btn-ghost btn-sm">↩ Tolak / Reset</button>
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
