<?php
include '../koneksi.php';

$id = $_GET['id'];

// Query Hapus
mysqli_query($koneksi, "DELETE FROM users WHERE id='$id'");

// Balik ke Frontend
header("location:../frontend/mahasiswa.php");
?>