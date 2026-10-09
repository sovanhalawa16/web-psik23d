<?php
session_start();
include '../koneksi.php';

// Cek Login
if(!isset($_SESSION['id'])){ header("location:../frontend/login.php"); exit; }

$id_berita = $_GET['id'];
$gambar = $_GET['gambar'];
$id_user_login = $_SESSION['id'];
$role_login = $_SESSION['role'];

// Cek Kepemilikan (Security Check)
$cek = mysqli_query($koneksi, "SELECT id_user FROM posts WHERE id='$id_berita'");
$data = mysqli_fetch_assoc($cek);

// Jika yang hapus adalah PEMBUATNYA atau ADMIN, maka gas!
if($data['id_user'] == $id_user_login || $role_login == 'admin') {
    
    // 1. Hapus File Gambar Fisik
    $path = "../assets/img/berita/" . $gambar;
    if(file_exists($path) && $gambar != 'default_post.png'){
        unlink($path);
    }

    // 2. Hapus Data Database
    mysqli_query($koneksi, "DELETE FROM posts WHERE id='$id_berita'");
    
    // Redirect balik sesuai role/lokasi
    if($role_login == 'admin' && strpos($_SERVER['HTTP_REFERER'], 'admin') !== false) {
        header("location:../frontend/admin_berita.php"); // Nanti kalo mau buat admin panel berita
    } else {
        header("location:../frontend/kelola_berita.php");
    }

} else {
    echo "<script>alert('Anda tidak berhak menghapus postingan ini!'); window.location='../frontend/kelola_berita.php';</script>";
}
?>