<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

// Fitur Pencarian
$where = "";
if(isset($_GET['cari'])){
    $cari = $_GET['cari'];
    $where = "WHERE judul LIKE '%$cari%' OR mata_kuliah LIKE '%$cari%'";
}

$query = mysqli_query($koneksi, "SELECT * FROM materials $where ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Kuliah</title>
    
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

        /* Header Styles - FIXED FOR MOBILE */
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

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
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

        /* Search Box */
        .search-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .search-box:hover {
            box-shadow: var(--shadow-lg);
        }

        .search-input {
            background: var(--bg-hover);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-main);
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-btn {
            background: var(--primary-gradient);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: white;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Materi Card */
        .materi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            height: 100%;
            animation: fadeInUp 0.5s ease-out;
        }

        .materi-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .materi-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .materi-meta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 0.5rem;
        }

        .materi-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-matkul {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            color: var(--primary-color);
        }

        .badge-semester {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            color: var(--success-color);
        }

        .materi-description {
            color: var(--text-sub);
            font-size: 0.875rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .materi-footer {
            background: var(--bg-hover);
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .upload-date {
            font-size: 0.75rem;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .download-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 0.75rem;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-sub);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
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
                padding-top: 56px; /* DIUBAH: dari 4rem menjadi 56px */
            }
            
            .mobile-header {
                display: flex;
            }
            
            /* PERBAIKAN: Header lebih rapat */
            .dashboard-header {
                margin-top: 0; /* DIUBAH: dari 0.5rem menjadi 0 */
                padding: 1.25rem;
                margin-bottom: 1.5rem;
                min-height: auto; /* DIUBAH: hapus min-height */
            }
            
            .dashboard-header h1 {
                font-size: 1.3rem; /* DIUBAH: dari 1.4rem menjadi 1.3rem */
                margin-bottom: 0.5rem;
            }
            
            .dashboard-header p {
                font-size: 0.85rem;
            }
            
            .theme-toggle {
                position: static;
                margin-left: auto;
            }
            
            .search-box {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .col-md-4 {
                width: 100%;
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            /* PERBAIKAN: Padding lebih rapat lagi */
            .main-content {
                padding: 0.75rem;
                padding-top: 56px; /* DIUBAH: dari 3.5rem menjadi 56px */
            }
            
            .dashboard-header {
                padding: 1rem;
                margin-bottom: 1.25rem;
                border-radius: 0.75rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.2rem;
                margin-bottom: 0.25rem;
            }
            
            .dashboard-header p {
                font-size: 0.8rem;
            }
            
            .search-box {
                margin-bottom: 1rem;
                padding: 0.875rem;
            }
            
            .materi-card {
                margin-bottom: 0.75rem;
            }
            
            .materi-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .materi-footer {
                flex-direction: column;
                gap: 0.75rem;
                align-items: stretch;
            }
            
            .download-btn {
                width: 100%;
                justify-content: center;
            }
            
            .mobile-header {
                padding: 0.5rem 1rem;
                height: 56px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
                padding-top: 56px;
            }
            
            .dashboard-header {
                padding: 0.875rem;
                margin-bottom: 1rem;
                border-radius: 0.875rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.1rem;
            }
            
            .dashboard-header p {
                font-size: 0.75rem;
            }
            
            .search-box {
                padding: 0.75rem;
            }
            
            .search-input {
                font-size: 0.875rem;
                padding: 0.625rem 0.875rem;
            }
            
            .search-btn {
                padding: 0.625rem 1rem;
                font-size: 0.875rem;
            }
            
            .materi-title {
                font-size: 1rem;
            }
            
            .materi-description {
                font-size: 0.8rem;
            }
            
            .mobile-header {
                padding: 0.5rem 0.75rem;
                height: 56px;
            }
            
            .mobile-brand {
                font-size: 1.1rem;
                margin: 0 0.75rem;
            }
        }

        @media (min-width: 992px) {
            .mobile-header {
                display: none;
            }
            
            .dashboard-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1.5rem 2rem;
            }
            
            .dashboard-header > div:first-child {
                flex: 1;
            }
            
            .theme-toggle {
                margin-left: 1rem;
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
                <a href="materi_kuliah.php" class="nav-link active">
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
        
        <!-- Dashboard Header - DIPERBAIKI UNTUK MOBILE -->
        <div class="dashboard-header">
            <div>
                <h1 class="h3 fw-bold mb-2"><i class="fas fa-books me-2"></i>Repository Materi Kuliah</h1>
                <p class="mb-0 opacity-90">Temukan dan unduh materi pembelajaran dari semua mata kuliah</p>
            </div>
            <button class="theme-toggle d-none d-lg-flex" onclick="toggleTheme()">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
        </div>

        <!-- Search Box -->
        <div class="search-box">
            <form action="" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-10">
                        <input type="text" name="cari" class="form-control search-input" 
                               placeholder="Cari materi berdasarkan judul atau mata kuliah..." 
                               value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn search-btn w-100" type="submit">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Materi Grid -->
        <?php if(mysqli_num_rows($query) > 0): ?>
            <div class="row g-4">
                <?php while($m = mysqli_fetch_assoc($query)): ?>
                <div class="col-md-4">
                    <div class="materi-card">
                        <div class="card-header">
                            <div class="materi-title"><?= htmlspecialchars($m['judul']) ?></div>
                            <div class="materi-meta">
                                <span class="materi-badge badge-matkul">
                                    <i class="fas fa-book me-1"></i><?= htmlspecialchars($m['mata_kuliah']) ?>
                                </span>
                                <span class="materi-badge badge-semester">
                                    <i class="fas fa-calendar-alt me-1"></i>Semester <?= htmlspecialchars($m['semester']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <p class="materi-description"><?= nl2br(htmlspecialchars($m['deskripsi'])) ?></p>
                        </div>
                        <div class="materi-footer">
                            <div class="upload-date">
                                <i class="far fa-clock"></i>
                                Diupload: <?= date('d M Y', strtotime($m['uploaded_at'])) ?>
                            </div>
                            <a href="../assets/files/<?= htmlspecialchars($m['file']) ?>" class="download-btn" target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h4 class="fw-bold mb-2">Materi tidak ditemukan</h4>
                <p class="text-muted mb-4">
                    <?php if(isset($_GET['cari'])): ?>
                        Tidak ada materi yang sesuai dengan pencarian "<?= htmlspecialchars($_GET['cari']) ?>"
                    <?php else: ?>
                        Belum ada materi yang tersedia
                    <?php endif; ?>
                </p>
                <?php if(isset($_GET['cari'])): ?>
                    <a href="materi_kuliah.php" class="download-btn">
                        <i class="fas fa-times me-2"></i> Tampilkan Semua Materi
                    </a>
                <?php endif; ?>
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
            const icon = document.getElementById('themeIcon');
            const mobileIcon = document.getElementById('mobileThemeIcon');
            
            if (icon) {
                icon.className = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
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

        // Add hover animations for cards (desktop only)
        if (window.innerWidth > 768) {
            document.querySelectorAll('.materi-card').forEach(card => {
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