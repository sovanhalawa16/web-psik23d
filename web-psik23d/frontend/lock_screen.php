<style>
    /* --- IMPORT FONT MODERN & MONOSPACE VSCODE --- */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap');

    /* --- LIBRARY PRISM.JS BUAT WARNA KODE (MONOKAI THEME) --- */
    /* Warna Dasar */
    .token.comment, .token.prolog, .token.doctype, .token.cdata { color: #828bb8; font-style: italic; }
    .token.namespace { opacity: .7; }
    /* Variabel & Properti */
    .token.property, .token.tag, .token.constant, .token.symbol, .token.deleted { color: #f92672; } /* Pink */
    /* Boolean, Angka */
    .token.boolean, .token.number { color: #ae81ff; } /* Ungu */
    /* Fungsi & Selector */
    .token.selector, .token.attr-name, .token.string, .token.char, .token.builtin, .token.inserted { color: #a6e22e; } /* Hijau */
    /* Operator, Keyword */
    .token.operator, .token.entity, .token.url, .language-css .token.string, .style .token.string, .token.variable { color: #f8f8f2; }
    .token.atrule, .token.attr-value, .token.keyword { color: #66d9ef; } /* Biru */
    .token.function, .token.class-name { color: #e6db74; } /* Kuning */
    .token.regex, .token.important { color: #fd971f; } /* Orange */


    /* --- SCI-FI LOCK SCREEN - PREMIUM GLASSMORPHISM UI --- */
    #iosLockScreen {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: #010409; 
        z-index: 99999; display: flex; flex-direction: column; justify-content: space-between; align-items: center;
        color: white; text-align: center; padding: 8vh 20px 6vh;
        transition: transform 0.8s cubic-bezier(0.85, 0, 0.15, 1), opacity 0.6s ease;
        -webkit-user-select: none; user-select: none; -webkit-touch-callout: none;
        overflow: hidden;
    }
    
    #iosLockScreen.unlocked {
        transform: scale(1.2) translateY(-5%);
        opacity: 0;
        pointer-events: none;
        filter: blur(10px);
    }

    /* --- LIVE CODING BACKGROUND (VSCODE STYLE) --- */
    #codeBackground {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: -2;
        padding: 30px;
        box-sizing: border-box;
        font-family: 'Fira Code', 'Consolas', monospace;
        font-size: 14px;
        line-height: 1.6;
        text-align: left;
        color: #f8f8f2; /* Warna teks dasar */
        overflow: hidden;
        /* GUE TERANGIN BIAR NAMPAK JELAS */
        opacity: 0.6; 
        transition: opacity 0.3s;
    }

    /* WADAH KODE ASLI SAAT DIKETIK */
    #codeTyped {
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    /* Efek Kursor Terminal berkedip di ujung ketikan */
    #cursor {
        display: inline-block;
        width: 8px;
        height: 1.2em;
        background-color: #34d399; /* Emerald Green Tech */
        margin-left: 2px;
        animation: blink 0.8s step-end infinite;
        vertical-align: middle;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
    }
    
    /* Overlay radial biar background kodenya elegan */
    .video-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: radial-gradient(circle at center, rgba(13, 17, 23, 0.3) 0%, rgba(1, 4, 9, 0.9) 100%);
        backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);
        z-index: -1;
    }

    /* --- CLOCK AREA --- */
    .lock-time-container {
        display: flex; flex-direction: column; align-items: center; gap: 5px;
        text-shadow: 0 4px 25px rgba(0,0,0,0.8);
        z-index: 10;
        margin-top: 6vh;
        animation: fadeDown 1s ease-out;
    }
    
    .lock-icon { 
        font-size: 1.8rem; 
        margin-bottom: 8px; 
        color: rgba(255, 255, 255, 1); 
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
        text-shadow: 0 4px 15px rgba(56, 189, 248, 0.6);
        animation: pulseLock 2s infinite;
    }
    
    @keyframes pulseLock {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .lock-time { 
        font-size: 5.5rem; 
        font-weight: 300; 
        line-height: 1; 
        letter-spacing: -3px; 
        font-variant-numeric: tabular-nums; 
    }
    .lock-date { 
        font-size: 1.15rem; 
        font-weight: 500; 
        opacity: 0.9; 
        letter-spacing: 0.5px;
    }

    /* --- ACTION BUTTON AREA --- */
    .action-wrapper {
        display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
        flex: 1; margin-bottom: 5vh; z-index: 10; width: 100%;
        animation: fadeUp 1s ease-out;
    }

    /* --- PERHATIAN AUDIO PERMANEN (GLASSMORPHISM) --- */
    .audio-notice {
        display: flex; align-items: center; gap: 10px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 10px 22px; border-radius: 50px;
        margin-bottom: 25px;
        font-size: 0.9rem; font-weight: 500; color: #f8fafc;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);
        backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
    }
    .audio-notice i { font-size: 1.1rem; }
    .text-audio-on { color: #34d399; text-shadow: 0 0 12px rgba(52,211,153,0.7); }
    .text-audio-off { color: #fb7185; text-shadow: 0 0 12px rgba(251,113,133,0.7); }

    /* --- TOMBOL INTERAKTIF --- */
    .btn-buka-jadwal {
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: white; font-weight: 600; font-size: 1.15rem;
        padding: 18px 45px; border-radius: 50px;
        letter-spacing: 1px;
        cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        display: flex; align-items: center; gap: 12px;
    }
    
    .btn-buka-jadwal::before {
        content: '';
        position: absolute;
        top: 0; left: -100%; width: 50%; height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-25deg);
        animation: shine 4s infinite;
    }
    @keyframes shine {
        0% { left: -100%; }
        20% { left: 200%; }
        100% { left: 200%; }
    }
    
    .btn-buka-jadwal:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 15px 40px rgba(56, 189, 248, 0.4);
        border-color: rgba(56, 189, 248, 0.7);
        color: #e0f2fe;
    }
    
    .btn-buka-jadwal:active {
        transform: translateY(2px) scale(0.98);
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    }

    .auth-footer { 
        font-size: 0.7rem; 
        color: rgba(255,255,255,0.6); 
        letter-spacing: 3px; 
        text-transform: uppercase; 
        z-index: 10; 
        font-weight: 600;
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        animation: fadeUp 1.2s ease-out;
    }

    /* Animasi Masuk */
    @keyframes fadeDown {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
</style>

<div id="iosLockScreen">
    <pre id="codeBackground"><code id="codeTyped" class="language-javascript"></code><span id="cursor"></span></pre>
    <div class="video-overlay"></div>

    <div class="lock-time-container">
        <i class="fas fa-lock lock-icon" id="padlockIcon"></i>
        <div class="lock-time" id="lsTime">00:00</div>
        <div class="lock-date" id="lsDate">Senin, 1 Januari</div>
    </div>

    <div class="action-wrapper">
        <div class="audio-notice" id="audioNoticeBox">
            <i class="fas fa-music" id="audioNoticeIcon"></i>
            <span id="audioNoticeText">Memeriksa sistem...</span>
        </div>

        <button class="btn-buka-jadwal" id="btnBukaJadwal">
            <i class="fas fa-fingerprint"></i> Buka Jadwal
        </button>
    </div>

    <div class="auth-footer">
         PSIK 23 D Encrypted Access
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- SCRIPT BACKGROUND LIVE CODING BERWARNA-WARNI ---
        const codeElement = document.getElementById('codeTyped');
        const codeBackground = document.getElementById('codeBackground');

        // Script sintaks profesional (campuran JS/Node.js & Security protocols)
        const codeText = `/**
 * SYSTEM INITIALIZATION
 * Target: PSIK 23 D Encrypted Server
 * Protocol: WSS/TCP TLS v1.3 Standard
 */

// Importing Core Security Modules
const crypto = require('crypto');
const { System, UI_CORE } = require('./core-libs');

// Setting up Secure Boot Parameters
const BOOT_CONFIG = {
    integrityCheck: true,
    firewall: 'ACTIVE',
    secureTunnel: 'ESTABLISHED'
};

/**
 * Verifies secure connection with backend
 * @returns {Promise<boolean>}
 */
async function verifySecureConnection() {
    console.log("[SYSTEM] Initializing encrypted tunnel...");
    
    // Simulate complex network handshake
    const ws = new WebSocket('wss://secure.univ-system.net/api/v2/auth');
    
    return new Promise((resolve) => {
        ws.onopen = () => {
            console.log("[WSS] Connection established. Requesting token.");
            
            const payload = JSON.stringify({ 
                accessType: 'PSIK-CORE-23', 
                timestamp: Date.now() 
            });
            ws.send(payload);
        };
        
        ws.onmessage = (event) => {
            const response = JSON.parse(event.data);
            if(response.status === 'VERIFIED') {
                console.log("[AUTH] Access GRANTED. Decrypting stream...");
                resolve(true);
            }
        };
    });
}

// core implementation class
class SecurityLayer {
    constructor(publicKey) {
        this.key = publicKey;
        this.status = 'INITIALIZED';
    }

    decryptChunk(data, hash) {
        // Decryption logic goes here
        return 'DECRYPTED_STUB';
    }
}

// Execute core boot sequence
(async function boot() {
    try {
        console.log("-> Checking BOOT_CONFIG integrity...");
        if (!BOOT_CONFIG.integrityCheck) return lockdown();

        const success = await verifySecureConnection();
        
        if (success) {
            console.log("[SYSTEM] Decrypting main interface...");
            const SL = new SecurityLayer(process.env.CORE_PK);
            System.mount(UI_CORE);
            console.log("[STATUS] Ready.");
        }
    } catch (err) {
        console.error("[FATAL] Breach detected. System lockdown active.");
    }
})();
`;

        let i = 0;
        function typeCode() {
            if (i < codeText.length) {
                // Tambahkan karakter satu per satu
                codeElement.textContent += codeText.charAt(i);
                
                // --- KUNCI: RE-HIGHLIGHT SINTAKS ---
                Prism.highlightElement(codeElement);
                // ------------------------------------

                // Variasi kecepatan ngetik biar keliatan natural (kayak diketik manusia)
                let typeSpeed = Math.random() * 20 + 8; // Sedikit dicepetin
                // Kasih jeda lebih lama kalau ketemu enter/baris baru
                if (codeText.charAt(i) === '\n') {
                    typeSpeed = Math.random() * 300 + 100;
                }
                
                i++;
                setTimeout(typeCode, typeSpeed);
            } else {
                // Kalau udah selesai ngetik semua, jeda lalu hapus lalu ulang dari awal
                setTimeout(() => {
                    codeBackground.style.opacity = '0'; // Fade out
                    setTimeout(() => {
                        codeElement.textContent = '';
                        codeBackground.style.opacity = '0.6'; // Fade in
                        i = 0;
                        typeCode();
                    }, 500);
                }, 10000); // Jeda 10 detik sebelum ngulang ngetik
            }
        }
        
        // Mulai ngetik setelah delay dikit
        setTimeout(typeCode, 800);
        // -------------------------------------


        // --- LOGIC BAWAAN ASLI (TIDAK DIUBAH) ---
        const ls = document.getElementById('iosLockScreen');
        const padlock = document.getElementById('padlockIcon');
        const btnBuka = document.getElementById('btnBukaJadwal');
        
        const audioNoticeIcon = document.getElementById('audioNoticeIcon');
        const audioNoticeText = document.getElementById('audioNoticeText');
        
        let isUnlocked = false;

        <?php
            $q_auto_ls = mysqli_query($koneksi, "SELECT nilai FROM pengaturan_jadwal WHERE nama_setting='autoplay_musik'");
            $is_autoplay_ls = (mysqli_fetch_assoc($q_auto_ls)['nilai'] ?? '0') == '1' ? 'true' : 'false';
        ?>
        const isAudioAutoPlay = <?= $is_autoplay_ls ?>;

        if (isAudioAutoPlay) {
            audioNoticeIcon.className = 'fas fa-volume-up text-audio-on';
            audioNoticeText.innerHTML = 'Audio setelan admin: <b class="text-audio-on">ON</b>';
        } else {
            audioNoticeIcon.className = 'fas fa-volume-mute text-audio-off';
            audioNoticeText.innerHTML = 'Audio setelan admin: <b class="text-audio-off">OFF</b>';
        }

        function updateLSTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            document.getElementById('lsTime').innerText = `${hours}:${minutes}`;
            document.getElementById('lsDate').innerText = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`;
        }
        setInterval(updateLSTime, 1000);
        updateLSTime();

        function unlockScreen() {
            if(isUnlocked) return;
            isUnlocked = true;
            
            padlock.className = 'fas fa-unlock lock-icon';
            padlock.style.color = '#34d399';
            padlock.style.animation = 'none';
            padlock.style.transform = 'scale(1.2)';
            
            if (typeof playSong === 'function' && isAudioAutoPlay) {
                const audioEl = document.getElementById('bgAudio');
                if(audioEl && audioEl.paused) playSong(); 
            }
            
            if(navigator.vibrate) navigator.vibrate(50);
            
            btnBuka.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuka...';
            btnBuka.style.borderColor = '#34d399';
            btnBuka.style.color = '#34d399';
            btnBuka.style.background = 'rgba(52, 211, 153, 0.1)';

            setTimeout(() => {
                ls.classList.add('unlocked');
                triggerAppStart();
            }, 800); 
        }

        btnBuka.addEventListener('click', unlockScreen);

        function triggerAppStart() {
            const mainContent = document.getElementById('mainContent');
            const skeleton = document.getElementById('skeletonLoader');
            const real = document.getElementById('realContent');

            if(mainContent) mainContent.style.opacity = '1';

            if (typeof window.triggerPopupDanKucing === 'function') {
                setTimeout(() => { window.triggerPopupDanKucing(); }, 800);
            }

            setTimeout(() => {
                if(skeleton) skeleton.style.display = 'none';
                if(real) {
                    real.style.display = 'block';
                    setTimeout(() => {
                        real.style.opacity = '1';
                        const activeCard = document.querySelector('.date-card.active') || document.querySelector('.date-card');
                        if(activeCard) activeCard.click(); 
                    }, 50);
                }
            }, 800);
        }
    });
</script>