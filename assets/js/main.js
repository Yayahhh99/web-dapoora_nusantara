/**
 * assets/js/main.js
 * Dapoora Nusantara — User Side JavaScript (ES6, no jQuery)
 */

document.addEventListener('DOMContentLoaded', () => {

  // ─── Hamburger menu ───────────────────────────────────────
  const hamburger = document.getElementById('hamburger');
  const navMenu   = document.getElementById('navMenu');

  if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navMenu.classList.toggle('open');
    });
    // Tutup saat klik di luar
    document.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
        hamburger.classList.remove('active');
        navMenu.classList.remove('open');
      }
    });
  }

  // ─── User dropdown toggle ─────────────────────────────────
  const userToggle   = document.getElementById('userToggle');
  const userDropdown = document.getElementById('userDropdown');

  if (userToggle && userDropdown) {
    userToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = userDropdown.classList.toggle('show');
      userToggle.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', () => {
      userDropdown.classList.remove('show');
      userToggle?.setAttribute('aria-expanded', 'false');
    });
  }

  // ─── Active nav link ──────────────────────────────────────
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-link').forEach(link => {
    if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').split('/').pop())) {
      link.classList.add('active');
    }
  });

  // ─── Live Search ──────────────────────────────────────────
  const searchInput    = document.getElementById('searchInput');
  const searchDropdown = document.getElementById('searchDropdown');

  if (searchInput && searchDropdown) {
    let searchTimeout;

    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      const query = searchInput.value.trim();

      if (query.length < 2) {
        searchDropdown.classList.remove('show');
        searchDropdown.innerHTML = '';
        return;
      }

      searchTimeout = setTimeout(() => doSearch(query), 280);
    });

    searchInput.addEventListener('keydown', (e) => {
      const items = searchDropdown.querySelectorAll('.search-item');
      const focused = searchDropdown.querySelector('.search-item.focused');
      let idx = Array.from(items).indexOf(focused);

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        idx = Math.min(idx + 1, items.length - 1);
        items.forEach(i => i.classList.remove('focused'));
        items[idx]?.classList.add('focused');
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        idx = Math.max(idx - 1, 0);
        items.forEach(i => i.classList.remove('focused'));
        items[idx]?.classList.add('focused');
      } else if (e.key === 'Enter') {
        const focusedItem = searchDropdown.querySelector('.search-item.focused');
        if (focusedItem) {
          e.preventDefault();
          window.location.href = focusedItem.href;
        }
      } else if (e.key === 'Escape') {
        searchDropdown.classList.remove('show');
      }
    });

    document.addEventListener('click', (e) => {
      if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
        searchDropdown.classList.remove('show');
      }
    });
  }

  async function doSearch(query) {
    try {
      // Deteksi base URL dari meta atau window
      const base = document.querySelector('meta[name="base-url"]')?.content || '';
      const res  = await fetch(`${base}/pages/api/search.php?q=${encodeURIComponent(query)}`);
      const data = await res.json();

      if (!data.results || data.results.length === 0) {
        searchDropdown.innerHTML = '<div style="padding:1rem;color:var(--text-muted);font-size:.875rem;text-align:center">Resep tidak ditemukan 😕</div>';
        searchDropdown.classList.add('show');
        return;
      }

      searchDropdown.innerHTML = data.results.map(r => `
        <a href="${r.url}" class="search-item" role="option">
          <img src="${r.foto}" alt="${r.judul}" loading="lazy">
          <div>
            <div class="si-title">${r.judul}</div>
            <div class="si-cat">${r.kategori}</div>
          </div>
        </a>
      `).join('');
      searchDropdown.classList.add('show');
    } catch (err) {
      console.error('Search error:', err);
    }
  }

  // ─── Bookmark buttons (card grid) ─────────────────────────
  document.querySelectorAll('.bookmark-btn[data-id]').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const recipeId = btn.dataset.id;
      const base     = document.querySelector('meta[name="base-url"]')?.content || '';

      try {
        const res  = await fetch(`${base}/process/bookmark_process.php`, {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ recipe_id: recipeId })
        });

        if (res.status === 401) {
          window.location.href = `${base}/pages/login.php`;
          return;
        }

        const data = await res.json();
        if (data.status === 'added') {
          btn.classList.add('active');
          btn.title = 'Hapus bookmark';
        } else {
          btn.classList.remove('active');
          btn.title = 'Simpan resep';
          // Jika di halaman bookmark, hapus card
          const card = btn.closest('.recipe-card');
          if (card && window.location.href.includes('bookmark')) {
            card.style.opacity = '0';
            card.style.transition = 'opacity .3s';
            setTimeout(() => card.closest('.card')?.remove(), 300);
          }
        }
      } catch (err) {
        console.error('Bookmark error:', err);
      }
    });
  });

  // ─── Lazy loading gambar ──────────────────────────────────
  if ('IntersectionObserver' in window) {
    const imgObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          imgObserver.unobserve(img);
        }
      });
    }, { rootMargin: '100px' });

    document.querySelectorAll('img[data-src]').forEach(img => imgObserver.observe(img));
  }

  // ─── AI Widget toggle ──────────────────────────────────────
  const aiToggle = document.getElementById('aiToggle');
  const aiBox    = document.getElementById('aiBox');

  if (aiToggle && aiBox) {
    aiToggle.addEventListener('click', () => {
      aiBox.classList.toggle('hidden');
      aiToggle.style.transform = aiBox.classList.contains('hidden') ? 'scale(1)' : 'rotate(15deg) scale(1.05)';
    });
  }

  // ─── Alert auto-dismiss ───────────────────────────────────
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .5s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });

});
