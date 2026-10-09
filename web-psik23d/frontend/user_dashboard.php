<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

date_default_timezone_set('Asia/Jakarta');
$id_mhs = $_SESSION['id'];

// --- LOGIKA PHP (TETAP) ---

// 1. Ambil nama user
$query_user = mysqli_query($koneksi, "SELECT nama FROM users WHERE id='$id_mhs'");
$user_data = mysqli_fetch_assoc($query_user);
$nama_user = $user_data['nama'] ?? 'Mahasiswa';

// 2. Tentukan salam berdasarkan waktu
$jam = date('H');
if ($jam >= 5 && $jam < 11) {
    $salam = "Selamat pagi";
} elseif ($jam >= 11 && $jam < 15) {
    $salam = "Selamat siang";
} elseif ($jam >= 15 && $jam < 19) {
    $salam = "Selamat sore";
} else {
    $salam = "Selamat malam";
}

// 3. JADWAL
$hari_ini = date('N'); 
$nama_hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][$hari_ini % 7];
$q_jadwal = mysqli_query($koneksi, "SELECT * FROM jadwal WHERE hari='$nama_hari' ORDER BY jam_mulai ASC");

// 4. TUGAS DEADLINE (MAKS 3 TERDEKAT)
$q_tugas = mysqli_query($koneksi, "SELECT * FROM tugas 
                                   WHERE deadline >= NOW() 
                                   AND id NOT IN (SELECT id_tugas FROM tugas_submission WHERE id_mhs='$id_mhs')
                                   ORDER BY deadline ASC LIMIT 3");

// 5. PENGUMUMAN
$q_info = mysqli_query($koneksi, "SELECT * FROM announcements WHERE target_audience IN ('all', 'public') ORDER BY created_at DESC LIMIT 5");

// 6. MATERI TERBARU
$q_materi = mysqli_query($koneksi, "SELECT * FROM materials ORDER BY uploaded_at DESC LIMIT 5");

// --- LOGIKA GRAFIK ---
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tugas");
$total_tugas = mysqli_fetch_assoc($q_total)['total'];

$q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) as selesai FROM tugas_submission WHERE id_mhs='$id_mhs'");
$tugas_selesai = mysqli_fetch_assoc($q_selesai)['selesai'];

