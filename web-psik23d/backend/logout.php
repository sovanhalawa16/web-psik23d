<?php
session_start();
include '../koneksi.php';

// Ambil ID sebelum hancur
$uid = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// Cek apakah Logout Manual atau Timeout
$is_timeout = (isset($_GET['reason']) && $_GET['reason'] == 'timeout');

if (!$is_timeout) {
    // === LOGOUT MANUAL (TOMBOL) ===
    // Hapus Cookie
    setcookie('id_user', '', time() - 3600, "/");
    setcookie('token', '', time() - 3600, "/");
    
    // Hapus Token di DB agar tidak ada yang bisa pakai token lama ini
    if($uid) {
        mysqli_query($koneksi, "UPDATE users SET remember_token = NULL, last_active = NULL WHERE id='$uid'");
    }
} else {
    // === TIMEOUT ===
    // Jangan hapus cookie, biar bisa auto login lagi kalau punya cookie valid
    // Hanya update last_active jadi NULL (Offline)
    if($uid) {
        mysqli_query($koneksi, "UPDATE users SET last_active = NULL WHERE id='$uid'");
    }
}

// Hapus Session
session_unset();
session_destroy();

header("location:../");
exit;
?>