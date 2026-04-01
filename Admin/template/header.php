<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="title" content="SMK Ganesha Tama Boyolali | Dashboard Admin" />
  <meta name="author" content="Bima Hendra" />
  <meta name="description" content="Panel Admin SMK Ganesha Tama Boyolali" />

  <title>SMK Ganesha Tama Boyolali &mdash; Dashboard Admin</title>

  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="./assets/dist/css/adminlte.css" />
  <link rel="preload" href="./assets/dist/css/adminlte.css" as="style" />

  <!-- OverlayScrollbars -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- ApexCharts & JSVectorMap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css" integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous" />

  <style>
    /* ===== GLOBAL FONT ===== */
    body, .app-header, .app-sidebar, .app-footer, .card, .small-box {
      font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    /* ===== HEADER NAVBAR ===== */
    .app-header {
      background: #ffffff !important;
      border-bottom: 1.5px solid #e8eaf0 !important;
      box-shadow: 0 1px 8px rgba(30, 58, 138, 0.06) !important;
      height: 64px;
    }

    /* Logo / Brand area di sidebar */
    .app-sidebar .brand-link,
    .sidebar-brand {
      background: #1e3a8a !important;
      border-bottom: 1px solid rgba(255,255,255,0.1) !important;
      padding: 14px 16px !important;
    }

    .brand-logo-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .brand-logo-icon {
      width: 38px;
      height: 38px;
      background: #ffffff;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: 700;
      color: #1e3a8a;
      flex-shrink: 0;
      letter-spacing: -1px;
    }

    .brand-text-main {
      font-size: 13px;
      font-weight: 700;
      color: #ffffff;
      line-height: 1.2;
      letter-spacing: 0.01em;
    }

    .brand-text-sub {
      font-size: 11px;
      font-weight: 400;
      color: rgba(255,255,255,0.65);
      letter-spacing: 0.02em;
    }

    /* Navbar kiri */
    .app-header .nav-link {
      color: #374151 !important;
      font-size: 14px;
      font-weight: 500;
      padding: 8px 12px !important;
      border-radius: 8px;
      transition: background 0.15s;
    }
    .app-header .nav-link:hover {
      background: #f1f5f9;
      color: #1e3a8a !important;
    }

    /* Badge notif */
    .navbar-badge {
      font-size: 10px !important;
      font-weight: 600 !important;
    }

    /* User menu avatar */
    .user-image {
      border: 2px solid #e2e8f0 !important;
    }

    /* ===== SIDEBAR ===== */
    .app-sidebar {
      background: #1e3a8a !important;
    }

    .sidebar-wrapper {
      background: #1e3a8a !important;
    }

    /* Menu item */
    .app-sidebar .nav-link {
      color: rgba(255,255,255,0.75) !important;
      font-size: 13.5px !important;
      font-weight: 500 !important;
      border-radius: 8px !important;
      margin: 2px 10px !important;
      padding: 9px 14px !important;
      transition: all 0.15s !important;
    }

    .app-sidebar .nav-link:hover,
    .app-sidebar .nav-link.active {
      background: rgba(255,255,255,0.12) !important;
      color: #ffffff !important;
    }

    .app-sidebar .nav-link.active {
      background: rgba(255,255,255,0.18) !important;
    }

    .app-sidebar .nav-header {
      color: rgba(255,255,255,0.4) !important;
      font-size: 10.5px !important;
      font-weight: 700 !important;
      letter-spacing: 0.08em !important;
      text-transform: uppercase !important;
      padding: 16px 24px 6px !important;
    }

    /* Sidebar icon warna */
    .app-sidebar .nav-icon {
      color: rgba(255,255,255,0.55) !important;
    }
    .app-sidebar .nav-link:hover .nav-icon,
    .app-sidebar .nav-link.active .nav-icon {
      color: #ffffff !important;
    }

    /* ===== CONTENT ===== */
    .app-content-header {
      background: transparent;
      padding: 20px 0 0;
    }

    .app-content-header h3 {
      font-weight: 700;
      color: #111827;
      font-size: 22px;
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">

  <!-- ===== HEADER ===== -->
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">

      <!-- Kiri: toggle + navigasi -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
            <i class="bi bi-list fs-5"></i>
          </a>
        </li>
        <li class="nav-item d-none d-md-block">
          <a href="../index.php" class="nav-link" title="Lihat halaman depan website">
            <i class="bi bi-house me-1"></i>Beranda Web
          </a>
        </li>
      </ul>

      <!-- Kanan: notifikasi + user -->
      <ul class="navbar-nav ms-auto align-items-center gap-1">

        <!-- Pesan masuk (dari kontak) -->
        <li class="nav-item">
          <a class="nav-link" href="hal_kontak.php" title="Pesan Masuk">
            <i class="bi bi-envelope fs-5"></i>
          </a>
        </li>

        <!-- Notifikasi -->
        <li class="nav-item dropdown">
          <a class="nav-link position-relative" data-bs-toggle="dropdown" href="#" aria-label="Notifikasi">
            <i class="bi bi-bell fs-5"></i>
            <span class="navbar-badge badge text-bg-danger" style="font-size:9px;">3</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-sm">
            <span class="dropdown-item dropdown-header fw-semibold">Notifikasi</span>
            <div class="dropdown-divider"></div>
            <a href="hal_kontak.php" class="dropdown-item d-flex align-items-center gap-2">
              <i class="bi bi-envelope-fill text-primary"></i>
              <div>
                <div style="font-size:13px;font-weight:600;">Pesan baru masuk</div>
                <div style="font-size:11px;color:#6b7280;">Baru saja</div>
              </div>
            </a>
            <div class="dropdown-divider"></div>
            <a href="hal_kontak.php" class="dropdown-item dropdown-footer text-center" style="font-size:13px;">
              Lihat Semua
            </a>
          </div>
        </li>

        <!-- Fullscreen -->
        <li class="nav-item">
          <a class="nav-link" href="#" data-lte-toggle="fullscreen" title="Layar Penuh">
            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display:none;"></i>
          </a>
        </li>

        <!-- User dropdown -->
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <div style="width:34px;height:34px;border-radius:50%;background:#1e3a8a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">
              AD
            </div>
            <span class="d-none d-md-inline" style="font-size:13.5px;font-weight:600;color:#374151;">Admin</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:180px;">
            <li>
              <a href="hal_profil.php" class="dropdown-item d-flex align-items-center gap-2">
                <i class="bi bi-person-circle text-primary"></i> Profil Saya
              </a>
            </li>
            <li>
              <a href="hal_admin.php" class="dropdown-item d-flex align-items-center gap-2">
                <i class="bi bi-gear text-secondary"></i> Pengaturan
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a href="../logout.php" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                <i class="bi bi-box-arrow-right"></i> Keluar
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </div>
  </nav>
  <!-- ===== END HEADER ===== -->