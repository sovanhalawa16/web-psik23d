<?php
include '../koneksi.php';

$id = $_GET['id'];
mysqli_query($koneksi, "DELETE FROM announcements WHERE id='$id'");

header("location:../frontend/pengumuman.php");
?>