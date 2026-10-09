<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

$id = $_SESSION['id'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

// Path foto profil (Cek default)
$foto_profil = (!empty($data['foto']) && $data['foto'] != 'default.png') 
                ? "../assets/img/mahasiswa/" . $data['foto'] 
                : "https://ui-avatars.com/api/?name=" . urlencode($data['nama']) . "&background=0d6efd&color=fff&size=200";

// --- LOGIC COVER PHOTO ---
$cover_photo = (!empty($data['cover_photo']) && $data['cover_photo'] != 'default_cover.jpg')
                ? "../assets/img/cover/" . $data['cover_photo']
                : "https://source.unsplash.com/800x200/?technology,blue";
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Lengkap</title>
    
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-hover: #f1f5f9;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border-color: #e2e8f0;
            --primary-color: #3b82f6;
            --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --sidebar-width: 280px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="dark"] {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-hover: #334155;
            --text-main: #f1f5f9;
            --text-sub: #94a3b8;
            --border-color: #334155;
            --primary-color: #60a5fa;
            --primary-gradient: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            --success-color: #34d399;
            --warning-color: #fbbf24;
            --danger-color: #f87171;
            --info-color: #22d3ee;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Sidebar Styles */
        .sidebar-wrapper {
            width: var(--sidebar-width);
            height: 100dvh;
            background: var(--bg-card);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand {
            padding: 1.5rem;
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-link {
            color: var(--text-sub);
            padding: 0.875rem 1.25rem;
            margin: 0.25rem 1rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .nav-link:hover {
            background: var(--bg-hover);
            color: var(--text-main);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: white !important;
            box-shadow: var(--shadow-md);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Mobile Header - DIPERBAIKI UNTUK PADDING RAPAT */
        .mobile-header {
            display: none;
            background: var(--bg-card);
            padding: 0.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1040;
            box-shadow: var(--shadow-md);
            height: 56px;
            align-items: center;
            justify-content: space-between;
        }

        .mobile-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 100%;
        }

        .menu-toggle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .menu-toggle:hover {
            background: var(--bg-hover);
        }

        .mobile-brand {
            font-weight: 700;
            font-size: 1.2rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            flex-grow: 1;
            margin: 0 1rem;
        }

        /* Profile Card - DIPERBAIKI UNTUK MOBILE RAPAT */
        .profile-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease-out;
            margin-top: 0; /* DIUBAH: hapus margin top */
        }

        /* Profile Sidebar - DIPERBAIKI UNTUK GRID RAPAT */
        .profile-sidebar { 
            background: var(--primary-gradient);
            color: white; 
            text-align: center;
            padding: 1.5rem 1rem; /* DIUBAH: dari 2.5rem 1.5rem menjadi 1.5rem 1rem */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .profile-sidebar::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite linear;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .profile-img-preview {
            width: 120px; /* DIUBAH: dari 160px menjadi 120px untuk lebih compact */
            height: 120px; /* DIUBAH: dari 160px menjadi 120px */
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.3); /* DIUBAH: dari 5px menjadi 4px */
            margin-bottom: 1rem; /* DIUBAH: dari 1.5rem menjadi 1rem */
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .profile-img-preview:hover { 
            transform: scale(1.05); 
            border-color: white; 
        }

        .badge-role { 
            background: rgba(255,255,255,0.2); 
            padding: 0.4rem 0.8rem; /* DIUBAH: dari 0.5rem 1rem */
            border-radius: 2rem; 
            font-size: 0.75rem; /* DIUBAH: dari 0.8rem */
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 0.75rem; /* DIUBAH: dari 1rem */
            position: relative;
            z-index: 1;
        }

        .profile-motto {
            font-style: italic;
            font-size: 0.8rem; /* DIUBAH: dari 0.9rem */
            opacity: 0.9;
            margin-bottom: 1rem; /* DIUBAH: dari 1.5rem */
            position: relative;
            z-index: 1;
            line-height: 1.4; /* DIUBAH: dari 1.5 */
            max-height: 60px; /* DITAMBAHKAN: batasi tinggi */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .upload-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 2rem;
            padding: 0.6rem 1.25rem; /* DIUBAH: dari 0.75rem 1.5rem */
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            position: relative;
            z-index: 1;
            width: 100%;
            justify-content: center;
            font-size: 0.85rem; /* DITAMBAHKAN */
        }

        .upload-btn:hover {
            background: rgba(255,255,255,0.2);
            border-color: white;
            transform: translateY(-2px);
        }

        /* Form Section */
        .form-section {
            padding: 1.5rem; /* DIUBAH: dari 2rem */
            height: 100%;
        }

        .section-title {
            font-size: 0.85rem; /* DIUBAH: dari 0.9rem */
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-sub);
            margin-bottom: 1.25rem; /* DIUBAH: dari 1.5rem */
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.625rem; /* DIUBAH: dari 0.75rem */
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control, .form-select {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: var(--text-main);
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-control:read-only {
            background: var(--bg-hover);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .input-group-text {
            background: var(--bg-hover);
            border: 1px solid var(--border-color);
            color: var(--text-sub);
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .social-icon {
            width: 20px;
            text-align: center;
        }

        .btn-save {
            background: var(--primary-gradient);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-hover);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--text-sub);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Theme Toggle */
        .theme-toggle {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .theme-toggle:hover {
            background: var(--bg-hover);
            transform: rotate(15deg);
        }

        /* Overlay untuk mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            backdrop-filter: blur(2px);
        }
        
        .sidebar-overlay.show {
            display: block;
        }

        /* Responsive Design - DIPERBAIKI UNTUK PADDING RAPAT */
        @media (max-width: 991px) {
            .sidebar-wrapper {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar-wrapper.show {
                transform: translateX(0);
            }
            
            /* PERBAIKAN: Padding lebih rapat */
            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-top: 56px; /* DIUBAH: dari 5rem menjadi 56px */
            }
            
            .mobile-header {
                display: flex;
                align-items: center;
            }
            
            .profile-card {
                border-radius: 0.75rem;
                margin-top: 0; /* DIUBAH: dari 1rem menjadi 0 */
            }
            
            .profile-sidebar {
                padding: 1.25rem 1rem; /* DIUBAH: lebih rapat */
                border-radius: 0.75rem 0.75rem 0 0;
            }
            
            .form-section {
                padding: 1.25rem; /* DIUBAH: dari 1.5rem */
            }
            
            .profile-img-preview {
                width: 100px; /* DIUBAH: lebih kecil untuk tablet */
                height: 100px;
                margin-bottom: 0.75rem;
            }
            
            .profile-sidebar h4 {
                font-size: 1.1rem; /* DIUBAH: lebih kecil */
                margin-bottom: 0.25rem;
            }
            
            .profile-motto {
                font-size: 0.75rem;
                margin-bottom: 0.75rem;
            }
        }

        @media (max-width: 768px) {
            /* PERBAIKAN: Padding lebih rapat lagi */
            .main-content {
                padding: 0.75rem;
                padding-top: 56px; /* DIUBAH: dari 5rem menjadi 56px */
            }
            
            .profile-img-preview {
                width: 90px; /* DIUBAH: lebih kecil untuk mobile */
                height: 90px;
                border-width: 3px;
            }
            
            .form-section {
                padding: 1rem; /* DIUBAH: dari 1.25rem */
            }
            
            .row.g-3 {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }
            
            .col-md-6, .col-md-12 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .section-title {
                font-size: 0.8rem;
                margin-bottom: 1rem;
                padding-bottom: 0.5rem;
            }
            
            .form-control, .form-select {
                padding: 0.625rem 0.875rem;
                font-size: 0.85rem;
            }
            
            .mobile-header {
                padding: 0.5rem 1rem;
                height: 56px;
            }
            
            .profile-sidebar {
                padding: 1rem; /* DIUBAH: lebih rapat */
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
                padding-top: 56px; /* TETAP RAPAT */
            }
            
            .profile-img-preview {
                width: 80px; /* DIUBAH: lebih kecil untuk mobile kecil */
                height: 80px;
            }
            
            .profile-sidebar h4 {
                font-size: 1rem;
            }
            
            .badge-role {
                font-size: 0.7rem;
                padding: 0.35rem 0.7rem;
                margin-bottom: 0.5rem;
            }
            
            .profile-motto {
                font-size: 0.7rem;
                margin-bottom: 0.5rem;
                max-height: 50px;
            }
            
            .upload-btn {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
            
            .section-title {
                font-size: 0.75rem;
            }
            
            .form-control, .form-select {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }
            
            .btn-save {
                padding: 0.625rem 1.5rem;
                font-size: 0.9rem;
            }
            
            .mobile-header {
                padding: 0.5rem 0.75rem;
                height: 56px;
            }
            
            .mobile-brand {
                font-size: 1.1rem;
                margin: 0 0.75rem;
            }
            
            .form-section h4 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }
            
            .profile-sidebar {
                padding: 0.875rem; /* DIUBAH: lebih rapat */
            }
        }

        @media (min-width: 992px) {
            .mobile-header {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="mobile-header d-lg-none">
        <div class="mobile-header-content">
            <button class="menu-toggle" type="button" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <span class="mobile-brand">PSIK 23 D</span>
            <button class="theme-toggle" onclick="toggleTheme()">
                <i class="fas fa-moon" id="mobileThemeIcon"></i>
            </button>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="sidebar-wrapper" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-graduation-cap"></i>
            <span>PSIK 23 D</span>
        </div>
        
        <div class="sidebar-menu mt-4 flex-grow-1">
            <div class="px-3 mb-4">
                <small class="text-uppercase fw-bold text-sub px-2 mb-2 d-block">Menu Utama</small>
                <a href="user_dashboard.php" class="nav-link">
                    <i class="fas fa-house"></i>
                    <span>Dashboard</span>
                </a>
                <a href="jadwal_kelas.php" class="nav-link">
                    <i class="fas fa-calendar-day"></i>
                    <span>Jadwal</span>
                </a>
                <a href="tugas_saya.php" class="nav-link">
                    <i class="fas fa-list-check"></i>
                    <span>Tugas Saya</span>
                </a>
                <a href="materi_kuliah.php" class="nav-link">
                    <i class="fas fa-book-open"></i>
                    <span>Materi</span>
                </a>
            </div>
            
            <div class="px-3 mb-4">
                <small class="text-uppercase fw-bold text-sub px-2 mb-2 d-block">Ekstra</small>
                <a href="kelola_berita.php" class="nav-link">
                    <i class="fas fa-pen-nib"></i>
                    <span>Tulis Info</span>
                </a>
                <a href="user_profil.php" class="nav-link"> <i class="fas fa-user-edit icon-animate"></i>
                    <span>Kelola Profil</span>
                </a>
                <a href="proses_edit_profil.php" class="nav-link active">
                    <i class="fas fa-user-cog"></i>
                    <span>Profil Saya</span>
                </a>
            </div>
            
            <div class="px-3 mt-auto mb-4">
                <a href="../backend/logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <div class="main-content">
        
        <div class="profile-card">
            <div class="row g-0">
                
                <div class="col-md-4">
                    <div class="profile-sidebar">
                        
                        <div class="w-100 px-3 mb-2">
                            <small class="text-white-50 d-block text-start mb-1" style="font-size: 0.7rem;">COVER PHOTO</small>
                            <img src="<?= $cover_photo ?>" id="previewCover" style="width: 100%; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(255,255,255,0.3); margin-bottom: 1rem;">
                            
                            <label class="upload-btn py-1" for="uploadCover" style="font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.5); border-radius: 20px; padding: 5px 10px; cursor: pointer;">
                                <i class="fas fa-image me-1"></i> Ganti Cover
                            </label>
                        </div>
                        <img src="<?= $foto_profil ?>" class="profile-img-preview" id="previewFoto">
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($data['nama']) ?></h4>
                        <span class="badge-role"><?= strtoupper(htmlspecialchars($data['jabatan'])) ?></span>
                        
                        <p class="profile-motto">
                            "<?= !empty($data['motto']) ? htmlspecialchars($data['motto']) : 'Belum ada motto hidup.' ?>"
                        </p>
                        
                        <div class="w-100">
                            <label class="upload-btn" for="uploadFoto">
                                <i class="fas fa-camera me-2"></i> Ganti Foto Profil
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-section">
                        <h4 class="fw-bold mb-4 text-dark">Edit Profil</h4>

                        <form action="../backend/proses_edit_profil.php" method="POST" enctype="multipart/form-data">
                            
                            <input type="file" name="foto" id="uploadFoto" accept="image/*" style="display: none;" onchange="previewImage(this, 'previewFoto')">
                            
                            <input type="file" name="cover_photo" id="uploadCover" accept="image/*" style="display: none;" onchange="previewImage(this, 'previewCover')">

                            <div class="section-title">
                                <i class="fas fa-user"></i>
                                Informasi Pribadi
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" readonly title="Hubungi admin jika ingin ubah nama">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Email Aktif <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" placeholder="contoh@gmail.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($data['tempat_lahir']) ?>" placeholder="Kota Kelahiran">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($data['tanggal_lahir']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Alamat Domisili</label>
                                    <textarea name="alamat_asal" class="form-control" rows="2" placeholder="Alamat lengkap saat ini..."><?= htmlspecialchars($data['alamat_asal']) ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Motto Hidup</label>
                                    <input type="text" name="motto" class="form-control" value="<?= htmlspecialchars($data['motto']) ?>" placeholder="Kutip kata-kata mutiaramu...">
                                </div>
                                
                                </div>

                            <div class="section-title">
                                <i class="fas fa-globe"></i>
                                Sosial Media
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fab fa-instagram text-danger"></i>
                                        </span>
                                        <input type="text" name="instagram" class="form-control" placeholder="Link Instagram" value="<?= htmlspecialchars($data['instagram']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fab fa-facebook text-primary"></i>
                                        </span>
                                        <input type="text" name="facebook" class="form-control" placeholder="Link Facebook" value="<?= htmlspecialchars($data['facebook']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fab fa-github"></i>
                                        </span>
                                        <input type="text" name="github" class="form-control" placeholder="Link GitHub" value="<?= htmlspecialchars($data['github']) ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-linkedin text-primary"></i></span>
                                        <input type="text" name="linkedin" class="form-control" placeholder="Link LinkedIn" value="<?= htmlspecialchars($data['linkedin'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe text-info"></i></span>
                                        <input type="text" name="website" class="form-control" placeholder="Link Website / Portfolio" value="<?= htmlspecialchars($data['website'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold">@</span>
                                        <input type="text" name="threads" class="form-control" placeholder="Link Threads" value="<?= htmlspecialchars($data['threads'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fab fa-twitter"></i>
                                        </span>
                                        <input type="text" name="twitter" class="form-control" placeholder="Link Twitter / X" value="<?= htmlspecialchars($data['twitter'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="section-title">
                                <i class="fas fa-lock"></i>
                                Keamanan
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Password Baru</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti password">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn-save">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        // Theme Toggle
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update mobile icon
            const mobileIcon = document.getElementById('mobileThemeIcon');
            if (mobileIcon) {
                mobileIcon.classList.add('animate-pulse-once');
                mobileIcon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                setTimeout(() => {
                    mobileIcon.classList.remove('animate-pulse-once');
                }, 300);
            }
        }

        // Initialize theme
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = localStorage.getItem('theme') || 'light';
            const mobileIcon = document.getElementById('mobileThemeIcon');
            
            if (mobileIcon) {
                mobileIcon.className = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        });

        // Mobile Sidebar Functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth < 992 && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target) &&
                sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if(window.innerWidth >= 992) {
                closeSidebar();
            }
        });

        // Preview image function - UPDATED UNTUK HANDLE COVER DAN FOTO
        function previewImage(input, targetId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById(targetId).src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Add hover animations for profile image (desktop only)
        if (window.innerWidth > 768) {
            const profileImg = document.getElementById('previewFoto');
            if (profileImg) {
                profileImg.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });
                
                profileImg.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            }
        }
    </script>

</body>
</html>