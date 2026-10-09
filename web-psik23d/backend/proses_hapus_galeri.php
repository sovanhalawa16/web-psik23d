<?php
include '../koneksi.php';

$id = $_GET['id'];
$foto = $_GET['foto'];

// 1. Hapus File Fisik di Folder
$path = "../assets/img/galeri/" . $foto;
if(file_exists($path)){
    unlink($path); 
}

// 2. Hapus Data di Database
mysqli_query($koneksi, "DELETE FROM galleries WHERE id='$id'");

header("location:../frontend/galeri.php");
?>