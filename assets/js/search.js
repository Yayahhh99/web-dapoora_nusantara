/**
 * assets/js/search.js
 * Dapoora Nusantara — Live search dengan debounce & keyboard navigation
 */

(function () {
  'use strict';

  // ─── Debounce ──────────────────────────────────────────────
  function debounce(fn, delay) {
    let timer;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  // ─── Get base URL ──────────────────────────────────────────
  function getBase() {
    return document.querySelector('meta[name="base-url"]')?.content || '';
  }

  // ─── Render hasil dropdown ─────────────────────────────────
  function renderResults(results, dropdown) {
    if (!results.length) {
      dropdown.innerHTML = `
        <div style="padding:1.1rem;text-align:center;color:var(--text-muted);font-size:.875rem">
          😕 Tidak ada resep yang cocok
        </div>`;
      dropdown.classList.add('show');
      return;
    }

    dropdown.innerHTML = results.map((r, i) => `
      <a href="${r.url}" class="search-item" role="option" tabindex="-1" data-index="${i}">
        <img src="${r.foto}" alt="${escapeHtml(r.judul)}" loading="lazy"
             onerror="this.src='${getBase()}/assets/images/recipes/placeholder.jpg'">
        <div>
          <div class="si-title">${escapeHtml(r.judul)}</div>
          <div class="si-cat">${escapeHtml(r.kategori)}</div>
        </div>
      </a>
    `).join('');

    dropdown.classList.add('show');
  }

  // ─── Escape HTML ──────────────────────────────────────────
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // ─── Fetch hasil pencarian ─────────────────────────────────
  async function fetchResults(query, dropdown) {
    try {
      const res  = await fetch(`${getBase()}/pages/api/search.php?q=${encodeURIComponent(query)}`);
      if (!res.ok) throw new Error('Network error');
      const data = await res.json();
      renderResults(data.results || [], dropdown);
    } catch (err) {
      dropdown.innerHTML = `<div style="padding:1rem;color:var(--danger);font-size:.85rem">⚠️ Gagal memuat hasil.</div>`;
      dropdown.classList.add('show');
    }
  }

  // ─── Keyboard navigation ───────────────────────────────────
  function handleKeyNav(e, input, dropdown) {
    const items = [...dropdown.querySelectorAll('.search-item')];
    if (!items.length) return;

    let focusedIdx = items.findIndex(i => i.classList.contains('focused'));

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      focusedIdx = Math.min(focusedIdx + 1, items.length - 1);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      focusedIdx = Math.max(focusedIdx - 1, 0);
    } else if (e.key === 'Enter') {
      const focused = items[focusedIdx];
      if (focused) { e.preventDefault(); window.location.href = focused.href; }
      return;
    } else if (e.key === 'Escape') {
      dropdown.classList.remove('show');
      input.blur();
      return;
    } else {
      return;
    }

    items.forEach(i => i.classList.remove('focused'));
    if (focusedIdx >= 0) items[focusedIdx].classList.add('focused');
  }

  // ─── Init: gantung event ke semua search input ─────────────
  document.addEventListener('DOMContentLoaded', () => {
    const input    = document.getElementById('searchInput');
    const dropdown = document.getElementById('searchDropdown');
    if (!input || !dropdown) return;

    const debouncedFetch = debounce((q) => fetchResults(q, dropdown), 300);

    input.addEventListener('input', () => {
      const q = input.value.trim();
      if (q.length < 2) {
        dropdown.classList.remove('show');
        dropdown.innerHTML = '';
        return;
      }
      debouncedFetch(q);
    });

    input.addEventListener('keydown', (e) => handleKeyNav(e, input, dropdown));

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
      }
    });

    // Fokus: tampilkan kembali jika ada query
    input.addEventListener('focus', () => {
      if (input.value.trim().length >= 2 && dropdown.innerHTML.trim()) {
        dropdown.classList.add('show');
      }
    });
  });

})();
