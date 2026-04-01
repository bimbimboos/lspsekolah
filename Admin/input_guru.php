<?php
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";
/* ================= PROSES SIMPAN ================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 $nama_guru = $_POST['nama_guru'];
 $nip = $_POST['nip'];
 $jenis_kelamin = $_POST['jenis_kelamin'];
 $mapel = $_POST['mapel'];
 $email = $_POST['email'];
 $no_hp = $_POST['no_hp'];
 // upload foto
 $foto = '';
 if (!empty($_FILES['foto']['name'])) {
 $foto = time().'_'.$_FILES['foto']['name'];
 move_uploaded_file($_FILES['foto']['tmp_name'], 'upload/'.$foto);
 }
 mysqli_query($koneksi, "INSERT INTO guru
 (nama_guru, nip, jenis_kelamin, mapel, foto, email, no_hp)
 VALUES
 ('$nama_guru','$nip','$jenis_kelamin','$mapel','$foto','$email','$no_hp')
 ");
 echo "<script>alert('Data guru berhasil
disimpan');window.location='data_guru.php';</script>";
}
?>
<main class="app-main">
 <div class="app-content-header">
 <div class="container-fluid">
 <div class="row">
 <div class="col-sm-6">
 <h3 class="mb-0">Input Guru</h3>
 </div>
 <div class="col-sm-6">
 <ol class="breadcrumb float-sm-end">
 <li class="breadcrumb-item"><a href="#">Home</a></li>
 <li class="breadcrumb-item active">Input Guru</li>
 </ol>
 </div>
 </div>
 </div>
 </div>
 <div class="app-content">
 <div class="container-fluid">
 <div class="card">
 <div class="card-header">
 <h3 class="card-title">Form Input Guru</h3>
 </div>
 <div class="card-body">
 <form method="post" enctype="multipart/form-data">
 <div class="row">
 <!-- Kolom Kiri -->
 <div class="col-md-6">
 <div class="mb-3">
 <label class="form-label">Nama Guru</label>
 <input type="text" name="nama_guru" class="form-control" required>
 </div>
 <div class="mb-3">
 <label class="form-label">NIP</label>
 <input type="text" name="nip" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Jenis Kelamin</label>
 <select name="jenis_kelamin" class="form-control" required>
 <option value="">-- Pilih --</option>
 <option value="Laki-laki">Laki-laki</option>
 <option value="Perempuan">Perempuan</option>
 </select>
 </div>
 <div class="mb-3">
 <label class="form-label">Mata Pelajaran</label>
 <input type="text" name="mapel" class="form-control" required>
 </div>
 </div>
 <!-- Kolom Kanan -->
 <div class="col-md-6">
 <div class="mb-3">
 <label class="form-label">Email</label>
 <input type="email" name="email" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">No HP</label>
 <input type="text" name="no_hp" class="form-control">
 </div>
 <div class="mb-3">
 <label class="form-label">Foto Guru</label>
 <input type="file" name="foto" class="form-control" accept="image/*">
 </div>
 </div>
 </div>
 <div class="card-footer text-end">
 <button type="submit" class="btn btn-primary">
 <i class="bi bi-save"></i> Simpan
 </button>
 <a href="data_guru.php" class="btn btn-secondary">
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