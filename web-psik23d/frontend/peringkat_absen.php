<?php 
session_start();
include '../koneksi.php'; 

date_default_timezone_set('Asia/Jakarta');

// Cek apakah mahasiswa sudah login
if(!isset($_SESSION['nim_mhs_login'])) {
    header("Location: rekap_absen.php");
    exit;
}

$nama_mhs = $_SESSION['nama_mhs_login'];
$nim_mhs = $_SESSION['nim_mhs_login'];

// ==========================================
// QUERY SEMUA PERINGKAT MAHASISWA
// ==========================================
$q_rank = mysqli_query($koneksi, "
    SELECT a.nim, a.nama_lengkap, u.foto,
    SUM(CASE 
        WHEN a.status_absensi = 'hadir' THEN 3 
        WHEN a.status_absensi = 'terlambat' THEN 1 
        ELSE 0 
    END) as score_hadir
    FROM absensi_real a
    LEFT JOIN users u ON a.nim = u.nim
    GROUP BY a.nim, a.nama_lengkap
    ORDER BY score_hadir DESC, a.nama_lengkap ASC
");

$all_students = [];
while($row = mysqli_fetch_assoc($q_rank)) {
    $all_students[] = $row;
}

// Pisahin Top 3 buat Podium, sisanya buat List Bawah
$top3 = array_slice($all_students, 0, 3);
$others = array_slice($all_students, 3);

function getFotoPath($foto, $nama) {
    return (!empty($foto) && $foto != 'default.jpg') ? '../assets/img/mahasiswa/'.$foto : 'https://ui-avatars.com/api/?background=random&name='.urlencode($nama);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peringkat Absensi - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; padding-bottom: 70px; }
        
        /* --- HEADER PREMIUM (UNIFORM STYLE) --- */
        .app-header { background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 12px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .logo-header { width: 38px; height: 38px; object-fit: contain; }
        .header-text { line-height: 1.2; text-align: left; }
        .greeting-time { font-size: 0.7rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; }
        .greeting-name { font-size: 0.75rem; font-weight: 700; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }

        /* HEADER JUDUL HALAMAN */
        .page-title-box { padding: 25px 20px 10px; text-align: center; }
        .page-title { font-size: 1.5rem; font-weight: 900; color: #0f172a; margin-bottom: 5px; }
        .page-subtitle { font-size: 0.8rem; font-weight: 600; color: #64748b; }

        /* --- 3D PODIUM CANGGIH & CLEAN --- */
        .podium-container-3d { display: flex; align-items: flex-end; justify-content: center; gap: 12px; margin-top: 30px; padding: 0 15px; }
        .podium-item { display: flex; flex-direction: column; align-items: center; width: 110px; position: relative; }
        
        .podium-photo-wrap { position: relative; z-index: 3; margin-bottom: -25px; }
        .podium-photo { width: 75px; height: 75px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.1); background: white; }
        
        .crown { position: absolute; top: -30px; left: 50%; transform: translateX(-50%); font-size: 2rem; color: #fbbf24; z-index: 4; animation: floatGlow 2s ease-in-out infinite; }
        @keyframes floatGlow { 
            0%, 100% { transform: translate(-50%, 0) scale(1); filter: drop-shadow(0 2px 5px rgba(251,191,36,0.5)); } 
            50% { transform: translate(-50%, -8px) scale(1.15); filter: drop-shadow(0 10px 20px rgba(251,191,36,0.9)); } 
        }

        /* Podium Block Clean Tanpa Garis + ANIMASI MENYALA */
        .podium-block { width: 100%; border-radius: 20px 20px 16px 16px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); position: relative; z-index: 2; border: 1px solid rgba(255,255,255,0.8); }
        
        /* Animasi Glow Emas, Perak, Perunggu */
        @keyframes glowRank1 {
            0%, 100% { box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3), 0 0 10px rgba(251, 191, 36, 0.2); }
            50% { box-shadow: 0 15px 45px rgba(251, 191, 36, 0.7), 0 0 30px rgba(251, 191, 36, 0.5); }
        }
        @keyframes glowRank2 {
            0%, 100% { box-shadow: 0 10px 25px rgba(148, 163, 184, 0.2), 0 0 10px rgba(148, 163, 184, 0.1); }
            50% { box-shadow: 0 15px 35px rgba(148, 163, 184, 0.6), 0 0 25px rgba(148, 163, 184, 0.4); }
        }
        @keyframes glowRank3 {
            0%, 100% { box-shadow: 0 10px 25px rgba(217, 119, 6, 0.2), 0 0 10px rgba(217, 119, 6, 0.1); }
            50% { box-shadow: 0 15px 35px rgba(217, 119, 6, 0.6), 0 0 25px rgba(217, 119, 6, 0.4); }
        }

        /* Terapkan ke Podium & Bikin Border Nyala */
        .rank-1 .podium-block { height: 150px; animation: glowRank1 2s infinite ease-in-out; border-color: rgba(251, 191, 36, 0.4); }
        .rank-1 .podium-photo { width: 90px; height: 90px; border-color: #fffbeb; }
        .rank-1 .podium-num { color: #fbbf24; font-size: 3.5rem; text-shadow: 0 4px 10px rgba(251, 191, 36, 0.4); }
        
        .rank-2 .podium-block { height: 110px; animation: glowRank2 2.5s infinite ease-in-out; border-color: rgba(148, 163, 184, 0.3); }
        .rank-2 .podium-num { color: #94a3b8; font-size: 2.8rem; text-shadow: 0 4px 10px rgba(148, 163, 184, 0.4); }
        
        .rank-3 .podium-block { height: 85px; animation: glowRank3 3s infinite ease-in-out; border-color: rgba(217, 119, 6, 0.3); }
        .rank-3 .podium-num { color: #d97706; font-size: 2.2rem; text-shadow: 0 4px 10px rgba(217, 119, 6, 0.4); }
        
        /* Name Plate Melayang */
        .podium-name-plate { background: #ffffff; padding: 6px 12px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); text-align: center; margin-top: 15px; position: relative; z-index: 4; width: 120%; }
        .p-name { font-size: 0.75rem; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .p-score { font-size: 0.65rem; font-weight: 700; color: #64748b; margin-top: 2px; }
        .rank-1 .p-name { color: #d97706; font-size: 0.85rem; }

        /* --- LIST RANKING BAWAH (ALA IOS) --- */
        .rank-list-container { padding: 25px 15px; margin-top: 20px; }
        .rank-card { background: #ffffff; border-radius: 20px; padding: 15px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #f8fafc; transition: 0.2s; }
        .rank-card:active { transform: scale(0.98); }
        
        .rank-position { width: 35px; font-size: 1.1rem; font-weight: 900; color: #94a3b8; text-align: center; flex-shrink: 0; }
        .rank-info { display: flex; align-items: center; gap: 12px; flex-grow: 1; overflow: hidden; padding-right: 10px; }
        .rank-info img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #f1f5f9; }
        .rank-info-text { display: flex; flex-direction: column; overflow: hidden; }
        .rank-name { font-size: 0.9rem; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .rank-nim { font-size: 0.7rem; font-weight: 600; color: #64748b; }
        
        .rank-score-badge { background: #f8fafc; color: #3b82f6; font-weight: 800; font-size: 0.85rem; padding: 6px 12px; border-radius: 12px; border: 1px solid #e2e8f0; flex-shrink: 0; }

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
        
        /* Highlight baris sendiri di list */
        .is-me { border: 2px solid #3b82f6; background: #eff6ff; }
        .is-me .rank-score-badge { background: #3b82f6; color: white; border-color: #3b82f6; }
    </style>
</head>
<body>

    <div class="app-header">
        <div class="d-flex align-items-center gap-2">
            <img src="../assets/img/FMIPA.png" alt="Logo Unimed" class="logo-header">
            <div class="header-text text-start">
                <div class="greeting-time">Selamat <?= (date('H')<11?'Pagi':(date('H')<15?'Siang':(date('H')<18?'Sore':'Malam'))) ?> 👋</div>
                <div class="greeting-name"><?= htmlspecialchars($nama_mhs) ?></div>
            </div>
        </div>

        <div class="d-flex align-items-center">
            <img src="../assets/img/logo_kelas.jpg" alt="Logo Kelas" class="logo-header rounded-circle shadow-sm">
        </div>
    </div>

    <div class="page-title-box">
        <h2 class="page-title">Klasemen Peringkat 🏆</h2>
        <div class="page-subtitle">Top Kehadiran Tertinggi PSIK 23 D</div>
    </div>

    <div class="podium-container-3d">
        <?php 
            $r1 = $top3[0] ?? null; 
            $r2 = $top3[1] ?? null; 
            $r3 = $top3[2] ?? null;
        ?>

        <?php if($r2): ?>
        <div class="podium-item rank-2">
            <div class="podium-photo-wrap"><img src="<?= getFotoPath($r2['foto'], $r2['nama_lengkap']) ?>" class="podium-photo"></div>
            <div class="podium-block"><span class="podium-num">2</span></div>
            <div class="podium-name-plate">
                <div class="p-name"><?= explode(' ', $r2['nama_lengkap'])[0] ?></div>
                <div class="p-score"><?= $r2['score_hadir'] ?> Poin</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($r1): ?>
        <div class="podium-item rank-1">
            <div class="podium-photo-wrap">
                <i class="fas fa-crown crown"></i>
                <img src="<?= getFotoPath($r1['foto'], $r1['nama_lengkap']) ?>" class="podium-photo">
            </div>
            <div class="podium-block"><span class="podium-num">1</span></div>
            <div class="podium-name-plate">
                <div class="p-name"><?= explode(' ', $r1['nama_lengkap'])[0] ?></div>
                <div class="p-score text-warning"><?= $r1['score_hadir'] ?> Poin</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($r3): ?>
        <div class="podium-item rank-3">
            <div class="podium-photo-wrap"><img src="<?= getFotoPath($r3['foto'], $r3['nama_lengkap']) ?>" class="podium-photo"></div>
            <div class="podium-block"><span class="podium-num">3</span></div>
            <div class="podium-name-plate">
                <div class="p-name"><?= explode(' ', $r3['nama_lengkap'])[0] ?></div>
                <div class="p-score"><?= $r3['score_hadir'] ?> Poin</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="rank-list-container">
        <h6 class="fw-bolder mb-3 text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-left: 5px;">Peringkat Selanjutnya</h6>
        
        <?php 
        $current_rank = 4;
        foreach($others as $mhs): 
            // Highlight kalau ini akunnya sendiri
            $is_me_class = ($mhs['nim'] == $nim_mhs) ? 'is-me shadow-sm' : '';
        ?>
        <div class="rank-card <?= $is_me_class ?>">
            <div class="rank-position">#<?= $current_rank ?></div>
            
            <div class="rank-info">
                <img src="<?= getFotoPath($mhs['foto'], $mhs['nama_lengkap']) ?>" alt="Foto">
                <div class="rank-info-text">
                    <span class="rank-name"><?= htmlspecialchars($mhs['nama_lengkap']) ?> <?= ($mhs['nim'] == $nim_mhs) ? '<i>(Kamu)</i>' : '' ?></span>
                    <span class="rank-nim"><?= htmlspecialchars($mhs['nim']) ?></span>
                </div>
            </div>
            
            <div class="rank-score-badge">
                <?= $mhs['score_hadir'] ?> pts
            </div>
        </div>
        <?php $current_rank++; endforeach; ?>
        
        <?php if(empty($others)): ?>
            <div class="text-center text-muted small mt-4 fw-bold">Belum ada data peringkat lainnya.</div>
        <?php endif; ?>
    </div>

    <div class="bottom-nav">
        <a href="/rekap_absen" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="/filter_absen" class="nav-item">
            <i class="fas fa-search"></i>
            <span>Filter</span>
        </a>
        <a href="/peringkat_absen" class="nav-item active">
            <i class="fas fa-trophy"></i>
            <span>Peringkat</span>
        </a>
        <a href="?logout=true" class="nav-item text-danger">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <div id="fireworks-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/fireworks-js@2.x/dist/index.umd.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('fireworks-container');
            const fireworks = new Fireworks.default(container, {
                autoresize: true,
                opacity: 0.5,
                acceleration: 1.05,
                friction: 0.97,
                gravity: 1.5,
                particles: 60, /* Jumlah percikan pas meledak */
                traceLength: 3,
                traceSpeed: 10,
                explosion: 5, /* Besar ledakan */
                intensity: 20, /* Seberapa sering petasan nembak (makin tinggi makin brutal) */
                flickering: 50, /* Efek kedap-kedip */
                lineStyle: 'round',
                hue: {
                    min: 0,
                    max: 360
                },
                delay: {
                    min: 15,
                    max: 30
                },
                rocketsPoint: {
                    min: 50,
                    max: 50
                },
                lineWidth: {
                    explosion: { min: 1, max: 3 },
                    trace: { min: 1, max: 2 }
                },
                brightness: {
                    min: 50,
                    max: 80
                },
                decay: {
                    min: 0.015,
                    max: 0.03
                },
                mouse: {
                    click: false,
                    move: false,
                    max: 1
                }
            });
            
            // Gas nyalakan petasannya!
            fireworks.start();

            // Opsional: Kalau mau petasannya otomatis berhenti setelah 15 detik biar nggak menuhin layar terus
            // Buka komentar di bawah ini kalau mau dipakai:
            // setTimeout(() => { fireworks.stop(); }, 15000); 
        });
    </script>
</body>
</html>