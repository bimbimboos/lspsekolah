<?php
session_start();

// hapus semua session
session_unset();
session_destroy();

// balik ke halaman login
header("Location: index.php");
exit;
