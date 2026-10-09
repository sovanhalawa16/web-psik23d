<?php 
// 1. START SESSION & KONEKSI
session_start();
date_default_timezone_set('Asia/Jakarta'); // WAJIB: Biar hitungan detik sinkron
include 'koneksi.php'; 
include 'backend/public_data.php'; 

// ==========================================
// 2. LOGIC AUTO LOGIN (REMEMBER ME) - PERBAIKAN
// ==========================================
if (isset($_COOKIE['id_user']) && isset($_COOKIE['token'])) {
    $id = $_COOKIE['id_user'];
    $cookie_token = $_COOKIE['token'];

    // Cek User
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id'");
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Validasi Token & Browser
        $current_browser = $_SERVER['HTTP_USER_AGENT'];
        $check_hash = hash('sha256', $cookie_token . $current_browser);

        // JIKA TOKEN COCOK, LANGSUNG LOGIN! (Jangan cek sedang_aktif lagi)
        if ($check_hash === $row['remember_token']) {
            
            // Login Otomatis
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['nim'] = $row['nim'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['foto'] = $row['foto'];
            $_SESSION['status'] = "login";
            $_SESSION['last_activity'] = time();
            
            // Update Aktif agar tidak dianggap logout
            mysqli_query($koneksi, "UPDATE users SET last_active = NOW() WHERE id='$row[id]'");
            
            // Redirect
            if($row['role'] == "admin"){
                header("location:frontend/admin_dashboard.php");
            } else {
                header("location:frontend/user_dashboard.php");
            }
            exit;
            
        } else {
            // Token Invalid (Hapus Cookie) - INI PENTING BIAR GAK LOOPING
            setcookie('id_user', '', time() - 3600, "/");
            setcookie('token', '', time() - 3600, "/");
        }
    }
}

