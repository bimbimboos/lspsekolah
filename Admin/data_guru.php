<?php
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";

/* ================= TAMBAH ================= */
if (isset($_POST['tambah'])) {
    $nama_guru     = mysqli_real_escape_string($koneksi, $_POST['nama_guru']);
    $nip           = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $mapel         = mysqli_real_escape_string($koneksi, $_POST['mapel']);
    $email         = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_hp         = mysqli_real_escape_string($koneksi, $_POST['no_hp']);

    $foto = '';
    if (!empty($_FILES['foto']['name'])) {
        $foto = time() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'upload/' . $foto);
    }

    mysqli_query($koneksi, "INSERT INTO guru (nama_guru, nip, jenis_kelamin, mapel, foto, email, no_hp)
        VALUES ('$nama_guru','$nip','$jenis_kelamin','$mapel','$foto','$email','$no_hp')");

    echo "<script>alert('Data berhasil ditambah'); window.location='data_guru.php';</script>";
    exit;
}

/* ================= HAPUS ================= */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q  = mysqli_query($koneksi, "SELECT foto FROM guru WHERE id='$id'");
    $d  = mysqli_fetch_assoc($q);
    if ($d && !empty($d['foto']) && file_exists("upload/" . $d['foto'])) {
        unlink("upload/" . $d['foto']);
    }
    mysqli_query($koneksi, "DELETE FROM guru WHERE id='$id'");
    echo "<script>alert('Data berhasil dihapus'); window.location='data_guru.php';</script>";
    exit;
}

/* ================= EDIT ================= */
if (isset($_POST['edit'])) {
    $id            = (int)$_POST['id'];
    $nama_guru     = mysqli_real_escape_string($koneksi, $_POST['nama_guru']);
    $nip           = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $mapel         = mysqli_real_escape_string($koneksi, $_POST['mapel']);
    $email         = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_hp         = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $foto_lama     = $_POST['foto_lama'];

    if (!empty($_FILES['foto']['name'])) {
        $foto_baru = time() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'upload/' . $foto_baru);
        if (!empty($foto_lama) && file_exists("upload/" . $foto_lama)) unlink("upload/" . $foto_lama);
    } else {
        $foto_baru = $foto_lama;
    }

    mysqli_query($koneksi, "UPDATE guru SET
        nama_guru='$nama_guru', nip='$nip', jenis_kelamin='$jenis_kelamin',
        mapel='$mapel', foto='$foto_baru', email='$email', no_hp='$no_hp'
        WHERE id='$id'");

    echo "<script>alert('Data berhasil diupdate'); window.location='data_guru.php';</script>";
    exit;
}

/* ================= FILTER & SEARCH ================= */
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_jk = isset($_GET['filter_jk']) ? mysqli_real_escape_string($koneksi, $_GET['filter_jk']) : '';
$filter_mapel = isset($_GET['filter_mapel']) ? mysqli_real_escape_string($koneksi, $_GET['filter_mapel']) : '';

$where = "WHERE 1=1";
if ($search)      $where .= " AND (nama_guru LIKE '%$search%' OR nip LIKE '%$search%' OR email LIKE '%$search%')";
if ($filter_jk)   $where .= " AND jenis_kelamin='$filter_jk'";
if ($filter_mapel) $where .= " AND mapel LIKE '%$filter_mapel%'";

/* ================= PAGINATION ================= */
$limit     = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$page      = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$page      = ($page < 1) ? 1 : $page;
$offset    = ($page - 1) * $limit;
$total     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM guru $where"));
$totalPage = ceil($total / $limit);
$data      = mysqli_query($koneksi, "SELECT * FROM guru $where ORDER BY id DESC LIMIT $limit OFFSET $offset");

/* ================= DAFTAR MAPEL UNTUK FILTER ================= */
$mapel_list = mysqli_query($koneksi, "SELECT DISTINCT mapel FROM guru ORDER BY mapel");
?>

<style>
/* ===== DESIGN SYSTEM ===== */
:root {
  --primary:     #1e40af;
  --primary-lt:  #3b82f6;
  --primary-xs:  #eff6ff;
  --accent:      #f59e0b;
  --danger:      #ef4444;
  --success:     #10b981;
  --warning:     #f59e0b;
  --dark:        #0f172a;
  --text:        #1e293b;
  --muted:       #64748b;
  --border:      #e2e8f0;
  --surface:     #f8fafc;
  --white:       #ffffff;
  --radius:      12px;
  --radius-sm:   8px;
  --shadow:      0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
  --shadow-md:   0 4px 12px rgba(0,0,0,.12), 0 8px 32px rgba(0,0,0,.08);
}

