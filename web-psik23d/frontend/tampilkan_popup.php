<?php
// 1. Koneksi ke database (sesuaikan pathnya jika berbeda)
include '../koneksi.php';

// SET ZONA WAKTU JAKARTA
date_default_timezone_set('Asia/Jakarta');

// 2. Ambil data pengumuman yang statusnya AKTIF
$query_popup = mysqli_query($koneksi, "SELECT * FROM popup_jadwal WHERE status = 'aktif' ORDER BY id DESC LIMIT 1");
$data_popup = mysqli_fetch_assoc($query_popup);

// Jika tidak ada pengumuman aktif, jangan tampilkan apa-apa
if (!$data_popup) {
    return; 
}

$subject   = $data_popup['subject'];
$deskripsi = $data_popup['deskripsi']; // Ini HTML dari Quill Editor
$penulis   = $data_popup['penulis'];

// TANGKAP STATUS VIDEO & GAMBAR
$status_video = isset($data_popup['status_video']) ? $data_popup['status_video'] : 'nonaktif';
$status_gambar = isset($data_popup['status_gambar']) ? $data_popup['status_gambar'] : 'nonaktif';
$file_gambar = isset($data_popup['file_gambar']) ? $data_popup['file_gambar'] : '';

// JURUS ANTI-DOUBLE: Kalau admin milih Mode Gambar, bunuh file teks ini sekarang juga!
if ($status_gambar === 'aktif') {
    return; // File ini berhenti kerja, biar popup_gambar.php aja yang maju.
}

// Format Waktu Dibuat (Mengambil dari database 'created_at')
$hari_array = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
$bulan_array = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

$waktu_db = strtotime($data_popup['created_at']); // Ambil waktu dari DB
$hari_ini = $hari_array[date('w', $waktu_db)];
$tanggal_ini = date('d', $waktu_db) . ' ' . $bulan_array[(int)date('m', $waktu_db)] . ' ' . date('Y', $waktu_db);
$jam_detik = date('H:i:s', $waktu_db);

