<?php
/**
 * includes/footer.php
 * Footer publik Dapoora Nusantara
 */
?>
<footer class="footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="<?= BASE_URL ?>" class="footer-logo">
          <img src="<?= BASE_URL ?>/assets/images/ikon.svg"
            width="56" height="56"
            style="border-radius:50%;object-fit:cover;flex-shrink:0;">
          <span class="logo-text"><?= SITE_NAME ?></span>
        </a>
        <p>Dapoora Nusantara adalah platform yang menyediakan berbagai resep masakan khas Indonesia. Masak jadi lebih mudah, lezat, dan menyenangkan!</p>
      </div>

      <!-- Menu -->
      <div class="footer-col">
        <h4>Menu</h4>
        <a href="<?= BASE_URL ?>">Home</a>
        <a href="<?= BASE_URL ?>/pages/resep.php">Resep</a>
        <a href="<?= BASE_URL ?>/pages/kategori.php">Kategori</a>
        <a href="<?= BASE_URL ?>/pages/bookmark.php">Bookmark</a>
      </div>

      <!-- Informasi -->
      <div class="footer-col">
        <h4>Informasi</h4>
        <a href="#">Tentang Kami</a>
        <a href="#">Kebijakan Privasi</a>
        <a href="#">Syarat &amp; Ketentuan</a>
      </div>

      <!-- Kontak -->
      <div class="footer-col">
        <h4>Kontak</h4>
        <div class="contact-item">✉️ <span>info@dapoora.com</span></div>
        <div class="contact-item">📞 <span>+62 812-3456-7890</span></div>
        <div class="contact-item" style="margin-top:.75rem;gap:.75rem;">
          <a href="#" aria-label="Instagram">📷</a>
          <a href="#" aria-label="Facebook">📘</a>
          <a href="#" aria-label="YouTube">▶️</a>
        </div>
      </div>

      <!-- Newsletter -->
      <div class="footer-col footer-newsletter">
        <h4>Dapatkan Resep Terbaru</h4>
        <label>Berlangganan newsletter kami</label>
        <div class="newsletter-form">
          <input type="email" placeholder="Email kamu..." aria-label="Email newsletter">
          <button class="btn btn-primary btn-sm" type="button">Subscribe</button>
        </div>
      </div>

    </div>
  </div>
  <div class="footer-bottom">
    <p>© <?= date('Y') ?> <?= SITE_NAME ?>. Semua hak dilindungi.</p>
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/search.js"></script>
<?= isset($extraJs) ? $extraJs : '' ?>
</body>
</html>
