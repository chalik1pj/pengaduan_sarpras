<?= $this->extend('layouts/mahasiswa') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6">
  <a href="<?= base_url('mahasiswa/pengaduan') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <div>
    <h2>Buat Pengaduan Baru</h2>
    <p class="text-muted text-sm">Laporkan masalah fasilitas kampus yang Anda temukan</p>
  </div>
</div>

<!-- Base URL meta for JS -->
<meta name="base-url" content="<?= base_url() ?>">

<form action="<?= base_url('mahasiswa/pengaduan/simpan') ?>" method="POST" enctype="multipart/form-data" id="pengaduanForm">
  <?= csrf_field() ?>

  <div class="grid grid-2" style="gap:1.5rem;align-items:start">
    <!-- LEFT COLUMN -->
    <div>
      <div class="card mb-4">
        <div class="card-header"><h4>📍 Lokasi Masalah</h4></div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label" for="id_gedung">Gedung <span class="required">*</span></label>
            <select id="id_gedung" name="id_gedung" class="form-control" required>
              <option value="">-- Pilih Gedung --</option>
              <?php foreach ($gedung as $g): ?>
              <option value="<?= $g['id_gedung'] ?>" <?= old('id_gedung')==$g['id_gedung']?'selected':'' ?>>
                <?= esc($g['nama_gedung']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="id_ruangan">Ruangan <span class="required">*</span></label>
            <select id="id_ruangan" name="id_ruangan" class="form-control" required>
              <option value="">-- Pilih Gedung dulu --</option>
            </select>
            <span class="form-text">Ruangan akan muncul setelah memilih gedung</span>
          </div>
          <div class="form-group">
            <label class="form-label" for="id_barang">Barang / Fasilitas Spesifik</label>
            <select id="id_barang" name="id_barang" class="form-control">
              <option value="">-- Tidak spesifik (keluhan umum ruangan) --</option>
            </select>
            <span class="form-text">Opsional — pilih jika ada barang tertentu yang bermasalah</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h4>📸 Foto Bukti</h4></div>
        <div class="card-body">
          <div class="upload-area" id="uploadArea">
            <span class="upload-icon">📷</span>
            <p class="upload-title">Klik atau seret foto ke sini</p>
            <p class="upload-hint">Maks. 3 foto • Maks. 1MB per foto • JPG/PNG/WEBP</p>
            <input type="file" id="foto_bukti" name="foto_bukti[]" accept="image/*" multiple>
          </div>
          <div id="uploadPreview" class="upload-previews"></div>
          <div class="form-text" id="uploadCounter">0/3 foto</div>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div>
      <div class="card">
        <div class="card-header"><h4>📝 Detail Pengaduan</h4></div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label" for="id_kategori">Kategori <span class="required">*</span></label>
            <select id="id_kategori" name="id_kategori" class="form-control" required>
              <option value="">-- Pilih Kategori --</option>
              <?php foreach ($kategori as $k): ?>
              <option value="<?= $k['id_kategori'] ?>" <?= old('id_kategori')==$k['id_kategori']?'selected':'' ?>>
                <?= esc($k['nama_kategori']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="judul">Judul Pengaduan <span class="required">*</span></label>
            <input type="text" id="judul" name="judul" class="form-control" placeholder="Contoh: AC Lab Komputer 01 tidak dingin" value="<?= old('judul') ?>" required maxlength="150">
          </div>

          <div class="form-group">
            <label class="form-label" for="deskripsi">Deskripsi <span class="required">*</span></label>
            <textarea id="deskripsi" name="deskripsi" class="form-control" rows="6" placeholder="Jelaskan masalah secara detail: kapan terjadi, bagaimana kondisinya, dll." required><?= old('deskripsi') ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Prioritas <span class="required">*</span></label>
            <div class="flex gap-3" style="flex-wrap:wrap">
              <?php foreach (['rendah'=>['🟢','Rendah','Tidak mendesak'],'sedang'=>['🟡','Sedang','Perlu ditangani'],'tinggi'=>['🔴','Tinggi','Mendesak/Darurat']] as $val=>$info): ?>
              <label class="form-check" style="flex:1;min-width:100px;cursor:pointer;padding:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);transition:var(--transition)" id="label_<?= $val ?>">
                <input type="radio" name="prioritas" value="<?= $val ?>" <?= old('prioritas','sedang')===$val?'checked':'' ?> onchange="updatePrioritas()">
                <div>
                  <div class="font-semibold text-sm"><?= $info[0] ?> <?= $info[1] ?></div>
                  <div class="text-xs text-muted"><?= $info[2] ?></div>
                </div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
            📤 Kirim Pengaduan
          </button>
          <p class="text-center text-xs text-muted" style="margin-top:0.5rem">Pengaduan akan diverifikasi oleh petugas sarpras</p>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
  <button class="lightbox-close" onclick="document.getElementById('lightbox').classList.remove('active')">✕</button>
  <img src="" id="lightboxImg" alt="Preview">
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function updatePrioritas() {
  document.querySelectorAll('[id^="label_"]').forEach(function(label) {
    const radio = label.querySelector('input[type=radio]');
    if (radio.checked) {
      label.style.borderColor = 'var(--primary)';
      label.style.background = 'rgba(99,102,241,0.08)';
    } else {
      label.style.borderColor = 'var(--border)';
      label.style.background = 'transparent';
    }
  });
}
updatePrioritas();

document.getElementById('pengaduanForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '⏳ Mengirim Pengaduan...';
});
</script>
<?= $this->endSection() ?>
