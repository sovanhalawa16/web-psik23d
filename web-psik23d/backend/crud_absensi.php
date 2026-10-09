<?php
session_start();
include '../koneksi.php';

header('Content-Type: application/json');

// Pastikan request lewat POST dan ada action-nya
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // ===================================
    // JIKA PERINTAHNYA ADALAH 'HAPUS'
    // ===================================
    if ($action == 'hapus') {
        $query = mysqli_query($koneksi, "DELETE FROM absensi_real WHERE id = '$id'");
        
        if ($query) {
            echo json_encode(['status' => 'sukses', 'pesan' => 'Data dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'pesan' => 'Gagal hapus: ' . mysqli_error($koneksi)]);
        }
        exit;
    }

    // ===================================
    // JIKA PERINTAHNYA ADALAH 'EDIT'
    // ===================================
    if ($action == 'edit') {
        $status_absensi = mysqli_real_escape_string($koneksi, $_POST['status_absensi']);
        
        $query = mysqli_query($koneksi, "UPDATE absensi_real SET status_absensi = '$status_absensi' WHERE id = '$id'");
        
        if ($query) {
            echo json_encode(['status' => 'sukses', 'pesan' => 'Data diupdate']);
        } else {
            echo json_encode(['status' => 'error', 'pesan' => 'Gagal update: ' . mysqli_error($koneksi)]);
        }
        exit;
    }

} else {
    // Kalo ada yang iseng akses file ini pake URL
    echo json_encode(['status' => 'error', 'pesan' => 'Akses Ilegal!']);
}
?>