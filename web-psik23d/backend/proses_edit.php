<?php
include '../koneksi.php';

$id = $_POST['id'];
$nim = $_POST['nim'];
$nama = $_POST['nama'];

// Query Update
mysqli_query($koneksi, "UPDATE users SET nim='$nim', nama='$nama' WHERE id='$id'");

// Balik ke Frontend
header("location:../frontend/mahasiswa.php");
?>