$q_lewat = mysqli_query($koneksi, "SELECT COUNT(*) as lewat FROM tugas 
                                   WHERE deadline < NOW() 
                                   AND id NOT IN (SELECT id_tugas FROM tugas_submission WHERE id_mhs='$id_mhs')");
$tugas_lewat = mysqli_fetch_assoc($q_lewat)['lewat'];

$tugas_belum = $total_tugas - $tugas_selesai - $tugas_lewat;
if($tugas_belum < 0) $tugas_belum = 0;
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

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
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
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --info-color: #06b6d4;
            --purple-gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            --sidebar-width: 280px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
            --danger-gradient: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            --info-color: #22d3ee;
            --purple-gradient: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ANIMATIONS */
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
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes heartbeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* TYPING ANIMATION */
        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50% { border-color: var(--primary-color); }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slide-in-right {
            animation: slideInRight 0.5s ease-out;
        }

        .animate-pulse-once {
            animation: pulse 0.3s ease-in-out;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-bounce {
            animation: bounce 2s ease-in-out infinite;
        }

        .animate-spin-slow {
            animation: spin 4s linear infinite;
        }

        .animate-heartbeat {
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        /* ICON ANIMATIONS */
        .icon-animate {
            transition: all 0.3s ease;
        }

        .icon-animate:hover {
            transform: scale(1.2) rotate(10deg);
        }

        /* TYPING TEXT STYLES */
        .typing-container {
            min-height: 2.5rem;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }

        .typing-text {
            display: inline-block;
            overflow: hidden;
            white-space: nowrap;
            border-right: 3px solid var(--primary-color);
            animation: typing 3s steps(30, end), blink-caret 0.75s step-end infinite;
            font-size: 1.875rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .typing-done {
            animation: none;
            border-right: none;
        }

        /* SIDEBAR */
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* HEADER */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .header-left {
            flex: 1;
        }

        .date-time {
            font-size: 0.875rem;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

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
            margin-left: 1rem;
        }

        .theme-toggle:hover {
            background: var(--bg-hover);
            transform: rotate(15deg);
        }

        /* STATS CARDS - DIUBAH MENJADI LEBIH MEWAH DENGAN ANIMASI ICON */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            opacity: 0.8;
        }

        .stat-card.danger::before {
            background: var(--danger-gradient);
        }

        .stat-card.success::before {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        }

        .stat-card.warning::before {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
        }

        .stat-card.purple::before {
            background: var(--purple-gradient);
        }

        .stat-icon-wrapper {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .stat-icon {
            font-size: 1.5rem;
            z-index: 2;
        }

        .stat-icon-wrapper::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 15px;
            opacity: 0.1;
            z-index: 1;
        }

        .stat-icon-wrapper.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
        }

        .stat-icon-wrapper.success::before {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        }

        .stat-icon-wrapper.primary {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.15) 100%);
        }

        .stat-icon-wrapper.primary::before {
            background: var(--primary-gradient);
        }

        .stat-icon-wrapper.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
        }

        .stat-icon-wrapper.danger::before {
            background: var(--danger-gradient);
        }

        .stat-icon-wrapper.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
        }

        .stat-icon-wrapper.warning::before {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
        }

        .stat-content h3 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            background: linear-gradient(135deg, var(--text-main) 0%, var(--text-sub) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-content p {
            color: var(--text-sub);
            font-size: 0.85rem;
            font-weight: 500;
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* DEADLINE CARD */
        .deadline-card {
            background: var(--bg-card);
            border-radius: 1.25rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            height: 100%;
        }

        .deadline-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-2px);
        }

        .deadline-header {
            background: var(--danger-gradient);
            color: white;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .deadline-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 150%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite linear;
        }

        .deadline-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .deadline-title h6 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .deadline-link {
            color: white;
            text-decoration: none;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
        }

        .deadline-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(3px);
        }

        .deadline-list {
            padding: 1.25rem;
        }

        .deadline-item {
            padding: 1rem;
            border-radius: 0.875rem;
            margin-bottom: 0.875rem;
            background: var(--bg-hover);
            border: 1px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .deadline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--danger-gradient);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .deadline-item:hover {
            border-color: var(--danger-color);
            background: var(--bg-card);
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .deadline-item:hover::before {
            opacity: 1;
        }

        .deadline-item-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .deadline-course {
            font-size: 0.75rem;
            color: var(--text-sub);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .deadline-task {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .deadline-timer {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            padding: 0.3rem 0.6rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            display: inline-block;
            transition: all 0.3s ease;
            min-width: 70px;
            text-align: center;
        }

        .deadline-timer.urgent {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
            color: var(--danger-color);
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        .deadline-timer.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
            color: var(--warning-color);
            animation: bounce 2s ease-in-out infinite;
        }

        .deadline-timer.safe {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
            color: var(--success-color);
        }

        /* SCHEDULE CARD */
        .schedule-card {
            background: var(--bg-card);
            border-radius: 1.25rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            height: 100%;
            transition: all 0.3s ease;
        }

        .schedule-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .schedule-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .schedule-header h6 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .day-badge {
            background: var(--primary-gradient);
            color: white;
            padding: 0.3rem 0.7rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            animation: pulse 2s infinite;
        }

        .schedule-item {
            padding: 1rem;
            border-radius: 0.875rem;
            margin-bottom: 0.875rem;
            background: var(--bg-hover);
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .schedule-item:hover {
            transform: translateX(5px);
            background: var(--bg-card);
            border-left-color: var(--danger-color);
            box-shadow: var(--shadow-md);
        }

        .schedule-time {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .schedule-time i {
            font-size: 0.7rem;
        }

        .schedule-course {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .schedule-room {
            font-size: 0.7rem;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ANNOUNCEMENTS & MATERIALS */
        .content-card {
            background: var(--bg-card);
            border-radius: 1.25rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            height: 100%;
            transition: all 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .content-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .content-header h6 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }

        .content-list {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .content-item {
            padding: 1rem;
            border-radius: 0.875rem;
            margin-bottom: 0.875rem;
            background: var(--bg-hover);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }

        .content-item:hover {
            transform: translateX(5px);
            background: var(--bg-card);
            border-color: var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .announcement-title {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .announcement-content {
            font-size: 0.75rem;
            color: var(--text-sub);
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .announcement-time {
            font-size: 0.65rem;
            color: var(--text-sub);
            opacity: 0.7;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .material-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.875rem;
            margin-bottom: 0.875rem;
            background: var(--bg-hover);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }

        .material-item:hover {
            transform: translateX(5px);
            background: var(--bg-card);
            border-color: var(--border-color);
            box-shadow: var(--shadow-md);
            color: inherit;
        }

        .material-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }

        .material-item:hover .material-icon {
            animation: bounce 1s ease-in-out infinite;
        }

        .material-content {
            flex: 1;
        }

        .material-title {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 0.25rem;
            line-height: 1.4;
        }

        .material-course {
            font-size: 0.7rem;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* MOBILE STYLES - DIPERBAIKI PADDING */
        .mobile-header {
            display: none;
            background: var(--bg-card);
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1040;
            box-shadow: var(--shadow-md);
            align-items: center;
            justify-content: space-between;
            height: 60px;
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
            font-size: 1.25rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-hover);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--text-sub);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 1199px) {
            :root {
                --sidebar-width: 260px;
            }
            
            .typing-text {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

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
                padding-top: 60px; /* Diperbaiki: lebih rapat ke atas */
            }
            
            .mobile-header {
                display: flex;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .typing-text {
                font-size: 1.25rem;
                white-space: normal;
            }
            
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                margin-top: 0.5rem; /* Diperbaiki: dikurangi margin */
                margin-bottom: 1.5rem;
            }
            
            .theme-toggle {
                margin-left: 0;
                align-self: flex-end;
                margin-top: -0.5rem; /* Diperbaiki: lebih rapat */
            }
            
            .row.g-4 {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }
            
            .col-lg-8, .col-lg-4 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .stat-card {
                padding: 1.25rem;
            }
            
            .stat-icon-wrapper {
                width: 50px;
                height: 50px;
            }
            
            .stat-content h3 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 767px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.875rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-content h3 {
                font-size: 1.3rem;
            }
            
            .schedule-card,
            .content-card,
            .deadline-card {
                padding: 1.25rem;
            }
            
            .deadline-header,
            .schedule-header,
            .content-header {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .deadline-list,
            .content-list {
                padding: 1rem;
            }
            
            .main-content {
                padding-top: 60px; /* Tetap rapat */
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .main-content {
                padding: 0.75rem;
                padding-top: 60px; /* Tetap rapat */
            }
            
            .date-time {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
                font-size: 0.75rem;
            }
            
            .typing-text {
                font-size: 1.1rem;
                animation-duration: 2s;
                border-right-width: 2px;
            }
            
            .typing-container {
                min-height: 2rem;
                margin-bottom: 0.25rem; /* Diperbaiki: lebih rapat */
            }
            
            .schedule-item,
            .content-item,
            .deadline-item,
            .material-item {
                padding: 0.875rem;
                margin-bottom: 0.625rem;
            }
            
            .nav-link {
                padding: 0.75rem 1rem;
                margin: 0.125rem 0.75rem;
                font-size: 0.875rem;
            }
            
            .sidebar-brand {
                padding: 1.25rem;
                font-size: 1.25rem;
            }
            
            .mobile-header {
                padding: 0.5rem 0.75rem;
                height: 56px;
            }
            
            .dashboard-header {
                margin-top: 0.25rem;
                margin-bottom: 1.25rem;
            }
        }

        @media (min-width: 992px) {
            /* Desktop styles */
            .mobile-header {
                display: none;
            }
            
            .menu-toggle {
                display: none;
            }
            
            .main-content {
                margin-left: var(--sidebar-width);
                padding-top: 2rem;
            }
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
        
        /* Loading animation for cards */
        .card-loading {
            background: linear-gradient(90deg, var(--bg-hover) 25%, var(--bg-card) 50%, var(--bg-hover) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>
</head>
<body>

    <!-- Mobile Header -->
    <div class="mobile-header d-lg-none">
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
            <i class="fas fa-graduation-cap animate-float"></i>
            <span>PSIK 23 D</span>
        </div>
        
        <div class="sidebar-menu mt-4 flex-grow-1">
            <div class="px-3 mb-4">
                <small class="text-uppercase fw-bold text-sub px-2 mb-2 d-block">Menu Utama</small>
                <a href="user_dashboard.php" class="nav-link active">
                    <i class="fas fa-house"></i>
                    <span>Dashboard</span>
                </a>
                <a href="jadwal_kelas.php" class="nav-link">
                    <i class="fas fa-calendar-day icon-animate"></i>
                    <span>Jadwal</span>
                </a>
                <a href="tugas_saya.php" class="nav-link">
                    <i class="fas fa-list-check icon-animate"></i>
                    <span>Tugas Saya</span>
                </a>
                <a href="materi_kuliah.php" class="nav-link">
                    <i class="fas fa-book-open icon-animate"></i>
                    <span>Materi</span>
                </a>
            </div>
            
            <div class="px-3 mb-4">
                <small class="text-uppercase fw-bold text-sub px-2 mb-2 d-block">Ekstra</small>
                <a href="kelola_berita.php" class="nav-link">
                    <i class="fas fa-pen-nib icon-animate"></i>
                    <span>Tulis Info</span>
                </a>
                <a href="user_profil.php" class="nav-link"> <i class="fas fa-user-edit icon-animate"></i>
                    <span>Kelola Profil</span>
                </a>
                <a href="edit_profil.php" class="nav-link">
                    <i class="fas fa-user-cog icon-animate"></i>
                    <span>Profil Saya</span>
                </a>
            </div>
            
            <div class="px-3 mt-auto mb-4">
                <a href="../backend/logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt icon-animate"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-left">
                <div class="typing-container">
                    <span class="typing-text" id="greetingText"></span>
                </div>
                <div class="date-time">
                    <i class="far fa-calendar"></i>
                    <span id="currentDate"><?= date('l, d F Y') ?></span>
                    •
                    <i class="far fa-clock"></i>
                    <span id="realtimeClock">00:00:00</span>
                </div>
            </div>
            <button class="theme-toggle d-none d-lg-flex" onclick="toggleTheme()">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
        </div>

        <!-- Stats Grid - DIPERBAIKI DENGAN ICON ANIMASI -->
        <div class="stats-grid">
            <div class="stat-card success animate-slide-in-right" style="animation-delay: 0.1s">
                <div class="stat-icon-wrapper success">
                    <i class="fas fa-check-circle stat-icon animate-heartbeat"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $tugas_selesai ?></h3>
                    <p>Tugas Selesai</p>
                </div>
            </div>
            
            <div class="stat-card animate-slide-in-right" style="animation-delay: 0.2s">
                <div class="stat-icon-wrapper primary">
                    <i class="fas fa-clock stat-icon animate-spin-slow"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $tugas_belum ?></h3>
                    <p>Belum Dikerjakan</p>
                </div>
            </div>
            
            <div class="stat-card danger animate-slide-in-right" style="animation-delay: 0.3s">
                <div class="stat-icon-wrapper danger">
                    <i class="fas fa-exclamation-triangle stat-icon animate-bounce"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $tugas_lewat ?></h3>
                    <p>Deadline Terlewat</p>
                </div>
            </div>
            
            <div class="stat-card warning animate-slide-in-right" style="animation-delay: 0.4s">
                <div class="stat-icon-wrapper warning">
                    <i class="fas fa-chalkboard-teacher stat-icon animate-float"></i>
                </div>
                <div class="stat-content">
                    <h3><?= mysqli_num_rows($q_jadwal) ?></h3>
                    <p>Kelas Hari Ini</p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <!-- Schedule -->
                    <div class="col-12">
                        <div class="schedule-card">
                            <div class="schedule-header">
                                <h6>
                                    <i class="fas fa-calendar-alt text-primary"></i>
                                    Jadwal Perkuliahan
                                    <span class="day-badge"><?= $nama_hari ?></span>
                                </h6>
                                <a href="jadwal_kelas.php" class="deadline-link text-primary">
                                    Lihat Semua
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                            <div class="content-list">
                                <?php if(mysqli_num_rows($q_jadwal) > 0): ?>
                                    <?php while($j = mysqli_fetch_assoc($q_jadwal)): ?>
                                        <div class="schedule-item">
                                            <div class="schedule-time">
                                                <i class="far fa-clock"></i>
                                                <?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?>
                                            </div>
                                            <div class="schedule-course"><?= $j['mata_kuliah'] ?></div>
                                            <div class="schedule-room">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?= $j['ruangan'] ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-coffee fa-2x mb-3 animate-float"></i>
                                        <p class="mb-0">Tidak ada kelas hari ini. Nikmati waktu luangmu!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Announcements -->
                    <div class="col-12">
                        <div class="content-card">
                            <div class="content-header">
                                <i class="fas fa-bullhorn text-warning animate-bounce"></i>
                                <h6>Pengumuman Terbaru</h6>
                            </div>
                            <div class="content-list">
                                <?php if(mysqli_num_rows($q_info) > 0): ?>
                                    <?php while($info = mysqli_fetch_assoc($q_info)): ?>
                                        <div class="content-item">
                                            <div class="announcement-title"><?= $info['title'] ?></div>
                                            <div class="announcement-content"><?= nl2br(substr($info['content'], 0, 150)) . (strlen($info['content']) > 150 ? '...' : '') ?></div>
                                            <div class="announcement-time">
                                                <i class="far fa-clock"></i>
                                                <?= date('d M Y, H:i', strtotime($info['created_at'])) ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted">
                                        <p class="mb-0">Belum ada pengumuman</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <div class="row g-4">
                    <!-- Deadline -->
                    <div class="col-12">
                        <div class="deadline-card">
                            <div class="deadline-header">
                                <div class="deadline-title">
                                    <h6><i class="fas fa-stopwatch me-2"></i>Deadline Mendatang</h6>
                                    <a href="tugas_saya.php" class="deadline-link">
                                        Lihat Semua
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                                <p class="mb-0 opacity-90">Tugas yang harus segera diselesaikan</p>
                            </div>
                            <div class="deadline-list">
                                <?php if(mysqli_num_rows($q_tugas) > 0): ?>
                                    <?php while($t = mysqli_fetch_assoc($q_tugas)): ?>
                                        <a href="tugas_saya.php" class="text-decoration-none">
                                            <div class="deadline-item">
                                                <div class="deadline-course"><?= $t['mata_kuliah'] ?></div>
                                                <div class="deadline-task"><?= $t['judul'] ?></div>
                                                <div class="deadline-item-content">
                                                    <small class="text-sub">
                                                        <i class="far fa-clock me-1"></i>
                                                        Deadline: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?>
                                                    </small>
                                                    <span class="deadline-timer" data-deadline="<?= $t['deadline'] ?>">
                                                        Loading...
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3 animate-heartbeat"></i>
                                        <h6 class="fw-bold">Tidak ada deadline</h6>
                                        <p class="text-sub mb-0">Semua tugas sudah dikerjakan!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Materials -->
                    <div class="col-12">
                        <div class="content-card">
                            <div class="content-header">
                                <i class="fas fa-book text-info animate-float"></i>
                                <h6>Materi Terbaru</h6>
                            </div>
                            <div class="content-list">
                                <?php if(mysqli_num_rows($q_materi) > 0): ?>
                                    <?php while($m = mysqli_fetch_assoc($q_materi)): ?>
                                        <a href="../assets/files/<?= $m['file'] ?>" target="_blank" class="material-item">
                                            <div class="material-icon">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <div class="material-content">
                                                <div class="material-title"><?= $m['judul'] ?></div>
                                                <div class="material-course">
                                                    <i class="fas fa-book-reader"></i>
                                                    <?= $m['mata_kuliah'] ?>
                                                </div>
                                                <small class="text-sub">
                                                    <?= date('d M Y', strtotime($m['uploaded_at'])) ?>
                                                </small>
                                            </div>
                                        </a>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted">
                                        <p class="mb-0">Belum ada materi</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Real-time Clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            
            document.getElementById('realtimeClock').textContent = timeString;
            document.getElementById('currentDate').textContent = dateString;
        }
        setInterval(updateClock, 1000);
        updateClock();

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
            
            // Trigger icon animations
            document.querySelectorAll('.icon-animate').forEach(icon => {
                icon.classList.add('animate-pulse-once');
                setTimeout(() => {
                    icon.classList.remove('animate-pulse-once');
                }, 300);
            });
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
            
            // Start typing animation after page load
            setTimeout(startTypingAnimation, 500);
            
            // Start icon animations
            startIconAnimations();
            
            // Check if mobile and adjust layout
            adjustMobileLayout();
            window.addEventListener('resize', adjustMobileLayout);
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

        // Adjust layout for mobile
        function adjustMobileLayout() {
            if (window.innerWidth < 992) {
                document.body.classList.add('mobile-mode');
            } else {
                document.body.classList.remove('mobile-mode');
                closeSidebar(); // Ensure sidebar is closed on desktop
            }
        }

        // Greeting based on time with typing animation
        function getGreeting() {
            const hour = new Date().getHours();
            let greeting;
            
            if (hour >= 5 && hour < 11) {
                greeting = "Selamat pagi";
            } else if (hour >= 11 && hour < 15) {
                greeting = "Selamat siang";
            } else if (hour >= 15 && hour < 19) {
                greeting = "Selamat sore";
            } else {
                greeting = "Selamat malam";
            }
            
            // Get user name from PHP variable
            const userName = "<?= htmlspecialchars($nama_user, ENT_QUOTES) ?>";
            return `${greeting}, ${userName}!`;
        }

        function startTypingAnimation() {
            const greetingElement = document.getElementById('greetingText');
            const greetingText = getGreeting();
            
            // Clear any existing text
            greetingElement.textContent = '';
            greetingElement.classList.remove('typing-done');
            
            // For mobile, show text immediately without animation if screen is small
            if (window.innerWidth <= 576) {
                greetingElement.textContent = greetingText;
                greetingElement.classList.add('typing-done');
                return;
            }
            
            let i = 0;
            const speed = 50;
            
            function typeWriter() {
                if (i < greetingText.length) {
                    greetingElement.textContent += greetingText.charAt(i);
                    i++;
                    setTimeout(typeWriter, speed);
                } else {
                    // Animation complete
                    greetingElement.classList.add('typing-done');
                    
                    // Blink a few times then stop
                    setTimeout(() => {
                        greetingElement.style.animation = 'none';
                        greetingElement.style.borderRight = 'none';
                    }, 2000);
                }
            }
            
            typeWriter();
        }

        // Deadline Timer with Real-time Updates
        function updateDeadlineTimers() {
            const timers = document.querySelectorAll('.deadline-timer');
            const now = new Date().getTime();
            
            timers.forEach(timer => {
                const deadline = new Date(timer.dataset.deadline).getTime();
                const diff = deadline - now;
                
                if (diff <= 0) {
                    timer.textContent = 'EXPIRED';
                    timer.className = 'deadline-timer urgent';
                    return;
                }
                
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                let timeString = '';
                if (days > 0) {
                    timeString = `${days}h`;
                    timer.className = days > 1 ? 'deadline-timer safe' : 'deadline-timer warning';
                } else if (hours > 0) {
                    timeString = `${hours}j ${minutes}m`;
                    timer.className = hours < 24 ? 'deadline-timer warning' : 'deadline-timer safe';
                } else if (minutes > 0) {
                    timeString = `${minutes}m`;
                    timer.className = 'deadline-timer urgent';
                } else {
                    timeString = 'Segera!';
                    timer.className = 'deadline-timer urgent';
                }
                
                timer.textContent = timeString;
            });
        }
        
        // Update timers every second for real-time countdown
        setInterval(updateDeadlineTimers, 1000);
        updateDeadlineTimers();

        // Icon animations
        function startIconAnimations() {
            // Add hover effects to icons
            document.querySelectorAll('.stat-icon').forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.classList.add('animate-pulse-once');
                });
                
                icon.addEventListener('mouseleave', function() {
                    setTimeout(() => {
                        this.classList.remove('animate-pulse-once');
                    }, 300);
                });
            });
            
            // Random animation delays for cards
            document.querySelectorAll('.schedule-item, .content-item, .deadline-item, .material-item').forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        }

        // Add hover animations (only on desktop)
        if (window.innerWidth > 992) {
            document.querySelectorAll('.stat-card, .deadline-card, .schedule-card, .content-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        }

        // Smooth scrolling for content areas
        document.querySelectorAll('.content-list').forEach(list => {
            list.addEventListener('wheel', function(e) {
                if (this.scrollHeight > this.clientHeight) {
                    e.preventDefault();
                    this.scrollTop += e.deltaY;
                }
            }, { passive: false });
        });

        // Handle mobile keyboard
        window.addEventListener('orientationchange', function() {
            setTimeout(adjustMobileLayout, 100);
        });

        // Add click animation to cards
        document.querySelectorAll('.stat-card, .schedule-item, .content-item, .deadline-item, .material-item').forEach(item => {
            item.addEventListener('click', function() {
                this.classList.add('animate-pulse-once');
                setTimeout(() => {
                    this.classList.remove('animate-pulse-once');
                }, 300);
            });
        });
    </script>

</body>
</html>