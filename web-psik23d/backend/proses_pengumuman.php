<?php
include '../koneksi.php';
include 'cek_session.php'; // Biar aman cuma admin yang bisa akses

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- LOGIKA TAMBAH FOTO ---
if ($action == 'tambah') {
    if(isset($_FILES['foto']['name'][0]) && $_FILES['foto']['name'][0] != "") {
        $jumlah_file = count($_FILES['foto']['name']);
        
        for($i = 0; $i < $jumlah_file; $i++) {
            $nama_file = $_FILES['foto']['name'][$i];
            $tmp_file = $_FILES['foto']['tmp_name'][$i];
            
            // Bersihin nama file dan bikin unik
            $nama_file_bersih = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $nama_file);
            $nama_baru = time() . "_" . $i . "_banner_" . $nama_file_bersih;
            
            if(move_uploaded_file($tmp_file, "../assets/img/pengumuman/" . $nama_baru)) {
                mysqli_query($koneksi, "INSERT INTO pengumuman (foto) VALUES ('$nama_baru')");
            }
        }
    }
    header("location:../frontend/admin_pengumuman.php");
}

// --- LOGIKA HAPUS FOTO ---
elseif ($action == 'hapus') {
    $id = (int)$_GET['id'];
    
    // Cari nama file fotonya dulu buat dihapus dari folder
    $q = mysqli_query($koneksi, "SELECT foto FROM pengumuman WHERE id = '$id'");
    if ($row = mysqli_fetch_assoc($q)) {
        $path = "../assets/img/pengumuman/" . $row['foto'];
        if (file_exists($path)) {
            unlink($path); // Hapus file fisiknya
        }
        // Hapus dari database
        mysqli_query($koneksi, "DELETE FROM pengumuman WHERE id = '$id'");
    }
    header("location:../frontend/admin_pengumuman.php");
}
?>