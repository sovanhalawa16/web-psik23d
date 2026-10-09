<?php
// Trik Path: Karena file ini dipanggil dari index.php (di root), 
// maka path ke koneksi.php cukup 'koneksi.php' (karena satu level dengan index)
include 'koneksi.php'; 

// 1. Ambil Pengumuman PUBLIK (Limit 3 Terbaru)
$q_info = "SELECT * FROM announcements 
           WHERE target_audience = 'public' 
           ORDER BY created_at DESC LIMIT 3";
$res_info = mysqli_query($koneksi, $q_info);

// 2. Ambil Data Dosen
$q_dosen = "SELECT * FROM dosen ORDER BY nama ASC";
$res_dosen = mysqli_query($koneksi, $q_dosen);

// 3. Ambil Data Mahasiswa (Untuk fitur Mari Berkenalan)
// Kita ambil yang role-nya mahasiswa saja
$q_mhs = "SELECT * FROM users WHERE role = 'mahasiswa' ORDER BY nim ASC";
$res_mhs = mysqli_query($koneksi, $q_mhs);

// Hitung Statistik (Opsional, buat keren-kerenan di Hero Section)
$total_mhs = mysqli_num_rows($res_mhs);
$total_dosen = mysqli_num_rows($res_dosen);

$q_galeri = mysqli_query($koneksi, "SELECT * FROM galleries ORDER BY id DESC");
$galeri_publik = [];
while($row = mysqli_fetch_assoc($q_galeri)){
    $galeri_publik[] = $row;
}
?>

