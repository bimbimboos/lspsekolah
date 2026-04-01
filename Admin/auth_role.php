<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

function hanya_admin() {
    if ($_SESSION['role'] !== 'admin') {
        die("Akses ditolak! Hanya admin.");
    }
}

function hanya_user() {
    if ($_SESSION['role'] !== 'user') {
        die("Akses ditolak! Hanya user.");
    }
}