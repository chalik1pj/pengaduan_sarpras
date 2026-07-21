<?= $this->extend('layouts/mahasiswa') ?>
<?= $this->section('content') ?>

<h2 class="mb-6">Profil Saya</h2>

<div class="grid grid-2" style="gap:1.5rem;align-items:start">
  <!-- Update Profil -->
  <div class="card">
    <div class="card-header"><h4>👤 Data Diri</h4></div>
    <div class="card-body">
      <div style="text-align:center;margin-bottom:1.5rem">
        <div class="avatar avatar-xl" style="margin:0 auto 1rem;font-size:2rem">
          <?php $foto = session()->get('mahasiswa_foto'); ?>
          <?php if ($foto): ?>
          <img src="<?= base_url($foto) ?>" alt="Foto profil">
          <?php else: ?>
          <?= strtoupper(substr($mahasiswa['nama']??'U',0,1)) ?>
          <?php endif; ?>
        </div>
        <div class="font-bold"><?= esc($mahasiswa['nama']) ?></div>
        <div class="text-muted text-sm"><?= esc($mahasiswa['nim']) ?></div>
      </div>
      <form action="<?= base_url('mahasiswa/profil/update') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">Foto Profil</label>
          <input type="file" name="foto_profil" class="form-control" accept="image/*">
          <span class="form-text">Maks 1MB, JPG/PNG/WEBP</span>
        </div>
        <div class="form-group">
          <label class="form-label" for="nama">Nama Lengkap <span class="required">*</span></label>
          <input type="text" id="nama" name="nama" class="form-control" value="<?= esc($mahasiswa['nama']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="email">Email <span class="required">*</span></label>
          <input type="email" id="email" name="email" class="form-control" value="<?= esc($mahasiswa['email']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="program_studi">Program Studi</label>
          <select id="program_studi" name="program_studi" class="form-control">
            <?php foreach (['Teknik Informatika','Sistem Informasi','Komputerisasi Akuntansi','Manajemen Informatika'] as $prodi): ?>
            <option value="<?= $prodi ?>" <?= $mahasiswa['program_studi']===$prodi?'selected':'' ?>><?= $prodi ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid grid-2" style="gap:1rem">
          <div class="form-group">
            <label class="form-label">Angkatan</label>
            <input type="number" name="angkatan" class="form-control" value="<?= esc($mahasiswa['angkatan']) ?>" min="2000" max="2099">
          </div>
          <div class="form-group">
            <label class="form-label">No. HP/WA</label>
            <input type="text" name="no_hp" class="form-control" value="<?= esc($mahasiswa['no_hp']) ?>">
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">💾 Simpan Perubahan</button>
      </form>
    </div>
  </div>

  <!-- Ganti Password -->
  <div class="card">
    <div class="card-header"><h4>🔐 Ganti Password</h4></div>
    <div class="card-body">
      <form action="<?= base_url('mahasiswa/profil/password') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">Password Lama <span class="required">*</span></label>
          <input type="password" name="password_lama" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password Baru <span class="required">*</span></label>
          <input type="password" name="password_baru" class="form-control" required>
          <span class="form-text">Minimal 8 karakter</span>
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password Baru <span class="required">*</span></label>
          <input type="password" name="konfirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger btn-block">🔐 Ganti Password</button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
