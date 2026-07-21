<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'Mahasiswa') ?> - Pengaduan Sarpras</title>
<meta name="description" content="Sistem Pengaduan Fasilitas Sarpras STIKOM Tunas Bangsa">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<div class="app-wrapper">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-logo">
        <img src="<?= base_url('assets/logo.png') ?>" alt="Logo STIKOM" class="brand-icon-img">
        <div class="brand-text">
          Pengaduan Sarpras<br>
          <span>STIKOM Tunas Bangsa</span>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <div class="sidebar-section-label">Menu</div>
        <a href="<?= base_url('mahasiswa/dashboard') ?>" class="sidebar-link <?= ($active ?? '') == 'dashboard' ? 'active' : '' ?>">
          <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="<?= base_url('mahasiswa/pengaduan/buat') ?>" class="sidebar-link <?= ($active ?? '') == 'buat' ? 'active' : '' ?>">
          <span class="nav-icon">➕</span> Buat Pengaduan
        </a>
        <a href="<?= base_url('mahasiswa/pengaduan') ?>" class="sidebar-link <?= ($active ?? '') == 'pengaduan' ? 'active' : '' ?>">
          <span class="nav-icon">📋</span> Riwayat Pengaduan
        </a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">Akun</div>
        <a href="<?= base_url('mahasiswa/profil') ?>" class="sidebar-link <?= ($active ?? '') == 'profil' ? 'active' : '' ?>">
          <span class="nav-icon">👤</span> Profil Saya
        </a>
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar">
          <?php $foto = session()->get('mahasiswa_foto'); ?>
          <?php if ($foto): ?>
            <img src="<?= base_url($foto) ?>" alt="Foto Profil">
          <?php else: ?>
            <?= strtoupper(substr(session()->get('mahasiswa_nama') ?? 'U', 0, 1)) ?>
          <?php endif; ?>
        </div>
        <div class="user-info">
          <div class="user-name"><?= esc(session()->get('mahasiswa_nama')) ?></div>
          <div class="user-role"><?= esc(session()->get('mahasiswa_nim')) ?></div>
        </div>
      </div>
      <a href="<?= base_url('auth/logout') ?>" class="btn btn-outline btn-sm btn-block">🚪 Keluar</a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
          ☰
        </button>
        <h1 class="page-title"><?= esc($title ?? '') ?></h1>
      </div>
      <div class="topbar-right">
        <div class="avatar avatar-sm" style="background:linear-gradient(135deg,#6366f1,#10b981)">
          <?= strtoupper(substr(session()->get('mahasiswa_nama') ?? 'U', 0, 1)) ?>
        </div>
        <span class="text-sm text-secondary" id="topbarName"><?= esc(session()->get('mahasiswa_nama')) ?></span>
      </div>
    </header>

    <main class="page-body">
      <!-- Flash Messages -->
      <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success animate-fade-up">
        <span class="alert-icon">✅</span>
        <span><?= session()->getFlashdata('success') ?></span>
      </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger animate-fade-up">
        <span class="alert-icon">❌</span>
        <span><?= session()->getFlashdata('error') ?></span>
      </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('errors')): ?>
      <div class="alert alert-danger animate-fade-up">
        <span class="alert-icon">⚠️</span>
        <ul style="margin:0;padding-left:1rem">
          <?php foreach (session()->getFlashdata('errors') as $err): ?>
          <li><?= esc($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?= $this->renderSection('content') ?>
    </main>
  </div>

</div>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
<script>
// BFCache Guard — Paksa reload saat halaman ditampilkan dari cache browser (tombol Back setelah logout)
window.addEventListener('pageshow', function(e) {
  if (e.persisted || (window.performance && window.performance.navigation.type === 2)) {
    window.location.reload();
  }
});
</script>
</body>
</html>
