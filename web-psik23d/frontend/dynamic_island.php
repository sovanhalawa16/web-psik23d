<?php
// --- 1. TUGAS TERDEKAT (Dari tugas_perkuliahan) ---
$tugas_teks = "Bebas Tugas! 🎉";
$tugas_waktu = "Aman bro, ga ada deadline";
$tugas_bg = "rgba(48, 209, 88, 0.9)"; // Hijau iOS Aman
$tugas_icon = "fa-check-circle text-white";
$tugas_anim = "";

try {
    // JURUS FIX: Kunci ke Waktu Indonesia, tembak langsung pakai string PHP!
    date_default_timezone_set('Asia/Jakarta');
    $waktu_sekarang_wib = date('Y-m-d H:i:s');
    
    // Tarik tugas yang benar-benar BELUM LEWAT jam sekarang di Indonesia
    $q_tugas = mysqli_query($koneksi, "SELECT tipe_tugas, mata_kuliah, waktu_selesai FROM tugas_perkuliahan WHERE waktu_selesai > '$waktu_sekarang_wib' ORDER BY waktu_selesai ASC LIMIT 1");
    
    if($q_tugas && mysqli_num_rows($q_tugas) > 0) {
        $tg = mysqli_fetch_assoc($q_tugas);
        
        // JURUS POTONG TEKS BIAR GAK MELAR
        $full_teks = htmlspecialchars($tg['tipe_tugas']) . " - " . htmlspecialchars($tg['mata_kuliah']);
        if(strlen($full_teks) > 28) {
            $tugas_teks = substr($full_teks, 0, 28) . '...';
        } else {
            $tugas_teks = $full_teks;
        }

        $tugas_waktu = "Kumpul: " . date('d M, H:i', strtotime($tg['waktu_selesai']));
        $tugas_bg = "rgba(255, 59, 48, 0.9)"; // Merah iOS Bahaya
        $tugas_icon = "fa-exclamation-triangle text-white";
        $tugas_anim = "animation: pulseWhite 1.5s infinite;";
    }
} catch (Exception $e) { }

// --- 2. JADWAL TERDEKAT / LIVE ---
$jadwal_teks = "Waktunya Healing ☕";
$jadwal_waktu = "Tidak ada kelas lagi";
$jadwal_bg = "rgba(10, 132, 255, 0.9)"; // Biru iOS Premium
$jadwal_icon = "fa-calendar-check text-white";

$hari_ini_en = date('l');
$mapping_en_id = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
$hari_ini_id = $mapping_en_id[$hari_ini_en];

$q_jdwl_now = mysqli_query($koneksi, "SELECT mata_kuliah, jam_mulai, jam_selesai FROM jadwal WHERE hari='$hari_ini_id' ORDER BY jam_mulai ASC");
if($q_jdwl_now && mysqli_num_rows($q_jdwl_now) > 0) {
    $now_time = time();
    while($jw = mysqli_fetch_assoc($q_jdwl_now)) {
        $mulai = strtotime(date('Y-m-d') . ' ' . $jw['jam_mulai']);
        $selesai = strtotime(date('Y-m-d') . ' ' . $jw['jam_selesai']);
        if($now_time >= $mulai && $now_time <= $selesai) {
            $jadwal_teks = (strlen($jw['mata_kuliah']) > 28) ? substr(htmlspecialchars($jw['mata_kuliah']), 0, 28) . '...' : htmlspecialchars($jw['mata_kuliah']);
            $jadwal_waktu = "🔴 SEDANG BERLANGSUNG";
            $jadwal_icon = "fa-bolt text-warning";
            break;
        } elseif ($mulai > $now_time) {
            $jadwal_teks = (strlen($jw['mata_kuliah']) > 28) ? substr(htmlspecialchars($jw['mata_kuliah']), 0, 28) . '...' : htmlspecialchars($jw['mata_kuliah']);
            $jadwal_waktu = "Mulai pukul " . date('H:i', $mulai) . " WIB";
            $jadwal_icon = "fa-clock text-white";
            break;
        }
    }
}

// --- 3. DAILY PROGRESS (Indikator Selesai Kuliah) ---
$q_jdwl_today = mysqli_query($koneksi, "SELECT jam_selesai FROM jadwal WHERE hari='$hari_ini_id'");
$total_kelas_hari_ini = $q_jdwl_today ? mysqli_num_rows($q_jdwl_today) : 0;
$kelas_selesai = 0;

if($total_kelas_hari_ini > 0) {
    $now_time = time();
    while($j_today = mysqli_fetch_assoc($q_jdwl_today)) {
        $selesai = strtotime(date('Y-m-d') . ' ' . $j_today['jam_selesai']);
        if($now_time > $selesai) {
            $kelas_selesai++;
        }
    }
    $progress_persen = round(($kelas_selesai / $total_kelas_hari_ini) * 100);
    $progress_teks = "$kelas_selesai dari $total_kelas_hari_ini Kelas Selesai";
    $progress_title = "Progres Hari Ini";
    if($kelas_selesai == $total_kelas_hari_ini) {
        $progress_teks = "Semua kelas selesai! 🎉";
    }
} else {
    $progress_persen = 100;
    $progress_teks = "Hari ini libur bro! 🌴";
    $progress_title = "Progres Hari Ini";
}
?>

