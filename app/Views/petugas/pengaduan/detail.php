<?= $this->extend('layouts/petugas') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6">
  <a href="<?= base_url('petugas/pengaduan') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <div>
    <h2><?= esc($pengaduan['judul']) ?></h2>
    <p class="text-muted text-sm">Kode: <strong class="text-primary-color"><?= esc($pengaduan['kode_pengaduan']) ?></strong></p>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr;gap:1.5rem;align-items:start">
  <!-- LEFT: Info + Laporan -->
  <div>
    <!-- Info Pengaduan -->
    <div class="card mb-4">
      <div class="card-header"><h4>📋 Detail Pengaduan Mahasiswa</h4></div>
      <div class="card-body">
        <div class="grid grid-2" style="gap:0.75rem;margin-bottom:1rem">
          <?php
          $details = [
            ['Mahasiswa', $pengaduan['nama_mahasiswa'].' ('.$pengaduan['nim'].')'],
            ['Gedung',    $pengaduan['nama_gedung']],
            ['Ruangan',   $pengaduan['nama_ruangan'].' (Lt.'.$pengaduan['lantai'].')'],
            ['Barang',    $pengaduan['nama_barang'] ?? 'Tidak spesifik'],
            ['Kategori',  $pengaduan['nama_kategori']],
            ['Tanggal',   date('d M Y H:i', strtotime($pengaduan['tanggal_pengaduan']))],
          ];
          foreach ($details as [$k,$v]): ?>
          <div style="padding:0.75rem;background:var(--bg-input);border-radius:var(--radius-md)">
            <div class="text-xs text-muted"><?= $k ?></div>
            <div class="font-semibold text-sm" style="margin-top:0.25rem"><?= esc($v) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php
        $pCls = ['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
        $sCls = ['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
        ?>
        <div style="display:flex;gap:0.75rem;margin-bottom:1rem">
          <span class="badge <?= $pCls[$pengaduan['prioritas']] ?? '' ?>">Prioritas: <?= ucfirst($pengaduan['prioritas']) ?></span>
          <span class="badge <?= $sCls[$pengaduan['nama_status']] ?? '' ?>"><?= esc($pengaduan['nama_status']) ?></span>
        </div>
        <div style="padding:1rem;background:var(--bg-input);border-radius:var(--radius-md)">
          <div class="text-xs text-muted mb-2">Deskripsi Masalah:</div>
          <p style="font-size:0.875rem;line-height:1.7"><?= nl2br(esc($pengaduan['deskripsi'])) ?></p>
        </div>
      </div>
    </div>

    <!-- Foto Bukti dari Mahasiswa -->
    <?php if (!empty($fotosPengaduan)): ?>
    <div class="card mb-4">
      <div class="card-header"><h4>📸 Foto Bukti dari Mahasiswa (<?= count($fotosPengaduan) ?>)</h4></div>
      <div class="card-body">
        <div class="photo-gallery">
          <?php foreach ($fotosPengaduan as $foto): ?>
          <div class="photo-item" data-src="<?= base_url($foto['path_file']) ?>">
            <img src="<?= base_url($foto['path_file']) ?>" alt="Bukti">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Riwayat Laporan Progres yang Sudah Dikirim -->
    <?php if (!empty($laporan)): ?>
    <div class="card">
      <div class="card-header"><h4>📝 Riwayat Laporan Progres Anda (<?= count($laporan) ?>)</h4></div>
      <div class="card-body" style="padding:0">
        <?php foreach ($laporan as $lap): ?>
        <div style="padding:1.25rem;border-bottom:1px solid var(--border)">
          <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem">
            <div>
              <div class="font-semibold"><?= esc($lap['judul_laporan']) ?></div>
              <div class="text-xs text-muted"><?= date('d M Y H:i', strtotime($lap['created_at'])) ?></div>
            </div>
            <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap">
              <span class="badge <?= $lap['status_laporan'] === 'selesai' ? 'badge-selesai' : 'badge-proses' ?>">
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
          <div class="photo-gallery" style="grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:0.5rem">
            <?php foreach ($lap['fotos'] as $foto): ?>
            <div class="photo-item" data-src="<?= base_url($foto['path_file']) ?>">
              <img src="<?= base_url($foto['path_file']) ?>" alt="Foto laporan">
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if ($lap['diverifikasi'] && !empty($lap['catatan_verif'])): ?>
          <div style="margin-top:0.75rem;padding:0.75rem;background:var(--success-light);border-radius:var(--radius-md)">
            <div class="text-xs font-semibold" style="color:#059669;margin-bottom:0.25rem">✅ Catatan Verifikasi dari Admin:</div>
            <p class="text-sm"><?= nl2br(esc($lap['catatan_verif'])) ?></p>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Form Upload Laporan -->
  <div>
    <div class="card">
      <div class="card-header"><h4>📤 Upload Laporan Progres</h4></div>
      <div class="card-body">
        <form action="<?= base_url('petugas/pengaduan/'.$pengaduan['id_pengaduan'].'/laporan') ?>" method="POST" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group">
            <label class="form-label">Judul Laporan <span class="required">*</span></label>
            <input type="text" name="judul_laporan" class="form-control" required
              value="<?= esc($pengaduan['judul']) ?>"
              placeholder="Misal: Progres penggantian AC Unit 1">
          </div>

          <div class="form-group">
            <label class="form-label">Deskripsi Progres <span class="required">*</span></label>
            <textarea name="deskripsi" class="form-control" rows="4" required
              placeholder="Jelaskan apa yang sudah dikerjakan, kendala yang dihadapi, dll..."></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Status Penanganan <span class="required">*</span></label>
            <select name="status_laporan" class="form-control" required>
              <option value="proses">Masih dalam Proses</option>
              <option value="selesai">Sudah Selesai Ditangani</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Foto Bukti Penanganan</label>
            <div class="upload-area" id="uploadAreaLaporan">
              <div class="upload-icon">📷</div>
              <div class="upload-text">Klik atau drag foto ke sini</div>
              <div class="upload-hint text-xs text-muted">Maksimal 3 foto, masing-masing max 1MB</div>
            </div>
            <input type="file" id="foto_laporan" name="foto_laporan[foto_laporan][]" accept="image/*" multiple style="display:none">
            <div id="uploadPreviewLaporan" class="upload-preview-grid" style="margin-top:0.75rem"></div>
            <div id="uploadCounterLaporan" class="text-xs text-muted" style="margin-top:0.25rem">0/3 foto</div>
          </div>

          <button type="submit" class="btn btn-primary btn-block">📤 Kirim Laporan Progres</button>
          <p class="text-xs text-muted" style="margin-top:0.5rem;text-align:center">
            Admin akan mendapat notifikasi WhatsApp setelah laporan dikirim.
          </p>
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
<?= $this->section('scripts') ?>
<script>
// Upload preview khusus halaman ini
(function() {
  const area    = document.getElementById('uploadAreaLaporan');
  const input   = document.getElementById('foto_laporan');
  const preview = document.getElementById('uploadPreviewLaporan');
  const counter = document.getElementById('uploadCounterLaporan');
  const MAX = 3;
  let selectedFiles = [];

  if (!area || !input) return;

  area.addEventListener('click', () => input.click());
  area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag-over'); });
  area.addEventListener('dragleave', () => area.classList.remove('drag-over'));
  area.addEventListener('drop', e => { e.preventDefault(); area.classList.remove('drag-over'); handleFiles(e.dataTransfer.files); });
  input.addEventListener('change', function() { handleFiles(this.files); });

  function handleFiles(files) {
    Array.from(files).forEach(file => {
      if (selectedFiles.length >= MAX) return;
      if (!file.type.startsWith('image/')) return;
      if (file.size > 1048576) { alert(file.name + ' melebihi 1MB'); return; }
      selectedFiles.push(file);
    });
    updatePreview();
    updateInput();
  }

  function updatePreview() {
    if (!preview) return;
    preview.innerHTML = '';
    counter.textContent = selectedFiles.length + '/' + MAX + ' foto';
    selectedFiles.forEach((file, idx) => {
      const reader = new FileReader();
      reader.onload = e => {
        const div = document.createElement('div');
        div.className = 'upload-preview-item';
        div.innerHTML = '<img src="' + e.target.result + '" alt="Preview"><button type="button" class="remove-btn" onclick="removeFileLaporan(' + idx + ')">✕</button>';
        preview.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  }

  function updateInput() {
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    input.files = dt.files;
  }

  window.removeFileLaporan = function(idx) {
    selectedFiles.splice(idx, 1);
    updatePreview();
    updateInput();
  };
})();
</script>
<?= $this->endSection() ?>
