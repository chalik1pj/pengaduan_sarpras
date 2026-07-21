<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<meta name="base-url" content="<?= base_url() ?>">

<div class="flex items-center gap-3 mb-5">
  <a href="<?= base_url('admin/barang') ?>" class="btn btn-ghost btn-sm">← Kembali</a>
  <div>
    <h2>Preview Cetak Inventaris Barang</h2>
    <p class="text-muted text-sm">Pilih ruangan yang ingin dicetak, lalu unduh dalam format yang diinginkan</p>
  </div>
</div>

<div class="preview-layout">

  <aside class="preview-sidebar card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
      <h4>🏢 Pilih Ruangan</h4>
      <span class="badge badge-proses" id="selectedCount">0 dipilih</span>
    </div>
    <div class="card-body" style="padding:1rem">

      <div class="form-group">
        <label class="form-label">Gedung</label>
        <select id="filterGedung" class="form-control">
          <option value="">-- Pilih Gedung --</option>
          <?php foreach ($gedung as $g): ?>
          <option value="<?= $g['id_gedung'] ?>"><?= esc($g['nama_gedung']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="flex gap-2 mb-3">
        <button id="btnSelectAll" class="btn btn-outline btn-sm flex-1" disabled>✅ Pilih Semua</button>
        <button id="btnClearAll"  class="btn btn-ghost  btn-sm flex-1" disabled>✕ Hapus Pilihan</button>
      </div>

      <div id="ruanganList" style="max-height:420px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.5rem">
        <div id="ruanganLoading" style="display:none;text-align:center;padding:1rem">
          <div class="spinner" style="width:24px;height:24px;border:3px solid var(--border);border-top-color:var(--primary-color);border-radius:50%;animation:spin 0.7s linear infinite;margin:0 auto 0.5rem"></div>
          <p class="text-muted text-sm">Memuat ruangan...</p>
        </div>
        <div id="ruanganItems">
          <p class="text-muted text-sm" style="text-align:center;padding:1rem">Pilih gedung terlebih dahulu</p>
        </div>
      </div>

      <hr style="margin:1rem 0;border-color:var(--border)">

      <button id="btnPreview" class="btn btn-primary btn-block" disabled>
        👁️ Tampilkan Preview
      </button>

      <hr style="margin:1rem 0;border-color:var(--border)">

      <p class="text-xs text-muted mb-2" style="text-align:center">Unduh ruangan yang dipilih:</p>
      <div style="display:flex;flex-direction:column;gap:0.5rem">
        <a id="btnPdf"  href="#" class="btn btn-danger  btn-sm disabled-link" target="_blank">
          📄 Download PDF
        </a>
        <a id="btnDocx" href="#" class="btn btn-outline btn-sm disabled-link">
          📝 Download DOCX
        </a>
        <a id="btnXlsx" href="#" class="btn btn-outline btn-sm disabled-link">
          📊 Download XLSX
        </a>
      </div>

    </div>
  </aside>

  <main class="preview-main">
    <div class="preview-toolbar card mb-3">
      <div class="card-body" style="padding:0.75rem 1rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
        <span id="previewInfo" class="text-muted text-sm">Belum ada ruangan dipilih</span>
        <div style="margin-left:auto;display:flex;gap:0.5rem">
          <button id="btnZoomOut" class="btn btn-ghost btn-sm" title="Zoom Out">🔍−</button>
          <span id="zoomLabel" class="text-sm" style="padding:0 0.5rem;line-height:2rem">100%</span>
          <button id="btnZoomIn"  class="btn btn-ghost btn-sm" title="Zoom In">🔍+</button>
        </div>
      </div>
    </div>

    <div class="preview-canvas-wrap">
      <div id="previewLoading" style="display:none;text-align:center;padding:3rem">
        <div class="spinner" style="width:40px;height:40px;border:4px solid var(--border);border-top-color:var(--primary-color);border-radius:50%;animation:spin 0.7s linear infinite;margin:0 auto 1rem"></div>
        <p class="text-muted">Memuat preview...</p>
      </div>
      <div id="previewEmpty" style="text-align:center;padding:4rem 2rem">
        <div style="font-size:4rem;margin-bottom:1rem">📋</div>
        <h3 style="color:var(--text-muted);font-weight:500;margin-bottom:0.5rem">Preview akan tampil di sini</h3>
        <p class="text-muted text-sm">Pilih gedung dan centang ruangan yang ingin dicetak,<br>lalu klik "Tampilkan Preview"</p>
      </div>
      <div id="previewCanvas" style="display:none">
      </div>
    </div>
  </main>

</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<style>

.preview-layout {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 1.5rem;
  align-items: start;
}
@media (max-width: 900px) {
  .preview-layout { grid-template-columns: 1fr; }
}
.preview-sidebar { position: sticky; top: 1rem; }
.preview-canvas-wrap {
  min-height: 600px;
  background: #e8e8e8;
  border-radius: var(--radius-md);
  padding: 1.5rem;
  overflow: auto;
}
@keyframes spin { to { transform: rotate(360deg); } }

.ruangan-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.5rem 0.6rem;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: background 0.15s;
  user-select: none;
}
.ruangan-item:hover { background: var(--bg-input); }
.ruangan-item input[type=checkbox] { accent-color: var(--primary-color); width:16px;height:16px; }
.ruangan-item .ri-name { font-size:0.875rem; font-weight:500; flex:1; }
.ruangan-item .ri-meta { font-size:0.75rem; color:var(--text-muted); }
.ruangan-item.checked { background: rgba(var(--primary-rgb,59,130,246),0.08); }