// 1. QUERY LEADERS
$q_lead = mysqli_query($koneksi, "SELECT * FROM users 
          WHERE jabatan IN ('Komting', 'Sekretaris', 'Bendahara') 
          ORDER BY FIELD(jabatan, 'Komting', 'Sekretaris', 'Bendahara')");

$leaders = []; 
while($r = mysqli_fetch_assoc($q_lead)) { $leaders[] = $r; }

// 2. QUERY MEMBERS
$q_members = mysqli_query($koneksi, "SELECT * FROM users WHERE role='mahasiswa' AND jabatan NOT IN ('Komting', 'Sekretaris', 'Bendahara', 'Administrator') ORDER BY nama ASC");
$members = []; while($r = mysqli_fetch_assoc($q_members)) { $members[] = $r; }

// 3. QUERY KEGIATAN
$q_act = mysqli_query($koneksi, "SELECT * FROM activities ORDER BY id DESC");
$kegiatan_kelas = []; while($r = mysqli_fetch_assoc($q_act)) $kegiatan_kelas[] = $r;

// 4. QUERY DOSEN
if(!isset($res_dosen)) {
    $res_dosen = mysqli_query($koneksi, "SELECT * FROM dosen ORDER BY nama ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSIK 23 D</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
       
        /* ========== FIX HERO OVERFLOW MOBILE ========== */
/* ========== FIX HERO & HILANGKAN GARIS PUTIH ========== */
html, body {
    overflow-x: hidden;
    width: 100%;
    max-width: 100%;
}

.hero-container { 
    position: relative; 
    height: 100vh; 
    width: 100%;
    overflow: hidden; 
    background: #000;
    margin: 0 !important;
    padding: 0 !important;
}

.hero-slide-img {
    height: 100vh;
    width: 100%;
    object-fit: cover;
}

.hero-content {
    width: 100%;
}

/* HAPUS SEMUA MARGIN NEGATIF */
#heroCarousel,
.carousel,
.carousel-inner,
.carousel-item {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
}

/* FIX MOBILE */
@media (max-width: 768px) {
    .hero-container,
    .hero-slide-img {
        width: 100vw;
        max-width: 100vw;
    }
}
        /* --- PREMIUM GLOBAL STYLE --- */
        :root {
            --primary: #0d6efd;
            --dark: #0f172a;
            --light: #f8fafc;
            --gradient: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
        }

        body { 
            overflow-x: hidden; 
            background-color: var(--light); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #334155;
        }
        
        section { 
            padding: 100px 0; 
        }
        
        /* Typography */
        h1, h2, h3, h4, h5, h6 { color: var(--dark); font-weight: 700; letter-spacing: -0.5px; }

        /* Section Title */
        .section-title { margin-bottom: 60px; text-align: center; }
        .section-title span { color: var(--primary); font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-size: 0.85rem; display: block; margin-bottom: 10px; }
        .section-title h2 { font-size: 2.5rem; font-weight: 800; }

        /* ========== LOTTIE ANIMATION STYLES ========== */
        .lottie-container {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .lottie-container > div {
            width: 100%;
        }

        /* Responsive untuk animasi mobile */
        @media (max-width: 768px) {
            .lottie-container {
                padding: 10px;
                margin-bottom: 20px;
            }
        }

        /* Dark mode support */
        body.dark-mode .lottie-container {
            filter: brightness(0.9) contrast(1.1);
        }

        /* --- NAVBAR GLASSMORPHISM & FIX CLICKABLE --- */
        .navbar { 
            transition: 0.4s; 
            backdrop-filter: blur(10px); 
            padding: 15px 0; 
            background: rgba(0,0,0,0.8); 
            z-index: 9999;
        }

        .navbar.scrolled { 
            background: #ffffff !important; 
            box-shadow: 0 4px 30px rgba(0,0,0,0.08); 
            padding: 10px 0; 
        }

        .navbar.scrolled .nav-link { color: #000000 !important; font-weight: 600; }
        .navbar.scrolled .nav-link:hover { color: var(--primary) !important; }
        
        /* PERBAIKAN: Tombol Login saat di-scroll (mode light) */
        .navbar.scrolled .btn-login {
            color: #000000 !important;
            border-color: #000000 !important;
        }
        .navbar.scrolled .btn-login:hover {
            background-color: #000000 !important;
            color: #ffffff !important;
        }
        
        /* Typing Text Style */
        .nav-typing-text { font-family: monospace; font-weight: 700; color: #ffffff; font-size: 1.2rem; }
        .nav-cursor { display: inline-block; width: 3px; background-color: #ffffff; animation: blink 1s infinite; margin-left: 2px; vertical-align: middle; height: 1.2rem; }
        .navbar.scrolled .nav-typing-text { color: #000000 !important; }
        .navbar.scrolled .nav-cursor { background-color: #000000 !important; }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0; } 100% { opacity: 1; } }

        /* Toggler Color Fix */
        .navbar.scrolled .navbar-toggler-icon { filter: invert(1) grayscale(100%) brightness(0); }
        .navbar.scrolled .navbar-toggler { border-color: rgba(0,0,0,0.1); }

        /* --- MOBILE SIDEBAR NEAT STYLE --- */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: white;
                padding: 12px 15px;
                border-radius: 12px;
                margin-top: 10px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            }
            .navbar-dark .navbar-nav .nav-link {
                color: #333 !important;
                padding: 8px 0;
                border-bottom: 1px solid #eee;
                font-size: 0.9rem;
            }
            .navbar.scrolled .navbar-collapse {
                box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            }
            /* Dark mode adjustment for mobile sidebar */
            body.dark-mode .navbar-collapse {
                background: #1e293b;
                border: 1px solid #334155;
                padding: 12px 15px;
            }
            body.dark-mode .navbar-dark .navbar-nav .nav-link {
                color: #fff !important;
                border-bottom: 1px solid #334155;
                padding: 8px 0;
            }
        }

        /* Dropdown Menu - Hover Support */
        .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }

        .dropdown-menu { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            padding: 10px; 
            margin-top: 15px; 
            transition: all 0.3s ease;
        }

        /* Desktop-only hover */
        @media (min-width: 992px) {
            .dropdown:hover .dropdown-menu {
                display: block;
                margin-top: 0;
            }
            
            .dropdown-menu {
                animation: fadeIn 0.2s ease;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        }
        
        .dropdown-item { border-radius: 8px; padding: 8px 15px; font-weight: 500; font-size: 0.9rem; transition: 0.2s; }
        .dropdown-item:hover { background: #eff6ff; color: var(--primary); transform: translateX(5px); }

        /* --- BUTTONS & CONTROLS --- */
        .theme-toggle-btn {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            width: 40px; 
            height: 40px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            cursor: pointer; 
            transition: 0.3s;
        }
        .theme-toggle-btn:hover { background: white; color: var(--primary); }
        
        .navbar.scrolled .theme-toggle-btn { border-color: #0d6efd; color: #0d6efd; }
        .navbar.scrolled .theme-toggle-btn:hover { background: #0d6efd; color: white; }
        
        /* Mobile Control Wrapper */
        .mobile-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- HERO SECTION --- */
        .hero-container { position: relative; height: 100vh; width: 100%; overflow: hidden; background: #000; }
        .hero-slide-img { height: 100vh; width: 100%; object-fit: cover; filter: brightness(0.35); transform: scale(1); transition: transform 8s ease; }
        .carousel-item.active .hero-slide-img { transform: scale(1.1); } 
        
        .hero-content { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; 
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; color: white; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); 
        }
        .hero-title { font-size: 5rem; font-weight: 900; color: white; margin-bottom: 20px; line-height: 1.1; }
        .hero-subtitle { 
            font-size: 1.5rem; font-weight: 300; letter-spacing: 5px; opacity: 1; 
            border-bottom: 1px solid rgba(255,255,255,0.5); padding-bottom: 20px; display: inline-block;
            color: #ffffff !important; 
        }

        /* --- CARDS --- */
        .profile-card, .member-card { 
            background: #fff; border-radius: 24px; border: 1px solid rgba(0,0,0,0.03);
            box-shadow: 0 10px 40px rgba(0,0,0,0.05); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        }
        .profile-card:hover, .member-card:hover { transform: translateY(-10px); box-shadow: 0 20px 50px rgba(0,0,0,0.1); }
        .foto-profile-big { width: 140px; height: 140px; object-fit: cover; border-radius: 50%; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 15px; }
        .foto-member { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #f1f5f9; margin-bottom: 10px; }

        /* --- SWIPER --- */
        .swiper { padding-bottom: 60px !important; }
        .swiper-pagination-bullet { width: 12px; height: 12px; background: var(--primary); opacity: 0.3; transition: 0.3s; }
        .swiper-pagination-bullet-active { opacity: 1; width: 30px; border-radius: 10px; }
        
        #galeri-kegiatan { background: #111; color: white; }
        #galeri-kegiatan .swiper-slide { 
            width: 600px; 
            height: 350px; 
            border-radius: 20px; 
            overflow: hidden; 
            background: #000; 
            position: relative;
        }
        
        /* PERBAIKAN: Gambar dokumentasi - object-fit: cover untuk menyesuaikan rasio gambar */
        #galeri-kegiatan .swiper-slide img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; /* Diubah dari 'contain' ke 'cover' */
            display: block; 
            transition: transform 0.3s ease;
        }
        
        #galeri-kegiatan .swiper-slide:hover img {
            transform: scale(1.05);
        }
        
        #struktur .swiper-slide, #dosen .swiper-slide { width: 300px; }
        #anggota .swiper-slide { width: 250px; }

        /* --- FOOTER & MODAL STYLES --- */
        footer { background: #0f172a; color: #94a3b8; padding: 80px 0 30px; }
        .footer-brand { color: white; font-size: 1.8rem; font-weight: 800; text-decoration: none; display: block; margin-bottom: 20px; }
        .footer-link { color: #94a3b8; text-decoration: none; transition: 0.3s; display: block; margin-bottom: 12px; }
        .footer-link:hover { color: white; transform: translateX(5px); }
        .social-btn { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.1); display: inline-flex; align-items: center; justify-content: center; color: white; text-decoration: none; margin-right: 10px; transition: 0.3s; }
        .social-btn:hover { background: var(--primary); transform: translateY(-3px); }

        .modal-detail-content { border-radius: 24px; overflow: hidden; border: none; }
        .modal-detail-header { height: 130px; background: var(--gradient); position: relative; border: 0; }
        .detail-foto-wrapper { position: absolute; top: 60px; left: 50%; transform: translateX(-50%); width: 100%; text-align: center; z-index: 10; }
        .detail-foto { width: 140px; height: 140px; border-radius: 50%; border: 5px solid white; box-shadow: 0 10px 30px rgba(0,0,0,0.15); background: white; object-fit: cover; }
        .modal-body-custom { padding-top: 90px; padding-bottom: 40px; padding-left: 30px; padding-right: 30px; text-align: center; }
        .skill-badge { background: #f1f5f9; color: #475569; font-size: 0.85rem; padding: 6px 14px; border-radius: 50px; margin: 3px; display: inline-block; font-weight: 600; }

        /* ========== LOGIN MODAL STYLES (SUPER COMPACT) ========== */
        #loginModal .modal-dialog {
            max-width: 340px;
            margin: 1rem auto;
        }

        #loginModal .modal-content {
            border-radius: 16px;
            overflow: hidden;
            border: none;
            background-color: #ffffff;
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.15);
            width: 100%;
        }

        #loginModal .modal-header {
            background: #ffffff;
            padding: 0.8rem 1rem 0.1rem 1rem;
            border-bottom: none;
            position: relative;
            z-index: 2;
        }

        #loginModal .modal-header .btn-close {
            background-color: #f1f5f9;
            border-radius: 50%;
            padding: 5px;
            opacity: 1;
            transition: 0.3s;
            font-size: 0.7rem;
            width: 24px;
            height: 24px;
        }
        #loginModal .modal-header .btn-close:hover { 
            background-color: #e2e8f0; 
            transform: rotate(90deg); 
        }

        #loginModal .modal-body { 
            padding: 0 1.2rem 1.2rem 1.2rem; 
            background: #ffffff; 
        }

        /* Input Login Premium (Super Compact) */
        .login-input-group {
            position: relative;
            margin-bottom: 12px;
        }
        .login-form-container .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-size: 0.85rem;
            background: #f8fafc;
            color: #334155;
            transition: 0.3s;
            height: 38px;
        }
        .login-form-container .form-control:focus {
            background: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
        }
        .login-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Password toggle */
        .password-toggle {
            cursor: pointer;
            color: #94a3b8;
            transition: 0.3s;
            z-index: 10;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: none;
            padding: 0 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        .password-toggle:hover { 
            background-color: #e2e8f0;
            color: var(--primary); 
        }

        .btn-login-premium {
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
            border: none;
            padding: 8px;
            border-radius: 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 12px rgba(13, 110, 253, 0.15);
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 6px;
            font-size: 0.8rem;
            height: 38px;
        }
        .btn-login-premium:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(13, 110, 253, 0.2);
        }

        /* Modal content styling */
        #loginModal .text-center {
            margin-bottom: 1.2rem;
        }
        #loginModal .text-center .d-inline-block {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        #loginModal .text-center .fa-user-lock {
            font-size: 1.2rem;
        }
        #loginModal .text-center h3 {
            font-size: 1.2rem;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 4px;
            color: #1e293b;
        }
        #loginModal .text-center p {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0;
        }

        /* Alert message */
        #loginModal .alert {
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.75rem;
        }
        #loginModal .alert .fas {
            font-size: 0.8rem;
        }

        /* Forgot password text */
        #loginModal .text-center small {
            font-size: 0.7rem;
        }

        /* Dark Mode Adjustments */
        body.dark-mode #loginModal .modal-content {
            background-color: #1e293b;
        }
        body.dark-mode #loginModal .modal-header {
            background: #1e293b;
        }
        body.dark-mode #loginModal .modal-body {
            background: #1e293b;
        }
        body.dark-mode #loginModal .text-center h3 {
            color: #ffffff;
        }
        body.dark-mode #loginModal .text-center p {
            color: #cbd5e1;
        }
        body.dark-mode #loginModal .login-form-container .form-control {
            background: #334155;
            border-color: #475569;
            color: #ffffff;
        }
        body.dark-mode #loginModal .login-label {
            color: #cbd5e1;
        }
        body.dark-mode #loginModal .password-toggle {
            background-color: #334155;
            border-color: #475569;
            color: #cbd5e1;
        }
        body.dark-mode #loginModal .password-toggle:hover {
            background-color: #475569;
        }
        body.dark-mode #loginModal .modal-header .btn-close {
            background-color: #334155;
            color: #cbd5e1;
        }
        body.dark-mode #loginModal .modal-header .btn-close:hover {
            background-color: #475569;
        }
        body.dark-mode #loginModal .text-center .d-inline-block {
            background-color: #334155 !important;
        }

        /* Responsive Modal */
        @media (max-width: 768px) {
            #loginModal .modal-dialog {
                max-width: 300px;
                margin: 0.5rem auto;
            }
            
            #loginModal .modal-header {
                padding: 0.6rem 0.8rem 0.1rem 0.8rem;
            }
            
            #loginModal .modal-body { 
                padding: 0 1rem 1rem 1rem; 
            }
            
            .login-form-container .form-control {
                padding: 7px 10px;
                font-size: 0.8rem;
                height: 36px;
            }
            
            .btn-login-premium {
                padding: 7px;
                font-size: 0.75rem;
                height: 36px;
            }
            
            #loginModal .text-center h3 {
                font-size: 1.1rem;
            }
            
            #loginModal .text-center .d-inline-block {
                width: 42px;
                height: 42px;
            }
            
            #loginModal .text-center .fa-user-lock {
                font-size: 1rem;
            }
        }

        /* Untuk layar sangat kecil (mobile landscape & kecil) */
        @media (max-width: 576px) {
            #loginModal .modal-dialog {
                max-width: 280px;
                margin: 0.3rem auto;
            }
            
            #loginModal .modal-body { 
                padding: 0 0.8rem 0.8rem 0.8rem; 
            }
            
            .login-form-container .form-control {
                padding: 6px 10px;
                height: 34px;
            }
            
            .btn-login-premium {
                padding: 6px;
                height: 34px;
            }
            
            .login-input-group {
                margin-bottom: 10px;
            }
            
            #loginModal .text-center {
                margin-bottom: 1rem;
            }
            
            #loginModal .text-center .d-inline-block {
                width: 40px;
                height: 40px;
            }
            
            #loginModal .text-center .fa-user-lock {
                font-size: 0.9rem;
            }
        }

        /* Untuk layar ekstra kecil */
        @media (max-width: 360px) {
            #loginModal .modal-dialog {
                max-width: 260px;
                margin: 0.2rem auto;
            }
            
            #loginModal .modal-body { 
                padding: 0 0.7rem 0.7rem 0.7rem; 
            }
            
            #loginModal .text-center h3 {
                font-size: 1rem;
            }
            
            #loginModal .text-center p {
                font-size: 0.7rem;
            }
        }

        /* TOMBOL BACK TO TOP */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 20px;
            z-index: 9999;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .back-to-top:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); }
        .back-to-top.show { display: flex; }
        
        /* MODE GELAP LENGKAP */
        body.dark-mode { background-color: #0f172a; color: #ffffff; }
        
        /* Typography Dark Mode */
        body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
        body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 { color: #ffffff; }
        
        body.dark-mode .section-title span { color: #60a5fa; }
        body.dark-mode .section-title h2 { color: #ffffff; }
        
        body.dark-mode p, body.dark-mode .lead, body.dark-mode .text-muted, 
        body.dark-mode small, body.dark-mode .modal-body-custom, body.dark-mode .card p { color: #cbd5e1 !important; }
        
        body.dark-mode .text-dark { color: #ffffff !important; }
        
        /* Cards Dark Mode */
        body.dark-mode .profile-card, body.dark-mode .member-card { background: #1e293b; color: #ffffff; border: 1px solid #334155; }
        body.dark-mode .profile-card h4, body.dark-mode .profile-card h5, body.dark-mode .profile-card h6, body.dark-mode .member-card h6 { color: #ffffff; }
        body.dark-mode .skill-badge { background: #334155; color: #cbd5e1; }
        
        /* Background Sections Dark Mode */
        body.dark-mode #profil, body.dark-mode #anggota { background: #1e293b !important; }
        body.dark-mode #struktur, body.dark-mode #dosen { background: #0f172a !important; }
        body.dark-mode .bg-white { background-color: #1e293b !important; }
        body.dark-mode .bg-light { background-color: #0f172a !important; }
        body.dark-mode .badge.bg-light { background-color: #334155 !important; color: #cbd5e1 !important; border-color: #475569 !important; }
        
        /* Modal Detail Dark Mode */
        body.dark-mode .modal-content { background-color: #1e293b; color: #ffffff; }
        body.dark-mode .modal-body-custom h4, body.dark-mode .modal-body-custom .fw-bold.text-dark { color: #ffffff !important; }
        body.dark-mode .modal-body-custom .bg-light { background-color: #334155 !important; }
        body.dark-mode .modal-body-custom .bg-white { background-color: #475569 !important; }
        body.dark-mode .modal-body-custom .text-dark { color: #ffffff !important; }
        body.dark-mode .modal-body-custom .text-muted { color: #94a3b8 !important; }
        
        /* PERBAIKAN: Tombol Login saat di-scroll (mode dark) */
        body.dark-mode .navbar.scrolled .btn-login {
            color: #ffffff !important;
            border-color: #ffffff !important;
        }
        body.dark-mode .navbar.scrolled .btn-login:hover {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
        
        /* Login Modal Dark Mode */
        body.dark-mode #loginModal .modal-content {
            background-color: #1e293b;
        }
        body.dark-mode #loginModal .modal-header {
            background: #1e293b;
        }
        body.dark-mode #loginModal .modal-body {
            background: #1e293b;
        }
        body.dark-mode #loginModal .text-center h3 {
            color: #ffffff;
        }
        body.dark-mode #loginModal .text-center p {
            color: #cbd5e1;
        }
        body.dark-mode #loginModal .login-form-container .form-control {
            background: #334155;
            border-color: #475569;
            color: #ffffff;
        }
        body.dark-mode #loginModal .login-label {
            color: #cbd5e1;
        }
        
        /* Footer Dark Mode */
        body.dark-mode footer { background: #0a0f1c !important; }
        body.dark-mode .footer-link { color: #94a3b8; }
        body.dark-mode .footer-link:hover { color: #ffffff; }
        body.dark-mode .text-white-50 { color: #94a3b8 !important; }
        
        /* Navbar Scroll Dark Mode */
        body.dark-mode .navbar.scrolled { background: #1e293b !important; }
        body.dark-mode .navbar.scrolled .nav-link { color: #ffffff !important; }
        body.dark-mode .navbar.scrolled .nav-typing-text { color: #ffffff !important; }
        body.dark-mode .navbar.scrolled .nav-cursor { background-color: #ffffff !important; }
        body.dark-mode .theme-toggle-btn:hover { background: #334155; color: white; }

        /* Responsive adjustments */
        @media (max-width: 768px) { 
            .hero-title { 
                font-size: 3rem; 
            }
            .hero-subtitle {
                font-size: 1.1rem;
                letter-spacing: 3px;
            }
            #galeri-kegiatan .swiper-slide { 
                width: 90%; 
                height: 250px; 
            }
            .back-to-top { 
                bottom: 20px; 
                right: 15px; 
                width: 45px; 
                height: 45px; 
                font-size: 1.1rem; 
            }
            .section-title h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 0.9rem;
                letter-spacing: 2px;
                padding-bottom: 15px;
            }
        }
    </style>
</head>
<body>

    <button class="back-to-top" id="backToTop" title="Kembali ke Atas">
        <i class="fas fa-arrow-up"></i>
    </button>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center me-auto" href="#">
                <span class="nav-typing-text"></span><span class="nav-cursor"></span>
            </a>

            <div class="mobile-controls d-lg-none">
                <button class="theme-toggle-btn" id="themeToggleMobile" title="Ganti Mode">
                    <i class="fas fa-moon"></i>
                </button>

                <button class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 40px; height: 40px;"
                        data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fas fa-sign-in-alt"></i>
                </button>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-lg-3">
                    <li class="nav-item"><a class="nav-link text-white" href="#beranda">Home</a></li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Profil</a>
                        <ul class="dropdown-menu shadow-lg">
                            <li><a class="dropdown-item" href="#profil">About</a></li>
                            <li><a class="dropdown-item" href="#struktur">Leadership</a></li>
                            <li><a class="dropdown-item" href="#anggota">The Squad</a></li>
                            <li><a class="dropdown-item" href="#dosen">Lecturers</a></li>
                        </ul>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="frontend/berita.php">News</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#kontak">Contact</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="frontend/features.php">Feature</a></li>
                    
                    <li class="nav-item ms-lg-2">
                        <a href="frontend/jadwal_kelas.php" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark">
                            <i class="fas fa-calendar-alt me-1"></i> Schedule
                        </a>
                    </li>
                    
                    <li class="nav-item d-none d-lg-block">
                        <button class="btn btn-outline-light rounded-pill px-4 fw-bold btn-login" 
                                data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt me-1"></i> Sign in
                        </button>
                    </li>

                    <li class="nav-item d-none d-lg-block">
                        <button class="theme-toggle-btn" id="themeToggleDesktop" title="Ganti Mode">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="beranda" class="hero-container p-0">
        <?php if(count($galeri_publik) > 0): ?>
            <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach($galeri_publik as $index => $g): ?>
                        <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>" data-bs-interval="4000">
                            <img src="assets/img/galeri/<?= $g['foto'] ?>" class="hero-slide-img">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="hero-static-bg"></div>
        <?php endif; ?>
        <div class="hero-content" data-aos="zoom-out" data-aos-duration="1200">
            <span class="badge bg-white text-dark px-3 py-2 rounded-pill mb-3 fw-bold text-uppercase ls-2">Welcome to Our Class</span>
            <h1 class="hero-title">PSIK 23 D</h1>
            <h2 class="hero-subtitle">State University of Medan</h2>
        </div>
    </section>

    <section id="profil" class="bg-white">
        <div class="container">
            <div class="row align-items-center">
                <!-- Desktop: Animasi di kiri, teks di kanan -->
                <div class="col-lg-6 d-none d-lg-block" data-aos="fade-right">
                    <div class="lottie-container text-center">
                        <!-- Container untuk animasi desktop -->
                        <div id="lottie-desktop" style="width: 100%; min-height: 400px;"></div>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="section-title mb-4">
                        <span>About us</span>
                    </div>
                    <p class="lead text-muted lh-lg">
                        Hello!
We are students of the Computer Science Study Program, Class of 2023, currently pursuing our education at Universitas Negeri Medan (UNIMED). We are a community of students who are actively engaged in developing knowledge and skills in the fields of information technology, programming, and computer systems. 
                    </p>
                </div>
                
                <!-- Mobile: Animasi di atas (kecil), teks di bawah -->
                <div class="col-12 d-lg-none mt-4" data-aos="fade-up">
                    <div class="lottie-container text-center">
                        <!-- Container untuk animasi mobile -->
                        <div id="lottie-mobile" style="width: 100%; min-height: 250px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="struktur" class="bg-light">
        <div class="container">
            <div class="section-title" data-aos="fade-up"><span>Leadership</span></div>
            <div class="swiper effectSwiper" data-aos="fade-up" data-aos-delay="200">
                <div class="swiper-wrapper">
                    <?php if(count($leaders) > 0): foreach($leaders as $boss): ?>
                    <div class="swiper-slide">
                        <div class="card profile-card text-center p-4">
                            <?php $foto = (!empty($boss['foto']) && $boss['foto']!='default.png') ? 'assets/img/mahasiswa/'.$boss['foto'] : 'https://ui-avatars.com/api/?name='.urlencode($boss['nama']).'&size=150&bold=true'; ?>
                            <div class="d-flex justify-content-center"><img src="<?= $foto ?>" class="foto-profile-big"></div>
                            <h4 class="fw-bold mb-1 text-dark"><?= $boss['nama'] ?></h4>
                            <p class="text-muted small mb-3"><?= $boss['nim'] ?></p>
                            <div class="mb-3">
                                <?php 
                                    $badge = 'bg-secondary'; $icon = 'fa-user';
                                    if($boss['jabatan'] == 'Komting') { $badge = 'bg-danger'; $icon = 'fa-crown'; } 
                                    elseif($boss['jabatan'] == 'Sekretaris') { $badge = 'bg-warning text-dark'; $icon = 'fa-pen-fancy'; } 
                                    elseif($boss['jabatan'] == 'Bendahara') { $badge = 'bg-success'; $icon = 'fa-wallet'; }
                                ?>
                                <span class="badge <?= $badge ?> rounded-pill px-3 py-2"><i class="fas <?= $icon ?> me-2"></i><?= strtoupper($boss['jabatan']) ?></span>
                            </div>
                            <a href="frontend/user_profile.php?id=<?= $boss['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2">
                                Lihat Profil
                            </a>
                        </div>
                    </div>
                    <?php endforeach; else: ?><div class="col-12 text-center text-muted">No Data</div><?php endif; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- SESUDAH: (BENAR - grafik di luar swiper, layout 2 kolom) -->
<section id="anggota" class="bg-white">
    <div class="container">
        <div class="section-title" data-aos="fade-up"><span>The Squad</span></div>
        
        <!-- SWIPER UNTUK ANGGOTA -->
        <div class="swiper effectSwiperMembers" data-aos="zoom-in" data-aos-delay="200">
            <div class="swiper-wrapper">
                <?php if(count($members) > 0): foreach($members as $m): ?>
                <div class="swiper-slide">
                    <div class="card member-card text-center p-4">
                        <?php $foto_mhs = (!empty($m['foto']) && $m['foto']!='default.png') ? 'assets/img/mahasiswa/'.$m['foto'] : 'https://ui-avatars.com/api/?name='.urlencode($m['nama']).'&size=128&bold=true&background=random'; ?>
                        <div class="d-flex justify-content-center"><img src="<?= $foto_mhs ?>" class="foto-member"></div>
                        <h6 class="fw-bold mb-1 text-dark text-truncate w-100"><?= $m['nama'] ?></h6>
                        <span class="badge bg-light text-dark border small mt-1 mb-3"><?= $m['nim'] ?></span>
                        <a href="frontend/user_profile.php?id=<?= $m['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            Lihat Profil
                        </a>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="col-12 text-center text-muted"><p>No Data</p></div>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        
        <!-- ROW UNTUK GRAFIK MAHASISWA - DI BAWAH SWIPER -->
        <div class="row justify-content-center mt-5" data-aos="fade-up">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <h6 class="text-center fw-bold mb-3 text-secondary text-uppercase ls-1">Komposisi Gender Kelas</h6>
                    <div style="height: 250px; position: relative;">
                        <canvas id="chartMahasiswa"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- SESUDAH: (BENAR - grafik di luar swiper) -->
<section id="dosen" class="bg-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up"><span>Lecturers</span></div>
        
        <!-- SWIPER UNTUK DOSEN -->
        <div class="swiper effectSwiper" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php while($d = mysqli_fetch_assoc($res_dosen)): ?>
                <div class="swiper-slide">
                    <div class="card profile-card text-center p-4 h-100">
                        <?php $foto = !empty($d['foto']) ? $d['foto'] : 'default_dosen.png'; ?>
                        <div class="d-flex justify-content-center mb-3"><img src="assets/img/dosen/<?= $foto ?>" class="foto-profile-big" style="border: 3px solid #0d6efd; padding: 3px;"></div>
                        <h5 class="fw-bold mb-1 text-dark"><?= $d['nama'] ?></h5>
                        <?php if(!empty($d['nidn'])): ?><small class="text-muted d-block mb-2"><i class="fas fa-id-card me-1 text-secondary"></i> NIDN: <?= $d['nidn'] ?></small><?php endif; ?>
                        <?php if(!empty($d['jabatan'])): ?><div class="mb-3"><span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3"><?= $d['jabatan'] ?></span></div><?php endif; ?>
                        <div class="d-flex justify-content-center gap-2 mt-auto">
                            <?php if(!empty($d['link_sinta']) && $d['link_sinta'] != '-'): ?><a href="<?= $d['link_sinta'] ?>" target="_blank" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark shadow-sm">Sinta</a><?php endif; ?>
                            <?php if(!empty($d['link_scholar']) && $d['link_scholar'] != '-'): ?><a href="<?= $d['link_scholar'] ?>" target="_blank" class="btn btn-info btn-sm rounded-pill px-3 fw-bold text-white shadow-sm">Scholar</a><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        
        <!-- ROW UNTUK GRAFIK DOSEN - DI BAWAH SWIPER -->
        <div class="row justify-content-center mt-5 g-4" data-aos="fade-up">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                    <h6 class="text-center fw-bold mb-3 text-secondary text-uppercase ls-1">Gender Dosen</h6>
                    <div style="height: 200px; position: relative;">
                        <canvas id="chartDosenGender"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                    <h6 class="text-center fw-bold mb-3 text-secondary text-uppercase ls-1">Struktur Jabatan</h6>
                    <div style="height: 200px; position: relative;">
                        <canvas id="chartDosenJabatan"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <section id="galeri-kegiatan" class="text-white">
        <div class="container text-center">
            <div class="section-title mb-5" data-aos="fade-up"><span class="text-white-50">Our Memories</span></div>
            <?php if(count($kegiatan_kelas) > 0): ?>
                <div class="swiper gallerySwiper" data-aos="zoom-in"><div class="swiper-wrapper"><?php foreach($kegiatan_kelas as $k): ?><div class="swiper-slide"><img src="assets/img/kegiatan/<?= $k['foto'] ?>" alt="Dokumentasi Kegiatan" loading="lazy"></div><?php endforeach; ?></div><div class="swiper-pagination"></div></div>
            <?php else: ?><p class="text-white-50">No Data</p><?php endif; ?>
        </div>
    </section>

   <section id="laporan" class="bg-gradient py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="text-center mb-5 animate-on-scroll">
                    <span class="badge bg-success bg-opacity-10 text-success px-4 py-2 rounded-pill mb-3">
                        <i class="fas fa-bullhorn me-2"></i>Suara Mahasiswa
                    </span>
                    <h2 class="fw-bold display-6 mb-3">Sampaikan Aspirasimu</h2>
                    <p class="text-muted lead">Setiap suara berharga untuk kemajuan PSIK 23 D</p>
                </div>

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden animate-on-scroll">
                    <div class="row g-0">
                        <!-- Left Side - Info Panel -->
                        <div class="col-md-5 bg-gradient-success text-white p-4 p-md-5 d-flex flex-column justify-content-center position-relative overflow-hidden">
                            <div class="position-relative z-2 text-center py-4">
                                <!-- Icon -->
                                <div class="icon-container mb-4">
                                    <div class="icon-circle">
                                        <i class="fas fa-comments fa-3x"></i>
                                    </div>
                                </div>
                                
                                <h3 class="fw-bold mb-4">Mengapa Melapor?</h3>
                                
                                <ul class="list-unstyled text-start mb-5">
                                    <li class="mb-3 d-flex align-items-start">
                                        <div class="me-3 mt-1">
                                            <i class="fas fa-check-circle text-light"></i>
                                        </div>
                                        <span>24/7 </span>
                                    </li>
                                    <li class="mb-3 d-flex align-items-start">
                                        <div class="me-3 mt-1">
                                            <i class="fas fa-check-circle text-light"></i>
                                        </div>
                                        <span>Fast respon ga kayak HTS an kamu awokawok</span>
                                    </li>
                                    <li class="d-flex align-items-start">
                                        <div class="me-3 mt-1">
                                            <i class="fas fa-check-circle text-light"></i>
                                        </div>
                                        <span>Privasi 100% Aman</span>
                                    </li>
                                </ul>
                                
                                <!-- Stats -->
                                <div class="stats-container mt-4">
                                    <div class="d-flex justify-content-center gap-4">
                                        <div class="stat-item text-center">
                                            <div class="stat-number fw-bold display-6 mb-1">24/7</div>
                                            <div class="stat-label text-white-50">Open Service</div>
                                        </div>
                                        <div class="stat-item text-center">
                                            <div class="stat-number fw-bold display-6 mb-1">100%</div>
                                            <div class="stat-label text-white-50">Response Rate</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side - Form -->
                        <div class="col-md-7 p-4 p-md-5 bg-white">
                            <!-- Form Header -->
                            <div class="form-header mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-wrapper bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="fas fa-paper-plane fa-lg text-success"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold mb-1 text-dark">Kirim Laporan Anda</h4>
                                        <p class="text-muted small mb-0">Isi form berikut untuk mengirim pesan</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Form -->
                            <form id="formLaporan" onsubmit="kirimLaporan(event)">
                                <div class="row g-3">
                                    <!-- Name Input -->
                                    <div class="col-md-6">
                                        <div class="form-floating floating-label-group">
                                            <input type="text" class="form-control" id="lap_nama" placeholder="Nama Lengkap" required>
                                            <label for="lap_nama" class="text-muted">
                                                Nama Lengkap
                                            </label>
                                            <div class="focus-border"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Email Input -->
                                    <div class="col-md-6">
                                        <div class="form-floating floating-label-group">
                                            <input type="email" class="form-control" id="lap_email" placeholder="Email Aktif" required>
                                            <label for="lap_email" class="text-muted">
                                                Email Aktif
                                            </label>
                                            <div class="focus-border"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Report Type -->
                                    <div class="col-12">
                                        <div class="form-floating floating-label-group">
                                            <select class="form-select custom-select" id="lap_jenis" required>
                                                <option value="" disabled selected>Pilih Jenis Laporan</option>
                                                <option value="Pertanyaan">Pertanyaan Akademik</option>
                                                <option value="Saran">Saran & Masukan</option>
                                                <option value="Pengaduan">Pengaduan Masalah</option>
                                                <option value="Lainnya">Lainnya</option>
                                            </select>
                                            <label for="lap_jenis" class="text-muted">
                                                Jenis Laporan
                                            </label>
                                            <div class="focus-border"></div>
                                            <div class="select-arrow">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="col-12">
                                        <div class="form-floating floating-label-group">
                                            <textarea class="form-control" id="lap_deskripsi" placeholder="Detail Pesan..." style="height: 150px" required></textarea>
                                            <label for="lap_deskripsi" class="text-muted">
                                                Detail Pesan...
                                            </label>
                                            <div class="focus-border"></div>
                                        </div>
                                        <div class="form-text text-end mt-2">
                                            <span id="charCount">0</span>/500 karakter
                                        </div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="col-12 mt-4">
                                        <button type="submit" id="btnKirimLap" class="btn btn-success btn-lg w-100 py-3 rounded-3 fw-bold shadow-sm btn-hover-animate">
                                            <span class="btn-content">
                                                <i class="fas fa-paper-plane me-2"></i> Kirim
                                            </span>
                                            <span class="btn-loader d-none">
                                                <span class="spinner-border spinner-border-sm me-2"></span>
                                                Mengirim...
                                            </span>
                                        </button>
                                    </div>
                                    
                                    <!-- Check Response Link -->
                                    <div class="col-12 mt-4 text-center">
                                        <a href="frontend/cek_laporan.php" class="text-decoration-none d-inline-flex align-items-center text-success hover-link">
                                            <div class="link-icon bg-success bg-opacity-10 p-2 rounded-circle me-2">
                                            </div>
                                            <span class="fw-medium">Sudah melapor? Cek balasan di sini</span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Custom Styles */
.bg-gradient {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    position: relative;
}

/* Icon Styling */
.icon-container {
    position: relative;
    display: inline-block;
}

.icon-circle {
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
    }
}

/* Form Styling */
.floating-label-group {
    position: relative;
}

.form-control, .form-select, .custom-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem 1rem;
    transition: all 0.3s ease;
    background: #fff !important;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

/* Custom dropdown arrow */
.select-arrow {
    position: absolute;
    right: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
    transition: all 0.3s ease;
}

.floating-label-group:focus-within .select-arrow {
    color: #10b981;
    transform: translateY(-50%) rotate(180deg);
}

.form-control:focus, .form-select:focus, .custom-select:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
    transform: translateY(-2px);
}

.form-floating > label {
    color: #6c757d;
}

.form-control:not(:placeholder-shown) ~ label,
.form-control:focus ~ label,
.form-select:not(:placeholder-shown) ~ label,
.form-select:focus ~ label,
.custom-select:not([value=""]) ~ label,
.custom-select:focus ~ label {
    color: #10b981;
    transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
}

/* Ensure label stays above when option is selected */
.custom-select option[value=""][disabled] {
    display: none;
}

.custom-select:invalid {
    color: #6c757d;
}

.custom-select:valid {
    color: #212529;
}

.focus-border {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #10b981;
    transition: width 0.3s ease;
}

.form-control:focus ~ .focus-border,
.form-select:focus ~ .focus-border,
.custom-select:focus ~ .focus-border {
    width: 100%;
}

/* Button Animation */
.btn-hover-animate {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.btn-hover-animate:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3) !important;
}

.btn-hover-animate:active {
    transform: translateY(-1px);
}

.btn-hover-animate::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%);
    transform-origin: 50% 50%;
}

.btn-hover-animate:focus:not(:active)::after {
    animation: ripple 1s ease-out;
}

@keyframes ripple {
    0% {
        transform: scale(0, 0);
        opacity: 0.5;
    }
    100% {
        transform: scale(20, 20);
        opacity: 0;
    }
}

/* Link Hover Effect */
.hover-link {
    transition: all 0.3s ease;
}

.hover-link:hover {
    transform: translateX(5px);
}

.hover-link:hover .fa-arrow-right {
    transform: translateX(5px);
}

/* Stats Styling */
.stat-item {
    transition: transform 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
}

.stat-number {
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

/* Scroll Animation */
.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease;
}

.animate-on-scroll.animated {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .card {
        margin: 0 -15px;
        border-radius: 0 !important;
    }
    
    .icon-circle {
        width: 80px;
        height: 80px;
    }
    
    .icon-circle i {
        font-size: 2rem !important;
    }
    
    .select-arrow {
        right: 1rem;
    }
}
</style>

<script>
// Character counter for description
document.getElementById('lap_deskripsi').addEventListener('input', function(e) {
    const charCount = document.getElementById('charCount');
    const maxLength = 500;
    const currentLength = e.target.value.length;
    
    charCount.textContent = currentLength;
    
    if (currentLength > maxLength * 0.9) {
        charCount.style.color = '#dc3545';
    } else if (currentLength > maxLength * 0.75) {
        charCount.style.color = '#ffc107';
    } else {
        charCount.style.color = '#6c757d';
    }
});

// Update dropdown arrow on change
document.getElementById('lap_jenis').addEventListener('change', function() {
    const arrow = this.parentElement.querySelector('.select-arrow i');
    if (this.value) {
        arrow.style.transform = 'rotate(180deg)';
        setTimeout(() => {
            arrow.style.transform = 'rotate(0deg)';
        }, 300);
    }
});

// Form submission animation
function kirimLaporan(event) {
    event.preventDefault();
    
    const btn = document.getElementById('btnKirimLap');
    const btnContent = btn.querySelector('.btn-content');
    const btnLoader = btn.querySelector('.btn-loader');
    
    // Show loading state
    btnContent.classList.add('d-none');
    btnLoader.classList.remove('d-none');
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        // Reset button state
        btnContent.classList.remove('d-none');
        btnLoader.classList.add('d-none');
        btn.disabled = false;
        
        // Show success message
        alert('Laporan berhasil dikirim! Kami akan membalas melalui email.');
        document.getElementById('formLaporan').reset();
        document.getElementById('charCount').textContent = '0';
        
        // Reset dropdown arrow
        const select = document.getElementById('lap_jenis');
        select.value = '';
        const arrow = select.parentElement.querySelector('.select-arrow i');
        arrow.style.transform = 'rotate(0deg)';
    }, 2000);
}

// Scroll animation
function checkScroll() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementTop < windowHeight - 100) {
            element.classList.add('animated');
        }
    });
}

// Initial check and scroll listener
window.addEventListener('load', checkScroll);
window.addEventListener('scroll', checkScroll);
</script>
    <footer id="kontak" class="bg-white pt-5 pb-4 border-top" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="container">
        <div class="row justify-content-between g-5">
            
            <div class="col-lg-7">
                <h3 class="fw-bold text-dark mb-1">PSIK 23 D</h3>
                <p class="text-muted fst-italic mb-4 small">"Computer Science 23 D"</p>
                
                <div class="d-flex mb-4">
                    <div class="me-3 mt-1">
                        <i class="fas fa-map-marker-alt text-success fs-5"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-secondary lh-sm">
                            <strong>Computer Science Study Program</strong><br>
                            JP47+JPR, Kenangan Baru, Kec. Percut Sei Tuan, Kabupaten Deli Serdang, Sumatera Utara 20371
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7 mb-4 mb-md-0">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-inline-block border-success border-2">Tautan Penting</h6>
                        <div class="row">
                            <div class="col-6">
                                <ul class="list-unstyled small">
                                    <li class="mb-2"><i class="fas fa-circle text-success me-2" style="font-size: 6px; vertical-align: middle;"></i> <a href="https://www.unimed.ac.id/" target="_blank" class="text-decoration-none text-secondary hover-success">Website UNIMED</a></li>
                                    <li class="mb-2"><i class="fas fa-circle text-success me-2" style="font-size: 6px; vertical-align: middle;"></i> <a href="https://fmipa.unimed.ac.id/" target="_blank" class="text-decoration-none text-secondary hover-success">Website FMIPA</a></li>
                                    <li class="mb-2"><i class="fas fa-circle text-success me-2" style="font-size: 6px; vertical-align: middle;"></i> <a href="https://bak.unimed.ac.id/" target="_blank" class="text-decoration-none text-secondary hover-success">Biro Akademik</a></li>
                                    <li class="mb-2"><i class="fas fa-circle text-success me-2" style="font-size: 6px; vertical-align: middle;"></i> <a href="#" class="text-decoration-none text-secondary hover-success">Perpustakaan</a></li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <ul class="list-unstyled small">
                                    <li class="mb-2"><i class="fas fa-circle text-success me-2" style="font-size: 6px; vertical-align: middle;"></i> <a href="https://sipda.unimed.ac.id/" target="_blank" class="text-decoration-none text-secondary hover-success">SIPDA UNIMED</a></li>
                                    <li class="mb-2"><i class="fas fa-circle text-success me-2" style="font-size: 6px; vertical-align: middle;"></i> <a href="#" class="text-decoration-none text-secondary hover-success">Portal Mahasiswa</a></li>
                                    <li class="mb-2"><i class="fas fa-circle text-success me-2" style="font-size: 6px; vertical-align: middle;"></i> <a href="#" class="text-decoration-none text-secondary hover-success">Jurnal Ilmiah</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-inline-block border-success border-2">Contact Class</h6>
                        <ul class="list-unstyled small text-secondary">
                            <li class="mb-3 d-flex align-items-center">
                                <div class="bg-light p-2 rounded-circle me-2 text-dark"><i class="fas fa-envelope"></i></div>
                                <div>
                                    <span class="d-block fw-bold text-dark">Email</span>
                                    psik23d@gmail.com
                                </div>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <div class="bg-light p-2 rounded-circle me-2 text-dark"><i class="fas fa-phone"></i></div>
                                <div>
                                    <span class="d-block fw-bold text-dark">Contact</span>
                                    (+62) 813 9707 9456 
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border shadow-sm rounded-4 p-1">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3 border-start border-4 border-success ps-2">Lokasi Kampus</h6>
                        
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden mb-3 border bg-light">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.4484868892464!2d98.7142641!3d3.6066002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x303131e66b0cead1%3A0x287c8513f41bc90f!2sGedung%2077%20Ilmu%20Komputer%20-%20UNIMED!5e1!3m2!1sid!2sid!4v1767165385527!5m2!1sid!2sid" 
                                style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <i class="fas fa-map-pin text-danger mt-1 me-2"></i>
                                <div>
                                    <small class="fw-bold d-block text-dark">Alamat:</small>
                                    <small class="text-muted">Gedung 77 ILMU KOMPUTER UNIMED</small>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center small mb-3 ps-4 ms-1">
                                <div class="text-warning me-2">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                                <span class="text-muted fw-semibold">4.8 (2,450 ulasan)</span>
                            </div>

                            <div class="d-grid gap-2">
    <a href="https://maps.app.goo.gl/RiWaCYE7S9AZXUGXA" target="_blank" 
       class="btn btn-primary btn-sm fw-bold rounded-3 d-flex align-items-center justify-content-center">
        <i class="fas fa-map-marked-alt me-2"></i> Buka di Google Maps
    </a>
    <a href="https://wa.me/6281397079456/" target="_blank" 
       class="btn btn-outline-primary btn-sm fw-bold rounded-3 d-flex align-items-center justify-content-center">
        <i class="fab fa-whatsapp me-2"></i> Dapatkan Arahan via WhatsApp
    </a>
</div>
                        </div>

                        <div class="bg-light border rounded-3 p-2 text-center">
                            <small class="text-secondary fw-semibold">
                                <i class="far fa-clock me-1"></i> Jam Kuliah: Senin - Jumat, 08:00 - 18:00 WIB
                            </small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <hr class="my-4 border-secondary opacity-25">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <small class="text-muted">&copy; 2026 <strong>PSIK 23 D</strong>. All Rights Reserved.</small>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <small class="text-muted">Developed by <span class="text-success fw-bold">Sovantri Putra Paskah Halawa</span></small>
            </div>
        </div>
    </div>
</footer>

<style>
    .hover-success:hover { color: #198754 !important; text-decoration: underline !important; }
    .text-justify { text-align: justify; }
</style>

    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                
                <div class="modal-header">
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="d-inline-block p-2 rounded-circle bg-light mb-2">
                            <i class="fas fa-user-lock fa-1x text-primary"></i>
                        </div>
                        <h3 class="fw-bold text-dark fs-5 mb-1">Welcome Back!</h3>
                        <p class="text-muted small">Please login to access your account</p>
                    </div>

                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger d-flex align-items-center small border-0 shadow-sm mb-3 rounded-2" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div><?= $_SESSION['error']; ?></div>
                        </div>
                        <script>
                            document.addEventListener("DOMContentLoaded", function(){
                                var myModal = new bootstrap.Modal(document.getElementById('loginModal'));
                                myModal.show();
                            });
                        </script>
                        <?php unset($_SESSION['error']); // Hapus error setelah tampil ?>
                    <?php endif; ?>

                    <form action="backend/auth_login.php" method="POST" class="login-form-container">
                        
                        <div class="login-input-group">
                            <label class="login-label">Username</label>
                            <input type="text" name="nim" class="form-control" placeholder="Input Username" required autocomplete="off">
                        </div>

                        <div class="login-input-group">
                            <label class="login-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="inputPassword" class="form-control border-end-0" placeholder="Input Password" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <span class="input-group-text bg-light border-start-0 password-toggle" id="togglePassword" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                <label class="form-check-label small text-muted" for="rememberMe" style="font-size: 0.8rem;">
                                    Ingat Saya
                                </label>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-login-premium text-white">
                                Login <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">Forget Password? Contact Admin.</small>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- SCRIPT LOTTIE DARI CDN YANG BERBEDA -->
    <script src="https://cdn.jsdelivr.net/npm/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    
    <script>
        AOS.init({ once: true, offset: 100, duration: 800, easing: 'ease-out-cubic' });

        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) { document.querySelector('.navbar').classList.add('scrolled'); } 
            else { document.querySelector('.navbar').classList.remove('scrolled'); }
        });

        const typeTextSpan = document.querySelector(".nav-typing-text");
        const texts = ["Welcome!","PSIK 23 D"];
        const typingDelay = 100;
        const erasingDelay = 50;
        const newTextDelay = 500;
        let textArrayIndex = 0;
        let charIndex = 0;

        function type() {
            if (charIndex < texts[textArrayIndex].length) {
                typeTextSpan.textContent += texts[textArrayIndex].charAt(charIndex);
                charIndex++;
                setTimeout(type, typingDelay);
            } else {
                setTimeout(erase, newTextDelay);
            }
        }

        function erase() {
            if (charIndex > 0) {
                typeTextSpan.textContent = texts[textArrayIndex].substring(0, charIndex - 1);
                charIndex--;
                setTimeout(erase, erasingDelay);
            } else {
                textArrayIndex++;
                if (textArrayIndex >= texts.length) textArrayIndex = 0;
                setTimeout(type, typingDelay + 1100);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            if (texts.length) setTimeout(type, 1000);
        });

        // LOGIC AUTO-OPEN LOGIN MODAL
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('pesan') && urlParams.get('pesan') === 'gagal') {
                var myModalEl = document.getElementById('loginModal');
                if(myModalEl) {
                    var modal = new bootstrap.Modal(myModalEl);
                    modal.show();
                    window.history.replaceState(null, null, window.location.pathname);
                }
            }
        });

        // PERBAIKAN: Swiper untuk gambar dokumentasi
        new Swiper(".gallerySwiper", { 
            effect: "coverflow", 
            grabCursor: true, 
            centeredSlides: true, 
            slidesPerView: "auto", 
            coverflowEffect: { 
                rotate: 20, 
                stretch: 0, 
                depth: 100, 
                modifier: 1, 
                slideShadows: true 
            }, 
            pagination: { 
                el: ".swiper-pagination", 
                clickable: true 
            }, 
            autoplay: { 
                delay: 3000, 
                disableOnInteraction: false 
            }, 
            loop: true,
            // Menambahkan breakpoints untuk responsif
            breakpoints: {
                320: {
                    coverflowEffect: {
                        rotate: 15,
                        depth: 80
                    }
                },
                768: {
                    coverflowEffect: {
                        rotate: 20,
                        depth: 100
                    }
                }
            }
        });
        
        new Swiper(".effectSwiper", { effect: "coverflow", grabCursor: true, centeredSlides: true, slidesPerView: "auto", coverflowEffect: { rotate: 30, stretch: 0, depth: 100, modifier: 1, slideShadows: true }, pagination: { el: ".swiper-pagination", clickable: true }, autoplay: { delay: 4000, disableOnInteraction: false }, loop: true });
        new Swiper(".effectSwiperMembers", { effect: "coverflow", grabCursor: true, centeredSlides: true, slidesPerView: "auto", coverflowEffect: { rotate: 20, stretch: 0, depth: 80, modifier: 1, slideShadows: false }, pagination: { el: ".swiper-pagination", clickable: true }, autoplay: { delay: 2500, disableOnInteraction: false }, loop: true });


        // TOGGLE PASSWORD VISIBILITY
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#inputPassword');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // toggle the eye / eye slash icon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        // ========== DARK MODE FUNCTIONALITY ==========
        const themeToggleDesktop = document.getElementById('themeToggleDesktop');
        const themeToggleMobile = document.getElementById('themeToggleMobile');
        const body = document.body;
        
        function updateIcon(isDark) {
            const iconClass = isDark ? 'fa-sun' : 'fa-moon';
            if(themeToggleDesktop) themeToggleDesktop.innerHTML = `<i class="fas ${iconClass}"></i>`;
            if(themeToggleMobile) themeToggleMobile.innerHTML = `<i class="fas ${iconClass}"></i>`;
        }

        // Cek preferensi user
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            updateIcon(true);
        }
        
        function toggleTheme() {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                updateIcon(true);
            } else {
                localStorage.setItem('darkMode', 'disabled');
                updateIcon(false);
            }
        }

        if(themeToggleDesktop) themeToggleDesktop.addEventListener('click', toggleTheme);
        if(themeToggleMobile) themeToggleMobile.addEventListener('click', toggleTheme);
        
        // ========== BACK TO TOP FUNCTIONALITY ==========
        const backToTop = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
		
        // ========== DROPDOWN HOVER FUNCTIONALITY ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Untuk dropdown di desktop (hover)
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                // Hanya di desktop
                if (window.innerWidth >= 992) {
                    dropdown.addEventListener('mouseenter', function() {
                        const menu = this.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.display = 'block';
                            setTimeout(() => {
                                menu.style.opacity = '1';
                                menu.style.transform = 'translateY(0)';
                            }, 10);
                        }
                    });
                    
                    dropdown.addEventListener('mouseleave', function() {
                        const menu = this.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.opacity = '0';
                            menu.style.transform = 'translateY(10px)';
                            setTimeout(() => {
                                if (!this.matches(':hover') && !menu.matches(':hover')) {
                                    menu.style.display = 'none';
                                }
                            }, 200);
                        }
                    });
                    
                    // Juga untuk dropdown menu itu sendiri
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.addEventListener('mouseenter', function() {
                            this.style.display = 'block';
                            this.style.opacity = '1';
                            this.style.transform = 'translateY(0)';
                        });
                        
                        menu.addEventListener('mouseleave', function() {
                            this.style.opacity = '0';
                            this.style.transform = 'translateY(10px)';
                            setTimeout(() => {
                                if (!this.matches(':hover') && !dropdown.matches(':hover')) {
                                    this.style.display = 'none';
                                }
                            }, 200);
                        });
                    }
                }
            });
            
            // Update saat resize window
            window.addEventListener('resize', function() {
                dropdowns.forEach(dropdown => {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu && window.innerWidth >= 992) {
                        menu.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                    }
                });
            });
        });
        
        // ========== LOTTIE ANIMATION - CARA SIMPLE DAN PASTI BERHASIL ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Tunggu sebentar agar DOM benar-benar siap
            setTimeout(function() {
                // GANTI DENGAN PATH FILE JSON YANG BENAR
                // Jika file lokal tidak berhasil, gunakan URL publik untuk testing
                const jsonPath = 'assets/lottie/coding-develio.json';
                // const jsonPath = 'https://assets1.lottiefiles.com/packages/lf20_iv4dsx3q.json'; // Untuk testing
                
                // Cek apakah ada element untuk desktop
                const desktopEl = document.getElementById('lottie-desktop');
                if (desktopEl) {
                    desktopEl.innerHTML = `
                        <dotlottie-player
                            src="${jsonPath}"
                            background="transparent"
                            speed="1"
                            style="width: 100%; height: 400px;"
                            loop
                            autoplay>
                        </dotlottie-player>
                    `;
                }
                
                // Cek apakah ada element untuk mobile
                const mobileEl = document.getElementById('lottie-mobile');
                if (mobileEl) {
                    mobileEl.innerHTML = `
                        <dotlottie-player
                            src="${jsonPath}"
                            background="transparent"
                            speed="1"
                            style="width: 100%; height: 250px;"
                            loop
                            autoplay>
                        </dotlottie-player>
                    `;
                }
                
                // Fallback jika file tidak ditemukan
                setTimeout(function() {
                    if (desktopEl && desktopEl.innerHTML.includes('dotlottie-player') && 
                        desktopEl.querySelector('dotlottie-player') && 
                        desktopEl.querySelector('dotlottie-player').shadowRoot === null) {
                        desktopEl.innerHTML = `
                            <div class="text-center p-4 border rounded" style="height: 400px; display: flex; align-items: center; justify-content: center;">
                                <div>
                                    <i class="fas fa-code fa-3x text-primary mb-3"></i>
                                    <p class="text-muted">Animasi coding sedang dimuat...</p>
                                    <small>Jika animasi tidak muncul, pastikan file ada di:<br>${jsonPath}</small>
                                </div>
                            </div>
                        `;
                    }
                    
                    if (mobileEl && mobileEl.innerHTML.includes('dotlottie-player') && 
                        mobileEl.querySelector('dotlottie-player') && 
                        mobileEl.querySelector('dotlottie-player').shadowRoot === null) {
                        mobileEl.innerHTML = `
                            <div class="text-center p-4 border rounded" style="height: 250px; display: flex; align-items: center; justify-content: center;">
                                <div>
                                    <i class="fas fa-code fa-2x text-primary mb-2"></i>
                                    <p class="text-muted small">Animasi coding</p>
                                </div>
                            </div>
                        `;
                    }
                }, 3000);
                
            }, 1000);
        });
    </script>
    <script>
    async function kirimLaporan(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btnKirimLap');
        const originalText = btn.innerHTML;
        
        // 1. Ambil Data
        const formData = new FormData();
        formData.append('nama', document.getElementById('lap_nama').value);
        formData.append('email', document.getElementById('lap_email').value);
        formData.append('jenis', document.getElementById('lap_jenis').value);
        formData.append('deskripsi', document.getElementById('lap_deskripsi').value);

        // 2. Loading State
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sedang Mengirim...';

        try {
            // Pastikan path backend/kirim_laporan.php SUDAH ADA sesuai instruksi sebelumnya
            const response = await fetch('backend/kirim_laporan.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if(result.status === 'success') {
                // Tampilkan Alert Sukses (Bisa diganti SweetAlert kalau mau lebih keren)
                alert("✅ " + result.message); 
                document.getElementById('formLaporan').reset();
            } else {
                alert("❌ Error: " + result.message);
            }
        } catch (err) {
            console.error(err);
            alert("⚠️ Terjadi kesalahan koneksi! Cek internet kamu.");
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
    </script>
<style>
    /* Layar Hitam di Belakang (Blocker) */
    .rating-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.95); /* Gelap banget */
        backdrop-filter: blur(10px); /* Efek Blur estetik */
        z-index: 9998;
        display: none; /* Default sembunyi */
        opacity: 0;
        transition: opacity 0.4s ease;
        cursor: pointer;
    }
    
    .rating-overlay.show {
        display: block;
        opacity: 1;
    }

    /* Kotak Popup di Tengah */
    .rating-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        width: 90%;
        max-width: 380px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        padding: 25px;
        z-index: 9999;
        display: none;
        opacity: 0;
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        text-align: center;
        overflow: hidden;
    }
    
    .rating-popup.show {
        display: block;
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
        animation: popupAppear 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes popupAppear {
        0% {
            transform: translate(-50%, -50%) scale(0.7);
            opacity: 0;
        }
        70% {
            transform: translate(-50%, -50%) scale(1.05);
        }
        100% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
    }

    /* Header popup */
    .rating-header {
        margin-bottom: 20px;
    }

    .rating-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: white;
        font-size: 24px;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    .rating-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .rating-subtitle {
        font-size: 0.85rem;
        color: #64748b;
        line-height: 1.4;
        max-width: 320px;
        margin: 0 auto;
    }

    /* Star Styling */
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 8px;
        margin: 20px 0 15px;
    }
    .star-rating input { 
        display: none; 
    }
    
    .star-rating label {
        font-size: 2.2rem;
        color: #e2e8f0;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .star-rating label:hover {
        transform: scale(1.15);
    }
    
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #fbbf24;
    }
    
    .star-rating input:checked + label {
        transform: scale(1.1);
        animation: starBounce 0.5s;
    }

    @keyframes starBounce {
        0%, 100% { transform: scale(1.1); }
        50% { transform: scale(1.3); }
    }

    /* Teks Reaksi */
    .reaction-text {
        font-weight: 700;
        font-size: 1rem;
        min-height: 28px;
        color: #64748b;
        margin: 10px 0 15px;
        transition: all 0.3s ease;
        opacity: 0.8;
    }

    .reaction-text.active {
        opacity: 1;
    }

    /* Form Detail */
    .rating-details {
        display: none;
        animation: slideIn 0.5s ease;
        text-align: left;
        margin-top: 20px;
    }
    
    @keyframes slideIn { 
        from { 
            opacity: 0; 
            transform: translateY(15px); 
        } 
        to { 
            opacity: 1; 
            transform: translateY(0); 
        } 
    }

    /* Form Styling */
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
        margin-left: 2px;
    }
    
    .form-input {
        width: 100%;
        padding: 12px 16px;
        background-color: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.95rem;
        color: #334155;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #667eea;
        background-color: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-textarea {
        min-height: 80px;
        resize: vertical;
        font-family: inherit;
    }

    /* Tombol */
    .submit-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(102, 126, 234, 0.4);
    }
    
    .submit-btn:active {
        transform: translateY(0);
    }
    
    .submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
    }

    /* Notifikasi Klik Luar */
    .escape-notification {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(-100px);
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 14px 24px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        z-index: 10000;
        box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        opacity: 0;
        border: 2px solid rgba(255, 255, 255, 0.3);
        max-width: 90%;
        text-align: center;
        backdrop-filter: blur(10px);
    }
    
    .escape-notification.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    
    .escape-notification i {
        font-size: 1.2rem;
        animation: shake 0.8s ease infinite alternate;
    }
    
    @keyframes shake {
        0% { transform: translateX(0) rotate(0deg); }
        25% { transform: translateX(-2px) rotate(-5deg); }
        75% { transform: translateX(2px) rotate(5deg); }
        100% { transform: translateX(0) rotate(0deg); }
    }

    /* Animasi untuk komentar setelah rating dipilih */
    .details-visible {
        display: block;
    }

    /* Responsif untuk mobile */
    @media (max-width: 640px) {
        .rating-popup {
            padding: 22px;
            border-radius: 18px;
            max-width: 90%;
        }
        
        .rating-icon {
            width: 55px;
            height: 55px;
            font-size: 22px;
        }
        
        .rating-title {
            font-size: 1.3rem;
        }
        
        .rating-subtitle {
            font-size: 0.8rem;
            padding: 0 5px;
        }
        
        .star-rating label {
            font-size: 1.9rem;
        }
        
        .form-input {
            padding: 10px 14px;
            font-size: 0.9rem;
        }
        
        .submit-btn {
            padding: 13px;
            font-size: 0.95rem;
        }
        
        .escape-notification {
            padding: 12px 18px;
            font-size: 0.85rem;
            top: 15px;
            max-width: 85%;
        }
        
        .escape-notification i {
            font-size: 1rem;
        }
    }

    /* Untuk layar sangat kecil */
    @media (max-width: 380px) {
        .rating-popup {
            padding: 18px;
        }
        
        .star-rating label {
            font-size: 1.7rem;
        }
        
        .escape-notification {
            padding: 10px 16px;
            font-size: 0.8rem;
            top: 10px;
        }
    }
