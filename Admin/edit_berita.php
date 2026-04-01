<?php
include "template/header.php";
include "template/menu.php";
include "../koneksi.php"; // sesuaikan dengan file koneksi kamu
// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Ambil data berita berdasarkan ID
$query = mysqli_query($koneksi, "SELECT * FROM berita WHERE id='$id'");
$data = mysqli_fetch_assoc($query);
if (!$data) {
 echo "<script>alert('Data tidak ditemukan');window.location='data_berita.php';</script>";
 exit;
}
?>
<main class="app-main">
 <!-- App Content Header -->
 <div class="app-content-header">
 <div class="container-fluid">
 <div class="row">
 <div class="col-sm-6">
 <h3 class="mb-0">Edit Berita</h3>
 </div>
 <div class="col-sm-6">
 <ol class="breadcrumb float-sm-end">
 <li class="breadcrumb-item"><a href="#">Home</a></li>
 <li class="breadcrumb-item active">Edit Berita</li>
 </ol>
 </div>
 </div>
 </div>
 </div>
 <!-- App Content -->
 <div class="app-content">
 <div class="container-fluid">
 <div class="row">
 <div class="col-12">
 <div class="card">
 <div class="card-header">
 <h3 class="card-title">Form Edit Berita</h3>
 </div>
 <div class="card-body">
 <form action="proses_edit_berita.php" method="post" enctype="multipart/form-data">
 <!-- ID -->
 <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
 <!-- Judul -->
 <div class="mb-3">
 <label class="form-label">Judul Berita</label>
 <input type="text" name="judul" class="form-control"
 value="<?php echo htmlspecialchars($data['judul']); ?>" required>
 </div>
 <!-- Isi -->
 <div class="mb-3">
 <label class="form-label">Isi Berita</label>
 <textarea name="isi" id="editor" rows="10" class="form-control" required>
<?php echo htmlspecialchars($data['isi']); ?>
 </textarea>
 </div>
 <!-- Gambar -->
 <div class="mb-3">
 <label class="form-label">Gambar</label>
 <input type="file" name="gambar" class="form-control" accept="image/*">
 <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar</small><br>
 <?php if (!empty($data['gambar'])): ?>
 <img src="upload/<?php echo $data['gambar']; ?>" width="150" class="mt-2">
 <?php endif; ?>
 </div>
 <!-- Tanggal -->
 <div class="mb-3">
 <label class="form-label">Tanggal</label>
 <input type="date" name="tanggal" class="form-control"
 value="<?php echo $data['tanggal']; ?>" required>
 </div>
 <!-- Penulis -->
 <div class="mb-3">
 <label class="form-label">Penulis</label>
 <input type="text" name="penulis" class="form-control"
 value="<?php echo htmlspecialchars($data['penulis']); ?>" required>
 </div>
 <div class="card-footer">
 <button type="submit" class="btn btn-warning">Update</button>
 <a href="data_berita.php" class="btn btn-secondary">Kembali</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
</main>
<?php include "template/footer.php"; ?>
<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
 CKEDITOR.replace('editor');
</script>