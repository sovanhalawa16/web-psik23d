<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../koneksi.php'; 

// 1. Cek Login Dasar
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:../index.php"); 
    exit;
}

// 2. Update Detak Jantung
if(isset($_SESSION['id'])) {
    $id_user = $_SESSION['id'];
    mysqli_query($koneksi, "UPDATE users SET last_active = NOW() WHERE id='$id_user'");
}

// 3. Cek Timeout PHP (Server Side Backup)
$timeout_duration = 1800; // 30 Menit
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > $timeout_duration) {
        header("location:../backend/logout.php?reason=timeout");
        exit;
    }
}
$_SESSION['last_activity'] = time();

// Hitung waktu timeout untuk JS
$js_timeout = ($timeout_duration * 1000) + 1000;

echo '
<style>
    @keyframes slideDown { from {top: -100px; opacity: 0;} to {top: 20px; opacity: 1;} }
    
    .custom-toast {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.95);
        color: #fff;
        padding: 15px 25px;
        border-radius: 50px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        z-index: 99999;
        font-family: "Plus Jakarta Sans", sans-serif;
        font-weight: 600;
        font-size: 14px;
        display: none; /* Default Sembunyi */
        align-items: center;
        gap: 12px;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.1);
        width: 90%;
        max-width: 400px;
        justify-content: center;
    }
    
    .custom-toast.show {
        display: flex;
        animation: slideDown 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .toast-icon-danger {
        width: 30px; height: 30px;
        background: #ef4444;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
    }
    
    .toast-icon-warning {
        width: 30px; height: 30px;
        background: #f59e0b;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
    }
</style>

<div id="timeoutToast" class="custom-toast">
    <div class="toast-icon-warning"><i class="fas fa-clock"></i></div>
    <span>Sesi telah berakhir, silakan login kembali.</span>
</div>

<div id="kickedToast" class="custom-toast">
    <div class="toast-icon-danger"><i class="fas fa-shield-alt"></i></div>
    <div style="text-align:left; line-height:1.2;">
        <div style="font-size:12px; opacity:0.8;">Keamanan Akun</div>
        <span>Akun login di perangkat lain!</span>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- A. LOGIC SESI HABIS (TIMEOUT) ---
        var timeoutTime = ' . $js_timeout . ';
        setTimeout(function() {
            document.getElementById("timeoutToast").classList.add("show");
            setTimeout(function() {
                window.location.href = "../backend/logout.php?reason=timeout";
            }, 2000);
        }, timeoutTime);

        // --- B. LOGIC REALTIME CHECK (Saling Tendang) ---
        // Cek ke server setiap 3 detik
        setInterval(function() {
            fetch("../backend/cek_status_login.php")
            .then(response => response.json())
            .then(data => {
                if(data.status === "kicked") {
                    // Tampilkan Popup Kicked
                    document.getElementById("kickedToast").classList.add("show");
                    
                    // Redirect paksa setelah 2 detik
                    setTimeout(function() {
                        window.location.href = "../backend/logout.php?reason=timeout"; 
                    }, 2500);
                }
                else if(data.status === "logout") {
                    // Jika sesi server hilang tiba-tiba
                    window.location.href = "../index.php";
                }
            })
            .catch(err => console.log("Koneksi check error"));
        }, 3000); // 3000ms = 3 detik
    });
</script>
';
?>