<?php
include '../koneksi.php';
session_start();

if($_SESSION['status'] != "login"){
    header("location:../frontend/login.php?pesan=belum_login");
    exit;
}

// Ambil data pengumuman urut dari yang terbaru
$query = mysqli_query($koneksi, "SELECT * FROM announcements ORDER BY created_at DESC");
$semua_pengumuman = [];

while($row = mysqli_fetch_assoc($query)){
    $semua_pengumuman[] = $row;
}
?>