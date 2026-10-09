<?php 
session_start();
include '../koneksi.php'; 

date_default_timezone_set('Asia/Jakarta');
$created_at = date('Y-m-d H:i:s');

function tampilkanNotif($tipe, $judul, $pesan, $redirect_url = null) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"><style>body{font-family:"Inter",sans-serif;background:#f1f5f9;}</style></head><body>';
    echo "<script>
        Swal.fire({
            icon: '$tipe',
            title: '$judul',
            text: '$pesan',
            confirmButtonColor: '#8b5cf6',
            confirmButtonText: 'Lanjutkan',
            backdrop: true
        }).then(() => { " . ($redirect_url ? "window.location.href='$redirect_url'" : "window.history.back()") . " });
    </script></body></html>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_mk             = trim(mysqli_real_escape_string($koneksi, $_POST['nama_mk'] ?? ''));
    $kode_mk             = trim(mysqli_real_escape_string($koneksi, $_POST['kode_mk'] ?? ''));
    $dosen_pengampu      = trim(mysqli_real_escape_string($koneksi, $_POST['dosen_pengampu'] ?? ''));
    $semester            = trim(mysqli_real_escape_string($koneksi, $_POST['semester'] ?? ''));
    $blok                = trim(mysqli_real_escape_string($koneksi, $_POST['blok'] ?? ''));
    $sks                 = (int)($_POST['sks'] ?? 0);
    $tipe_kelas          = trim(mysqli_real_escape_string($koneksi, $_POST['tipe_kelas'] ?? 'Teori'));
    $pertemuan_ke        = (int)($_POST['pertemuan_ke'] ?? 0);
    $metode_pembelajaran = trim(mysqli_real_escape_string($koneksi, $_POST['metode_pembelajaran'] ?? 'offline'));
    $ruangan_link        = trim(mysqli_real_escape_string($koneksi, $_POST['ruangan_link'] ?? ''));
    $waktu_mulai         = str_replace('T', ' ', mysqli_real_escape_string($koneksi, $_POST['waktu_mulai'] ?? ''));
    $waktu_selesai       = str_replace('T', ' ', mysqli_real_escape_string($koneksi, $_POST['waktu_selesai'] ?? ''));

    // Validasi metode pembelajaran (hanya 4 nilai yang diperbolehkan)
    $allowed_methods = ['offline', 'online', 'asynchronous', 'kelas batal'];
    if (!in_array($metode_pembelajaran, $allowed_methods)) {
        $metode_pembelajaran = 'offline'; // fallback aman
    }

    // Validasi ruangan untuk offline/online
    if (($metode_pembelajaran == 'offline' || $metode_pembelajaran == 'online') && empty($ruangan_link)) {
        tampilkanNotif('warning', 'Perhatian', 'Untuk metode offline/online, Ruangan/Link wajib diisi.');
    }

    $mhs_nama = $_POST['mhs_nama'] ?? [];
    $mhs_nim  = $_POST['mhs_nim'] ?? [];

    if (empty($nama_mk) || empty($mhs_nama)) {
        tampilkanNotif('error', 'Data Tidak Lengkap', 'Pastikan semua field terisi.');
    }

    $values = [];
    for ($i = 0; $i < count($mhs_nama); $i++) {
        $nama   = mysqli_real_escape_string($koneksi, $mhs_nama[$i]);
        $nim    = mysqli_real_escape_string($koneksi, $mhs_nim[$i]);
        $nim_safe = preg_replace('/[^a-zA-Z0-9]/', '', $nim);
        $status = mysqli_real_escape_string($koneksi, $_POST["status_absen_$nim_safe"] ?? 'hadir');

        $values[] = "('$nama', '$nim', '$nama_mk', '$kode_mk', '$dosen_pengampu', '$semester', '$blok', '$sks', '$tipe_kelas', '$pertemuan_ke', '$metode_pembelajaran', '$ruangan_link', '$waktu_mulai', '$waktu_selesai', '$status', '$created_at')";
    }

    $sql = "INSERT INTO absensi_real 
            (nama_lengkap, nim, nama_mk, kode_mk, dosen_pengampu, semester, blok, sks, tipe_kelas, pertemuan_ke, metode_pembelajaran, ruangan_link, waktu_mulai, waktu_selesai, status_absensi, created_at)
            VALUES " . implode(", ", $values);

    if (mysqli_query($koneksi, $sql)) {
        tampilkanNotif('success', 'Berhasil Tersimpan', "Absensi $nama_mk (pertemuan $pertemuan_ke) telah disimpan.", '../frontend/input_absensi.php');
    } else {
        $err = mysqli_error($koneksi);
        tampilkanNotif('error', 'Gagal Menyimpan', "Terjadi kesalahan: $err");
    }
} else {
    header("Location: ../frontend/admin_dashboard.php");
    exit;
}
?>