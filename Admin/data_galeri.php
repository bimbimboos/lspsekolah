<?php
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";

/* ================= TAMBAH ================= */
if (isset($_POST['tambah'])) {
    $judul     = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $tanggal   = date('Y-m-d');

    $nama_gambar = '';
    if (!empty($_FILES['gambar']['name'])) {
        $nama_gambar = time() . '_' . basename($_FILES['gambar']['name']);
        if (!is_dir("upload/")) mkdir("upload/", 0777, true);
        move_uploaded_file($_FILES['gambar']['tmp_name'], "upload/" . $nama_gambar);
    }

    mysqli_query($koneksi, "INSERT INTO galeri (judul, deskripsi, gambar, tanggal_upload)
        VALUES ('$judul', '$deskripsi', '$nama_gambar', '$tanggal')");

    echo "<script>alert('Data berhasil ditambah'); window.location='data_galeri.php';</script>";
    exit;
}

/* ================= HAPUS ================= */
if (isset($_GET['hapus'])) {
    $id  = (int)$_GET['hapus'];
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT gambar FROM galeri WHERE id='$id'"));
    if ($cek && $cek['gambar'] && file_exists("upload/" . $cek['gambar'])) {
        unlink("upload/" . $cek['gambar']);
    }
    mysqli_query($koneksi, "DELETE FROM galeri WHERE id='$id'");
    echo "<script>alert('Data berhasil dihapus'); window.location='data_galeri.php';</script>";
    exit;
}

/* ================= EDIT (POST) ================= */
if (isset($_POST['edit'])) {
    $id        = (int)$_POST['id'];
    $judul     = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $gambar_lama = $_POST['gambar_lama'];

    if (!empty($_FILES['gambar']['name'])) {
        $nama_gambar = time() . '_' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], "upload/" . $nama_gambar);
        if ($gambar_lama && file_exists("upload/" . $gambar_lama)) unlink("upload/" . $gambar_lama);
    } else {
        $nama_gambar = $gambar_lama;
    }

    mysqli_query($koneksi, "UPDATE galeri SET judul='$judul', deskripsi='$deskripsi', gambar='$nama_gambar' WHERE id='$id'");
    echo "<script>alert('Data berhasil diupdate'); window.location='data_galeri.php';</script>";
    exit;
}

/* ================= FILTER & SEARCH ================= */
$search      = isset($_GET['search'])      ? mysqli_real_escape_string($koneksi, $_GET['search'])      : '';
$filter_bulan = isset($_GET['filter_bulan']) ? mysqli_real_escape_string($koneksi, $_GET['filter_bulan']) : '';

$where = "WHERE 1=1";
if ($search)       $where .= " AND (judul LIKE '%$search%' OR deskripsi LIKE '%$search%')";
if ($filter_bulan) $where .= " AND DATE_FORMAT(tanggal_upload,'%Y-%m')='$filter_bulan'";

/* ================= PAGINATION ================= */
$limit     = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$page      = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$page      = ($page < 1) ? 1 : $page;
$offset    = ($page - 1) * $limit;
$total     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM galeri $where"));
$totalPage = ceil($total / $limit);
$data      = mysqli_query($koneksi, "SELECT * FROM galeri $where ORDER BY id DESC LIMIT $limit OFFSET $offset");

