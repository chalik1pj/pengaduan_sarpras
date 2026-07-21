<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
  <div><h2>Manajemen Petugas</h2><p class="text-muted text-sm"><?= count($items) ?> petugas terdaftar</p></div>
  <button class="btn btn-primary" onclick="openModal('addPetugasModal')">➕ Tambah Petugas</button>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama</th><th>Email</th><th>Jabatan</th><th>No. WA</th><th>Level</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($items as $i => $item): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= esc($item['nama']) ?></strong></td>
        <td class="text-sm"><?= esc($item['email']) ?></td>
        <td class="text-muted text-sm"><?= esc($item['jabatan']??'-') ?></td>
        <td class="text-sm"><?= $item['no_wa'] ? '📱 '.esc($item['no_wa']) : '<span class="text-muted">-</span>' ?></td>
        <td><?php
          $lCls=['super_admin'=>'badge-ditolak','admin'=>'badge-proses','petugas'=>'badge-verifikasi'];
          echo '<span class="badge '.($lCls[$item['level_akses']]??'').'">'.esc($item['level_akses']).'</span>';
        ?></td>
        <td><span class="badge <?= $item['status_akun']==='aktif'?'badge-aktif':'badge-nonaktif' ?>"><?= esc($item['status_akun']) ?></span></td>
        <td class="table-actions">
          <button class="btn btn-outline btn-sm" onclick="openEditPetugas(this)" data-id="<?= $item['id_petugas'] ?>" data-nama="<?= esc($item['nama']) ?>" data-email="<?= esc($item['email']) ?>" data-jabatan="<?= esc($item['jabatan']??'') ?>" data-no_wa="<?= esc($item['no_wa']??'') ?>" data-level_akses="<?= esc($item['level_akses']) ?>" data-status_akun="<?= esc($item['status_akun']) ?>">✏️ Edit</button>
          <?php if ($item['id_petugas'] != session()->get('admin_id')): ?>
          <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $item['id_petugas'] ?>,'<?= esc($item['nama']) ?>')">🗑️</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addPetugasModal">
  <div class="modal">
    <div class="modal-header"><h4>➕ Tambah Petugas</h4><button class="modal-close" onclick="closeModal('addPetugasModal')">✕</button></div>
    <form action="<?= base_url('admin/petugas/simpan') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="nama" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Email <span class="required">*</span></label><input type="email" name="email" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Password <span class="required">*</span></label><input type="password" name="password" class="form-control" required><span class="form-text">Min 8 karakter</span></div>
        <div class="grid grid-2" style="gap:1rem">
          <div class="form-group"><label class="form-label">Jabatan</label><input type="text" name="jabatan" class="form-control" placeholder="Staff Sarpras"></div>
          <div class="form-group"><label class="form-label">No. WhatsApp</label><input type="text" name="no_wa" class="form-control" placeholder="6281234567890"><span class="form-text">Format: 628xxx (tanpa +)</span></div>
        </div>
        <div class="form-group">
          <label class="form-label">Level Akses <span class="required">*</span></label>
          <select name="level_akses" class="form-control" required>
            <option value="petugas">Petugas</option>
            <option value="admin">Admin</option>
            <option value="super_admin">Super Admin</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addPetugasModal')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editPetugasModal">
  <div class="modal">
    <div class="modal-header"><h4>✏️ Edit Petugas</h4><button class="modal-close" onclick="closeModal('editPetugasModal')">✕</button></div>
    <form method="POST" id="editPetugasForm">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="nama" id="ep_nama" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Email <span class="required">*</span></label><input type="email" name="email" id="ep_email" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Password Baru</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah"></div>
        <div class="grid grid-2" style="gap:1rem">
          <div class="form-group"><label class="form-label">Jabatan</label><input type="text" name="jabatan" id="ep_jabatan" class="form-control"></div>
          <div class="form-group"><label class="form-label">No. WhatsApp</label><input type="text" name="no_wa" id="ep_no_wa" class="form-control"></div>
        </div>
        <div class="grid grid-2" style="gap:1rem">
          <div class="form-group">
            <label class="form-label">Level Akses</label>
            <select name="level_akses" id="ep_level_akses" class="form-control">
              <option value="petugas">Petugas</option>
              <option value="admin">Admin</option>
              <option value="super_admin">Super Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Status Akun</label>
            <select name="status_akun" id="ep_status_akun" class="form-control">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editPetugasModal')">Batal</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal-header"><h4>🗑️ Hapus Petugas</h4><button class="modal-close" onclick="closeModal('deleteModal')">✕</button></div>
    <div class="modal-body"><div class="alert alert-danger"><span>⚠️</span><span>Hapus petugas <strong id="deleteName"></strong>?</span></div></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeModal('deleteModal')">Batal</button>
      <form method="POST" data-action-template="<?= base_url('admin/petugas/hapus/{id}') ?>" style="display:inline"><?= csrf_field() ?><button type="submit" class="btn btn-danger">Ya, Hapus</button></form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE_PT = document.querySelector('meta[name="base-url"]') ? document.querySelector('meta[name="base-url"]').getAttribute('content') : '<?= base_url() ?>';
function openEditPetugas(btn) {
  const d = btn.dataset;
  document.getElementById('ep_nama').value         = d.nama;
  document.getElementById('ep_email').value        = d.email;
  document.getElementById('ep_jabatan').value      = d.jabatan;
  document.getElementById('ep_no_wa').value        = d.no_wa;
  document.getElementById('ep_level_akses').value  = d.level_akses;
  document.getElementById('ep_status_akun').value  = d.status_akun;
  document.getElementById('editPetugasForm').action = BASE_PT + 'admin/petugas/update/' + d.id;
  openModal('editPetugasModal');
}
</script>
<?= $this->endSection() ?>
