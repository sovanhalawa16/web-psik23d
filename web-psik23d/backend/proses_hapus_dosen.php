<?php
include '../koneksi.php';

$id = $_GET['id'];

// 1. Ambil nama foto dulu sebelum datanya dihapus
$query_cari = mysqli_query($koneksi, "SELECT foto FROM dosen WHERE id='$id'");
$data = mysqli_fetch_assoc($query_cari);
$foto_lama = $data['foto'];

// 2. Hapus File Foto Dosen di Folder (Jika bukan default)
if($foto_lama != "default_dosen.png"){
    $path_file = "../assets/img/dosen/" . $foto_lama;
    if(file_exists($path_file)){
        unlink($path_file); // Hapus file fisik
    }
}

// 3. PENTING: Hapus dulu jadwal yang berhubungan dengan dosen ini
// (Supaya tidak kena error Foreign Key Constraint)
mysqli_query($koneksi, "DELETE FROM jadwal WHERE dosen_id='$id'");

// 4. Baru hapus Dosennya
$hapus = mysqli_query($koneksi, "DELETE FROM dosen WHERE id='$id'");

if($hapus){
    header("location:../frontend/dosen.php");
} else {
    echo "Gagal menghapus: " . mysqli_error($koneksi);
}
?>