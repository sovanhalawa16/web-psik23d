<?php
session_start();
include '../koneksi.php';

// Cek Login
if(!isset($_SESSION['id'])){ header("location:../frontend/login.php"); exit; }

$judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
$matkul = mysqli_real_escape_string($koneksi, $_POST['mata_kuliah']);
$semester = $_POST['semester'];
$deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
$uploader = $_SESSION['id'];

// LOGIKA UPLOAD FILE
$nama_file_db = "";
if(isset($_FILES['file_materi']['name']) && $_FILES['file_materi']['name'] != "") {
    $nama_asal = $_FILES['file_materi']['name'];
    $tmp_file = $_FILES['file_materi']['tmp_name'];
    $ekstensi = strtolower(pathinfo($nama_asal, PATHINFO_EXTENSION));
    
    // Validasi Format (Bisa PDF, PPT, DOC, ZIP)
    $allowed = ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
    
    if(in_array($ekstensi, $allowed)) {
        // Nama file unik biar gak bentrok
        $nama_baru = time() . "_" . str_replace(" ", "_", $judul) . "." . $ekstensi;
        $tujuan = "../assets/files/" . $nama_baru;
        
        // Buat folder jika belum ada
        if (!file_exists('../assets/files')) {
            mkdir('../assets/files', 0777, true);
        }

        if(move_uploaded_file($tmp_file, $tujuan)) {
            $nama_file_db = $nama_baru;
        } else {
            echo "Gagal upload file."; exit;
        }
    } else {
        echo "Format file tidak diizinkan!"; exit;
    }
}

// INSERT DATABASE
$query = "INSERT INTO materials (judul, deskripsi, mata_kuliah, semester, file, uploaded_by) 
          VALUES ('$judul', '$deskripsi', '$matkul', '$semester', '$nama_file_db', '$uploader')";

if(mysqli_query($koneksi, $query)){
    header("location:../frontend/admin_materi.php?pesan=sukses");
} else {
    echo "Gagal database: " . mysqli_error($koneksi);
}
?>