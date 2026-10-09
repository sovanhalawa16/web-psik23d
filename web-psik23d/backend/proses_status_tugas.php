<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['id'])){ header("location:../frontend/login.php"); exit; }

$id_tugas = $_GET['id'];
$id_mhs = $_SESSION['id'];

// Cek apakah sudah ditandai?
$cek = mysqli_query($koneksi, "SELECT id FROM tugas_submission WHERE id_tugas='$id_tugas' AND id_mhs='$id_mhs'");

if(mysqli_num_rows($cek) > 0){
    // Kalau sudah ada, berarti mau batalin (DELETE)
    mysqli_query($koneksi, "DELETE FROM tugas_submission WHERE id_tugas='$id_tugas' AND id_mhs='$id_mhs'");
} else {
    // Kalau belum ada, berarti mau nandain selesai (INSERT)
    mysqli_query($koneksi, "INSERT INTO tugas_submission (id_tugas, id_mhs, status) VALUES ('$id_tugas', '$id_mhs', 'selesai')");
}

header("location:../frontend/tugas_saya.php");
?>