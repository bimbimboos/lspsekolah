<?php
include "../koneksi.php"; // pastikan sudah ada koneksi mysqli
$judul = $_POST['judul'];
$isi = $_POST['isi'];
$penulis = $_POST['penulis'];
$tanggal = date('Y-m-d');
// Folder upload
$folder = "upload/";
if (!is_dir($folder)) {
 mkdir($folder, 0777, true);
}
// Data gambar
$nama_gambar = $_FILES['gambar']['name'];
$tmp_gambar = $_FILES['gambar']['tmp_name'];
$ext = pathinfo($nama_gambar, PATHINFO_EXTENSION);
$nama_baru = time() . "_" . rand(100,999) . "." . $ext;
// Validasi ekstensi
$allowed = array('jpg','jpeg','png','gif');
if (!in_array(strtolower($ext), $allowed)) {
 echo "Format gambar tidak diizinkan!";
 exit;
}
// Upload file
if (move_uploaded_file($tmp_gambar, $folder . $nama_baru)) {
 $sql = "INSERT INTO berita (judul, isi, gambar, tanggal, penulis)
 VALUES ('$judul','$isi','$nama_baru','$tanggal','$penulis')";
 if (mysqli_query($koneksi, $sql)) {
 echo "<script>
 alert('Berita berhasil disimpan');
 window.location='input_berita.php';
 </script>";
 } else {
 echo "Gagal menyimpan data";
 }
} else {
 echo "Upload gambar gagal";
}
?>