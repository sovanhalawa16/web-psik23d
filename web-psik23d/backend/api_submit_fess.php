<?php
header('Content-Type: application/json');
include '../koneksi.php';

// Cek method post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil & Bersihkan Input (PENTING BIAR GAK DI-HACK)
    $sender = isset($_POST['sender']) ? trim($_POST['sender']) : 'Anonim';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : 'bg-warning';

    // Validasi sederhana
    if (empty($sender)) $sender = 'Anonim';
    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong!']);
        exit;
    }
    if (strlen($message) > 300) {
        echo json_encode(['status' => 'error', 'message' => 'Pesan kepanjangan! Maks 300 karakter.']);
        exit;
    }

    // Escape string untuk database
    $sender = mysqli_real_escape_string($koneksi, htmlspecialchars($sender));
    $message = mysqli_real_escape_string($koneksi, htmlspecialchars($message));
    $color = mysqli_real_escape_string($koneksi, $color);

    // Simpan
    $query = "INSERT INTO confessions (sender, message, color) VALUES ('$sender', '$message', '$color')";
    
    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pesan.']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
}
?>