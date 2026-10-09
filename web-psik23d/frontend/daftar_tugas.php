<?php
session_start();
 
// KUNCI MAINTENANCE
$flag_file = __DIR__ . '/../maintenance.flag';
if (file_exists($flag_file)) { 
    header("Location: /maintenance"); // Panggil file yang ada di folder FRONTEND
    exit;
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../koneksi.php'; 
date_default_timezone_set('Asia/Jakarta');

// ==========================================
// 1. FITUR AJAX: HANDLE ABSENSI PINTAR
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'add_checklist') {
    ob_start();
    $tugas_id = (int)$_POST['tugas_id'];
    $nama_mhs = mysqli_real_escape_string($koneksi, trim($_POST['nama_mhs']));
    $waktu    = date('Y-m-d H:i:s');

    if (!empty($nama_mhs)) {
        $cek = mysqli_query($koneksi, "SELECT id FROM checklist_tugas WHERE tugas_id = '$tugas_id' AND nama_mahasiswa = '$nama_mhs'");
        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($koneksi, "INSERT INTO checklist_tugas (tugas_id, nama_mahasiswa, status, tanggal_selesai) VALUES ('$tugas_id', '$nama_mhs', 'selesai', '$waktu')");
            $resp = ['status' => 'success', 'waktu' => date('H:i', strtotime($waktu))];
        } else {
            $resp = ['status' => 'error', 'msg' => 'Kamu sudah menandai tugas ini selesai!'];
        }
    } else {
        $resp = ['status' => 'error', 'msg' => 'Nama tidak valid'];
    }
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($resp);
    exit;
}

// ==========================================
// 2. AMBIL DATA UTAMA & DASHBOARD STATS
// ==========================================
$q_tugas = mysqli_query($koneksi, "SELECT t.*, d.nama AS nama_dosen, d.foto AS foto_dosen FROM tugas_perkuliahan t LEFT JOIN dosen d ON t.dosen_id = d.id ORDER BY t.waktu_selesai ASC");

$q_mhs = mysqli_query($koneksi, "SELECT nama FROM users WHERE role = 'mahasiswa' ORDER BY nama ASC");
$mahasiswa_list = [];
while ($m = mysqli_fetch_assoc($q_mhs)) { $mahasiswa_list[] = $m['nama']; }
$total_mhs = count($mahasiswa_list);

$q_check = mysqli_query($koneksi, "SELECT * FROM checklist_tugas ORDER BY tanggal_selesai DESC");
$checklist_data = [];
while ($c = mysqli_fetch_assoc($q_check)) {
    $checklist_data[$c['tugas_id']][] = [
        'nama' => $c['nama_mahasiswa'],
        'waktu' => $c['tanggal_selesai']
    ];
}

// Kalkulasi Mini Dashboard dengan Logika Waktu Baru
$total_tugas_count = mysqli_num_rows($q_tugas);
$dash_aktif = 0;
$dash_selesai = 0;
$total_semua_absen = 0;

mysqli_data_seek($q_tugas, 0);
$ranking_mhs = [];
foreach ($checklist_data as $tugas_id => $mhs_list) {
    foreach ($mhs_list as $mhs) {
        $n = $mhs['nama'];
        $ranking_mhs[$n] = ($ranking_mhs[$n] ?? 0) + 1;
    }
}
arsort($ranking_mhs); // Urutkan dari yang paling banyak (Rajin)
$top_mhs = array_slice($ranking_mhs, 0, 5, true); // Ambil Top 5 aja biar UI-nya premium
while($t = mysqli_fetch_assoc($q_tugas)) {
    $id = $t['id'];
    $end_ts = strtotime($t['waktu_selesai']);
    $diff_sec = $end_ts - time();
    $selesai_tugas = isset($checklist_data[$id]) ? count($checklist_data[$id]) : 0;
    
    $total_semua_absen += $selesai_tugas;
    
    $stat = 'Aktif';
    if ($diff_sec <= 0) {
        if ($diff_sec <= -(7 * 24 * 60 * 60)) { // Lewat 7 Hari (Arsip)
            $stat = 'Arsip';
        } else { // Habis waktu tapi masih dalam 1 minggu (Selesai)
            $stat = 'Selesai';
        }
    } elseif ($total_mhs > 0 && $selesai_tugas == $total_mhs) { // 100% Checklist sblm expired
        $stat = 'Selesai';
    }

    if($stat == 'Aktif') $dash_aktif++;
    if($stat == 'Selesai') $dash_selesai++;
}
$dash_persen = ($total_mhs > 0 && $total_tugas_count > 0) ? ($total_semua_absen / ($total_mhs * $total_tugas_count)) * 100 : 0;
mysqli_data_seek($q_tugas, 0);