</style>

<div id="ratingOverlay" class="rating-overlay"></div>

<!-- NOTIFIKASI KLIK LUAR -->
<div id="escapeNotification" class="escape-notification">
    <i class="fas fa-hand-pointer"></i>
    <span>Eitss.. mau kemana? tolong ratingnya lah wkwk</span>
</div>

<div id="psikRatingPopup" class="rating-popup">
    <div class="rating-header">
        <div class="rating-icon">
            <i class="fas fa-star"></i>
        </div>
        <h3 class="rating-title">Mohon Penilaiannya 🙏</h3>
        <p class="rating-subtitle">
            Kami butuh masukanmu agar website PSIK 23 D makin berkembang.
            <span style="color: #ef4444; font-weight: 600;">*Wajib diisi sebelum lanjut</span>
        </p>
    </div>

    <div class="star-rating">
        <input type="radio" name="star" id="star5" value="5" onclick="setRating(5)"><label for="star5"><i class="fas fa-star"></i></label>
        <input type="radio" name="star" id="star4" value="4" onclick="setRating(4)"><label for="star4"><i class="fas fa-star"></i></label>
        <input type="radio" name="star" id="star3" value="3" onclick="setRating(3)"><label for="star3"><i class="fas fa-star"></i></label>
        <input type="radio" name="star" id="star2" value="2" onclick="setRating(2)"><label for="star2"><i class="fas fa-star"></i></label>
        <input type="radio" name="star" id="star1" value="1" onclick="setRating(1)"><label for="star1"><i class="fas fa-star"></i></label>
    </div>

    <div id="reactionText" class="reaction-text">Pilih bintang untuk melanjutkan</div>

    <div id="ratingFormDetails" class="rating-details">
        <div class="form-group">
            <label class="form-label">Nama Kamu</label>
            <input type="text" id="ratingNama" class="form-input" placeholder="Masukkan nama..." required>
        </div>
        <div class="form-group">
            <label class="form-label">Masukan / Kritik (opsional)</label>
            <textarea id="ratingKomentar" class="form-input form-textarea" rows="3" placeholder="Apa yang perlu kami perbaiki?"></textarea>
        </div>
        <button onclick="kirimRating()" id="btnKirimRating" class="submit-btn">
            <span>KIRIM & LANJUT</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

