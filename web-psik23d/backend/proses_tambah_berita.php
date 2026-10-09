<?php
session_start();
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Cek Login
if(!isset($_SESSION['id'])){ header("location:../index.php"); exit; }

$id_user = $_SESSION['id'];
$judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
$kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
$isi = mysqli_real_escape_string($koneksi, $_POST['isi']);
$tanggal = date('Y-m-d H:i:s');

// Default Gambar
$nama_gambar = "default.jpg";

// LOGIKA UPLOAD GAMBAR
if(isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != "") {
    $nama_file = $_FILES['gambar']['name'];
    $tmp_file = $_FILES['gambar']['tmp_name'];
    $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    
    // Validasi Format
    if(in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
        $nama_baru = time() . "_" . rand(100,999) . "." . $ekstensi;
        $tujuan = "../assets/img/berita/" . $nama_baru;
        
        // Buat folder jika belum ada
        if (!file_exists('../assets/img/berita')) { mkdir('../assets/img/berita', 0777, true); }

        if(move_uploaded_file($tmp_file, $tujuan)) {
            $nama_gambar = $nama_baru;
        }
    }
}

// INSERT KE DATABASE
$query = "INSERT INTO posts (id_user, judul, kategori, isi, gambar, tanggal) 
          VALUES ('$id_user', '$judul', '$kategori', '$isi', '$nama_gambar', '$tanggal')";

if(mysqli_query($koneksi, $query)){
    
    // --- [FIX REDIRECT DI SINI] ---
    if($_SESSION['role'] == 'admin'){
        // Kalau Admin, balik ke halaman Admin Berita
        header("location:../frontend/admin_berita.php?pesan=sukses");
    } else {
        // Kalau Mahasiswa, balik ke Dashboard Mahasiswa / Kelola Berita
        header("location:../frontend/kelola_berita.php?pesan=sukses");
    }

} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>