<?php
session_start();
include '../koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = mysqli_real_escape_string($koneksi, $_POST['password']);

// Ambil data user dari tabel users
$data = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($data);

if ($user) {
    // Cek password (plaintext — sesuai sistem yang sudah ada)
    if ($password == $user['password']) {

        // Simpan session
        $_SESSION['id_user']  = $user['id'];
        $_SESSION['id_admin'] = $user['id']; // untuk kompatibilitas hal_profil.php
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['status']   = 'login';

        // Redirect sesuai role
        if ($user['role'] == 'admin') {
            header("Location: hal_admin.php");
        } else {
            // Semua role selain admin → halaman user
            header("Location: hal_user.php");
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