<?php
session_start(); // Wajib untuk CSRF token
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";

/* ============================================================
   CSRF TOKEN — dibuat sekali per sesi, disimpan di $_SESSION
   Setiap form POST harus menyertakan token ini.
   ============================================================ */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fungsi validasi CSRF — panggil sebelum proses POST apapun
function cek_csrf() {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;color:#dc2626;">
            <h2>403 - Akses Ditolak</h2>
            <p>Token keamanan tidak valid. Silakan <a href="data_kontak.php">kembali ke halaman kontak</a>.</p>
        </div>');
    }
}

// Fungsi tampilkan notifikasi (lebih profesional dari alert())
function set_flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/* ============================================================
   TAMBAH
   ============================================================ */
if (isset($_POST['tambah'])) {
    cek_csrf(); // Validasi CSRF dulu

    $nama  = trim(mysqli_real_escape_string($koneksi, $_POST['nama']));
    $email = trim($_POST['email']);
    $pesan = trim(mysqli_real_escape_string($koneksi, $_POST['pesan']));

    // Validasi server-side: email harus format yang benar
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Format email tidak valid.');
        header("Location: data_kontak.php");
        exit;
    }

    // Validasi: field tidak boleh kosong
    if (empty($nama) || empty($pesan)) {
        set_flash('danger', 'Nama dan pesan tidak boleh kosong.');
        header("Location: data_kontak.php");
        exit;
    }

    $email   = mysqli_real_escape_string($koneksi, $email);
    $tanggal = date('Y-m-d');

    mysqli_query($koneksi, "INSERT INTO kontak (nama, email, pesan, tanggal_kirim)
                             VALUES ('$nama', '$email', '$pesan', '$tanggal')");
    set_flash('success', 'Pesan berhasil ditambahkan.');
    header("Location: data_kontak.php");
    exit;
}

/* ============================================================
   HAPUS — pakai POST bukan GET, lebih aman
   ============================================================ */
if (isset($_POST['hapus'])) {
    cek_csrf(); // Validasi CSRF dulu

    $id = (int)$_POST['hapus_id']; // cast ke integer agar aman
    if ($id > 0) {
        mysqli_query($koneksi, "DELETE FROM kontak WHERE id=$id");
        set_flash('success', 'Pesan berhasil dihapus.');
    }
    header("Location: data_kontak.php");
    exit;
}

/* ============================================================
   EDIT
   ============================================================ */
if (isset($_POST['edit'])) {
    cek_csrf(); // Validasi CSRF dulu

    $id    = (int)$_POST['id'];
    $nama  = trim(mysqli_real_escape_string($koneksi, $_POST['nama']));
    $email = trim($_POST['email']);
    $pesan = trim(mysqli_real_escape_string($koneksi, $_POST['pesan']));

    // Validasi server-side email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Format email tidak valid.');
        header("Location: data_kontak.php");
        exit;
    }

    if (empty($nama) || empty($pesan) || $id <= 0) {
        set_flash('danger', 'Data tidak lengkap atau tidak valid.');
        header("Location: data_kontak.php");
        exit;
    }

    $email = mysqli_real_escape_string($koneksi, $email);
    mysqli_query($koneksi, "UPDATE kontak SET nama='$nama', email='$email', pesan='$pesan' WHERE id=$id");
    set_flash('success', 'Pesan berhasil diperbarui.');
    header("Location: data_kontak.php");
    exit;
}

/* ============================================================
   FILTER & SEARCH
   ============================================================ */
$search       = isset($_GET['search'])       ? mysqli_real_escape_string($koneksi, $_GET['search'])       : '';
$filter_bulan = isset($_GET['filter_bulan']) ? mysqli_real_escape_string($koneksi, $_GET['filter_bulan']) : '';

$where = "WHERE 1=1";
if ($search)       $where .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%' OR pesan LIKE '%$search%')";
if ($filter_bulan) $where .= " AND DATE_FORMAT(tanggal_kirim,'%Y-%m')='$filter_bulan'";

/* ============================================================
   PAGINATION
   ============================================================ */
