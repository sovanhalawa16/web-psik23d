<?php
// Panggil koneksi (mundur 1 folder karena ada di dalam folder backend)
include '../koneksi.php';

// Logic 1: Cek Login
session_start();
if($_SESSION['status'] != "login"){
    header("location:../frontend/login.php?pesan=belum_login");
    exit;
}

// Logic 2: Ambil Data dari Database
// Kita simpan hasilnya dalam variabel $semua_mahasiswa
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE role='mahasiswa'");
$semua_mahasiswa = [];

while($row = mysqli_fetch_assoc($query)){
    $semua_mahasiswa[] = $row;
}

// Selesai. Backend cuma nyiapin data $semua_mahasiswa.
?>