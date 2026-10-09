<?php
session_start();
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Generate ID Acak
    $id_tugas_acak = str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
    
    // 2. Tangkap Data Dasar
    $subject          = mysqli_real_escape_string($koneksi, trim($_POST['subject_html']));
    $mata_kuliah      = mysqli_real_escape_string($koneksi, trim($_POST['mata_kuliah']));
    $kode_mk          = mysqli_real_escape_string($koneksi, trim($_POST['kode_mk']));
    $bobot_sks        = (int)$_POST['bobot_sks'];
    $semester         = (int)$_POST['semester'];
    $dosen_id         = (int)$_POST['dosen_id'];
    
    $jenis_tugas      = mysqli_real_escape_string($koneksi, $_POST['jenis_tugas']);
    $tugas_ke         = (int)$_POST['tugas_ke'];
    
    // Tangkap Field Baru (Tipe Tugas Manual & Referensi)
    $tipe_tugas         = mysqli_real_escape_string($koneksi, trim($_POST['tipe_tugas']));
    $link_lampiran_tipe = mysqli_real_escape_string($koneksi, trim($_POST['link_lampiran_tipe']));
    $ada_referensi      = mysqli_real_escape_string($koneksi, $_POST['ada_referensi']);
    $link_referensi     = mysqli_real_escape_string($koneksi, trim($_POST['link_referensi']));

    $sifat_tugas        = mysqli_real_escape_string($koneksi, $_POST['sifat_tugas']);
    $toleransi_waktu    = isset($_POST['toleransi_waktu']) ? (int)$_POST['toleransi_waktu'] : 0;
    $butuh_upload       = mysqli_real_escape_string($koneksi, $_POST['butuh_upload']);
    $tempat_pengumpulan = isset($_POST['tempat_pengumpulan']) ? mysqli_real_escape_string($koneksi, trim($_POST['tempat_pengumpulan'])) : '';
    $butuh_presentasi   = mysqli_real_escape_string($koneksi, $_POST['butuh_presentasi']);
    $auto_close         = mysqli_real_escape_string($koneksi, $_POST['auto_close']);
    
    $waktu_mulai      = mysqli_real_escape_string($koneksi, $_POST['waktu_mulai']);
    $waktu_selesai    = mysqli_real_escape_string($koneksi, $_POST['waktu_selesai']);
    $link_pengumpulan = mysqli_real_escape_string($koneksi, trim($_POST['link_pengumpulan']));
    $deskripsi        = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi_html']));

    // 3. Proses Upload File
    $upload_dir = '../assets/img/tugas/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

    // --- A. Upload Multiple Media (Carousel) ---
    $uploaded_media = [];
    if (isset($_FILES['file_media']) && !empty($_FILES['file_media']['name'][0])) {
        foreach ($_FILES['file_media']['name'] as $key => $name) {
            $tmp_name = $_FILES['file_media']['tmp_name'][$key];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $new_name = 'TGS_' . uniqid() . '_' . time() . '.' . $ext;
            if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) { $uploaded_media[] = $new_name; }
        }
    }
    $file_media_string = implode(',', $uploaded_media);

    // --- B. Upload Lampiran Tipe Tugas (Soal) ---
    $lampiran_tipe = '';
    if (isset($_FILES['lampiran_tipe']) && $_FILES['lampiran_tipe']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['lampiran_tipe']['name'], PATHINFO_EXTENSION));
        $lampiran_tipe = 'SOAL_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['lampiran_tipe']['tmp_name'], $upload_dir . $lampiran_tipe);
    }

    // --- C. Upload File Referensi ---
    $file_referensi = '';
    if ($ada_referensi == 'Ya' && isset($_FILES['file_referensi']) && $_FILES['file_referensi']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['file_referensi']['name'], PATHINFO_EXTENSION));
        $file_referensi = 'REF_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['file_referensi']['tmp_name'], $upload_dir . $file_referensi);
    }

    // 4. Validasi Wajib
    if(empty($subject) || empty($mata_kuliah) || empty($kode_mk) || empty($semester) || empty($dosen_id) || empty($tipe_tugas) || empty($waktu_mulai)) {
        echo json_encode(['status' => 'error', 'pesan' => 'Form bertanda bintang (*) wajib diisi semua!']); exit;
    }

    // 5. Query Insert
    $query = "INSERT INTO tugas_perkuliahan (
                id_tugas_acak, file_media, subject, mata_kuliah, kode_mk, bobot_sks, semester, dosen_id, 
                jenis_tugas, tipe_tugas, lampiran_tipe, link_lampiran_tipe, tugas_ke, sifat_tugas, toleransi_waktu, 
                butuh_upload, tempat_pengumpulan, butuh_presentasi, auto_close, waktu_mulai, waktu_selesai, 
                link_pengumpulan, deskripsi, ada_referensi, file_referensi, link_referensi
              ) VALUES (
                '$id_tugas_acak', '$file_media_string', '$subject', '$mata_kuliah', '$kode_mk', '$bobot_sks', '$semester', '$dosen_id',
                '$jenis_tugas', '$tipe_tugas', '$lampiran_tipe', '$link_lampiran_tipe', '$tugas_ke', '$sifat_tugas', '$toleransi_waktu', 
                '$butuh_upload', '$tempat_pengumpulan', '$butuh_presentasi', '$auto_close', '$waktu_mulai', '$waktu_selesai', 
                '$link_pengumpulan', '$deskripsi', '$ada_referensi', '$file_referensi', '$link_referensi'
              )";

    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'sukses', 'pesan' => 'Tugas dengan ID #'.$id_tugas_acak.' berhasil dipublikasikan.']);
    } else {
        echo json_encode(['status' => 'error', 'pesan' => 'Database error: ' . mysqli_error($koneksi)]);
    }

} else {
    echo json_encode(['status' => 'error', 'pesan' => 'Akses ditolak!']);
}
?>