$limit     = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$limit     = in_array($limit, [5,10,25,50]) ? $limit : 5; // whitelist nilai limit
$page      = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$page      = max(1, $page);
$offset    = ($page - 1) * $limit;
$total     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM kontak $where"));
$totalPage = ($total > 0) ? ceil($total / $limit) : 1;
$data      = mysqli_query($koneksi, "SELECT * FROM kontak $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$bulan_list = mysqli_query($koneksi, "SELECT DISTINCT DATE_FORMAT(tanggal_kirim,'%Y-%m') AS bln, DATE_FORMAT(tanggal_kirim,'%M %Y') AS lbl FROM kontak ORDER BY bln DESC");

/* Stat cards */
$total_all   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM kontak"));
$total_bulan = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM kontak WHERE MONTH(tanggal_kirim)=MONTH(NOW()) AND YEAR(tanggal_kirim)=YEAR(NOW())"));
$total_hari  = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM kontak WHERE DATE(tanggal_kirim)=CURDATE()"));

/* Ambil flash message jika ada */
$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
unset($_SESSION['flash']);
?>

<style>
:root{--primary:#1e40af;--primary-lt:#3b82f6;--primary-xs:#eff6ff;--accent:#f59e0b;--danger:#ef4444;--success:#10b981;--dark:#0f172a;--text:#1e293b;--muted:#64748b;--border:#e2e8f0;--surface:#f8fafc;--white:#ffffff;--radius:12px;--radius-sm:8px;--shadow:0 1px 3px rgba(0,0,0,.08),0 4px 16px rgba(0,0,0,.06);--shadow-md:0 4px 12px rgba(0,0,0,.12),0 8px 32px rgba(0,0,0,.08)}
.pg-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px}
.pg-title{font-size:1.5rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:10px}
.pg-title .icon-wrap{width:40px;height:40px;background:var(--primary);border-radius:var(--radius-sm);display:grid;place-items:center;color:#fff;font-size:1.1rem}
.pg-title small{font-size:.85rem;font-weight:400;color:var(--muted);display:block;margin-top:2px}
.stat-row{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.stat-card{flex:1;min-width:130px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:16px 18px;box-shadow:var(--shadow);display:flex;align-items:center;gap:14px}
.stat-icon{width:44px;height:44px;border-radius:10px;display:grid;place-items:center;font-size:1.2rem;flex-shrink:0}
.stat-icon.blue{background:#eff6ff;color:var(--primary)}.stat-icon.green{background:#ecfdf5;color:var(--success)}.stat-icon.amber{background:#fffbeb;color:var(--accent)}
.stat-val{font-size:1.5rem;font-weight:700;color:var(--dark);line-height:1}
.stat-lbl{font-size:.75rem;color:var(--muted);margin-top:3px}
.toolbar{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:14px 18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px;box-shadow:var(--shadow)}
.toolbar-left{display:flex;gap:10px;flex-wrap:wrap;flex:1;align-items:center}
.toolbar-right{display:flex;gap:8px;align-items:center}
.search-wrap{position:relative;min-width:220px}
.search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem}
.search-wrap input{padding-left:32px!important;border-radius:var(--radius-sm)!important;border-color:var(--border)!important;font-size:.85rem!important;height:36px!important}
.search-wrap input:focus{border-color:var(--primary-lt)!important;box-shadow:0 0 0 3px rgba(59,130,246,.15)!important}
.filter-select{height:36px!important;font-size:.85rem!important;border-radius:var(--radius-sm)!important;border-color:var(--border)!important;min-width:150px}
.btn-add{height:36px;padding:0 16px;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-size:.85rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .18s;white-space:nowrap}
.btn-add:hover{background:#1d4ed8}
.btn-reset{height:36px;padding:0 12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.82rem;color:var(--muted);cursor:pointer;transition:.18s;text-decoration:none;display:flex;align-items:center}
.btn-reset:hover{background:var(--border);color:var(--text)}
.tbl-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.tbl-card thead th{background:var(--surface)!important;border-bottom:2px solid var(--border)!important;color:var(--muted)!important;font-size:.72rem!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.06em!important;padding:12px 14px!important;white-space:nowrap}
.tbl-card tbody td{padding:12px 14px!important;vertical-align:middle!important;border-color:var(--border)!important;font-size:.875rem!important;color:var(--text)!important}
.tbl-card tbody tr{transition:background .12s}
.tbl-card tbody tr:hover{background:var(--primary-xs)}
.sender-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-lt));color:#fff;font-weight:700;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.pesan-preview{max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;color:var(--muted)}
.badge-tgl{font-size:.72rem;background:#f1f5f9;color:var(--muted);padding:3px 8px;border-radius:20px}
.badge-email{font-size:.72rem;background:#ecfdf5;color:#065f46;padding:3px 9px;border-radius:20px;font-weight:600}
.btn-act{width:32px;height:32px;border-radius:var(--radius-sm);border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;transition:.18s}
.btn-act.view{background:#eff6ff;color:var(--primary)}.btn-act.view:hover{background:var(--primary);color:#fff}
.btn-act.edit{background:#fffbeb;color:var(--accent)}.btn-act.edit:hover{background:var(--accent);color:#fff}
.btn-act.hapus{background:#fff1f2;color:var(--danger)}.btn-act.hapus:hover{background:var(--danger);color:#fff}
.pagi-wrap{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-top:1px solid var(--border);font-size:.82rem;color:var(--muted);flex-wrap:wrap;gap:10px}
.pagi-wrap .pagination{margin:0!important}
.pagi-wrap .page-link{border-color:var(--border)!important;color:var(--text)!important;font-size:.82rem!important;padding:5px 11px!important;border-radius:6px!important;margin:0 2px!important}
.pagi-wrap .page-item.active .page-link{background:var(--primary)!important;border-color:var(--primary)!important;color:#fff!important}
.modal-content{border-radius:var(--radius)!important;border:none!important;box-shadow:var(--shadow-md)!important}
.modal-header{border-bottom:1px solid var(--border)!important;padding:18px 22px!important}
.modal-title{font-weight:700!important;color:var(--dark)!important}
.modal-body{padding:20px 22px!important}
.modal-footer{border-top:1px solid var(--border)!important;padding:14px 22px!important}
.form-label{font-size:.8rem!important;font-weight:600!important;color:var(--muted)!important;margin-bottom:5px!important}
.form-control,.form-select{border-radius:var(--radius-sm)!important;border-color:var(--border)!important;font-size:.875rem!important}
.form-control:focus,.form-select:focus{border-color:var(--primary-lt)!important;box-shadow:0 0 0 3px rgba(59,130,246,.15)!important}
.empty-state{text-align:center;padding:60px 20px;color:var(--muted)}
.empty-state i{font-size:2.5rem;margin-bottom:12px;display:block;opacity:.35}
.limit-wrap{display:flex;align-items:center;gap:6px;font-size:.82rem;color:var(--muted)}
.limit-wrap select{width:auto!important;height:30px!important;font-size:.8rem!important;padding:0 8px!important}
.pesan-box{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:14px;font-size:.875rem;line-height:1.6;color:var(--text);white-space:pre-wrap}
/* Modal konfirmasi hapus */
.modal-hapus-icon{width:60px;height:60px;background:#fff1f2;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--danger);margin:0 auto 16px}
/* Flash alert */
.flash-alert{border-radius:var(--radius-sm);padding:12px 18px;margin-bottom:18px;font-size:.875rem;font-weight:500;display:flex;align-items:center;gap:10px}
.flash-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.flash-danger{background:#fff1f2;border:1px solid #fecaca;color:#991b1b}
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-3">

  <!-- PAGE HEADER -->
  <div class="pg-header">
    <div class="pg-title">
      <div class="icon-wrap"><i class="bi bi-envelope-fill"></i></div>
      <div>
        Data Kontak
        <small>Pesan masuk dari pengunjung sekolah</small>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:.82rem">
        <li class="breadcrumb-item"><a href="hal_admin.php" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active">Data Kontak</li>
      </ol>
    </nav>
  </div>

  <!-- FLASH MESSAGE (menggantikan alert() bawaan browser) -->
  <?php if ($flash): ?>
  <div class="flash-alert flash-<?= $flash['type'] ?>">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
  </div>
  <?php endif; ?>

  <!-- STAT -->
  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-envelope-fill"></i></div>
      <div><div class="stat-val"><?= $total_all ?></div><div class="stat-lbl">Total Pesan</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-calendar-check"></i></div>
      <div><div class="stat-val"><?= $total_bulan ?></div><div class="stat-lbl">Bulan Ini</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber"><i class="bi bi-bell-fill"></i></div>
      <div><div class="stat-val"><?= $total_hari ?></div><div class="stat-lbl">Hari Ini</div></div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <form method="GET" id="filterForm">
  <div class="toolbar">
    <div class="toolbar-left">
      <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Cari nama, email, pesan..."
          value="<?= htmlspecialchars($search) ?>" oninput="autoSubmit()">
      </div>
      <select name="filter_bulan" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">Semua Bulan</option>
        <?php while($b = mysqli_fetch_assoc($bulan_list)): ?>
        <option value="<?= $b['bln'] ?>" <?= $filter_bulan==$b['bln']?'selected':'' ?>><?= $b['lbl'] ?></option>
        <?php endwhile; ?>
      </select>
      <div class="limit-wrap">
        Tampilkan
        <select name="limit" class="form-select" onchange="this.form.submit()">
          <?php foreach([5,10,25,50] as $l): ?>
          <option value="<?= $l ?>" <?= $limit==$l?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
        data
      </div>
    </div>
    <div class="toolbar-right">
      <?php if ($search || $filter_bulan): ?>
      <a href="data_kontak.php" class="btn-reset"><i class="bi bi-x-circle me-1"></i>Reset</a>
      <?php endif; ?>
      <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#tambahKontak">
        <i class="bi bi-plus-lg"></i> Tambah Pesan
      </button>
    </div>
  </div>
  </form>

  <!-- TABLE -->
  <div class="tbl-card">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th style="width:48px">No</th>
          <th>Pengirim</th>
          <th>Email</th>
          <th>Pesan</th>
          <th>Tanggal</th>
          <th style="width:110px;text-align:center">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($data) == 0): ?>
        <tr><td colspan="6">
          <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Tidak ada pesan yang ditemukan<?= $search ? ' untuk "<b>'.htmlspecialchars($search).'</b>"' : '' ?></p>
          </div>
        </td></tr>
      <?php else: $no = $offset + 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><span style="color:var(--muted);font-weight:600"><?= $no++ ?></span></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="sender-avatar"><?= strtoupper(substr($d['nama'],0,1)) ?></div>
              <span style="font-weight:600;color:var(--dark)"><?= htmlspecialchars($d['nama']) ?></span>
            </div>
          </td>
          <td><span class="badge-email"><?= htmlspecialchars($d['email']) ?></span></td>
          <td><div class="pesan-preview"><?= htmlspecialchars($d['pesan']) ?></div></td>
          <td><span class="badge-tgl"><i class="bi bi-calendar2 me-1"></i><?= $d['tanggal_kirim'] ? date('d M Y', strtotime($d['tanggal_kirim'])) : '-' ?></span></td>
          <td style="text-align:center">
            <!-- Tombol Lihat -->
            <button class="btn-act view" data-bs-toggle="modal" data-bs-target="#lihatKontak<?= $d['id'] ?>" title="Lihat Detail">
              <i class="bi bi-eye-fill"></i>
            </button>
            <!-- Tombol Edit -->
            <button class="btn-act edit" data-bs-toggle="modal" data-bs-target="#editKontak<?= $d['id'] ?>" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </button>
            <!-- Tombol Hapus — trigger modal konfirmasi, bukan confirm() -->
            <button class="btn-act hapus ms-1" title="Hapus"
              onclick="konfirmasiHapus(<?= $d['id'] ?>, '<?= addslashes(htmlspecialchars($d['nama'])) ?>')">
              <i class="bi bi-trash-fill"></i>
            </button>
          </td>
        </tr>

        <!-- MODAL LIHAT -->
        <div class="modal fade" id="lihatKontak<?= $d['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye-fill me-2 text-primary"></i>Detail Pesan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-3 p-3" style="background:var(--primary-xs);border-radius:8px">
                  <div class="sender-avatar" style="width:48px;height:48px;font-size:1rem"><?= strtoupper(substr($d['nama'],0,1)) ?></div>
                  <div>
                    <div style="font-weight:700;color:var(--dark)"><?= htmlspecialchars($d['nama']) ?></div>
                    <div style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($d['email']) ?></div>
                    <div style="font-size:.75rem;color:var(--muted)"><?= $d['tanggal_kirim'] ? date('d M Y', strtotime($d['tanggal_kirim'])) : '-' ?></div>
                  </div>
                </div>
                <label class="form-label">Isi Pesan</label>
                <div class="pesan-box"><?= htmlspecialchars($d['pesan']) ?></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <a href="mailto:<?= htmlspecialchars($d['email']) ?>" class="btn btn-primary btn-sm">
                  <i class="bi bi-reply-fill me-1"></i>Balas Email
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- MODAL EDIT -->
        <div class="modal fade" id="editKontak<?= $d['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" action="data_kontak.php">
                <!-- CSRF Token wajib ada di setiap form POST -->
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-header">
                  <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Kontak</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $d['id'] ?>">
                  <div class="mb-3">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($d['nama']) ?>" required maxlength="100">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($d['email']) ?>" required maxlength="150">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Pesan <span class="text-danger">*</span></label>
                    <textarea name="pesan" class="form-control" rows="4" required maxlength="2000"><?= htmlspecialchars($d['pesan']) ?></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" name="edit" class="btn btn-warning btn-sm"><i class="bi bi-check-lg me-1"></i>Update</button>
                </div>
              </form>
            </div>
          </div>
        </div>

      <?php endwhile; endif; ?>
      </tbody>
    </table>
    </div>

    <!-- PAGINATION -->
    <div class="pagi-wrap">
      <div>Menampilkan <b><?= $total > 0 ? $offset+1 : 0 ?></b>–<b><?= min($offset+$limit, $total) ?></b> dari <b><?= $total ?></b> pesan</div>
      <?php if ($totalPage > 1): ?>
      <ul class="pagination">
        <li class="page-item <?= $page==1?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter_bulan=<?= urlencode($filter_bulan) ?>&limit=<?= $limit ?>"><i class="bi bi-chevron-left"></i></a>
        </li>
        <?php for($i=1;$i<=$totalPage;$i++): ?>
        <li class="page-item <?= $i==$page?'active':'' ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter_bulan=<?= urlencode($filter_bulan) ?>&limit=<?= $limit ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page==$totalPage?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter_bulan=<?= urlencode($filter_bulan) ?>&limit=<?= $limit ?>"><i class="bi bi-chevron-right"></i></a>
        </li>
      </ul>
      <?php endif; ?>
    </div>
  </div>

</div>
</div>
</main>

<!-- ============================================================
     MODAL TAMBAH
     ============================================================ -->
<div class="modal fade" id="tambahKontak" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="data_kontak.php">
        <!-- CSRF Token wajib ada -->
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-envelope-plus-fill me-2 text-primary"></i>Tambah Pesan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" placeholder="Nama pengirim" required maxlength="100">
          </div>
          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required maxlength="150">
          </div>
          <div class="mb-3">
            <label class="form-label">Pesan <span class="text-danger">*</span></label>
            <textarea name="pesan" class="form-control" rows="4" placeholder="Tulis pesan..." required maxlength="2000"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="tambah" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================
     MODAL KONFIRMASI HAPUS (menggantikan confirm() bawaan browser)
     ============================================================ -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="POST" action="data_kontak.php" id="formHapus">
        <!-- CSRF Token wajib ada -->
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="hapus_id" id="hapusIdInput" value="">
        <input type="hidden" name="hapus" value="1">
        <div class="modal-body text-center" style="padding:28px 22px!important">
          <div class="modal-hapus-icon"><i class="bi bi-trash-fill"></i></div>
          <h6 style="font-weight:700;color:var(--dark);margin-bottom:8px">Hapus Pesan?</h6>
          <p style="font-size:.85rem;color:var(--muted);margin-bottom:20px">
            Pesan dari <b id="hapusNamaLabel">-</b> akan dihapus permanen dan tidak bisa dikembalikan.
          </p>
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger btn-sm px-4">
              <i class="bi bi-trash me-1"></i>Ya, Hapus
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Auto-submit search setelah 500ms berhenti mengetik
let searchTimer;
function autoSubmit() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
}

// Isi modal konfirmasi hapus dengan id & nama yang sesuai
function konfirmasiHapus(id, nama) {
  document.getElementById('hapusIdInput').value  = id;
  document.getElementById('hapusNamaLabel').textContent = nama;
  new bootstrap.Modal(document.getElementById('modalHapus')).show();
}
</script>

<?php include "template/footer.php"; ?> 