<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

// Pastikan Timezone Aman
date_default_timezone_set('Asia/Jakarta');

$id_mhs = $_SESSION['id'];

// Ambil data tugas
$query = "SELECT tugas.*, 
          (SELECT status FROM tugas_submission WHERE id_tugas = tugas.id AND id_mhs = '$id_mhs') as status_saya
          FROM tugas 
          ORDER BY deadline ASC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deadline Tugas Saya</title>
    
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

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Mobile Header - DIPERBAIKI */
        .mobile-header {
            display: none;
            background: var(--bg-card);
            padding: 0.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1040;
            box-shadow: var(--shadow-md);
            align-items: center;
            justify-content: space-between;
            height: 56px;
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
        }

        /* Tugas Card */
        .card-tugas {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary-color);
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.5s ease-out;
        }

        .card-tugas:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-left-color: var(--info-color);
        }

        .card-tugas.done {
            border-left-color: var(--success-color);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
        }

        .card-tugas.late {
            border-left-color: var(--danger-color);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(220, 38, 38, 0.05) 100%);
        }

        .card-tugas.done:hover {
            border-left-color: var(--success-color);
        }

        .card-tugas.late:hover {
            border-left-color: var(--danger-color);
        }

        /* Alert Box */
        .alert-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            border-left: 5px solid var(--warning-color);
            box-shadow: var(--shadow-md);
            animation: slideInRight 0.5s ease-out;
        }

        /* Countdown */
        .countdown-box {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: inline-block;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }

        .countdown-box.urgent {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
            color: var(--danger-color);
            animation: pulse 1s infinite;
        }

        .countdown-box.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
            color: var(--warning-color);
        }

        .countdown-box.safe {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
            color: var(--success-color);
        }

        /* Buttons */
        .btn-custom {
            border-radius: 0.75rem;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid transparent;
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

        /* Badges */
        .badge-custom {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.75rem;
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
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-top: 56px; /* DIUBAH: dari 3rem jadi 56px */
            }
            
            .mobile-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            
            .dashboard-header {
                margin-top: 0; /* DIUBAH: dari 1rem jadi 0 */
                padding: 1.25rem;
                margin-bottom: 1.5rem; /* DITAMBAHKAN */
            }
            
            .card-tugas {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0.75rem;
                padding-top: 56px; /* DIUBAH: dari 1rem jadi 56px */
            }
            
            .dashboard-header {
                padding: 1rem;
                border-radius: 0.75rem;
                margin-bottom: 1.25rem; /* DITAMBAHKAN */
            }
            
            .card-tugas {
                border-radius: 0.75rem;
                margin-bottom: 0.875rem; /* DIUBAH: lebih rapat */
            }
            
            .btn-custom {
                padding: 0.375rem 1rem;
                font-size: 0.875rem;
            }
            
            .mobile-header {
                padding: 0.5rem 0.75rem; /* DITAMBAHKAN: lebih rapat */
                height: 56px; /* DITAMBAHKAN */
            }
            
            .alert-custom {
                margin-bottom: 1.5rem; /* DITAMBAHKAN: untuk konsistensi */
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
                padding-top: 56px; /* TETAP RAPAT */
            }
            
            .dashboard-header {
                padding: 0.875rem;
                margin-bottom: 1rem;
                border-radius: 0.875rem;
            }
            
            .card-tugas {
                padding: 1rem !important;
                margin-bottom: 0.75rem;
            }
            
            .mobile-header {
                padding: 0.5rem 0.75rem;
                height: 56px;
            }
            
            .badge-custom {
                font-size: 0.7rem;
                padding: 0.2rem 0.6rem;
            }
            
            .btn-custom {
                padding: 0.3rem 0.8rem;
                font-size: 0.8rem;
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

    <!-- Mobile Header -->
    <div class="mobile-header d-lg-none animate-fade-in-up">
        <button class="menu-toggle" type="button" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <span class="mobile-brand">PSIK 23 D</span>
        <button class="theme-toggle" onclick="toggleTheme()">
            <i class="fas fa-moon" id="mobileThemeIcon"></i>
        </button>
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
                <a href="tugas_saya.php" class="nav-link active">
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
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-2"><i class="fas fa-tasks me-2"></i>Deadline Tugas Saya</h1>
                    <p class="mb-0 opacity-90">Pantau dan kelola semua tugas kuliahmu di satu tempat</p>
                </div>
                <button class="theme-toggle d-none d-lg-flex" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
            </div>
        </div>

        <!-- Warning Alert -->
        <div class="alert alert-custom mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fa-2x text-warning me-3 mt-1"></i>
                <div>
                    <strong class="d-block mb-2">Penting untuk Diingat!</strong>
                    <p class="mb-0 small">
                        Tombol "Tandai Selesai" hanya untuk catatan pribadimu. 
                        Pastikan kamu tetap mengumpulkan tugas di link yang tersedia sebelum menandai selesai.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tugas List -->
        <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <?php while($t = mysqli_fetch_assoc($result)): 
                        $is_done = ($t['status_saya'] == 'selesai');
                        
                        // Siapkan class CSS
                        $card_class = "card-tugas";
                        if($is_done) $card_class .= " done";
                        
                        // Format Deadline untuk JS (ISO Format)
                        $deadline_iso = date('Y-m-d H:i:s', strtotime($t['deadline']));
                    ?>

                    <div class="card <?= $card_class ?>" id="card-<?= $t['id'] ?>">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <h5 class="fw-bold mb-0"><?= $t['judul'] ?></h5>
                                        <span class="badge badge-custom" style="background: rgba(100, 116, 139, 0.1); color: var(--text-sub);">
                                            <?= $t['mata_kuliah'] ?>
                                        </span>
                                    </div>
                                    
                                    <p class="text-muted mb-3 small"><?= nl2br($t['deskripsi']) ?></p>
                                    
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if($t['link_pengumpulan']): ?>
                                            <a href="<?= $t['link_pengumpulan'] ?>" target="_blank" class="btn btn-primary-custom btn-custom">
                                                <i class="fas fa-upload me-2"></i> Kumpul Tugas
                                            </a>
                                        <?php endif; ?>

                                        <a href="../backend/proses_status_tugas.php?id=<?= $t['id'] ?>" 
                                           class="btn btn-outline-custom btn-custom <?= $is_done ? 'border-success text-success' : '' ?>">
                                            <?php if($is_done): ?>
                                                <i class="fas fa-check-square me-2"></i> Selesai (Batal)
                                            <?php else: ?>
                                                <i class="far fa-square me-2"></i> Tandai Selesai
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <?php if($is_done): ?>
                                        <span class="badge badge-custom" style="background: rgba(16, 185, 129, 0.1); color: var(--success-color);">
                                            <i class="fas fa-check-circle me-1"></i> Sudah Dikerjakan
                                        </span>
                                    <?php else: ?>
                                        <div class="countdown-box" 
                                             data-deadline="<?= $deadline_iso ?>" 
                                             data-id="<?= $t['id'] ?>">
                                             <i class="fas fa-spinner fa-spin me-1"></i> Loading...
                                        </div>
                                    <?php endif; ?>

                                    <div class="small text-muted mt-2">
                                        <i class="far fa-clock me-1"></i>
                                        Deadline: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php endwhile; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-party-horn"></i>
                <h4 class="fw-bold mb-2">Tidak ada tugas aktif!</h4>
                <p class="text-muted mb-4">Semua tugas sudah selesai atau belum ada tugas yang diberikan.</p>
                <a href="user_dashboard.php" class="btn btn-primary-custom btn-custom">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
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

        // Countdown Timer
        function updateCountdowns() {
            const now = new Date().getTime();
            
            document.querySelectorAll('.countdown-box').forEach(box => {
                const deadlineStr = box.getAttribute('data-deadline');
                const deadline = new Date(deadlineStr.replace(' ', 'T')).getTime();
                const distance = deadline - now;
                const cardId = box.getAttribute('data-id');
                const cardElement = document.getElementById('card-' + cardId);

                if (distance < 0) {
                    // Waktu Habis
                    box.innerHTML = "<span class='badge badge-custom' style='background: rgba(239, 68, 68, 0.1); color: var(--danger-color);'><i class='fas fa-exclamation-triangle me-1'></i> Terlewat</span>";
                    box.className = 'countdown-box urgent';
                    if(cardElement && !cardElement.classList.contains('done')) {
                        cardElement.classList.add('late');
                    }
                } else {
                    // Hitung Waktu
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Format Tampilan
                    let text = "";
                    let boxClass = "countdown-box safe";
                    
                    if(days > 0) {
                        text += days + "h ";
                        if(days < 3) boxClass = "countdown-box warning";
                    }
                    if(hours > 0 || days > 0) text += hours + "j ";
                    if(minutes > 0 || hours > 0 || days > 0) text += minutes + "m ";
                    text += seconds + "d";
                    
                    // Atur class berdasarkan urgensi
                    if(days == 0 && hours < 5) boxClass = "countdown-box warning";
                    if(days == 0 && hours == 0 && minutes < 60) boxClass = "countdown-box urgent";

                    box.className = boxClass;
                    box.innerHTML = `<i class="fas fa-clock me-1"></i> ${text}`;
                }
            });
        }

        // Jalankan setiap 1 detik
        setInterval(updateCountdowns, 1000);
        updateCountdowns(); // Jalankan langsung pas load

        // Add hover animations
        document.querySelectorAll('.card-tugas').forEach(card => {
            card.addEventListener('mouseenter', function() {
                if(window.innerWidth > 768) {
                    this.style.transform = 'translateY(-5px)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if(window.innerWidth > 768) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if(window.innerWidth >= 992) {
                closeSidebar();
            }
        });
    </script>

</body>
</html>