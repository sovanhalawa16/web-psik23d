<?php
include '../koneksi.php';

// Ambil semua foto galeri urut dari yang terbaru
$query = mysqli_query($koneksi, "SELECT * FROM galleries ORDER BY id DESC");
$semua_galeri = [];

while($row = mysqli_fetch_assoc($query)){
    $semua_galeri[] = $row;
}
?>