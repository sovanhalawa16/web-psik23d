<?php
session_start();
include '../koneksi.php';

// Cek Sesi
if(!isset($_SESSION['id'])){
    header("location:../frontend/login.php?pesan=sesi_ilegal");
    exit;
}

$id_user = $_SESSION['id'];

// 1. TANGKAP INPUTAN TEXT (DATA DIRI)
$email = mysqli_real_escape_string($koneksi, $_POST['email']); 
$tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
$tanggal_lahir = $_POST['tanggal_lahir']; 
$alamat_asal = mysqli_real_escape_string($koneksi, $_POST['alamat_asal']);
$motto = mysqli_real_escape_string($koneksi, $_POST['motto']);

// 2. TANGKAP SOSMED (LENGKAP)
// Pastikan name="..." di form frontend sesuai dengan yang ditangkap ini
$ig = mysqli_real_escape_string($koneksi, $_POST['instagram']);
$fb = mysqli_real_escape_string($koneksi, $_POST['facebook']);
$gh = mysqli_real_escape_string($koneksi, $_POST['github']);
$linkedin = mysqli_real_escape_string($koneksi, $_POST['linkedin']); 
$website = mysqli_real_escape_string($koneksi, $_POST['website']);   
$threads = mysqli_real_escape_string($koneksi, $_POST['threads']); // <-- BARU
$twitter = mysqli_real_escape_string($koneksi, $_POST['twitter']); // <-- BARU

// 3. AMBIL DATA LAMA (Untuk default foto & password jika tidak diubah)
$q_cek = mysqli_query($koneksi, "SELECT foto, cover_photo, password FROM users WHERE id='$id_user'");
$data_lama = mysqli_fetch_assoc($q_cek);

$foto_final = $data_lama['foto'];
$cover_final = $data_lama['cover_photo']; 
$pass_final = $data_lama['password'];      

// 4. LOGIKA UPLOAD FOTO PROFIL
if(isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != "") {
    $nama_file = $_FILES['foto']['name'];
    $tmp_file = $_FILES['foto']['tmp_name'];
    $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    
    if(in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
        // Hapus foto lama jika bukan default
        $path_lama = "../assets/img/mahasiswa/" . $data_lama['foto'];
        if($data_lama['foto'] != 'default.png' && file_exists($path_lama)){
            unlink($path_lama);
        }
        
        // Upload foto baru
        $nama_baru = "mhs_" . $id_user . "_" . time() . "." . $ekstensi;
        $tujuan = "../assets/img/mahasiswa/" . $nama_baru;
        
        if(move_uploaded_file($tmp_file, $tujuan)){
            $foto_final = $nama_baru;
            $_SESSION['foto'] = $nama_baru; // Update session foto dashboard
        }
    }
}

// 5. LOGIKA UPLOAD COVER PHOTO
if(isset($_FILES['cover_photo']['name']) && $_FILES['cover_photo']['name'] != "") {
    $nama_cover = $_FILES['cover_photo']['name'];
    $tmp_cover = $_FILES['cover_photo']['tmp_name'];
    $ext_cover = strtolower(pathinfo($nama_cover, PATHINFO_EXTENSION));

    if(in_array($ext_cover, ['jpg', 'jpeg', 'png'])) {
        // Hapus cover lama jika bukan default
        $path_cover_lama = "../assets/img/cover/" . $data_lama['cover_photo'];
        if($data_lama['cover_photo'] != 'default_cover.jpg' && file_exists($path_cover_lama)){
            unlink($path_cover_lama);
        }

        // Upload cover baru
        $cover_baru = "cover_" . $id_user . "_" . time() . "." . $ext_cover;
        $tujuan_cover = "../assets/img/cover/" . $cover_baru;

        if(move_uploaded_file($tmp_cover, $tujuan_cover)){
            $cover_final = $cover_baru;
        }
    }
}

// 6. LOGIKA PASSWORD BARU
// Cek apakah user input password baru?
if(isset($_POST['password']) && !empty($_POST['password'])) {
    $password_baru = $_POST['password'];
    // Jika pakai hash: $pass_final = password_hash($password_baru, PASSWORD_DEFAULT);
    // Jika plain text (sesuai kodemu):
    $pass_final = $password_baru;
}

// 7. UPDATE DATABASE (MASUKKAN SEMUA KOLOM BARU)
$query = "UPDATE users SET 
            email = '$email',
            tempat_lahir = '$tempat_lahir',
            tanggal_lahir = '$tanggal_lahir',
            alamat_asal = '$alamat_asal',
            motto = '$motto',
            instagram = '$ig',
            facebook = '$fb',
            github = '$gh',
            linkedin = '$linkedin',
            website = '$website',
            threads = '$threads',
            twitter = '$twitter',
            foto = '$foto_final',
            cover_photo = '$cover_final',
            password = '$pass_final'
          WHERE id = '$id_user'";

if(mysqli_query($koneksi, $query)){
    // Redirect balik ke edit profil dengan pesan sukses
    header("location:../frontend/edit_profil.php?pesan=sukses_update");
} else {
    // Tampilkan error jika query gagal (berguna untuk debug)
    echo "Gagal Update: " . mysqli_error($koneksi);
}
?>