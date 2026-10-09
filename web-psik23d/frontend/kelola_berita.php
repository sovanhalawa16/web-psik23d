<?php 
session_start();
include '../koneksi.php';

// Cek Login
if(!isset($_SESSION['id'])){ header("location:login.php"); exit; }

$id_user = $_SESSION['id'];

// Ambil berita HANYA milik user yang login
$query = mysqli_query($koneksi, "SELECT * FROM posts WHERE id_user='$id_user' ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Postingan Saya</title>
    
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>

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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
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

        /* Header Styles */
        .dashboard-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite linear;
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

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title h3 {
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-title p {
            color: var(--text-sub);
            margin-bottom: 0;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .btn-custom {
            border-radius: 0.75rem;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            color: white;
            border: none;
        }

        .btn-outline-custom {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .btn-outline-custom:hover {
            background: var(--bg-hover);
            border-color: var(--primary-color);
        }

        /* Postingan Card */
        .post-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            height: 100%;
            animation: fadeInUp 0.5s ease-out;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .post-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
        }

        .post-content {
            padding: 1.25rem;
        }

        .post-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-block;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            color: var(--primary-color);
        }

        .post-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
            margin-bottom: 0.75rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-date {
            font-size: 0.75rem;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-actions {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-color);
            background: var(--bg-hover);
            display: flex;
            gap: 0.75rem;
        }

        .action-btn {
            flex: 1;
            border-radius: 0.75rem;
            padding: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .edit-btn {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            color: var(--warning-color);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .edit-btn:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.2) 100%);
            color: var(--warning-color);
            border-color: var(--warning-color);
        }

        .delete-btn {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.2) 100%);
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-sub);
            width: 100%;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h5 {
            font-weight: 600;
            margin-bottom: 1rem;
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
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
                margin-bottom: 1.5rem; /* DIUBAH: dari 2rem menjadi 1.5rem */
            }
            
            .action-buttons {
                justify-content: flex-start;
            }
            
            .col-md-4 {
                width: 100%;
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .page-title h3 {
                font-size: 1.5rem; /* DIUBAH: lebih kecil untuk mobile */
                margin-bottom: 0.25rem;
            }
            
            .page-title p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            /* PERBAIKAN: Padding lebih rapat lagi */
            .main-content {
                padding: 0.75rem;
                padding-top: 56px; /* DIUBAH: dari 5rem menjadi 56px */
            }
            
            .post-card {
                margin-bottom: 1rem;
            }
            
            .post-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
            }
            
            .page-header {
                margin-bottom: 1.25rem; /* DIUBAH: lebih rapat */
            }
            
            .page-title h3 {
                font-size: 1.3rem;
            }
            
            .page-title p {
                font-size: 0.85rem;
            }
            
            .mobile-header {
                padding: 0.5rem 1rem;
                height: 56px;
            }
            
            .btn-custom {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
                padding-top: 56px; /* TETAP RAPAT */
            }
            
            .page-header {
                margin-bottom: 1rem;
                gap: 0.75rem;
            }
            
            .page-title h3 {
                font-size: 1.2rem;
            }
            
            .page-title p {
                font-size: 0.8rem;
            }
            
            .action-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-custom {
                width: 100%;
                justify-content: center;
            }
            
            .post-image {
                height: 180px; /* DIUBAH: lebih kecil */
            }
            
            .post-content {
                padding: 1rem;
            }
            
            .post-actions {
                padding: 0.875rem 1rem;
            }
            
            .mobile-header {
                padding: 0.5rem 0.75rem;
                height: 56px;
            }
            
            .mobile-brand {
                font-size: 1.1rem;
                margin: 0 0.75rem;
            }
            
            .empty-state {
                padding: 3rem 1rem; /* DIUBAH: lebih kecil */
            }
            
            .empty-state i {
                font-size: 3rem;
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

    <!-- Mobile Header - DIPERBAIKI UNTUK PADDING RAPAT -->
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

    <!-- Sidebar Overlay (Mobile only) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
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
                <a href="kelola_berita.php" class="nav-link active">
                    <i class="fas fa-pen-nib"></i>
                    <span>Tulis Info</span>
                </a>
                <a href="user_profil.php" class="nav-link"> <i class="fas fa-user-edit icon-animate"></i>
                    <span>Kelola Profil</span>
                </a>
                <a href="edit_profil.php" class="nav-link">
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

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h3><i class="fas fa-pen-nib me-2"></i>Postingan Saya</h3>
                <p>Bagikan pengalaman magang, KKN, atau projectmu disini.</p>
            </div>
            
            <div class="action-buttons">
                <a href="user_dashboard.php" class="btn btn-outline-custom btn-custom">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
                <a href="tambah_berita.php" class="btn btn-primary-custom btn-custom">
                    <i class="fas fa-plus me-2"></i> Tulis Baru
                </a>
            </div>
        </div>

        <!-- Postingan Grid -->
        <?php if(mysqli_num_rows($query) > 0): ?>
            <div class="row g-4">
                <?php while($p = mysqli_fetch_assoc($query)): ?>
                <div class="col-md-4">
                    <div class="post-card">
                        <img src="../assets/img/berita/<?= htmlspecialchars($p['gambar']) ?>" 
                             class="post-image" 
                             alt="<?= htmlspecialchars($p['judul']) ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1581276879432-15e50529f34b?w=400&h=200&fit=crop'">
                        
                        <div class="post-content">
                            <span class="post-badge"><?= strtoupper(htmlspecialchars($p['kategori'])) ?></span>
                            <h5 class="post-title"><?= htmlspecialchars($p['judul']) ?></h5>
                            <div class="post-date">
                                <i class="far fa-calendar"></i>
                                <?= date('d M Y', strtotime($p['tanggal'])) ?>
                            </div>
                        </div>
                        
                        <div class="post-actions">
                            <a href="edit_berita.php?id=<?= $p['id'] ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="../backend/proses_hapus_berita.php?id=<?= $p['id'] ?>&gambar=<?= $p['gambar'] ?>" 
                               class="action-btn delete-btn" 
                               onclick="return confirm('Yakin ingin menghapus postingan ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h5 class="fw-bold">Belum ada postingan</h5>
                <p class="text-muted mb-4">Mulai bagikan pengalaman dan ceritamu dengan menulis postingan pertama.</p>
                <a href="tambah_berita.php" class="btn btn-primary-custom btn-custom">
                    <i class="fas fa-plus me-2"></i> Tulis Postingan
                </a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme Toggle
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update desktop icon
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.classList.add('animate-pulse-once');
                icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                setTimeout(() => {
                    icon.classList.remove('animate-pulse-once');
                }, 300);
            }
            
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
            
            // Add fallback for broken images
            document.querySelectorAll('.post-image').forEach(img => {
                img.addEventListener('error', function() {
                    this.src = 'https://images.unsplash.com/photo-1581276879432-15e50529f34b?w=400&h=200&fit=crop';
                });
            });
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

        // Add hover animations for cards (desktop only)
        if (window.innerWidth > 768) {
            document.querySelectorAll('.post-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        }
    </script>

</body>
</html>