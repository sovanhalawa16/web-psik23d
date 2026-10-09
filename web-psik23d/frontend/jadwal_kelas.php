<?php 
session_start();
// KUNCI MAINTENANCE
$flag_file = __DIR__ . '/../maintenance.flag';
if (file_exists($flag_file)) { 
    header("Location: /maintenance"); 
    exit;
}
include '../koneksi.php';
// --- JURUS TV LIVE STREAMING (Ambil sinyal dari Admin) ---
$q_live = mysqli_query($koneksi, "SELECT nilai FROM pengaturan_jadwal WHERE nama_setting = 'status_live'");
$status_live = mysqli_fetch_assoc($q_live)['nilai'] ?? 'OFF';

$q_room = mysqli_query($koneksi, "SELECT nilai FROM pengaturan_jadwal WHERE nama_setting = 'room_live'");
$room_live = mysqli_fetch_assoc($q_room)['nilai'] ?? 'PSIK23D_LIVE_SECRET_ROOM_999';


// --- 0. AUTO CREATE TABLE ABSENSI (JIKA BELUM ADA) ---
$create_table = "CREATE TABLE IF NOT EXISTS absensi_jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jadwal_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    tanggal DATETIME NOT NULL
)";
mysqli_query($koneksi, $create_table);

// FIX JAM 00:00 - Memaksa perubahan tipe data ke DATETIME
mysqli_query($koneksi, "ALTER TABLE absensi_jadwal MODIFY COLUMN tanggal DATETIME NOT NULL");

// --- 1. SETUP TIMEZONE & LOGIC HARI ---
date_default_timezone_set('Asia/Jakarta');
$hari_inggris = date('l'); 
$mapping_hari = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];
$hari_ini = $mapping_hari[$hari_inggris];
$tanggal_hari_ini = date('Y-m-d');

$bulan_indo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$map_en = ['Senin'=>'Monday','Selasa'=>'Tuesday','Rabu'=>'Wednesday','Kamis'=>'Thursday','Jumat'=>'Friday','Sabtu'=>'Saturday','Minggu'=>'Sunday'];

// --- GET TERAKHIR UPDATE JADWAL DARI DATABASE ---
$last_update_str = date('d M Y, H:i'); 
$q_last_update = mysqli_query($koneksi, "SELECT UPDATE_TIME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jadwal'");
if($q_last_update && mysqli_num_rows($q_last_update) > 0) {
    $row_time = mysqli_fetch_assoc($q_last_update);
    if(!empty($row_time['UPDATE_TIME'])) {
        $last_update_str = date('d M Y, H:i', strtotime($row_time['UPDATE_TIME']));
    }
}

