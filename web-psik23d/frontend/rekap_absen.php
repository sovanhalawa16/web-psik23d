<?php 
session_start();
include '../koneksi.php'; 

date_default_timezone_set('Asia/Jakarta');

// ==========================================
// 1. LOGIKA LOGIN NIM
// ==========================================
if(isset($_POST['login_nim'])) {
    $nim_input = mysqli_real_escape_string($koneksi, $_POST['login_nim']);
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE nim='$nim_input' AND role='mahasiswa'");
    
    if(mysqli_num_rows($cek) > 0) {
        $user = mysqli_fetch_assoc($cek);
        $_SESSION['nim_mhs_login'] = $user['nim'];
        $_SESSION['nama_mhs_login'] = $user['nama'];
        $_SESSION['foto_mhs_login'] = $user['foto'];
        header("Location: " . $_SERVER['PHP_SELF'] . "?login=success");
        exit;
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?login=failed");
        exit;
    }
}

if(isset($_GET['logout'])) {
    unset($_SESSION['nim_mhs_login'], $_SESSION['nama_mhs_login'], $_SESSION['foto_mhs_login']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$is_logged_in = isset($_SESSION['nim_mhs_login']);
$nim_mhs = $is_logged_in ? $_SESSION['nim_mhs_login'] : '';
$nama_mhs = $is_logged_in ? $_SESSION['nama_mhs_login'] : '';
$foto_mhs = $is_logged_in ? $_SESSION['foto_mhs_login'] : '';

if($is_logged_in) {
    // A. DATA GLOBAL
    $q_pribadi = mysqli_query($koneksi, "
        SELECT COUNT(id) as total_kelas, 
        SUM(CASE WHEN status_absensi = 'hadir' THEN 1 ELSE 0 END) as hadir, 
        SUM(CASE WHEN status_absensi = 'terlambat' THEN 1 ELSE 0 END) as telat, 
        SUM(CASE WHEN status_absensi = 'izin' THEN 1 ELSE 0 END) as izin, 
        SUM(CASE WHEN status_absensi = 'sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status_absensi = 'alpha' THEN 1 ELSE 0 END) as alpha 
        FROM absensi_real WHERE nim = '$nim_mhs'
    ");
    $data_pribadi = mysqli_fetch_assoc($q_pribadi);
    
    $total_kelas = $data_pribadi['total_kelas'];
    $persen_health = ($total_kelas > 0) ? round((($data_pribadi['hadir'] + $data_pribadi['telat']) / $total_kelas) * 100) : 100;

    // Hitung persentase grafik bar
    $pHadir = ($total_kelas > 0) ? round(($data_pribadi['hadir'] / $total_kelas) * 100) : 0;
    $pTelat = ($total_kelas > 0) ? round(($data_pribadi['telat'] / $total_kelas) * 100) : 0;
    $pIzinSakit = ($total_kelas > 0) ? round((($data_pribadi['izin'] + $data_pribadi['sakit']) / $total_kelas) * 100) : 0;
    $pAlpha = ($total_kelas > 0) ? round(($data_pribadi['alpha'] / $total_kelas) * 100) : 0;

    // B. DATA DETAIL PER MATKUL
    $max_alpha = 3;
    $q_matkul_detail = mysqli_query($koneksi, "
        SELECT nama_mk, 
        SUM(CASE WHEN status_absensi = 'hadir' THEN 1 ELSE 0 END) as h,
        SUM(CASE WHEN status_absensi = 'izin' THEN 1 ELSE 0 END) as i,
        SUM(CASE WHEN status_absensi = 'sakit' THEN 1 ELSE 0 END) as s,
        SUM(CASE WHEN status_absensi = 'terlambat' THEN 1 ELSE 0 END) as t,
        SUM(CASE WHEN status_absensi = 'alpha' THEN 1 ELSE 0 END) as a
        FROM absensi_real 
        WHERE nim = '$nim_mhs' 
        GROUP BY nama_mk 
        ORDER BY nama_mk ASC
    ");
}

function getFotoPath($foto, $nama) {
    return (!empty($foto) && $foto != 'default.jpg') ? '../assets/img/mahasiswa/'.$foto : 'https://ui-avatars.com/api/?background=random&name='.urlencode($nama);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>My Student Portal - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; padding-bottom: 70px;}
        /* --- BOTTOM NAVBAR (IOS STYLE) --- */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex; justify-content: space-around; align-items: center;
            padding: 12px 10px 25px 10px; /* Safe area untuk iPhone */
            z-index: 1050; box-shadow: 0 -5px 20px rgba(0,0,0,0.02);
        }
        /* --- BOTTOM NAVBAR (SLIM & TIGHT STYLE) --- */
        .bottom-nav { 
            position: fixed; 
            bottom: 0; 
            left: 0; 
            width: 100%; 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px); 
            border-top: 1px solid rgba(0,0,0,0.08); 
            display: flex; 
            justify-content: space-around; 
            align-items: center; 
            /* 👇 Padding ditekan biar lebih langsing */
            padding: 8px 10px 10px 10px; 
            z-index: 1050; 
            box-shadow: 0 -2px 15px rgba(0,0,0,0.05); 
        }

        .nav-item { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            text-decoration: none; 
            color: #94a3b8; 
            font-size: 0.6rem; /* Font lebih kecil dikit biar slim */
            font-weight: 700; 
            gap: 2px; /* Jarak icon ke teks dirapetin */
            transition: all 0.2s ease; 
        }

        .nav-item i { 
            font-size: 1.2rem; /* Icon dikecilin dikit biar gak bongsor */
            transition: transform 0.2s ease; 
        }

        .nav-item.active { color: #3b82f6; }
        /* Efek naik dikit pas aktif tapi gak lebay */
        .nav-item.active i { transform: translateY(-2px); }
        .nav-item:hover { color: #3b82f6; text-decoration: none; }
        
        /* --- LOGIN --- */
        #loginOverlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(25px); z-index: 9999; display: flex; justify-content: center; align-items: center; }
        .login-box { background: white; width: 90%; max-width: 360px; border-radius: 30px; padding: 40px 30px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.2); }
        .login-input { text-align: center; font-weight: 800; font-size: 1.5rem; letter-spacing: 3px; border-radius: 20px; border: 2px solid #e2e8f0; padding: 15px; }
        .login-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 5px rgba(59,130,246,0.1); outline: none; }

        /* --- HEADER PREMIUM (UNIFORM STYLE) --- */
        .app-header { background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 12px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .logo-header { width: 38px; height: 38px; object-fit: contain; }
        .header-text { line-height: 1.2; text-align: left; } /* Perubahan ke kiri agar rapi di samping logo */
        .greeting-time { font-size: 0.7rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; }
        .greeting-name { font-size: 0.75rem; font-weight: 700; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }

        /* --- BENTO CONTAINER --- */
        .bento-container { display: flex; flex-direction: column; gap: 15px; margin-top: 20px; padding: 0 15px; }
        .bento-card { background: #ffffff; border-radius: 24px; padding: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.03); border: 1px solid #f8fafc; }
        
        /* --- CARD PSIK 23 D (CLEAN) --- */
        .card-psik { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden; }
        .card-psik::after { content: ''; position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%; }
        .card-psik img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.2); }

        /* --- TABLE H-I-S-T-A --- */
        .table-hista { width: 100%; border-collapse: collapse; }
        .table-hista th { font-size: 0.7rem; color: #64748b; font-weight: 800; text-align: center; padding: 10px 5px; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; }
        .table-hista th:first-child { text-align: left; }
        .table-hista td { font-size: 0.9rem; font-weight: 800; text-align: center; padding: 12px 5px; border-bottom: 1px dashed #f1f5f9; color: #334155; }
        .table-hista td:first-child { text-align: left; font-weight: 700; font-size: 0.8rem; color: #0f172a; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .table-hista tr:last-child td { border-bottom: none; }

        /* --- GRID 5 MINI STATS --- */
        .mini-stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
        .mini-stat-box { border-radius: 16px; padding: 15px 5px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .mini-val { font-size: 1.5rem; font-weight: 900; line-height: 1; margin-bottom: 4px; }
        .mini-lbl { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Colors Stats */
        .bg-h { background: #d1fae5; color: #059669; }
        .bg-i { background: #e0f2fe; color: #0284c7; }
        .bg-s { background: #f1f5f9; color: #475569; }
        .bg-t { background: #fef3c7; color: #d97706; }
        .bg-a { background: #fee2e2; color: #dc2626; }

        /* --- KESEHATAN ABSENSI --- */
        .health-circle { width: 85px; height: 85px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 900; color: #1e293b; flex-shrink: 0; }
        .health-inner { width: 65px; height: 65px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        
        .mk-quota-item { padding: 15px 0; border-bottom: 1px solid #f1f5f9; }
        .mk-quota-item:last-child { border-bottom: none; }
        .quota-msg { font-size: 0.75rem; font-weight: 700; margin-top: 5px; display: inline-block; padding: 4px 10px; border-radius: 8px; }

        /* --- STACKED BAR CHART --- */
        .chart-wrapper { background: #f8fafc; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; }
        .custom-progress { height: 25px; border-radius: 15px; overflow: hidden; display: flex; background: #e2e8f0; width: 100%; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }
        .prog-bar { display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 0.65rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2); transition: width 1s ease-in-out; }
    </style>
</head>
<body>

<?php if(!$is_logged_in): ?>
    <div id="loginOverlay">
        <div class="login-box">
            <img src="../assets/img/FMIPA.png" style="width: 60px; margin-bottom: 15px;">
            <h4 class="fw-bolder">Student Portal</h4>
            <p class="text-muted small mb-4">Masukkan NIM untuk akses data</p>
            <form method="POST">
                <input type="number" name="login_nim" class="form-control login-input mb-4" placeholder="NIM ANDA" required autofocus>
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm">Masuk Portal</button>
            </form>
        </div>
    </div>
<?php else: ?>

    <div class="app-header">
        <div class="d-flex align-items-center gap-2">
            <img src="../assets/img/FMIPA.png" alt="Logo" class="logo-header">
            <div class="header-text">
                <div class="greeting-time">Selamat <?= (date('H')<11?'Pagi':(date('H')<15?'Siang':(date('H')<18?'Sore':'Malam'))) ?> 👋</div>
                <div class="greeting-name"><?= htmlspecialchars($nama_mhs) ?></div>
            </div>
        </div>

        <div class="d-flex align-items-center">
            <img src="../assets/img/logo_kelas.jpg" alt="Logo Kelas" class="logo-header rounded-circle shadow-sm">
        </div>
    </div>

    <div class="bento-container">
        
        <div class="bento-card card-psik shadow-sm">
            <div>
                <span class="badge bg-white text-dark rounded-pill mb-2 px-3 fw-bolder" style="font-size: 0.65rem; letter-spacing: 1px;">PSIK 23 D</span>
                <div class="fw-bolder fs-4 lh-1 mb-1"><?= htmlspecialchars($nama_mhs) ?></div>
                <div class="text-white-50 fw-bold mt-1" style="letter-spacing: 2px; font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($nim_mhs) ?></div>
            </div>
            <img src="<?= getFotoPath($foto_mhs, $nama_mhs) ?>" alt="Foto">
        </div>

        <div class="mini-stats-grid">
            <div class="mini-stat-box bg-h shadow-sm">
                <div class="mini-val"><?= $data_pribadi['hadir'] ?></div><div class="mini-lbl">Hadir</div>
            </div>
            <div class="mini-stat-box bg-i shadow-sm">
                <div class="mini-val"><?= $data_pribadi['izin'] ?></div><div class="mini-lbl">Izin</div>
            </div>
            <div class="mini-stat-box bg-s shadow-sm">
                <div class="mini-val"><?= $data_pribadi['sakit'] ?></div><div class="mini-lbl">Sakit</div>
            </div>
            <div class="mini-stat-box bg-t shadow-sm">
                <div class="mini-val"><?= $data_pribadi['telat'] ?></div><div class="mini-lbl">Telat</div>
            </div>
            <div class="mini-stat-box bg-a shadow-sm">
                <div class="mini-val"><?= $data_pribadi['alpha'] ?></div><div class="mini-lbl">Alpha</div>
            </div>
        </div>

        <div class="bento-card shadow-sm">
            <h6 class="fw-bolder mb-3 text-dark"><i class="fas fa-table text-primary me-2"></i>Rincian Kehadiran</h6>
            <table class="table-hista">
                <thead>
                    <tr>
                        <th>MATA KULIAH</th>
                        <th>H</th>
                        <th>I</th>
                        <th>S</th>
                        <th>T</th>
                        <th>A</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Reset pointer query biar bisa dipake lagi
                    mysqli_data_seek($q_matkul_detail, 0);
                    while($mk_det = mysqli_fetch_assoc($q_matkul_detail)): 
                    ?>
                    <tr>
                        <td title="<?= htmlspecialchars($mk_det['nama_mk']) ?>"><?= htmlspecialchars($mk_det['nama_mk']) ?></td>
                        <td class="text-success"><?= $mk_det['h'] ?></td>
                        <td class="text-info"><?= $mk_det['i'] ?></td>
                        <td class="text-secondary"><?= $mk_det['s'] ?></td>
                        <td class="text-warning"><?= $mk_det['t'] ?></td>
                        <td class="text-danger"><?= $mk_det['a'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="bento-card shadow-sm">
            <div class="d-flex align-items-center gap-4 mb-4 border-bottom border-light pb-3">
                <?php 
                    $hc = ($persen_health < 75 ? '#ef4444' : ($persen_health < 90 ? '#f59e0b' : '#10b981'));
                ?>
                <div class="health-circle shadow-sm" style="background: conic-gradient(<?= $hc ?> <?= $persen_health ?>%, #e2e8f0 0);">
                    <div class="health-inner"><?= $persen_health ?>%</div>
                </div>
                <div>
                    <div class="fw-bolder fs-5 text-dark">Kesehatan Absensi</div>
                    <div class="small text-muted fw-bold">Dari total <?= $total_kelas ?> pertemuan</div>
                </div>
            </div>

            <div class="mt-3">
                <div class="small fw-bolder text-muted text-uppercase mb-3" style="letter-spacing: 1px;">Status Jatah Alpha per Matkul</div>
                
                <?php 
                mysqli_data_seek($q_matkul_detail, 0); 
                while($mk = mysqli_fetch_assoc($q_matkul_detail)): 
                    $alpha = $mk['a'];
                    $sisa = $max_alpha - $alpha;
                    $sisa = max(0, $sisa); 
                    
                    // --- JURUS WARNA BARU SESUAI INSTRUKSI ---
                    if($sisa >= 3) {
                        $msg = "Pertahankan bro! Kehadiran mulus.";
                        $badge_class = "bg-success bg-opacity-10 text-success border border-success border-opacity-25";
                        $bg_sisa = "bg-success text-white"; 
                    } elseif($sisa == 2) {
                        $msg = "Aman, tapi jangan dibiasain bolos ya.";
                        $badge_class = "bg-warning bg-opacity-10 text-warning border border-warning";
                        $bg_sisa = "bg-warning text-dark"; 
                    } elseif($sisa == 1) {
                        $msg = "Lampu kuning! Udah mepet batas maksimal.";
                        $badge_class = "bg-warning bg-opacity-10 text-warning border border-warning";
                        $bg_sisa = "bg-warning text-dark"; 
                    } else {
                        $msg = "Maaf, kamu tidak bisa UTS dan UAS.";
                        $badge_class = "bg-danger text-white shadow-sm";
                        $bg_sisa = "bg-danger text-white"; 
                    }
                ?>
                <div class="mk-quota-item">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bolder text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($mk['nama_mk']) ?></span>
                        <span class="badge <?= $bg_sisa ?> rounded-pill shadow-sm">Sisa: <?= $sisa ?></span>
                    </div>
                    <span class="quota-msg <?= $badge_class ?>"><i class="fas <?= ($sisa==0 ? 'fa-times-circle' : 'fa-info-circle') ?> me-1"></i><?= $msg ?></span>
                </div>
                <?php endwhile; ?>
            </div>

        <div class="bento-card shadow-sm mb-4">
            <h6 class="fw-bolder mb-3 text-dark"><i class="fas fa-chart-line text-primary me-2"></i>Tren Keseluruhan Status</h6>
            <div class="chart-wrapper" style="height: 250px; position: relative; padding: 10px;">
                <canvas id="tradingChart"></canvas>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if(isset($_GET['login']) && $_GET['login'] == 'success'): ?>
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Portal Terbuka! Welcome <?= explode(' ', $nama_mhs)[0] ?>', showConfirmButton: false, timer: 3000 });
                window.history.replaceState({}, document.title, window.location.pathname);
            <?php endif; ?>
        });
    </script>
<?php endif; ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // === INISIALISASI GRAFIK TRADING ===
            const ctx = document.getElementById('tradingChart').getContext('2d');

            // Bikin efek Gradient Fill (warna luntur ke bawah) ala Crypto/Saham
            let gradient = ctx.createLinearGradient(0, 0, 0, 250);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.6)'); // Biru nyala di atas
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)'); // Transparan di bawah

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Hadir', 'Telat', 'Izin', 'Sakit', 'Alpha'],
                    datasets: [{
                        label: 'Total Kehadiran',
                        data: [
                            <?= $data_pribadi['hadir'] ?>, 
                            <?= $data_pribadi['telat'] ?>, 
                            <?= $data_pribadi['izin'] ?>, 
                            <?= $data_pribadi['sakit'] ?>, 
                            <?= $data_pribadi['alpha'] ?>
                        ],
                        borderColor: '#3b82f6', // Garis biru neon
                        backgroundColor: gradient, // Efek luntur
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        fill: true,
                        tension: 0.4 // Bikin garis melengkung smooth (gak kaku patah-patah)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 13, family: "'Plus Jakarta Sans', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Plus Jakarta Sans', sans-serif" },
                            padding: 10,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [5, 5], color: '#e2e8f0' }, 
                            ticks: { stepSize: 1, font: { weight: 'bold' } } 
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { font: { weight: 'bold' } }
                        }
                    },
                    animation: {
                        y: { duration: 2000, easing: 'easeOutElastic' } // Animasi mantul pas load
                    }
                }
            });
        });
    </script>
<div class="bottom-nav">
    <a href="rekap_absen" class="nav-item active">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="/filter_absen" class="nav-item">
        <i class="fas fa-search"></i>
        <span>Filter</span>
    </a>
    <a href="/peringkat_absen" class="nav-item">
        <i class="fas fa-trophy"></i>
        <span>Peringkat</span>
    </a>
    <a href="?logout=true" class="nav-item text-danger">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>
</body>
</html>