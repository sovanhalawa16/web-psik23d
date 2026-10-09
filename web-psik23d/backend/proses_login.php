<?php
// 1. MULAI SESI PALING ATAS
session_start();
include '../koneksi.php';

$nim = $_POST['nim'];
$password = $_POST['password'];

// 2. CEK LOGIN
$login = mysqli_query($koneksi, "SELECT * FROM users WHERE nim='$nim' AND password='$password'");
$cek = mysqli_num_rows($login);

if($cek > 0){
    $data = mysqli_fetch_assoc($login);
    
    // 3. SIMPAN DATA PENTING KE SESSION (JANGAN ADA YANG TERLEWAT)
    $_SESSION['id'] = $data['id'];      // <--- INI KUNCINYA (Tadi pasti ini belum ada)
    $_SESSION['nama'] = $data['nama'];
    $_SESSION['nim'] = $data['nim'];
    $_SESSION['role'] = $data['role'];
    $_SESSION['foto'] = $data['foto'];  // Simpan foto juga biar langsung muncul
    $_SESSION['status'] = "login";

    // 4. ARAHKAN SESUAI ROLE
    if($data['role'] == "admin"){
        header("location:../frontend/admin_dashboard.php");
    } else {
        header("location:../frontend/user_dashboard.php");
    }
} else {
    header("location:../frontend/login.php?pesan=gagal");
}
?>