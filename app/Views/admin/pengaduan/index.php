<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-4">
  <h2>Manajemen Pengaduan</h2>
</div>

<!-- Filter bar -->
<div class="card mb-4">
  <div class="card-body" style="padding:1rem">
    <form method="GET" action="" class="filter-bar">
      <button type="button" class="filter-btn <?= empty($filterStatus)?'active':'' ?>" onclick="filterByStatus('')">Semua (<?= count($items) ?>)</button>
      <?php foreach ($statuses as $s):
        $counts = array_filter($items, fn($i) => $i['id_status_saat_ini'] == $s['id_status']);
      ?>
      <button type="button" class="filter-btn <?= $filterStatus==$s['id_status']?'active':'' ?>" onclick="filterByStatus(<?= $s['id_status'] ?>)">
        <?= esc($s['nama_status']) ?> (<?= count($counts) ?>)
      </button>
      <?php endforeach; ?>
      <div class="search-box ms-auto">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Cari kode/judul/mahasiswa...">
      </div>
      <button type="submit" class="btn btn-outline btn-sm">🔍 Cari</button>
    </form>
  </div>
</div>

<div class="card">
  <?php if (empty($items)): ?>
  <div class="empty-state"><div class="empty-icon">📭</div><h3>Tidak ada pengaduan</h3><p>Tidak ada data yang sesuai dengan filter</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Mahasiswa</th><th>Judul</th><th>Lokasi</th><th>Kategori</th><th>Prioritas</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($items as $item):
        $sCls=['Menunggu Verifikasi'=>'badge-menunggu','Diverifikasi'=>'badge-verifikasi','Diproses'=>'badge-proses','Selesai'=>'badge-selesai','Ditolak'=>'badge-ditolak'];
        $pCls=['rendah'=>'badge-rendah','sedang'=>'badge-sedang','tinggi'=>'badge-tinggi'];
      ?>
      <tr>
        <td><strong class="text-xs text-primary-color"><?= esc($item['kode_pengaduan']) ?></strong></td>
        <td class="text-sm"><?= esc($item['nama_mahasiswa']) ?><br><span class="text-xs text-muted"><?= esc($item['nim']) ?></span></td>
        <td style="max-width:180px"><strong><?= esc($item['judul']) ?></strong></td>
        <td class="text-sm"><?= esc($item['nama_gedung']) ?><br><span class="text-xs text-muted"><?= esc($item['nama_ruangan']) ?></span></td>
        <td class="text-sm"><?= esc($item['nama_kategori']) ?></td>
        <td><span class="badge <?= $pCls[$item['prioritas']]??'' ?>"><?= ucfirst($item['prioritas']) ?></span></td>
        <td><span class="badge <?= $sCls[$item['nama_status']]??'' ?>"><?= esc($item['nama_status']) ?></span></td>
        <td class="text-xs text-muted"><?= date('d M Y', strtotime($item['tanggal_pengaduan'])) ?></td>
        <td><a href="<?= base_url('admin/pengaduan/'.$item['id_pengaduan']) ?>" class="btn btn-primary btn-sm">Detail</a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
