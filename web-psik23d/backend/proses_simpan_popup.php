<?php
session_start();
// Pastikan path koneksi sesuaikan dengan folder lu
include '../koneksi.php';

// SET ZONA WAKTU JAKARTA
date_default_timezone_set('Asia/Jakarta');
$waktu_sekarang = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Tangkap semua data Teks & Status
    $id_popup      = isset($_POST['id_popup']) ? mysqli_real_escape_string($koneksi, $_POST['id_popup']) : '';
    $subject       = mysqli_real_escape_string($koneksi, trim($_POST['subject']));
    $deskripsi     = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi_html']));
    $status        = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    $status_video  = isset($_POST['status_video']) ? mysqli_real_escape_string($koneksi, $_POST['status_video']) : 'nonaktif'; 
    $status_gambar = isset($_POST['status_gambar']) ? mysqli_real_escape_string($koneksi, $_POST['status_gambar']) : 'nonaktif'; 
    
    $penulis       = mysqli_real_escape_string($koneksi, trim($_POST['penulis']));

    // 2. Validasi Sederhana
    if (empty($subject) || empty($penulis)) {
        echo json_encode([
            'status' => 'error',
            'pesan' => 'Gagal disimpan! Pastikan Subject dan Penulis tidak boleh kosong bro.'
        ]);
        exit;
    }

    // --- AMBIL NAMA FILE LAMA DARI DATABASE (BIAR BISA DIHAPUS NANTI) ---
    $file_lama = "";
    if(!empty($id_popup)) {
        $q_cek = mysqli_query($koneksi, "SELECT file_gambar FROM popup_jadwal WHERE id='$id_popup'");
        if($row = mysqli_fetch_assoc($q_cek)) {
            $file_lama = $row['file_gambar'];
        }
    }

    // 3. JURUS NANGKEP FILE GAMBAR & MENGHAPUS YANG LAMA
    $query_gambar = ""; // Variabel penolong buat update kolom file_gambar
    $nama_file_baru = "";
    
    if (isset($_FILES['file_gambar']) && $_FILES['file_gambar']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $file_name   = $_FILES['file_gambar']['name'];
        $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_tmp    = $_FILES['file_gambar']['tmp_name'];
        
        // Cek ekstensi file
        if (in_array($file_ext, $allowed_ext)) {
            $nama_file_baru = time() . '_' . uniqid() . '.' . $file_ext;
            $path_upload = '../assets/img/popup/' . $nama_file_baru;
            
            // Pindahkan file baru ke folder
            if (move_uploaded_file($file_tmp, $path_upload)) {
                
                // JURUS SAPU BERSIH: Hapus gambar lama dari server (Biar ga menuh-menuhin hosting)
                if(!empty($file_lama)) {
                    $path_lama = '../assets/img/popup/' . $file_lama;
                    if(file_exists($path_lama)) {
                        unlink($path_lama); // Delete file!
                    }
                }

                // Siapkan peluru buat update database
                $query_gambar = ", file_gambar='$nama_file_baru'";
            } else {
                echo json_encode(['status' => 'error', 'pesan' => 'Gagal memindahkan file gambar ke folder server.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'pesan' => 'Format file ditolak bro! Cuma boleh JPG, PNG, atau WEBP.']);
            exit;
        }
    }

    // 4. Logika Insert atau Update Database
    if (!empty($id_popup)) {
        // Kalau update, `$query_gambar` cuma bakal keisi kalau ada file baru yang diupload. 
        // Kalau gaada file baru, dia tetep pake gambar yang lama.
        $query = "UPDATE popup_jadwal 
                  SET subject='$subject', deskripsi='$deskripsi', status='$status', status_video='$status_video', status_gambar='$status_gambar', penulis='$penulis', created_at='$waktu_sekarang' $query_gambar 
                  WHERE id='$id_popup'";
    } else {
        // Kalau bener-bener insert dari nol
        $query = "INSERT INTO popup_jadwal (subject, deskripsi, status, status_video, status_gambar, file_gambar, penulis, created_at) 
                  VALUES ('$subject', '$deskripsi', '$status', '$status_video', '$status_gambar', '$nama_file_baru', '$penulis', '$waktu_sekarang')";
    }

    $eksekusi = mysqli_query($koneksi, $query);

    if ($eksekusi) {
        echo json_encode([
            'status' => 'sukses',
            'pesan' => 'Mantap! Pengumuman dan setting berhasil disimpan.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'pesan' => 'Waduh, terjadi kesalahan saat menyimpan ke database: ' . mysqli_error($koneksi)
        ]);
    }

} else {
    echo json_encode([
        'status' => 'error',
        'pesan' => 'Akses ditolak!'
    ]);
}
?>