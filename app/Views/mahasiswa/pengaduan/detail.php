<?= $this->extend('layouts/mahasiswa') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6">
  <a href="<?= base_url('mahasiswa/pengaduan') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <div>
    <h2><?= esc($pengaduan['judul']) ?></h2>
    <p class="text-muted text-sm">Kode: <strong class="text-primary-color"><?= esc($pengaduan['kode_pengaduan']) ?></strong></p>
  </div>
</div>

<div class="grid grid-2" style="gap:1.5rem;align-items:start">
  <!-- LEFT -->
  <div>
    <!-- Status Progress -->
    <div class="card mb-4">
      <div class="card-header"><h4>📊 Status Pengaduan</h4></div>
      <div class="card-body">
        <?php
        $currentStatus = $pengaduan['id_status_saat_ini'];
        $ditolak = $currentStatus == 5;
        ?>
        <?php if ($ditolak): ?>
        <div class="alert alert-danger"><span class="alert-icon">❌</span><strong>Pengaduan Ditolak</strong> — Silakan hubungi petugas untuk informasi lebih lanjut.</div>
        <?php else: ?>
        <div class="status-progress">
          <?php
          $steps = [[1,'⏳','Menunggu'],[2,'✔️','Diverifikasi'],[3,'🔧','Diproses'],[4,'✅','Selesai']];
          foreach ($steps as [$id,$icon,$label]):
            $cls = '';
            if ($id < $currentStatus) $cls = 'done';
            elseif ($id == $currentStatus) $cls = 'active';
          ?>
          <div class="status-step <?= $cls ?>">
            <div class="status-dot"><?= $cls==='done'?'✓':($cls==='active'?$icon:$id) ?></div>
            <div class="status-label"><?= $label ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="flex justify-between" style="margin-top:1rem">
          <span class="badge <?php
            $sCls = ['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
            echo $sCls[$pengaduan['nama_status']]??'';
          ?>"><?= esc($pengaduan['nama_status']) ?></span>
          <span class="text-xs text-muted"><?= date('d M Y H:i', strtotime($pengaduan['tanggal_pengaduan'])) ?></span>
        </div>
      </div>
    </div>

    <!-- Detail Info -->
    <div class="card mb-4">
      <div class="card-header"><h4>📋 Informasi Pengaduan</h4></div>
      <div class="card-body">
        <table style="width:100%;font-size:0.875rem;border-collapse:collapse">
          <?php $rows = [
            ['Kategori',$pengaduan['nama_kategori']],
            ['Gedung',$pengaduan['nama_gedung']],
            ['Ruangan',$pengaduan['nama_ruangan'].' (Lantai '.$pengaduan['lantai'].')'],
            ['Barang',$pengaduan['nama_barang']??'Tidak spesifik'],
            ['Petugas',$pengaduan['nama_petugas']??'Belum ditetapkan'],
            ['Tanggal Selesai',$pengaduan['tanggal_selesai']?date('d M Y',strtotime($pengaduan['tanggal_selesai'])):'-'],
          ]; ?>
          <?php foreach ($rows as [$k,$v]): ?>
          <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:0.5rem 0;color:var(--text-muted);width:40%"><?= $k ?></td>
            <td style="padding:0.5rem 0;font-weight:500"><?= esc($v) ?></td>
          </tr>
          <?php endforeach; ?>
          <tr>
            <td style="padding:0.5rem 0;color:var(--text-muted)">Prioritas</td>
            <td style="padding:0.5rem 0"><?php
              $pCls=['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
              echo '<span class="badge '.($pCls[$pengaduan['prioritas']]??'').'">'.ucfirst($pengaduan['prioritas']).'</span>';
            ?></td>
          </tr>
        </table>
        <div style="margin-top:1rem;padding:1rem;background:var(--bg-input);border-radius:var(--radius-md)">
          <div class="text-xs text-muted mb-2">Deskripsi:</div>
          <p style="font-size:0.875rem;line-height:1.7"><?= nl2br(esc($pengaduan['deskripsi'])) ?></p>
        </div>
      </div>
    </div>

    <!-- Foto Bukti -->
    <?php if (!empty($fotos)): ?>
    <div class="card">
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
  </div>

  <!-- RIGHT: Timeline -->
  <div>
    <div class="card">
      <div class="card-header"><h4>📅 Riwayat Perubahan Status</h4></div>
      <div class="card-body">
        <?php if (empty($logs)): ?>
        <p class="text-muted text-sm">Belum ada riwayat perubahan status.</p>
        <?php else: ?>
        <div class="timeline">
          <?php foreach (array_reverse($logs) as $idx => $log): ?>
          <div class="timeline-item <?= $idx===0?'active':'done' ?>">
            <div class="timeline-time"><?= date('d M Y H:i', strtotime($log['waktu_perubahan'])) ?></div>
            <div class="timeline-content">
              <div class="timeline-title"><?= esc($log['status_baru']) ?></div>
              <?php if ($log['status_lama']): ?>
              <div class="text-xs text-muted">dari: <?= esc($log['status_lama']) ?></div>
              <?php endif; ?>
              <?php if ($log['catatan']): ?>
              <div class="timeline-note">💬 <?= esc($log['catatan']) ?></div>
              <?php endif; ?>
              <?php if ($log['nama_petugas']): ?>
              <div class="text-xs text-muted">Oleh: <?= esc($log['nama_petugas']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
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
