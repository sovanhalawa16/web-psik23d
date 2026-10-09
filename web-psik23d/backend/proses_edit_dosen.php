<?php
include '../koneksi.php';

// 1. Tangkap Data dari Form
$id = $_POST['id'];
$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$nidn = mysqli_real_escape_string($koneksi, $_POST['nidn']);
$jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
$link_sinta = mysqli_real_escape_string($koneksi, $_POST['link_sinta']);
$link_scholar = mysqli_real_escape_string($koneksi, $_POST['link_scholar']);

// 2. Cek apakah User Mengupload Foto Baru?
$nama_file = $_FILES['foto']['name'];
$tmp_file = $_FILES['foto']['tmp_name'];

// KONDISI A: JIKA ADA FOTO BARU
if($nama_file != "") {
    
    // Rename nama file biar unik (pake timestamp)
    $nama_baru = time() . "_" . $nama_file;
    $path = "../assets/img/dosen/" . $nama_baru;

    // Coba upload file
    if(move_uploaded_file($tmp_file, $path)) {
        
        // (Opsional) Hapus foto lama biar server gak penuh
        // $q_lama = mysqli_query($koneksi, "SELECT foto FROM dosen WHERE id='$id'");
        // $f_lama = mysqli_fetch_assoc($q_lama);
        // if(file_exists("../assets/img/dosen/".$f_lama['foto']) && $f_lama['foto'] != 'default.png'){
        //     unlink("../assets/img/dosen/".$f_lama['foto']);
        // }

        // Query Update DENGAN Foto
        $query = "UPDATE dosen SET 
                    nama = '$nama', 
                    nidn = '$nidn', 
                    jabatan = '$jabatan', 
                    foto = '$nama_baru', 
                    link_sinta = '$link_sinta', 
                    link_scholar = '$link_scholar' 
                  WHERE id = '$id'";
                  
    } else {
        echo "Gagal upload gambar!";
        exit;
    }

} else {
    // KONDISI B: JIKA TIDAK GANTI FOTO (Foto tetap yang lama)
    $query = "UPDATE dosen SET 
                nama = '$nama', 
                nidn = '$nidn', 
                jabatan = '$jabatan', 
                link_sinta = '$link_sinta', 
                link_scholar = '$link_scholar' 
              WHERE id = '$id'";
}

// 3. Eksekusi Query
if(mysqli_query($koneksi, $query)) {
    // Berhasil -> Balik ke halaman Admin Dosen (Ganti sesuai nama file admin dosenmu)
    header("location:../frontend/dosen.php?pesan=edit_sukses");
} else {
    echo "Gagal mengupdate data: " . mysqli_error($koneksi);
}
?>