<script>
    // Logic tetap sama seperti sebelumnya
    let selectedStar = 0;
    const popup = document.getElementById('psikRatingPopup');
    const overlay = document.getElementById('ratingOverlay');
    const reaction = document.getElementById('reactionText');
    const details = document.getElementById('ratingFormDetails');
    const escapeNotification = document.getElementById('escapeNotification');
    let escapeClickCount = 0;
    let notificationTimeout;

    // --- LOGIC TRIGGER (Scroll ke Footer) ---
    window.addEventListener('scroll', () => {
        // Cek LocalStorage: Kalau udah pernah rate, JANGAN muncul lagi
        if(localStorage.getItem('psik_rated_force') === 'true') return;

        const footer = document.querySelector('footer');
        const position = footer.getBoundingClientRect();

        // Jika footer terlihat, KUNCI LAYAR
        if(position.top < window.innerHeight && position.bottom >= 0) {
            showModalForce();
        }
    });

    function showModalForce() {
        overlay.classList.add('show');
        popup.classList.add('show');
        // Matikan Scroll pada Body biar gak bisa kabur ke atas
        document.body.style.overflow = 'hidden';
        
        // Reset hitungan klik escape
        escapeClickCount = 0;
        
        // Tambah event listener untuk klik di overlay
        overlay.addEventListener('click', handleOverlayClick);
    }

    function closeModalForce() {
        overlay.classList.remove('show');
        popup.classList.remove('show');
        // Hidupkan Scroll lagi
        document.body.style.overflow = 'auto';
        
        // Hapus event listener
        overlay.removeEventListener('click', handleOverlayClick);
        
        // Sembunyikan notifikasi jika masih aktif
        hideEscapeNotification();
    }

    // Fungsi handle klik di luar popup
    function handleOverlayClick(event) {
        // Cek apakah klik di luar popup
        if (!popup.contains(event.target)) {
            escapeClickCount++;
            
            // Tampilkan notifikasi
            showEscapeNotification();
            
            // Beri efek shake pada popup
            popup.style.animation = 'none';
            setTimeout(() => {
                popup.style.animation = 'popupAppear 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            }, 10);
            
            // Jika sudah 3x klik, beri pesan berbeda
            if (escapeClickCount >= 3) {
                escapeNotification.innerHTML = '<i class="fas fa-lock"></i><span>Gabisa kabur bro, rating dulu baru boleh lanjut wkwk</span>';
            }
        }
    }

    // Fungsi tampilkan notifikasi escape
    function showEscapeNotification() {
        // Hapus timeout sebelumnya jika ada
        if (notificationTimeout) {
            clearTimeout(notificationTimeout);
        }
        
        // Tampilkan notifikasi
        escapeNotification.classList.add('show');
        
        // Sembunyikan setelah 3 detik
        notificationTimeout = setTimeout(() => {
            hideEscapeNotification();
        }, 3000);
    }

    // Fungsi sembunyikan notifikasi escape
    function hideEscapeNotification() {
        escapeNotification.classList.remove('show');
    }

    // --- LOGIC TEXT BINTANG ---
    function setRating(val) {
        selectedStar = val;
        details.style.display = 'block';
        details.classList.add('details-visible');
        reaction.classList.add('active');
        
        if(val === 1) { 
            reaction.innerHTML = "😭 waduh, sakit kali hatiku wak"; 
            reaction.style.color = "#ef4444"; 
        } 
        else if(val === 2) { 
            reaction.innerHTML = "😔 serius aku di giniin?"; 
            reaction.style.color = "#f97316"; 
        } 
        else if(val === 3) { 
            reaction.innerHTML = "😐 hmm, boleh lah ya..."; 
            reaction.style.color = "#eab308"; 
        } 
        else if(val === 4) { 
            reaction.innerHTML = "😁 mantap! makasih bro"; 
            reaction.style.color = "#3b82f6"; 
        } 
        else if(val === 5) { 
            reaction.innerHTML = "🔥 KELAS KING! GACOR BTUL WAK!"; 
            reaction.style.color = "#10b981"; 
        }
    }

    // --- KIRIM DATA ---
    async function kirimRating() {
        const nama = document.getElementById('ratingNama').value;
        const komen = document.getElementById('ratingKomentar').value;
        const btn = document.getElementById('btnKirimRating');

        if(!nama) { 
            alert("Nama wajib diisi bro, biar kami tau siapa yang kasih nilai!"); 
            document.getElementById('ratingNama').focus();
            return; 
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData();
        formData.append('nama', nama);
        formData.append('bintang', selectedStar);
        formData.append('komentar', komen);

        try {
            const res = await fetch('backend/kirim_rating.php', { method: 'POST', body: formData });
            const data = await res.json();

            if(data.status === 'success') {
                // Simpan "Kunci" di browser user
                localStorage.setItem('psik_rated_force', 'true'); 
                
                alert("✅ " + data.message);
                closeModalForce(); // Baru boleh tutup setelah sukses
            } else {
                alert("❌ " + data.message);
            }
        } catch(e) {
            console.error(e);
            alert("Gagal koneksi! Coba lagi.");
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>KIRIM & LANJUT</span><i class="fas fa-arrow-right"></i>';
        }
    }
</script>
<style>
    .live-toast {
        position: fixed;
        /* FIX POSISI DESKTOP: Turunkan jadi 100px biar di bawah Navbar */
        top: 100px; 
        left: -100%; /* Sembunyi */
        
        /* Desain Glassmorphism */
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        
        /* Bentuk & Bayangan */
        border-radius: 50px; /* Bentuk Pill / Kapsul */
        padding: 10px 20px 10px 10px; /* Padding kanan lebih luas */
        box-shadow: 0 10px 40px rgba(0,0,0,0.08), 0 2px 10px rgba(0,0,0,0.02);
        
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 9900;
        
        width: auto;
        max-width: 350px; /* Batasi lebar di desktop */
        
        transition: left 0.7s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease;
        opacity: 0;
        pointer-events: none;
    }

    .live-toast.active {
        left: 20px; /* Muncul */
        opacity: 1;
        pointer-events: auto;
    }

    /* Icon Bulat di Kiri */
    .toast-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); /* Hijau Gradien */
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
    }

    /* Konten Teks */
    .toast-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.2;
    }

    .toast-top-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 2px;
    }

    .toast-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
    }

    .toast-stars {
        display: flex;
        gap: 1px;
        font-size: 0.65rem;
        color: #f59e0b; /* Emas */
    }

    .toast-comment {
        font-size: 0.75rem;
        color: #64748b;
        white-space: nowrap;      /* Satu baris aja */
        overflow: hidden;         /* Potong kalo kepanjangan */
        text-overflow: ellipsis;  /* Kasih titik tiga (...) */
        max-width: 200px;
    }

    /* Logic Hilang saat Scroll */
    .live-toast.hide-by-scroll {
        transform: translateY(-80px);
        opacity: 0 !important;
    }

    /* MOBILE RESPONSIVE */
    @media (max-width: 576px) {
        .live-toast {
            border-radius: 16px; /* Sedikit kotak di HP */
            padding: 10px;
            /* FIX POSISI MOBILE: Sesuaikan kalau navbar HP lebih kecil */
            top: 80px; 
            max-width: 90%; 
        }
        .live-toast.active {
            left: 10px; /* Mepet kiri */
            right: auto;
        }
        .toast-comment {
            max-width: 180px; /* Lebih pendek di HP */
        }
    }
