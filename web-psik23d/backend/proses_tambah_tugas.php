<?php
session_start();
include '../koneksi.php';

if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
$matkul = mysqli_real_escape_string($koneksi, $_POST['mata_kuliah']);
$deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
$deadline = $_POST['deadline'];
$link = mysqli_real_escape_string($koneksi, $_POST['link']);

$query = "INSERT INTO tugas (judul, mata_kuliah, deskripsi, deadline, link_pengumpulan) 
          VALUES ('$judul', '$matkul', '$deskripsi', '$deadline', '$link')";

if(mysqli_query($koneksi, $query)){
    header("location:../frontend/admin_tugas.php?pesan=sukses");
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>