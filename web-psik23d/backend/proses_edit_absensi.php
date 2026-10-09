<?php
session_start();
include '../koneksi.php';

date_default_timezone_set('Asia/Jakarta');

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../frontend/tampil_absensi.php");
    exit;
}

$grup_id = (int)($_POST['grup_id'] ?? 0);
if ($grup_id <= 0) {
    tampilkanNotif('error', 'Error', 'ID grup tidak valid.', '../frontend/tampil_absensi.php');
}

$query_group = mysqli_query($koneksi, "SELECT * FROM absensi_real WHERE id = $grup_id");
$group = mysqli_fetch_assoc($query_group);
if (!$group) {
    tampilkanNotif('error', 'Error', 'Data grup tidak ditemukan.', '../frontend/tampil_absensi.php');
}

$nama_mk_baru     = trim(mysqli_real_escape_string($koneksi, $_POST['nama_mk'] ?? ''));
$kode_mk_baru     = trim(mysqli_real_escape_string($koneksi, $_POST['kode_mk'] ?? ''));
$dosen_baru       = trim(mysqli_real_escape_string($koneksi, $_POST['dosen_pengampu'] ?? ''));
$semester_baru    = trim(mysqli_real_escape_string($koneksi, $_POST['semester'] ?? ''));
$blok_baru        = trim(mysqli_real_escape_string($koneksi, $_POST['blok'] ?? ''));
$sks_baru         = (int)($_POST['sks'] ?? 0);
$tipe_kelas_baru  = trim(mysqli_real_escape_string($koneksi, $_POST['tipe_kelas'] ?? 'Teori'));
$pertemuan_baru   = (int)($_POST['pertemuan_ke'] ?? 0);
$metode_baru      = trim(mysqli_real_escape_string($koneksi, $_POST['metode_pembelajaran'] ?? 'offline'));
$ruangan_baru     = trim(mysqli_real_escape_string($koneksi, $_POST['ruangan_link'] ?? ''));
$waktu_mulai_baru = mysqli_real_escape_string($koneksi, $_POST['waktu_mulai'] ?? '');
$waktu_selesai_baru = mysqli_real_escape_string($koneksi, $_POST['waktu_selesai'] ?? '');

// Validasi metode (hanya 4 nilai yang diperbolehkan)
$allowed_methods = ['offline', 'online', 'asynchronous', 'kelas batal'];
if (!in_array($metode_baru, $allowed_methods)) $metode_baru = 'offline';

if (($metode_baru == 'offline' || $metode_baru == 'online') && empty($ruangan_baru)) {
    tampilkanNotif('warning', 'Perhatian', 'Untuk metode offline/online, Ruangan/Link wajib diisi.', "../frontend/edit_absensi.php?id=$grup_id");
}

$filter = "nama_mk = '{$group['nama_mk']}' 
    AND kode_mk = '{$group['kode_mk']}' 
    AND pertemuan_ke = {$group['pertemuan_ke']} 
    AND semester = '{$group['semester']}' 
    AND blok = '{$group['blok']}' 
    AND waktu_mulai = '{$group['waktu_mulai']}'";

$query_update_umum = "UPDATE absensi_real SET 
    nama_mk = '$nama_mk_baru',
    kode_mk = '$kode_mk_baru',
    dosen_pengampu = '$dosen_baru',
    semester = '$semester_baru',
    blok = '$blok_baru',
    sks = $sks_baru,
    tipe_kelas = '$tipe_kelas_baru',
    pertemuan_ke = $pertemuan_baru,
    metode_pembelajaran = '$metode_baru',
    ruangan_link = '$ruangan_baru',
    waktu_mulai = '$waktu_mulai_baru',
    waktu_selesai = '$waktu_selesai_baru'
    WHERE $filter";

if (!mysqli_query($koneksi, $query_update_umum)) {
    tampilkanNotif('error', 'Gagal Update', mysqli_error($koneksi), "../frontend/edit_absensi.php?id=$grup_id");
}

$mhs_ids = $_POST['mhs_id'] ?? [];
$mhs_nim = $_POST['mhs_nim'] ?? [];

for ($i = 0; $i < count($mhs_ids); $i++) {
    $id_mhs = (int)$mhs_ids[$i];
    $nim_safe = preg_replace('/[^a-zA-Z0-9]/', '', $mhs_nim[$i]);
    $status_baru = mysqli_real_escape_string($koneksi, $_POST["status_absen_$nim_safe"] ?? 'hadir');
    $query = "UPDATE absensi_real SET status_absensi = '$status_baru' WHERE id = $id_mhs";
    mysqli_query($koneksi, $query);
}

if (isset($_POST['hapus_ids']) && is_array($_POST['hapus_ids'])) {
    $hapus_ids = array_map('intval', $_POST['hapus_ids']);
    $ids_str = implode(',', $hapus_ids);
    $query_hapus = "DELETE FROM absensi_real WHERE id IN ($ids_str)";
    mysqli_query($koneksi, $query_hapus);
}

tampilkanNotif('success', 'Berhasil', 'Data absensi berhasil diperbarui.', '../frontend/tampil_absensi.php');
?>