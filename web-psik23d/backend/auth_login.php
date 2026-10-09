<?php
session_start();
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta'); // Sinkronkan waktu

$nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
$password = $_POST['password'];

$query = mysqli_query($koneksi, "SELECT * FROM users WHERE nim='$nim'");

if(mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);

    if(password_verify($password, $data['password']) || $password == $data['password']) {
        
        // ==========================================
        // 1. GENERATE TOKEN SESI BARU (KUNCI UTAMA)
        // ==========================================
        // Setiap login baru = Token baru. Ini yang bikin device lama ketendang.
        $token_baru = bin2hex(random_bytes(32)); 
        
        // ==========================================
        // 2. SET SESSION
        // ==========================================
        $_SESSION['id'] = $data['id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['nim'] = $data['nim'];
        $_SESSION['role'] = $data['role']; 
        $_SESSION['foto'] = $data['foto'];
        $_SESSION['status'] = "login";
        $_SESSION['last_activity'] = time();
        
        // SIMPAN TOKEN DI SESSION (PENTING!)
        $_SESSION['login_token'] = $token_baru; 

        // ==========================================
        // 3. UPDATE DATABASE (Token & Waktu)
        // ==========================================
        // Kita update token di DB dengan token baru. Device lama tokennya jadi basi.
        $waktu_skrg = date("Y-m-d H:i:s");
        mysqli_query($koneksi, "UPDATE users SET last_active = '$waktu_skrg', remember_token = '$token_baru' WHERE id='$data[id]'");

        // ==========================================
        // 4. FITUR COOKIE (INGAT SAYA)
        // ==========================================
        if(isset($_POST['remember'])) {
            // Cookie juga simpan token yang sama
            setcookie('id_user', $data['id'], time() + (86400 * 30), "/", "", true, true);
            setcookie('token', $token_baru, time() + (86400 * 30), "/", "", true, true);
        }

        // Redirect
        if($data['role'] == "admin"){
            header("location:../frontend/admin_dashboard.php");
        } else {
            header("location:../frontend/user_dashboard.php");
        }
        exit;

    } else {
        $_SESSION['error'] = "Password salah!";
        header("location:../");
        exit;
    }
} else {
    $_SESSION['error'] = "NIM tidak ditemukan!";
    header("location:../");
    exit;
}
?>