/* ===== PAGE HEADER ===== */
.pg-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 24px;
}
.pg-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--dark);
  display: flex;
  align-items: center;
  gap: 10px;
}
.pg-title .icon-wrap {
  width: 40px; height: 40px;
  background: var(--primary);
  border-radius: var(--radius-sm);
  display: grid; place-items: center;
  color: #fff; font-size: 1.1rem;
}
.pg-title small {
  font-size: .85rem;
  font-weight: 400;
  color: var(--muted);
  display: block;
  margin-top: 2px;
}

/* ===== STAT CARDS ===== */
.stat-row { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 22px; }
.stat-card {
  flex: 1; min-width: 130px;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px 18px;
  box-shadow: var(--shadow);
  display: flex; align-items: center; gap: 14px;
}
.stat-icon {
  width: 44px; height: 44px; border-radius: 10px;
  display: grid; place-items: center;
  font-size: 1.2rem; flex-shrink: 0;
}
.stat-icon.blue  { background: #eff6ff; color: var(--primary); }
.stat-icon.green { background: #ecfdf5; color: var(--success); }
.stat-icon.amber { background: #fffbeb; color: var(--accent); }
.stat-icon.rose  { background: #fff1f2; color: var(--danger); }
.stat-val  { font-size: 1.5rem; font-weight: 700; color: var(--dark); line-height: 1; }
.stat-lbl  { font-size: .75rem; color: var(--muted); margin-top: 3px; }

/* ===== TOOLBAR ===== */
.toolbar {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 14px 18px;
  display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
  margin-bottom: 16px;
  box-shadow: var(--shadow);
}
.toolbar-left  { display: flex; gap: 10px; flex-wrap: wrap; flex: 1; align-items: center; }
.toolbar-right { display: flex; gap: 8px; align-items: center; }

.search-wrap {
  position: relative; min-width: 220px;
}
.search-wrap i {
  position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
  color: var(--muted); font-size: .85rem;
}
.search-wrap input {
  padding-left: 32px !important;
  border-radius: var(--radius-sm) !important;
  border-color: var(--border) !important;
  font-size: .85rem !important;
  height: 36px !important;
}
.search-wrap input:focus { border-color: var(--primary-lt) !important; box-shadow: 0 0 0 3px rgba(59,130,246,.15) !important; }

.filter-select {
  height: 36px !important;
  font-size: .85rem !important;
  border-radius: var(--radius-sm) !important;
  border-color: var(--border) !important;
  min-width: 130px;
}
.filter-select:focus { border-color: var(--primary-lt) !important; box-shadow: 0 0 0 3px rgba(59,130,246,.15) !important; }

.btn-add {
  height: 36px;
  padding: 0 16px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-sm);
  font-size: .85rem;
  font-weight: 600;
  cursor: pointer;
  display: flex; align-items: center; gap: 6px;
  transition: background .18s;
  white-space: nowrap;
}
.btn-add:hover { background: #1d4ed8; }

.btn-reset {
  height: 36px; padding: 0 12px;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius-sm); font-size: .82rem;
  color: var(--muted); cursor: pointer; transition: .18s;
}
.btn-reset:hover { background: var(--border); color: var(--text); }

/* ===== TABLE CARD ===== */
.tbl-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.tbl-card table { margin: 0 !important; }
.tbl-card thead th {
  background: var(--surface) !important;
  border-bottom: 2px solid var(--border) !important;
  color: var(--muted) !important;
  font-size: .72rem !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: .06em !important;
  padding: 12px 14px !important;
  white-space: nowrap;
}
.tbl-card tbody td {
  padding: 12px 14px !important;
  vertical-align: middle !important;
  border-color: var(--border) !important;
  font-size: .875rem !important;
  color: var(--text) !important;
}
.tbl-card tbody tr { transition: background .12s; }
.tbl-card tbody tr:hover { background: var(--primary-xs); }

/* Avatar */
.avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--border);
}
.avatar-placeholder {
  width: 44px; height: 44px; border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--primary-lt));
  color: #fff; font-weight: 700; font-size: .85rem;
  display: inline-flex; align-items: center; justify-content: center;
}

