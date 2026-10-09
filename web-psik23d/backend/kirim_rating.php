<?php
// 1. SET ZONA WAKTU INDONESIA BARAT (WIB)
date_default_timezone_set('Asia/Jakarta');

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($koneksi, htmlspecialchars($_POST['nama']));
    $bintang = (int)$_POST['bintang'];
    $komentar = mysqli_real_escape_string($koneksi, htmlspecialchars($_POST['komentar']));
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // 2. AMBIL WAKTU SEKARANG (WIB)
    $waktu_sekarang = date('Y-m-d H:i:s');

    if(empty($nama) || $bintang < 1) {
        echo json_encode(['status' => 'error', 'message' => 'Nama dan Bintang wajib diisi!']);
        exit;
    }

    // 3. SIMPAN KE DATABASE (Sertakan kolom created_at manual biar akurat)
    // Pastikan tabel database 'rating' punya kolom 'created_at' (Timestamp/Datetime)
    $query = "INSERT INTO rating (nama, bintang, komentar, ip_address, created_at) 
              VALUES ('$nama', '$bintang', '$komentar', '$ip', '$waktu_sekarang')";
    
    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Terima kasih! Penilaian Anda berhasil dikirim.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal: ' . mysqli_error($koneksi)]);
    }
}
?>