<?php
include '../koneksi.php';

// Cek Sesi (Biar gak bisa ditembak langsung tanpa login)
session_start();
if($_SESSION['status'] != "login"){
    header("location:../frontend/login.php?pesan=belum_login");
    exit;
}

// Ambil semua data dosen
$query = mysqli_query($koneksi, "SELECT * FROM dosen");
$semua_dosen = [];

while($row = mysqli_fetch_assoc($query)){
    $semua_dosen[] = $row;
}
?>