</style>

<div id="liveToast" class="live-toast">
    <div class="toast-icon">
        <i class="fas fa-quote-left" style="font-size: 0.9rem;"></i>
    </div>
    <div class="toast-content">
        <div class="toast-top-row">
            <span class="toast-name" id="toastName">User</span>
            <div class="toast-stars" id="toastStars">
                </div>
        </div>
        <div class="toast-comment" id="toastComment">Memuat ulasan...</div>
    </div>
</div>

<script>
    const liveToast = document.getElementById('liveToast');
    const toastName = document.getElementById('toastName');
    const toastStars = document.getElementById('toastStars');
    const toastComment = document.getElementById('toastComment');
    const stopSection = document.getElementById('profil'); 

    // --- VARIABEL KONTROL ---
    let ratingQueue = []; 
    let currentIndex = 0; 
    let isPaused = false; 

    // --- 1. LOGIC SCROLL ---
    window.addEventListener('scroll', () => {
        if (!stopSection) return;
        const scrollY = window.scrollY;
        // Hilang 100px sebelum masuk section profil
        const stopPosition = stopSection.offsetTop - 100; 

        if (scrollY > stopPosition) {
            liveToast.classList.add('hide-by-scroll');
            isPaused = true;
        } else {
            liveToast.classList.remove('hide-by-scroll');
            isPaused = false;
        }
    });

    // --- 2. FETCH DATA (INIT) ---
    async function initRatings() {
        try {
            const response = await fetch('backend/get_public_rating.php');
            const result = await response.json();

            if (result.status === 'success' && result.data.length > 0) {
                ratingQueue = result.data; 
                startLooping(); 
            }
        } catch (error) {
            console.error("Gagal load rating:", error);
        }
    }

    // --- 3. LOOPING ---
    function startLooping() {
        showNextRating();
        
        // Loop setiap 5 Detik
        setInterval(() => {
            // Cek pause dan pastikan elemen tidak sedang disembunyikan scroll
            if (!isPaused && ratingQueue.length > 0 && !liveToast.classList.contains('hide-by-scroll')) {
                liveToast.classList.remove('active');
                
                setTimeout(() => {
                    currentIndex++;
                    if (currentIndex >= ratingQueue.length) {
                        currentIndex = 0; 
                    }
                    showNextRating();
                }, 600); 
            }
        }, 5000); 
    }

    function showNextRating() {
        const data = ratingQueue[currentIndex];
        
        toastName.textContent = data.nama;
        toastComment.textContent = `"${data.komentar}"`; 

        // Render Bintang
        let starsHTML = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= data.bintang) {
                starsHTML += '<i class="fas fa-star"></i>';
            } else {
                starsHTML += '<i class="fas fa-star text-muted opacity-25"></i>';
            }
        }
        toastStars.innerHTML = starsHTML;

        liveToast.classList.add('active');
    }

    setTimeout(initRatings, 2500);
</script>
    
    <?php include 'popup.php'; ?>
    <?php include 'user_grafik.php'; ?>
</body>
</html>