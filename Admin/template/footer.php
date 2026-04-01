<!-- ===== FOOTER ===== -->
  <footer class="app-footer" style="
    background: #ffffff;
    border-top: 1.5px solid #e8eaf0;
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 12.5px;
    color: #6b7280;
  ">
    <div>
      &copy; <?= date('Y') ?> &nbsp;
      <span style="font-weight:600;color:#1e3a8a;">SMK Ganesha Tama Boyolali</span>
      &mdash; Hak Cipta Dilindungi
    </div>
    <div style="color:#9ca3af;">
      Dibuat oleh <span style="font-weight:600;color:#374151;">Bima Hendra</span>
    </div>
  </footer>
  <!-- ===== END FOOTER ===== -->

</div><!-- end .app-wrapper -->

<!-- ===== SCRIPTS ===== -->
<!-- OverlayScrollbars -->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<!-- Popper + Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<!-- AdminLTE -->
<script src="./js/adminlte.js"></script>
<!-- jQuery (untuk sub-menu dropdown) -->
<script src="assets/plugins/jquery/jquery.min.js"></script>

<!-- OverlayScrollbars Sidebar -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sidebarWrapper = document.querySelector('.sidebar-wrapper');
    if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
      OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
        scrollbars: {
          theme: 'os-theme-light',
          autoHide: 'leave',
          clickScroll: true,
        },
      });
    }
  });
</script>

</body>
</html>