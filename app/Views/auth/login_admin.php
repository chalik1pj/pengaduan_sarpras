<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-card">
  <div class="auth-header">
    <img src="<?= base_url('assets/logo.png') ?>" alt="Logo STIKOM" class="auth-logo-img">
    <h1 class="auth-title">Admin Panel</h1>
    <p class="auth-subtitle">Login sebagai Admin / Petugas Sarpras</p>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success mb-4"><span class="alert-icon">✅</span><span><?= session()->getFlashdata('success') ?></span></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger mb-4"><span class="alert-icon">❌</span><span><?= session()->getFlashdata('error') ?></span></div>
  <?php endif; ?>

  <form action="<?= base_url('admin/auth/login') ?>" method="POST" id="adminLoginForm">
    <?= csrf_field() ?>

    <div class="form-group">
      <label class="form-label" for="email">Email <span class="required">*</span></label>
      <div class="input-group">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <input type="email" id="email" name="email" class="form-control" placeholder="admin@tunasbangsa.ac.id" value="<?= old('email') ?>" required autocomplete="email">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="password">Password <span class="required">*</span></label>
      <div class="input-group input-icon-right">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
        <svg class="input-icon" style="cursor:pointer;pointer-events:all;right:1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" onclick="togglePassword('password', this)">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </div>
    </div>

    <button type="submit" class="btn btn-lg btn-block" id="adminLoginBtn" style="background:linear-gradient(135deg,#f59e0b,#ef4444);color:white;box-shadow:0 4px 15px rgba(245,158,11,0.35)">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Masuk Admin
    </button>
  </form>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function togglePassword(id, icon) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
}
document.getElementById('adminLoginForm').addEventListener('submit', function() {
  const btn = document.getElementById('adminLoginBtn');
  btn.disabled = true;
  btn.innerHTML = '⏳ Memverifikasi...';
});
</script>
<?= $this->endSection() ?>
