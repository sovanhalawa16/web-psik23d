<?php
session_start();
include '../koneksi.php';

// Default response: Aman
$response = ['status' => 'aman'];

if(isset($_SESSION['id']) && isset($_SESSION['login_token'])) {
    $id_user = $_SESSION['id'];
    $sess_token = $_SESSION['login_token'];

    // Ambil token asli dari database
    $query = mysqli_query($koneksi, "SELECT remember_token FROM users WHERE id='$id_user'");
    $data = mysqli_fetch_assoc($query);

    // Bandingkan
    if($data) {
        if($data['remember_token'] != $sess_token) {
            $response = ['status' => 'kicked']; // Token beda = Ada device baru
        }
    } else {
        $response = ['status' => 'error']; // User tidak ditemukan
    }
} else {
    $response = ['status' => 'logout']; // Sesi kosong
}

// Kirim jawaban ke Javascript
header('Content-Type: application/json');
echo json_encode($response);
?>