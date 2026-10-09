<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['id'])) { header("location:../index.php"); exit; }
$user_id = $_SESSION['id'];

// Tangkap Input
$link_profil = mysqli_real_escape_string($koneksi, $_POST['link_profil']);
$jumlah_sitasi = $_POST['jumlah_sitasi'];
$h_index = $_POST['h_index'];
$i10_index = $_POST['i10_index'];

// Cek apakah user ini sudah punya data scholar sebelumnya?
$cek = mysqli_query($koneksi, "SELECT id FROM user_scholar WHERE user_id='$user_id'");

if(mysqli_num_rows($cek) > 0) {
    // UPDATE
    $query = "UPDATE user_scholar SET 
                link_profil='$link_profil', 
                jumlah_sitasi='$jumlah_sitasi', 
                h_index='$h_index', 
                i10_index='$i10_index' 
              WHERE user_id='$user_id'";
} else {
    // INSERT BARU
    $query = "INSERT INTO user_scholar (user_id, link_profil, jumlah_sitasi, h_index, i10_index) 
              VALUES ('$user_id', '$link_profil', '$jumlah_sitasi', '$h_index', '$i10_index')";
}

if(mysqli_query($koneksi, $query)){
    header("location:../frontend/user_profil.php?pesan=scholar_sukses#tab-scholar");
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>