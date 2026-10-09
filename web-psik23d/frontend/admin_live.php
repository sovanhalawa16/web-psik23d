<?php
session_start();
include '../koneksi.php'; // Sesuaikan path ini dengan koneksi database lu

// Cek keamanan, pastikan ini admin (Silakan aktifkan/sesuaikan dengan session admin lu)
// if(!isset($_SESSION['nim_admin'])) {
//     die("<h3>Akses Ditolak! Hanya Admin yang boleh masuk ruang siaran.</h3>");
// }

// 1. JURUS AUTO-DATABASE (Bikin kolom setting otomatis kalau belum ada)
mysqli_query($koneksi, "INSERT IGNORE INTO pengaturan_jadwal (nama_setting, nilai) VALUES ('status_live', 'OFF')");
mysqli_query($koneksi, "INSERT IGNORE INTO pengaturan_jadwal (nama_setting, nilai) VALUES ('room_live', 'PSIK23D_LIVE_SECRET_ROOM_999')");

// 2. LOGIK TOMBOL ON / OFF LIVE
if(isset($_POST['toggle_live'])) {
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status_baru']);
    mysqli_query($koneksi, "UPDATE pengaturan_jadwal SET nilai = '$status_baru' WHERE nama_setting = 'status_live'");
    header("Location: admin_live.php"); // Refresh halaman biar efeknya jalan
    exit;
}

// 3. AMBIL STATUS TERKINI DARI DATABASE
$q_status = mysqli_query($koneksi, "SELECT nilai FROM pengaturan_jadwal WHERE nama_setting = 'status_live'");
$status_live = mysqli_fetch_assoc($q_status)['nilai'] ?? 'OFF';

$q_room = mysqli_query($koneksi, "SELECT nilai FROM pengaturan_jadwal WHERE nama_setting = 'room_live'");
$room_live = mysqli_fetch_assoc($q_room)['nilai'] ?? 'PSIK23D_LIVE_SECRET_ROOM_999';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Live - Admin PSIK 23 D</title>
    <!-- Bootstrap & FontAwesome biar UI/UX nya elegan -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Wajib panggil API Jitsi Meet -->
    <script src="https://meet.jit.si/external_api.js"></script>
    
    <style>
        body { background-color: #0f172a; color: #e2e8f0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .studio-card { background-color: #1e293b; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); margin-top: 30px; }
        .status-badge { font-size: 1.2rem; padding: 10px 20px; border-radius: 50px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
        .status-on { background-color: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; animation: pulse 2s infinite; }
        .status-off { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        #jitsi-container { width: 100%; height: 65vh; background: #000; border-radius: 12px; overflow: hidden; margin-top: 20px; border: 2px solid #334155; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="studio-card text-center">
                <h2 class="fw-bold mb-1"><i class="fas fa-video text-primary"></i> STUDIO LIVE STREAMING</h2>
                <p class="text-secondary mb-4">Control Panel Broadcasting PSIK 23 D</p>

                <!-- INDIKATOR STATUS -->
                <div class="mb-4">
                    <?php if($status_live == 'ON'): ?>
                        <span class="status-badge status-on"><i class="fas fa-satellite-dish me-2"></i> LIVE SEKARANG</span>
                    <?php else: ?>
                        <span class="status-badge status-off"><i class="fas fa-power-off me-2"></i> OFFLINE</span>
                    <?php endif; ?>
                </div>

                <!-- TOMBOL KONTROL -->
                <form method="POST" class="mb-4">
                    <?php if($status_live == 'OFF'): ?>
                        <input type="hidden" name="status_baru" value="ON">
                        <button type="submit" name="toggle_live" class="btn btn-success btn-lg px-5 fw-bold rounded-pill shadow">
                            <i class="fas fa-play me-2"></i> MULAI LIVE STREAMING
                        </button>
                        <p class="text-muted mt-3 small">Klik tombol ini untuk menyalakan kamera dan memberi tahu seluruh mahasiswa bahwa kelas sedang live.</p>
                    <?php else: ?>
                        <input type="hidden" name="status_baru" value="OFF">
                        <button type="submit" name="toggle_live" class="btn btn-danger btn-lg px-5 fw-bold rounded-pill shadow">
                            <i class="fas fa-stop me-2"></i> AKHIRI LIVE STREAMING
                        </button>
                        <p class="text-warning mt-3 small">Jangan lupa klik tombol ini setelah kelas selesai agar notifikasi live di web mahasiswa menghilang.</p>
                    <?php endif; ?>
                </form>

                <!-- WADAH PEMUTAR VIDEO JITSI -->
                <?php if($status_live == 'ON'): ?>
                    <div id="jitsi-container"></div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
    // Hanya render Jitsi kalau statusnya lagi ON
    <?php if($status_live == 'ON'): ?>
        document.addEventListener('DOMContentLoaded', () => {
            const domain = 'meet.jit.si';
            const options = {
                roomName: '<?= $room_live ?>',
                width: '100%',
                height: '100%',
                parentNode: document.getElementById('jitsi-container'),
                userInfo: {
                    displayName: 'Admin PSIK 23 D (Sutradara)' // Nama admin saat live
                },
                configOverwrite: {
                    prejoinPageEnabled: false, // Bypass halaman pre-join biar langsung masuk kamera
                    startWithAudioMuted: false, 
                    startWithVideoMuted: false,
                    disableDeepLinking: true // Mencegah Jitsi maksa buka aplikasinya di HP
                },
                interfaceConfigOverwrite: {
                    // Sembunyikan tombol-tombol yang gak perlu buat admin biar rapi
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'desktop', 'fullscreen',
                        'fodeviceselection', 'profile', 'chat', 'settings', 'videoquality', 'tileview'
                    ],
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                }
            };
            const api = new JitsiMeetExternalAPI(domain, options);
        });
    <?php endif; ?>
</script>

</body>
</html>