<?php
include "../koneksi.php";
include "template/header.php";
include "template/menu.php";
/* ================= AMBIL DATA ================= */
$id = $_GET['id'];
$q = mysqli_query($koneksi, "SELECT * FROM galeri WHERE id='$id'");
$d = mysqli_fetch_assoc($q);
/* ================= PROSES UPDATE ================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 $judul = $_POST['judul'];
 $deskripsi = $_POST['deskripsi'];
 $tanggal_upload = $_POST['tanggal_upload'];
 $gambar_lama = $_POST['gambar_lama'];
 // cek upload gambar baru
 if (!empty($_FILES['gambar']['name'])) {
 $gambar_baru = time().'_'.$_FILES['gambar']['name'];
 move_uploaded_file($_FILES['gambar']['tmp_name'], 'upload/'.$gambar_baru);
 // hapus gambar lama
 if ($gambar_lama && file_exists('upload/'.$gambar_lama)) {
 unlink('upload/'.$gambar_lama);
 }
 } else {
 $gambar_baru = $gambar_lama;
 }
 mysqli_query($koneksi, "UPDATE galeri SET
 judul='$judul',
 deskripsi='$deskripsi',
 gambar='$gambar_baru',
 tanggal_upload='$tanggal_upload'
 WHERE id='$id'
 ");
 echo "<script>alert('Galeri berhasil diupdate');window.location='data_galeri.php';</script>";
}
?>
<main class="app-main">
 <div class="app-content-header">
 <div class="container-fluid">
 <div class="row">
 <div class="col-sm-6">
 <h3 class="mb-0">Edit Galeri</h3>
 </div>
 <div class="col-sm-6">
 <ol class="breadcrumb float-sm-end">
 <li class="breadcrumb-item"><a href="#">Home</a></li>
 <li class="breadcrumb-item active">Edit Galeri</li>
 </ol>
 </div>
 </div>
 </div>
 </div>
 <div class="app-content">
 <div class="container-fluid">
 <div class="card">
 <div class="card-header">
 <h3 class="card-title">Form Edit Galeri</h3>
 </div>
 <div class="card-body">
 <form method="post" enctype="multipart/form-data">
 <input type="hidden" name="gambar_lama" value="<?= $d['gambar']; ?>">
 <!-- Judul -->
 <div class="mb-3">
 <label class="form-label">Judul Galeri</label>
 <input type="text" name="judul" class="form-control" value="<?= $d['judul']; ?>"
required>
 </div>
 <!-- Deskripsi -->
 <div class="mb-3">
 <label class="form-label">Deskripsi</label>
 <textarea name="deskripsi" id="editor" rows="6" class="form-control"><?=
htmlspecialchars_decode($d['deskripsi']); ?></textarea>
 </div>
 <!-- Gambar -->
 <div class="mb-3">
 <label class="form-label">Gambar</label><br>
 <img src="upload/<?= $d['gambar']; ?>" width="150" class="mb-2"><br>
 <input type="file" name="gambar" class="form-control" accept="image/*">
 <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar</small>
 </div>
 <!-- Tanggal -->
 <div class="mb-3">
 <label class="form-label">Tanggal Upload</label>
 <input type="date" name="tanggal_upload" class="form-control" value="<?=
$d['tanggal_upload']; ?>" required>
 </div>
 <div class="card-footer">
 <button type="submit" class="btn btn-primary">Update</button>
 <a href="data_galeri.php" class="btn btn-secondary">Kembali</a>
 </div>
 </form>
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