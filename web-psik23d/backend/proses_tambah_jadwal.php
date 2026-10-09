<?php
include '../koneksi.php';

// Tangkap Data Dasar
$blok = $_POST['blok'];
$kode_mk = $_POST['kode_mk'];
$semester = $_POST['semester'];
$sks = $_POST['sks'];
$pertemuan_ke = $_POST['pertemuan_ke'];
$hari = $_POST['hari'];
$mata_kuliah = $_POST['mata_kuliah'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$dosen_id = $_POST['dosen_id'];
$catatan = $_POST['catatan'];
// INI DIA TANGKAPAN KATEGORI KELASNYA
$kategori_kelas = mysqli_real_escape_string($koneksi, $_POST['kategori_kelas']);

// LOGIC JENIS KELAS
$jenis_kelas = $_POST['jenis_kelas']; // 'offline' atau 'online'
$ruangan = "";
$link_online = "";

// 1. Tentukan Default Gambar berdasarkan jenis kelas
$foto_ruangan = ($jenis_kelas == 'offline') ? "default_room.jpg" : "default_online.jpg";

if ($jenis_kelas == 'offline') {
    $ruangan = $_POST['ruangan'];
} else {
    // Kalau Online
    $ruangan = "ONLINE"; 
    $link_online = $_POST['link_online'];
}

// --- 2. LOGIC UPLOAD FOTO/VIDEO (SEKARANG BERLAKU UNTUK OFFLINE & ONLINE) ---
if(isset($_FILES['foto_ruangan']['name']) && $_FILES['foto_ruangan']['name'][0] != "") {
    $file_terupload = [];
    $jumlah_file = count($_FILES['foto_ruangan']['name']);
    
    for($i = 0; $i < $jumlah_file; $i++) {
        $nama_file = $_FILES['foto_ruangan']['name'][$i];
        $tmp_file = $_FILES['foto_ruangan']['tmp_name'][$i];
        
        if($nama_file != "") {
            // Bersihkan nama file dari karakter aneh dan tambahkan penanda unik
            $nama_file_bersih = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $nama_file);
            $nama_baru = time() . "_" . $i . "_media_" . $nama_file_bersih;
            
            if(move_uploaded_file($tmp_file, "../assets/img/ruangan/" . $nama_baru)) {
                $file_terupload[] = $nama_baru;
            }
        }
    }
    
    // Jika ada file yang sukses terupload, timpa default dengan nama file baru
    if(count($file_terupload) > 0) {
        $foto_ruangan = implode(",", $file_terupload);
    }
}

// Eksekusi Query (Kolom kategori_kelas udah masuk sini!)
$query = "INSERT INTO jadwal (blok, kode_mk, semester, sks, pertemuan_ke, hari, mata_kuliah, jam_mulai, jam_selesai, dosen_id, ruangan, foto_ruangan, catatan, jenis_kelas, link_online, kategori_kelas) 
          VALUES ('$blok', '$kode_mk', '$semester', '$sks', '$pertemuan_ke', '$hari', '$mata_kuliah', '$jam_mulai', '$jam_selesai', '$dosen_id', '$ruangan', '$foto_ruangan', '$catatan', '$jenis_kelas', '$link_online', '$kategori_kelas')";

if(mysqli_query($koneksi, $query)){
    header("location:../frontend/jadwal.php");
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>