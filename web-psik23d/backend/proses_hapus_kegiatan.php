<?php
include '../koneksi.php';
$id = $_GET['id'];
$foto = $_GET['foto'];

// Hapus file fisik
$path = "../assets/img/kegiatan/" . $foto;
if(file_exists($path)) unlink($path);

// Hapus database
mysqli_query($koneksi, "DELETE FROM activities WHERE id='$id'");
header("location:../frontend/kegiatan.php");
?>