<style>
    /* BUNGKUS DYNAMIC ISLAND (VERTIKAL EFEK) */
    .pill-carousel-wrapper {
        width: 100%; 
        height: 75px; /* Tinggi statis buat nge-hold pil */
        margin: 0 auto 25px auto; 
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* DESAIN PILL INDIVIDUAL - CLEAN UI */
    .ios-pill {
        position: absolute;
        width: 90%; /* Responsif di layar HP */
        max-width: 320px;
        height: 65px; 
        border-radius: 35px;
        display: flex; align-items: center; padding: 10px 15px; gap: 15px;
        color: white;
        backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255,255,255,0.4); cursor: pointer;
        -webkit-tap-highlight-color: transparent; 
        
        /* POSISI AWAL: Sembunyi di atas & mengecil */
        transform: translateY(-80px) scale(0.85);
        opacity: 0;
        pointer-events: none;
        z-index: 1;
        
        /* TRANSISI KELUAR: Bergerak naik dan hilang halus */
        transition: transform 0.5s cubic-bezier(0.55, 0.085, 0.68, 0.53), opacity 0.4s ease-out;
    }
    
    /* KETIKA PILL MUNCUL */
    .ios-pill.active {
        transform: translateY(0) scale(1);
        opacity: 1;
        pointer-events: auto;
        z-index: 5;
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        
        /* JURUS SAKTI: Transisi masuk dengan efek BOUNCE (mantul naik turun) */
        transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.6s ease-in;
    }

    .pill-icon {
        width: 42px; height: 42px; border-radius: 50%; background: rgba(255, 255, 255, 0.25);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0; box-shadow: inset 0 0 10px rgba(255,255,255,0.1);
        position: relative;
    }
    .pill-content { display: flex; flex-direction: column; overflow: hidden; white-space: nowrap; text-align: left; flex: 1; }
    .pill-title { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; margin-bottom: 2px;}
    .pill-desc { font-size: 0.95rem; font-weight: 800; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }

    @keyframes pulseWhite { 0% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); } 70% { box-shadow: 0 0 0 10px rgba(255,255,255,0); } 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); } }
    
    /* Animasi Music */
    .vis-container { display: flex; gap: 3px; align-items: flex-end; height: 16px; margin-top: 4px; }
    .vis-bar { width: 3px; background: #ffffff; border-radius: 3px; animation: bounceBar 0.5s infinite alternate; }
    .vis-bar:nth-child(2) { animation-delay: 0.2s; }
    .vis-bar:nth-child(3) { animation-delay: 0.4s; }
    @keyframes bounceBar { 0% { height: 4px; } 100% { height: 16px; } }

    /* Lingkaran Progress Bar */
    .progress-circle {
        width: 100%; height: 100%; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: conic-gradient(#ffffff <?= $progress_persen ?>%, rgba(255,255,255,0.2) 0);
    }
    .progress-inner {
        width: 75%; height: 75%; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.65rem; font-weight: 800; color: white;
    }
</style>

<div class="pill-carousel-wrapper">
    <div class="ios-pill pill-item active" style="background: <?= $jadwal_bg ?>;" onclick="window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'})">
        <div class="pill-icon"><i class="fas <?= $jadwal_icon ?>"></i></div>
        <div class="pill-content">
            <span class="pill-title"><?= $jadwal_waktu ?></span>
            <span class="pill-desc"><?= $jadwal_teks ?></span>
        </div>
    </div>

    <div class="ios-pill pill-item" style="background: <?= $tugas_bg ?>; <?= $tugas_anim ?>" onclick="window.location.href='https://psik23d.kesug.com/daftar_tugas'">
        <div class="pill-icon"><i class="fas <?= $tugas_icon ?>"></i></div>
        <div class="pill-content">
            <span class="pill-title"><?= $tugas_waktu ?></span>
            <span class="pill-desc"><?= $tugas_teks ?></span>
        </div>
    </div>

    <div class="ios-pill pill-item" style="background: rgba(225, 29, 72, 0.9);">
        <div class="pill-icon" style="background: transparent; box-shadow: none;">
            <div class="progress-circle">
                <div class="progress-inner" style="background: rgba(225, 29, 72, 1);"><?= $progress_persen ?>%</div>
            </div>
        </div>
        <div class="pill-content">
            <span class="pill-title"><?= $progress_title ?></span>
            <span class="pill-desc"><?= $progress_teks ?></span>
        </div>
    </div>

    <div class="ios-pill pill-item" style="background: rgba(88, 86, 214, 0.9);">
        <div class="pill-icon"><i class="fas fa-cloud-sun text-white" id="weatherIcon"></i></div>
        <div class="pill-content">
            <span class="pill-title">Info Cuaca Kampus</span>
            <span class="pill-desc" id="weatherTemp">Mengecek satelit...</span>
        </div>
    </div>

    <div class="ios-pill pill-item" style="background: rgba(255, 149, 0, 0.9);" onclick="window.location.href='rekap_absen.php'">
        <div class="pill-icon"><i class="fas fa-fire text-white"></i></div>
        <div class="pill-content">
            <span class="pill-title">Semangat Kelas</span>
            <span class="pill-desc">Tap lihat rekap absen!</span>
        </div>
    </div>

    <div class="ios-pill pill-item" style="background: rgba(28, 28, 30, 0.85);" onclick="document.getElementById('vinylBtn').click()">
        <div class="pill-icon">
            <i class="fas fa-music text-white" id="musicIcon"></i>
            <div class="vis-container" id="musicVis" style="display: none;">
                <div class="vis-bar"></div><div class="vis-bar"></div><div class="vis-bar"></div>
            </div>
        </div>
        <div class="pill-content">
            <span class="pill-title">Sedang Diputar</span>
            <span class="pill-desc" id="musicText" style="color: #ffffff;">Musik dijeda</span>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pills = document.querySelectorAll('.pill-item');
    const totalPills = pills.length;
    let currentIndex = 0;
    let isHovered = false;

    // --- LOGIKA DYNAMIC ISLAND (VERTIKAL SWITCH) ---
    function animatePills() {
        if(isHovered) return; // Kalo lagi ditahan jari, jangan ganti dulu (UX aman)

        // 1. Suruh pill saat ini naik ke atas (hilang)
        pills[currentIndex].classList.remove('active');

        // 2. Siapin index buat pill berikutnya
        currentIndex = (currentIndex + 1) % totalPills;

        // 3. Kasih delay dikit biar pill lama beneran naik dulu, baru pill baru terjun 
        setTimeout(() => {
            pills[currentIndex].classList.add('active');
        }, 300); // 300ms jeda yang pas
    }

    // Jalankan ganti pil tiap 3.5 detik
    let autoSlideInterval = setInterval(animatePills, 3500);

    // --- PAUSE KETIKA DISENTUH USER ---
    const wrapper = document.querySelector('.pill-carousel-wrapper');
    wrapper.addEventListener('mouseenter', () => isHovered = true);
    wrapper.addEventListener('mouseleave', () => isHovered = false);
    wrapper.addEventListener('touchstart', () => isHovered = true, {passive: true});
    wrapper.addEventListener('touchend', () => {
        setTimeout(() => { isHovered = false; }, 1500); // Lanjut animasi 1.5 detik setelah dilepas
    }, {passive: true});

    // --- API CUACA MEDAN ---
    fetch('https://api.open-meteo.com/v1/forecast?latitude=3.5952&longitude=98.6722&current_weather=true')
    .then(res => res.json()).then(data => {
        const weather = data.current_weather;
        const code = weather.weathercode;
        let wText = "Cerah Berawan"; let wIcon = "fa-cloud-sun";
        
        if(code === 0) { wText = "Cerah Terik ☀️"; wIcon = "fa-sun"; }
        else if(code >= 1 && code <= 3) { wText = "Berawan Aman 🌤️"; wIcon = "fa-cloud-sun"; }
        else if(code >= 51 && code <= 67) { wText = "Gerimis Bro 🌧️"; wIcon = "fa-cloud-rain"; }
        else if(code >= 80 && code <= 99) { wText = "Hujan Deras ⛈️"; wIcon = "fa-poo-storm"; }
        
        document.getElementById('weatherTemp').innerText = `${weather.temperature}°C - ${wText}`;
        document.getElementById('weatherIcon').className = `fas ${wIcon} text-white`;
    }).catch(() => { document.getElementById('weatherTemp').innerText = "Cerah 🌤️"; });

    // --- SINKRONISASI MUSIC PLAYER ---
    const audio = document.getElementById('bgAudio');
    const musicVis = document.getElementById('musicVis');
    const musicIcon = document.getElementById('musicIcon');
    const musicText = document.getElementById('musicText');
    const mainMusicTitle = document.getElementById('musicTitle');

    function syncMusic() {
        if(!audio.paused) {
            musicVis.style.display = 'flex';
            musicIcon.style.display = 'none';
            musicText.innerText = mainMusicTitle.innerText;
        } else {
            musicVis.style.display = 'none';
            musicIcon.style.display = 'block';
            musicText.innerText = "Musik dijeda";
        }
    }
    audio.addEventListener('play', syncMusic);
    audio.addEventListener('pause', syncMusic);
    setTimeout(syncMusic, 1000);
});
</script>