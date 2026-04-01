<?php
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";

/* ================= HAPUS ================= */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q  = mysqli_query($koneksi, "SELECT logo FROM profil_sekolah WHERE id='$id'");
    $d  = mysqli_fetch_assoc($q);
    if ($d && !empty($d['logo']) && file_exists("upload/" . $d['logo'])) {
        unlink("upload/" . $d['logo']);
    }
    mysqli_query($koneksi, "DELETE FROM profil_sekolah WHERE id='$id'");
    echo "<script>alert('Data berhasil dihapus'); window.location='data_sekolah.php';</script>";
    exit;
}

/* ================= UPDATE ================= */
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $fields = ['nama_sekolah','npsn','alamat','desa','kecamatan','kabupaten','provinsi',
               'email','telepon','website','kepala_sekolah','visi','misi','deskripsi'];
    $sets = [];
    foreach ($fields as $f) {
        $val = mysqli_real_escape_string($koneksi, $_POST[$f]);
        $sets[] = "$f='$val'";
    }
    $logo_lama = $_POST['logo_lama'];
    if (!empty($_FILES['logo']['name'])) {
        $logo_baru = time() . '_' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], "upload/" . $logo_baru);
        if ($logo_lama && file_exists("upload/" . $logo_lama)) unlink("upload/" . $logo_lama);
    } else {
        $logo_baru = $logo_lama;
    }
    $sets[] = "logo='$logo_baru'";
    mysqli_query($koneksi, "UPDATE profil_sekolah SET " . implode(', ', $sets) . " WHERE id='$id'");
    echo "<script>alert('Profil berhasil diperbarui'); window.location='data_sekolah.php';</script>";
    exit;
}

/* ================= TAMBAH ================= */
if (isset($_POST['tambah'])) {
    $fields = ['nama_sekolah','npsn','alamat','desa','kecamatan','kabupaten','provinsi',
               'email','telepon','website','kepala_sekolah','visi','misi','deskripsi'];
    $cols = implode(',', $fields);
    $vals = [];
    foreach ($fields as $f) {
        $vals[] = "'" . mysqli_real_escape_string($koneksi, $_POST[$f] ?? '') . "'";
    }
    $logo = '';
    if (!empty($_FILES['logo']['name'])) {
        $logo = time() . '_' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], "upload/" . $logo);
    }
    $vals[] = "'$logo'";
    mysqli_query($koneksi, "INSERT INTO profil_sekolah ($cols, logo) VALUES (" . implode(',', $vals) . ")");
    echo "<script>alert('Data berhasil ditambah'); window.location='data_sekolah.php';</script>";
    exit;
}

/* ================= EDIT DATA ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id   = (int)$_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM profil_sekolah WHERE id='$id'"));
}

/* ================= SEARCH ================= */
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$where  = "WHERE 1=1";
if ($search) $where .= " AND (nama_sekolah LIKE '%$search%' OR npsn LIKE '%$search%' OR kepala_sekolah LIKE '%$search%')";

