<?php
include '../koneksi.php';

// 1. TANGKAP INPUT
$nama = $_POST['nama'];
$nim = $_POST['nim'];
$jabatan = $_POST['jabatan']; // Komting/Sekretaris/Mahasiswa
$role = 'mahasiswa';

// 2. SETTING PASSWORD DEFAULT = NIM
// (Ini bagian yang kita ubah bro)
$password = $nim; 

// 3. LOGIKA UPLOAD FOTO
$foto_nama = "default.png"; // Default foto awal

if(isset($_FILES['foto']) && $_FILES['foto']['name'] != "") {
    $nama_file = $_FILES['foto']['name'];
    $tmp_file = $_FILES['foto']['tmp_name'];
    $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
    
    // Validasi Foto
    if(in_array(strtolower($ekstensi), ['jpg', 'jpeg', 'png'])) {
        // Rename biar unik: nim_waktu.jpg
        $foto_baru = $nim . "_" . time() . "." . $ekstensi;
        $tujuan = "../assets/img/mahasiswa/" . $foto_baru;
        
        if(move_uploaded_file($tmp_file, $tujuan)) {
            $foto_nama = $foto_baru;
        }
    }
}

// 4. SIMPAN KE DATABASE
// Pastikan variabel $password (yang isinya NIM) masuk ke query
$query = "INSERT INTO users (nama, nim, password, role, jabatan, foto) 
          VALUES ('$nama', '$nim', '$password', '$role', '$jabatan', '$foto_nama')";

if(mysqli_query($koneksi, $query)) {
    header("location:../frontend/mahasiswa.php");
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>