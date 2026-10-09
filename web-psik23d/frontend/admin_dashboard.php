<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

if($_SESSION['role'] != 'admin'){ exit("Akses Ditolak"); }

// ==========================================
// --- LOGIKA SAKLAR MAINTENANCE ---
// ==========================================
if(isset($_POST['toggle_maintenance'])) {
    // 1. SALAHNYA DI SINI BRO: Tambahkan __DIR__ biar path-nya absolut (pasti ketemu)
    $flag_file = __DIR__ . '/../maintenance.flag'; 
    
    if(file_exists($flag_file)){
        unlink($flag_file); // Hapus file = Matikan Maintenance
    } else {
        file_put_contents($flag_file, 'MANTAP'); // Bikin file = Nyalakan Maintenance
    }
    
    // Refresh halaman biar tombolnya berubah & cegah re-submission form
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit;
}
// 2. SALAHNYA DI SINI JUGA: Tambahkan __DIR__ di pengecekan ini
$is_maintenance = file_exists(__DIR__ . '/../maintenance.flag');

// --- DATA LOGIKA ---
$total_mhs = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM users WHERE role='mahasiswa'"));
$total_dosen = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM dosen"));
$total_post = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM posts"));
$total_materi = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM materials"));

// DATA GRAFIK
$kat_info = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM posts WHERE kategori='info'"));
$kat_magang = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM posts WHERE kategori='magang'"));
$kat_kkn = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM posts WHERE kategori='kkn'"));
$kat_project = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM posts WHERE kategori='project'"));

