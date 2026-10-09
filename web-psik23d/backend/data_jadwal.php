<?php
include '../koneksi.php';

// Cek Session (Gunakan session_status agar aman jika di-include berkali-kali)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:../frontend/login.php?pesan=belum_login");
    exit;
}

// 1. QUERY UTAMA (JOIN TABLE)
// Mengambil semua data jadwal + data dosen + foto dosen
// jadwal.* otomatis mengambil kolom baru (jenis_kelas, link_online, pertemuan_ke)
$query = "SELECT jadwal.*, dosen.nama AS nama_dosen, dosen.foto AS foto_dosen 
          FROM jadwal 
          LEFT JOIN dosen ON jadwal.dosen_id = dosen.id 
          ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam_mulai ASC";

$result = mysqli_query($koneksi, $query);

// 2. SIAPKAN ARRAY PENAMPUNG
$semua_jadwal = []; // Untuk Admin (Menampilkan semua)
$data_blok1   = []; // Untuk Mahasiswa Tab 1
$data_blok2   = []; // Untuk Mahasiswa Tab 2
$data_blok3   = []; // Jaga-jaga jika ada Blok 3

// 3. LOOPING DATA & PEMISAHAN KATEGORI
while($row = mysqli_fetch_assoc($result)){
    
    // Masukkan ke master array (untuk admin)
    $semua_jadwal[] = $row;

    // Pisahkan berdasarkan BLOK (untuk mahasiswa)
    // Pastikan stringnya sama persis dengan yang diinput di database ('Blok 1', 'Blok 2')
    if ($row['blok'] == 'Blok 1') {
        $data_blok1[] = $row;
    } 
    elseif ($row['blok'] == 'Blok 2') {
        $data_blok2[] = $row;
    }
    elseif ($row['blok'] == 'Blok 3') {
        $data_blok3[] = $row;
    }
}
?>