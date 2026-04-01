<?php
include '../koneksi.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$pesan = $_POST['pesan'];
$tanggal_kirim = $_POST['tanggal_kirim'];

mysqli_query($koneksi,
  "INSERT INTO kontak (nama, email, pesan, tanggal_kirim)
   VALUES ('$nama', '$email', '$pesan', '$tanggal_kirim')"
);

header("location:data_kontak.php");