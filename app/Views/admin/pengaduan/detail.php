<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6">
  <a href="<?= base_url('admin/pengaduan') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <div>
    <h2><?= esc($pengaduan['judul']) ?></h2>
    <p class="text-muted text-sm">Kode: <strong class="text-primary-color"><?= esc($pengaduan['kode_pengaduan']) ?></strong></p>
  </div>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">
  <!-- LEFT -->
  <div>
    <!-- Info Pengaduan -->
    <div class="card mb-4">
      <div class="card-header"><h4>📋 Detail Pengaduan</h4></div>
      <div class="card-body">
        <div class="grid grid-2" style="gap:1rem;margin-bottom:1rem">
          <?php
          $pCls=['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
          $sCls=['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
          $details=[
            ['Mahasiswa',$pengaduan['nama_mahasiswa'].' ('.$pengaduan['nim'].')'],
            ['Gedung',$pengaduan['nama_gedung']],
            ['Ruangan',$pengaduan['nama_ruangan'].' (Lt.'.$pengaduan['lantai'].')'],
            ['Barang',$pengaduan['nama_barang']??'Tidak spesifik'],
            ['Kategori',$pengaduan['nama_kategori']],
            ['Petugas',$pengaduan['nama_petugas']??'Belum ditetapkan'],
          ];
          foreach ($details as [$k,$v]):
          ?>
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted"><?= $k ?></div>
            <div class="font-semibold text-sm" style="margin-top:0.25rem"><?= esc($v) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:0.75rem;margin-bottom:1rem">
          <span class="badge <?= $pCls[$pengaduan['prioritas']]??'' ?>">Prioritas: <?= ucfirst($pengaduan['prioritas']) ?></span>
          <span class="badge <?= $sCls[$pengaduan['nama_status']]??'' ?>"><?= esc($pengaduan['nama_status']) ?></span>
        </div>
        <div style="padding:1rem;background:var(--bg-input);border-radius:var(--radius-md)">
          <div class="text-xs text-muted mb-2">Deskripsi:</div>
          <p style="font-size:0.875rem;line-height:1.7"><?= nl2br(esc($pengaduan['deskripsi'])) ?></p>
        </div>
      </div>
    </div>

    <!-- Foto Bukti -->
    <?php if (!empty($fotos)): ?>
    <div class="card mb-4">
      <div class="card-header"><h4>📸 Foto Bukti (<?= count($fotos) ?>)</h4></div>
      <div class="card-body">
        <div class="photo-gallery">
          <?php foreach ($fotos as $foto): ?>
          <div class="photo-item" data-src="<?= base_url($foto['path_file']) ?>">
            <img src="<?= base_url($foto['path_file']) ?>" alt="Bukti">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Log Timeline -->
    <div class="card">
      <div class="card-header"><h4>📅 Riwayat Status</h4></div>
      <div class="card-body">
        <?php if (empty($logs)): ?>
        <p class="text-muted text-sm">Belum ada log.</p>
        <?php else: ?>
        <div class="timeline">
          <?php foreach (array_reverse($logs) as $idx => $log): ?>
          <div class="timeline-item <?= $idx===0?'active':'done' ?>">
            <div class="timeline-time"><?= date('d M Y H:i', strtotime($log['waktu_perubahan'])) ?></div>
            <div class="timeline-content">
              <div class="timeline-title"><?= esc($log['status_baru']) ?></div>
              <?php if ($log['catatan']): ?><div class="timeline-note">💬 <?= esc($log['catatan']) ?></div><?php endif; ?>
              <?php if ($log['nama_petugas']): ?><div class="text-xs text-muted">Oleh: <?= esc($log['nama_petugas']) ?></div><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    </div>

    <!-- Laporan Progres Petugas -->
    <?php if (!empty($laporan)): ?>
    <div class="card mt-4" id="laporanPetugas">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h4>📝 Laporan Progres Petugas (<?= count($laporan) ?>)</h4>
        <a href="<?= base_url('admin/laporan?pengaduan='.$pengaduan['id_pengaduan']) ?>" class="btn btn-ghost btn-sm">Lihat Semua →</a>
      </div>
      <div class="card-body" style="padding:0">
        <?php foreach ($laporan as $lap): ?>
        <div style="padding:1.25rem;border-bottom:1px solid var(--border)">
          <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem">
            <div>
              <div class="font-semibold"><?= esc($lap['judul_laporan']) ?></div>
              <div class="text-xs text-muted">
                Oleh: <?= esc($lap['nama_petugas']) ?> &nbsp;·&nbsp; <?= date('d M Y H:i', strtotime($lap['created_at'])) ?>
              </div>
            </div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
              <span class="badge <?= $lap['status_laporan']==='selesai'?'badge-selesai':'badge-proses' ?>"><?= ucfirst($lap['status_laporan']) ?></span>
              <?php if ($lap['diverifikasi']): ?>
              <span class="badge badge-selesai">✅ Terverifikasi</span>
              <?php else: ?>
              <span class="badge badge-menunggu">⏳ Menunggu Verif</span>
              <?php endif; ?>
            </div>
          </div>
          <p class="text-sm" style="line-height:1.7;margin-bottom:0.75rem"><?= nl2br(esc($lap['deskripsi'])) ?></p>
          <?php if (!empty($lap['fotos'])): ?>
          <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.75rem">
            <?php foreach ($lap['fotos'] as $foto): ?>
            <a href="<?= base_url($foto['path_file']) ?>" target="_blank">
              <img src="<?= base_url($foto['path_file']) ?>" alt="Foto laporan"
                style="width:70px;height:70px;object-fit:cover;border-radius:var(--radius-md);border:2px solid var(--border)">
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <form action="<?= base_url('admin/laporan/'.$lap['id_laporan'].'/verifikasi') ?>" method="POST"
            style="display:flex;gap:0.5rem;align-items:end;flex-wrap:wrap">
            <?= csrf_field() ?>
            <div style="flex:1;min-width:180px">
              <label class="form-label text-xs">Catatan Verifikasi</label>
              <input type="text" name="catatan_verif" class="form-control" style="padding:0.4rem 0.75rem;font-size:0.8rem"
                placeholder="Catatan (opsional)" value="<?= esc($lap['catatan_verif'] ?? '') ?>">
            </div>
            <input type="hidden" name="diverifikasi" value="1">
            <?php if (!$lap['diverifikasi']): ?>
            <button type="submit" class="btn btn-primary btn-sm">✅ Verifikasi</button>
            <button type="button" onclick="this.form.elements['diverifikasi'].value='0';this.form.submit()" class="btn btn-ghost btn-sm">↩ Tolak</button>
            <?php else: ?>
            <button type="button" onclick="this.form.elements['diverifikasi'].value='0';this.form.submit()" class="btn btn-ghost btn-sm">↩ Batalkan</button>
            <?php endif; ?>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Actions -->
  <div>
    <!-- Tindakan Admin (Combined) -->
    <div class="card">
      <div class="card-header"><h4>⚙️ Tindakan Admin</h4></div>
      <div class="card-body">
        <form action="<?= base_url('admin/pengaduan/'.$pengaduan['id_pengaduan'].'/status') ?>" method="POST">
          <?= csrf_field() ?>
          
          <div class="form-group">
            <label class="form-label">Status Pengaduan</label>
            <select name="id_status" class="form-control" required>
              <?php foreach ($statuses as $s): ?>
              <option value="<?= $s['id_status'] ?>" <?= $pengaduan['id_status_saat_ini']==$s['id_status']?'selected':'' ?>><?= esc($s['nama_status']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Petugas Penangani</label>
            <select name="id_petugas" class="form-control">
              <option value="">-- Belum Ditetapkan --</option>
              <?php foreach ($petugas as $p): ?>
              <option value="<?= $p['id_petugas'] ?>" <?= $pengaduan['id_petugas_penangani']==$p['id_petugas']?'selected':'' ?>>
                <?= esc($p['nama']) ?> (<?= esc($p['jabatan'] ?? $p['level_akses']) ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Catatan Tindak Lanjut</label>
            <textarea name="catatan" class="form-control" rows="3" placeholder="Keterangan perkembangan pengaduan..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-block">💾 Simpan Perubahan</button>
        </form>
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
