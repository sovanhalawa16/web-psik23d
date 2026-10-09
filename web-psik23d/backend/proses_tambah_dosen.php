<?php
include '../koneksi.php';

$nama = $_POST['nama'];
$nip = $_POST['nip'];
$nidn = $_POST['nidn'];
$jabatan = $_POST['jabatan'];
$bidang_ahli = $_POST['bidang_ahli'];
$mata_kuliah = $_POST['mata_kuliah'];
$link_sinta = $_POST['link_sinta'];
$link_scholar = $_POST['link_scholar'];

// Upload Foto
$foto_nama = "default_dosen.png";
if($_FILES['foto']['name'] != "") {
    $nama_file = $_FILES['foto']['name'];
    $tmp_file = $_FILES['foto']['tmp_name'];
    $foto_nama = time()."_".$nama_file;
    move_uploaded_file($tmp_file, "../assets/img/dosen/".$foto_nama);
}

// Query Lengkap
$query = "INSERT INTO dosen (nama, nip, nidn, jabatan, bidang_ahli, mata_kuliah, link_sinta, link_scholar, foto) 
          VALUES ('$nama', '$nip', '$nidn', '$jabatan', '$bidang_ahli', '$mata_kuliah', '$link_sinta', '$link_scholar', '$foto_nama')";

if(mysqli_query($koneksi, $query)){
    header("location:../frontend/dosen.php");
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>