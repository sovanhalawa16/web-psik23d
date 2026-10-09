<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['id'])) { header("location:../index.php"); exit; }
$user_id = $_SESSION['id'];
$act = $_GET['act'];

// --- TAMBAH PROJECT (MULTIPLE IMAGE) ---
if($act == 'add') {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul_project']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $link_github = mysqli_real_escape_string($koneksi, $_POST['link_github']);
    $link_demo = mysqli_real_escape_string($koneksi, $_POST['link_demo']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);

    // 1. Insert Data Project Dulu (Tanpa Gambar Utama Dulu)
    $q_project = "INSERT INTO user_projects (user_id, judul_project, deskripsi, gambar_project, link_github, link_demo, kategori) 
                  VALUES ('$user_id', '$judul', '$deskripsi', 'default_project.jpg', '$link_github', '$link_demo', '$kategori')";
    
    if(mysqli_query($koneksi, $q_project)){
        $project_id = mysqli_insert_id($koneksi); // Ambil ID project yang barusan dibuat

        // 2. Proses Multiple Upload
        $jumlah_file = count($_FILES['gambar_project']['name']);
        $first_image = ""; // Buat nyimpen nama file pertama sbg thumbnail

        for($i = 0; $i < $jumlah_file; $i++) {
            $nama_file = $_FILES['gambar_project']['name'][$i];
            $tmp_file = $_FILES['gambar_project']['tmp_name'][$i];

            if(!empty($nama_file)){
                $nama_baru = time() . "_" . $i . "_" . $nama_file;
                $path = "../assets/img/projects/" . $nama_baru;

                if(move_uploaded_file($tmp_file, $path)){
                    // Simpan ke tabel galeri
                    mysqli_query($koneksi, "INSERT INTO project_gallery (project_id, foto) VALUES ('$project_id', '$nama_baru')");
                    
                    // Ambil foto pertama untuk jadi cover/thumbnail di tabel utama
                    if($i == 0) { $first_image = $nama_baru; }
                }
            }
        }

        // 3. Update Thumbnail Project Utama (Biar di dashboard tetap muncul 1 foto)
        if(!empty($first_image)){
            mysqli_query($koneksi, "UPDATE user_projects SET gambar_project='$first_image' WHERE id='$project_id'");
        }

        header("location:../frontend/user_profil.php#tab-project");
    } else {
        echo "Gagal: " . mysqli_error($koneksi);
    }
}

    elseif($act == 'delete') {
    $id = $_GET['id'];
    
    // Hapus file gambarnya dulu biar bersih
    $q_gam = mysqli_query($koneksi, "SELECT gambar_project FROM user_projects WHERE id='$id' AND user_id='$user_id'");
    $d_gam = mysqli_fetch_assoc($q_gam);
    if($d_gam && $d_gam['gambar_project'] != 'default_project.jpg'){
        unlink("../assets/img/projects/".$d_gam['gambar_project']);
    }

    mysqli_query($koneksi, "DELETE FROM user_projects WHERE id='$id' AND user_id='$user_id'");
    header("location:../frontend/user_profil.php#tab-project");
}
?>