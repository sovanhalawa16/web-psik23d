<?php
include '../koneksi.php';

$nama_file = $_FILES['foto']['name'];
$tmp_file = $_FILES['foto']['tmp_name'];
$ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
$nama_baru = time() . "_kegiatan." . $ekstensi;

// Upload ke folder 'kegiatan'
if(move_uploaded_file($tmp_file, "../assets/img/kegiatan/" . $nama_baru)) {
    mysqli_query($koneksi, "INSERT INTO activities (foto) VALUES ('$nama_baru')");
    header("location:../frontend/kegiatan.php");
} else {
    echo "Gagal upload.";
}
?>