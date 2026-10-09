<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['id'])) { header("location:../index.php"); exit; }
$user_id = $_SESSION['id'];
$act = $_GET['act']; // menangkap aksi (add_general, del_general, add_coding, del_coding)

// --- A. SKILL GENERAL (UMUM) ---
if($act == 'add_general') {
    $nama_skill = mysqli_real_escape_string($koneksi, $_POST['nama_skill']);
    $persentase = $_POST['persentase'];
    
    $q = "INSERT INTO user_skills_general (user_id, nama_skill, persentase) VALUES ('$user_id', '$nama_skill', '$persentase')";
    mysqli_query($koneksi, $q);
    header("location:../frontend/user_profil.php#tab-skill");

} elseif($act == 'del_general') {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM user_skills_general WHERE id='$id' AND user_id='$user_id'");
    header("location:../frontend/user_profil.php#tab-skill");
}

// --- B. SKILL CODING (BAHASA) ---
elseif($act == 'add_coding') {
    $bahasa = mysqli_real_escape_string($koneksi, $_POST['bahasa']);
    $persentase = $_POST['persentase'];
    
    $q = "INSERT INTO user_skills_coding (user_id, bahasa, persentase) VALUES ('$user_id', '$bahasa', '$persentase')";
    mysqli_query($koneksi, $q);
    header("location:../frontend/user_profil.php#tab-skill");

} elseif($act == 'del_coding') {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM user_skills_coding WHERE id='$id' AND user_id='$user_id'");
    header("location:../frontend/user_profil.php#tab-skill");
}
?>