<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<meta name="base-url" content="<?= base_url() ?>">

<div class="flex justify-between items-center mb-4">
  <div><h2>Barang / Fasilitas</h2><p class="text-muted text-sm"><?= count($items) ?> item terdaftar</p></div>
  <div class="flex gap-2 flex-wrap">
    <?php if (session()->get('admin_level') === 'super_admin'): ?>
    <?php
      $previewQuery = http_build_query(array_filter(['gedung' => $filterGedung]));
      $previewUrl   = base_url('admin/barang/cetak/preview') . ($previewQuery ? '?'.$previewQuery : '');
    ?>
    <a href="<?= $previewUrl ?>" class="btn btn-outline btn-sm" style="display:flex;align-items:center;gap:0.4rem">
      🖨️ Preview &amp; Cetak
    </a>
    <?php endif; ?>
    <button class="btn btn-primary" onclick="openModal('addBarangModal')">➕ Tambah Barang</button>
  </div>
</div>

<!-- Cascading Filter -->
<div class="card mb-4">
  <div class="card-body" style="padding:1rem">
    <form method="GET" class="filter-bar flex-wrap">
      <select id="filter_gedung_select" name="gedung" class="form-control" style="width:auto" onchange="this.form.submit()">
        <option value="">-- Semua Gedung --</option>
        <?php foreach ($gedung as $g): ?>
        <option value="<?= $g['id_gedung'] ?>" <?= $filterGedung==$g['id_gedung']?'selected':'' ?>><?= esc($g['nama_gedung']) ?></option>
        <?php endforeach; ?>
      </select>
      <select id="filter_ruangan_select" name="ruangan" class="form-control" style="width:auto" onchange="this.form.submit()">
        <option value="">-- Semua Ruangan --</option>
        <?php if (!empty($ruanganList)): ?>
          <?php foreach ($ruanganList as $r): ?>
          <option value="<?= $r['id_ruangan'] ?>" <?= $filterRuangan==$r['id_ruangan']?'selected':'' ?>>
            Lt.<?= $r['lantai'] ?> - <?= esc($r['nama_ruangan']) ?>
          </option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Cari nama barang...">
      </div>
      <button type="submit" class="btn btn-outline btn-sm">🔍 Filter</button>
      <a href="<?= base_url('admin/barang') ?>" class="btn btn-ghost btn-sm">✕ Reset</a>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Gedung</th><th>Ruangan</th><th>Nama Barang</th><th>Merek/Ukuran</th><th>Jumlah</th><th>Kondisi</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (empty($items)): ?>
      <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📦</div><p>Tidak ada barang ditemukan</p></div></td></tr>
      <?php else: ?>
      <?php foreach ($items as $i => $item): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td class="text-sm"><?= esc($item['nama_gedung']) ?></td>
        <td class="text-sm"><?= esc($item['nama_ruangan']) ?></td>
        <td><strong><?= esc($item['nama_barang']) ?></strong></td>
        <td class="text-muted text-sm"><?= esc($item['merek_ukuran']??'-') ?></td>
        <td class="text-center"><?= $item['jumlah'] ?></td>
        <td><span class="badge <?= $item['kondisi']==='Baik dan Layak'?'badge-selesai':'badge-ditolak' ?>"><?= esc($item['kondisi']) ?></span></td>
        <td style="vertical-align: middle;">
          <div class="table-actions">
            <button class="btn btn-outline btn-sm" onclick="openEditBarang(this)" data-id="<?= $item['id_barang'] ?>" data-id_gedung="<?= $item['id_gedung'] ?>" data-id_ruangan="<?= $item['id_ruangan'] ?>" data-nama_barang="<?= esc($item['nama_barang']) ?>" data-merek_ukuran="<?= esc($item['merek_ukuran']??'') ?>" data-jumlah="<?= $item['jumlah'] ?>" data-kondisi="<?= esc($item['kondisi']) ?>">✏️</button>
            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $item['id_barang'] ?>,'<?= esc($item['nama_barang']) ?>')">🗑️</button>
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
<div class="modal-overlay" id="addBarangModal">
  <div class="modal">
    <div class="modal-header"><h4>➕ Tambah Barang</h4><button class="modal-close" onclick="closeModal('addBarangModal')">✕</button></div>
    <form action="<?= base_url('admin/barang/simpan') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Gedung <span class="required">*</span></label>
          <select id="modal_id_gedung" class="form-control">
            <option value="">-- Pilih Gedung --</option>
            <?php foreach ($gedung as $g): ?>
            <option value="<?= $g['id_gedung'] ?>"><?= esc($g['nama_gedung']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Ruangan <span class="required">*</span></label>
          <select id="modal_id_ruangan" name="id_ruangan" class="form-control" required><option value="">-- Pilih Gedung dulu --</option></select>
        </div>
        <div class="form-group"><label class="form-label">Nama Barang <span class="required">*</span></label><input type="text" name="nama_barang" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Merek/Ukuran</label><input type="text" name="merek_ukuran" class="form-control"></div>
        <div class="grid grid-2" style="gap:1rem">
          <div class="form-group"><label class="form-label">Jumlah <span class="required">*</span></label><input type="number" name="jumlah" class="form-control" min="1" value="1" required></div>
          <div class="form-group">
            <label class="form-label">Kondisi <span class="required">*</span></label>
            <select name="kondisi" class="form-control" required>
              <option value="Baik dan Layak">Baik dan Layak</option>
              <option value="Perlu Perbaikan">Perlu Perbaikan</option>
              <option value="Rusak">Rusak</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addBarangModal')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editBarangModal">
  <div class="modal">
    <div class="modal-header"><h4>✏️ Edit Barang</h4><button class="modal-close" onclick="closeModal('editBarangModal')">✕</button></div>
    <form method="POST" id="editBarangForm">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Ruangan (ID)</label>
          <input type="number" name="id_ruangan" id="edit_barang_ruangan" class="form-control" required readonly style="opacity:0.6">
          <span class="form-text">Untuk memindahkan barang ke ruangan lain, hapus dan tambah ulang.</span>
        </div>
        <div class="form-group"><label class="form-label">Nama Barang</label><input type="text" name="nama_barang" id="edit_barang_nama" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Merek/Ukuran</label><input type="text" name="merek_ukuran" id="edit_barang_merek" class="form-control"></div>
        <div class="grid grid-2" style="gap:1rem">
          <div class="form-group"><label class="form-label">Jumlah</label><input type="number" name="jumlah" id="edit_barang_jumlah" class="form-control" min="1"></div>
          <div class="form-group">
            <label class="form-label">Kondisi</label>
            <select name="kondisi" id="edit_barang_kondisi" class="form-control">
              <option value="Baik dan Layak">Baik dan Layak</option>
              <option value="Perlu Perbaikan">Perlu Perbaikan</option>
              <option value="Rusak">Rusak</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editBarangModal')">Batal</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal-header"><h4>🗑️ Hapus Barang</h4><button class="modal-close" onclick="closeModal('deleteModal')">✕</button></div>
    <div class="modal-body"><div class="alert alert-danger"><span>⚠️</span><span>Hapus <strong id="deleteName"></strong>?</span></div></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeModal('deleteModal')">Batal</button>
      <form method="POST" data-action-template="<?= base_url('admin/barang/hapus/{id}') ?>" style="display:inline"><?= csrf_field() ?><button type="submit" class="btn btn-danger">Ya, Hapus</button></form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function openEditBarang(btn) {
  const d = btn.dataset;
  const BASE = document.querySelector('meta[name="base-url"]').getAttribute('content');
  document.getElementById('edit_barang_ruangan').value = d.id_ruangan;
  document.getElementById('edit_barang_nama').value    = d.nama_barang;
  document.getElementById('edit_barang_merek').value   = d.merek_ukuran;
  document.getElementById('edit_barang_jumlah').value  = d.jumlah;
  document.getElementById('edit_barang_kondisi').value = d.kondisi;
  document.getElementById('editBarangForm').action = BASE + 'admin/barang/update/' + d.id;
  openModal('editBarangModal');
}
</script>
<?= $this->endSection() ?>