/* ================= PAGINATION ================= */
$limit     = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$page      = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$page      = ($page < 1) ? 1 : $page;
$offset    = ($page - 1) * $limit;
$total     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM profil_sekolah $where"));
$totalPage = ceil($total / $limit);
$data      = mysqli_query($koneksi, "SELECT * FROM profil_sekolah $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
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
.btn-add{height:36px;padding:0 16px;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-size:.85rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .18s;white-space:nowrap}
.btn-add:hover{background:#1d4ed8}
.btn-reset{height:36px;padding:0 12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.82rem;color:var(--muted);cursor:pointer;transition:.18s;text-decoration:none;display:flex;align-items:center}
.btn-reset:hover{background:var(--border);color:var(--text)}
.tbl-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.tbl-card thead th{background:var(--surface)!important;border-bottom:2px solid var(--border)!important;color:var(--muted)!important;font-size:.72rem!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.06em!important;padding:12px 14px!important;white-space:nowrap}
.tbl-card tbody td{padding:12px 14px!important;vertical-align:middle!important;border-color:var(--border)!important;font-size:.875rem!important;color:var(--text)!important}
.tbl-card tbody tr{transition:background .12s}
.tbl-card tbody tr:hover{background:var(--primary-xs)}
.school-logo{width:52px;height:52px;object-fit:contain;border-radius:8px;border:2px solid var(--border);background:var(--surface);padding:2px}
.school-logo-ph{width:52px;height:52px;border-radius:8px;background:var(--surface);border:2px dashed var(--border);display:inline-flex;align-items:center;justify-content:center;color:var(--muted)}
.badge-npsn{font-size:.72rem;background:#f1f5f9;color:var(--muted);padding:3px 8px;border-radius:20px;font-family:monospace}
.badge-kepala{font-size:.72rem;background:#ecfdf5;color:#065f46;padding:3px 9px;border-radius:20px;font-weight:600}
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
.section-divider{font-size:.72rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.08em;padding:8px 0 4px;border-bottom:1px solid var(--border);margin-bottom:12px;margin-top:8px}
.detail-row{display:grid;grid-template-columns:140px 1fr;gap:4px 12px;font-size:.85rem;margin-bottom:6px}
.detail-row .lbl{color:var(--muted);font-weight:600}
.detail-row .val{color:var(--dark)}
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-3">

  <!-- PAGE HEADER -->
  <div class="pg-header">
    <div class="pg-title">
      <div class="icon-wrap"><i class="bi bi-building-fill"></i></div>
      <div>
        Profil Sekolah
        <small>Data dan informasi resmi sekolah</small>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:.82rem">
        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active">Profil Sekolah</li>
      </ol>
    </nav>
  </div>

  <!-- STAT -->
  <?php
  $total_all = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM profil_sekolah"));
  $r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT provinsi) AS p, COUNT(DISTINCT kabupaten) AS k FROM profil_sekolah"));
  ?>
  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-building-fill"></i></div>
      <div><div class="stat-val"><?= $total_all ?></div><div class="stat-lbl">Total Sekolah</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-geo-alt-fill"></i></div>
      <div><div class="stat-val"><?= $r['k'] ?></div><div class="stat-lbl">Kabupaten/Kota</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber"><i class="bi bi-map-fill"></i></div>
      <div><div class="stat-val"><?= $r['p'] ?></div><div class="stat-lbl">Provinsi</div></div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <form method="GET" id="filterForm">
  <div class="toolbar">
    <div class="toolbar-left">
      <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Cari nama sekolah, NPSN, kepala sekolah..."
          value="<?= htmlspecialchars($search) ?>" oninput="autoSubmit()">
      </div>
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
      <?php if ($search): ?>
      <a href="data_sekolah.php" class="btn-reset"><i class="bi bi-x-circle me-1"></i>Reset</a>
      <?php endif; ?>
      <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#tambahSekolah">
        <i class="bi bi-plus-lg"></i> Tambah Sekolah
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
          <th style="width:70px">Logo</th>
          <th>Nama Sekolah</th>
          <th>NPSN</th>
          <th>Kepala Sekolah</th>
          <th>Lokasi</th>
          <th style="width:110px;text-align:center">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($data) == 0): ?>
        <tr><td colspan="7">
          <div class="empty-state">
            <i class="bi bi-building"></i>
            <p>Tidak ada data sekolah<?= $search ? ' untuk "<b>'.htmlspecialchars($search).'</b>"' : '' ?></p>
          </div>
        </td></tr>
      <?php else: $no = $offset + 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><span style="color:var(--muted);font-weight:600"><?= $no++ ?></span></td>
          <td>
            <?php if ($d['logo']): ?>
              <img src="upload/<?= $d['logo'] ?>" class="school-logo" alt="">
            <?php else: ?>
              <div class="school-logo-ph"><i class="bi bi-building"></i></div>
            <?php endif; ?>
          </td>
          <td>
            <div style="font-weight:700;color:var(--dark)"><?= htmlspecialchars($d['nama_sekolah']) ?></div>
            <?php if ($d['website']): ?>
              <div style="font-size:.75rem;color:var(--primary-lt)"><?= htmlspecialchars($d['website']) ?></div>
            <?php endif; ?>
          </td>
          <td><span class="badge-npsn"><?= $d['npsn'] ?: '-' ?></span></td>
          <td><span class="badge-kepala"><?= htmlspecialchars($d['kepala_sekolah']) ?: '-' ?></span></td>
          <td>
            <div style="font-size:.82rem;color:var(--text)"><?= htmlspecialchars($d['kecamatan']) ?></div>
            <div style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($d['kabupaten']) ?>, <?= htmlspecialchars($d['provinsi']) ?></div>
          </td>
          <td style="text-align:center">
            <button class="btn-act view" data-bs-toggle="modal" data-bs-target="#lihatSekolah<?= $d['id'] ?>" title="Detail">
              <i class="bi bi-eye-fill"></i>
            </button>
            <a href="?edit=<?= $d['id'] ?>" class="btn-act edit" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </a>
            <a href="?hapus=<?= $d['id'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>&limit=<?= $limit ?>"
               class="btn-act hapus ms-1" title="Hapus"
               onclick="return confirm('Yakin ingin menghapus data sekolah ini?')">
              <i class="bi bi-trash-fill"></i>
            </a>
          </td>
        </tr>

        <!-- MODAL DETAIL -->
        <div class="modal fade" id="lihatSekolah<?= $d['id'] ?>" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-building-fill me-2 text-primary"></i>Detail Sekolah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-4 p-3" style="background:var(--primary-xs);border-radius:10px">
                  <?php if ($d['logo']): ?>
                    <img src="upload/<?= $d['logo'] ?>" style="width:64px;height:64px;object-fit:contain;border-radius:8px;border:2px solid var(--border);background:#fff;padding:2px">
                  <?php else: ?>
                    <div style="width:64px;height:64px;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.6rem;border:2px solid var(--border)"><i class="bi bi-building"></i></div>
                  <?php endif; ?>
                  <div>
                    <div style="font-weight:800;font-size:1.1rem;color:var(--dark)"><?= htmlspecialchars($d['nama_sekolah']) ?></div>
                    <div style="font-size:.8rem;color:var(--muted)">NPSN: <?= $d['npsn'] ?: '-' ?></div>
                  </div>
                </div>
                <div class="section-divider">Informasi Umum</div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="detail-row"><span class="lbl">Kepala Sekolah</span><span class="val"><?= htmlspecialchars($d['kepala_sekolah']) ?: '-' ?></span></div>
                    <div class="detail-row"><span class="lbl">Email</span><span class="val"><?= $d['email'] ?: '-' ?></span></div>
                    <div class="detail-row"><span class="lbl">Telepon</span><span class="val"><?= $d['telepon'] ?: '-' ?></span></div>
                    <div class="detail-row"><span class="lbl">Website</span><span class="val"><?= $d['website'] ?: '-' ?></span></div>
                  </div>
                  <div class="col-md-6">
                    <div class="detail-row"><span class="lbl">Alamat</span><span class="val"><?= htmlspecialchars($d['alamat']) ?: '-' ?></span></div>
                    <div class="detail-row"><span class="lbl">Desa/Kel</span><span class="val"><?= htmlspecialchars($d['desa']) ?: '-' ?></span></div>
                    <div class="detail-row"><span class="lbl">Kecamatan</span><span class="val"><?= htmlspecialchars($d['kecamatan']) ?: '-' ?></span></div>
                    <div class="detail-row"><span class="lbl">Kabupaten</span><span class="val"><?= htmlspecialchars($d['kabupaten']) ?: '-' ?></span></div>
                    <div class="detail-row"><span class="lbl">Provinsi</span><span class="val"><?= htmlspecialchars($d['provinsi']) ?: '-' ?></span></div>
                  </div>
                </div>
                <?php if ($d['visi']): ?>
                <div class="section-divider">Visi & Misi</div>
                <p style="font-size:.85rem"><b>Visi:</b> <?= htmlspecialchars($d['visi']) ?></p>
                <?php if ($d['misi']): ?><p style="font-size:.85rem"><b>Misi:</b> <?= htmlspecialchars($d['misi']) ?></p><?php endif; ?>
                <?php endif; ?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <a href="?edit=<?= $d['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-fill me-1"></i>Edit</a>
              </div>
            </div>
          </div>
        </div>

      <?php endwhile; endif; ?>
      </tbody>
    </table>
    </div>

    <!-- PAGINATION -->
    <div class="pagi-wrap">
      <div>Menampilkan <b><?= $total > 0 ? $offset+1 : 0 ?></b>–<b><?= min($offset+$limit, $total) ?></b> dari <b><?= $total ?></b> sekolah</div>
      <?php if ($totalPage > 1): ?>
      <ul class="pagination">
        <li class="page-item <?= $page==1?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>"><i class="bi bi-chevron-left"></i></a>
        </li>
        <?php for($i=1;$i<=$totalPage;$i++): ?>
        <li class="page-item <?= $i==$page?'active':'' ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page==$totalPage?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>"><i class="bi bi-chevron-right"></i></a>
        </li>
      </ul>
      <?php endif; ?>
    </div>
  </div>

  <!-- FORM EDIT (inline, bukan modal karena field banyak) -->
  <?php if ($edit): ?>
  <div class="card mt-4" style="border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow)">
    <div class="card-header" style="background:var(--surface);border-bottom:1px solid var(--border);padding:16px 20px;border-radius:var(--radius) var(--radius) 0 0">
      <h5 style="margin:0;font-weight:700;color:var(--dark)"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Profil Sekolah</h5>
    </div>
    <div class="card-body p-4">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <input type="hidden" name="logo_lama" value="<?= $edit['logo'] ?>">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Nama Sekolah</label><input type="text" name="nama_sekolah" class="form-control" value="<?= htmlspecialchars($edit['nama_sekolah']) ?>"></div>
          <div class="col-md-6"><label class="form-label">NPSN</label><input type="text" name="npsn" class="form-control" value="<?= $edit['npsn'] ?>"></div>
          <div class="col-md-12"><label class="form-label">Alamat</label><input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($edit['alamat']) ?>"></div>
          <div class="col-md-4"><label class="form-label">Desa/Kelurahan</label><input type="text" name="desa" class="form-control" value="<?= htmlspecialchars($edit['desa']) ?>"></div>
          <div class="col-md-4"><label class="form-label">Kecamatan</label><input type="text" name="kecamatan" class="form-control" value="<?= htmlspecialchars($edit['kecamatan']) ?>"></div>
          <div class="col-md-4"><label class="form-label">Kabupaten/Kota</label><input type="text" name="kabupaten" class="form-control" value="<?= htmlspecialchars($edit['kabupaten']) ?>"></div>
          <div class="col-md-6"><label class="form-label">Provinsi</label><input type="text" name="provinsi" class="form-control" value="<?= htmlspecialchars($edit['provinsi']) ?>"></div>
          <div class="col-md-6"><label class="form-label">Kepala Sekolah</label><input type="text" name="kepala_sekolah" class="form-control" value="<?= htmlspecialchars($edit['kepala_sekolah']) ?>"></div>
          <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= $edit['email'] ?>"></div>
          <div class="col-md-4"><label class="form-label">Telepon</label><input type="text" name="telepon" class="form-control" value="<?= $edit['telepon'] ?>"></div>
          <div class="col-md-4"><label class="form-label">Website</label><input type="text" name="website" class="form-control" value="<?= $edit['website'] ?>"></div>
          <div class="col-md-6">
            <label class="form-label">Logo Sekolah</label>
            <?php if ($edit['logo']): ?><div class="mb-2"><img src="upload/<?= $edit['logo'] ?>" width="80" style="border-radius:8px;border:2px solid var(--border)"></div><?php endif; ?>
            <input type="file" name="logo" class="form-control" accept="image/*">
          </div>
          <div class="col-md-12"><label class="form-label">Visi</label><textarea name="visi" class="form-control" rows="3"><?= htmlspecialchars($edit['visi']) ?></textarea></div>
          <div class="col-md-12"><label class="form-label">Misi</label><textarea name="misi" class="form-control" rows="3"><?= htmlspecialchars($edit['misi']) ?></textarea></div>
          <div class="col-md-12"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($edit['deskripsi']) ?></textarea></div>
        </div>
        <div class="mt-4 d-flex gap-2">
          <button type="submit" name="update" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Update</button>
          <a href="data_sekolah.php" class="btn btn-secondary btn-sm">Batal</a>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>
</main>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="tambahSekolah" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-building-fill me-2 text-primary"></i>Tambah Data Sekolah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nama Sekolah <span class="text-danger">*</span></label><input type="text" name="nama_sekolah" class="form-control" placeholder="Nama lengkap sekolah" required></div>
            <div class="col-md-6"><label class="form-label">NPSN</label><input type="text" name="npsn" class="form-control" placeholder="Nomor Pokok Sekolah Nasional"></div>
            <div class="col-md-12"><label class="form-label">Alamat</label><input type="text" name="alamat" class="form-control" placeholder="Jalan, nomor, RT/RW..."></div>
            <div class="col-md-4"><label class="form-label">Desa/Kelurahan</label><input type="text" name="desa" class="form-control" placeholder="Nama desa/kelurahan"></div>
            <div class="col-md-4"><label class="form-label">Kecamatan</label><input type="text" name="kecamatan" class="form-control" placeholder="Nama kecamatan"></div>
            <div class="col-md-4"><label class="form-label">Kabupaten/Kota</label><input type="text" name="kabupaten" class="form-control" placeholder="Nama kabupaten/kota"></div>
            <div class="col-md-6"><label class="form-label">Provinsi</label><input type="text" name="provinsi" class="form-control" placeholder="Nama provinsi"></div>
            <div class="col-md-6"><label class="form-label">Kepala Sekolah</label><input type="text" name="kepala_sekolah" class="form-control" placeholder="Nama kepala sekolah"></div>
            <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="email@sekolah.sch.id"></div>
            <div class="col-md-4"><label class="form-label">Telepon</label><input type="text" name="telepon" class="form-control" placeholder="(021) xxxx-xxxx"></div>
            <div class="col-md-4"><label class="form-label">Website</label><input type="text" name="website" class="form-control" placeholder="https://"></div>
            <div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
            <div class="col-md-12"><label class="form-label">Visi</label><textarea name="visi" class="form-control" rows="2" placeholder="Visi sekolah..."></textarea></div>
            <div class="col-md-12"><label class="form-label">Misi</label><textarea name="misi" class="form-control" rows="2" placeholder="Misi sekolah..."></textarea></div>
            <div class="col-md-12"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi umum sekolah..."></textarea></div>
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

<script>
let searchTimer;
function autoSubmit() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
}
</script>

<?php include "template/footer.php"; ?>