/* Badges */
.badge-jk {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 10px; border-radius: 20px;
  font-size: .72rem; font-weight: 600;
}
.badge-jk.laki   { background: #eff6ff; color: #1d4ed8; }
.badge-jk.perempuan { background: #fdf2f8; color: #9d174d; }

.badge-mapel {
  display: inline-block;
  padding: 3px 10px; border-radius: 20px;
  font-size: .72rem; font-weight: 600;
  background: #ecfdf5; color: #065f46;
}

/* Action buttons */
.btn-act {
  width: 32px; height: 32px; border-radius: var(--radius-sm);
  border: none; cursor: pointer;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: .8rem; transition: .18s;
}
.btn-act.edit    { background: #fffbeb; color: var(--accent); }
.btn-act.edit:hover   { background: var(--accent); color: #fff; }
.btn-act.hapus   { background: #fff1f2; color: var(--danger); }
.btn-act.hapus:hover  { background: var(--danger); color: #fff; }

/* ===== PAGINATION ===== */
.pagi-wrap {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px;
  border-top: 1px solid var(--border);
  font-size: .82rem; color: var(--muted);
  flex-wrap: wrap; gap: 10px;
}
.pagi-wrap .pagination { margin: 0 !important; }
.pagi-wrap .page-link {
  border-color: var(--border) !important;
  color: var(--text) !important;
  font-size: .82rem !important;
  padding: 5px 11px !important;
  border-radius: 6px !important;
  margin: 0 2px !important;
}
.pagi-wrap .page-item.active .page-link {
  background: var(--primary) !important;
  border-color: var(--primary) !important;
  color: #fff !important;
}

/* ===== MODAL ===== */
.modal-content { border-radius: var(--radius) !important; border: none !important; box-shadow: var(--shadow-md) !important; }
.modal-header  { border-bottom: 1px solid var(--border) !important; padding: 18px 22px !important; }
.modal-title   { font-weight: 700 !important; color: var(--dark) !important; }
.modal-body    { padding: 20px 22px !important; }
.modal-footer  { border-top: 1px solid var(--border) !important; padding: 14px 22px !important; }
.form-label    { font-size: .8rem !important; font-weight: 600 !important; color: var(--muted) !important; margin-bottom: 5px !important; }
.form-control, .form-select {
  border-radius: var(--radius-sm) !important;
  border-color: var(--border) !important;
  font-size: .875rem !important;
}
.form-control:focus, .form-select:focus {
  border-color: var(--primary-lt) !important;
  box-shadow: 0 0 0 3px rgba(59,130,246,.15) !important;
}

/* ===== EMPTY STATE ===== */
.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state i { font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: .35; }
.empty-state p { font-size: .9rem; }

/* ===== LIMIT SELECT ===== */
.limit-wrap { display: flex; align-items: center; gap: 6px; font-size: .82rem; color: var(--muted); }
.limit-wrap select { width: auto !important; height: 30px !important; font-size: .8rem !important; padding: 0 8px !important; }
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-3">

  <!-- PAGE HEADER -->
  <div class="pg-header">
    <div class="pg-title">
      <div class="icon-wrap"><i class="bi bi-people-fill"></i></div>
      <div>
        Data Guru
        <small>Manajemen data seluruh guru</small>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:.82rem">
        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active">Data Guru</li>
      </ol>
    </nav>
  </div>

  <!-- STAT CARDS -->
  <?php
  $total_all    = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM guru"));
  $total_laki   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM guru WHERE jenis_kelamin='Laki-laki'"));
  $total_prmpn  = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM guru WHERE jenis_kelamin='Perempuan'"));
  $total_mapel  = mysqli_num_rows(mysqli_query($koneksi, "SELECT DISTINCT mapel FROM guru WHERE mapel != ''"));
  ?>
  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
      <div><div class="stat-val"><?= $total_all ?></div><div class="stat-lbl">Total Guru</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-person-fill"></i></div>
      <div><div class="stat-val"><?= $total_laki ?></div><div class="stat-lbl">Laki-laki</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber"><i class="bi bi-person-fill"></i></div>
      <div><div class="stat-val"><?= $total_prmpn ?></div><div class="stat-lbl">Perempuan</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon rose"><i class="bi bi-book-fill"></i></div>
      <div><div class="stat-val"><?= $total_mapel ?></div><div class="stat-lbl">Mata Pelajaran</div></div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <form method="GET" id="filterForm">
  <div class="toolbar">
    <div class="toolbar-left">
      <!-- Search -->
      <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Cari nama, NIP, email..."
          value="<?= htmlspecialchars($search) ?>" oninput="autoSubmit()">
      </div>
      <!-- Filter JK -->
      <select name="filter_jk" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">Semua Jenis Kelamin</option>
        <option value="Laki-laki"  <?= $filter_jk=='Laki-laki'  ? 'selected':'' ?>>Laki-laki</option>
        <option value="Perempuan"  <?= $filter_jk=='Perempuan'  ? 'selected':'' ?>>Perempuan</option>
      </select>
      <!-- Filter Mapel -->
      <select name="filter_mapel" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">Semua Mata Pelajaran</option>
        <?php while($m = mysqli_fetch_assoc($mapel_list)): ?>
        <option value="<?= $m['mapel'] ?>" <?= $filter_mapel==$m['mapel'] ? 'selected':'' ?>><?= $m['mapel'] ?></option>
        <?php endwhile; ?>
      </select>
      <!-- Rows per page -->
      <div class="limit-wrap">
        Tampilkan
        <select name="limit" class="form-select" onchange="this.form.submit()">
          <?php foreach([5,10,25,50] as $l): ?>
          <option value="<?= $l ?>" <?= $limit==$l ? 'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
        data
      </div>
    </div>
    <div class="toolbar-right">
      <?php if ($search || $filter_jk || $filter_mapel): ?>
      <a href="data_guru.php" class="btn-reset"><i class="bi bi-x-circle me-1"></i>Reset</a>
      <?php endif; ?>
      <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#tambahGuru">
        <i class="bi bi-plus-lg"></i> Tambah Guru
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
          <th>Nama Guru</th>
          <th>NIP</th>
          <th>J/K</th>
          <th>Mata Pelajaran</th>
          <th>Email</th>
          <th>No HP</th>
          <th style="width:90px; text-align:center">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($data) == 0): ?>
        <tr><td colspan="8">
          <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Tidak ada data yang ditemukan<?= $search ? ' untuk "<b>'.htmlspecialchars($search).'</b>"' : '' ?></p>
          </div>
        </td></tr>
      <?php else: $no = $offset + 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><span style="color:var(--muted);font-weight:600"><?= $no++ ?></span></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <?php if ($d['foto']): ?>
                <img src="upload/<?= $d['foto'] ?>" class="avatar" alt="">
              <?php else: ?>
                <div class="avatar-placeholder"><?= strtoupper(substr($d['nama_guru'],0,1)) ?></div>
              <?php endif; ?>
              <div>
                <div style="font-weight:600;color:var(--dark)"><?= htmlspecialchars($d['nama_guru']) ?></div>
              </div>
            </div>
          </td>
          <td style="font-family:monospace;font-size:.82rem;color:var(--muted)"><?= $d['nip'] ?: '-' ?></td>
          <td>
            <span class="badge-jk <?= $d['jenis_kelamin']=='Laki-laki' ? 'laki':'perempuan' ?>">
              <i class="bi bi-<?= $d['jenis_kelamin']=='Laki-laki' ? 'person':'person-dress' ?>"></i>
              <?= $d['jenis_kelamin'] ?>
            </span>
          </td>
          <td><?php if($d['mapel']): ?><span class="badge-mapel"><?= htmlspecialchars($d['mapel']) ?></span><?php else: ?>-<?php endif ?></td>
          <td style="font-size:.82rem"><?= $d['email'] ?: '-' ?></td>
          <td style="font-size:.82rem"><?= $d['no_hp'] ?: '-' ?></td>
          <td style="text-align:center">
            <button class="btn-act edit" data-bs-toggle="modal" data-bs-target="#editGuru<?= $d['id'] ?>" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </button>
            <a href="?hapus=<?= $d['id'] ?><?= $search?"&search=$search":'' ?><?= $filter_jk?"&filter_jk=$filter_jk":'' ?><?= $filter_mapel?"&filter_mapel=$filter_mapel":'' ?>&page=<?= $page ?>&limit=<?= $limit ?>"
               class="btn-act hapus ms-1" title="Hapus"
               onclick="return confirm('Yakin ingin menghapus guru ini?')">
              <i class="bi bi-trash-fill"></i>
            </a>
          </td>
        </tr>

        <!-- MODAL EDIT -->
        <div class="modal fade" id="editGuru<?= $d['id'] ?>" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                  <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Guru</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $d['id'] ?>">
                  <input type="hidden" name="foto_lama" value="<?= $d['foto'] ?>">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Nama Guru</label>
                      <input type="text" name="nama_guru" class="form-control" value="<?= htmlspecialchars($d['nama_guru']) ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">NIP</label>
                      <input type="text" name="nip" class="form-control" value="<?= $d['nip'] ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Jenis Kelamin</label>
                      <select name="jenis_kelamin" class="form-select">
                        <option <?= $d['jenis_kelamin']=='Laki-laki' ? 'selected':'' ?>>Laki-laki</option>
                        <option <?= $d['jenis_kelamin']=='Perempuan'  ? 'selected':'' ?>>Perempuan</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Mata Pelajaran</label>
                      <input type="text" name="mapel" class="form-control" value="<?= htmlspecialchars($d['mapel']) ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Email</label>
                      <input type="email" name="email" class="form-control" value="<?= $d['email'] ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">No HP</label>
                      <input type="text" name="no_hp" class="form-control" value="<?= $d['no_hp'] ?>">
                    </div>
                    <div class="col-md-12">
                      <label class="form-label">Foto</label>
                      <?php if ($d['foto']): ?>
                        <div class="mb-2"><img src="upload/<?= $d['foto'] ?>" width="80" style="border-radius:8px;border:2px solid var(--border)"></div>
                      <?php endif; ?>
                      <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" name="edit" class="btn btn-warning btn-sm">
                    <i class="bi bi-check-lg me-1"></i>Update
                  </button>
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
      <div>
        Menampilkan <b><?= $total > 0 ? $offset+1 : 0 ?></b>–<b><?= min($offset+$limit, $total) ?></b> dari <b><?= $total ?></b> data
      </div>
      <?php if ($totalPage > 1): ?>
      <ul class="pagination">
        <li class="page-item <?= $page==1?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= $search ?>&filter_jk=<?= $filter_jk ?>&filter_mapel=<?= $filter_mapel ?>&limit=<?= $limit ?>">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>
        <?php for($i=1;$i<=$totalPage;$i++): ?>
        <li class="page-item <?= $i==$page?'active':'' ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&filter_jk=<?= $filter_jk ?>&filter_mapel=<?= $filter_mapel ?>&limit=<?= $limit ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page==$totalPage?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= $search ?>&filter_jk=<?= $filter_jk ?>&filter_mapel=<?= $filter_mapel ?>&limit=<?= $limit ?>">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
      </ul>
      <?php endif; ?>
    </div>

  </div>

</div>
</div>
</main>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="tambahGuru" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Guru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama Guru <span class="text-danger">*</span></label>
              <input type="text" name="nama_guru" class="form-control" placeholder="Masukkan nama guru" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">NIP</label>
              <input type="text" name="nip" class="form-control" placeholder="Nomor Induk Pegawai">
            </div>
            <div class="col-md-6">
              <label class="form-label">Jenis Kelamin</label>
              <select name="jenis_kelamin" class="form-select">
                <option>Laki-laki</option>
                <option>Perempuan</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Mata Pelajaran</label>
              <input type="text" name="mapel" class="form-control" placeholder="Contoh: Matematika">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" placeholder="email@sekolah.sch.id">
            </div>
            <div class="col-md-6">
              <label class="form-label">No HP</label>
              <input type="text" name="no_hp" class="form-control" placeholder="08xx-xxxx-xxxx">
            </div>
            <div class="col-md-12">
              <label class="form-label">Foto</label>
              <input type="file" name="foto" class="form-control" accept="image/*">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="tambah" class="btn btn-primary btn-sm">
            <i class="bi bi-check-lg me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let searchTimer;
function autoSubmit() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
}
</script>

<?php include "template/footer.php"; ?>