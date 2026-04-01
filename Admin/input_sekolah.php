<?php
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";
/* ================= PROSES SIMPAN ================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 $nama_sekolah = $_POST['nama_sekolah'];
 $npsn = $_POST['npsn'];
 $alamat = $_POST['alamat'];
 $desa = $_POST['desa'];
 $kecamatan = $_POST['kecamatan'];
 $kabupaten = $_POST['kabupaten'];
 $provinsi = $_POST['provinsi'];
 $email = $_POST['email'];
 $telepon = $_POST['telepon'];
 $website = $_POST['website'];
 $kepala_sekolah = $_POST['kepala_sekolah'];
 $visi = $_POST['visi'];
 $misi = $_POST['misi'];
 $deskripsi = $_POST['deskripsi'];
 /* upload logo */
 $logo = '';
 if (!empty($_FILES['logo']['name'])) {
 $logo = time().'_'.$_FILES['logo']['name'];
 move_uploaded_file($_FILES['logo']['tmp_name'], 'upload/'.$logo);
 }
 mysqli_query($koneksi, "INSERT INTO profil_sekolah

(nama_sekolah,npsn,alamat,desa,kecamatan,kabupaten,provinsi,email,telepon,website,kepala_s
ekolah,logo,visi,misi,deskripsi)
 VALUES

('$nama_sekolah','$npsn','$alamat','$desa','$kecamatan','$kabupaten','$provinsi','$email','$telepo
n','$website','$kepala_sekolah','$logo','$visi','$misi','$deskripsi')
 ");
 echo "<script>alert('Profil sekolah berhasil
disimpan');window.location='data_sekolah.php';</script>";
}
?>
<main class="app-main">
 <div class="app-content-header">
 <div class="container-fluid">
 <div class="row">
 <div class="col-sm-6">
 <h3 class="mb-0">Input Profil Sekolah</h3>
 </div>
 <div class="col-sm-6">
 <ol class="breadcrumb float-sm-end">
 <li class="breadcrumb-item"><a href="#">Home</a></li>
 <li class="breadcrumb-item active">Profil Sekolah</li>
 </ol>
 </div>
 </div>
 </div>
 </div>
 <div class="app-content">
 <div class="container-fluid">
 <div class="card">
 <div class="card-header">
 <h3 class="card-title">Form Profil Sekolah</h3>
 </div>
 <div class="card-body">
 <form method="post" enctype="multipart/form-data">
 <div class="row">
 <!-- ===== KOLOM KIRI ===== -->
 <div class="col-md-6">
 <div class="mb-3">
 <label class="form-label">Nama Sekolah</label>
 <input type="text" name="nama_sekolah" class="form-control" required>
 </div>
 <div class="mb-3">
 <label class="form-label">NPSN</label>
 <input type="text" name="npsn" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Alamat</label>
 <textarea name="alamat" class="form-control" rows="3"></textarea>
 </div>
 <div class="mb-3">
 <label class="form-label">Desa</label>
 <input type="text" name="desa" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Kecamatan</label>
 <input type="text" name="kecamatan" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Kabupaten</label>
 <input type="text" name="kabupaten" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Provinsi</label>
 <input type="text" name="provinsi" class="form-control">
 </div>
 </div>
 <!-- ===== KOLOM KANAN ===== -->
 <div class="col-md-6">
 <div class="mb-3">
 <label class="form-label">Email</label>
 <input type="email" name="email" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Telepon</label>
 <input type="text" name="telepon" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Website</label>
 <input type="text" name="website" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Kepala Sekolah</label>
 <input type="text" name="kepala_sekolah" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Logo Sekolah</label>
 <input type="file" name="logo" class="form-control" accept="image/*">
 </div>
 </div>
 <!-- ===== VISI & MISI (2 KOLOM) ===== -->
<div class="col-md-6">
 <div class="mb-3">
 <label class="form-label">Visi</label>
 <textarea name="visi" id="visi" class="form-control" rows="6"></textarea>
 </div>
</div>
<div class="col-md-6">
 <div class="mb-3">
 <label class="form-label">Misi</label>
 <textarea name="misi" id="misi" class="form-control" rows="6"></textarea>
 </div>
</div>
<!-- ===== DESKRIPSI (FULL WIDTH) ===== -->
<div class="col-12">
 <div class="mb-3">
 <label class="form-label">Deskripsi Sekolah</label>
 <textarea name="deskripsi" id="deskripsi" class="form-control" rows="6"></textarea>
 </div>
</div>
 </div>
 <div class="card-footer text-end">
 <button type="submit" class="btn btn-primary">
 <i class="bi bi-save"></i> Simpan
 </button>
 <a href="profil_sekolah.php" class="btn btn-secondary">
 Kembali
 </a>
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
 CKEDITOR.replace('visi');
 CKEDITOR.replace('misi');
 CKEDITOR.replace('deskripsi');
</script>