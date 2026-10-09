<?php
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ============ SESI: SIMPAN ============
if ($action == 'save_sesi') {
    $id = (int)($_POST['id'] ?? 0);
    $hari_tanggal = mysqli_real_escape_string($koneksi, $_POST['hari_tanggal']);
    $pukul_sesi = mysqli_real_escape_string($koneksi, $_POST['pukul_sesi']);
    $notulen = mysqli_real_escape_string($koneksi, $_POST['notulen']);
    $waktu_update = date('Y-m-d H:i:s');

    if ($id > 0) {
        $q = "UPDATE sempro_sesi SET hari_tanggal='$hari_tanggal', pukul_sesi='$pukul_sesi', notulen='$notulen', waktu_update='$waktu_update' WHERE id='$id'";
    } else {
        $q = "INSERT INTO sempro_sesi (hari_tanggal, pukul_sesi, notulen, waktu_update) VALUES ('$hari_tanggal','$pukul_sesi','$notulen','$waktu_update')";
    }
    mysqli_query($koneksi, $q);
    header("location:../frontend/admin_sempro.php?msg=sukses");
    exit;
}

// ============ SESI: HAPUS ============
if ($action == 'delete_sesi') {
    $id = (int)($_GET['id'] ?? 0);
    mysqli_query($koneksi, "DELETE FROM sempro_sesi WHERE id='$id'");
    header("location:../frontend/admin_sempro.php?msg=hapus");
    exit;
}

// ============ MAHASISWA: SIMPAN ============
if ($action == 'save_mhs') {
    $id = (int)($_POST['id'] ?? 0);
    $sesi_id = (int)$_POST['sesi_id'];
    $nama_mhs = mysqli_real_escape_string($koneksi, $_POST['nama_mhs']);
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);
    $judul_skripsi = mysqli_real_escape_string($koneksi, $_POST['judul_skripsi']);
    $pukul_mhs = mysqli_real_escape_string($koneksi, $_POST['pukul_mhs']);
    $durasi_menit = (int)($_POST['durasi_menit'] ?? 60);
    $ruang = mysqli_real_escape_string($koneksi, $_POST['ruang']);
    $dosen_ps = mysqli_real_escape_string($koneksi, $_POST['dosen_ps']);
    $dosen_pa = mysqli_real_escape_string($koneksi, $_POST['dosen_pa']);
    $dosen_ns1 = mysqli_real_escape_string($koneksi, $_POST['dosen_ns1']);
    $dosen_ns2 = mysqli_real_escape_string($koneksi, $_POST['dosen_ns2']);
    $dosen_ns3 = mysqli_real_escape_string($koneksi, $_POST['dosen_ns3']);
    $waktu_update = date('Y-m-d H:i:s');

    if ($id > 0) {
        $q = "UPDATE sempro_mhs SET 
                sesi_id='$sesi_id', nama_mhs='$nama_mhs', nim='$nim', prodi='$prodi',
                judul_skripsi='$judul_skripsi', pukul_mhs='$pukul_mhs', durasi_menit='$durasi_menit',
                ruang='$ruang', dosen_ps='$dosen_ps', dosen_pa='$dosen_pa',
                dosen_ns1='$dosen_ns1', dosen_ns2='$dosen_ns2', dosen_ns3='$dosen_ns3',
                waktu_update='$waktu_update' WHERE id='$id'";
    } else {
        $q = "INSERT INTO sempro_mhs 
                (sesi_id, nama_mhs, nim, prodi, judul_skripsi, pukul_mhs, durasi_menit, ruang,
                 dosen_ps, dosen_pa, dosen_ns1, dosen_ns2, dosen_ns3, waktu_update)
              VALUES 
                ('$sesi_id','$nama_mhs','$nim','$prodi','$judul_skripsi','$pukul_mhs','$durasi_menit','$ruang',
                 '$dosen_ps','$dosen_pa','$dosen_ns1','$dosen_ns2','$dosen_ns3','$waktu_update')";
    }
    mysqli_query($koneksi, $q);
    header("location:../frontend/admin_sempro.php?msg=sukses");
    exit;
}

// ============ MAHASISWA: HAPUS ============
if ($action == 'delete_mhs') {
    $id = (int)($_GET['id'] ?? 0);
    mysqli_query($koneksi, "DELETE FROM sempro_mhs WHERE id='$id'");
    header("location:../frontend/admin_sempro.php?msg=hapus");
    exit;
}

header("location:../frontend/admin_sempro.php");
exit;
?>