.disabled-link { pointer-events:none; opacity:0.4; }
.disabled-link.active-link { pointer-events:auto; opacity:1; }

#previewCanvas {
  transform-origin: top center;
  transition: transform 0.2s;
}
.preview-page {
  background: #fff;
  width: 210mm;
  min-height: 297mm;
  margin: 0 auto 2rem;
  padding: 1cm 1cm 1cm;
  box-shadow: 0 4px 24px rgba(0,0,0,0.18);
  font-family: "Times New Roman", Times, serif;
  font-size: 12pt;
  color: #111;
  box-sizing: border-box;
}

.kop-outer { position: relative; border-bottom: 3px double #111; margin-bottom: 14px; padding-bottom: 6px; min-height: 72px; }
.kop-logo-kiri { position: absolute; left: 0; top: 0; height: 68px; width: auto; z-index: 0; }
.kop-logo-kanan { position: absolute; right: 0; top: 4px; height: 50px; width: 140px; z-index: 0; }
.kop-center { position: relative; z-index: 1; text-align: center; padding: 0 80px; }
.kop-kampus { font-size: 15pt; font-weight: bold; line-height: 1.2; }
.kop-singkatan { font-size: 14pt; font-weight: bold; line-height: 1.2; }
.kop-sk { font-size: 9pt; margin-top: 3px; }
.kop-prodi { font-size: 8pt; margin-top: 2px; }

.doc-judul { text-align:center; font-size:14pt; font-weight:bold; margin:14px 0 10px; }
.doc-info { width:100%; border-collapse:collapse; margin-bottom:12px; }
.doc-info td { padding:2px 6px; font-size:12pt; }
.doc-info td.lbl { width:130px; font-weight:bold; }
.doc-info td.sep { width:15px; }

.doc-table { width:100%; border-collapse:collapse; margin-bottom:16px; font-size:11pt; }
.doc-table th { background:#d3d3d3; border:1px solid #111; padding:5px 6px; text-align:center; font-weight:bold; }
.doc-table td { border:1px solid #111; padding:4px 6px; }

.doc-footer-note { font-size:9pt; font-style:italic; text-align:center; margin-top:8px; }
.doc-ttd { width:100%; border-collapse:collapse; margin-top:4px; }
.doc-ttd td { text-align:center; font-size:11pt; }
</style>

<script>
const BASE = document.querySelector('meta[name="base-url"]').getAttribute('content');
let selectedIds = new Set();
let zoomLevel = 1;

const elGedung     = document.getElementById('filterGedung');
const elItems      = document.getElementById('ruanganItems');
const elLoading    = document.getElementById('ruanganLoading');
const elBtnAll     = document.getElementById('btnSelectAll');
const elBtnClear   = document.getElementById('btnClearAll');
const elBtnPreview = document.getElementById('btnPreview');
const elCanvas     = document.getElementById('previewCanvas');
const elEmpty      = document.getElementById('previewEmpty');
const elPrevLoad   = document.getElementById('previewLoading');
const elPrevInfo   = document.getElementById('previewInfo');
const elCount      = document.getElementById('selectedCount');

elGedung.addEventListener('change', function() {
  const gedungId = this.value;
  selectedIds.clear();
  updateUI();

  if (!gedungId) {
    elItems.innerHTML = '<p class="text-muted text-sm" style="text-align:center;padding:1rem">Pilih gedung terlebih dahulu</p>';
    elBtnAll.disabled = true;
    elBtnClear.disabled = true;
    return;
  }

  elLoading.style.display = 'block';
  elItems.innerHTML = '';

  fetch(BASE + 'api/ruangan/' + gedungId)
    .then(r => r.json())
    .then(data => {
      elLoading.style.display = 'none';
      if (!data || data.length === 0) {
        elItems.innerHTML = '<p class="text-muted text-sm" style="text-align:center;padding:1rem">Tidak ada ruangan pada gedung ini</p>';
        return;
      }
      let html = '';
      data.forEach(r => {
        html += `<label class="ruangan-item" id="ri-${r.id_ruangan}">
          <input type="checkbox" class="ruangan-cb" value="${r.id_ruangan}" onchange="onCbChange(this)">
          <span>
            <div class="ri-name">${esc(r.nama_ruangan)}</div>
            <div class="ri-meta">Lt. ${r.lantai} &bull; ${esc(r.tipe_ruangan)}</div>
          </span>
        </label>`;
      });
      elItems.innerHTML = html;
      elBtnAll.disabled   = false;
      elBtnClear.disabled = false;
    })
    .catch(() => {
      elLoading.style.display = 'none';
      elItems.innerHTML = '<p class="text-muted text-sm" style="text-align:center;padding:1rem;color:#ef4444">Gagal memuat ruangan</p>';
    });
});

function onCbChange(cb) {
  const id = parseInt(cb.value);
  if (cb.checked) {
    selectedIds.add(id);
    cb.closest('.ruangan-item').classList.add('checked');
  } else {
    selectedIds.delete(id);
    cb.closest('.ruangan-item').classList.remove('checked');
  }
  updateUI();
}
elBtnAll.addEventListener('click', () => {
  document.querySelectorAll('.ruangan-cb').forEach(cb => {
    cb.checked = true;
    selectedIds.add(parseInt(cb.value));
    cb.closest('.ruangan-item').classList.add('checked');
  });
  updateUI();
});
elBtnClear.addEventListener('click', () => {
  document.querySelectorAll('.ruangan-cb').forEach(cb => {
    cb.checked = false;
    cb.closest('.ruangan-item').classList.remove('checked');
  });
  selectedIds.clear();
  updateUI();
});

function updateUI() {
  const n = selectedIds.size;
  elCount.textContent = n + ' dipilih';
  elBtnPreview.disabled = n === 0;

  const qs = buildQueryString();
  const pdfUrl  = BASE + 'admin/barang/cetak/pdf?'  + qs;
  const docxUrl = BASE + 'admin/barang/cetak/docx?' + qs;
  const xlsxUrl = BASE + 'admin/barang/cetak/xlsx?' + qs;

  const btnPdf  = document.getElementById('btnPdf');
  const btnDocx = document.getElementById('btnDocx');
  const btnXlsx = document.getElementById('btnXlsx');

  if (n > 0) {
    btnPdf.href  = pdfUrl;  btnPdf.classList.add('active-link');  btnPdf.classList.remove('disabled-link');
    btnDocx.href = docxUrl; btnDocx.classList.add('active-link'); btnDocx.classList.remove('disabled-link');
    btnXlsx.href = xlsxUrl; btnXlsx.classList.add('active-link'); btnXlsx.classList.remove('disabled-link');
  } else {
    btnPdf.href  = '#'; btnPdf.classList.remove('active-link');  btnPdf.classList.add('disabled-link');
    btnDocx.href = '#'; btnDocx.classList.remove('active-link'); btnDocx.classList.add('disabled-link');
    btnXlsx.href = '#'; btnXlsx.classList.remove('active-link'); btnXlsx.classList.add('disabled-link');
  }
}

function buildQueryString() {
  return [...selectedIds].map(id => 'ruangan[]=' + id).join('&');
}

elBtnPreview.addEventListener('click', () => {
  if (selectedIds.size === 0) return;

  elEmpty.style.display   = 'none';
  elCanvas.style.display  = 'none';
  elPrevLoad.style.display = 'block';

  const qs = buildQueryString();
  fetch(BASE + 'admin/barang/cetak/preview-content?' + qs)
    .then(r => r.json())
    .then(data => {
      elPrevLoad.style.display = 'none';
      if (!data.html) {
        elEmpty.style.display = 'block';
        return;
      }
      elCanvas.innerHTML      = data.html;
      elCanvas.style.display  = 'block';
      elPrevInfo.textContent  = data.count + ' ruangan ditampilkan';
      applyZoom();
    })
    .catch(() => {
      elPrevLoad.style.display = 'none';
      elEmpty.style.display    = 'block';
      elPrevInfo.textContent   = 'Gagal memuat preview';
    });
});

function applyZoom() {
  elCanvas.style.transform = 'scale(' + zoomLevel + ')';
  document.getElementById('zoomLabel').textContent = Math.round(zoomLevel * 100) + '%';
  elCanvas.style.transformOrigin = 'top center';
}
document.getElementById('btnZoomIn').addEventListener('click', () => {
  zoomLevel = Math.min(2, zoomLevel + 0.1);
  applyZoom();
});
document.getElementById('btnZoomOut').addEventListener('click', () => {
  zoomLevel = Math.max(0.3, zoomLevel - 0.1);
  applyZoom();
});

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

(function() {
  const params = new URLSearchParams(window.location.search);
  const gedung = params.get('gedung');
  if (gedung) {
    elGedung.value = gedung;
    elGedung.dispatchEvent(new Event('change'));
  }
})();
</script>
<?= $this->endSection() ?>