// --- FITUR GENERATE .ICS (DOWNLOAD KALENDER) ---
if (isset($_GET['download_ics'])) {
    $id_ics = (int)$_GET['download_ics'];
    $q_ics = mysqli_query($koneksi, "SELECT * FROM jadwal WHERE id = '$id_ics'");
    if(mysqli_num_rows($q_ics) > 0){
        $j = mysqli_fetch_assoc($q_ics);
        
        $h_en = $map_en[$j['hari']];
        $is_today = (date('l') == $h_en);
        $tgl_time = $is_today ? time() : strtotime("next $h_en");
        
        $jam_mulai_arr = explode(':', $j['jam_mulai']);
        $jam_selesai_arr = explode(':', $j['jam_selesai']);
        
        $start_time = mktime((int)$jam_mulai_arr[0], (int)$jam_mulai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
        $end_time = mktime((int)$jam_selesai_arr[0], (int)$jam_selesai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
        
        if ($is_today && time() > $end_time) {
            $tgl_time = strtotime("next $h_en");
            $start_time = mktime((int)$jam_mulai_arr[0], (int)$jam_mulai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
            $end_time = mktime((int)$jam_selesai_arr[0], (int)$jam_selesai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
        }

        $dtstart = gmdate("Ymd\THis\Z", $start_time);
        $dtend = gmdate("Ymd\THis\Z", $end_time);
        $stamp = gmdate("Ymd\THis\Z");
        $uid = uniqid() . "@psik23d.kesug.com";
        $summary = "Kuliah: " . $j['mata_kuliah'];
        $location = ($j['jenis_kelas'] == 'online') ? $j['link_online'] : $j['ruangan'];
        
        $ics_content = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//PSIK 23 D//NONSGML v1.0//EN\r\nBEGIN:VEVENT\r\nUID:".$uid."\r\nDTSTAMP:".$stamp."\r\nDTSTART:".$dtstart."\r\nDTEND:".$dtend."\r\nSUMMARY:".$summary."\r\nLOCATION:".$location."\r\nBEGIN:VALARM\r\nTRIGGER:-PT30M\r\nACTION:DISPLAY\r\nDESCRIPTION:Pengingat Kuliah\r\nEND:VALARM\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="jadwal_'.$j['mata_kuliah'].'.ics"');
        echo $ics_content;
        exit;
    }
}

// --- FITUR AJAX: POST DATA ABSEN & LOGIKA WAKTU CANGGIH ---
if (isset($_POST['action'])) {
    
    // Logika Insert/Update Absen
    if ($_POST['action'] == 'submit_absen') {
        ob_start();
        $jadwal_id = (int)$_POST['jadwal_id'];
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0; // TANGKAP USER ID
        $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
        $status_post = mysqli_real_escape_string($koneksi, $_POST['status']);
        
        $waktu_sekarang = date('Y-m-d H:i:s');
        $status_final = $status_post;

        // ========================================================
        // JURUS CANGGIH 1: DETEKSI KETERLAMBATAN OTOMATIS
        // ========================================================
        if ($status_post == 'hadir_auto') {
            $q_jdwl = mysqli_query($koneksi, "SELECT jam_mulai FROM jadwal WHERE id = '$jadwal_id'");
            if($row_j = mysqli_fetch_assoc($q_jdwl)){
                $jam_mulai_db = $row_j['jam_mulai']; 
                
                // Kalkulasi selisih waktu dalam menit
                $time_mulai = strtotime(date('Y-m-d') . ' ' . $jam_mulai_db);
                $time_now = time();
                $diff_minutes = floor(($time_now - $time_mulai) / 60);
                
                // Logic: 
                // <= 15 Menit = Hadir
                // 16 - 60 Menit = Terlambat
                // > 60 Menit = Alpha
                if ($diff_minutes <= 15) {
                    $status_final = 'hadir';
                } elseif ($diff_minutes <= 60) {
                    $status_final = 'terlambat';
                } else {
                    $status_final = 'alpha';
                }
            } else {
                $status_final = 'hadir'; // Fallback aman
            }
        }

        if (!empty($nama)) {
            $cek = mysqli_query($koneksi, "SELECT id FROM absensi_jadwal WHERE jadwal_id = '$jadwal_id' AND nama = '$nama'");
            if(mysqli_num_rows($cek) == 0) {
                mysqli_query($koneksi, "INSERT INTO absensi_jadwal (jadwal_id, user_id, nama, status, tanggal) VALUES ('$jadwal_id', '$user_id', '$nama', '$status_final', '$waktu_sekarang')");
            } else {
                mysqli_query($koneksi, "UPDATE absensi_jadwal SET user_id = '$user_id', status = '$status_final', tanggal = '$waktu_sekarang' WHERE jadwal_id = '$jadwal_id' AND nama = '$nama'");
            }
            
            $_SESSION['absen_done_'.$jadwal_id] = true;
            $_SESSION['absen_nama_'.$jadwal_id] = $nama; 
            
            // Kirim balikan status aslinya buat UI Pop-up
            $resp = ['status' => 'success', 'status_tercatat' => $status_final];
        } else {
            $resp = ['status' => 'error', 'msg' => 'Nama tidak boleh kosong'];
        }
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    }
    
    // Logika Hapus Kehadiran
    if ($_POST['action'] == 'delete_absen') {
        ob_start(); 
        $jadwal_id = (int)$_POST['jadwal_id'];
        $nama = $_SESSION['absen_nama_'.$jadwal_id] ?? '';
        
        if(!empty($nama)){
            mysqli_query($koneksi, "DELETE FROM absensi_jadwal WHERE jadwal_id = '$jadwal_id' AND nama = '$nama'");
        }
        
        unset($_SESSION['absen_done_'.$jadwal_id]);
        unset($_SESSION['absen_nama_'.$jadwal_id]);
        
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// --- FUNGSI RENDER HTML ABSEN & STATISTIK ---
function generateHTMLAbsen($koneksi, $jadwal_id) {
    $q_absen = mysqli_query($koneksi, "
        SELECT a.nama, a.status, a.tanggal, u.foto 
        FROM absensi_jadwal a 
        LEFT JOIN users u ON a.nama = u.nama 
        WHERE a.jadwal_id = '$jadwal_id' 
        ORDER BY a.tanggal DESC
    ");
    $html_absen = '';
    $total_mahasiswa = 23; 
    $total_hadir = 0;

    if(mysqli_num_rows($q_absen) > 0) {
        $html_absen .= '<style>.scroll-absen-'.$jadwal_id.'::-webkit-scrollbar { display: none; }</style>';
        $html_absen .= '<div class="d-flex overflow-auto pb-3 pt-1 gap-2 scroll-absen-'.$jadwal_id.'" style="margin-left: -15px; margin-right: -15px; padding-left: 15px; padding-right: 15px; scrollbar-width: none;">'; 
        
        while($ab = mysqli_fetch_assoc($q_absen)) {
            $time_str = isset($ab['tanggal']) ? date('H:i', strtotime($ab['tanggal'])) : '--:--';
            
            $bg_nama = 'bg-success'; $text_jam = 'text-success'; $icon = 'fa-check-circle';
            if($ab['status'] == 'alpha') { $bg_nama = 'bg-danger text-white'; $text_jam = 'text-danger'; $icon = 'fa-times-circle'; }
            elseif($ab['status'] == 'sakit') { $bg_nama = 'bg-warning text-dark'; $text_jam = 'text-warning'; $icon = 'fa-briefcase-medical'; }
            elseif($ab['status'] == 'izin') { $bg_nama = 'bg-info text-dark'; $text_jam = 'text-info'; $icon = 'fa-envelope-open-text'; }
            elseif($ab['status'] == 'terlambat') { $bg_nama = 'bg-secondary text-white'; $text_jam = 'text-secondary'; $icon = 'fa-running'; $total_hadir++; }
            
            if($ab['status'] == 'hadir') $total_hadir++;

            $fotoPath = (!empty($ab['foto']) && $ab['foto'] !== 'default.jpg' && $ab['foto'] !== 'default.png') ? '../assets/img/mahasiswa/' . $ab['foto'] : 'https://ui-avatars.com/api/?background=random&name=' . urlencode($ab['nama']);

            $html_absen .= '
            <div class="d-flex align-items-center bg-white border border-secondary border-opacity-10 rounded-4 p-2 shadow-sm flex-shrink-0" style="width: 180px;">
                <img src="'.$fotoPath.'" class="rounded-circle shadow-sm me-2" style="width: 45px; height: 45px; min-width: 45px; min-height: 45px; object-fit: cover; border: 2px solid #e2e8f0;" onerror="this.src=\'https://ui-avatars.com/api/?background=random&name='.urlencode($ab['nama']).'\'">
                <div class="d-flex flex-column justify-content-center text-start w-100" style="line-height: 1.1;">
                    <span class="fw-bolder text-dark d-inline-block text-truncate" style="font-size: 0.75rem; max-width: 105px;">'.$ab['nama'].'</span>
                    <span class="badge '.$bg_nama.' rounded-pill my-1 text-start" style="font-size: 0.6rem; width: fit-content;"><i class="fas '.$icon.' me-1"></i>'.ucfirst($ab['status']).'</span>
                    <span class="text-muted fw-bold" style="font-size: 0.65rem;"><i class="far fa-clock me-1"></i>'.$time_str.' WIB</span>
                </div>
            </div>';
        }
        $html_absen .= '</div>';

    } else {
        $html_absen = '<div class="text-center text-muted small fst-italic py-3 bg-light rounded-4 mb-3 border border-dashed"><i class="fas fa-user-times mb-2 fs-5"></i><br>Belum ada mahasiswa yang presensi</div>';
    }
    return $html_absen;
}

// --- FITUR AJAX: REALTIME LIST ABSEN ---
if (isset($_GET['ajax_status'])) {
    $response = [];
    $query = "SELECT id FROM jadwal";
    $result = mysqli_query($koneksi, $query);
    
    while($row = mysqli_fetch_assoc($result)) {
        $response[] = [
            'id' => $row['id'],
            'html_absen' => generateHTMLAbsen($koneksi, $row['id'])
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; 
}

// --- 2. QUERY DATA UTAMA ---
$query = "SELECT jadwal.*, dosen.nama AS nama_dosen, dosen.foto AS foto_dosen 
          FROM jadwal 
          LEFT JOIN dosen ON jadwal.dosen_id = dosen.id 
          ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam_mulai ASC";

$result = mysqli_query($koneksi, $query);

$data_blok1 = [];
$data_blok2 = [];

while($row = mysqli_fetch_assoc($result)) {
    if (strpos($row['blok'], '1') !== false) { $data_blok1[] = $row; } 
    else { $data_blok2[] = $row; }
}

$active_tab = 'blok1';
if (empty($data_blok1) && !empty($data_blok2)) {
    $active_tab = 'blok2';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Kelas - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ========================================== */
        /* STICKY HEADER - DEEP OCEAN SOLID EDITION */
        /* ========================================== */
        .sticky-header {
            position: sticky; top: 0; z-index: 1020;
            /* Gradasi Biru Gelap ke Biru Terang */
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            border-bottom: none;
            padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 12px 35px rgba(29, 78, 216, 0.4);
        }
        /* Sihir Paksa Teks Jadi Putih Terang! */
        .sticky-header .text-muted, 
        .sticky-header .text-dark {
            color: #ffffff !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        /* ========================================= */
        /* UI 17: THE STEPPING STONES (SUNSET ORANGE) */
        /* ========================================= */
        .stepping-wrapper {
            position: relative;
            padding: 35px 0; /* Kasih ruang biar batunya bisa lompat atas-bawah */
            margin: 5px 0 15px 0;
            overflow: hidden;
        }
        
        /* Garis Putus-Putus (Rute Peta) di Belakang */
        .stepping-wrapper::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-image: linear-gradient(to right, #cbd5e1 50%, transparent 50%);
            background-size: 12px 2px; /* Jarak putus-putus */
            z-index: 0;
            transform: translateY(-50%);
        }

        .stone-container {
            display: flex;
            gap: 22px;
            overflow-x: auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
            scroll-behavior: smooth;
            /* Sembunyiin Scrollbar jelek di HP */
            scrollbar-width: none; 
            -ms-overflow-style: none;
        }
        .stone-container::-webkit-scrollbar { display: none; }

        /* Desain Batu Pijakan Normal */
        .stone {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            color: #64748b;
            text-decoration: none;
            flex-shrink: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        
        /* SIHIR ASIMETRIS: Ganjil ke bawah, Genap ke atas */
        .stone:nth-child(odd) { transform: translateY(14px); }
        .stone:nth-child(even) { transform: translateY(-14px); }

        .stone-content { display: flex; flex-direction: column; align-items: center; transition: all 0.3s ease; }
        .stone .day { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; margin-bottom: -2px; }
        .stone .date { font-size: 1.2rem; font-weight: 800; line-height: 1.1; }

        /* 🚀 BATU AKTIF = BERUBAH JADI MAP MARKER ORANGE! 🚀 */
        .stone.active {
            background: linear-gradient(135deg, #f97316, #ea580c); /* Sunset Orange */
            border-color: #ea580c;
            color: white;
            /* Bentuk tetesan air (Pin Lokasi) */
            border-radius: 50% 50% 50% 0; 
            /* Rotasi biar ujung lancipnya ke bawah, lalu diangkat sedikit */
            transform: translateY(-20px) rotate(-45deg) !important;
            box-shadow: 4px 10px 15px rgba(234, 88, 12, 0.3);
            z-index: 10;
        }
        /* Putar balik teksnya biar gak ikut miring wkwkwk */
        .stone.active .stone-content { transform: rotate(45deg); }
        .stone.active .day, .stone.active .date { color: #ffffff; }
        /* PERBAIKAN UKURAN POPUP GAMBAR */
.modal .carousel-item img,
.modal .carousel-item video {
    max-width: 100% !important;
    max-height: 300px !important;
    width: auto !important;
    height: auto !important;
    object-fit: contain !important;
}

.modal .position-relative[style*="height: 180px"] {
    height: auto !important;
    min-height: 180px;
    max-height: 350px;
}
        /* --- SWIPE TO OPEN UX (INTERAKTIF) --- */
        .swipe-track {
    background: transparent; border-radius: 50px; height: 45px; position: relative;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    margin-top: 10px; width: 100%; border: none;
    touch-action: none;
}
        .swipe-text {
            font-size: 0.75rem; font-weight: 800; color: #94a3b8; z-index: 1; pointer-events: none;
            text-transform: uppercase; letter-spacing: 1px; transition: opacity 0.3s;
            display: flex; align-items: center; justify-content: center;
        }
        
        /* Animasi Panah >>> Berjalan */
        .animated-arrows { display: inline-flex; margin-left: 8px; color: #cbd5e1; }
        .animated-arrows i { animation: arrowMove 1.5s infinite; margin-left: -3px; font-size: 0.8rem; }
        .animated-arrows i:nth-child(1) { animation-delay: 0s; }
        .animated-arrows i:nth-child(2) { animation-delay: 0.15s; }
        .animated-arrows i:nth-child(3) { animation-delay: 0.3s; }
        
        @keyframes arrowMove {
            0% { opacity: 0; transform: translateX(-3px); }
            50% { opacity: 1; transform: translateX(3px); }
            100% { opacity: 0; transform: translateX(-3px); }
        }
        
        .swipe-thumb {
    position: absolute; left: 3px; top: 3px; bottom: 3px; width: 40px;
    background: transparent;
    display: flex; align-items: center; justify-content: center;
    box-shadow: none; cursor: grab; z-index: 2;
}
        
        /* Animasi Tombol Gerak Sendiri Minta Digeser */
        .swipe-thumb.hint-anim { animation: swipeHint 2s infinite ease-in-out; }
        @keyframes swipeHint {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(8px); } /* Gerak 8px ke kanan */
        }
        .swipe-thumb:active { cursor: grabbing; }

        /* --- LOCKED CLASS OVERLAY (EFEK KACA ES) --- */
        .locked-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.3) !important; /* Putihnya dibikin super transparan */
            backdrop-filter: blur(8px) !important; /* Blurnya dikencengin */
            -webkit-backdrop-filter: blur(8px) !important;
            z-index: 30; border-radius: 16px; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center;
            border: 2px solid transparent;
        }
        .locked-overlay.batal { border-color: rgba(239, 68, 68, 0.4); }
        .locked-overlay.mandiri { border-color: rgba(245, 158, 11, 0.4); }
        /* --- STYLE SMART GRID ABSENSI --- */
        .mhs-chip {
            display: flex; align-items: center; gap: 10px; padding: 8px 12px;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
            margin-bottom: 8px; cursor: pointer; transition: 0.2s;
        }
        .mhs-chip:hover { background: #eff6ff; border-color: #3b82f6; transform: translateY(-2px); }
        .mhs-chip img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .mhs-chip span { font-weight: 700; font-size: 0.85rem; color: #1e293b; text-align: left; }
        #mhsGridContainer::-webkit-scrollbar { width: 5px; }
        #mhsGridContainer::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        /* --- STYLE BARU: MODERN VERTICAL TIMELINE --- */
        .timeline-container { position: relative; padding-left: 30px; }
        .timeline-container::before {
            content: ''; position: absolute; left: 6px; top: 0; bottom: -30px;
            width: 2px; background-color: #e2e8f0; border-radius: 2px;
        }
        /* Hapus garis lebihan pada item terakhir */
        .mb-3:last-child .timeline-container::before { bottom: 0; }
        
        .timeline-dot {
            position: absolute; left: 0; top: 20px;
            width: 14px; height: 14px; border-radius: 50%;
            background-color: #ffffff; border: 3px solid #3b82f6;
            box-shadow: 0 0 0 4px #f4f7fb; z-index: 1; transition: 0.3s;
        }
        
        .timeline-card {
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 15px; display: flex; flex-direction: column; gap: 5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); cursor: pointer; transition: 0.3s;
            text-align: left; width: 100%;
        }
        .timeline-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.05); border-color: #cbd5e1; }
        .timeline-card:hover .timeline-dot { background-color: #3b82f6; transform: scale(1.2); }
        
        .tl-time { font-size: 0.8rem; font-weight: 800; color: #1e293b; letter-spacing: 0.5px; }
        .tl-title { font-size: 1.05rem; font-weight: 800; color: #1e293b; margin: 0; }
        .tl-room { font-size: 0.75rem; font-weight: 600; color: #64748b; }
        
        /* Animasi pulse kalau kelas lagi berlangsung */
        .is-live .timeline-dot {
            background-color: #10b981; border-color: #10b981;
            animation: pulseLive 2s infinite;
        }
        .is-live .tl-time { color: #10b981; }
        @keyframes pulseLive { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
        body { 
            background-color: #f4f7fb; font-family: 'Plus Jakarta Sans', sans-serif; color: #334155; overflow-x: hidden; padding-top: 0; 
        }

        /* --- IPHONE STYLE CLEAN MODAL & BLUR BACKDROP --- */
        .clean-modal {
            border-radius: 28px;
            border: none;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            background-color: #ffffff;
        }
        .modal-backdrop {
            background-color: rgba(15, 23, 42, 0.4) !important;
        }
        .modal-backdrop.show {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            opacity: 1 !important;
        }
        .modal-dialog {
            margin: 1.5rem;
        }
        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 350px;
                margin: 2rem auto;
            }
        }

        /* --- LOCK SCREEN --- */
        #lockScreen {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            z-index: 99999; display: flex; flex-direction: column; justify-content: center; align-items: center;
            transition: transform 0.8s cubic-bezier(0.77, 0, 0.175, 1), opacity 0.8s ease;
            color: white; text-align: center; padding: 20px;
        }
        #lockScreen.opened { transform: translateY(-100%); opacity: 0; pointer-events: none; }

        .btn-open-lock {
            background: linear-gradient(135deg, #4f46e5 0%, #0284c7 100%); border: none; color: white; font-weight: 800; padding: 15px 40px;
            border-radius: 50px; font-size: 1.1rem; box-shadow: 0 10px 25px rgba(2, 132, 199, 0.4);
            transition: 0.3s; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;
            animation: pulseLock 2s infinite; margin-top: 30px;
        }
        .btn-open-lock:hover { transform: scale(1.05); }
        @keyframes pulseLock { 0% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(2, 132, 199, 0); } 100% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); } }

        /* --- FLOATING MUSIC PLAYER --- */
        .music-wrapper {
            position: fixed; top: 75%; left: 10px; transform: translateY(-50%);
            z-index: 1040; 
            display: flex; flex-direction: column; align-items: flex-start; gap: 10px; touch-action: none; 
        }
        .vinyl-btn {
            width: 55px; height: 55px; border-radius: 50%;
            background: url('../assets/img/piringmusic.png') center/cover; background-color: #000;
            border: 2px solid rgba(255,255,255,0.8); cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.5); transition: transform 0.2s;
        }
        .vinyl-btn.playing { animation: spinVinyl 4s linear infinite; }
        @keyframes spinVinyl { 100% { transform: rotate(360deg); } }

        .mini-player {
            background: #181818; color: white; border-radius: 16px; padding: 15px 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: flex; flex-direction: column; gap: 15px; width: 300px; opacity: 1; transform: scale(1); 
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform-origin: bottom left; pointer-events: auto;
        }
        .mini-player.hidden { opacity: 0; transform: scale(0.5); pointer-events: none; position: absolute; }
        .player-info { text-align: center; }
        .player-title { font-weight: 700; font-size: 1rem; color: #1db954; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .player-subtitle { font-size: 0.75rem; color: #b3b3b3; }
        
        .progress-container { display: flex; align-items: center; gap: 10px; font-size: 0.75rem; color: #b3b3b3; font-weight: 600; }
        #songProgress { flex: 1; height: 4px; cursor: pointer; }
        .form-range::-webkit-slider-thumb { background: #1db954; width: 12px; height: 12px; margin-top: -4px; }
        .form-range::-moz-range-thumb { background: #1db954; width: 12px; height: 12px; border: none; }

        .player-controls { display: flex; justify-content: center; align-items: center; gap: 25px; }
        .player-controls button { background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; transition: 0.2s; padding: 0; }
        .player-controls button:hover { color: #1db954; transform: scale(1.1); }
        .player-controls .btn-play {
            width: 45px; height: 45px; border-radius: 50%; background: white; color: black;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .player-controls .btn-play:hover { background: #1db954; color: white; transform: scale(1.05); }

        .timer-select { background: #282828; color: white; border: 1px solid #444; border-radius: 5px; padding: 4px 8px; font-size: 0.75rem; cursor: pointer; outline: none; }
        .timer-select:focus { border-color: #1db954; }

        /* --- JURUS BARU: IOS MODAL ABSENSI CANGGIH --- */
        .ios-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            z-index: 1060; /* Di atas modal bootstrap yang z-indexnya 1050 */
            display: flex; justify-content: center; align-items: center;
            opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .ios-overlay.active { opacity: 1; pointer-events: auto; }
        
        .ios-box {
            background: rgba(255, 255, 255, 0.95);
            width: 85%; max-width: 320px;
            border-radius: 24px; text-align: center;
            padding: 30px 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            transform: scale(0.8); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .ios-overlay.active .ios-box { transform: scale(1); }

        /* --- FLOATING WHATSAPP BUTTON --- */
        .wa-floating-container {
            position: fixed; bottom: 20px; right: 20px; 
            z-index: 1040; 
            display: flex; align-items: center; gap: 10px;
        }
        .wa-tooltip {
            background-color: white; color: #334155; font-size: 0.8rem; font-weight: 700;
            padding: 8px 15px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0; opacity: 0; transform: translateX(20px); transition: 0.5s ease;
        }
        .wa-tooltip.show { opacity: 1; transform: translateX(0); }
        
        .wa-btn {
            background-color: #25D366; color: white; width: 55px; height: 55px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); text-decoration: none; animation: bounceWA 3s infinite;
        }
        .wa-btn:hover { background-color: #128C7E; color: white; animation: none; transform: scale(1.05); }
        @keyframes bounceWA { 0%, 20%, 50%, 80%, 100% {transform: translateY(0);} 40% {transform: translateY(-10px);} 60% {transform: translateY(-5px);} }

        /* --- MAIN STYLES --- */
        #mainContent { opacity: 0; transition: opacity 1s ease 0.5s; }
        .header-modern {
            background: linear-gradient(135deg, #4f46e5 0%, #0284c7 100%); padding: 25px 0 20px; 
            color: white; text-align: center; border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
            position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(2, 132, 199, 0.15); margin-bottom: 20px;
        }
        .bg-shape-1 { position: absolute; width: 150px; height: 150px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; top: -50px; left: -30px; filter: blur(2px); }
        .bg-shape-2 { position: absolute; width: 100px; height: 100px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; bottom: -20px; right: -20px; filter: blur(1px); }

        .nav-pills { gap: 10px; justify-content: center !important; }
        .nav-pills .nav-link {
            background-color: white; color: #64748b; border-radius: 50px; padding: 10px 25px; 
            font-weight: 800; font-size: 0.9rem; transition: all 0.3s ease; border: 1px solid #e2e8f0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02); text-transform: uppercase; letter-spacing: 1px;
        }
        .nav-pills .nav-link.active { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); border-color: transparent; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .jadwal-list-card { 
            background: #ffffff;
            position: relative; 
        }
        .jadwal-img-top { width: 100%; height: 180px; object-fit: cover; position: relative; }
        .card-visual-online {
            height: 180px; width: 100%; background: #000;
            display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;
        }

        .badge-hari {
            position: absolute; top: 15px; left: 15px; padding: 6px 14px; border-radius: 10px;
            font-weight: 800; color: white; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2); z-index: 10; font-size: 0.8rem;
        }

        .btn-calendar-bell {
            position: absolute; top: 15px; right: 15px; background: #ffffff; color: #3b82f6; 
            width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 10; transition: 0.3s;
        }
        .btn-calendar-bell:hover { background: #3b82f6; color: white; transform: scale(1.1); }

        .card-body-custom { padding: 12px 15px; }
        .mk-title { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: 5px; text-align: center; }
        .detail-list { list-style: none; padding: 0; margin: 0 0 10px 0; border-top: 2px solid #f1f5f9; padding-top: 10px; }
        .detail-list li { padding: 6px 0; border-bottom: 1px dashed #e2e8f0; display: flex; align-items: flex-start; }
        .detail-list li:last-child { border-bottom: none; }
        .detail-label { font-weight: 700; color: #64748b; width: 110px; font-size: 0.85rem; }
        .detail-value { font-weight: 700; color: #1e293b; flex: 1; font-size: 0.85rem; }

        .dosen-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 8px 12px; display: flex; align-items: center; gap: 10px; margin-bottom: 10px; transition: background 0.3s; }
        .dosen-pic { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 2px solid white; }
        
        .live-absen-box { background: #f8fafc; border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0; }

        .animate-fade { animation: fadeIn 0.4s ease-in-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes blink-animation { 0% { opacity: 1; } 50% { opacity: 0.2; } 100% { opacity: 1; } }
        .blink { animation: blink-animation 1s infinite; }
        .header-profile { display: flex; align-items: center; gap: 12px; }
        .header-profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #3b82f6; padding: 2px; }
        .header-greeting { line-height: 1.2; }
        
        /* --- Dynamic Island (Up Next Class) --- */
        .dynamic-island {
            background: #000000; color: #ffffff;
            border-radius: 40px; padding: 12px 20px;
            margin: 0 auto 25px auto; width: fit-content; max-width: 90%;
            display: flex; align-items: center; gap: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer; overflow: hidden; position: relative;
        }
        .dynamic-island:hover { transform: scale(1.05); }
        .island-icon {
            width: 35px; height: 35px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            display: flex; align-items: center; justify-content: center; font-size: 1rem;
            animation: pulseIsland 2s infinite; flex-shrink: 0;
        }
        .island-content { display: flex; flex-direction: column; text-align: left; }
        .island-title { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .island-time { font-size: 1.1rem; font-weight: 800; letter-spacing: 0.5px; }
        @keyframes pulseIsland { 0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); } 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); } }
        
        /* --- STYLE BARU: TIMELINE PILIH HARI & FILTER --- */
        .timeline-nav-wrapper {
            position: relative; padding: 15px 0 25px 0; margin-top: 5px; overflow-x: auto;
            scrollbar-width: none; -ms-overflow-style: none;
        }
        .timeline-nav-wrapper::-webkit-scrollbar { display: none; }
        .timeline-track {
            display: flex; position: relative; 
            width: max-content; /* Biar flexbox melar nyambung terus ke kanan ujung */
            margin: 0 auto; /* Tengahkan otomatis kalau buka di desktop */
            padding: 0 15px; /* Jarak aman mepet di kiri layar HP */
            gap: 15px; /* Spasi antar hari */
        }
        .timeline-nav-line {
            position: absolute; top: 50%; 
            left: 47px; right: 47px; /* 15px padding + 32.5px (setengah dari 65px lebar kotak) = Presisi 100% ujung ke ujung! */
            height: 1px; border-top: 1.5px dashed #cbd5e1; z-index: 0; transform: translateY(7px);
        }
        .timeline-node {
            position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center;
            width: 65px; min-width: 65px; /* Diperlebar dikit biar tanggal format panjang muat */
            cursor: pointer; transition: 0.3s; flex-shrink: 0;
        }
        .timeline-node .node-label { font-size: 0.85rem; font-weight: 700; color: #64748b; transition: 0.3s; }
        .timeline-node .node-date { 
            font-size: 0.65rem; color: #94a3b8; transition: 0.3s; margin-bottom: 8px; 
            white-space: nowrap; letter-spacing: -0.5px; /* Biar sebaris lurus rapi di bawah hari */
        }
        .timeline-node .node-dot {
            width: 14px; height: 14px; border-radius: 50%; background: #0f172a; border: 2px solid #0f172a; transition: 0.3s;
        }
        .timeline-node.active .node-label, .timeline-node.active .node-date { color: #ef4444; font-weight: 800; }
        .timeline-node.active .node-dot { background: #ef4444; border-color: #ef4444; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15); }

        /* Floating Bottom Nav Bar (Ala IG/TikTok) */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            display: flex; justify-content: space-around; align-items: center; padding: 12px 0 15px 0; 
            border-radius: 20px 20px 0 0; box-shadow: 0 -5px 25px rgba(0,0,0,0.05); 
            border-top: 1px solid rgba(0,0,0,0.05); z-index: 1030;
        }
        .nav-item-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #64748b; text-decoration: none; transition: 0.3s; gap: 4px;
        }
        .nav-item-btn i { font-size: 1.2rem; }
        .nav-item-btn span { font-size: 0.65rem; font-weight: 700; }
        .nav-item-btn.active { color: #3b82f6; transform: translateY(-2px); }
        .nav-item-btn:hover { color: #3b82f6; }

        /* Skeleton Loading Animation */
        .skeleton-box {
            background: #e2e8f0; border-radius: 16px; height: 120px; width: 100%; margin-bottom: 15px;
            position: relative; overflow: hidden;
        }
        .skeleton-box::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: loadingSkeleton 1.5s infinite;
        }
        @keyframes loadingSkeleton { 100% { left: 100%; } }
        #realContent { display: none; opacity: 0; transition: opacity 0.5s ease; }
        .header-profile { display: flex; align-items: center; gap: 12px; }
        .header-profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #3b82f6; padding: 2px; }
        .header-greeting { line-height: 1.2; }
        
        /* Hero Widget (Up Next Class) */
        .hero-widget {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            border-radius: 24px; padding: 25px 20px; color: white;
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.3); margin-top: 15px; margin-bottom: 25px;
            position: relative; overflow: hidden;
        }
        .hero-widget::after {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 150px; height: 150px; background: rgba(255,255,255,0.1);
            border-radius: 50%; filter: blur(20px);
        }
        
        /* Horizontal Date Swipe (Calendar) */
        .date-swipe-container {
            display: flex; overflow-x: auto; gap: 12px; padding: 5px 20px 15px 20px;
            scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none;
            margin-left: -15px; margin-right: -15px; /* Biar nembus padding container */
        }
        .date-swipe-container::-webkit-scrollbar { display: none; }
        .date-card {
            min-width: 65px; height: 80px; background: white; border: 1px solid #e2e8f0;
            border-radius: 18px; display: flex; flex-direction: column; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.3s; scroll-snap-align: start; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .date-card.active {
            background: #3b82f6; color: white; border-color: #3b82f6;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3); transform: translateY(-3px);
        }
        .date-card span.day { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .date-card span.date { font-size: 1.3rem; font-weight: 800; }
        .date-card:not(.active) span.day { color: #64748b; }
        .date-card:not(.active) span.date { color: #1e293b; }

        /* Floating Bottom Nav Bar */
        .bottom-nav {
            position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            display: flex; gap: 30px; padding: 15px 30px; border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.5); z-index: 1030;
        }
        .nav-item-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #64748b; text-decoration: none; transition: 0.3s; gap: 4px;
        }
        .nav-item-btn i { font-size: 1.2rem; }
        .nav-item-btn span { font-size: 0.65rem; font-weight: 700; }
        .nav-item-btn.active { color: #3b82f6; transform: translateY(-2px); }
        .nav-item-btn:hover { color: #3b82f6; }

        /* Skeleton Loading Animation */
        .skeleton-box {
            background: #e2e8f0; border-radius: 16px; height: 120px; width: 100%; margin-bottom: 15px;
            position: relative; overflow: hidden;
        }
        .skeleton-box::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: loadingSkeleton 1.5s infinite;
        }
        @keyframes loadingSkeleton { 100% { left: 100%; } }
        #realContent { display: none; opacity: 0; transition: opacity 0.5s ease; }
    </style>
</head>
<body>

    <div id="iosAbsenOverlay" class="ios-overlay">
        <div class="ios-box" id="iosAbsenContent">
            </div>
    </div>

    <?php include 'lock_screen.php'; ?>

    <div id="musicPlayerWrapper" class="music-wrapper d-none">
        <div id="miniPlayer" class="mini-player hidden">
            <div class="player-info">
                <div id="musicTitle" class="player-title">🎵 Memuat Lagu...</div>
                <div class="player-subtitle">PSIK 23 D Playlist</div>
            </div>
            
            <div class="progress-container">
                <span id="currentTime">0:00</span>
                <input type="range" id="songProgress" class="form-range" value="0" min="0" step="1">
                <span id="totalTime">0:00</span>
            </div>

            <div class="player-controls mt-2">
                <button onclick="prevSong()" title="Previous"><i class="fas fa-backward"></i></button>
                <button onclick="togglePlayPause()" id="playPauseBtn" class="btn-play" title="Play/Pause"><i class="fas fa-play"></i></button>
                <button onclick="nextSong()" title="Next"><i class="fas fa-forward"></i></button>
            </div>
            
            <div class="player-timer mt-2 border-top border-secondary pt-3 border-opacity-50 d-flex justify-content-between align-items-center">
                <span class="small text-muted fw-bold"><i class="fas fa-bed me-1"></i> Berhenti pada:</span>
                <select id="sleepTimerSelect" class="timer-select">
                    <option value="off">Off</option>
                    <option value="5">5 Menit</option>
                    <option value="15">15 Menit</option>
                    <option value="60">1 Jam</option>
                    <option value="after_song">Setelah lagu ini</option>
                </select>
            </div>
        </div>
        <button id="vinylBtn" class="vinyl-btn" title="Geser atau Klik"></button>
    </div>

    <audio id="bgAudio"></audio>

    <div id="mainContent">
        <div class="sticky-header">
            <div class="header-profile" style="flex: 1; min-width: 0; padding-right: 10px;">
                    
                    <!-- 🚀 1. FOTO PROFIL UNIMED (POLOS & UKURAN DIPERBESAR) 🚀 -->
                    <!-- Nah ini dia bro tempat ngubah ukurannya! Ada tulisan width: 50px; height: 50px; -->
                    <!-- Kalau kurang gede, lu bisa ganti jadi 55px atau 60px. -->
                    <!-- border: none; dan padding: 0; adalah jurus buat ngilangin garis birunya! -->
                    <img src="../assets/img/FMIPA.png" alt="Profile" style="flex-shrink: 0; width: 50px; height: 50px; border: none; padding: 0; border-radius: 50%; object-fit: cover;">
                    
                    <div class="header-greeting" style="flex: 1; min-width: 0; margin-left: 8px;">
                        <?php 
                            $jam = date('H');
                            if($jam < 11) $sapaan = "Selamat pagi,";
                            elseif($jam < 15) $sapaan = "Selamat siang,";
                            elseif($jam < 18) $sapaan = "Selamat sore,";
                            else $sapaan = "Selamat malam,";
                        ?>
                        <!-- TEKS SAPAAN -->
                        <span class="d-block text-muted fw-bold mb-1" style="font-size: 0.75rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;"><?= $sapaan ?></span>
                        
                        <!-- 🚀 2. WADAH AVATAR MARQUEE (POLOS, TANPA PILL BIRU) 🚀 -->
                        <div style="height: 24px; max-width: 140px; overflow: hidden; display: flex; align-items: center; position: relative;">
                            
                            <div style="transform: scale(0.6); transform-origin: left center; width: 166%; padding-left: 2px;">
                                <?php include 'avatar_marquee.php'; ?>
                            </div>                                                       
                        </div>
                    </div>
                </div>
            <div>
                <button class="btn btn-light rounded-circle shadow-sm border-0" style="width: 40px; height: 40px;" onclick="if(typeof window.triggerPopupDanKucing === 'function') window.triggerPopupDanKucing()">
                    <i class="fas fa-bell text-primary"></i>
                </button>
            </div>
        </div>

        <div class="container pb-5 mb-5">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                
            <?php include 'banner_pengumuman.php'; ?>
            <div class="d-flex justify-content-end px-3 mt-3">
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-2 shadow-none border-0" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background: transparent; color: #0f172a; font-weight: 800; font-size: 0.8rem; letter-spacing: 0.5px;">
                        <i class="fas fa-filter text-secondary"></i> <span id="textFilterAktif">SEMUA</span> <i class="fas fa-caret-down ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; font-size: 0.85rem;" aria-labelledby="filterDropdown">
                        <li><a class="dropdown-item fw-bold text-dark py-2" href="#" onclick="filterKategori('SEMUA', this); return false;">SEMUA</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item fw-bold text-secondary py-2" href="#" onclick="filterKategori('TEORI', this); return false;">TEORI</a></li>
                        <li><a class="dropdown-item fw-bold text-secondary py-2" href="#" onclick="filterKategori('PRAKTEK', this); return false;">PRAKTEK</a></li>
                        <li><a class="dropdown-item fw-bold text-secondary py-2" href="#" onclick="filterKategori('UTS', this); return false;">UTS</a></li>
                        <li><a class="dropdown-item fw-bold text-secondary py-2" href="#" onclick="filterKategori('UAS', this); return false;">UAS</a></li>
                    </ul>
                </div>
            </div>

            <div class="timeline-nav-wrapper">
                <div class="timeline-track">
                    <div class="timeline-nav-line"></div>
                    <?php 
                        $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        foreach($hari_list as $h) {
                            $isActive = ($h == $hari_ini) ? 'active' : '';
                            
                            // Logika cari tanggal asli format dd/mm/yyyy
                            $h_en_loop = $map_en[$h];
                            $tgl_loop = (date('l') == $h_en_loop) ? time() : strtotime("next $h_en_loop");
                            $tgl_format = date('d/m/Y', $tgl_loop); 

                            echo "
                            <div class='timeline-node $isActive' onclick=\"filterJadwalHari('$h', this)\">
                                <span class='node-label mb-0'>$h</span>
                                <span class='node-date'>$tgl_format</span>
                                <div class='node-dot'></div>
                            </div>";
                        }
                    ?>
                </div>
            </div>

           <?php include 'dynamic_island.php'; ?>
            <!-- ========================================== -->
            <!-- TV POPUP LIVE STREAMING (NOBAR BOLA STYLE) -->
            <!-- ========================================== -->
            <?php if($status_live == 'ON'): ?>
                <div class="text-center mb-4 mt-2">
                    <button class="btn btn-danger btn-lg pulse-live shadow-lg w-100" data-bs-toggle="modal" data-bs-target="#liveModal" style="max-width: 400px;">
                        <i class="fas fa-satellite-dish me-2"></i> KELAS SEDANG LIVE - TONTON!
                    </button>
                </div>

                <div class="modal fade" id="liveModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                  <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content bg-dark text-white" style="border: 2px solid #ef4444; border-radius: 15px; overflow: hidden;">
                      <div class="modal-header border-0 bg-black p-3">
                        <h5 class="modal-title fw-bold text-danger"><i class="fas fa-record-vinyl fa-spin me-2"></i> LIVE STREAMING KELAS</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="stopLive()"></button>
                      </div>
                      <div class="modal-body p-0">
                        <div id="jitsi-public-container" style="width: 100%; height: 75vh; background: #000;"></div>
                      </div>
                    </div>
                  </div>
                </div>

                <script src="https://meet.jit.si/external_api.js"></script>
                <script>
                    let api = null;
                    document.getElementById('liveModal').addEventListener('shown.bs.modal', function () {
                        if(!api) {
                            const domain = 'meet.jit.si';
                            const options = {
                                roomName: '<?= $room_live ?>',
                                width: '100%',
                                height: '100%',
                                parentNode: document.getElementById('jitsi-public-container'),
                                userInfo: { displayName: 'Penonton PSIK 23 D' },
                                configOverwrite: {
                                    prejoinPageEnabled: false,
                                    startWithAudioMuted: true, 
                                    startWithVideoMuted: true, 
                                    disableDeepLinking: true
                                },
                                interfaceConfigOverwrite: {
                                    TOOLBAR_BUTTONS: ['fullscreen', 'tileview'],
                                    SHOW_JITSI_WATERMARK: false,
                                    SHOW_WATERMARK_FOR_GUESTS: false
                                }
                            };
                            api = new JitsiMeetExternalAPI(domain, options);
                        }
                    });

                    function stopLive() {
                        if(api) {
                            api.dispose(); 
                            api = null;
                        }
                    }
                </script>
                <style>
                    .pulse-live { 
                        animation: pulse-red 1.5s infinite; font-weight: 800; border-radius: 12px; 
                        padding: 15px; border: 2px solid #fff; letter-spacing: 1px; font-size: 0.95rem;
                    }
                    @keyframes pulse-red {
                        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8); }
                        70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
                        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
                    }
                </style>
            <?php endif; ?>
            <!-- ========================================== -->

            <div id="skeletonLoader">
                <div class="skeleton-box"></div>
                <div class="skeleton-box"></div>
                <div class="skeleton-box"></div>
            </div>

            <div id="realContent">
                <div id="jadwalContainer">
                        <?php 
                        $all_jadwal = array_merge($data_blok1, $data_blok2);
                        
                        if(empty($all_jadwal)): ?>
                            <div class="text-center py-5 text-muted"><br>Belum ada jadwal terdaftar.</div>
                        <?php else: 
                            $delay = 0; $no = 1;
                            foreach($all_jadwal as $j): 
                                $h_en = $map_en[$j['hari']];
                                $is_today = (date('l') == $h_en);
                                $tgl_time = $is_today ? time() : strtotime("next $h_en");
                                
                                $jam_mulai_arr = explode(':', $j['jam_mulai']);
                                $jam_selesai_arr = explode(':', $j['jam_selesai']);
                                
                                $end_time = mktime((int)$jam_selesai_arr[0], (int)$jam_selesai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
                                
                                if ($is_today && time() > $end_time) {
                                    $tgl_time = strtotime("next $h_en");
                                }
                                
                                // INI YANG BIKIN ERROR KEMAREN (KUTIPNYA GUE BENERIN PAKE KUTIP SATU)
                                $kategori_kelas = $j['kategori_kelas'] ?? 'Teori'; // Ambil dr DB, default Teori
                                echo "<div class='jadwal-card-wrapper' data-hari='" . $j['hari'] . "' data-kategori='" . strtoupper($kategori_kelas) . "'>";
                                renderKartu($j, $delay, $bulan_indo, $map_en, $koneksi, $no); 
                                echo "</div>";

                                $delay += 0.15; $no++;
                            endforeach; 
                        endif;
                        ?>
                        <div id="emptyState" class="text-center py-5 text-muted d-none" style="background: white; border-radius: 20px; border: 1px dashed #cbd5e1; margin-top: 20px;">
                            <i class="fas fa-bed mb-3" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <h6 class="fw-bolder">Tidak ada kelas hari ini!</h6>
                            <p class="small mb-0">Selamat menikmati hari liburmu bro.</p>
                        </div>
                    </div> </div> </div> </div> </div>
            

        
        </div>
    </div>
    <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-10 pb-4">
            <span class="text-muted fw-bold" style="font-size: 0.75rem;">&copy; 2026 PSIK 23 D. All Right Reserved</span>
        </div>
    <?php include 'tampilkan_popup.php'; ?>
    <?php include 'popup_gambar.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    <?php
        // Ambil list nama & foto mahasiswa dari DB untuk di-passing ke JS
        $q_mhs = mysqli_query($koneksi, "SELECT id, nama, foto FROM users WHERE role='mahasiswa' ORDER BY nama ASC");
        $data_mhs = [];
        while($r_mhs = mysqli_fetch_assoc($q_mhs)){
            $data_mhs[] = $r_mhs;
        }
    ?>
    const dataMahasiswa = <?= json_encode($data_mhs) ?>;
    // === JURUS CANGGIH: AMBIL DATA SESSION ABSENSI USER DARI PHP ===
    const userAbsenSession = {
        <?php
        $q_all_jadwal = mysqli_query($koneksi, "SELECT id FROM jadwal");
        while($r_j = mysqli_fetch_assoc($q_all_jadwal)){
            $is_done = isset($_SESSION['absen_done_'.$r_j['id']]) ? 'true' : 'false';
            echo $r_j['id'] . ": " . $is_done . ",\n";
        }
        ?>
    };

    // === 1. LOGIKA DRAGGABLE & SPOTIFY PLAYER (DINAMIS DARI DATABASE) ===
    <?php
        $q_playlist_db = mysqli_query($koneksi, "SELECT * FROM playlist_jadwal ORDER BY urutan ASC");
        $playlist_array = [];
        while($pl = mysqli_fetch_assoc($q_playlist_db)){
            $playlist_array[] = '{ title: "'.addslashes($pl['judul']).'", src: "'.addslashes($pl['file_lagu']).'" }';
        }
        $q_autoplay_db = mysqli_query($koneksi, "SELECT nilai FROM pengaturan_jadwal WHERE nama_setting='autoplay_musik'");
        $is_autoplay = (mysqli_fetch_assoc($q_autoplay_db)['nilai'] ?? '0') == '1' ? 'true' : 'false';
    ?>
    
    const playlist = [
        <?= implode(",\n        ", $playlist_array) ?>
    ];
    
    if(playlist.length === 0) {
        playlist.push({ title: "Belum Ada Lagu", src: "" });
    }
    let currentSongIndex = 0;
    
    const audio = document.getElementById('bgAudio');
    const titleEl = document.getElementById('musicTitle');
    const playBtn = document.getElementById('playPauseBtn');
    const vinylBtn = document.getElementById('vinylBtn');
    const miniPlayer = document.getElementById('miniPlayer');
    const wrapper = document.getElementById('musicPlayerWrapper');
    
    const songProgress = document.getElementById('songProgress');
    const currentTimeEl = document.getElementById('currentTime');
    const totalTimeEl = document.getElementById('totalTime');
    const sleepTimerSelect = document.getElementById('sleepTimerSelect');

    let stopAfterThisSong = false;
    let sleepTimeout;

    function formatTime(seconds) {
        if(isNaN(seconds)) return "0:00";
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    }

    loadSong(currentSongIndex);

    function loadSong(index) {
        audio.src = '../assets/music/' + playlist[index].src;
        titleEl.innerText = playlist[index].title;
    }

    function playSong() {
        audio.play().then(() => {
            playBtn.innerHTML = '<i class="fas fa-pause"></i>';
            vinylBtn.classList.add('playing');
        }).catch(e => console.log("Auto-play diblokir browser."));
    }

    function pauseSong() {
        audio.pause();
        playBtn.innerHTML = '<i class="fas fa-play" style="margin-left: 3px;"></i>';
        vinylBtn.classList.remove('playing');
    }

    function togglePlayPause() {
        if (audio.paused) playSong(); else pauseSong();
    }

    function stopMusic() {
        audio.pause();
        audio.currentTime = 0;
        playBtn.innerHTML = '<i class="fas fa-play" style="margin-left: 3px;"></i>';
        vinylBtn.classList.remove('playing');
    }

    function nextSong() {
        currentSongIndex = (currentSongIndex + 1) % playlist.length;
        loadSong(currentSongIndex);
        playSong();
    }

    function prevSong() {
        currentSongIndex = (currentSongIndex - 1 + playlist.length) % playlist.length;
        loadSong(currentSongIndex);
        playSong();
    }

    audio.addEventListener('loadedmetadata', () => {
        songProgress.max = audio.duration;
        totalTimeEl.innerText = formatTime(audio.duration);
    });

    audio.addEventListener('timeupdate', () => {
        songProgress.value = audio.currentTime;
        currentTimeEl.innerText = formatTime(audio.currentTime);
    });

    songProgress.addEventListener('input', () => {
        audio.currentTime = songProgress.value;
    });

    audio.addEventListener('ended', () => {
        if (stopAfterThisSong) {
            stopMusic();
            sleepTimerSelect.value = 'off';
            stopAfterThisSong = false;
        } else {
            nextSong();
        }
    });

    

    // JURUS FILTER GABUNGAN (HARI + DROPDOWN KATEGORI)
    let currentKategori = 'SEMUA';
    let currentHari = '<?= $hari_ini ?>';

    function filterJadwalHari(hariTarget, element) {
        if(element) {
            // Ubah class styling buat node yang diklik
            document.querySelectorAll('.timeline-node').forEach(node => node.classList.remove('active'));
            element.classList.add('active');
            
            const container = element.parentElement;
            container.scrollTo({
                left: element.offsetLeft - (container.clientWidth / 2) + (element.clientWidth / 2),
                behavior: 'smooth'
            });
        }
        currentHari = hariTarget;
        terapkanFilterKombinasi();
    }

    function filterKategori(kategori, element) {
        currentKategori = kategori;
        // Ganti teks di tombol dropdown
        document.getElementById('textFilterAktif').innerText = kategori;
        terapkanFilterKombinasi();
    }

    function terapkanFilterKombinasi() {
        let adaJadwal = false;
        document.querySelectorAll('.jadwal-card-wrapper').forEach(wrapper => {
            let matchHari = (wrapper.getAttribute('data-hari') === currentHari);
            
            // JURUS ANTI GAGAL: Bikin UPPERCASE semua teks sebelum dibandingin!
            let paramKategori = (wrapper.getAttribute('data-kategori') || '').toUpperCase();
            let stateKategori = currentKategori.toUpperCase();
            
            let matchKategori = (stateKategori === 'SEMUA' || paramKategori === stateKategori);

            // Kalau cocok hari DAN kategori (atau kategorinya SEMUA), tampilkan!
            if (matchHari && matchKategori) {
                wrapper.style.display = 'block';
                adaJadwal = true;
            } else {
                wrapper.style.display = 'none';
            }
        });

        // Tampilkan State Kosong kalau gak ada kelas
        const emptyState = document.getElementById('emptyState');
        if(!adaJadwal) {
            emptyState.classList.remove('d-none');
        } else {
            emptyState.classList.add('d-none');
        }
    }

    // Drag and Drop Player
    let isDragging = false, isClick = true;
    let startX, startY, initialX, initialY;

    wrapper.addEventListener('mousedown', dragStart);
    wrapper.addEventListener('touchstart', dragStart, {passive: true});

    function dragStart(e) {
        if (e.target.closest('.mini-player') && !e.target.closest('#vinylBtn')) return; 
        
        isDragging = true;
        isClick = true;
        
        if(e.type === 'touchstart') {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        } else {
            startX = e.clientX;
            startY = e.clientY;
        }
        
        initialX = wrapper.offsetLeft;
        initialY = wrapper.offsetTop;
        
        document.addEventListener('mousemove', dragging);
        document.addEventListener('touchmove', dragging, {passive: false});
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('touchend', dragEnd);
    }

    function dragging(e) {
        if (!isDragging) return;
        let currentX, currentY;
        
        if(e.type === 'touchmove') {
            currentX = e.touches[0].clientX;
            currentY = e.touches[0].clientY;
            e.preventDefault(); 
        } else {
            currentX = e.clientX;
            currentY = e.clientY;
        }
        
        let diffX = currentX - startX;
        let diffY = currentY - startY;

        if(Math.abs(diffX) > 5 || Math.abs(diffY) > 5) isClick = false;

        wrapper.style.left = (initialX + diffX) + 'px';
        wrapper.style.top = (initialY + diffY) + 'px';
        wrapper.style.bottom = 'auto'; 
        wrapper.style.right = 'auto';
    }

    function dragEnd() {
        isDragging = false;
        document.removeEventListener('mousemove', dragging);
        document.removeEventListener('touchmove', dragging);
        document.removeEventListener('mouseup', dragEnd);
        document.removeEventListener('touchend', dragEnd);
    }
    
    vinylBtn.addEventListener('click', (e) => {
        if(isClick) {
            const rect = wrapper.getBoundingClientRect();
            const isRightSide = rect.left > window.innerWidth - 320;
            
            if (rect.top < 350) { 
                wrapper.style.flexDirection = 'column-reverse';
                miniPlayer.style.transformOrigin = isRightSide ? 'top right' : 'top left';
            } else { 
                wrapper.style.flexDirection = 'column';
                miniPlayer.style.transformOrigin = isRightSide ? 'bottom right' : 'bottom left';
            }
            
            if (isRightSide) { wrapper.style.alignItems = 'flex-end'; } 
            else { wrapper.style.alignItems = 'flex-start'; }

            miniPlayer.classList.toggle('hidden');
        }
    });

    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target) && !miniPlayer.classList.contains('hidden')) {
            miniPlayer.classList.add('hidden');
        }
    });

    sleepTimerSelect.addEventListener('change', function() {
        clearTimeout(sleepTimeout); 
        stopAfterThisSong = false; 
        
        const val = this.value;
        if (val === 'off') {
        } else if (val === 'after_song') {
            stopAfterThisSong = true;
        } else {
            const minutes = parseInt(val);
            sleepTimeout = setTimeout(() => {
                stopMusic();
                sleepTimerSelect.value = 'off'; 
                alert('Zzz... Waktu timer habis, musik dihentikan otomatis.');
            }, minutes * 60 * 1000); 
        }
    });


    // === 3. LOGIKA COUNTDOWN & ABSENSI REALTIME ===
    let currentAbsenSelection = {};

    function updateLiveAbsen() {
        let isRiwayatOpen = document.querySelectorAll('.riwayat-collapse.show').length > 0;
        if (isRiwayatOpen) { return; }

        fetch(window.location.pathname + '?ajax_status=true')
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const listAbsenContainer = document.getElementById('list-live-absen-' + item.id);
                    if (listAbsenContainer) {
                        // JURUS 1: Simpan posisi scroll (geseran) saat ini sebelum ditimpa AJAX
                        const scrollBox = listAbsenContainer.querySelector('.overflow-auto');
                        let currentScroll = scrollBox ? scrollBox.scrollLeft : 0;

                        // Timpa data HTML baru dari database
                        listAbsenContainer.innerHTML = item.html_absen;

                        // JURUS 2: Balikin lagi posisi scrollnya biar nggak kerasa ke-reset!
                        const newScrollBox = listAbsenContainer.querySelector('.overflow-auto');
                        if(newScrollBox) {
                            newScrollBox.scrollLeft = currentScroll;
                        }
                    }
                });
            })
            .catch(error => { console.error('Info: Gagal refresh status', error); });
    }

    // === JURUS MESIN PENGGERAK OTOMATIS (AUTO-SCROLL) ===
    setInterval(() => {
        // Cari semua kotak presensi
        document.querySelectorAll('[class*="scroll-absen-"]').forEach(box => {
            // Pastikan isinya emang panjang (bisa digeser)
            if(box.scrollWidth > box.clientWidth) {
                box.scrollLeft += 1; // Kecepatan geser 1px
                
                // Kalau udah mentok mentok ujung kanan, balikin ke kiri lagi
                if(Math.ceil(box.scrollLeft) >= (box.scrollWidth - box.clientWidth)) {
                    box.scrollLeft = 0;
                }
            }
        });
    }, 40); // Interval 40ms = pergerakan mulus kayak jalan tol!

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.modal').forEach(modal => {
            document.body.appendChild(modal);
        });
        function updateCountdowns() {
            const now = new Date().getTime();
            
            // 1. UPDATE TIMER DI DALAM MODAL
            const timers = document.querySelectorAll('.countdown-timer');
            timers.forEach(timer => {
                const start = parseInt(timer.getAttribute('data-start')) * 1000;
                const end = parseInt(timer.getAttribute('data-end')) * 1000;

                if (now < start) {
                    const distance = start - now;
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    let timeStr = '';
                    if(days > 0) timeStr += days + ' Hari ';
                    if(hours > 0) timeStr += hours + ' Jam ';
                    if(minutes > 0) timeStr += minutes + ' Menit ';
                    timeStr += seconds + ' Detik';

                    let colorClass = 'bg-primary text-primary border-primary';
                    let animateClass = '';

                    if (days === 0 && hours === 0) {
                        if (minutes < 15) {
                            colorClass = 'bg-danger text-danger border-danger';
                            animateClass = 'blink';
                        } else {
                            colorClass = 'bg-warning text-warning-emphasis border-warning';
                        }
                    }
                    
                    timer.innerHTML = `
                        <div class="text-muted small fw-bold mb-1" style="font-size: 0.75rem;">Dimulai dalam:</div>
                        <span class="badge ${colorClass} bg-opacity-10 border border-opacity-50 px-3 py-2 fs-6 ${animateClass}">
                            ${timeStr}
                        </span>
                    `;
                } else if (now >= start && now <= end) {
                    const totalDuration = end - start;
                    const passedDuration = now - start;
                    let percentage = (passedDuration / totalDuration) * 100;
                    if(percentage > 100) percentage = 100;

                    timer.innerHTML = `
                        <div class="text-success small fw-bold mb-1 d-flex justify-content-between px-4" style="font-size: 0.75rem;">
                            <span>Sedang Berlangsung</span>
                            <span>${percentage.toFixed(2)}%</span>
                        </div>
                        <div class="px-4">
                            <div class="progress" style="height: 10px; border-radius: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: ${percentage}%;"></div>
                            </div>
                        </div>
                    `;
                } else {
                    timer.innerHTML = `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-50 px-3 py-2 fs-6 mt-3">Telah Berakhir</span>`;
                }
            });

            // 2. LOGIKA BARU: KONTROL TAMPILAN DI KARTU (LUAR MODAL)
            const timerLuars = document.querySelectorAll('.timer-luar');
            timerLuars.forEach(tl => {
                const id = tl.id.split('-')[2];
                // Cari data waktu dari timer modal yang tersembunyi
                const modalTimer = document.querySelector('#modalJadwal' + id + ' .countdown-timer');
                if(!modalTimer) return;

                const start = parseInt(modalTimer.getAttribute('data-start')) * 1000;
                const end = parseInt(modalTimer.getAttribute('data-end')) * 1000;
                const swipeTrack = document.getElementById('swipe-track-' + id);
                
                if (now < start) {
                    // KELAS BELUM MULAI -> Munculin Countdown, Sembunyiin Geseran
                    const distance = start - now;
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24)); // INI DIA OBATNYA BRO!
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    // Format Teks Auto-Adjust (Kalau ada hari, dimunculin)
                    let text = '';
                    if(days > 0) text += days + 'h ';
                    if(hours > 0 || days > 0) text += hours + 'j ';
                    text += minutes + 'm ' + seconds + 's';
                    
                    // STYLE 2: SPLIT LABEL
                    tl.innerHTML = `
                        <div class="d-inline-flex align-items-center mb-2 shadow-sm" style="border: 1.5px solid #1e293b; border-radius: 8px; overflow: hidden;">
                            <div class="bg-dark text-white px-2 py-1 fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                <i class="fas fa-lock me-1"></i> TERBUKA DALAM
                            </div>
                            <div class="bg-white text-dark px-2 py-1 fw-bolder" style="font-size: 0.85rem; font-family: 'Courier New', monospace;">
                                ${text}
                            </div>
                        </div>
                    `;
                    tl.classList.remove('d-none');
                    if(swipeTrack) swipeTrack.classList.add('d-none');
                    
                } else if (now >= start && now <= end) {
                    // KELAS BERLANGSUNG -> Hapus Countdown, Munculin Geseran!
                    tl.innerHTML = '';
                    tl.classList.add('d-none');
                    if(swipeTrack) swipeTrack.classList.remove('d-none');
                    
                } else {
                    // KELAS SELESAI
                    tl.innerHTML = `<div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill shadow-sm">Kelas Selesai</div>`;
                    tl.classList.remove('d-none');
                    if(swipeTrack) swipeTrack.classList.add('d-none');
                }
            });
        }

        updateCountdowns();
        setInterval(updateCountdowns, 1000);
        setInterval(updateLiveAbsen, 10000); 

        function updateRealTimeClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const day = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            // Jam untuk Header/Main
            const clockEl = document.getElementById('clockText');
            if(clockEl) {
                clockEl.innerHTML = `${day}, ${date} ${month} ${year} <br> ${hours}:${minutes}:${seconds} WIB`;
            }
            
            // Jam untuk Dynamic Island
            const islandClockEl = document.getElementById('clockTextIsland');
            if(islandClockEl) {
                islandClockEl.innerHTML = `${hours}:${minutes}:${seconds} WIB`;
            }
        }
        setInterval(updateRealTimeClock, 1000);
        updateRealTimeClock();
        terapkanFilterKombinasi();
    });

    // ========================================================
    // JURUS CANGGIH 2: IOS MODAL TRIGGER & AUTO-TIME LOGIC
    // ========================================================
    // Tambah parameter startTimeTimestamp di dalam kurung!
    function triggerIosAbsen(jadwalId, matkul, startTimeTimestamp) {
        const overlay = document.getElementById('iosAbsenOverlay');
        const content = document.getElementById('iosAbsenContent');
        overlay.classList.add('active');

        // ==========================================
        // KODE BARU: CEK KELAS UDAH MULAI / BELUM
        // ==========================================
        const currentTime = Math.floor(Date.now() / 1000); 
        if (currentTime < startTimeTimestamp) {
            content.innerHTML = `
                <i class="fas fa-lock text-secondary mb-3" style="font-size: 4rem;"></i>
                <h5 class="fw-bolder text-dark">Kelas Belum Dimulai!</h5>
                <p class="text-muted small mb-4">Bro, kelas <b>${matkul}</b> belum waktunya mulai. Sabar ya, tunggu jadwalnya tiba baru bisa absen!</p>
                <button class="btn btn-secondary w-100 rounded-pill fw-bold shadow-sm" onclick="document.getElementById('iosAbsenOverlay').classList.remove('active')">Oke, Mengerti</button>
            `;
            return; // Stop disini, jangan lanjut ngecek database
        }

        // State 1: Animasi Loading (Memeriksa Database)
        content.innerHTML = `
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <h5 class="fw-bolder text-dark">Memeriksa Database...</h5>
            <p class="text-muted small mb-0">Mohon tunggu sebentar, mencari namamu di kelas <b>${matkul}</b>.</p>
        `;

        setTimeout(() => {
            if(userAbsenSession[jadwalId]) {
                // State 2A: Sudah Absen
                content.innerHTML = `
                    <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-bolder text-dark">Berhasil!</h5>
                    <p class="text-muted small mb-4">Nama kamu sudah tercatat di database kelas ini.</p>
                    <button class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm" onclick="bukaModalJadwal(${jadwalId})">Lihat Jadwal</button>
                `;
            } else {
                // State 2B: TAMPILAN GRID NAMA (TAP & GO)
                content.innerHTML = `
                    <h5 class="fw-bolder text-dark mb-1">Siapa Kamu?</h5>
                    <p class="text-muted small mb-3">Pilih namamu untuk mengisi presensi</p>
                    <input type="text" id="searchMhsInput" class="form-control form-control-sm text-center rounded-pill mb-3" placeholder="Cari nama kamu..." onkeyup="filterMhs(${jadwalId})" autocomplete="off">
                    <div id="mhsGridContainer" style="max-height: 250px; overflow-y: auto; padding: 2px;"></div>
                `;
                setTimeout(() => {
                    document.getElementById('searchMhsInput').focus();
                    filterMhs(jadwalId); // Panggil fungsi render grid otomatis
                }, 100);
            }
        }, 1500); 
    }

    window.filterMhs = function(jadwalId) {
        const keyword = document.getElementById('searchMhsInput').value.toLowerCase();
        const container = document.getElementById('mhsGridContainer');
        container.innerHTML = '';

        dataMahasiswa.forEach(mhs => {
            if (mhs.nama.toLowerCase().includes(keyword)) {
                // Atur path foto, kalo fotonya default/kosong, pake ui-avatars
                let fotoPath = '';
                if(mhs.foto && mhs.foto !== 'default.jpg' && mhs.foto !== 'default.png') {
                    fotoPath = '../assets/img/mahasiswa/' + mhs.foto;
                } else {
                    fotoPath = 'https://ui-avatars.com/api/?background=random&name=' + encodeURIComponent(mhs.nama);
                }

                const chip = document.createElement('div');
                chip.className = 'mhs-chip';
                // WAKTU DI KLIK, DIA NGIRIM ID, BUKAN CUMA NAMA
                chip.onclick = () => submitIosAbsen(jadwalId, mhs.id, mhs.nama);
                chip.innerHTML = `
                    <img src="${fotoPath}" onerror="this.src='https://ui-avatars.com/api/?background=random&name=${encodeURIComponent(mhs.nama)}'">
                    <span>${mhs.nama}</span>
                `;
                container.appendChild(chip);
            }
        });

        if(container.innerHTML === '') {
            container.innerHTML = '<div class="text-center text-muted small mt-3">Nama tidak ditemukan</div>';
        }
    };

    function submitIosAbsen(jadwalId, userId, namaTerpilih) {
        const nama = namaTerpilih;
        if(nama.trim() === '') {
            alert('Nama tidak boleh kosong bro!');
            return;
        }

        const content = document.getElementById('iosAbsenContent');
        content.innerHTML = `
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <h5 class="fw-bolder text-dark">Menyinkronkan...</h5>
        `;

        const formData = new FormData();
        formData.append('action', 'submit_absen');
        formData.append('jadwal_id', jadwalId);
        formData.append('user_id', userId);
        formData.append('nama', nama);
        formData.append('status', 'hadir_auto'); // Kirim sinyal ke PHP biar auto-kalkulasi waktu!

        fetch(window.location.pathname, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                userAbsenSession[jadwalId] = true; // Update local state session
                
                // Tentukan warna UI berdasarkan balikan PHP
                let statWarna = 'text-success';
                let statIcon = 'fa-check-circle';
                if(data.status_tercatat === 'terlambat') { statWarna = 'text-warning'; statIcon = 'fa-running'; }
                if(data.status_tercatat === 'alpha') { statWarna = 'text-danger'; statIcon = 'fa-times-circle'; }

                content.innerHTML = `
                    <i class="fas ${statIcon} ${statWarna} mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-bolder text-dark">Sukses!</h5>
                    <p class="text-muted small mb-4">Namamu tercatat dengan status: <strong class="${statWarna} text-uppercase">${data.status_tercatat}</strong></p>
                    <button class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm" onclick="bukaModalJadwal(${jadwalId})">Lanjut ke Jadwal</button>
                `;
                
                // Update UI riwayat kehadiran di belakang layar
                updateLiveAbsen(); 
                
                // Tampilkan tombol Hapus di Modal Bootstrap (jaga-jaga dia mau hapus absen)
                document.getElementById('motivate-box-' + jadwalId).classList.remove('d-none');
            } else {
                alert('Gagal: ' + data.msg);
                document.getElementById('iosAbsenOverlay').classList.remove('active');
            }
        }).catch(error => {
            alert('Terjadi kesalahan jaringan.');
            document.getElementById('iosAbsenOverlay').classList.remove('active');
        });
    }

    // Fungsi transisi buat nutup iOS Modal dan buka Schedule Modal aslinya
    function bukaModalJadwal(jadwalId) {
        document.getElementById('iosAbsenOverlay').classList.remove('active');
        const modal = new bootstrap.Modal(document.getElementById('modalJadwal' + jadwalId));
        modal.show();
    }

    function hapusAbsen(id) {
        if(confirm('Yakin ingin membatalkan dan menghapus data kehadiranmu untuk jadwal ini?')) {
            const formData = new FormData();
            formData.append('action', 'delete_absen');
            formData.append('jadwal_id', id);

            fetch(window.location.pathname, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    userAbsenSession[id] = false; // Update local state session
                    document.getElementById('motivate-box-' + id).classList.add('d-none');
                    
                    fetch(window.location.pathname + '?ajax_status=true')
                    .then(r => r.json())
                    .then(d => {
                        d.forEach(item => {
                            const listAbsenContainer = document.getElementById('list-live-absen-' + item.id);
                            if (listAbsenContainer) { listAbsenContainer.innerHTML = item.html_absen; }
                        });
                    });
                } else {
                    alert('Gagal menghapus data.');
                }
            }).catch(error => {
                alert('Terjadi kesalahan jaringan.');
            });
        }
    }

    // ==========================================
    // 5. TICKER NAMA ABSENSI (GANTI TIAP 1.5 DETIK)
    // ==========================================
    document.addEventListener('DOMContentLoaded', () => {
        const tickers = document.querySelectorAll('.ticker-absen');
        
        tickers.forEach(ticker => {
            try {
                const encodedData = ticker.getAttribute('data-namas');
                if (!encodedData) return;
                
                const decodedData = atob(encodedData);
                const namas = JSON.parse(decodedData);
                
                if (namas && namas.length > 1) {
                    let idx = 0;
                    setInterval(() => {
                        ticker.style.opacity = '0'; 
                        
                        setTimeout(() => {
                            idx = (idx + 1) % namas.length;
                            const div = document.createElement('div');
                            div.innerHTML = namas[idx]; 
                            ticker.innerHTML = div.innerHTML; 
                            
                            ticker.style.opacity = '1'; 
                        }, 400); 
                        
                    }, 1500); 
                }
            } catch(e) {
                console.error("Error parsing ticker absen data:", e);
            }
        });
    });
    window.initSwipe = function(e, jadwalId, matkul, startTime) {
        if(e.cancelable) e.preventDefault(); 
        
        const track = document.getElementById('swipe-track-' + jadwalId);
        const thumb = document.getElementById('swipe-thumb-' + jadwalId);
        const text = document.getElementById('swipe-text-' + jadwalId);
        
        // MATIKAN ANIMASI "MINTA DIGESER" SAAT JARI MULAI MENYENTUH
        thumb.classList.remove('hint-anim');
        
        let isDragging = true;
        let startX = e.type.includes('mouse') ? e.pageX : e.touches[0].pageX;
        let thumbLeft = 3; 
        const maxDrag = track.clientWidth - thumb.clientWidth - 6; 

        function onMove(moveEvent) {
            if (!isDragging) return;
            let currentX = moveEvent.type.includes('mouse') ? moveEvent.pageX : moveEvent.touches[0].pageX;
            let diffX = currentX - startX;
            
            let newLeft = thumbLeft + diffX;
            if (newLeft < 3) newLeft = 3;
            if (newLeft > maxDrag + 3) newLeft = maxDrag + 3;
            
            thumb.style.transform = `translateX(${newLeft - 3}px)`;
            text.style.opacity = 1 - (newLeft / maxDrag); 
            
            if (newLeft >= maxDrag + 3) {
                isDragging = false;
                cleanup();
                
                thumb.style.background = "#10b981";
                thumb.innerHTML = '<i class="fas fa-unlock"></i>';
                
                setTimeout(() => {
    thumb.style.transform = `translateX(0px)`;
    thumb.style.background = "transparent";  // ← HAPUS BIRUNYA!
    thumb.innerHTML = '<img src="../assets/img/gestur.jpg?v=' + Date.now() + '" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">';
    text.style.opacity = 1;
    thumb.classList.add('hint-anim');
    
    triggerIosAbsen(jadwalId, matkul, startTime);
}, 400);
            }
        }

        function cleanup() {
            isDragging = false;
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('mouseup', cleanup);
            document.removeEventListener('touchend', cleanup);
            
            // Kalau gagal buka (dilepas di tengah jalan), balikin & nyalain lagi animasinya
            if(thumb.style.transform !== `translateX(${maxDrag}px)`) {
                thumb.style.transform = `translateX(0px)`;
                text.style.opacity = 1;
                setTimeout(() => thumb.classList.add('hint-anim'), 150);
            }
        }

        document.addEventListener('mousemove', onMove);
        document.addEventListener('touchmove', onMove, {passive: false});
        document.addEventListener('mouseup', cleanup);
        document.addEventListener('touchend', cleanup);
    };
    </script>
</body>
</html>

<?php
function renderKartu($j, $delay, $bulan_indo, $map_en, $koneksi, $no) {
    $foto_dosen = !empty($j['foto_dosen']) ? '../assets/img/dosen/'.$j['foto_dosen'] : 'https://ui-avatars.com/api/?background=random&name='.urlencode($j['nama_dosen'] ?? 'Dosen');
    $is_online = ($j['jenis_kelas'] == 'online');
    
    $file_string = $j['foto_ruangan'];
    $files = [];
    if (!empty($file_string) && $file_string != 'default_room.jpg' && $file_string != 'default_online.jpg') {
        $files = explode(',', $file_string);
    }
    
    $jam_mulai = date('H:i', strtotime($j['jam_mulai']));
    $jam_selesai = date('H:i', strtotime($j['jam_selesai']));
    // --- TAMBAHAN LOGIKA WARNA TIMELINE ---
    $jam_angka = (int)date('H', strtotime($j['jam_mulai']));
    if ($jam_angka >= 6 && $jam_angka <= 10) { 
        $warna_waktu = '#f59e0b'; // Pagi: Orange/Kuning
    } elseif ($jam_angka >= 11 && $jam_angka <= 14) { 
        $warna_waktu = '#ef4444'; // Siang: Merah
    } elseif ($jam_angka >= 15 && $jam_angka <= 18) { 
        $warna_waktu = '#10b981'; // Sore: Hijau
    } else { 
        $warna_waktu = '#6366f1'; // Malam: Ungu/Indigo
    }
    
    $sudah_absen = isset($_SESSION['absen_done_'.$j['id']]);
    $html_absen_awal = generateHTMLAbsen($koneksi, $j['id']);

    $h_en = $map_en[$j['hari']];
    $is_today = (date('l') == $h_en);
    
    $jam_mulai_arr = explode(':', $j['jam_mulai']);
    $jam_selesai_arr = explode(':', $j['jam_selesai']);
    
    $tgl_time = $is_today ? time() : strtotime("next $h_en");
    $start_time = mktime((int)$jam_mulai_arr[0], (int)$jam_mulai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
    $end_time = mktime((int)$jam_selesai_arr[0], (int)$jam_selesai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
    
    if ($is_today && time() > $end_time) {
        $tgl_time = strtotime("next $h_en");
        $start_time = mktime((int)$jam_mulai_arr[0], (int)$jam_mulai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
        $end_time = mktime((int)$jam_selesai_arr[0], (int)$jam_selesai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
    }
    
    $tgl_format = date('d', $tgl_time) . ' ' . $bulan_indo[(int)date('m', $tgl_time)] . ' ' . date('Y', $tgl_time);

    $gcal_start = gmdate("Ymd\THis\Z", $start_time);
    $gcal_end = gmdate("Ymd\THis\Z", $end_time);
    $gcal_title = urlencode("Kuliah: " . $j['mata_kuliah']);
    $gcal_location = urlencode(($is_online) ? $j['link_online'] : $j['ruangan']);
    $gcal_link = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gcal_title}&dates={$gcal_start}/{$gcal_end}&location={$gcal_location}";

    ?>

    <?php 
    // Logika ngecek apakah kelas ini SEDANG BERLANGSUNG sekarang
    $is_live_now = ($is_today && time() >= $start_time && time() <= $end_time);
    $live_class = $is_live_now ? 'is-live' : '';

    // LOGIKA KELAS DIBATALKAN / BELAJAR MANDIRI
    $status_kelas = $j['status_kelas'] ?? 'Aktif';
    $is_locked = ($status_kelas == 'Dibatalkan' || $status_kelas == 'Belajar Mandiri');
    $lock_bg = ($status_kelas == 'Dibatalkan') ? 'bg-danger' : 'bg-warning text-dark';
    $lock_icon = ($status_kelas == 'Dibatalkan') ? 'fa-ban' : 'fa-laptop-house';
    $lock_msg = ($status_kelas == 'Dibatalkan') ? 'Kelas ditiadakan hari ini' : 'Dosen menugaskan asynchronous';
    $lock_class = ($status_kelas == 'Dibatalkan') ? 'batal' : 'mandiri';
    ?>

    <div class="mb-3 <?= $live_class ?>" style="animation-delay: <?= $delay ?>s; animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0;">
        <div class="timeline-container">
            <div class="timeline-dot" style="border-color: <?= $is_locked ? ($status_kelas == 'Dibatalkan' ? '#ef4444' : '#f59e0b') : $warna_waktu ?> !important;"></div>
            
            <div class="timeline-card position-relative text-start border-0" id="card-jadwal-<?= $j['id'] ?>">
                
                <?php if($is_locked): 
    // 🚀 FIX: Baca langsung, karena DB udah nyimpen WIB (dari date_default_timezone_set)
    $waktu_info = isset($j['waktu_update']) && !empty($j['waktu_update']) 
        ? date('d/m/Y H:i', strtotime($j['waktu_update'])) 
        : date('d/m/Y H:i');
    $aksi_dosen = ($status_kelas == 'Dibatalkan') ? 'dibatalkan' : 'dialihkan ke Asinkronus';
?>
                <div class="locked-overlay <?= $lock_class ?> p-2" style="z-index: 30;">
                    <div class="d-flex flex-column align-items-center justify-content-center w-100 h-100 p-3" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.6); border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-y: auto;">
                        
                        <div class="badge <?= $lock_bg ?> rounded-pill px-3 py-1 mb-2 shadow-sm" style="font-size: 0.7rem;">
                            <i class="fas <?= $lock_icon ?> me-1"></i><?= $status_kelas ?>
                        </div>
                        
                        <div class="fw-bold text-dark mb-1 text-center" style="font-size: 0.8rem; line-height: 1.3;">
                            MK <?= $j['mata_kuliah'] ?> <span class="<?= ($status_kelas == 'Dibatalkan') ? 'text-danger' : 'text-warning' ?>"><?= $aksi_dosen ?></span> oleh dosen <?= $j['nama_dosen'] ?? 'pengampu' ?>
                        </div>
                        
                        <div class="text-muted fst-italic mb-2 text-center" style="font-size: 0.65rem;">
                            <i class="far fa-clock me-1"></i>Diinformasikan: <?= $waktu_info ?> WIB
                        </div>
                        
                        <?php if(!empty($j['catatan'])): ?>
                        <div class="w-100 bg-light border rounded text-start p-2 mt-1" style="font-size: 0.7rem; color: #334155; line-height: 1.4;">
                            <span class="fw-bolder d-block mb-1 text-info"><i class="fas fa-info-circle me-1"></i>Catatan:</span>
                            <span class="fst-italic"><?= $j['catatan'] ?></span>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endif; ?>

                <style>
                    /* JURUS ANIMASI BADGE BERGANTIAN (SLOT MACHINE) */
                    .badge-flipper-container { height: 22px; overflow: hidden; position: relative; }
                    .badge-flipper { 
                        display: flex; flex-direction: column; 
                        animation: flipBadgeUp 6s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite; 
                    }
                    .badge-flip-item { 
                        height: 22px; display: inline-flex; align-items: center; justify-content: center; 
                        margin-bottom: 5px; font-size: 0.65rem; 
                    }
                    @keyframes flipBadgeUp {
                        0%, 40% { transform: translateY(0); }
                        50%, 90% { transform: translateY(-27px); } /* 22px tinggi + 5px margin */
                        100% { transform: translateY(0); }
                    }
                    /* CSS TIKET PREMIUM - COLORFUL & RESPONSIVE */
                    .ticket-box {
                        border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff;
                        margin-bottom: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.04);
                        display: flex; flex-direction: column; position: relative;
                        transition: transform 0.2s;
                    }
                    .ticket-box:active { transform: scale(0.98); }
                    
                    .ticket-top {
                        display: flex; align-items: stretch; border-bottom: 2px dashed #cbd5e1;
                        position: relative; background: #ffffff; border-radius: 16px 16px 0 0; overflow: hidden;
                    }
                    .ticket-top::before, .ticket-top::after {
                        content: ''; position: absolute; bottom: -12px; width: 24px; height: 24px;
                        background: #f4f7fb; border-radius: 50%; border: 1px solid #e2e8f0; z-index: 2;
                    }
                    .ticket-top::before { left: -13px; border-right-color: transparent; border-bottom-color: transparent; transform: rotate(-45deg); }
                    .ticket-top::after { right: -13px; border-left-color: transparent; border-bottom-color: transparent; transform: rotate(45deg); }

                    .ticket-left {
                        width: 35%; padding: 15px 10px; display: flex; flex-direction: column; justify-content: center; align-items: center;
                        border-right: 2px dashed rgba(255,255,255,0.4); text-align: center; position: relative; overflow: hidden;
                    }
                    /* Ornamen Pattern di kiri biar kelihatan eksklusif */
                    .ticket-left::before { content: ''; position: absolute; top: -15px; right: -15px; width: 65px; height: 65px; background: rgba(255,255,255,0.15); border-radius: 50%; }
                    .ticket-left::after { content: ''; position: absolute; bottom: -20px; left: -20px; width: 70px; height: 70px; background: rgba(255,255,255,0.1); border-radius: 50%; }
                    
                    .ticket-right { width: 65%; padding: 15px; display: flex; flex-direction: column; justify-content: center; text-align: left; background: #ffffff; position: relative; z-index: 1;}
                    
                    .ticket-bottom {
                        padding: 12px 15px; background: #f8fafc; border-radius: 0 0 16px 16px;
                        display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;
                    }

                    .t-time-main { font-size: 1.4rem; font-weight: 900; color: #ffffff; line-height: 1.1; margin-bottom: 2px; position: relative; z-index: 1;}
                    .t-time-sub { font-size: 0.75rem; font-weight: 700; color: rgba(255,255,255,0.9); margin-bottom: 10px; position: relative; z-index: 1;}
                    .t-title { font-size: 1.05rem; font-weight: 800; color: #1e293b; line-height: 1.3; margin-bottom: 8px; }
                    .t-info { font-size: 0.75rem; font-weight: 700; color: #64748b; display: flex; align-items: center; gap: 5px; margin-bottom: 8px; }
                    .t-tag { font-size: 0.65rem; font-weight: 800; padding: 4px 8px; border-radius: 6px; background: #f1f5f9; color: #475569; display: inline-block; letter-spacing: 0.5px;}
                    
                    .t-dosen { font-size: 0.8rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; text-align: left; flex: 1; min-width: 150px;}
                    .t-dosen img { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 2px solid #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex-shrink: 0; }
                    
                    .t-room { font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 6px; text-align: left; }
                    .icon-circle { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

                    /* JURUS MOBILE RESPONSIVE TINGKAT DEWA */
                    @media (max-width: 450px) {
                        .ticket-left { width: 36%; padding: 12px 6px; }
                        .ticket-right { width: 64%; padding: 12px; }
                        .t-time-main { font-size: 1.15rem; }
                        .t-time-sub { font-size: 0.65rem; margin-bottom: 8px;}
                        .t-title { font-size: 0.95rem; margin-bottom: 6px;}
                        .t-dosen { min-width: 100%; margin-bottom: 5px;}
                    }
                    
                    .pulse-live-small { animation: pulse-red-small 1.5s infinite; }
                    @keyframes pulse-red-small {
                        0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.5); }
                        70% { box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); }
                        100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
                    }
                </style>

                <?php
                    // LOGIKA WAKTU & STATUS (Kelap-kelip Live Adaptif dengan Background Warna)
                    $now_time = time();
                    $status_kelas_teks = "";
                    $status_kelas_badge = "";
                    
                    if ($is_today && $now_time >= $start_time && $now_time <= $end_time) {
                        $status_kelas_teks = '<i class="fas fa-circle blink text-danger"></i> LIVE NOW';
                        $status_kelas_badge = 'bg-white text-dark pulse-live-small border-0 shadow-sm';
                    } elseif ($is_today && $now_time > $end_time) {
                        $status_kelas_teks = '<i class="fas fa-check-double"></i> SELESAI';
                        $status_kelas_badge = 'bg-dark bg-opacity-25 text-white border-0';
                    } else {
                        $status_kelas_teks = '<i class="fas fa-hourglass-half"></i> BELUM MULAI';
                        $status_kelas_badge = 'bg-white bg-opacity-25 text-white border-0';
                    }
                ?>

                <!-- WADAH TIKET UTAMA -->
                <div class="ticket-box mt-3">
                    <div class="ticket-top">
                        <!-- KIRI: WAKTU & STATUS (Pewarnaan Dinamis Gradient dari Pagi/Siang/Sore/Malam) -->
                        <div class="ticket-left" style="background: linear-gradient(135deg, <?= $warna_waktu ?> 0%, rgba(0,0,0,0.2) 200%); background-color: <?= $warna_waktu ?>;">
                            <div class="t-time-main"><?= $jam_mulai ?></div>
                            <div class="t-time-sub">s/d <?= $jam_selesai ?> WIB</div>
                            <span class="badge <?= $status_kelas_badge ?> rounded-pill px-2 py-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">
                                <?= $status_kelas_teks ?>
                            </span>
                        </div>
                        
                        <!-- KANAN: INFO MATA KULIAH -->
                        <div class="ticket-right">
                            <div class="d-flex justify-content-between align-items-start mb-2 w-100">
                                <!-- INI DIA WADAH BADGE BERGANTIANNYA -->
                                <?php
                                    $kat = $j['kategori_kelas'] ?? 'Teori';
                                    if ($kat == 'UTS') { $label_kat = 'Ujian Tengah Semester'; } 
                                    elseif ($kat == 'UAS') { $label_kat = 'Ujian Akhir Semester'; } 
                                    else { $label_kat = 'Kelas ' . $kat; }
                                ?>
                                <div class="badge-flipper-container">
                                    <div class="badge-flipper">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2 py-1 shadow-sm badge-flip-item">
                                            Pertemuan Ke-<?= $j['pertemuan_ke'] ?>
                                        </span>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-2 py-1 shadow-sm badge-flip-item">
                                            <i class="fas fa-tags me-1"></i> <?= $label_kat ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="t-title"><?= $j['mata_kuliah'] ?></div>
                            
                            <div class="t-info">
                                 <?= $j['hari'] ?>, <?= $tgl_format ?>
                            </div>
                            
                            <div class="d-flex gap-1 mt-1 flex-wrap">
                                <span class="t-tag"><?= $j['blok'] ?? '-' ?></span>
                                <span class="t-tag">Kode MK: <?= $j['kode_mk'] ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- BAWAH: DOSEN & RUANGAN (100% Anti Kepotong, Rapi di Mobile) -->
                    <div class="ticket-bottom" style="display: flex; flex-direction: column; gap: 8px; align-items: flex-start;">
                        
                        <!-- Baris Dosen (Dibuat Full Lebar Biar Bebas Melar) -->
                        <div class="t-dosen w-100">
                            <img src="<?= $foto_dosen ?>" alt="Dosen">
                            <span style="white-space: normal; word-break: break-word; line-height: 1.3;">
                                <?= $j['nama_dosen'] ?? 'Belum ditentukan' ?>
                            </span>
                        </div>
                        
                        <!-- Baris Bawah: Ruangan (Kiri) & Tombol QR (Kanan) -->
                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                            <div class="t-room">
                                <?php if($is_online): ?>
                                    <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-video" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="text-primary fw-bolder">Online Class</span>
                                <?php else: ?>
                                    <div class="icon-circle bg-secondary bg-opacity-10 text-secondary">
                                        <i class="fas fa-map-marker-alt" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="text-dark"><?= $j['ruangan'] ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- 🚀 TOMBOL IKON QR CODE PINDAH KE SINI 🚀 -->
                            <button class="btn btn-sm border border-secondary border-opacity-25 shadow-sm rounded-circle d-flex align-items-center justify-content-center bg-white" style="width: 34px; height: 34px; flex-shrink: 0; transition: transform 0.2s; z-index: 10;" onmouseover="this.style.transform='scale(1.15) rotate(5deg)'" onmouseout="this.style.transform='scale(1) rotate(0deg)'" data-bs-toggle="modal" data-bs-target="#qrModal<?= $j['id'] ?>" title="Bagikan Jadwal Ini">
                                <i class="fas fa-qrcode text-dark" style="font-size: 0.95rem;"></i>
                            </button>
                        </div>
                        
                    </div>
                </div>

                <?php 
                    // JURUS JOIN: Ambil data foto dari tabel users
                    $q_absen_ticker = mysqli_query($koneksi, "
                        SELECT a.nama, a.status, a.tanggal, u.foto 
                        FROM absensi_jadwal a 
                        LEFT JOIN users u ON a.nama = u.nama 
                        WHERE a.jadwal_id = '".$j['id']."' 
                        ORDER BY a.tanggal DESC
                    ");
                    $absen_list = [];
                    while($row_ab = mysqli_fetch_assoc($q_absen_ticker)){
                        $jam_absen = date('H:i', strtotime($row_ab['tanggal'])) . ' WIB';
                        $stat = strtolower($row_ab['status']);
                        
                        if($stat == 'hadir') { $c = 'text-success'; $i = '✅'; }
                        elseif($stat == 'sakit') { $c = 'text-warning'; $i = '💊'; }
                        elseif($stat == 'izin') { $c = 'text-info'; $i = '💌'; }
                        elseif($stat == 'terlambat') { $c = 'text-secondary'; $i = '🏃'; }
                        else { $c = 'text-danger'; $i = '❌'; }
                        
                        // Cek Foto Avatar (Biar bentuknya bulat kecil)
                        $fotoPath = (!empty($row_ab['foto']) && $row_ab['foto'] !== 'default.jpg' && $row_ab['foto'] !== 'default.png') ? '../assets/img/mahasiswa/' . $row_ab['foto'] : 'https://ui-avatars.com/api/?background=random&name=' . urlencode($row_ab['nama']);
                        
                        // Format HTML ke dalam Array Ticker (Ditambah <img avatar> di depannya)
                        $absen_list[] = '<div class="d-inline-flex align-items-center gap-1"><img src="'.$fotoPath.'" class="rounded-circle shadow-sm" style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; object-fit: cover;" onerror="this.src=\'https://ui-avatars.com/api/?background=random&name='.urlencode($row_ab['nama']).'\'"> <span>'.htmlspecialchars($row_ab['nama']).'</span> <span class="'.$c.' fw-bolder ms-1">'.$i.' ' . ucfirst($stat) . '</span> <span class="text-muted ms-1" style="font-size:0.65rem;">· ' . $jam_absen . '</span></div>';
                    }
                    $absen_encoded = base64_encode(json_encode($absen_list));
                ?>
                <div class="mt-1 w-100 text-start mb-2">
                    <div class="badge bg-light text-dark border border-secondary border-opacity-25 rounded-pill px-3 py-2 text-start w-100 d-flex align-items-center shadow-sm" style="overflow: visible; gap: 4px; flex-wrap: wrap;">
                        <span class="fw-bold text-dark ticker-absen w-100" style="font-size: 0.75rem; transition: opacity 0.4s ease-in-out; white-space: normal; word-break: break-word; flex: 1;" data-namas="<?= $absen_encoded ?>">
                            <?= count($absen_list) > 0 ? $absen_list[0] : '<span class="text-muted fst-italic">Belum ada absen</span>' ?>
                        </span>
                    </div>
                </div>

                <?php if(!$is_locked): ?>
                <div id="absen-controller-<?= $j['id'] ?>" class="mt-2">
                    <div class="timer-luar text-center" id="timer-luar-<?= $j['id'] ?>"></div>
                    
                    <div class="swipe-track d-none" id="swipe-track-<?= $j['id'] ?>">
                        <div class="swipe-text" id="swipe-text-<?= $j['id'] ?>">
                            Geser untuk Buka
                            <span class="animated-arrows">
                                <i class="fas fa-chevron-right"></i>
                                <i class="fas fa-chevron-right"></i>
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </div>
                        <div class="swipe-thumb hint-anim" id="swipe-thumb-<?= $j['id'] ?>" 
                             onmousedown="initSwipe(event, <?= $j['id'] ?>, '<?= addslashes($j['mata_kuliah']) ?>', <?= $start_time ?>)" 
                             ontouchstart="initSwipe(event, <?= $j['id'] ?>, '<?= addslashes($j['mata_kuliah']) ?>', <?= $start_time ?>)">
                            <img src="../assets/img/gestur.jpg?v=<?= time() ?>" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <?php 
        $q_absen_ticker = mysqli_query($koneksi, "SELECT nama, status FROM absensi_jadwal WHERE jadwal_id = '".$j['id']."' ORDER BY id ASC");
        $absen_list = [];
        while($row_ab = mysqli_fetch_assoc($q_absen_ticker)){
            $icon_stat = '';
            if($row_ab['status'] == 'hadir') $icon_stat = '✅';
            elseif($row_ab['status'] == 'sakit') $icon_stat = '💊';
            elseif($row_ab['status'] == 'izin') $icon_stat = '💌';
            elseif($row_ab['status'] == 'terlambat') $icon_stat = '🏃';
            elseif($row_ab['status'] == 'alpha') $icon_stat = '❌';
            
            $absen_list[] = $icon_stat . ' ' . htmlspecialchars($row_ab['nama']) . " - <span class='text-uppercase'>" . $row_ab['status'] . "</span>";
        }
        $absen_encoded = base64_encode(json_encode($absen_list));
    ?>
        </div>
        
        <div class="modal fade" id="modalJadwal<?= $j['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content clean-modal">
                    <div class="modal-body p-0">
                        <div class="jadwal-list-card m-0" style="border: none;">
                            <div class="position-relative">
                                <div class="badge-hari">Hari/Tgl: <?= $j['hari'] ?>, <?= $tgl_format ?></div>
                                
                                <a href="<?= $gcal_link ?>" target="_blank" class="btn-calendar-bell" title="Set Pengingat">
                                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                </a>

                                <div class="position-relative" style="height: auto; overflow: hidden;">
                                    <?php if(count($files) > 0): ?>
                                        <div id="carouselJadwal<?= $j['id'] ?>" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-pause="false">
                                            <div class="carousel-inner">
                                                <?php 
                                                $first = true;
                                                foreach($files as $f): 
                                                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                                    $is_vid = in_array($ext, ['mp4','webm','ogg']);
                                                    $active = $first ? 'active' : '';
                                                ?>
                                                    <div class="carousel-item <?= $active ?>" data-bs-interval="3000">
                                                        <?php if($is_vid): ?>
                                                            <video src="../assets/img/ruangan/<?= $f ?>" class="d-block w-100" style="height: 180px; object-fit: cover;" autoplay muted loop playsinline></video>
                                                        <?php else: ?>
                                                            <img src="../assets/img/ruangan/<?= $f ?>" class="d-block w-100" style="height: 180px; object-fit: cover;" alt="Ruangan">
                                                        <?php endif; ?>
                                                    </div>
                                                <?php 
                                                    $first = false;
                                                endforeach; 
                                                ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <img src="https://source.unsplash.com/800x400/?classroom,university" class="d-block w-100" style="height: 180px; object-fit: cover;" alt="Ruangan default">
                                    <?php endif; ?>

                                    <?php if($is_online): ?>
                                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center" style="z-index: 2;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-body-custom">
                                    
                                    <div class="live-absen-box text-start mb-3 p-0" style="background: transparent; border: none;">
                                        <div class="fw-bolder text-dark mb-2 ms-1" style="font-size: 0.85rem;"><i class="fas fa-video text-danger me-2 blink"></i>Live Presensi:</div>
                                        <div id="list-live-absen-<?= $j['id'] ?>">
                                            <?= $html_absen_awal ?>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mb-3 pt-3 border-top border-secondary border-opacity-10">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-1 mb-2 fw-bold" style="font-size: 0.8rem;">
                                            Pertemuan Ke-<?= $j['pertemuan_ke'] ?>
                                        </span>
                                        <h3 class="mk-title m-0"><?= $j['mata_kuliah'] ?></h3>
                                        <div class="countdown-timer mt-2" data-start="<?= $start_time ?>" data-end="<?= $end_time ?>"></div>
                                    </div>
                                    
                                    <ul class="detail-list">
                                        <li><span class="detail-label">Kode MK</span><span class="detail-value">: <?= $j['kode_mk'] ?></span></li>
                                        <li><span class="detail-label">Bobot</span><span class="detail-value">: <?= $j['sks'] ?> SKS</span></li>
                                        <li><span class="detail-label">Semester</span><span class="detail-value">: <?= $j['semester'] ?></span></li>
                                        <li>
                                            <span class="detail-label">Ruang</span>
                                            <span class="detail-value">: 
                                                <?php if($is_online): ?>
                                                    <span class="text-primary">Online</span>
                                                    <?php if(!empty($j['link_online'])): ?>
                                                        <a href="<?= $j['link_online'] ?>" target="_blank" class="ms-2 badge bg-primary text-decoration-none">Buka Link</a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?= $j['ruangan'] ?>
                                                <?php endif; ?>
                                            </span>
                                        </li>
                                        <li><span class="detail-label">Pukul</span><span class="detail-value text-primary fw-bold">: <?= $jam_mulai ?> - <?= $jam_selesai ?> WIB</span></li>
                                    </ul>

                                <div class="dosen-box">
                                    <img src="<?= $foto_dosen ?>" class="dosen-pic">
                                    <div>
                                        <span class="d-block text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">Dosen Pengampu:</span>
                                        <span class="d-block fw-bold text-dark" style="font-size: 1.05rem; line-height: 1.2;"><?= $j['nama_dosen'] ?? 'Belum ditentukan' ?></span>
                                    </div>
                                </div>

                                <?php if(!empty($j['catatan'])): ?>
                                    <div class="alert alert-warning border-start border-4 border-warning bg-warning bg-opacity-10 text-dark mb-4 py-2">
                                        <div class="fw-bold mb-1 small">Catatan:</div>
                                        <div class="small fst-italic ms-3"><?= $j['catatan'] ?></div>
                                    </div>
                                <?php endif; ?>

                                <div class="attendance-box mt-3 pb-2 d-none" id="attendance-box-container-<?= $j['id'] ?>"></div>
                                
                                <div id="motivate-box-<?= $j['id'] ?>" class="<?= $sudah_absen ? '' : 'd-none' ?>">
                                    <div class="alert alert-success border-success mt-2 mb-2 py-2 small fw-bold animate-fade text-center">
                                        Kamu sudah mengisi presensi di jadwal ini
                                    </div>
                                    <div class="d-flex justify-content-center gap-3">
                                        <button class="btn btn-link text-decoration-none shadow-none p-0 text-danger small fw-bold" onclick="hapusAbsen(<?= $j['id'] ?>)"><i class="fas fa-trash"></i> Batal / Hapus Absen</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- ========================================== -->
        <!-- MODAL POPUP QR CODE SHARE JADWAL -->
        <!-- ========================================== -->
        <?php 
            // Bikin link otomatis ke jadwal spesifik ini
            $link_share = "https://" . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0] . "#card-jadwal-" . $j['id'];
            // Bikin gambar barcode super ngebut via API
            $qr_image = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($link_share) . "&color=0f172a";
        ?>
        <div class="modal fade" id="qrModal<?= $j['id'] ?>" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
                    
                    <div class="modal-header border-0 bg-light pb-0 position-relative">
                        <h5 class="modal-title fw-bolder text-dark w-100 text-center mt-2" style="font-size: 1.1rem;">
                            <i class="fas fa-share-alt me-2 text-primary"></i>Bagikan Jadwal
                        </h5>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body text-center pt-2 pb-4">
                        <p class="small text-muted mb-3 px-2">Arahkan kamera temanmu ke barcode ini untuk langsung membuka jadwal <b><?= $j['mata_kuliah'] ?></b>.</p>
                        
                        <div class="bg-white p-3 rounded-4 shadow-sm border border-secondary border-opacity-25 d-inline-block mb-3">
                            <img src="<?= $qr_image ?>" alt="QR Code <?= $j['mata_kuliah'] ?>" style="width: 180px; height: 180px; object-fit: contain;">
                        </div>
                        
                        <div>
                            <!-- Tombol Salin Link Kalo Dia Malas Scan -->
                            <button class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm" onclick="navigator.clipboard.writeText('<?= $link_share ?>'); alert('Link jadwal berhasil disalin bro!');">
                                <i class="fas fa-link me-2"></i>Salin Tautan
                            </button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        <!-- ========================================== -->
<?php } ?>