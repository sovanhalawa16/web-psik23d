<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Application Hub - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #1e293b; 
            min-height: 100vh;
            padding-bottom: 50px;
            position: relative;
            overflow-x: hidden;
            z-index: 1;
        }

        /* --- BACKGROUND ANIMATION (ORBS MENGAMBANG) --- */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            animation: floatBlob 10s ease-in-out infinite alternate;
        }
        body::before {
            top: -10%; left: -10%; width: 350px; height: 350px; 
            background: rgba(59, 130, 246, 0.25); /* Biru Elegan */
        }
        body::after {
            bottom: -10%; right: -10%; width: 350px; height: 350px; 
            background: rgba(14, 165, 233, 0.15); /* Biru Muda */
            animation-delay: -5s; 
        }
        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 60px) scale(1.1); }
        }

        /* --- CONTAINER UTAMA --- */
        .app-container {
            max-width: 480px; 
            margin: 0 auto;
            padding: 0 20px;
        }

        /* --- PROFILE HEADER PREMIUM --- */
        .profile-section {
            text-align: center;
            padding: 50px 0 30px;
        }
        .profile-img-wrap {
            position: relative;
            display: inline-block;
            margin-bottom: 18px;
            animation: floatAvatar 6s ease-in-out infinite;
        }
        @keyframes floatAvatar {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid transparent;
            background: linear-gradient(white, white) padding-box, 
                        linear-gradient(135deg, #3b82f6, #0ea5e9, #8b5cf6) border-box; /* Gradient Ring */
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.2);
            padding: 4px;
        }
        .verified-badge {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }
        .profile-title { font-size: 1.4rem; font-weight: 900; color: #0f172a; margin-bottom: 5px; letter-spacing: -0.5px; }
        .profile-subtitle { font-size: 0.85rem; font-weight: 600; color: #64748b; }

        /* --- SECTION TITLE ELEGAN --- */
        .section-title {
            font-size: 0.8rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 35px 0 18px 5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .section-title::after {
            content: '';
            flex-grow: 1;
            height: 2px;
            background: linear-gradient(90deg, rgba(0,0,0,0.06), transparent);
            border-radius: 2px;
        }

        /* --- LINK CARD (ADVANCED GLASSMORPHISM) --- */
        .link-card {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.45); /* Kaca Transparan */
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            padding: 14px 18px;
            margin-bottom: 16px;
            text-decoration: none;
            color: #0f172a;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Efek mantul / bounce */
            box-shadow: 0 8px 32px rgba(0,0,0,0.03), inset 0 0 0 1px rgba(255,255,255,0.5);
            position: relative;
            overflow: hidden;
        }
        
        /* Efek Cahaya Lewat (Shine Sweep) */
        .link-card::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            transform: skewX(-20deg);
            transition: 0.5s;
        }
        .link-card:hover::before {
            left: 150%;
        }

        .link-card:hover, .link-card:active {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.1), inset 0 0 0 1px rgba(255,255,255,1);
            background: rgba(255, 255, 255, 0.85);
        }
        
        .link-logo {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            object-fit: cover;
            background: #ffffff;
            padding: 6px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.8);
        }
        .link-text-wrapper {
            flex-grow: 1;
            padding: 0 15px;
            text-align: left;
            min-width: 0;
        }
        .link-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 3px;
            letter-spacing: -0.3px;
            text-overflow: ellipsis;
        }
        .link-url {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 5px;
            text-overflow: ellipsis;
        }
        
        /* Tombol Aksi Bulat Estetik */
        .link-action {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #ffffff;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
            flex-shrink: 0;
            z-index: 2;
        }
        .link-card:hover .link-action {
            background: #0f172a;
            color: #ffffff;
            transform: rotate(-45deg) scale(1.1); /* Efek panah nembak ke pojok atas & membesar */
            box-shadow: 0 6px 15px rgba(15, 23, 42, 0.2);
        }
    </style>
