<?php
include '../koneksi.php';

// 1. Tangkap Data
$judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
$isi = mysqli_real_escape_string($koneksi, $_POST['isi']);
$status = $_POST['status'];
$jenis = $_POST['jenis']; // info, peringatan, event, penting
$penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);

// 2. Tangkap Link
$link_url = mysqli_real_escape_string($koneksi, $_POST['link_url']);
$link_label = mysqli_real_escape_string($koneksi, $_POST['link_label']);

if(!empty($link_url) && empty($link_label)) { $link_label = "Klik Disini"; }

// 3. Reset Tabel (Agar cuma ada 1 data)
mysqli_query($koneksi, "TRUNCATE TABLE popup_info"); 

// 4. Insert Data Lengkap
$query = "INSERT INTO popup_info (judul, isi, status, link_url, link_label, penulis, jenis) 
          VALUES ('$judul', '$isi', '$status', '$link_url', '$link_label', '$penulis', '$jenis')";

if(mysqli_query($koneksi, $query)){
    header("location:../frontend/admin_popup.php?pesan=sukses");
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>