<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-card">
  <div class="auth-header">
    <img src="<?= base_url('assets/logo.png') ?>" alt="Logo STIKOM" class="auth-logo-img">
    <h1 class="auth-title text-gradient">Pengaduan Sarpras</h1>
    <p class="auth-subtitle">Masuk sebagai Mahasiswa<br>STIKOM Tunas Bangsa</p>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success mb-4">
    <span class="alert-icon">✅</span>
    <span><?= session()->getFlashdata('success') ?></span>
  </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger mb-4">
    <span class="alert-icon">❌</span>
    <span><?= session()->getFlashdata('error') ?></span>
  </div>
  <?php endif; ?>

  <form action="<?= base_url('auth/login') ?>" method="POST" id="loginForm">
    <?= csrf_field() ?>

    <div class="form-group">
      <label class="form-label" for="nim">NIM / Email <span class="required">*</span></label>
      <div class="input-group">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <input type="text" id="nim" name="nim" class="form-control" placeholder="Masukkan NIM atau Email" value="<?= old('nim') ?>" required autocomplete="username" autofocus>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="password">Password <span class="required">*</span></label>
      <div class="input-group input-icon-right">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
        <svg class="input-icon" id="togglePwd" style="cursor:pointer;pointer-events:all;right:1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" onclick="togglePassword('password','togglePwd')">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg" id="loginBtn">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Masuk
    </button>
  </form>

  <div class="auth-divider">atau</div>

  <div class="auth-footer">
    Belum punya akun? <a href="<?= base_url('auth/register') ?>">Daftar di sini</a>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function togglePassword(id, iconId) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
}

document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.disabled = true;
  btn.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin-bg 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Memproses...';
});
</script>
<?= $this->endSection() ?>
