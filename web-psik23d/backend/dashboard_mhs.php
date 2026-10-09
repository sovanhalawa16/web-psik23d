<?php
session_start();
include '../koneksi.php';

// Cek apakah sudah login?
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:../frontend/login.php?pesan=belum_login");
    exit;
}

// Cek apakah ID ada? (Penangkal Error yang kamu alami)
if(!isset($_SESSION['id'])){
    // Kalau ID hilang, suruh login ulang biar session ke-set lagi
    header("location:../frontend/login.php?pesan=sesi_habis");
    exit;
}

// Ambil Data Jadwal (Ini kode lama kamu, tetap dipake)
$nim_saya = $_SESSION['nim'];
// ... (logika query jadwal dll biarkan saja) ...
// Kita cuma butuh perbaiki bagian paling atas tadi.

// CONTOH LANJUTAN KODE YANG BIASANYA ADA DI SINI (JANGAN DIHAPUS):
$query_jadwal = "SELECT jadwal.*, dosen.nama as nama_dosen 
                 FROM jadwal 
                 JOIN dosen ON jadwal.dosen_id = dosen.id 
                 ORDER BY jadwal.hari DESC, jadwal.jam_mulai ASC";
$result_jadwal = mysqli_query($koneksi, $query_jadwal);

$query_info = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$result_info = mysqli_query($koneksi, $query_info);
?>