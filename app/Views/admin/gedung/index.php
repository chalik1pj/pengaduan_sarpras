<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
  <div><h2>Manajemen Gedung</h2><p class="text-muted text-sm"><?= count($items) ?> gedung terdaftar</p></div>
  <button class="btn btn-primary" onclick="openModal('addGedungModal')">➕ Tambah Gedung</button>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama Gedung</th><th>Keterangan</th><th>Dibuat</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (empty($items)): ?>
      <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">🏢</div><p>Belum ada data gedung</p></div></td></tr>
      <?php else: ?>
      <?php foreach ($items as $i => $item): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= esc($item['nama_gedung']) ?></strong></td>
        <td class="text-muted"><?= esc($item['keterangan']??'-') ?></td>
        <td class="text-xs text-muted"><?= date('d M Y', strtotime($item['created_at']??'now')) ?></td>
        <td style="vertical-align: middle;">
          <div class="table-actions">
            <button class="btn btn-outline btn-sm" onclick="openEditModal('editGedungModal', this)" data-id="<?= $item['id_gedung'] ?>" data-nama_gedung="<?= esc($item['nama_gedung']) ?>" data-keterangan="<?= esc($item['keterangan']??'') ?>">✏️ Edit</button>
            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $item['id_gedung'] ?>, '<?= esc($item['nama_gedung']) ?>')">🗑️ Hapus</button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addGedungModal">
  <div class="modal">
    <div class="modal-header"><h4>➕ Tambah Gedung</h4><button class="modal-close" onclick="closeModal('addGedungModal')">✕</button></div>
    <form action="<?= base_url('admin/gedung/simpan') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Nama Gedung <span class="required">*</span></label><input type="text" name="nama_gedung" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="3"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addGedungModal')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editGedungModal">
  <div class="modal">
    <div class="modal-header"><h4>✏️ Edit Gedung</h4><button class="modal-close" onclick="closeModal('editGedungModal')">✕</button></div>
    <form method="POST" data-action-template="<?= base_url('admin/gedung/update/{id}') ?>">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Nama Gedung <span class="required">*</span></label><input type="text" name="nama_gedung" id="edit_nama_gedung" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Keterangan</label><textarea name="keterangan" id="edit_keterangan" class="form-control" rows="3"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editGedungModal')">Batal</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal-header"><h4>🗑️ Hapus Gedung</h4><button class="modal-close" onclick="closeModal('deleteModal')">✕</button></div>
    <div class="modal-body">
      <div class="alert alert-danger"><span class="alert-icon">⚠️</span><span>Apakah Anda yakin ingin menghapus <strong id="deleteName"></strong>? Semua ruangan dan barang terkait akan ikut terhapus.</span></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeModal('deleteModal')">Batal</button>
      <form method="POST" id="deleteForm" data-action-template="<?= base_url('admin/gedung/hapus/{id}') ?>" style="display:inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
