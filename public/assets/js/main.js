/**
 * Sistem Pengaduan Fasilitas Kampus
 * Main JavaScript — Sidebar, Cascading Dropdown, Upload Preview, UI
 */

// ============================================================
// SIDEBAR TOGGLE
// ============================================================
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (sidebar) {
    sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('active');
  }
}

function closeSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (sidebar) sidebar.classList.remove('open');
  if (overlay) overlay.classList.remove('active');
}

// Close sidebar on resize (desktop)
window.addEventListener('resize', function () {
  if (window.innerWidth > 768) closeSidebar();
});

// ============================================================
// MODAL
// ============================================================
function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.remove('active');
  document.body.style.overflow = '';
}

// Close modal when clicking overlay
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
    document.body.style.overflow = '';
  }
});

// ESC key closes modals
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.active').forEach(function (m) {
      m.classList.remove('active');
    });
    document.body.style.overflow = '';
  }
});

// ============================================================
// CASCADING DROPDOWN — Gedung → Ruangan → Barang
// ============================================================
const BASE_URL = document.querySelector('meta[name="base-url"]')
  ? document.querySelector('meta[name="base-url"]').getAttribute('content')
  : window.location.origin + '/pengaduan_sarpras/public/';

function initCascadingDropdown() {
  const selGedung  = document.getElementById('id_gedung');
  const selRuangan = document.getElementById('id_ruangan');
  const selBarang  = document.getElementById('id_barang');

  if (!selGedung) return;

  selGedung.addEventListener('change', function () {
    const gedungId = this.value;

    // Reset downstream
    resetSelect(selRuangan, '-- Pilih Ruangan --');
    if (selBarang) resetSelect(selBarang, '-- Pilih Barang (opsional) --');

    if (!gedungId) return;

    setLoading(selRuangan, true);

    fetch(BASE_URL + 'api/ruangan/' + gedungId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        setLoading(selRuangan, false);
        populateSelect(selRuangan, data, 'id_ruangan', function (item) {
          return 'Lantai ' + item.lantai + ' — ' + item.nama_ruangan + ' (' + item.tipe_ruangan + ')';
        });
      })
      .catch(function (err) {
        setLoading(selRuangan, false);
        console.error('Error loading ruangan:', err);
      });
  });

  if (selRuangan) {
    selRuangan.addEventListener('change', function () {
      const ruanganId = this.value;

      if (selBarang) resetSelect(selBarang, '-- Pilih Barang (opsional) --');

      if (!ruanganId) return;

      if (selBarang) {
        setLoading(selBarang, true);

        fetch(BASE_URL + 'api/barang/' + ruanganId)
          .then(function (r) { return r.json(); })
          .then(function (data) {
            setLoading(selBarang, false);
            populateSelect(selBarang, data, 'id_barang', function (item) {
              return item.nama_barang + (item.merek_ukuran ? ' (' + item.merek_ukuran + ')' : '') + ' — Jml: ' + item.jumlah;
            }, true); // true = add empty option at start
          })
          .catch(function (err) {
            setLoading(selBarang, false);
            console.error('Error loading barang:', err);
          });
      }
    });
  }
}

// Admin: filter ruangan by gedung (for barang page)
function initAdminGedungFilter() {
  const selGedung  = document.getElementById('filter_gedung_barang');
  const selRuangan = document.getElementById('filter_ruangan_barang');
  if (!selGedung || !selRuangan) return;

  selGedung.addEventListener('change', function () {
    const gedungId = this.value;
    resetSelect(selRuangan, '-- Semua Ruangan --');
    if (!gedungId) return;

    fetch(BASE_URL + 'api/ruangan/' + gedungId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        populateSelect(selRuangan, data, 'id_ruangan', function (item) {
          return 'Lt.' + item.lantai + ' - ' + item.nama_ruangan;
        }, true);
      });
  });
}

// Admin: modal gedung→ruangan for add/edit barang
function initModalGedungRuangan() {
  const selGedung  = document.getElementById('modal_id_gedung');
  const selRuangan = document.getElementById('modal_id_ruangan');
  if (!selGedung || !selRuangan) return;

  selGedung.addEventListener('change', function () {
    const gedungId = this.value;
    resetSelect(selRuangan, '-- Pilih Ruangan --');
    if (!gedungId) return;

    setLoading(selRuangan, true);
    fetch(BASE_URL + 'api/ruangan/' + gedungId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        setLoading(selRuangan, false);
        populateSelect(selRuangan, data, 'id_ruangan', function (item) {
          return 'Lt.' + item.lantai + ' — ' + item.nama_ruangan;
        });
      })
      .catch(function () { setLoading(selRuangan, false); });
  });
}

// ============================================================
// SELECT HELPERS
// ============================================================
function resetSelect(sel, label) {
  if (!sel) return;
  sel.innerHTML = '<option value="">' + label + '</option>';
  sel.disabled = false;
}

function populateSelect(sel, data, valueKey, labelFn, hasEmpty) {
  if (!sel) return;
  sel.innerHTML = '';
  if (hasEmpty) {
    sel.innerHTML = '<option value="">-- Tidak spesifik --</option>';
  }
  data.forEach(function (item) {
    const opt = document.createElement('option');
    opt.value = item[valueKey];
    opt.textContent = labelFn(item);
    sel.appendChild(opt);
  });
  sel.disabled = data.length === 0;
}

