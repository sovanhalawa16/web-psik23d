<?php
header('Content-Type: application/json');
include '../koneksi.php';

// Pastikan method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

// 1. Tangkap Input NIM & Password
$nim = isset($_POST['nim']) ? mysqli_real_escape_string($koneksi, trim($_POST['nim'])) : '';
$password_input = isset($_POST['password']) ? trim($_POST['password']) : '';

// 2. Validasi Input Kosong
if(empty($nim) || empty($password_input)) {
    echo json_encode(['status' => 'error', 'message' => 'NIM dan Password wajib diisi!']);
    exit;
}

// 3. Cari User Berdasarkan NIM
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE nim='$nim'");

if(mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    $password_db = $data['password']; // Password yang tersimpan di DB

    // 4. Verifikasi Password
    $is_valid = false;

    // Cek 1: Jika menggunakan password_hash() (Standar PHP Modern)
    if (password_verify($password_input, $password_db)) {
        $is_valid = true;
    }
    // Cek 2: Jika menggunakan MD5 (Cara Lama)
    else if (md5($password_input) === $password_db) {
        $is_valid = true;
    }
    // Cek 3: Jika Plain Text (Tanpa Enkripsi - Jaga-jaga project mahasiswa)
    else if ($password_input === $password_db) {
        $is_valid = true;
    }

    // 5. Hasil Verifikasi
    if ($is_valid) {
        // --- DATA COCOK, SIAPKAN RESPON ---

        // Handle Foto URL
        if(empty($data['foto']) || $data['foto'] == 'default.png') {
            $data['foto_url'] = "https://ui-avatars.com/api/?name=".urlencode($data['nama'])."&background=0d6efd&color=fff&size=500&bold=true";
        } else {
            // Pastikan path ini sesuai struktur foldermu
            $data['foto_url'] = "../assets/img/mahasiswa/" . $data['foto'];
        }

        // Format Tanggal Lahir (Indonesia)
        if(!empty($data['tanggal_lahir'])) {
            $bulanIndo = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            $tgl = date('j', strtotime($data['tanggal_lahir']));
            $bln = $bulanIndo[date('n', strtotime($data['tanggal_lahir']))];
            $thn = date('Y', strtotime($data['tanggal_lahir']));
            $data['ttl_format'] = ($data['tempat_lahir'] ?? '') . ", $tgl $bln $thn";
        } else {
            $data['ttl_format'] = "-";
        }

        // Status Default
        $data['status_mhs'] = isset($data['status']) ? $data['status'] : 'Aktif';

        // Hapus password dari array response biar aman (JANGAN KIRIM PASSWORD BALIK)
        unset($data['password']);

        echo json_encode(['status' => 'success', 'data' => $data]);

    } else {
        // Password Salah
        echo json_encode(['status' => 'error', 'message' => 'Password yang Anda masukkan salah!']);
    }

} else {
    // NIM Tidak Ditemukan
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak terdaftar di sistem!']);
}
?>