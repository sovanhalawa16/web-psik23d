<?php
session_start();
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];

    // 1. Ambil nama file media dulu sebelum datanya dihapus
    $cek = mysqli_query($koneksi, "SELECT file_media FROM tugas_perkuliahan WHERE id = '$id'");
    $data = mysqli_fetch_assoc($cek);

    if ($data) {
        // 2. Hapus file fisik di folder assets
        if (!empty($data['file_media'])) {
            $files = explode(',', $data['file_media']);
            foreach ($files as $file) {
                $path = '../assets/img/tugas/' . trim($file);
                if (file_exists($path)) {
                    unlink($path); // Menghapus file dari server
                }
            }
        }

        // 3. Hapus data di tabel checklist (foreign key cleanup manual)
        mysqli_query($koneksi, "DELETE FROM checklist_tugas WHERE tugas_id = '$id'");

        // 4. Hapus data utama di tabel tugas
        $hapus = mysqli_query($koneksi, "DELETE FROM tugas_perkuliahan WHERE id = '$id'");

        if ($hapus) {
            echo json_encode(['status' => 'success', 'msg' => 'Tugas berhasil dibersihkan!']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal menghapus data di database.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Data tidak ditemukan!']);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Akses ditolak.']);
}