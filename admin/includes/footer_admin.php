<?php
/**
 * admin/includes/footer_admin.php
 */
?>
  </main><!-- end .admin-content -->

  <footer class="admin-footer">
    <p>© <?= date('Y') ?> <?= SITE_NAME ?>. Semua hak dilindungi.</p>
  </footer>

</div><!-- end .admin-main -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
<?= isset($extraJs) ? $extraJs : '' ?>
</body>
</html>
