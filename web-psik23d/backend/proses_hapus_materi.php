<?php
session_start();
include '../koneksi.php';

// Cek Admin (Hanya admin yg boleh hapus materi master)
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$id = $_GET['id'];

// Ambil info file dulu buat dihapus fisiknya
$q = mysqli_query($koneksi, "SELECT file FROM materials WHERE id='$id'");
$data = mysqli_fetch_assoc($q);
$file_path = "../assets/files/" . $data['file'];

// Hapus File Fisik
if(file_exists($file_path)){
    unlink($file_path);
}

// Hapus Data Database
mysqli_query($koneksi, "DELETE FROM materials WHERE id='$id'");

header("location:../frontend/admin_materi.php");
?>