$waktu_dibuat = $hari_ini . ', ' . $tanggal_ini . ' ' . $jam_detik . ' WIB';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
    /* Paksa hapus spasi/enter (br) kosong raksasa yang bikin jarak bolong */
    #typing-content p:empty, 
    #typing-content p br:only-child {
        display: none !important;
    }
    .premium-backdrop {
        background-color: rgba(15, 23, 42, 0.45) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }
    .premium-modal-content {
        border-radius: 28px !important;
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
        font-family: 'Inter', -apple-system, sans-serif;
    }
    .premium-header {
        padding: 30px 30px 15px 30px !important;
        text-align: center;
    }
    .premium-badge {
        background: linear-gradient(135deg, #fef08a 0%, #fde047 100%);
        color: #854d0e !important;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 1.2px;
        padding: 8px 16px;
    }
    .premium-title {
        font-weight: 800;
        color: #0f172a;
        font-size: 1.3rem;
        margin-top: 15px;
        line-height: 1.4;
    }
    .premium-body {
        padding: 5px 30px 35px 30px !important;
        color: #334155;
    }
    
    /* Box Induk untuk Efek Sirkuit Menyala */
    .animated-circuit-box {
        position: relative;
        border-radius: 18px;
        background: #cbd5e1; /* Warna dasar garis */
        padding: 3px; /* Ketebalan garis sirkuit */
        margin-bottom: 20px;
        overflow: hidden;
        z-index: 1;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    /* Animasi "Laser Sirkuit" Berputar */
    .animated-circuit-box::before {
        content: "";
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(
            transparent 0%,
            transparent 60%,
            #00e1ff 75%, /* Biru Cyan */
            #ff007a 85%, /* Pink Neon */
            #10b981 100%  /* Hijau Emerald */
        );
        animation: rotate-circuit 2.5s linear infinite;
        z-index: -1;
    }

    @keyframes rotate-circuit {
        100% {
            transform: rotate(360deg);
        }
    }

    /* KONTEN DI DALAM KOTAK */
    .announcement-box-inner {
        background-color: #ffffff; 
        border-radius: 15px; 
        padding: 20px 24px;
        position: relative;
        min-height: 60px;
        max-height: 300px; 
        overflow-y: auto;
        z-index: 2;
        width: 100%;
        height: 100%;
    }

    /* === PERBAIKAN TEKS RATA FULL & ANTI BOLONG === */
    #typing-content {
        font-family: 'Inter', sans-serif !important;
        font-size: 0.95rem !important;
        line-height: 1.7 !important;
        color: #1e293b !important;
        text-align: justify !important;
        text-justify: inter-word !important;
        hyphens: auto !important;
        -webkit-hyphens: auto !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }

    /* Paksa semua paragraf dari Quill editor buat ikut rata full */
    #typing-content p, #typing-content div {
        text-align: justify !important;
        margin-bottom: 12px;
    }

    /* Kustomisasi Scrollbar di dalam Box agar cantik */
    .announcement-box-inner::-webkit-scrollbar {
        width: 6px;
    }
    .announcement-box-inner::-webkit-scrollbar-track {
        background: transparent;
    }
    .announcement-box-inner::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.5);
        border-radius: 10px;
    }
    .announcement-box-inner::-webkit-scrollbar-thumb:hover {
        background-color: rgba(148, 163, 184, 0.8);
    }

    .watermark-box {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px dashed rgba(148, 163, 184, 0.4);
        text-align: center;
    }
    .watermark-text {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0;
        line-height: 1.6;
    }
    .watermark-text strong {
        color: #0f172a;
        font-weight: 700;
    }
    .watermark-text em {
        font-style: italic;
        color: #3b82f6;
        font-weight: 600;
    }

    .modal.fade .modal-dialog {
        transform: scale(0.9) translateY(20px);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal.show .modal-dialog {
        transform: scale(1) translateY(0);
    }

    /* Hilangkan outline dari canvas kicau mania */
    #kicauCanvas {
        outline: none;
        border: none;
        box-shadow: none;
        background: transparent;
    }

    /* === STYLING KHUSUS UNTUK POPUP MODE GAMBAR === */
    .modal-content-image {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .popup-image-container {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    }
    .popup-image-container img {
        width: 100%;
        height: auto;
        display: block;
    }
</style>

<div class="modal fade" id="autoPopupJadwal" tabindex="-1" aria-hidden="true" lang="id">
    <div class="modal-dialog modal-dialog-centered">
        
        <?php if($status_gambar == 'aktif' && !empty($file_gambar)): ?>
            <div class="modal-content modal-content-image">
                <div class="popup-image-container">
                    <img src="../assets/img/popup/<?= $file_gambar ?>" alt="Pengumuman Kelas">
                </div>
            </div>
            
        <?php else: ?>
            <div class="modal-content premium-modal-content">
                <div class="premium-header">
                    <div>
                        <span class="badge premium-badge rounded-pill">
                            <i class="fas fa-bell me-1"></i> PENGUMUMAN BARU
                        </span>
                    </div>
                    <h5 class="premium-title"><?= htmlspecialchars($subject) ?></h5>
                </div>
                
                <div class="premium-body">
                    <div class="animated-circuit-box">
                        <div class="announcement-box-inner">
                            <div class="ql-snow">
                                <div id="typing-content" class="ql-editor">
                                    <div id="source-html" style="display:none;">
                                        <?= $deskripsi ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="watermark-box">
                        <p class="watermark-text">Disiarkan oleh: <strong><?= htmlspecialchars($penulis) ?></strong></p>
                        <p class="watermark-text">Pada: <em><?= $waktu_dibuat ?></em></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<div id="kicauManiaWrapper" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 99999; pointer-events: none; transition: opacity 0.1s;">
    <video id="kicauVideo" src="../assets/img/kicaumania1.mp4" loop muted playsinline crossorigin="anonymous" style="display: none;"></video>
    <canvas id="kicauCanvas" style="width: 250px;"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('autoPopupJadwal');
    
    // Konfigurasi Modal Bootstrap (Klik di luar gambar buat nutup modal)
    const myModal = new bootstrap.Modal(modalEl, {
        backdrop: true, 
        keyboard: true  
    });
    // --- JURUS AUTO-TRIGGER: PAKSA MUNCUL PAS WEB DIBUKA ---
    setTimeout(() => {
        if (typeof window.triggerPopupDanKucing === 'function') {
            window.triggerPopupDanKucing();
        }
    }, 1000); // Delay 1 detik biar tampilannya nunggu webnya rapi dulu
    // -------------------------------------------------------
    
    // ==========================================
    // 1. FUNGSI MENGETIK OTOMATIS (KHUSUS MODE TEKS)
    // ==========================================
    function startTyping() {
        const source = document.getElementById('source-html');
        if(!source) return; // Kalau mode gambar, ini ga akan jalan
        
        const target = document.getElementById('typing-content');
        const box = document.querySelector('.announcement-box-inner');
        const fullContent = source.innerHTML.trim();
        
        target.innerHTML = ''; 
        let i = 0;
        
        const timer = setInterval(() => {
            if (fullContent[i] === '<') {
                let tagEnd = fullContent.indexOf('>', i);
                i = tagEnd + 1;
            } else {
                i++;
            }
            
            target.innerHTML = fullContent.substring(0, i);
            box.scrollTop = box.scrollHeight;
            
            if (i >= fullContent.length) {
                clearInterval(timer);
            }
        }, 30); 
    }

    // ==========================================
    // 2. FUNGSI CHROMA KEY (HAPUS GREEN SCREEN + CROP VIDEO)
    // ==========================================
    const kicauWrapper = document.getElementById('kicauManiaWrapper');
    const kicauVideo = document.getElementById('kicauVideo');
    const kicauCanvas = document.getElementById('kicauCanvas');
    const ctx = kicauCanvas.getContext('2d', { willReadFrequently: true });
    
    function processGreenScreen() {
        if (kicauVideo.paused || kicauVideo.ended) return;
        
        // --- JURUS CROP OTOMATIS (Biar flek hitam atas bawah musnah!) ---
        const cropSize = Math.min(kicauVideo.videoWidth, kicauVideo.videoHeight);
        
        if(cropSize > 0 && kicauCanvas.width !== cropSize) {
            kicauCanvas.width = cropSize;
            kicauCanvas.height = cropSize;
        }

        const startX = (kicauVideo.videoWidth - cropSize) / 2;
        const startY = (kicauVideo.videoHeight - cropSize) / 2;

        ctx.drawImage(kicauVideo, startX, startY, cropSize, cropSize, 0, 0, cropSize, cropSize);
        
        let frame = ctx.getImageData(0, 0, kicauCanvas.width, kicauCanvas.height);
        let l = frame.data.length / 4;

        for (let i = 0; i < l; i++) {
            let r = frame.data[i * 4 + 0];
            let g = frame.data[i * 4 + 1];
            let b = frame.data[i * 4 + 2];
            
            if (g > 90 && g > r * 1.3 && g > b * 1.3) {
                frame.data[i * 4 + 3] = 0; 
            }
            else if (r < 15 && g < 15 && b < 15) {
                frame.data[i * 4 + 3] = 0;
            }
        }
        
        ctx.putImageData(frame, 0, 0);
        requestAnimationFrame(processGreenScreen);
    }

    kicauVideo.addEventListener('play', function() {
        processGreenScreen();
    });

    // KETIKA MODAL TERBUKA
    modalEl.addEventListener('shown.bs.modal', function () {
        // Cek kalau ini mode teks, jalankan typing animation
        if(document.getElementById('source-html')) {
            startTyping();
        }
    });

    // ==========================================
    // JURUS BARU: CEK STATUS VIDEO DARI PHP
    // ==========================================
    window.triggerPopupDanKucing = function() {
        // 1. Munculin Modal
        myModal.show();
        
        // 2. Pasang backdrop cakepnya
        setTimeout(() => {
            const backdrop = document.querySelector('.modal-backdrop');
            if(backdrop) backdrop.classList.add('premium-backdrop');
        }, 100); 

        // AMBIL STATUS VIDEO DARI DATABASE (Dilempar lewat PHP)
        const statusVideoAktif = '<?= $status_video ?>';

        // JIKA ADMIN MILIH 'AKTIF', BARU KUCINGNYA NONGOL!
        if(statusVideoAktif === 'aktif') {
            
            // 3. Bangunin Kucingnya!
            kicauWrapper.style.display = 'block';
            kicauWrapper.style.opacity = '1';
            kicauVideo.play().catch(e => console.log("Auto-play diblokir browser: " + e));
            
            let isKucingRunning = true; // Saklar buat matiin loop nanti

            // Jurus Looping Asimetris (Nyala 5s, Mati 1.5s)
            function kucingJedagJedug() {
                if (!isKucingRunning) return; 

                // 1. Kucing Muncul
                kicauWrapper.style.opacity = '1';

                // 2. Tunggu 5 detik (5000ms), lalu sembunyikan
                setTimeout(() => {
                    if (!isKucingRunning) return;
                    kicauWrapper.style.opacity = '0';

                    // 3. Tunggu 1,5 detik (1500ms) saat sembunyi, lalu ulangi muncul lagi
                    setTimeout(() => {
                        kucingJedagJedug();
                    }, 1500);

                }, 5000);
            }

            // Jalankan animasi jedag-jedugnya
            kucingJedagJedug();
            
            // Timer Pembunuh: Hapus Kucingnya Total Setelah 30 Detik
            setTimeout(() => {
                isKucingRunning = false; // Matikan sistem jedag-jedug
                kicauWrapper.style.display = 'none';
                kicauVideo.pause();
            }, 30000); 
            
        } else {
            // JIKA NONAKTIF, SEMBUNYIKAN SECARA PAKSA BIAR AMAN!
            kicauWrapper.style.display = 'none';
            kicauVideo.pause();
        }
    };

});
</script>