// Helper Color Coded Tags
$pastel_bg = ['#fee2e2', '#e0e7ff', '#dcfce7', '#fef3c7', '#f3e8ff', '#ffedd5', '#cffafe'];
$pastel_text = ['#991b1b', '#3730a3', '#166534', '#92400e', '#6b21a8', '#9a3412', '#155e75'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Daftar Tugas - PSIK 23 D</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; overflow-x: hidden; padding-bottom: 90px;}

        /* --- HEADER FORMAL ELEGAN --- */
        .header-modern { 
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); 
            padding: 45px 0 35px; 
            color: #ffffff; 
            text-align: center; 
            margin-bottom: -30px; /* Menarik konten ke atas sedikit */
            border-radius: 0 0 35px 35px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
            position: relative;
            overflow: hidden;
            z-index: 10;
        }
        .header-modern::before {
            content: ''; position: absolute; top: -50px; left: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;
        }
        .header-modern::after {
            content: ''; position: absolute; bottom: -50px; right: -50px; width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%;
        }

        /* --- DASHBOARD STATS --- */
        .dashboard-wrapper { position: relative; z-index: 20; padding-top: 15px; margin-top: 30px; }
        .dash-card { background: #fff; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.03); transition: transform 0.3s; }
        .dash-card:hover { transform: translateY(-3px); }
        .dash-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .bg-red-light { background: #fee2e2; color: #ef4444; }
        .bg-green-light { background: #dcfce7; color: #10b981; }
        .bg-blue-light { background: #e0e7ff; color: #3b82f6; }
        .dash-value { font-size: 1.5rem; font-weight: 800; line-height: 1; margin-top: 5px; color: #0f172a; }
        .dash-title { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; }

        /* --- TOOLBAR --- */
        .toolbar-wrapper { display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between; align-items: center; margin-top: 25px; margin-bottom: 25px; }
        .search-box { position: relative; flex: 1; min-width: 250px; }
        .search-box input { width: 100%; padding: 10px 20px 10px 45px; border-radius: 50px; border: 1px solid #cbd5e1; font-size: 0.9rem; font-weight: 600; background: #ffffff; transition: all 0.3s; }
        .search-box input:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); outline: none; }
        .search-box i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1rem; }
        .controls-group { display: flex; gap: 10px; align-items: center; }

        /* --- NAVIGATION TABS --- */
        .desktop-filter-wrapper { display: flex; justify-content: center; gap: 8px; margin-bottom: 25px; }
        .filter-btn { background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 8px 22px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); transition: all 0.2s; cursor: pointer; }
        .filter-btn.active { background: #0f172a; color: #ffffff; border-color: #0f172a; }

        .mobile-bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(15px); border-top: 1px solid rgba(0,0,0,0.08); z-index: 9999; display: none; justify-content: space-around; padding: 12px 10px 20px; box-shadow: 0 -10px 30px rgba(0,0,0,0.04); }
        .nav-item-mobile { display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; text-decoration: none; font-size: 0.7rem; font-weight: 800; transition: all 0.3s; width: 60px; cursor: pointer; }
        .nav-item-mobile i { font-size: 1.3rem; margin-bottom: 4px; transition: transform 0.2s; }
        .nav-item-mobile.active { color: #0f172a; }
        .nav-item-mobile.active i { color: #2563eb; transform: translateY(-3px) scale(1.1); }

        /* --- VIEW TOGGLE (LIST VS GRID) --- */
        .task-container { transition: all 0.3s ease; }
        .view-list { position: relative; padding-left: 45px; display: flex; flex-direction: column; }
        .view-list::before { content: ''; position: absolute; top: 15px; bottom: 0; left: 20px; width: 4px; border-radius: 5px; background: linear-gradient(180deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b); opacity: 0.8; }
        .view-list .task-card { margin-bottom: 30px; }
        .view-list .task-card::before { content: ''; position: absolute; top: 35px; left: -31px; width: 18px; height: 18px; border-radius: 50%; background: #ffffff; border: 4px solid #8b5cf6; z-index: 2; box-shadow: 0 0 10px rgba(139, 92, 246, 0.4); }
        
        .view-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; padding-left: 0; }
        .view-grid::before { display: none; }
        .view-grid .task-card::before { display: none; }
        .view-grid .task-card { margin-bottom: 0; height: max-content; }

        /* --- TASK CARD PREMIUM BOUNCE --- */
        .task-card { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03); position: relative; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
        .task-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06); border-color: #cbd5e1; }
        
        /* PIN ICON */
        .pin-btn { position: absolute; top: 25px; right: 25px; font-size: 1.2rem; color: #cbd5e1; cursor: pointer; transition: 0.3s; z-index: 10; }
        .pin-btn:hover { transform: scale(1.2); }
        .is-pinned .pin-btn { color: #f59e0b; text-shadow: 0 0 10px rgba(245,158,11,0.5); }

        .card-header-toggle { padding: 25px 30px; cursor: pointer; background: transparent; border-radius: 20px; }
        .toggle-icon { transition: transform 0.3s; }
        .collapsed .toggle-icon { transform: rotate(-90deg); }
        @keyframes pulseFire { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.7; transform: scale(1.05); } 100% { opacity: 1; transform: scale(1); } }
        .card-body-custom { padding: 0 30px 30px; border-top: 1px solid transparent; }
        .show .card-body-custom { border-top-color: #f1f5f9; padding-top: 20px; }

        /* --- LIST INFORMASI KAKAP --- */
        .info-list { padding: 0; margin: 0 0 25px 0; list-style: none; }
        .list-row { display: flex; align-items: flex-start; padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 0.95rem; line-height: 1.5; }
        .list-row:last-child { border-bottom: none; }
        .list-label { width: 140px; font-weight: 600; color: #64748b; flex-shrink: 0; }
        .list-colon { width: 15px; font-weight: 700; color: #0f172a; flex-shrink: 0; }
        .list-value { flex: 1; font-weight: 700; color: #0f172a; word-wrap: break-word; }

        /* --- EXTRA STYLES --- */
        .color-badge { display: inline-block; padding: 4px 12px; border-radius: 8px; font-size: 1.05rem; font-weight: 800; letter-spacing: -0.5px; }
        .private-note-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 15px; margin-top: 20px; }
        .private-note-box textarea { width: 100%; border: none; background: transparent; resize: none; font-size: 0.9rem; color: #92400e; font-weight: 600; outline: none; }
        .private-note-box textarea::placeholder { color: #d97706; opacity: 0.6; }
        
        /* --- MEDIA CAROUSEL (FOTO/VIDEO TUGAS) --- */
        .media-carousel { border-radius: 16px; overflow: hidden; margin-bottom: 25px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .media-carousel .carousel-item img, .media-carousel .carousel-item video { 
            width: 100%; 
            max-height: 300px; /* Batas tinggi maksimal gambar */
            object-fit: contain; /* Biar gambar gak kepotong/gepeng */
        }
        
        .prop-pills-wrapper { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;}
        .prop-pill { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 6px 16px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        
        .action-buttons { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
        .btn-outline-dark-custom { background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 20px; font-weight: 700; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; transition: 0.2s; }
        .btn-outline-dark-custom:hover { background: #f8fafc; border-color: #0f172a; }
        .btn-outline-dark-custom i { margin-right: 8px; font-size: 1.1rem; color: #2563eb; }
        .btn-dark-custom { background: #0f172a; color: #ffffff; border: 1px solid #0f172a; }
        .btn-dark-custom:hover { background: #1e293b; color: #ffffff; cursor: pointer;}
        
        /* --- DESKRIPSI TUGAS (UPDATE CLEAN UI) --- */
        .desc-box { 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            border-radius: 16px; 
            padding: 15px 20px; 
            margin-bottom: 25px;
        }
        .desc-title { 
            font-size: 0.75rem; 
            font-weight: 800; 
            color: #64748b; 
            text-transform: uppercase; 
            margin-bottom: 10px; 
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 8px;
        }
        .ql-editor { padding: 0 !important; min-height: auto !important; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.95rem; color: #334155; line-height: 1.6;}

        .time-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 25px; }
        .time-progress-container { background: #f1f5f9; border-radius: 50px; height: 8px; overflow: hidden; margin: 15px 0; }
        .time-progress-bar { height: 100%; width: 100%; border-radius: 50px; background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b); background-size: 300% 100%; animation: gradientMove 4s ease infinite; }
        
        .absensi-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-top: 15px; }
        .progress-mhs { height: 8px; border-radius: 10px; background: #e2e8f0; margin: 10px 0 20px; overflow: hidden; }
        .progress-mhs-bar { height: 100%; background: #10b981; transition: width 1s ease-in-out; }
        .history-list { max-height: 200px; overflow-y: auto; margin-top: 15px; }
        .history-item { display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px dashed #e2e8f0; font-size: 0.85rem; }
        .history-item:last-child { border-bottom: none; }

        @media (max-width: 768px) {
            .desktop-filter-wrapper { display: none; }
            .mobile-bottom-nav { display: flex; }
            .view-list { padding-left: 30px; }
            .view-list::before { left: 10px; width: 3px; }
            .view-list .task-card::before { left: -26px; width: 15px; height: 15px; border-width: 3px; }
            .card-header-toggle { padding: 20px; }
            .card-body-custom { padding: 0 20px 20px; }
            .pin-btn { top: 20px; right: 20px; }
            .controls-group { width: 100%; justify-content: space-between; }
            .list-label { width: 110px; font-size: 0.9rem;}
            .list-value { font-size: 0.9rem; }
            .header-modern { padding: 35px 0 25px; border-radius: 0 0 25px 25px; }
        }
    </style>
</head>
<body>

    <datalist id="list-mahasiswa">
        <?php foreach($mahasiswa_list as $mhs) echo "<option value=\"$mhs\">"; ?>
    </datalist>

    <div class="header-modern">
        <div class="container position-relative" style="z-index: 2;">
            <span class="badge bg-light bg-opacity-25 text-white border border-light border-opacity-25 rounded-pill px-3 py-1 mb-3 fw-bold" style="font-size: 0.7rem; letter-spacing: 1.5px;">WELCOME</span>
            <h2 class="fw-bolder mb-2" style="letter-spacing: -0.5px;">Daftar Tugas Perkuliahan</h2>
            <p class="opacity-75 mb-0 fw-bold font-monospace" style="font-size: 0.85rem;">Program Studi Ilmu Komputer - PSIK 23 D</p>
        </div>
    </div>
    

    <div class="container dashboard-wrapper mb-2">
        <div class="row justify-content-center g-3">
            <div class="col-6 col-md-3">
                <div class="dash-card">
                    <div class="dash-icon bg-red-light"><i class="fas fa-fire"></i></div>
                    <div><div class="dash-title">Tugas Aktif</div><div class="dash-value"><?= $dash_aktif ?></div></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dash-card">
                    <div class="dash-icon bg-blue-light"><i class="fas fa-check-double"></i></div>
                    <div><div class="dash-title">Full Selesai</div><div class="dash-value"><?= $dash_selesai ?></div></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="dash-card h-100">
                    <div class="dash-icon bg-green-light"><i class="fas fa-chart-pie"></i></div>
                    <div class="w-100">
                        <div class="d-flex justify-content-between">
                            <span class="dash-title">Progress Kelas</span>
                            <span class="fw-bolder text-dark" style="font-size: 0.8rem;"><?= number_format($dash_persen,1) ?>%</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $dash_persen ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-3">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #ffffff; overflow: hidden;">
                    <div class="card-header bg-white border-bottom border-light p-3 px-4 d-flex align-items-center">
                        <i class="fas fa-trophy text-warning me-2 fs-5"></i> 
                        <h6 class="fw-bolder text-dark m-0" style="letter-spacing: -0.5px;">Top 5 Mahasiswa Paling Rajin</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if(empty($top_mhs)): ?>
                            <div class="text-center p-4 text-muted small fst-italic">Belum ada data pengerjaan tugas...</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php 
                                $rank = 1;
                                foreach($top_mhs as $nama => $total): 
                                    // Logika Visual Ranking
                                    if ($rank == 1) {
                                        $medal = '<i class="fas fa-medal fs-3 text-warning"></i>';
                                        $bg = 'background: linear-gradient(to right, rgba(253, 224, 71, 0.15), transparent); border-left: 4px solid #facc15;';
                                        $txt_rank = 'text-warning-emphasis';
                                    } elseif ($rank == 2) {
                                        $medal = '<i class="fas fa-medal fs-4 text-secondary"></i>';
                                        $bg = 'background: linear-gradient(to right, rgba(148, 163, 184, 0.1), transparent); border-left: 4px solid #94a3b8;';
                                        $txt_rank = 'text-secondary';
                                    } elseif ($rank == 3) {
                                        $medal = '<i class="fas fa-medal fs-4" style="color: #d97706;"></i>';
                                        $bg = 'background: linear-gradient(to right, rgba(217, 119, 6, 0.1), transparent); border-left: 4px solid #d97706;';
                                        $txt_rank = 'text-dark opacity-75';
                                    } else {
                                        $medal = '<span class="badge bg-light text-muted border px-2 py-1 fs-6 rounded-circle">'.$rank.'</span>';
                                        $bg = 'border-left: 4px solid transparent;';
                                        $txt_rank = 'text-muted';
                                    }
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3" style="<?= $bg ?>">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width: 35px; text-align: center;"><?= $medal ?></div>
                                        <div>
                                            <div class="fw-bolder <?= $rank <= 3 ? 'text-dark' : 'text-secondary' ?> mb-1" style="font-size: 0.95rem;"><?= htmlspecialchars($nama) ?></div>
                                            <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill" style="font-size: 0.7rem;">
                                                <i class="fas fa-check-circle me-1"></i><?= $total ?>x Mengerjakan Tugas
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end d-none d-sm-block">
                                        <span class="fw-bold <?= $txt_rank ?>" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Rank #<?= $rank ?></span>
                                    </div>
                                </li>
                                <?php $rank++; endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mt-4 mb-1">
        <a href="https://psik23d.kesug.com/jadwal_kelas" class="text-decoration-none fw-bold teks-jadwal-kedip">
            sudah lihat daftar tugas? yuk lihat jadwal kelas
        </a>
    </div>
 
    <style>
        .teks-jadwal-kedip {
            color: #0d6efd; /* Warna biru URL */
            font-size: 0.85rem;
            animation: kedip-2detik 2s infinite ease-in-out;
        }
        @keyframes kedip-2detik {
            0% { opacity: 1; }
            50% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
                <div class="toolbar-wrapper">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" oninput="applySortAndFilter()" placeholder="Cari mata kuliah, jenis, atau dosen...">
                    </div>
                    <div class="controls-group">
                        <select id="sortSelect" class="form-select form-select-sm rounded-pill fw-bold border-secondary" style="width: auto; padding-right: 30px;" onchange="applySortAndFilter()">
                            <option value="deadline">📅 Deadline Terdekat</option>
                            <option value="sks">⚖️ SKS Terbesar</option>
                            <option value="abjad">🔤 Abjad A - Z</option>
                        </select>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary active btn-sm" id="btn-list" onclick="setView('list')"><i class="fas fa-list"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-grid" onclick="setView('grid')"><i class="fas fa-th-large"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="desktop-filter-wrapper d-none d-md-flex">
        <button class="filter-btn active" data-filter="Semua" onclick="filterTask('Semua', this)">Semua Tugas</button>
        <button class="filter-btn" data-filter="Aktif" onclick="filterTask('Aktif', this)">Tugas Aktif</button>
        <button class="filter-btn" data-filter="Selesai" onclick="filterTask('Selesai', this)">Selesai</button>
        <button class="filter-btn" data-filter="Arsip" onclick="filterTask('Arsip', this)">Arsip</button>
    </div>

    <nav class="mobile-bottom-nav d-md-none">
        <a class="nav-item-mobile active" data-filter="Semua" onclick="filterTask('Semua', this)"><i class="fas fa-layer-group"></i><span>Semua</span></a>
        <a class="nav-item-mobile" data-filter="Aktif" onclick="filterTask('Aktif', this)"><i class="fas fa-fire"></i><span>Aktif</span></a>
        <a class="nav-item-mobile" data-filter="Selesai" onclick="filterTask('Selesai', this)"><i class="fas fa-check-circle"></i><span>Selesai</span></a>
        <a class="nav-item-mobile" data-filter="Arsip" onclick="filterTask('Arsip', this)"><i class="fas fa-archive"></i><span>Arsip</span></a>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div id="noDataMessage" class="mb-4 d-none">
                    </div>

                <div id="taskContainer" class="task-container view-list">
                    <?php if(mysqli_num_rows($q_tugas) == 0): ?>
                        <div class="text-center py-5 mt-3"><h5 class="fw-bold text-muted opacity-50">Belum ada tugas saat ini.</h5></div>
                    <?php endif; ?>

                    <?php 
                    while($t = mysqli_fetch_assoc($q_tugas)): 
                        $id_tugas = $t['id'];
                        
                        $list_selesai = isset($checklist_data[$id_tugas]) ? $checklist_data[$id_tugas] : [];
                        $total_selesai = count($list_selesai);
                        $persen_selesai = ($total_mhs > 0) ? ($total_selesai / $total_mhs) * 100 : 0;
                        
                        $foto_dosen = !empty($t['foto_dosen']) ? '../assets/img/dosen/'.$t['foto_dosen'] : 'https://ui-avatars.com/api/?name='.urlencode($t['nama_dosen']).'&background=random';
                        $media_files = !empty($t['file_media']) ? explode(',', $t['file_media']) : [];
                        $val_tol = ($t['toleransi_waktu'] > 0) ? "Ya ({$t['toleransi_waktu']} Mnt)" : "Tidak";

                        $start_ts = strtotime($t['waktu_mulai']) * 1000;
                        $end_ts = strtotime($t['waktu_selesai']) * 1000;
                        $now_ts = time() * 1000;
                        
                        $diff_sec = ($end_ts - $now_ts) / 1000;
                        $is_fire = ($diff_sec > 0 && $diff_sec <= 172800);
                        $is_expired = ($diff_sec <= 0);
                        $is_archived = ($diff_sec <= -(7 * 24 * 60 * 60)); // <= Minus 7 Hari
                        
                        // LOGIKA BARU SESUAI PERMINTAAN
                        $status_tugas = 'Aktif';
                        if ($is_archived) {
                            $status_tugas = 'Arsip';
                        } elseif ($is_expired) {
                            $status_tugas = 'Selesai';
                        } elseif ($total_selesai == $total_mhs && $total_mhs > 0) {
                            $status_tugas = 'Selesai';
                        }
                        
                        $searchable_text = strtolower($t['mata_kuliah'] . " " . $t['tipe_tugas'] . " " . $t['nama_dosen']);
                        
                        // Color Code Generation
                        $hash_idx = crc32($t['mata_kuliah']) % count($pastel_bg);
                        $bg_tag = $pastel_bg[$hash_idx];
                        $txt_tag = $pastel_text[$hash_idx];
                    ?>
                    
                    <div class="task-card task-item" id="task-<?= $id_tugas ?>" 
                         data-id="<?= $id_tugas ?>" 
                         data-status="<?= $status_tugas ?>" 
                         data-search="<?= $searchable_text ?>" 
                         data-end-ts="<?= $end_ts ?>" 
                         data-sks="<?= $t['bobot_sks'] ?>" 
                         data-mk="<?= htmlspecialchars($t['mata_kuliah']) ?>"
                         style="order: 0;">
                        
                        <i class="fas fa-star pin-btn" id="pin-icon-<?= $id_tugas ?>" onclick="togglePin(event, '<?= $id_tugas ?>')"></i>

                        <div class="card-header-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#detailTugas<?= $id_tugas ?>">
                            <div class="w-100 pe-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="fw-bolder text-primary" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;"><?= $t['tipe_tugas'] ?></span>
                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.65rem;">#<?= $t['id_tugas_acak'] ?></span>
                                </div>
                                
                                <div class="color-badge mb-2" style="color: <?= $txt_tag ?>; background: <?= $bg_tag ?>;"><?= $t['mata_kuliah'] ?></div>
                                
                                <div class="text-muted small fw-bold mb-3 d-flex align-items-center gap-2">
                                    <img src="<?= $foto_dosen ?>" style="width: 22px; height: 22px; border-radius: 50%; object-fit: cover;">
                                    <?= $t['nama_dosen'] ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light border-opacity-50">
                                   <div>
    <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 800; text-transform: uppercase;">Deadline</div>
    <div class="fw-bolder text-danger" style="font-size: 0.85rem;">
        <i class="far fa-clock me-1"></i>
        <?php 
            $hari_en = date('l', strtotime($t['waktu_selesai']));
            $hari_id = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
            echo $hari_id[$hari_en] . ', ' . date('d M, H:i', strtotime($t['waktu_selesai']));
        ?>
    </div>
</div>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if($is_expired): ?>
                                            <span class="badge bg-secondary">Berakhir</span>
                                        <?php elseif($is_fire): ?>
                                            <span class="text-danger fw-bolder" style="font-size: 0.75rem; animation: pulseFire 1s infinite;">🔥 Segera!</span>
                                        <?php else: ?>
                                            <span class="text-success fw-bolder" style="font-size: 0.75rem;">🍃 Aman</span>
                                        <?php endif; ?>
                                        <div class="text-muted ms-2 border-start ps-3"><i class="fas fa-chevron-down toggle-icon"></i></div>
                                    </div>
                                </div>
                                
                                <?php 
                                    $nama_selesai_arr = array_column($list_selesai, 'nama');
                                    // Gunakan base64_encode agar super aman dari error tanda kutip (quotes) di JS
                                    $nama_encoded = base64_encode(json_encode($nama_selesai_arr));
                                ?>
                                <div class="mt-3 p-2 rounded-3 text-start d-flex align-items-center" style="background: #f0fdf4; border: 1px dashed #bbf7d0;">
                                    <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                                    <div style="font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
                                        <span class="fw-bold text-success opacity-75"></span>
                                        <span class="fw-bolder text-dark ticker-nama" style="transition: opacity 0.4s ease-in-out; display: inline-block;" data-namas="<?= $nama_encoded ?>">
                                            <?= count($nama_selesai_arr) > 0 ? htmlspecialchars($nama_selesai_arr[0]) : '<span class="text-muted fst-italic">Belum ada</span>' ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if(!$is_expired): ?>
                                <div style="height: 4px; border-radius: 5px; background: #e2e8f0; overflow: hidden; margin-top: 15px;">
                                    <div class="time-progress-bar" id="header-bar-<?= $id_tugas ?>" data-start="<?= $start_ts ?>" data-end="<?= $end_ts ?>"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="detailTugas<?= $id_tugas ?>" class="collapse">
                            <div class="card-body-custom">
                                
                                <div class="private-note-box mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span style="font-size: 0.75rem; font-weight: 800; color: #b45309; text-transform: uppercase;"><i class="fas fa-lock me-1"></i>Catatan Pribadiku</span>
                                        <span style="font-size: 0.65rem; color: #d97706;">Tersimpan di HP ini saja</span>
                                    </div>
                                    <textarea id="note-<?= $id_tugas ?>" rows="2" placeholder="Ketik catatan atau pengingat untuk tugas ini..." oninput="saveNote('<?= $id_tugas ?>')"></textarea>
                                </div>

                                <?php if(count($media_files) > 0): ?>
                                <div id="carouselTugas<?= $id_tugas ?>" class="carousel slide media-carousel mb-3" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php foreach($media_files as $idx => $file): 
                                            $active = ($idx == 0) ? 'active' : '';
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        ?>
                                            <div class="carousel-item <?= $active ?>">
                                                <?php if(in_array($ext, ['mp4','webm','ogg'])): ?>
                                                    <video src="../assets/img/tugas/<?= $file ?>" controls autoplay muted loop></video>
                                                <?php else: ?>
                                                    <img src="../assets/img/tugas/<?= $file ?>" alt="Media Tugas">
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="ql-snow mb-4"><div class="ql-editor task-subject"><?= $t['subject'] ?></div></div>

                                <ul class="info-list">
                                    <li class="list-row">
                                        <div class="list-label">ID Tugas</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value text-muted font-monospace">#<?= $t['id_tugas_acak'] ?></div>
                                    </li>
                                    <li class="list-row">
                                        <div class="list-label">Nama MK</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value text-primary"><?= $t['mata_kuliah'] ?></div>
                                    </li>
                                    <li class="list-row">
                                        <div class="list-label">Kode MK</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value"><?= $t['kode_mk'] ?></div>
                                    </li>
                                    <li class="list-row">
                                        <div class="list-label">Bobot</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value"><?= $t['bobot_sks'] ?> SKS</div>
                                    </li>
                                    <li class="list-row">
                                        <div class="list-label">Semester</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value"><?= $t['semester'] ?></div>
                                    </li>
                                    <li class="list-row align-items-center">
                                        <div class="list-label">Dosen Pengampu</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value d-flex align-items-center gap-2">
                                            <img src="<?= $foto_dosen ?>" style="width:26px; height:26px; border-radius:50%; object-fit:cover;">
                                            <?= $t['nama_dosen'] ?>
                                        </div>
                                    </li>
                                    <li class="list-row">
                                        <div class="list-label">Jenis Tugas</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value"><?= $t['tipe_tugas'] ?></div>
                                    </li>
                                    <li class="list-row">
                                        <div class="list-label">Tugas Ke-</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value"><?= $t['tugas_ke'] ?></div>
                                    </li>
                                    <li class="list-row">
                                        <div class="list-label">Status Tugas</div>
                                        <div class="list-colon">:</div>
                                        <div class="list-value text-primary"><?= $status_tugas ?></div>
                                    </li>
                                </ul>

                                <div class="prop-pills-wrapper">
                                    <div class="prop-pill"><span>Individu:</span> <?= ($t['jenis_tugas'] == 'Individu') ? 'Ya' : 'Tidak' ?></div>
                                    <div class="prop-pill"><span>Kelompok:</span> <?= ($t['jenis_tugas'] == 'Kelompok') ? 'Ya' : 'Tidak' ?></div>
                                    <div class="prop-pill"><span>Sifat:</span> <?= $t['sifat_tugas'] ?></div>
                                    <div class="prop-pill"><span>Toleransi Waktu:</span> <?= $val_tol ?></div>
                                    <div class="prop-pill"><span>Upload File:</span> <?= $t['butuh_upload'] ?></div>
                                </div>

                                <div class="action-buttons">
                                    <?php if(!empty($t['lampiran_tipe'])): ?>
                                        <a href="../assets/img/tugas/<?= $t['lampiran_tipe'] ?>" target="_blank" class="btn-outline-dark-custom"><i class="fas fa-file-alt"></i> Soal</a>
                                    <?php endif; ?>
                                    <?php if(!empty($t['link_lampiran_tipe'])): ?>
                                        <a href="<?= $t['link_lampiran_tipe'] ?>" target="_blank" class="btn-outline-dark-custom"><i class="fas fa-link"></i> Link Soal</a>
                                    <?php endif; ?>
                                    <?php if($t['ada_referensi'] == 'Ya'): ?>
                                        <?php if(!empty($t['file_referensi'])): ?>
                                            <a href="../assets/img/tugas/<?= $t['file_referensi'] ?>" target="_blank" class="btn-outline-dark-custom"><i class="fas fa-book-open"></i> PDF Materi</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php 
                                        $cal_title = urlencode("Tugas: " . $t['mata_kuliah']);
                                        $cal_desc = urlencode("Subject: " . $t['subject'] . "\nID: #" . $t['id_tugas_acak']);
                                        $d1 = gmdate('Ymd\THis\Z', strtotime($t['waktu_selesai']) - 3600);
                                        $d2 = gmdate('Ymd\THis\Z', strtotime($t['waktu_selesai']));
                                        $gcal_url = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$cal_title}&details={$cal_desc}&dates={$d1}/{$d2}";
                                    ?>
                                    <a href="<?= $gcal_url ?>" target="_blank" class="btn-outline-dark-custom btn-dark-custom"><i class="fas fa-calendar-check"></i> Ingatkan Saya</a>
                                </div>

                                <?php if(!empty($t['deskripsi']) && $t['deskripsi'] != '<p><br></p>'): ?>
                                    <div class="desc-box mt-4">
                                        <div class="desc-title"><i class="fas fa-align-left text-primary me-2"></i> Catatan Tambahan</div>
                                        <div class="ql-snow"><div class="ql-editor"><?= $t['deskripsi'] ?></div></div>
                                    </div>
                                <?php endif; ?>
                                <div class="time-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bolder text-muted text-uppercase">Sisa Waktu</span>
                                        <span class="fs-5 fw-bolder text-dark font-monospace lh-1" id="text-timer-<?= $id_tugas ?>">Menghitung...</span>
                                    </div>
                                    <div class="time-progress-container"><div id="bar-timer-<?= $id_tugas ?>" class="time-progress-bar" data-start="<?= $start_ts ?>" data-end="<?= $end_ts ?>"></div></div>
                                    <div class="text-end"><span class="fs-6 fw-bold text-muted font-monospace" id="persen-timer-<?= $id_tugas ?>">100.00%</span></div>
                                </div>

                                <div id="link-area-<?= $id_tugas ?>">
                                    <?php if($t['butuh_upload'] == 'Ya' && !empty($t['link_pengumpulan'])): ?>
                                        <a href="<?= $t['link_pengumpulan'] ?>" target="_blank" class="btn btn-primary w-100 rounded-pill fw-bold py-3 mb-4 shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%); border: none;">
                                            <i class="fas fa-cloud-upload-alt me-2"></i>Kumpulkan di <?= $t['tempat_pengumpulan'] ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-2 border-top pt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="fw-bolder text-dark mb-1 fs-5">Checklist Kelas</div>
                                        <button class="btn btn-outline-dark btn-sm rounded-pill fw-bold px-4" type="button" data-bs-toggle="collapse" data-bs-target="#meja-<?= $id_tugas ?>">Buka Meja</button>
                                    </div>
                                    
                                    <div class="collapse" id="meja-<?= $id_tugas ?>">
                                        <div class="absensi-box">
                                            <div class="d-flex justify-content-between align-items-end mb-2">
                                                <div class="fw-bolder" style="font-size: 0.9rem;">Progress Kelas</div>
                                                <div class="fw-bold text-primary" style="font-size: 0.85rem;"><span id="angka-selesai-<?= $id_tugas ?>"><?= $total_selesai ?></span>/<?= $total_mhs ?> Selesai</div>
                                            </div>
                                            <div class="progress-mhs">
                                                <div class="progress-mhs-bar" id="bar-selesai-<?= $id_tugas ?>" style="width: <?= $persen_selesai ?>%;"></div>
                                            </div>
                                            
                                            <div class="input-group mb-3 shadow-sm mt-3">
                                                <input type="text" id="input-nama-<?= $id_tugas ?>" list="list-mahasiswa" class="form-control" placeholder="Ketik nama untuk absen..." style="border-radius: 10px 0 0 10px; border-color: #cbd5e1; font-weight: bold;">
                                                <button class="btn btn-dark fw-bold" type="button" onclick="submitAbsen('<?= $id_tugas ?>')" style="border-radius: 0 10px 10px 0;">Selesai</button>
                                            </div>

                                            <div class="history-list" id="history-<?= $id_tugas ?>">
                                                <?php foreach($list_selesai as $s): ?>
                                                <div class="history-item">
                                                    <span class="fw-bold text-dark"><i class="fas fa-check-circle text-success me-2"></i><?= $s['nama'] ?></span>
                                                    <span class="text-muted font-monospace"><?= date('H:i', strtotime($s['waktu'])) ?></span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // ==========================================
        // 1. DATA LOKAL: PIN & NOTES
        // ==========================================
        let pinnedTasks = JSON.parse(localStorage.getItem('psik_pinned_tasks')) || [];
        let privateNotes = JSON.parse(localStorage.getItem('psik_private_notes')) || {};

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.task-item').forEach(card => {
                let tid = card.getAttribute('data-id');
                if (pinnedTasks.includes(tid)) {
                    card.classList.add('is-pinned');
                }
                let noteInput = document.getElementById('note-' + tid);
                if (noteInput && privateNotes[tid]) {
                    noteInput.value = privateNotes[tid];
                }
            });
            applySortAndFilter(); 
        });

        function togglePin(e, tid) {
            e.stopPropagation(); 
            let card = document.getElementById('task-' + tid);
            
            if (pinnedTasks.includes(tid)) {
                pinnedTasks = pinnedTasks.filter(id => id !== tid);
                card.classList.remove('is-pinned');
            } else {
                pinnedTasks.push(tid);
                card.classList.add('is-pinned');
            }
            localStorage.setItem('psik_pinned_tasks', JSON.stringify(pinnedTasks));
            applySortAndFilter(); 
        }

        function saveNote(tid) {
            let val = document.getElementById('note-' + tid).value;
            privateNotes[tid] = val;
            localStorage.setItem('psik_private_notes', JSON.stringify(privateNotes));
        }

        // ==========================================
        // 2. FILTER, SORTING, & VIEW TOGGLE
        // ==========================================
        let currentStatus = 'Semua';

        function filterTask(status, btn) {
            document.querySelectorAll('.desktop-filter-wrapper .filter-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.mobile-bottom-nav .nav-item-mobile').forEach(b => b.classList.remove('active'));
            if(btn && btn.classList.contains('nav-item-mobile')) btn.classList.add('active');
            else if(btn) btn.classList.add('active');
            
            currentStatus = status;
            applySortAndFilter();
        }

        function setView(mode) {
            let container = document.getElementById('taskContainer');
            if(mode === 'grid') {
                container.classList.remove('view-list');
                container.classList.add('view-grid');
                document.getElementById('btn-grid').classList.add('active');
                document.getElementById('btn-list').classList.remove('active');
            } else {
                container.classList.remove('view-grid');
                container.classList.add('view-list');
                document.getElementById('btn-list').classList.add('active');
                document.getElementById('btn-grid').classList.remove('active');
            }
        }

        function applySortAndFilter() {
            let keyword = document.getElementById('searchInput').value.toLowerCase();
            let sortType = document.getElementById('sortSelect').value;
            let container = document.getElementById('taskContainer');
            let cards = Array.from(container.querySelectorAll('.task-item'));
            let countVisible = 0;

            // FILTERING
            cards.forEach(card => {
                let matchStatus = (currentStatus === 'Semua' || card.getAttribute('data-status') === currentStatus);
                let matchSearch = card.getAttribute('data-search').includes(keyword);
                if (matchStatus && matchSearch) {
                    card.style.display = 'block';
                    countVisible++;
                } else {
                    card.style.display = 'none';
                }
            });

            // SORTING
            cards.sort((a, b) => {
                let pinA = a.classList.contains('is-pinned') ? 1 : 0;
                let pinB = b.classList.contains('is-pinned') ? 1 : 0;
                if (pinA !== pinB) return pinB - pinA;

                if (sortType === 'deadline') {
                    return parseFloat(a.getAttribute('data-end-ts')) - parseFloat(b.getAttribute('data-end-ts'));
                } else if (sortType === 'sks') {
                    return parseFloat(b.getAttribute('data-sks')) - parseFloat(a.getAttribute('data-sks'));
                } else if (sortType === 'abjad') {
                    return a.getAttribute('data-mk').localeCompare(b.getAttribute('data-mk'));
                }
            });

            cards.forEach((card, index) => {
                card.style.order = index;
            });

            // PESAN ALERT KECIL & RAPAT (PERBAIKAN REVISI)
            let msgBox = document.getElementById('noDataMessage');
            if (countVisible === 0) {
                msgBox.innerHTML = '<h5 class="fw-bold text-muted opacity-50 text-center py-4">Tugas tidak ditemukan.</h5>';
                msgBox.classList.remove('d-none');
            } else if (currentStatus !== 'Semua') {
                let pesan = currentStatus === 'Aktif' ? `🔥 Menampilkan ${countVisible} Tugas Aktif` : 
                            currentStatus === 'Selesai' ? `✅ Menampilkan ${countVisible} Tugas Selesai` : 
                            `🗄️ Menampilkan ${countVisible} Tugas Arsip`;
                msgBox.innerHTML = `<div class="alert alert-primary fw-bold text-center border-0 shadow-sm py-2 px-4 mx-auto" style="border-radius: 50px; background: #e0f2fe; color: #0369a1; font-size: 0.85rem; width: max-content;">${pesan}</div>`;
                msgBox.classList.remove('d-none');
            } else {
                msgBox.classList.add('d-none');
            }
        }

        // ==========================================
        // 3. COUNTDOWN & REALTIME BAR
        // ==========================================
        function updateRealtimeBars() {
            const now = Date.now(); 
            document.querySelectorAll('.time-progress-bar').forEach(bar => {
                const start = parseInt(bar.getAttribute('data-start'));
                const end = parseInt(bar.getAttribute('data-end'));
                const id = bar.id.split('-').pop(); 
                
                const textTimer = document.getElementById('text-timer-' + id);
                const textPersen = document.getElementById('persen-timer-' + id);

                if (now >= end) {
                    bar.style.width = '0%';
                    bar.style.background = '#e2e8f0';
                    if(textPersen) textPersen.innerText = '0.00%';
                    if(textTimer) { textTimer.innerText = 'WAKTU HABIS'; textTimer.className = 'fs-5 fw-bolder text-danger font-monospace lh-1'; }
                } else if (now <= start) {
                    bar.style.width = '100%';
                } else {
                    const totalDuration = end - start;
                    const timeLeft = end - now;
                    let percentage = (timeLeft / totalDuration) * 100;
                    bar.style.width = percentage + '%';
                    if(textPersen) textPersen.innerText = percentage.toFixed(2) + '%'; 

                    if(textTimer) {
                        const d = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                        const h = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const m = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                        const s = Math.floor((timeLeft % (1000 * 60)) / 1000);
                        let str = d > 0 ? `${d}h ${h}j ${m}m ${s}s` : `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`; 
                        textTimer.innerText = str;
                    }
                }
            });
            requestAnimationFrame(updateRealtimeBars);
        }
        requestAnimationFrame(updateRealtimeBars);

        // ==========================================
        // 4. SUBMIT ABSENSI PINTAR & GAMIFIKASI
        // ==========================================
        function submitAbsen(tid) {
            const inputEl = document.getElementById(`input-nama-${tid}`);
            const nama = inputEl.value.trim();
            const totalMhs = <?= $total_mhs ?>;

            if(nama === '') { Swal.fire('Oops!','Ketik namamu dulu bro!','warning'); return; }

            const fd = new FormData();
            fd.append('action', 'add_checklist');
            fd.append('tugas_id', tid);
            fd.append('nama_mhs', nama);

            inputEl.disabled = true;

            fetch(window.location.href, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                inputEl.disabled = false;
                if(data.status === 'success') {
                    let angkaEl = document.getElementById(`angka-selesai-${tid}`);
                    let barEl = document.getElementById(`bar-selesai-${tid}`);
                    let current = parseInt(angkaEl.innerText) + 1;
                    
                    angkaEl.innerText = current;
                    barEl.style.width = ((current / totalMhs) * 100) + '%';

                    let historyEl = document.getElementById(`history-${tid}`);
                    let newRow = `<div class="history-item text-primary" style="background:#f0f9ff; animation: pulseFire 1s;">
                                    <span class="fw-bold"><i class="fas fa-check-circle text-success me-2"></i>${nama}</span>
                                    <span class="font-monospace fw-bold">${data.waktu}</span>
                                  </div>`;
                    historyEl.insertAdjacentHTML('afterbegin', newRow);
                    inputEl.value = ''; 

                    // TRIGGER GAMIFIKASI JIKA 100% KELAS SELESAI
                    if (current === totalMhs) {
                        confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 }, zIndex: 10000 });
                        Swal.fire({ title: 'GOKIL!', text: `Tugas ini resmi diselesaikan oleh semua mahasiswa!`, icon: 'success', timer: 3000 });
                    } else {
                        Swal.fire({ title: 'Sukses!', text: `Kamu (${nama}) sudah menandai tugas selesai.`, icon: 'success', timer: 1500, showConfirmButton: false });
                    }
                } else {
                    Swal.fire('Info', data.msg, 'info');
                }
            }).catch(err => {
                inputEl.disabled = false;
                Swal.fire('Error','Kesalahan jaringan bro.','error');
            });
        }

        // ==========================================
        // 5. PERBAIKAN: TICKER NAMA MAHASISWA 
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            const tickers = document.querySelectorAll('.ticker-nama');
            
            tickers.forEach(ticker => {
                try {
                    // Decode base64 dari PHP supaya aman dibaca JS
                    const encodedData = ticker.getAttribute('data-namas');
                    if (!encodedData) return;
                    
                    const decodedData = atob(encodedData);
                    const namas = JSON.parse(decodedData);
                    
                    if (namas && namas.length > 1) {
                        let idx = 0;
                        setInterval(() => {
                            // 1. Hilangkan teks (Fade Out)
                            ticker.style.opacity = '0'; 
                            
                            // 2. Ganti teks saat tidak terlihat, lalu munculkan lagi (Fade In)
                            setTimeout(() => {
                                idx = (idx + 1) % namas.length;
                                // Escape HTML untuk keamanan
                                const div = document.createElement('div');
                                div.innerText = namas[idx];
                                ticker.innerHTML = div.innerHTML; 
                                
                                ticker.style.opacity = '1'; 
                            }, 400); // Tunggu 400ms biar efek fade-outnya selesai dulu
                            
                        }, 2000); // Ganti tiap 2 Detik (Biar lebih enak dibaca matanya)
                    }
                } catch(e) {
                    console.error("Error parsing ticker data:", e);
                }
            });
        });
    </script>
</body>
</html>