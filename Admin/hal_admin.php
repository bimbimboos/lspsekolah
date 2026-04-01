<?php
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";

// Ambil data dari database
$users   = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM users"));
$berita  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM berita"));
$galeri  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM galeri"));
$guru    = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM guru"));
$kontak  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kontak"));
$profil  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM profil_sekolah"));
?>

<style>
  /* ===== DASHBOARD CARDS ===== */
  .stat-card {
    background: #ffffff;
    border: 1.5px solid #e8eaf0;
    border-radius: 14px;
    padding: 22px 22px 18px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    text-decoration: none;
    transition: box-shadow 0.18s, transform 0.18s, border-color 0.18s;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    border-radius: 0 0 14px 14px;
    opacity: 0;
    transition: opacity 0.18s;
  }

  .stat-card:hover {
    box-shadow: 0 6px 24px rgba(30, 58, 138, 0.10);
    transform: translateY(-2px);
    border-color: #c7d2fe;
    text-decoration: none;
  }

  .stat-card:hover::before {
    opacity: 1;
  }

  /* Warna accent per kartu */
  .stat-card.blue::before {
    background: #1e3a8a;
  }

  .stat-card.green::before {
    background: #059669;
  }

  .stat-card.amber::before {
    background: #d97706;
  }

  .stat-card.red::before {
    background: #dc2626;
  }

  .stat-card.cyan::before {
    background: #0891b2;
  }

  .stat-card.slate::before {
    background: #475569;
  }

  .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
  }

  .stat-icon.blue {
    background: #eff6ff;
    color: #1e3a8a;
  }

  .stat-icon.green {
    background: #ecfdf5;
    color: #059669;
  }

  .stat-icon.amber {
    background: #fffbeb;
    color: #d97706;
  }

  .stat-icon.red {
    background: #fef2f2;
    color: #dc2626;
  }

  .stat-icon.cyan {
    background: #ecfeff;
    color: #0891b2;
  }

  .stat-icon.slate {
    background: #f1f5f9;
    color: #475569;
  }

  .stat-number {
    font-size: 30px;
    font-weight: 700;
    color: #111827;
    line-height: 1;
    margin: 0 0 4px;
  }

  .stat-label {
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    margin: 0;
  }

  .stat-footer {
    font-size: 11.5px;
    font-weight: 500;
    color: #9ca3af;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  /* ===== WELCOME CARD ===== */
  .welcome-card {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    border-radius: 14px;
    padding: 28px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 0;
  }

  .welcome-card h4 {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 6px;
  }

  .welcome-card p {
    font-size: 13.5px;
    color: rgba(255, 255, 255, 0.75);
    margin: 0;
  }

  .welcome-badge {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    padding: 10px 18px;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  /* ===== SECTION TITLE ===== */
  .section-title {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9ca3af;
    margin: 28px 0 14px;
  }
</style>

<main class="app-main">

  <!-- Breadcrumb Header -->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h3 class="mb-0" style="font-size:20px;font-weight:700;color:#111827;">Dashboard</h3>
          <p style="font-size:13px;color:#6b7280;margin:2px 0 0;">Selamat datang kembali, Admin 👋</p>
        </div>
        <div style="font-size:12.5px;color:#6b7280;background:#f9fafb;border:1.5px solid #e8eaf0;border-radius:8px;padding:6px 14px;">
          <i class="bi bi-calendar3 me-1"></i>
          <?= date('d F Y') ?>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">

      <!-- Welcome Banner -->
      <div class="welcome-card mb-4">
        <div>
          <h4>SMK Ganesha Tama Boyolali</h4>
          <p>Panel administrasi website sekolah. Kelola data melalui menu di samping.</p>
        </div>
        <div class="welcome-badge d-none d-md-block">
          <i class="bi bi-shield-check me-1"></i> Admin Panel
        </div>
      </div>

      <!-- Section Label -->
      <div class="section-title">Ringkasan Data</div>

      <!-- Baris 1: 4 Kartu -->
      <div class="row g-3 mb-3">

        <!-- Berita -->
        <div class="col-lg-3 col-sm-6">
          <a href="data_berita.php" class="stat-card green d-block">
            <div>
              <p class="stat-number"><?= $berita ?></p>
              <p class="stat-label">Berita</p>
              <div class="stat-footer">
                <i class="bi bi-arrow-right-circle"></i> Lihat detail
              </div>
            </div>
            <div class="stat-icon green">
              <i class="bi bi-newspaper"></i>
            </div>
          </a>
        </div>

        <!-- Galeri -->
        <div class="col-lg-3 col-sm-6">
          <a href="data_galeri.php" class="stat-card amber d-block">
            <div>
              <p class="stat-number"><?= $galeri ?></p>
              <p class="stat-label">Galeri Foto</p>
              <div class="stat-footer">
                <i class="bi bi-arrow-right-circle"></i> Lihat detail
              </div>
            </div>
            <div class="stat-icon amber">
              <i class="bi bi-images"></i>
            </div>
          </a>
        </div>

        <!-- Guru -->
        <div class="col-lg-6 col-sm-6">
          <a href="data_guru.php" class="stat-card red d-block">
            <div>
              <p class="stat-number"><?= $guru ?></p>
              <p class="stat-label">Data Guru</p>
              <div class="stat-footer">
                <i class="bi bi-arrow-right-circle"></i> Lihat detail
              </div>
            </div>
            <div class="stat-icon red">
              <i class="bi bi-person-badge-fill"></i>
            </div>
          </a>
        </div>

      </div>

      <!-- Baris 2: 2 Kartu -->
      <div class="row g-3 mb-4">

        <!-- Kontak Masuk -->
        <div class="col-lg-6 col-sm-6">
          <a href="data_kontak.php" class="stat-card cyan d-block">
            <div>
              <p class="stat-number"><?= $kontak ?></p>
              <p class="stat-label">Kontak Masuk</p>
              <div class="stat-footer">
                <i class="bi bi-arrow-right-circle"></i> Lihat pesan
              </div>
            </div>
            <div class="stat-icon cyan">
              <i class="bi bi-envelope-fill"></i>
            </div>
          </a>
        </div>

        <!-- Profil Sekolah -->
        <div class="col-lg-6 col-sm-6">
          <a href="data_profil.php" class="stat-card slate d-block">
            <div>
              <p class="stat-number"><?= $profil ?></p>
              <p class="stat-label">Profil Sekolah</p>
              <div class="stat-footer">
                <i class="bi bi-arrow-right-circle"></i> Kelola profil
              </div>
            </div>
            <div class="stat-icon slate">
              <i class="bi bi-building-fill"></i>
            </div>
          </a>
        </div>

      </div>
    </div>
  </div>
</main>

<?php include "template/footer.php"; ?>