<?php
include "template/header.php";
include "template/menu.php";
?>
<main class="app-main">
 <!-- App Content Header -->
 <div class="app-content-header">
 <div class="container-fluid">
 <div class="row">
 <div class="col-sm-6">
 <h3 class="mb-0">Input Berita</h3>
 </div>
 <div class="col-sm-6">
 <ol class="breadcrumb float-sm-end">
 <li class="breadcrumb-item"><a href="#">Home</a></li>
 <li class="breadcrumb-item active">Input Berita</li>
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
 <h3 class="card-title">Form Berita</h3>
 </div>
 <div class="card-body">
 <form action="proses_berita.php" method="post" enctype="multipart/form-data">
 <!-- Judul -->
 <div class="mb-3">
 <label class="form-label">Judul Berita</label>
 <input type="text" name="judul" class="form-control" required>
 </div>
 <!-- Isi -->
 <div class="mb-3">
 <label class="form-label">Isi Berita</label>
 <textarea name="isi" id="editor" rows="10" class="form-control" required></textarea>
 </div>
 <!-- Gambar -->
 <div class="mb-3">
 <label class="form-label">Gambar</label>
 <input type="file" name="gambar" class="form-control" accept="image/*">
 </div>
 <!-- Tanggal -->
 <div class="mb-3">
 <label class="form-label">Tanggal</label>
 <input type="date" name="tanggal" class="form-control" required>
 </div>
 <!-- Penulis -->
 <div class="mb-3">
 <label class="form-label">Penulis</label>
 <input type="text" name="penulis" class="form-control" required>
 </div>
 <div class="card-footer">
 <button type="submit" class="btn btn-primary">Simpan</button>
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
<!-- CKEditor 4 (WordPress klasik style) -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
 CKEDITOR.replace('editor');
</script>
