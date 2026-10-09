<?php
include '../koneksi.php';

// Judul kita kosongkan otomatis (karena user gak input lagi)
$judul = "-"; 

// Logika Upload
$nama_file = $_FILES['foto']['name'];
$tmp_file = $_FILES['foto']['tmp_name'];
$ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);

// Rename file unik
$nama_baru = time() . "_galeri." . $ekstensi;
$tujuan = "../assets/img/galeri/" . $nama_baru;

// Cek format gambar
if(in_array(strtolower($ekstensi), ['jpg', 'jpeg', 'png'])) {
    if(move_uploaded_file($tmp_file, $tujuan)) {
        
        // Simpan ke DB (Kolom judul diisi default "-")
        $query = "INSERT INTO galleries (judul, foto) VALUES ('$judul', '$nama_baru')";
        
        if(mysqli_query($koneksi, $query)){
            header("location:../frontend/galeri.php");
        } else {
            echo "Gagal database: " . mysqli_error($koneksi);
        }

    } else {
        echo "Gagal upload file ke folder.";
    }
} else {
    echo "Format file harus JPG/PNG.";
}
?>