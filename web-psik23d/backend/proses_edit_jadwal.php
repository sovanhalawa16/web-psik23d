<?php
include '../koneksi.php';

// 🚀 KUNCI TIMEZONE WIB
date_default_timezone_set('Asia/Jakarta');

// Tangkap ID yang mau di-edit
$id = $_POST['id'];

// Tangkap Data Dasar
$blok = $_POST['blok'];
$kode_mk = $_POST['kode_mk'];
$semester = $_POST['semester'];
$sks = $_POST['sks'];
$pertemuan_ke = $_POST['pertemuan_ke'];
$kategori_kelas = mysqli_real_escape_string($koneksi, $_POST['kategori_kelas']);
$hari = $_POST['hari'];
$mata_kuliah = $_POST['mata_kuliah'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$dosen_id = $_POST['dosen_id'];
$status_kelas = $_POST['status_kelas'];
$catatan = $_POST['catatan'];

// 🚀 SET WAKTU UPDATE EXPLICIT DALAM WIB
$waktu_update = date('Y-m-d H:i:s');

// Logic Jenis Kelas (Online / Offline)
$jenis_kelas = $_POST['jenis_kelas'];
$ruangan = "";
$link_online = "";

if ($jenis_kelas == 'offline') {
    $ruangan = $_POST['ruangan'];
} else {
    $ruangan = "ONLINE"; 
    $link_online = $_POST['link_online'];
}

// 1. TENTUKAN FOTO RUANGAN
$foto_ruangan = $_POST['foto_ruangan_lama']; 

// 2. CEK APAKAH ADMIN UPLOAD FOTO BARU
if(isset($_FILES['foto_ruangan']['name']) && $_FILES['foto_ruangan']['name'][0] != "") {
    $file_terupload = [];
    $jumlah_file = count($_FILES['foto_ruangan']['name']);
    
    for($i = 0; $i < $jumlah_file; $i++) {
        $nama_file = $_FILES['foto_ruangan']['name'][$i];
        $tmp_file = $_FILES['foto_ruangan']['tmp_name'][$i];
        
        if($nama_file != "") {
            $nama_file_bersih = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $nama_file);
            $nama_baru = time() . "_" . $i . "_media_" . $nama_file_bersih;
            
            if(move_uploaded_file($tmp_file, "../assets/img/ruangan/" . $nama_baru)) {
                $file_terupload[] = $nama_baru;
            }
        }
    }
    
    if(count($file_terupload) > 0) {
        $foto_ruangan = implode(",", $file_terupload);
    }
} else {
    if ($foto_ruangan == 'default_room.jpg' && $jenis_kelas == 'online') {
        $foto_ruangan = 'default_online.jpg';
    } elseif ($foto_ruangan == 'default_online.jpg' && $jenis_kelas == 'offline') {
        $foto_ruangan = 'default_room.jpg';
    }
}

// Eksekusi Update Query - 🚀 TAMBAHIN waktu_update
$query = "UPDATE jadwal SET 
            blok = '$blok',
            kode_mk = '$kode_mk',
            semester = '$semester',
            sks = '$sks',
            pertemuan_ke = '$pertemuan_ke',
            kategori_kelas = '$kategori_kelas',
            hari = '$hari',
            mata_kuliah = '$mata_kuliah',
            jam_mulai = '$jam_mulai',
            jam_selesai = '$jam_selesai',
            dosen_id = '$dosen_id',
            status_kelas = '$status_kelas',
            catatan = '$catatan',
            jenis_kelas = '$jenis_kelas',
            ruangan = '$ruangan',
            link_online = '$link_online',
            foto_ruangan = '$foto_ruangan',
            waktu_update = '$waktu_update'
          WHERE id = '$id'";

if(mysqli_query($koneksi, $query)){
    header("location:../frontend/jadwal.php");
} else {
    echo "Gagal Update: " . mysqli_error($koneksi);
}
?>