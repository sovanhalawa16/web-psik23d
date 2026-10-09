<?php
// 1. SET ZONA WAKTU (Wajib Paling Atas)
date_default_timezone_set('Asia/Jakarta');

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Tangkap Input
    $nama = mysqli_real_escape_string($koneksi, htmlspecialchars($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, htmlspecialchars($_POST['email']));
    $jenis = mysqli_real_escape_string($koneksi, htmlspecialchars($_POST['jenis']));
    $deskripsi = mysqli_real_escape_string($koneksi, htmlspecialchars($_POST['deskripsi']));
    
    // 3. Generate Data Otomatis
    $waktu_sekarang = date('Y-m-d H:i:s');
    
    // Bikin ID Unik (Format: LAP-6KarakterAcak) -> Contoh: LAP-X7B9A2
    $random_str = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    $ticket_id = "LAP-" . $random_str;

    // 4. Validasi
    if(empty($nama) || empty($email) || empty($deskripsi)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi!']);
        exit;
    }

    // 5. Simpan ke Database (Include ticket_id)
    $query = "INSERT INTO laporan (ticket_id, nama, email, jenis_laporan, deskripsi, created_at) 
              VALUES ('$ticket_id', '$nama', '$email', '$jenis', '$deskripsi', '$waktu_sekarang')";
    
    if (mysqli_query($koneksi, $query)) {
        echo json_encode([
            'status' => 'success', 
            'message' => "Laporan Terkirim! \nSimpan ID Tiket Anda: $ticket_id"
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal: ' . mysqli_error($koneksi)]);
    }
}
?>