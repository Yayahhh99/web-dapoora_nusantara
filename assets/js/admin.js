/**
 * assets/js/admin.js
 * Dapoora Nusantara — Admin Dashboard JavaScript (ES6, no jQuery)
 */

document.addEventListener('DOMContentLoaded', () => {

  // ─── Sidebar toggle (collapse/expand) ─────────────────────
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar       = document.getElementById('sidebar');
  const adminMain     = document.getElementById('adminMain');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      // Tablet/mobile: overlay effect
      if (window.innerWidth <= 900) {
        if (sidebar.classList.contains('open')) {
          // Tambah backdrop
          let bd = document.getElementById('sidebarBackdrop');
          if (!bd) {
            bd = document.createElement('div');
            bd.id = 'sidebarBackdrop';
            bd.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999';
            document.body.appendChild(bd);
            bd.addEventListener('click', () => {
              sidebar.classList.remove('open');
              bd.remove();
            });
          }
        } else {
          document.getElementById('sidebarBackdrop')?.remove();
        }
      }
    });
  }

  // ─── Image preview sebelum upload ─────────────────────────
  document.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
    input.addEventListener('change', function () {
      // Cari elemen preview terdekat
      const wrap = this.parentElement.querySelector('.img-preview-wrap')
                || document.getElementById('previewWrap');
      const img  = wrap?.querySelector('img') || document.getElementById('imgPreview');

      if (wrap && img && this.files?.[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          img.src = e.target.result;
          wrap.classList.add('show');
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  });

  // ─── Alert auto-dismiss ────────────────────────────────────
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .5s';
      alert.style.opacity    = '0';
      setTimeout(() => alert.remove(), 500);
    }, 4500);
  });

  // ─── Konfirmasi hapus resep (global) ──────────────────────
  // Fungsi global confirmHapus sudah inline di masing-masing halaman

  // ─── Toggle status pengguna via fetch ─────────────────────
  document.querySelectorAll('.toggle-status[data-id]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const userId = btn.dataset.id;
      const base   = getBaseUrl();

      try {
        const res  = await fetch(`${base}/process/user_process.php`, {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ action: 'toggle_status', user_id: userId })
        });
        const data = await res.json();

        if (data.success) {
          const isAktif = data.new_status === 'aktif';
          btn.textContent = isAktif ? 'Aktif' : 'Nonaktif';
          btn.className   = `badge badge-${isAktif ? 'success' : 'gray'} toggle-status`;
          btn.dataset.status = data.new_status;
          showToast(isAktif ? '✅ User diaktifkan' : '🔒 User dinonaktifkan');
        }
      } catch (err) {
        console.error('Toggle status error:', err);
      }
    });
  });

  // ─── Update status komentar via fetch ─────────────────────
  document.querySelectorAll('.update-komentar[data-id]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const base = getBaseUrl();
      try {
        const res  = await fetch(`${base}/process/comment_process.php`, {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({
            action:     'update_status',
            comment_id: btn.dataset.id,
            status:     btn.dataset.status
          })
        });
        const data = await res.json();
        if (data.success) location.reload();
      } catch (err) {
        console.error('Update komentar error:', err);
      }
    });
  });

  // ─── Hapus komentar konfirmasi ─────────────────────────────
  document.querySelectorAll('.hapus-komentar[data-id]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (confirm('Hapus komentar ini secara permanen?')) {
        document.getElementById('hapusKomId').value = btn.dataset.id;
        document.getElementById('hapusKomForm').submit();
      }
    });
  });

  // ─── Sidebar active link ───────────────────────────────────
  const currentPath = window.location.pathname;
  document.querySelectorAll('.sidebar-nav a').forEach(link => {
    if (link.getAttribute('href') && currentPath.endsWith(link.getAttribute('href').split('/').pop())) {
      link.classList.add('active');
    }
  });

  // ─── Helper: get BASE_URL ──────────────────────────────────
  function getBaseUrl() {
    return document.querySelector('meta[name="base-url"]')?.content
      || window.location.origin;
  }

  // ─── Toast notification mini ──────────────────────────────
  function showToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = `
      position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999;
      background:#2C1810; color:#fff; padding:.75rem 1.25rem;
      border-radius:10px; font-size:.875rem; font-weight:600;
      box-shadow:0 8px 24px rgba(0,0,0,.25); opacity:0;
      transition:opacity .3s;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.style.opacity = '1');
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 2800);
  }

  // ─── Tabel search client-side (opsional fast filter) ──────
  const tableSearchInputs = document.querySelectorAll('[data-table-search]');
  tableSearchInputs.forEach(input => {
    const targetId = input.dataset.tableSearch;
    const table    = document.getElementById(targetId);
    if (!table) return;

    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });

});
