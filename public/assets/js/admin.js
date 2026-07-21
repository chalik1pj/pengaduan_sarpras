/**
 * Admin-specific JavaScript
 */

// ============================================================
// EDIT MODAL PRE-POPULATION
// ============================================================

/**
 * Populate an edit modal with data attributes from a button
 * Usage: <button onclick="openEditModal('editGedung', this)" data-id="1" data-nama="Gedung 1">
 */
function openEditModal(modalId, btn) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  const data = btn ? btn.dataset : {};

  // Set all data attributes as input values inside the modal
  Object.keys(data).forEach(function (key) {
    const field = modal.querySelector('[name="' + key + '"]') || modal.querySelector('#edit_' + key);
    if (field) field.value = data[key];
  });

  // Update form action if id is present
  if (data.id) {
    const form = modal.querySelector('form[data-action-template]');
    if (form) {
      const template = form.getAttribute('data-action-template');
      form.action = template.replace('{id}', data.id);
    }
  }

  openModal(modalId);
}

/**
 * Open delete confirmation modal
 */
function openDeleteModal(id, name) {
  const modal = document.getElementById('deleteModal');
  if (!modal) return;

  const nameEl = modal.querySelector('#deleteName');
  if (nameEl) nameEl.textContent = name || 'item ini';

  const form = modal.querySelector('form[data-action-template]');
  if (form) {
    const template = form.getAttribute('data-action-template');
    form.action = template.replace('{id}', id);
  }

  openModal('deleteModal');
}

// ============================================================
// CHART.JS HELPERS
// ============================================================
function createDonutChart(canvasId, labels, data, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;

  new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: data,
        backgroundColor: colors || ['#6366f1', '#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
        borderColor: '#1e293b',
        borderWidth: 3,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#94a3b8',
            padding: 16,
            font: { size: 12 },
          }
        }
      },
      cutout: '65%',
    }
  });
}

function createBarChart(canvasId, labels, data, label) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: label || 'Pengaduan',
        data: data,
        backgroundColor: 'rgba(99,102,241,0.6)',
        borderColor: '#6366f1',
        borderWidth: 2,
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          ticks: { color: '#94a3b8' },
          grid: { color: 'rgba(148,163,184,0.08)' },
        },
        y: {
          ticks: { color: '#94a3b8', precision: 0 },
          grid: { color: 'rgba(148,163,184,0.08)' },
          beginAtZero: true,
        }
      }
    }
  });
}

// ============================================================
// TOGGLE STATUS FILTER (Admin Pengaduan)
// ============================================================
function filterByStatus(status) {
  const url = new URL(window.location.href);
  if (status) {
    url.searchParams.set('status', status);
  } else {
    url.searchParams.delete('status');
  }
  window.location.href = url.toString();
}

// ============================================================
// ADMIN INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  // Activate filter buttons based on URL param
  const urlParams = new URLSearchParams(window.location.search);
  const currentStatus = urlParams.get('status');
  document.querySelectorAll('.filter-btn[data-status]').forEach(function (btn) {
    const val = btn.getAttribute('data-status');
    if (val === currentStatus || (!currentStatus && val === '')) {
      btn.classList.add('active');
    }
  });
});