// RECENT POSTS
$recent_posts = mysqli_query($koneksi, "SELECT posts.judul, users.nama, posts.tanggal FROM posts JOIN users ON posts.id_user = users.id ORDER BY tanggal DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSIK 23 D</title>
    
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- CSS VARIABLE SYSTEM (DIPERBAIKI) --- */
        :root {
            /* Mode Terang (Light Mode) - Sidebar Putih, Teks Gelap */
            --bg-body: #f1f5f9;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --text-sidebar: #334155; /* Teks menu gelap */
            --text-sidebar-hover: #0f172a;
            --sidebar-border: #e2e8f0;
            --hover-sidebar-bg: #f8fafc;
            --border-color: #e2e8f0;
            --brand-color: #4f46e5;
        }

        [data-bs-theme="dark"] {
            /* Mode Gelap (Dark Mode) - Sidebar Navy, Teks Terang */
            --bg-body: #020617;
            --bg-sidebar: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --text-sidebar: #cbd5e1; /* Teks menu terang */
            --text-sidebar-hover: #ffffff;
            --sidebar-border: rgba(255,255,255,0.05);
            --hover-sidebar-bg: rgba(255,255,255,0.05);
            --border-color: #334155;
            --brand-color: #818cf8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* --- SIDEBAR PREMIUM FIX --- */
        .sidebar-wrapper {
            width: 260px;
            height: 100dvh;
            background: var(--bg-sidebar);
            position: fixed;
            top: 0; left: 0;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--sidebar-border);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem;
            color: var(--brand-color);
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .sidebar-menu { flex: 1; overflow-y: auto; padding: 1rem 0; }
        
        .nav-section {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.2px;
            color: var(--text-sub); margin: 1.5rem 1.5rem 0.5rem; font-weight: 700;
        }

        .nav-link {
            color: var(--text-sidebar);
            padding: 12px 24px;
            margin: 4px 12px; /* Margin kiri kanan agar ada jarak */
            border-radius: 8px; /* Rounded item */
            font-weight: 500;
            transition: all 0.2s;
            display: flex; align-items: center;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--hover-sidebar-bg);
            color: var(--brand-color);
            font-weight: 600;
        }
        
        /* Indikator aktif di kiri */
        .nav-link.active {
            background: rgba(79, 70, 229, 0.1); /* Tint warna primary */
        }

        .nav-link i { width: 24px; text-align: center; margin-right: 10px; }

        /* --- CONTENT --- */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* --- MOBILE HEADER FIX --- */
        .mobile-header {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1040;
            padding: 1rem;
            display: none; /* Default hidden di desktop */
        }

        /* --- CARDS & ANIMATIONS --- */
        .premium-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .premium-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }

        .animate-up {
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* --- THEME TOGGLE --- */
        .theme-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            width: 40px; height: 40px;
            border-radius: 50%;
            display: grid; place-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .theme-btn:hover { background: var(--border-color); }

        /* --- MOBILE RESPONSIVE --- */
        @media (max-width: 991px) {
            .sidebar-wrapper { transform: translateX(-100%); width: 280px; }
            .sidebar-wrapper.show { transform: translateX(0); box-shadow: 10px 0 30px rgba(0,0,0,0.1); }
            .main-content { margin-left: 0; padding: 1rem; }
            .mobile-header { display: flex; justify-content: space-between; align-items: center; }
        }
    </style>
</head>
<body>

<div class="mobile-header d-lg-none">
    <div class="d-flex align-items-center">
        <button class="btn btn-outline-secondary border-0 me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile" style="color: var(--text-main);">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <span class="fw-bold fs-5" style="color: var(--text-main);">Admin Panel</span>
    </div>
    <button class="theme-btn" onclick="toggleTheme()"><i class="fas fa-moon" id="iconMobile"></i></button>
</div>

<div class="offcanvas-lg offcanvas-start sidebar-wrapper" tabindex="-1" id="sidebarMobile">
    <div class="sidebar-brand d-flex justify-content-between align-items-center">
        <span><i class="fas fa-layer-group me-2"></i>DASHBOARD</span>
        <button type="button" class="btn border-0 d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMobile" style="color: var(--text-main);">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="sidebar-menu">
        <div class="nav-section">Utama</div>
        <a href="admin_dashboard.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a>
        
        <div class="nav-section">Data Akademik</div>
        <a href="mahasiswa.php" class="nav-link"><i class="fas fa-user-graduate"></i> Mahasiswa</a>
        <a href="dosen.php" class="nav-link"><i class="fas fa-chalkboard-user"></i> Dosen</a>
        <a href="jadwal.php" class="nav-link"><i class="fas fa-calendar-days"></i> Jadwal</a>
        <a href="data_tugas.php" class="nav-link"><i class="fas fa-calendar-days"></i>Daftar Tugas</a>
        <a href="tampil_absensi.php" class="nav-link"><i class="fas fa-calendar-days"></i>Absensi</a>
        
        <div class="nav-section">Konten</div>
        <a href="kelola_lagu.php" class="nav-link"><i class="fas fa-music"></i> Musik</a>
        <a href="admin_berita.php" class="nav-link"><i class="fas fa-newspaper"></i> Berita</a>
        <a href="admin_materi.php" class="nav-link"><i class="fas fa-folder-open"></i> Materi</a>
        <a href="admin_tugas.php" class="nav-link"><i class="fas fa-list-check"></i> Tugas</a>
        <a href="pengumuman.php" class="nav-link"><i class="fas fa-bullhorn"></i> Pengumuman</a>
        <a href="admin_popup.php" class="nav-link"><i class="fas fa-external-link-alt"></i> Popup</a>
        <a href="galeri.php" class="nav-link"><i class="fas fa-camera"></i> Galeri Kelas</a>
        <a href="kegiatan.php" class="nav-link"><i class="fas fa-calendar-day"></i> Kegiatan Kelas</a>
        <a href="admin_sempro.php" class="nav-link"><i class="fas fa-chalkboard-teacher me-2"></i> Kelola Sempro</a>
        <a href="admin_pembayaran.php" class="nav-link"><i class="fas fa-chalkboard-teacher me-2"></i> Kelola Payment</a>
        
        <div class="nav-section">Informasi</div>
        <a href="admin_laporan.php" class="nav-link"><i class="fas fa-clipboard-list"></i> Laporan</a>
        <a href="admin_rating.php" class="nav-link"><i class="fas fa-star"></i> Publik Rating</a>
        
        <div class="px-4 mt-5">
            <a href="../backend/logout.php" class="btn btn-danger w-100 fw-bold shadow-sm">
                <i class="fas fa-sign-out-alt me-2"></i> Keluar
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    
    <div class="d-none d-lg-flex justify-content-between align-items-center mb-5 animate-up">
        <div>
            <h2 class="fw-bold mb-1">Selamat Datang, Admin! </h2>
            <p class="text-sub mb-0">Ringkasan aktivitas akademik hari ini.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <div class="fw-bold"><?= date('l, d F Y') ?></div>
                <small class="text-primary fw-bold" id="jamRealtime"></small>
            </div>
            <button class="theme-btn" onclick="toggleTheme()"><i class="fas fa-moon" id="iconDesktop"></i></button>
        </div>
    </div>

    <div class="premium-card mb-4 animate-up delay-1" style="border-radius: 16px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="mb-3 mb-md-0">
                <h5 class="fw-bolder mb-1"><i class="fas fa-tools me-2 text-warning"></i>Mode Maintenance Sistem</h5>
                <span class="text-sub small d-block">Kunci halaman Jadwal Kelas & Daftar Tugas untuk proses perbaikan/update agar tidak diakses mahasiswa sementara waktu.</span>
            </div>
            <form method="POST" class="m-0">
                <?php if($is_maintenance): ?>
                    <button type="submit" name="toggle_maintenance" class="btn btn-danger fw-bold rounded-pill px-4 shadow-sm" style="transition: 0.3s;">
                        <i class="fas fa-lock me-2"></i>Sistem Terkunci (ON)
                    </button>
                <?php else: ?>
                    <button type="submit" name="toggle_maintenance" class="btn btn-outline-success fw-bold rounded-pill px-4" style="transition: 0.3s;">
                        <i class="fas fa-globe me-2"></i>Sistem Terbuka (OFF)
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <?php 
        $stats = [
            ['Mahasiswa', $total_mhs, 'fa-users', 'primary', 'bg-primary'],
            ['Dosen', $total_dosen, 'fa-chalkboard', 'success', 'bg-success'],
            ['Postingan', $total_post, 'fa-newspaper', 'warning', 'bg-warning'],
            ['Materi', $total_materi, 'fa-book-open', 'info', 'bg-info']
        ];
        $delay = 1;
        foreach($stats as $s): ?>
        <div class="col-6 col-xl-3 animate-up delay-<?= $delay++ ?>">
            <div class="premium-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-sub mb-1 small text-uppercase fw-bold"><?= $s[0] ?></p>
                        <h2 class="fw-bold mb-0"><?= $s[1] ?></h2>
                    </div>
                    <div class="stat-icon <?= $s[4] ?> bg-opacity-10 text-<?= $s[3] ?>">
                        <i class="fas <?= $s[2] ?>"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-8 animate-up delay-2">
            <div class="premium-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Statistik Kategori Post</h5>
                </div>
                <div style="height: 320px;">
                    <canvas id="beritaChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 animate-up delay-3">
            <div class="premium-card h-100">
                <h5 class="fw-bold mb-4">Postingan Terbaru</h5>
                <div class="d-flex flex-column gap-3">
                    <?php if(mysqli_num_rows($recent_posts) > 0): ?>
                        <?php while($rp = mysqli_fetch_assoc($recent_posts)): ?>
                        <div class="d-flex gap-3 pb-3 border-bottom" style="border-color: var(--border-color) !important;">
                            <div class="mt-1"><i class="fas fa-circle text-primary" style="font-size: 8px;"></i></div>
                            <div style="overflow: hidden;">
                                <div class="fw-bold text-truncate"><?= $rp['judul'] ?></div>
                                <small class="text-sub" style="font-size: 0.8rem;">
                                    <?= $rp['nama'] ?> • <?= date('d M', strtotime($rp['tanggal'])) ?>
                                </small>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center text-sub py-5">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // --- JAM REALTIME ---
    setInterval(() => {
        const now = new Date();
        document.getElementById('jamRealtime').innerText = now.toLocaleTimeString('id-ID');
    }, 1000);

    // --- DARK MODE LOGIC ---
    const icons = { desktop: document.getElementById('iconDesktop'), mobile: document.getElementById('iconMobile') };
    
    function updateUI(theme) {
        const iconClass = theme === 'dark' ? 'fa-sun' : 'fa-moon';
        if(icons.desktop) icons.desktop.className = `fas ${iconClass}`;
        if(icons.mobile) icons.mobile.className = `fas ${iconClass}`;
        
        // Update Chart Colors Dynamic
        if(window.myChart) {
            const textColor = theme === 'dark' ? '#94a3b8' : '#64748b';
            const gridColor = theme === 'dark' ? '#334155' : '#e2e8f0';
            
            window.myChart.options.scales.x.ticks.color = textColor;
            window.myChart.options.scales.y.ticks.color = textColor;
            window.myChart.options.scales.y.grid.color = gridColor;
            window.myChart.update();
        }
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-bs-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
        updateUI(next);
    }

    // Init Icon
    updateUI(localStorage.getItem('theme') || 'light');

    // --- CHART CONFIG ---
    const ctx = document.getElementById('beritaChart').getContext('2d');
    
    // Check initial theme for chart
    const initialIsDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    
    window.myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Info', 'Magang', 'KKN', 'Project'],
            datasets: [{
                label: 'Jumlah Post',
                data: [<?= $kat_info ?>, <?= $kat_magang ?>, <?= $kat_kkn ?>, <?= $kat_project ?>],
                backgroundColor: ['#6366f1', '#22c55e', '#eab308', '#06b6d4'],
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { borderDash: [5, 5], color: initialIsDark ? '#334155' : '#e2e8f0' },
                    ticks: { color: initialIsDark ? '#94a3b8' : '#64748b' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { color: initialIsDark ? '#94a3b8' : '#64748b' }
                }
            }
        }
    });
</script>

</body>
</html>