$bulan_list = mysqli_query($koneksi, "SELECT DISTINCT DATE_FORMAT(tanggal_upload,'%Y-%m') AS bln, DATE_FORMAT(tanggal_upload,'%M %Y') AS lbl FROM galeri ORDER BY bln DESC");
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
.galeri-thumb{width:80px;height:55px;object-fit:cover;border-radius:8px;border:2px solid var(--border);cursor:pointer;transition:transform .18s}
.galeri-thumb:hover{transform:scale(1.08)}
.galeri-thumb-ph{width:80px;height:55px;border-radius:8px;background:var(--surface);border:2px dashed var(--border);display:inline-flex;align-items:center;justify-content:center;color:var(--muted)}
.deskripsi-preview{max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;color:var(--muted)}
.badge-tgl{font-size:.72rem;background:#f1f5f9;color:var(--muted);padding:3px 8px;border-radius:20px}
.btn-act{width:32px;height:32px;border-radius:var(--radius-sm);border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;transition:.18s}
.btn-act.edit{background:#fffbeb;color:var(--accent)}
.btn-act.edit:hover{background:var(--accent);color:#fff}
.btn-act.hapus{background:#fff1f2;color:var(--danger)}
.btn-act.hapus:hover{background:var(--danger);color:#fff}
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
/* Lightbox */
.lightbox{display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;align-items:center;justify-content:center}
.lightbox.show{display:flex}
.lightbox img{max-width:90vw;max-height:85vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.lightbox-close{position:absolute;top:18px;right:22px;background:rgba(255,255,255,.15);border:none;color:#fff;width:40px;height:40px;border-radius:50%;font-size:1.2rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.18s}
.lightbox-close:hover{background:rgba(255,255,255,.3)}
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-3">

  <!-- PAGE HEADER -->
  <div class="pg-header">
    <div class="pg-title">
      <div class="icon-wrap"><i class="bi bi-images"></i></div>
      <div>
        Data Galeri
        <small>Manajemen foto dan dokumentasi sekolah</small>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:.82rem">
        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active">Data Galeri</li>
      </ol>
    </nav>
  </div>

  <!-- STAT -->
  <?php
  $total_all   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM galeri"));
  $total_bulan = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM galeri WHERE MONTH(tanggal_upload)=MONTH(NOW()) AND YEAR(tanggal_upload)=YEAR(NOW())"));
  $total_foto  = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM galeri WHERE gambar != ''"));
  ?>
  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-images"></i></div>
      <div><div class="stat-val"><?= $total_all ?></div><div class="stat-lbl">Total Galeri</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-image"></i></div>
      <div><div class="stat-val"><?= $total_foto ?></div><div class="stat-lbl">Dengan Foto</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber"><i class="bi bi-calendar-check"></i></div>
      <div><div class="stat-val"><?= $total_bulan ?></div><div class="stat-lbl">Bulan Ini</div></div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <form method="GET" id="filterForm">
  <div class="toolbar">
    <div class="toolbar-left">
      <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Cari judul, deskripsi..."
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
      <a href="data_galeri.php" class="btn-reset"><i class="bi bi-x-circle me-1"></i>Reset</a>
      <?php endif; ?>
      <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#tambahGaleri">
        <i class="bi bi-plus-lg"></i> Tambah Galeri
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
          <th style="width:100px">Gambar</th>
          <th>Judul</th>
          <th>Deskripsi</th>
          <th>Tanggal Upload</th>
          <th style="width:90px;text-align:center">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($data) == 0): ?>
        <tr><td colspan="6">
          <div class="empty-state">
            <i class="bi bi-images"></i>
            <p>Tidak ada galeri yang ditemukan<?= $search ? ' untuk "<b>'.htmlspecialchars($search).'</b>"' : '' ?></p>
          </div>
        </td></tr>
      <?php else: $no = $offset + 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><span style="color:var(--muted);font-weight:600"><?= $no++ ?></span></td>
          <td>
            <?php if ($d['gambar']): ?>
              <img src="upload/<?= $d['gambar'] ?>" class="galeri-thumb" onclick="showLightbox('upload/<?= $d['gambar'] ?>')" title="Klik untuk perbesar">
            <?php else: ?>
              <div class="galeri-thumb-ph"><i class="bi bi-image"></i></div>
            <?php endif; ?>
          </td>
          <td><div style="font-weight:600;color:var(--dark)"><?= htmlspecialchars($d['judul']) ?></div></td>
          <td><div class="deskripsi-preview"><?= htmlspecialchars($d['deskripsi']) ?: '-' ?></div></td>
          <td><span class="badge-tgl"><i class="bi bi-calendar2 me-1"></i><?= $d['tanggal_upload'] ? date('d M Y', strtotime($d['tanggal_upload'])) : '-' ?></span></td>
          <td style="text-align:center">
            <button class="btn-act edit" data-bs-toggle="modal" data-bs-target="#editGaleri<?= $d['id'] ?>" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </button>
            <a href="?hapus=<?= $d['id'] ?>&search=<?= urlencode($search) ?>&filter_bulan=<?= urlencode($filter_bulan) ?>&page=<?= $page ?>&limit=<?= $limit ?>"
               class="btn-act hapus ms-1" title="Hapus"
               onclick="return confirm('Yakin ingin menghapus foto ini?')">
              <i class="bi bi-trash-fill"></i>
            </a>
          </td>
        </tr>

        <!-- MODAL EDIT -->
        <div class="modal fade" id="editGaleri<?= $d['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                  <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Galeri</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $d['id'] ?>">
                  <input type="hidden" name="gambar_lama" value="<?= $d['gambar'] ?>">
                  <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($d['judul']) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($d['deskripsi']) ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Gambar</label>
                    <?php if ($d['gambar']): ?>
                      <div class="mb-2"><img src="upload/<?= $d['gambar'] ?>" style="width:120px;border-radius:8px;border:2px solid var(--border)"></div>
                    <?php endif; ?>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
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
      <div>Menampilkan <b><?= $total > 0 ? $offset+1 : 0 ?></b>–<b><?= min($offset+$limit, $total) ?></b> dari <b><?= $total ?></b> item</div>
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

<!-- MODAL TAMBAH -->
<div class="modal fade" id="tambahGaleri" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-images me-2 text-primary"></i>Tambah Galeri</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Judul <span class="text-danger">*</span></label>
            <input type="text" name="judul" class="form-control" placeholder="Judul foto / album" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Gambar</label>
            <input type="file" name="gambar" class="form-control" accept="image/*">
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

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="this.classList.remove('show')">
  <button class="lightbox-close" onclick="document.getElementById('lightbox').classList.remove('show')">
    <i class="bi bi-x-lg"></i>
  </button>
  <img id="lightboxImg" src="" alt="">
</div>

<script>
let searchTimer;
function autoSubmit() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
}
function showLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('show');
}
</script>

<?php include "template/footer.php"; ?>