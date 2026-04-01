<?php
session_start();
include '../koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = mysqli_real_escape_string($koneksi, $_POST['password']);

// 🔥 ambil data user
$data = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($data);

if ($user) {

    // ⚠️ kalau masih pakai plaintext (seperti kode kamu sekarang)
    if ($password == $user['password']) {

        // 🔥 SIMPAN SESSION (WAJIB)
        $_SESSION['id_admin'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role']; // 🔥 INI KUNCI ROLE
        $_SESSION['status']   = "login";

        // 🔥 redirect sesuai role
        if ($user['role'] == 'admin') {
            header("Location: hal_admin.php");
        } else {
            header("Location: index.php"); // atau halaman user
        }
        exit;

    } else {
        header("Location: index.php?pesan=password_salah");
        exit;
    }

} else {
    header("Location: index.php?pesan=username_tidak_ada");
    exit;
}