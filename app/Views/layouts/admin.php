<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'Admin') ?> - Pengaduan Sarpras</title>
<meta name="description" content="Admin - Sistem Pengaduan Fasilitas Sarpras STIKOM Tunas Bangsa">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
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
          Admin Panel<br>
          <span>Pengaduan Sarpras</span>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <div class="sidebar-section-label">Menu</div>
        <a href="<?= base_url('admin/dashboard') ?>" class="sidebar-link <?= ($active ?? '') == 'dashboard' ? 'active' : '' ?>">
          <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="<?= base_url('admin/pengaduan') ?>" class="sidebar-link <?= ($active ?? '') == 'pengaduan' ? 'active' : '' ?>">
          <span class="nav-icon">📋</span> Pengaduan
        </a>
        <a href="<?= base_url('admin/laporan') ?>" class="sidebar-link <?= ($active ?? '') == 'laporan' ? 'active' : '' ?>">
          <span class="nav-icon">📝</span> Laporan Petugas
        </a>
        <a href="<?= base_url('admin/inspeksi') ?>" class="sidebar-link <?= ($active ?? '') == 'inspeksi' ? 'active' : '' ?>">
          <span class="nav-icon">🔍</span> Inspeksi Fasilitas
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-section-label">Manajemen Fasilitas</div>
        <a href="<?= base_url('admin/gedung') ?>" class="sidebar-link <?= ($active ?? '') == 'gedung' ? 'active' : '' ?>">
          <span class="nav-icon">🏢</span> Gedung
        </a>
        <a href="<?= base_url('admin/ruangan') ?>" class="sidebar-link <?= ($active ?? '') == 'ruangan' ? 'active' : '' ?>">
          <span class="nav-icon">🚪</span> Ruangan
        </a>
        <a href="<?= base_url('admin/barang') ?>" class="sidebar-link <?= ($active ?? '') == 'barang' ? 'active' : '' ?>">
          <span class="nav-icon">📦</span> Barang / Fasilitas
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-section-label">Pengguna</div>
        <?php if (session()->get('admin_level') === 'super_admin'): ?>
        <a href="<?= base_url('admin/petugas') ?>" class="sidebar-link <?= ($active ?? '') == 'petugas' ? 'active' : '' ?>">
          <span class="nav-icon">👮</span> Petugas
        </a>
        <?php endif; ?>
        <a href="<?= base_url('admin/mahasiswa') ?>" class="sidebar-link <?= ($active ?? '') == 'mahasiswa' ? 'active' : '' ?>">
          <span class="nav-icon">🎓</span> Mahasiswa
        </a>
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar">
          <?= strtoupper(substr(session()->get('admin_nama') ?? 'A', 0, 1)) ?>
        </div>
        <div class="user-info">
          <div class="user-name"><?= esc(session()->get('admin_nama')) ?></div>
          <div class="user-role"><?= esc(session()->get('admin_level')) ?></div>
        </div>
      </div>
      <a href="<?= base_url('admin/auth/logout') ?>" class="btn btn-outline btn-sm btn-block">🚪 Keluar</a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">☰</button>
        <h1 class="page-title"><?= esc($title ?? '') ?></h1>
      </div>
      <div class="topbar-right">
        <span class="badge badge-proses text-xs"><?= esc(session()->get('admin_level')) ?></span>
        <div class="avatar avatar-sm" style="background:linear-gradient(135deg,#f59e0b,#ef4444)">
          <?= strtoupper(substr(session()->get('admin_nama') ?? 'A', 0, 1)) ?>
        </div>
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
<script src="<?= base_url('assets/js/admin.js') ?>"></script>
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
