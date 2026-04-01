<?php
include "../koneksi.php";
$id = $_POST['id'];
$judul = $_POST['judul'];
$isi = $_POST['isi'];
$tanggal = $_POST['tanggal'];
$penulis = $_POST['penulis'];
$gambar_lama = mysqli_fetch_assoc(
 mysqli_query($koneksi, "SELECT gambar FROM berita WHERE id='$id'")
)['gambar'];
// Jika upload gambar baru
if (!empty($_FILES['gambar']['name'])) {
 $gambar = time() . "_" . $_FILES['gambar']['name'];
 move_uploaded_file($_FILES['gambar']['tmp_name'], "upload/" . $gambar);
 // Update dengan gambar
 mysqli_query($koneksi, "UPDATE berita SET
 judul='$judul',
 isi='$isi',
 gambar='$gambar',
 tanggal='$tanggal',
 penulis='$penulis'
 WHERE id='$id'");
} else {
 // Update tanpa ganti gambar
 mysqli_query($koneksi, "UPDATE berita SET
 judul='$judul',
 isi='$isi',
 tanggal='$tanggal',
 penulis='$penulis'
 WHERE id='$id'");
}
echo "<script>alert('Data berhasil diupdate');window.location='data_berita.php';</script>";