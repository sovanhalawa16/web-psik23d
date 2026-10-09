<?php
include '../koneksi.php';

$id = $_GET['id'];

// 1. Ambil nama foto ruangan dulu
$query_cari = mysqli_query($koneksi, "SELECT foto_ruangan FROM jadwal WHERE id='$id'");
$data = mysqli_fetch_assoc($query_cari);
$foto_ruangan = $data['foto_ruangan'];

// 2. Hapus File Foto Ruangan (Jika bukan default)
if($foto_ruangan != "default_room.jpg" && !empty($foto_ruangan)){
    $path_file = "../assets/img/ruangan/" . $foto_ruangan;
    if(file_exists($path_file)){
        unlink($path_file);
    }
}

// 3. Hapus Data Jadwal
$hapus = mysqli_query($koneksi, "DELETE FROM jadwal WHERE id='$id'");

if($hapus){
    header("location:../frontend/jadwal.php");
} else {
    echo "Gagal menghapus: " . mysqli_error($koneksi);
}
?>