function setLoading(sel, loading) {
  if (!sel) return;
  if (loading) {
    sel.classList.add('loading');
    sel.disabled = true;
  } else {
    sel.classList.remove('loading');
    sel.disabled = false;
  }
}

// ============================================================
// MULTI-IMAGE UPLOAD PREVIEW
// ============================================================
function initUploadPreview() {
  const input   = document.getElementById('foto_bukti');
  const area    = document.getElementById('uploadArea');
  const preview = document.getElementById('uploadPreview');
  const counter = document.getElementById('uploadCounter');
  const MAX     = 3;
  const MAX_MB  = 1; // 1MB per file

  if (!input || !area) return;

  // Click area to trigger input
  area.addEventListener('click', function () { input.click(); });

  // Drag and drop
  area.addEventListener('dragover', function (e) {
    e.preventDefault();
    area.classList.add('drag-over');
  });
  area.addEventListener('dragleave', function () { area.classList.remove('drag-over'); });
  area.addEventListener('drop', function (e) {
    e.preventDefault();
    area.classList.remove('drag-over');
    handleFiles(e.dataTransfer.files);
  });

  input.addEventListener('change', function () {
    handleFiles(this.files);
  });

  let selectedFiles = [];

  function handleFiles(files) {
    let errors = [];
    Array.from(files).forEach(function (file) {
      if (selectedFiles.length >= MAX) {
        errors.push('Maksimal ' + MAX + ' foto yang diizinkan.');
        return;
      }
      if (!file.type.startsWith('image/')) {
        errors.push(file.name + ' bukan file gambar.');
        return;
      }
      if (file.size > MAX_MB * 1024 * 1024) {
        errors.push(file.name + ' melebihi ' + MAX_MB + 'MB.');
        return;
      }
      selectedFiles.push(file);
    });

    if (errors.length > 0) {
      showToast(errors.join(' '), 'error');
    }

    updatePreview();
    updateInputFiles();
  }

  function updatePreview() {
    if (!preview) return;
    preview.innerHTML = '';
    if (counter) counter.textContent = selectedFiles.length + '/' + MAX + ' foto';

    selectedFiles.forEach(function (file, idx) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const div = document.createElement('div');
        div.className = 'upload-preview-item';
        div.innerHTML = '<img src="' + e.target.result + '" alt="Preview"><button type="button" class="remove-btn" onclick="removeFile(' + idx + ')">✕</button>';
        preview.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  }

  function updateInputFiles() {
    const dt = new DataTransfer();
    selectedFiles.forEach(function (f) { dt.items.add(f); });
    input.files = dt.files;
  }

  window.removeFile = function (idx) {
    selectedFiles.splice(idx, 1);
    updatePreview();
    updateInputFiles();
  };
}

// ============================================================
// LIGHTBOX (untuk foto bukti)
// ============================================================
function initLightbox() {
  const lightbox = document.getElementById('lightbox');
  const img      = document.getElementById('lightboxImg');
  if (!lightbox || !img) return;

  document.querySelectorAll('.photo-item[data-src]').forEach(function (item) {
    item.addEventListener('click', function () {
      img.src = this.getAttribute('data-src');
      lightbox.classList.add('active');
    });
  });

  lightbox.addEventListener('click', function (e) {
    if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
      lightbox.classList.remove('active');
    }
  });
}

// ============================================================
// TOAST NOTIFICATIONS
// ============================================================
function showToast(message, type) {
  type = type || 'info';
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
  const toast = document.createElement('div');
  toast.className = 'toast toast-' + type;
  toast.innerHTML = '<span>' + (icons[type] || '') + '</span><span>' + message + '</span>';
  container.appendChild(toast);

  setTimeout(function () {
    toast.style.animation = 'fadeIn 0.3s reverse';
    setTimeout(function () { toast.remove(); }, 300);
  }, 4000);
}

// ============================================================
// CONFIRM DELETE
// ============================================================
function confirmDelete(formId, msg) {
  msg = msg || 'Apakah Anda yakin ingin menghapus data ini?';
  if (confirm(msg)) {
    document.getElementById(formId).submit();
  }
}

// ============================================================
// AUTO-DISMISS ALERTS
// ============================================================
function initAutoDismissAlerts() {
  document.querySelectorAll('.alert').forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = 'opacity 0.5s';
      alert.style.opacity = '0';
      setTimeout(function () { alert.remove(); }, 500);
    }, 5000);
  });
}

// ============================================================
// TOGGLE PASSWORD VISIBILITY
// ============================================================
function togglePassword(inputId, icon) {
  const input = document.getElementById(inputId);
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
}

// ============================================================
// TABLE SEARCH (client-side)
// ============================================================
function initTableSearch(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;

  input.addEventListener('input', function () {
    const term = this.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(function (row) {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(term) ? '' : 'none';
    });
  });
}

// ============================================================
// INIT ALL
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  initCascadingDropdown();
  initAdminGedungFilter();
  initModalGedungRuangan();
  initUploadPreview();
  initLightbox();
  initAutoDismissAlerts();
});
