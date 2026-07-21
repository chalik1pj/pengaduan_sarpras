<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-card" style="max-width:560px">
  <div class="auth-header">
    <img src="<?= base_url('assets/logo.png') ?>" alt="Logo STIKOM" class="auth-logo-img">
    <h1 class="auth-title text-gradient">Daftar Akun</h1>
    <p class="auth-subtitle">Buat akun mahasiswa untuk mengakses sistem pengaduan</p>
  </div>

  <?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger mb-4">
    <span class="alert-icon">⚠️</span>
    <ul style="margin:0;padding-left:1rem">
      <?php foreach (session()->getFlashdata('errors') as $err): ?>
      <li><?= esc($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <form action="<?= base_url('auth/register') ?>" method="POST" id="registerForm">
    <?= csrf_field() ?>

    <div class="grid grid-2" style="gap:1rem">
      <div class="form-group">
        <label class="form-label" for="nim">NIM <span class="required">*</span></label>
        <input type="text" id="nim" name="nim" class="form-control" placeholder="2201011001" value="<?= old('nim') ?>" required maxlength="11">
      </div>
      <div class="form-group">
        <label class="form-label" for="angkatan">Angkatan <span class="required">*</span></label>
        <input type="number" id="angkatan" name="angkatan" class="form-control" placeholder="2022" value="<?= old('angkatan') ?>" required min="2000" max="2099">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="nama">Nama Lengkap <span class="required">*</span></label>
      <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama sesuai KTM" value="<?= old('nama') ?>" required>
    </div>

    <div class="form-group">
      <label class="form-label" for="email">Email <span class="required">*</span></label>
      <input type="email" id="email" name="email" class="form-control" placeholder="nim@mhs.tunasbangsa.ac.id" value="<?= old('email') ?>" required>
    </div>

    <div class="form-group">
      <label class="form-label" for="program_studi">Program Studi <span class="required">*</span></label>
      <select id="program_studi" name="program_studi" class="form-control" required>
        <option value="">-- Pilih Program Studi --</option>
        <option value="Teknik Informatika" <?= old('program_studi')=='Teknik Informatika'?'selected':'' ?>>Teknik Informatika</option>
        <option value="Sistem Informasi" <?= old('program_studi')=='Sistem Informasi'?'selected':'' ?>>Sistem Informasi</option>
        <option value="Komputerisasi Akuntansi" <?= old('program_studi')=='Komputerisasi Akuntansi'?'selected':'' ?>>Komputerisasi Akuntansi</option>
        <option value="Manajemen Informatika" <?= old('program_studi')=='Manajemen Informatika'?'selected':'' ?>>Manajemen Informatika</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label" for="no_hp">Nomor HP / WhatsApp</label>
      <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="081234567890" value="<?= old('no_hp') ?>">
    </div>

    <div class="grid grid-2" style="gap:1rem">
      <div class="form-group">
        <label class="form-label" for="password">Password <span class="required">*</span></label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Min 8 karakter" required autocomplete="new-password">
        <span class="form-text">Minimal 8 karakter</span>
      </div>
      <div class="form-group">
        <label class="form-label" for="konfirm_password">Konfirmasi Password <span class="required">*</span></label>
        <input type="password" id="konfirm_password" name="konfirm_password" class="form-control" placeholder="Ulangi password" required autocomplete="new-password">
      </div>
    </div>

    <button type="submit" class="btn btn-accent btn-block btn-lg" id="regBtn">
      🎓 Daftar Sekarang
    </button>
  </form>

  <div class="auth-footer">
    Sudah punya akun? <a href="<?= base_url('auth/login') ?>">Masuk di sini</a>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.getElementById('registerForm').addEventListener('submit', function() {
  const btn = document.getElementById('regBtn');
  const pwd = document.getElementById('password').value;
  const konfirm = document.getElementById('konfirm_password').value;
  if (pwd !== konfirm) { alert('Konfirmasi password tidak cocok!'); return false; }
  btn.disabled = true;
  btn.innerHTML = '⏳ Mendaftarkan...';
});
</script>
<?= $this->endSection() ?>
