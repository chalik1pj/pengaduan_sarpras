<?= $this->extend('layouts/petugas') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-6">
  <a href="<?= base_url('petugas/inspeksi') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <h2>🔍 Buat Laporan Inspeksi Fasilitas</h2>
</div>

<form id="formInspeksi" action="<?= base_url('petugas/inspeksi/simpan') ?>" method="POST" enctype="multipart/form-data">
<?= csrf_field() ?>

<div class="grid" style="grid-template-columns:1fr 380px;gap:1.5rem;align-items:start">
  <!-- Kiri: Detail Form -->
  <div class="card">
    <div class="card-header"><h4>📝 Detail Inspeksi</h4></div>
    <div class="card-body">

      <div class="form-group">
        <label class="form-label">Gedung <span class="required">*</span></label>
        <select id="id_gedung" class="form-control" required>
          <option value="">-- Pilih Gedung --</option>
          <?php foreach ($gedung as $g): ?>
          <option value="<?= $g['id_gedung'] ?>"><?= esc($g['nama_gedung']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Ruangan <span class="required">*</span></label>
        <select id="id_ruangan" name="id_ruangan" class="form-control" required>
          <option value="">-- Pilih Gedung Dulu --</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Barang Spesifik <span class="text-muted text-xs">(opsional)</span></label>
        <select id="id_barang" name="id_barang" class="form-control">
          <option value="">-- Tidak Ada / Umum Ruangan --</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Judul Inspeksi <span class="required">*</span></label>
        <input type="text" name="judul_inspeksi" class="form-control" required
          placeholder="Misal: Pengecekan AC Lab 06 Gedung 3">
      </div>

      <div class="form-group">
        <label class="form-label">Kondisi Fasilitas <span class="required">*</span></label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem">
          <?php
          $kondisiOpts = [
            ['baik',            '✅', 'Baik',           'Fasilitas dalam kondisi baik dan layak digunakan'],
            ['perlu_perbaikan', '⚠️', 'Perlu Perbaikan','Ada kerusakan ringan, masih bisa digunakan'],
            ['rusak_berat',     '🔴', 'Rusak Berat',    'Tidak bisa digunakan, butuh perbaikan segera'],
          ];
          foreach ($kondisiOpts as [$val, $icon, $label, $hint]): ?>
          <label style="cursor:pointer">
            <input type="radio" name="kondisi_temuan" value="<?= $val ?>" required style="display:none" class="kondisi-radio">
            <div class="kondisi-card" style="padding:0.75rem;border:2px solid var(--border);border-radius:var(--radius-md);text-align:center;transition:all 0.2s">
              <div style="font-size:1.5rem"><?= $icon ?></div>
              <div class="font-semibold text-xs" style="margin-top:0.25rem"><?= $label ?></div>
              <div class="text-xs text-muted" style="margin-top:0.25rem;line-height:1.3"><?= $hint ?></div>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Deskripsi Temuan <span class="required">*</span></label>
        <textarea name="deskripsi" class="form-control" rows="4" required
          placeholder="Deskripsikan kondisi yang ditemukan secara detail..."></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Rekomendasi Tindak Lanjut</label>
        <textarea name="rekomendasi" class="form-control" rows="3"
          placeholder="Saran perbaikan atau tindakan yang diperlukan..."></textarea>
      </div>

      <button type="submit" class="btn btn-primary btn-block">🔍 Kirim Laporan Inspeksi</button>
      <p class="text-xs text-muted" style="margin-top:0.5rem;text-align:center">
        Admin akan mendapat notifikasi WhatsApp setelah laporan dikirim.
      </p>
    </div>
  </div>

  <!-- Kanan: Upload Foto -->
  <div class="card">
    <div class="card-header"><h4>📸 Foto Kondisi Fasilitas</h4></div>
    <div class="card-body">
      <div class="upload-area" id="uploadAreaInspeksi">
        <div class="upload-icon">📷</div>
        <div class="upload-text">Klik atau drag foto ke sini</div>
        <div class="upload-hint text-xs text-muted">Maksimal 3 foto, masing-masing max 1MB</div>
      </div>
      <input type="file" id="foto_inspeksi" name="foto_inspeksi[foto_inspeksi][]"
        accept="image/*" multiple style="display:none">
      <div id="uploadPreviewInspeksi" class="upload-preview-grid" style="margin-top:0.75rem"></div>
      <div id="uploadCounterInspeksi" class="text-xs text-muted" style="margin-top:0.25rem">0/3 foto</div>

      <div style="margin-top:1.5rem;padding:1rem;background:var(--bg-input);border-radius:var(--radius-md)">
        <div class="font-semibold text-sm mb-2">💡 Tips Foto Inspeksi:</div>
        <ul class="text-xs text-muted" style="padding-left:1rem;line-height:1.8">
          <li>Ambil foto dari sudut yang jelas memperlihatkan kondisi</li>
          <li>Sertakan foto close-up jika ada kerusakan spesifik</li>
          <li>Pastikan pencahayaan cukup</li>
          <li>Foto dari keseluruhan ruangan untuk konteks</li>
        </ul>
      </div>
    </div>
  </div>
</div>

</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
// Kondisi card selection
document.querySelectorAll('.kondisi-radio').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.kondisi-card').forEach(c => {
      c.style.borderColor = 'var(--border)';
      c.style.background  = 'transparent';
    });
    const card = this.closest('label').querySelector('.kondisi-card');
    card.style.borderColor = 'var(--primary)';
    card.style.background  = 'var(--primary-light)';
  });
});

// Upload foto inspeksi
(function() {
  const area    = document.getElementById('uploadAreaInspeksi');
  const input   = document.getElementById('foto_inspeksi');
  const preview = document.getElementById('uploadPreviewInspeksi');
  const counter = document.getElementById('uploadCounterInspeksi');
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
        div.innerHTML = '<img src="' + e.target.result + '" alt="Preview"><button type="button" class="remove-btn" onclick="removeFileInspeksi(' + idx + ')">✕</button>';
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

  window.removeFileInspeksi = function(idx) {
    selectedFiles.splice(idx, 1);
    updatePreview();
    updateInput();
  };
})();
</script>
<?= $this->endSection() ?>