</head>
<body>

    <div class="app-container">
        
        <div class="profile-section">
            <div class="profile-img-wrap">
                <img src="../assets/img/logo_kelas.jpg" alt="Logo" class="link-logo profile-img">
                <div class="verified-badge"><i class="fas fa-check"></i></div>
            </div>
            <h1 class="profile-title">PSIK 23 D APPLICATION</h1>
            <div class="profile-subtitle">Student Integrated Service Access Center</div>
        </div>

        <div class="section-title"><i class="fas fa-star text-warning"></i> PSIK 23 D OFFICIAL</div>

        <a href="https://psik23d.kesug.com" class="link-card">
            <img src="../assets/img/logo_kelas.jpg" alt="Logo" class="link-logo">
            <div class="link-text-wrapper">
                <div class="link-title">APP PSIK 23 D</div>
                <div class="link-url"><i class="fas fa-link"></i> psik23d.kesug.com</div>
            </div>
            <div class="link-action"><i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="https://psik23d.kesug.com/jadwal_kelas" class="link-card">
            <img src="../assets/img/logo_kelas.jpg" alt="Logo" class="link-logo">
            <div class="link-text-wrapper">
                <div class="link-title">APP SCHEDULE</div>
                <div class="link-url"><i class="fas fa-link"></i> psik23d.kesug.com/jadwal_kelas</div>
            </div>
            <div class="link-action"><i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="https://psik23d.kesug.com/rekap_absen" class="link-card">
            <img src="../assets/img/logo_kelas.jpg" alt="Logo" class="link-logo">
            <div class="link-text-wrapper">
                <div class="link-title">APP PRESENCE</div>
                <div class="link-url"><i class="fas fa-link"></i> psik23d.kesug.com/rekap_absen</div>
            </div>
            <div class="link-action"><i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="https://psik23d.kesug.com/daftar_tugas" class="link-card">
            <img src="../assets/img/logo_kelas.jpg" alt="Logo" class="link-logo">
            <div class="link-text-wrapper">
                <div class="link-title">APP TASK</div>
                <div class="link-url"><i class="fas fa-link"></i> psik23d.kesug.com/daftar_tugas</div>
            </div>
            <div class="link-action"><i class="fas fa-arrow-right"></i></div>
        </a>


        <div class="section-title mt-4"><i class="fas fa-university text-success"></i> UNIMED OFFICIAL</div>

        <a href="https://www.unimed.ac.id/" target="_blank" class="link-card">
            <img src="../assets/img/unimed.jpg" alt="Logo Unimed" class="link-logo">
            <div class="link-text-wrapper">
                <div class="link-title">Portal Unimed</div>
                <div class="link-url"><i class="fas fa-external-link-alt"></i> unimed.ac.id</div>
            </div>
            <div class="link-action"><i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="https://devakad.unimed.ac.id/" target="_blank" class="link-card">
            <img src="../assets/img/unimed.jpg" alt="Logo Unimed" class="link-logo">
            <div class="link-text-wrapper">
                <div class="link-title">Akad Unimed</div>
                <div class="link-url"><i class="fas fa-external-link-alt"></i> devakad.unimed.ac.id</div>
            </div>
            <div class="link-action"><i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="https://fmipa.unimed.in/" target="_blank" class="link-card">
            <img src="../assets/img/unimed.jpg" alt="Logo Unimed" class="link-logo">
            <div class="link-text-wrapper">
                <div class="link-title">Portal FMIPA</div>
                <div class="link-url"><i class="fas fa-external-link-alt"></i> fmipa.unimed.in</div>
            </div>
            <div class="link-action"><i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="https://billing.unimed.ac.id/v1.0/loginmhs" target="_blank" class="link-card">
            <img src="../assets/img/unimed.jpg" alt="Logo Unimed" class="link-logo">
            <div class="link-text-wrapper">
                <div class="link-title">E-Billing UNIMED</div>
                <div class="link-url"><i class="fas fa-external-link-alt"></i> billing.unimed.ac.id</div>
            </div>
            <div class="link-action"><i class="fas fa-arrow-right"></i></div>
        </a>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>