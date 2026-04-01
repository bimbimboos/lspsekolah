<?php
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";

/* ================= TAMBAH ================= */
if (isset($_POST['tambah'])) {
    $judul   = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi     = mysqli_real_escape_string($koneksi, $_POST['isi']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $tanggal = date('Y-m-d');

    $gambar = '';
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . '_' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'upload/' . $gambar);
    }

    mysqli_query($koneksi, "INSERT INTO berita (judul, isi, gambar, tanggal, penulis)
        VALUES ('$judul','$isi','$gambar','$tanggal','$penulis')");

    echo "<script>alert('Berita berhasil ditambah'); window.location='data_berita.php';</script>";
    exit;
}

/* ================= HAPUS ================= */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q  = mysqli_query($koneksi, "SELECT gambar FROM berita WHERE id='$id'");
    $d  = mysqli_fetch_assoc($q);
    if ($d && !empty($d['gambar']) && file_exists("upload/" . $d['gambar'])) {
        unlink("upload/" . $d['gambar']);
    }
    mysqli_query($koneksi, "DELETE FROM berita WHERE id='$id'");
    echo "<script>alert('Berita berhasil dihapus'); window.location='data_berita.php';</script>";
    exit;
}

/* ================= EDIT (POST) ================= */
if (isset($_POST['edit'])) {
    $id      = (int)$_POST['id'];
    $judul   = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi     = mysqli_real_escape_string($koneksi, $_POST['isi']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $gambar_lama = $_POST['gambar_lama'];

    if (!empty($_FILES['gambar']['name'])) {
        $gambar_baru = time() . '_' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'upload/' . $gambar_baru);
        if (!empty($gambar_lama) && file_exists("upload/" . $gambar_lama)) unlink("upload/" . $gambar_lama);
    } else {
        $gambar_baru = $gambar_lama;
    }

    mysqli_query($koneksi, "UPDATE berita SET
        judul='$judul', isi='$isi', gambar='$gambar_baru', penulis='$penulis'
        WHERE id='$id'");

    echo "<script>alert('Berita berhasil diupdate'); window.location='data_berita.php';</script>";
    exit;
}

/* ================= FILTER & SEARCH ================= */
$search       = isset($_GET['search'])       ? mysqli_real_escape_string($koneksi, $_GET['search'])       : '';
$filter_penulis = isset($_GET['filter_penulis']) ? mysqli_real_escape_string($koneksi, $_GET['filter_penulis']) : '';
$filter_bulan   = isset($_GET['filter_bulan'])   ? mysqli_real_escape_string($koneksi, $_GET['filter_bulan'])   : '';

$where = "WHERE 1=1";
if ($search)         $where .= " AND (judul LIKE '%$search%' OR penulis LIKE '%$search%')";
if ($filter_penulis) $where .= " AND penulis='$filter_penulis'";
if ($filter_bulan)   $where .= " AND DATE_FORMAT(tanggal,'%Y-%m')='$filter_bulan'";

/* ================= PAGINATION ================= */
$limit     = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$page      = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$page      = ($page < 1) ? 1 : $page;
$offset    = ($page - 1) * $limit;
$total     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM berita $where"));
$totalPage = ceil($total / $limit);
$data      = mysqli_query($koneksi, "SELECT * FROM berita $where ORDER BY id DESC LIMIT $limit OFFSET $offset");

$penulis_list = mysqli_query($koneksi, "SELECT DISTINCT penulis FROM berita WHERE penulis != '' ORDER BY penulis");
$bulan_list   = mysqli_query($koneksi, "SELECT DISTINCT DATE_FORMAT(tanggal,'%Y-%m') AS bln, DATE_FORMAT(tanggal,'%M %Y') AS lbl FROM berita ORDER BY bln DESC");
?>

<style>
:root {
  --primary:#1e40af;--primary-lt:#3b82f6;--primary-xs:#eff6ff;
  --accent:#f59e0b;--danger:#ef4444;--success:#10b981;
  --dark:#0f172a;--text:#1e293b;--muted:#64748b;
  --border:#e2e8f0;--surface:#f8fafc;--white:#ffffff;
  --radius:12px;--radius-sm:8px;
  --shadow:0 1px 3px rgba(0,0,0,.08),0 4px 16px rgba(0,0,0,.06);
  --shadow-md:0 4px 12px rgba(0,0,0,.12),0 8px 32px rgba(0,0,0,.08);
}
.pg-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px}
.pg-title{font-size:1.5rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:10px}
.pg-title .icon-wrap{width:40px;height:40px;background:var(--primary);border-radius:var(--radius-sm);display:grid;place-items:center;color:#fff;font-size:1.1rem}
.pg-title small{font-size:.85rem;font-weight:400;color:var(--muted);display:block;margin-top:2px}
.stat-row{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.stat-card{flex:1;min-width:130px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:16px 18px;box-shadow:var(--shadow);display:flex;align-items:center;gap:14px}
.stat-icon{width:44px;height:44px;border-radius:10px;display:grid;place-items:center;font-size:1.2rem;flex-shrink:0}
.stat-icon.blue{background:#eff6ff;color:var(--primary)}
.stat-icon.green{background:#ecfdf5;color:var(--success)}
.stat-icon.amber{background:#fffbeb;color:var(--accent)}
.stat-val{font-size:1.5rem;font-weight:700;color:var(--dark);line-height:1}
.stat-lbl{font-size:.75rem;color:var(--muted);margin-top:3px}
.toolbar{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:14px 18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px;box-shadow:var(--shadow)}
.toolbar-left{display:flex;gap:10px;flex-wrap:wrap;flex:1;align-items:center}
.toolbar-right{display:flex;gap:8px;align-items:center}
.search-wrap{position:relative;min-width:220px}
.search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem}
.search-wrap input{padding-left:32px!important;border-radius:var(--radius-sm)!important;border-color:var(--border)!important;font-size:.85rem!important;height:36px!important}
.search-wrap input:focus{border-color:var(--primary-lt)!important;box-shadow:0 0 0 3px rgba(59,130,246,.15)!important}
.filter-select{height:36px!important;font-size:.85rem!important;border-radius:var(--radius-sm)!important;border-color:var(--border)!important;min-width:130px}
.btn-add{height:36px;padding:0 16px;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-size:.85rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .18s;white-space:nowrap}
.btn-add:hover{background:#1d4ed8}
.btn-reset{height:36px;padding:0 12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.82rem;color:var(--muted);cursor:pointer;transition:.18s;text-decoration:none;display:flex;align-items:center}
.btn-reset:hover{background:var(--border);color:var(--text)}
.tbl-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.tbl-card thead th{background:var(--surface)!important;border-bottom:2px solid var(--border)!important;color:var(--muted)!important;font-size:.72rem!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.06em!important;padding:12px 14px!important;white-space:nowrap}
.tbl-card tbody td{padding:12px 14px!important;vertical-align:middle!important;border-color:var(--border)!important;font-size:.875rem!important;color:var(--text)!important}
.tbl-card tbody tr{transition:background .12s}
.tbl-card tbody tr:hover{background:var(--primary-xs)}
.news-thumb{width:60px;height:44px;object-fit:cover;border-radius:6px;border:2px solid var(--border)}
.news-thumb-ph{width:60px;height:44px;border-radius:6px;background:var(--surface);border:2px solid var(--border);display:inline-flex;align-items:center;justify-content:center;color:var(--muted);font-size:.9rem}
.isi-preview{max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;color:var(--muted)}
.badge-tgl{font-size:.72rem;background:#f1f5f9;color:var(--muted);padding:3px 8px;border-radius:20px}
.badge-penulis{font-size:.72rem;background:#eff6ff;color:var(--primary);padding:3px 9px;border-radius:20px;font-weight:600}
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
.empty-state p{font-size:.9rem}
.limit-wrap{display:flex;align-items:center;gap:6px;font-size:.82rem;color:var(--muted)}
.limit-wrap select{width:auto!important;height:30px!important;font-size:.8rem!important;padding:0 8px!important}
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-3">

  <!-- PAGE HEADER -->
  <div class="pg-header">
    <div class="pg-title">
      <div class="icon-wrap"><i class="bi bi-newspaper"></i></div>
      <div>
        Data Berita
        <small>Manajemen konten berita sekolah</small>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:.82rem">
        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active">Data Berita</li>
      </ol>
    </nav>
  </div>

  <!-- STAT CARDS -->
  <?php
  $total_all     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM berita"));
  $total_bulan   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM berita WHERE MONTH(tanggal)=MONTH(NOW()) AND YEAR(tanggal)=YEAR(NOW())"));
  $total_penulis = mysqli_num_rows(mysqli_query($koneksi, "SELECT DISTINCT penulis FROM berita WHERE penulis!=''"));
  ?>
  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-newspaper"></i></div>
      <div><div class="stat-val"><?= $total_all ?></div><div class="stat-lbl">Total Berita</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-calendar-check"></i></div>
      <div><div class="stat-val"><?= $total_bulan ?></div><div class="stat-lbl">Bulan Ini</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber"><i class="bi bi-person-badge"></i></div>
      <div><div class="stat-val"><?= $total_penulis ?></div><div class="stat-lbl">Penulis</div></div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <form method="GET" id="filterForm">
  <div class="toolbar">
    <div class="toolbar-left">
      <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Cari judul, penulis..."
          value="<?= htmlspecialchars($search) ?>" oninput="autoSubmit()">
      </div>
      <select name="filter_penulis" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">Semua Penulis</option>
        <?php while($p = mysqli_fetch_assoc($penulis_list)): ?>
        <option value="<?= $p['penulis'] ?>" <?= $filter_penulis==$p['penulis']?'selected':'' ?>><?= htmlspecialchars($p['penulis']) ?></option>
        <?php endwhile; ?>
      </select>
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
      <?php if ($search || $filter_penulis || $filter_bulan): ?>
      <a href="data_berita.php" class="btn-reset"><i class="bi bi-x-circle me-1"></i>Reset</a>
      <?php endif; ?>
      <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#tambahBerita">
        <i class="bi bi-plus-lg"></i> Tambah Berita
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
          <th style="width:70px">Gambar</th>
          <th>Judul</th>
          <th>Isi Berita</th>
          <th>Penulis</th>
          <th>Tanggal</th>
          <th style="width:90px;text-align:center">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($data) == 0): ?>
        <tr><td colspan="7">
          <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Tidak ada berita yang ditemukan<?= $search ? ' untuk "<b>'.htmlspecialchars($search).'</b>"' : '' ?></p>
          </div>
        </td></tr>
      <?php else: $no = $offset + 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><span style="color:var(--muted);font-weight:600"><?= $no++ ?></span></td>
          <td>
            <?php if ($d['gambar']): ?>
              <img src="upload/<?= $d['gambar'] ?>" class="news-thumb" alt="">
            <?php else: ?>
              <div class="news-thumb-ph"><i class="bi bi-image"></i></div>
            <?php endif; ?>
          </td>
          <td>
            <div style="font-weight:600;color:var(--dark);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?= htmlspecialchars($d['judul']) ?>
            </div>
          </td>
          <td><div class="isi-preview"><?= strip_tags($d['isi']) ?></div></td>
          <td><span class="badge-penulis"><?= htmlspecialchars($d['penulis']) ?: '-' ?></span></td>
          <td><span class="badge-tgl"><i class="bi bi-calendar2 me-1"></i><?= date('d M Y', strtotime($d['tanggal'])) ?></span></td>
          <td style="text-align:center">
            <button class="btn-act edit" data-bs-toggle="modal" data-bs-target="#editBerita<?= $d['id'] ?>" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </button>
            <a href="?hapus=<?= $d['id'] ?>&search=<?= urlencode($search) ?>&filter_penulis=<?= urlencode($filter_penulis) ?>&filter_bulan=<?= urlencode($filter_bulan) ?>&page=<?= $page ?>&limit=<?= $limit ?>"
               class="btn-act hapus ms-1" title="Hapus"
               onclick="return confirm('Yakin ingin menghapus berita ini?')">
              <i class="bi bi-trash-fill"></i>
            </a>
          </td>
        </tr>

        <!-- MODAL EDIT -->
        <div class="modal fade" id="editBerita<?= $d['id'] ?>" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                  <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Berita</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $d['id'] ?>">
                  <input type="hidden" name="gambar_lama" value="<?= $d['gambar'] ?>">
                  <div class="row g-3">
                    <div class="col-md-8">
                      <label class="form-label">Judul Berita</label>
                      <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($d['judul']) ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Penulis</label>
                      <input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($d['penulis']) ?>">
                    </div>
                    <div class="col-md-12">
                      <label class="form-label">Isi Berita</label>
                      <textarea name="isi" class="form-control" rows="5" required><?= htmlspecialchars($d['isi']) ?></textarea>
                    </div>
                    <div class="col-md-12">
                      <label class="form-label">Gambar</label>
                      <?php if ($d['gambar']): ?>
                        <div class="mb-2"><img src="upload/<?= $d['gambar'] ?>" style="width:120px;border-radius:8px;border:2px solid var(--border)"></div>
                      <?php endif; ?>
                      <input type="file" name="gambar" class="form-control" accept="image/*">
                    </div>
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
      <div>Menampilkan <b><?= $total > 0 ? $offset+1 : 0 ?></b>–<b><?= min($offset+$limit, $total) ?></b> dari <b><?= $total ?></b> berita</div>
      <?php if ($totalPage > 1): ?>
      <ul class="pagination">
        <li class="page-item <?= $page==1?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter_penulis=<?= urlencode($filter_penulis) ?>&filter_bulan=<?= urlencode($filter_bulan) ?>&limit=<?= $limit ?>"><i class="bi bi-chevron-left"></i></a>
        </li>
        <?php for($i=1;$i<=$totalPage;$i++): ?>
        <li class="page-item <?= $i==$page?'active':'' ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter_penulis=<?= urlencode($filter_penulis) ?>&filter_bulan=<?= urlencode($filter_bulan) ?>&limit=<?= $limit ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page==$totalPage?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter_penulis=<?= urlencode($filter_penulis) ?>&filter_bulan=<?= urlencode($filter_bulan) ?>&limit=<?= $limit ?>"><i class="bi bi-chevron-right"></i></a>
        </li>
      </ul>
      <?php endif; ?>
    </div>
  </div>

</div>
</div>
</main>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="tambahBerita" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-newspaper me-2 text-primary"></i>Tambah Berita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Judul Berita <span class="text-danger">*</span></label>
              <input type="text" name="judul" class="form-control" placeholder="Masukkan judul berita" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Penulis</label>
              <input type="text" name="penulis" class="form-control" placeholder="Nama penulis">
            </div>
            <div class="col-md-12">
              <label class="form-label">Isi Berita <span class="text-danger">*</span></label>
              <textarea name="isi" class="form-control" rows="6" placeholder="Tulis isi berita di sini..." required></textarea>
            </div>
            <div class="col-md-12">
              <label class="form-label">Gambar</label>
              <input type="file" name="gambar" class="form-control" accept="image/*">
            </div>
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