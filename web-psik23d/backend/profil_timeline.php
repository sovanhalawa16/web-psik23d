<?php
session_start();
include '../koneksi.php';

// Cek Login
if(!isset($_SESSION['id'])) { header("location:../index.php"); exit; }

$user_id = $_SESSION['id'];
$act = isset($_GET['act']) ? $_GET['act'] : '';

// --- 1. TAMBAH TIMELINE (ADD) ---
if($act == 'add') {
    $kategori = $_POST['kategori']; // pendidikan, organisasi, pekerjaan
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']); // Nama Sekolah/Instansi
    $sub_judul = mysqli_real_escape_string($koneksi, $_POST['sub_judul']); // Jurusan/Jabatan
    $tahun_mulai = $_POST['tahun_mulai'];
    $tahun_selesai = $_POST['tahun_selesai'];
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    $q = "INSERT INTO user_timeline (user_id, kategori, judul, sub_judul, tahun_mulai, tahun_selesai, deskripsi) 
          VALUES ('$user_id', '$kategori', '$judul', '$sub_judul', '$tahun_mulai', '$tahun_selesai', '$deskripsi')";
    
    if(mysqli_query($koneksi, $q)){
        header("location:../frontend/user_profil.php?pesan=sukses_add#tab-timeline");
    } else {
        echo "Gagal menambah data: " . mysqli_error($koneksi);
    }

// --- 2. EDIT TIMELINE (UPDATE) ---
} elseif($act == 'edit') {
    $id = $_POST['id'];
    $kategori = $_POST['kategori'];
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $sub_judul = mysqli_real_escape_string($koneksi, $_POST['sub_judul']);
    $tahun_mulai = $_POST['tahun_mulai'];
    $tahun_selesai = $_POST['tahun_selesai'];
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    $q = "UPDATE user_timeline SET 
            kategori='$kategori', 
            judul='$judul', 
            sub_judul='$sub_judul', 
            tahun_mulai='$tahun_mulai', 
            tahun_selesai='$tahun_selesai', 
            deskripsi='$deskripsi' 
          WHERE id='$id' AND user_id='$user_id'";
    
    if(mysqli_query($koneksi, $q)){
        header("location:../frontend/user_profil.php?pesan=sukses_edit#tab-timeline");
    } else {
        echo "Gagal update data: " . mysqli_error($koneksi);
    }

// --- 3. HAPUS TIMELINE (DELETE) ---
} elseif($act == 'delete') {
    $id = $_GET['id'];
    
    $q = "DELETE FROM user_timeline WHERE id='$id' AND user_id='$user_id'";
    
    if(mysqli_query($koneksi, $q)){
        header("location:../frontend/user_profil.php?pesan=sukses_hapus#tab-timeline");
    } else {
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }

} else {
    // Jika akses langsung tanpa parameter ?act=...
    header("location:../frontend/user_profil.php");
}
?>