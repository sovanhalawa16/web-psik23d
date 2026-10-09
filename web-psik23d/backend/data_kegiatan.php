<?php
include '../koneksi.php';
$q = mysqli_query($koneksi, "SELECT * FROM activities ORDER BY id DESC");
$semua_kegiatan = [];
while($row = mysqli_fetch_assoc($q)){
    $semua_kegiatan[] = $row;
}
?>