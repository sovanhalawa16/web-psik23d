<?php
include '../koneksi.php';
session_start();

// Cek Admin
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

// 1. Ambil ID Admin
$nim_admin = $_SESSION['nim'];
$cari_admin = mysqli_query($koneksi, "SELECT id FROM users WHERE nim='$nim_admin'");
$data_admin = mysqli_fetch_assoc($cari_admin);
$id_admin = $data_admin['id'];

// 2. Tangkap Data
$title = mysqli_real_escape_string($koneksi, $_POST['title']);
$content = mysqli_real_escape_string($koneksi, $_POST['content']);
$type = $_POST['type']; // info, urgent, event
$target = $_POST['target_audience']; // all, L, P, public

// 3. Masukkan ke Database (Tabel announcements)
$query = "INSERT INTO announcements (title, content, type, target_audience, created_by, created_at) 
          VALUES ('$title', '$content', '$type', '$target', '$id_admin', NOW())";

if(mysqli_query($koneksi, $query)) {
    // Sukses -> Balik ke halaman Kelola Pengumuman
    header("location:../frontend/pengumuman.php?pesan=sukses");
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>