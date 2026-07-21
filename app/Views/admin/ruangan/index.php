<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<meta name="base-url" content="<?= base_url() ?>">

<div class="flex justify-between items-center mb-4">
  <div><h2>Manajemen Ruangan</h2><p class="text-muted text-sm"><?= count($items) ?> ruangan terdaftar</p></div>
  <button class="btn btn-primary" onclick="openModal('addRuanganModal')">➕ Tambah Ruangan</button>
</div>

<!-- Filter -->
<div class="card mb-4">
  <div class="card-body" style="padding:1rem">
    <form method="GET" class="filter-bar">
      <select name="gedung" class="form-control" style="width:auto" onchange="this.form.submit()">
        <option value="">-- Semua Gedung --</option>
        <?php foreach ($gedung as $g): ?>
        <option value="<?= $g['id_gedung'] ?>" <?= $filterGedung==$g['id_gedung']?'selected':'' ?>><?= esc($g['nama_gedung']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Cari ruangan...">
      </div>
      <button type="submit" class="btn btn-outline btn-sm">🔍</button>
      <?php if ($filterGedung || $search): ?>
      <a href="<?= base_url('admin/ruangan') ?>" class="btn btn-ghost btn-sm">✕ Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Gedung</th><th>Nama Ruangan</th><th>Tipe</th><th>Lantai</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (empty($items)): ?>
      <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🚪</div><p>Tidak ada ruangan ditemukan</p></div></td></tr>
      <?php else: ?>
      <?php foreach ($items as $i => $item): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= esc($item['nama_gedung']) ?></strong></td>
        <td><?= esc($item['nama_ruangan']) ?></td>
        <td class="text-sm text-muted"><?= esc($item['tipe_ruangan']) ?></td>
        <td class="text-center"><span class="badge badge-proses">Lt. <?= $item['lantai'] ?></span></td>
        <td style="vertical-align: middle;">
          <div class="table-actions">
            <button class="btn btn-outline btn-sm" onclick="openEditModal('editRuanganModal',this)" data-id="<?= $item['id_ruangan'] ?>" data-nama_ruangan="<?= esc($item['nama_ruangan']) ?>" data-tipe_ruangan="<?= esc($item['tipe_ruangan']) ?>" data-lantai="<?= $item['lantai'] ?>" data-id_gedung="<?= $item['id_gedung'] ?>">✏️ Edit</button>
            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $item['id_ruangan'] ?>,'<?= esc($item['nama_ruangan']) ?>')">🗑️</button>
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
<div class="modal-overlay" id="addRuanganModal">
  <div class="modal">
    <div class="modal-header"><h4>➕ Tambah Ruangan</h4><button class="modal-close" onclick="closeModal('addRuanganModal')">✕</button></div>
    <form action="<?= base_url('admin/ruangan/simpan') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Gedung <span class="required">*</span></label>
          <select name="id_gedung" class="form-control" required>
            <option value="">-- Pilih Gedung --</option>
            <?php foreach ($gedung as $g): ?>
            <option value="<?= $g['id_gedung'] ?>"><?= esc($g['nama_gedung']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Nama Ruangan <span class="required">*</span></label><input type="text" name="nama_ruangan" class="form-control" required></div>
        <div class="form-group">
          <label class="form-label">Tipe Ruangan <span class="required">*</span></label>
          <select name="tipe_ruangan" class="form-control" required>
            <?php foreach(['Ruang Kelas','Ruang Laboratorium Komputer','Ruang Staff','Ruang Pimpinan','Ruang Seminar','Ruang Receptionist','Ruang Kasir','Ruang Dapur','Ruang Istirahat','Ruang Server','Ruang Kuliah','Lainnya'] as $t): ?>
            <option value="<?= $t ?>"><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Lantai <span class="required">*</span></label><input type="number" name="lantai" class="form-control" min="1" max="20" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addRuanganModal')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editRuanganModal">
  <div class="modal">
    <div class="modal-header"><h4>✏️ Edit Ruangan</h4><button class="modal-close" onclick="closeModal('editRuanganModal')">✕</button></div>
    <form method="POST" data-action-template="<?= base_url('admin/ruangan/update/{id}') ?>">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Gedung <span class="required">*</span></label>
          <select name="id_gedung" id="edit_id_gedung" class="form-control" required>
            <?php foreach ($gedung as $g): ?>
            <option value="<?= $g['id_gedung'] ?>"><?= esc($g['nama_gedung']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Nama Ruangan <span class="required">*</span></label><input type="text" name="nama_ruangan" id="edit_nama_ruangan" class="form-control" required></div>
        <div class="form-group">
          <label class="form-label">Tipe Ruangan</label>
          <select name="tipe_ruangan" id="edit_tipe_ruangan" class="form-control">
            <?php foreach(['Ruang Kelas','Ruang Laboratorium Komputer','Ruang Staff','Ruang Pimpinan','Ruang Seminar','Ruang Receptionist','Ruang Kasir','Ruang Dapur','Ruang Istirahat','Ruang Server','Ruang Kuliah','Lainnya'] as $t): ?>
            <option value="<?= $t ?>"><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Lantai</label><input type="number" name="lantai" id="edit_lantai" class="form-control" min="1" max="20"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editRuanganModal')">Batal</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal-header"><h4>🗑️ Hapus Ruangan</h4><button class="modal-close" onclick="closeModal('deleteModal')">✕</button></div>
    <div class="modal-body"><div class="alert alert-danger"><span>⚠️</span><span>Hapus <strong id="deleteName"></strong>? Semua barang terkait akan ikut terhapus.</span></div></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeModal('deleteModal')">Batal</button>
      <form method="POST" data-action-template="<?= base_url('admin/ruangan/hapus/{id}') ?>" style="display:inline"><?= csrf_field() ?><button type="submit" class="btn btn-danger">Ya, Hapus</button></form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
// Override openEditModal to also set tipe_ruangan select value
const _orig = window.openEditModal;
window.openEditModal = function(modalId, btn) {
  _orig(modalId, btn);
  if (modalId === 'editRuanganModal' && btn) {
    const tipe = document.getElementById('edit_tipe_ruangan');
    if (tipe) tipe.value = btn.dataset.tipe_ruangan || '';
  }
};
</script>
<?= $this->endSection() ?>
