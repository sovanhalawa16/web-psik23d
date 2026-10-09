<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-KTM Generator | PSIK 23 D</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Courier+Prime:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        :root {
            --primary-color: #22c55e; 
            --primary-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            --secondary-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-sub: #64748b;
        }

        body { 
            background-color: #f1f5f9;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
            color: var(--text-main); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
            overflow-x: hidden;
        }

        /* Container Style */
        .card-generator { 
            background: rgba(255, 255, 255, 0.95); 
            border-radius: 24px; 
            padding: 40px; 
            box-shadow: 0 20px 60px -10px rgba(0,0,0,0.08), 0 10px 20px -5px rgba(0,0,0,0.04); 
            border: 1px solid #e2e8f0; 
            max-width: 1100px; 
            width: 100%; 
            position: relative;
            z-index: 10;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Typography */
        h2 { font-family: 'Rajdhani', sans-serif; letter-spacing: -0.5px; }
        .text-gradient {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Input Styling */
        .input-group {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: 0.3s;
        }
        .input-group:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }
        .input-group-text { 
            background: #f0fdf4; 
            color: var(--primary-color); 
            border: none; 
            padding: 16px 20px;
        }
        .form-control { 
            background: transparent; 
            border: none; 
            color: var(--text-main); 
            padding: 16px; 
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }
        .form-control:focus { background: transparent; box-shadow: none; }
        .form-control::placeholder { color: #94a3b8; font-weight: 400; letter-spacing: 0; }

        /* Buttons */
        .btn-generate { 
            background: var(--primary-gradient); 
            border: none; 
            color: white; 
            font-weight: 700; 
            width: 100%; 
            padding: 16px; 
            border-radius: 12px; 
            transition: all 0.3s ease; 
            margin-top: 10px;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
            position: relative;
            overflow: hidden;
        }
        .btn-generate:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(34, 197, 94, 0.3); }
        .btn-generate:active { transform: translateY(1px); }
        .btn-generate:disabled { opacity: 0.7; cursor: not-allowed; transform: none; box-shadow: none; filter: grayscale(100%); }

        .btn-download { 
            background: #0ea5e9; 
            color: white; 
            font-weight: bold; 
            width: 100%; 
            padding: 14px; 
            border-radius: 12px; 
            border: none; 
            display: none; 
            margin-top: 15px; 
            transition: 0.3s; 
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
        }
        .btn-download:hover { background: #0284c7; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3); }
        
        /* Canvas Area */
        #canvas-wrapper { 
            background: #f1f5f9; 
            border-radius: 20px; 
            padding: 20px; 
            border: 2px dashed #cbd5e1;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            position: relative;
            transition: 0.3s;
        }
        #canvas-wrapper.has-result {
            background: #e2e8f0;
            border-style: solid;
            border-color: #cbd5e1;
        }
        canvas { max-width: 100%; height: auto; border-radius: 15px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        
        .loading { display: none; text-align: center; margin-top: 20px; color: var(--primary-color); font-weight: 600; }
        
        .preview-placeholder { 
            text-align: center; 
            color: #94a3b8; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            animation: fadeIn 1s ease;
        }
        .preview-icon-bg {
            background: #e2e8f0;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: #64748b;
        }

        /* Hidden div untuk generate QR */
        #qrcode-hidden { 
            position: fixed;
            top: -1000px;
            left: -1000px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
        
        /* Back Link */
        .back-link { color: #64748b; transition: 0.3s; display: inline-flex; align-items: center; }
        .back-link:hover { color: var(--primary-color); transform: translateX(-5px); }

        /* Animations */
        @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* Mobile Optimizations */
        @media (max-width: 992px) {
            .card-generator { padding: 30px 20px; }
            #canvas-wrapper { margin-top: 30px; min-height: 300px; }
        }
        @media (max-width: 768px) {
            body { padding: 10px; align-items: flex-start; }
            .card-generator { margin-top: 10px; border-radius: 16px; }
            .btn-generate, .btn-download { font-size: 14px; padding: 14px; }
            canvas { max-width: 100%; }
            
            /* Optimasi input untuk mobile */
            input[type="tel"] { font-size: 16px !important; height: 44px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card-generator">
        <div class="row g-5 align-items-center">
            <div class="col-lg-4">
                <div class="mb-4">
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-3 fw-bold"><i class="fas fa-university me-1"></i> PSIK 23 D</span>
                    <h2 class="fw-bold text-uppercase display-6 mb-1">Smart <span class="text-gradient">ID Card</span></h2>
                    <p class="text-sub">Demi menjaga privasi dan keamanan data, silakan login menggunakan NIM dan Password yang terdaftar.</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-uppercase fw-bold small text-sub ls-1 mb-2">Nomor Induk Mahasiswa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-badge fa-lg"></i></span>
                        <input type="tel" id="inputNIM" class="form-control">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-uppercase fw-bold small text-sub ls-1 mb-2">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock fa-lg"></i></span>
                        <input type="password" id="inputPass" class="form-control">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div class="form-text small text-muted fst-italic mt-1">
                        *Verifikasi keamanan diperlukan.
                    </div>
                </div>
                
                <button onclick="generateKTM()" class="btn-generate" id="generateButton">
                    <i class="fas fa-bolt me-2"></i> GENERATE SEKARANG
                </button>
                
                <div class="loading" id="loading">
                    <div class="spinner-border text-success spinner-border-sm" role="status"></div>
                    <span class="ms-2 small">Sedang memproses data...</span>
                </div>

                <a id="downloadLink" class="btn-download text-center text-decoration-none">
                    <i class="fas fa-file-download me-2"></i> SIMPAN
                </a>
                
                <div class="mt-5 pt-3 border-top">
                    <a href="https://psik23d.kesug.com/frontend/features.php" class="back-link small text-decoration-none fw-semibold">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div id="canvas-wrapper">
                    <canvas id="ktmCanvas" width="1000" height="630" style="display:none;"></canvas>
                    
                    <div class="preview-placeholder" id="placeholder-img">
                        <div class="preview-icon-bg">
                            <i class="fas fa-id-card fa-3x"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Preview Kartu</h5>
                        <p class="small mb-0">Masukkan NIM kamu untuk<br>melihat hasil kartu digital Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="qrcode-hidden"></div>
</div>

<script>
// Toggle Password Visibility
function togglePass() {
    const passField = document.getElementById("inputPass");
    const eyeIcon = document.getElementById("eyeIcon");
    if (passField.type === "password") {
        passField.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    } else {
        passField.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const inputNIM = document.getElementById('inputNIM');
    const inputPass = document.getElementById('inputPass');
    const generateButton = document.getElementById('generateButton');

    // Trigger Enter di kedua field
    [inputNIM, inputPass].forEach(field => {
        field.addEventListener('keypress', function(e) { 
            if (e.key === 'Enter') { e.preventDefault(); generateKTM(); } 
        });
    });

    // Mobile Optimization
    if ('ontouchstart' in window) {
        generateButton.addEventListener('touchstart', function() { this.classList.add('active'); }, { passive: true });
        generateButton.addEventListener('touchend', function() { this.classList.remove('active'); }, { passive: true });
    }
});

// Helper Functions
function getJakartaTime() { 
    const now = new Date(); 
    const options = { 
        timeZone: 'Asia/Jakarta', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: false 
    }; 
    return new Intl.DateTimeFormat('id-ID', options).format(now) + ' WIB'; 
}

function fitTextOnCanvas(ctx, text, fontFace, initialSize, maxWidth) { 
    let size = initialSize; 
    ctx.font = `bold ${size}px ${fontFace}`; 
    while (ctx.measureText(text).width > maxWidth && size > 20) { 
        size -= 2; 
        ctx.font = `bold ${size}px ${fontFace}`; 
    } 
    return size; 
}

// Fungsi untuk generate QR Code dengan Promise
function generateQRCodeAsync(text, size = 100) {
    return new Promise((resolve) => {
        try {
            // Buat hidden div untuk QR
            const qrHiddenDiv = document.getElementById('qrcode-hidden');
            
            // Clear previous QR code
            qrHiddenDiv.innerHTML = '';
            
            // Create a container div for the QR code
            const qrContainer = document.createElement('div');
            qrContainer.id = 'temp-qr-' + Date.now();
            qrHiddenDiv.appendChild(qrContainer);
            
            // Generate QR Code
            const qr = new QRCode(qrContainer, {
                text: text,
                width: size,
                height: size,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            
            // Cek apakah QR sudah dibuat
            function checkQRGenerated() {
                const qrImg = qrContainer.querySelector('img');
                const qrCanvas = qrContainer.querySelector('canvas');
                
                // Di mobile, library QRCode bisa menghasilkan canvas atau img
                if (qrImg && qrImg.complete && qrImg.naturalWidth > 0) {
                    resolve({
                        type: 'img',
                        element: qrImg,
                        src: qrImg.src
                    });
                } else if (qrCanvas) {
                    // Jika hasilnya canvas, konversi ke data URL
                    resolve({
                        type: 'canvas',
                        element: qrCanvas,
                        src: qrCanvas.toDataURL('image/png')
                    });
                } else {
                    // Coba lagi setelah delay
                    setTimeout(checkQRGenerated, 100);
                }
            }
            
            // Mulai pengecekan
            setTimeout(checkQRGenerated, 300);
            
            // Timeout sebagai fallback
            setTimeout(() => {
                const qrImg = qrContainer.querySelector('img');
                const qrCanvas = qrContainer.querySelector('canvas');
                
                if (qrImg || qrCanvas) {
                    if (qrImg && qrImg.src) {
                        resolve({
                            type: 'img',
                            element: qrImg,
                            src: qrImg.src
                        });
                    } else if (qrCanvas) {
                        resolve({
                            type: 'canvas',
                            element: qrCanvas,
                            src: qrCanvas.toDataURL('image/png')
                        });
                    }
                } else {
                    // Fallback: buat QR sederhana
                    resolve({
                        type: 'fallback',
                        element: null,
                        src: null
                    });
                }
            }, 1500);
            
        } catch (error) {
            console.error("QR Generation error:", error);
            resolve({
                type: 'error',
                element: null,
                src: null
            });
        }
    });
}

// --- FUNGSI GENERATE UTAMA DENGAN FIX MOBILE ---
async function generateKTM() {
    const nim = document.getElementById('inputNIM').value.trim();
    const password = document.getElementById('inputPass').value.trim();
    
    const loading = document.getElementById('loading');
    const canvas = document.getElementById('ktmCanvas');
    const ctx = canvas.getContext('2d');
    const btnDownload = document.getElementById('downloadLink');
    const placeholder = document.getElementById('placeholder-img');
    const canvasWrapper = document.getElementById('canvas-wrapper');
    const generateButton = document.getElementById('generateButton');

    // Validasi Input
    if(!nim) { alert("NIM wajib diisi!"); return; }
    if(!password) { alert("Password wajib diisi untuk verifikasi!"); return; }

    generateButton.disabled = true;
    generateButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> VERIFIKASI...';
    loading.style.display = 'block';
    btnDownload.style.display = 'none';
    
    // Clear canvas sebelum generate baru
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    canvas.style.display = 'none';
    placeholder.style.display = 'flex';
    canvasWrapper.classList.remove('has-result');
    
    // Scroll ke hasil untuk mobile
    if (window.innerWidth <= 768) {
        setTimeout(() => {
            document.getElementById('canvas-wrapper').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 300);
    }
    
    try {
        const formData = new FormData();
        formData.append('nim', nim);
        formData.append('password', password);

        // Tambah timeout untuk mobile
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);
        
        const response = await fetch('../backend/api_get_mhs.php', { 
            method: 'POST', 
            body: formData,
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        const result = await response.json();

        if(result.status !== 'success') { 
            alert(result.message);
            resetUI();
            return; 
        }

        // --- AUTH SUKSES, LANJUT GAMBAR ---
        generateButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> RENDERING...';
        const mhs = result.data;
        
        // Cek jika ada foto_url
        if (!mhs.foto_url) {
            mhs.foto_url = '../assets/img/mahasiswa/default.png';
        }
        
        const fontPrimary = "'Roboto Condensed', Arial, sans-serif";

        // Load logo
        const imgLogo = new Image();
        imgLogo.crossOrigin = "anonymous";
        imgLogo.src = '../assets/img/FMIPA.png';
        
        imgLogo.onerror = () => {
            loadProfileImage(null);
        };
        
        imgLogo.onload = () => {
            loadProfileImage(imgLogo);
        };

        function loadProfileImage(logoObject) {
            const imgProfil = new Image();
            imgProfil.crossOrigin = "anonymous";
            
            let profileUrl = mhs.foto_url;
            
            // Pastikan path benar
            if (!profileUrl.startsWith('http') && !profileUrl.startsWith('data:')) {
                if (!profileUrl.startsWith('../')) {
                    profileUrl = '../assets/img/mahasiswa/' + profileUrl;
                }
            }
            
            // Tambah timestamp untuk cache busting
            profileUrl += (profileUrl.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
            imgProfil.src = profileUrl;

            const imageTimeout = setTimeout(() => {
                imgProfil.onerror = null;
                imgProfil.onload = null;
                drawKTM(logoObject, null);
            }, 8000);

            imgProfil.onload = () => {
                clearTimeout(imageTimeout);
                drawKTM(logoObject, imgProfil);
            };
            
            imgProfil.onerror = () => {
                clearTimeout(imageTimeout);
                // Gunakan fallback avatar
                const fallbackImg = new Image();
                fallbackImg.onload = () => {
                    drawKTM(logoObject, fallbackImg);
                };
                fallbackImg.onerror = () => {
                    drawKTM(logoObject, null);
                };
                fallbackImg.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(mhs.nama) + '&size=280&background=0d6efd&color=fff&bold=true';
            };
        }

        async function drawKTM(logoObject, profileImage) {
            try {
                // 1. Background putih
                ctx.fillStyle = "#ffffff";
                ctx.fillRect(0, 0, 1000, 630);
                
                // 2. Header hijau
                const headerGreen = "#22c55e";
                ctx.fillStyle = headerGreen;
                ctx.fillRect(0, 0, 1000, 130);
                
                // 3. Sudut kuning
                ctx.beginPath();
                ctx.moveTo(600, 130);
                ctx.lineTo(1000, 130);
                ctx.lineTo(1000, 160);
                ctx.lineTo(550, 130);
                ctx.fillStyle = "#facc15";
                ctx.fill();
                
                // 4. Logo (jika ada)
                if (logoObject) {
                    ctx.drawImage(logoObject, 30, 20, 90, 90);
                }
                
                // 5. Text header
                ctx.textAlign = "right";
                ctx.fillStyle = "#ffffff";
                ctx.font = `bold 32px ${fontPrimary}`;
                ctx.fillText("UNIVERSITAS NEGERI MEDAN", 950, 60);
                
                ctx.font = `22px ${fontPrimary}`;
                ctx.fillStyle = "#dcfce7";
                ctx.fillText("KARTU TANDA MAHASISWA AKTIF PSIK 23 D", 950, 95);
                
                // 6. Foto profil (jika ada)
                if (profileImage) {
                    const photoX = 60;
                    const photoY = 180;
                    const photoW = 280;
                    const photoH = 350;
                    
                    ctx.save();
                    ctx.beginPath();
                    ctx.roundRect(photoX, photoY, photoW, photoH, 25);
                    ctx.closePath();
                    ctx.clip();
                    ctx.drawImage(profileImage, photoX, photoY, photoW, photoH);
                    ctx.restore();
                    
                    // Border foto
                    ctx.strokeStyle = "#f1f5f9";
                    ctx.lineWidth = 10;
                    ctx.beginPath();
                    ctx.roundRect(photoX, photoY, photoW, photoH, 25);
                    ctx.stroke();
                    
                    ctx.strokeStyle = headerGreen;
                    ctx.lineWidth = 3;
                    ctx.beginPath();
                    ctx.roundRect(photoX + 8, photoY + 8, photoW - 16, photoH - 16, 20);
                    ctx.stroke();
                } else {
                    // Jika tidak ada foto, buat placeholder
                    const photoX = 60;
                    const photoY = 180;
                    const photoW = 280;
                    const photoH = 350;
                    
                    ctx.fillStyle = "#f1f5f9";
                    ctx.beginPath();
                    ctx.roundRect(photoX, photoY, photoW, photoH, 25);
                    ctx.fill();
                    
                    ctx.fillStyle = "#94a3b8";
                    ctx.font = "bold 24px Arial";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    ctx.fillText("NO PHOTO", photoX + photoW/2, photoY + photoH/2);
                }
                
                // 7. Data mahasiswa
                const dataBX = 390;
                ctx.textAlign = "left";
                ctx.fillStyle = "#1e293b";
                
                // Nama
                const namaMhs = mhs.nama.toUpperCase();
                const fontSizeNama = fitTextOnCanvas(ctx, namaMhs, fontPrimary, 50, 550);
                ctx.font = `bold ${fontSizeNama}px ${fontPrimary}`;
                ctx.fillText(namaMhs, dataBX, 215);
                
                // Garis bawah nama
                ctx.fillStyle = headerGreen;
                ctx.fillRect(dataBX, 230, 550, 4);
                
                // Data lainnya
                let currentY = 290;
                const labelColor = "#64748b";
                const valueColor = "#1e293b";
                
                // NIM
                ctx.fillStyle = labelColor;
                ctx.font = `bold 16px ${fontPrimary}`;
                ctx.fillText("NOMOR INDUK MAHASISWA", dataBX, currentY);
                ctx.fillStyle = headerGreen;
                ctx.font = `bold 30px 'Courier New', monospace`;
                ctx.fillText(mhs.nim, dataBX, currentY + 32);
                currentY += 85;
                
                // TTL
                ctx.fillStyle = labelColor;
                ctx.font = `bold 16px ${fontPrimary}`;
                ctx.fillText("TEMPAT, TANGGAL LAHIR", dataBX, currentY);
                ctx.fillStyle = valueColor;
                ctx.font = `24px ${fontPrimary}`;
                ctx.fillText(mhs.ttl_format || "Tidak tersedia", dataBX, currentY + 32);
                currentY += 85;
                
                // Status
                ctx.fillStyle = labelColor;
                ctx.font = `bold 16px ${fontPrimary}`;
                ctx.fillText("STATUS MAHASISWA", dataBX, currentY);
                const statusText = mhs.status_mhs?.toUpperCase() || "MAHASISWA";
                ctx.font = `bold 18px ${fontPrimary}`;
                const badgeW = ctx.measureText(statusText).width + 30;
                ctx.fillStyle = "#10b981";
                ctx.beginPath();
                ctx.roundRect(dataBX, currentY + 12, badgeW, 36, 8);
                ctx.fill();
                ctx.fillStyle = "#ffffff";
                ctx.fillText(statusText, dataBX + 15, currentY + 36);
                
                // 8. Stempel PSIK 23 D
                ctx.save();
                ctx.translate(820, 390);
                ctx.rotate(-15 * Math.PI / 180);
                ctx.strokeStyle = "rgba(220, 38, 38, 0.7)";
                ctx.fillStyle = "rgba(220, 38, 38, 0.7)";
                ctx.lineWidth = 4;
                ctx.strokeRect(-90, -35, 180, 70);
                ctx.lineWidth = 2;
                ctx.strokeRect(-82, -27, 164, 54);
                ctx.font = "bold 32px 'Courier Prime', 'Courier New', monospace";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                ctx.fillText("PSIK 23 D", 0, 2);
                ctx.restore();
                
                // 9. Footer
                ctx.fillStyle = "#f8fafc";
                ctx.fillRect(0, 540, 1000, 90);
                ctx.fillStyle = "#e2e8f0";
                ctx.fillRect(0, 540, 1000, 2);

                // 10. QR Code - FIX UNTUK MOBILE
                const qrX = 870;
                const qrY = 480;
                
                try {
                    // Generate QR Code dengan async function
                    const qrResult = await generateQRCodeAsync(`https://psik23d.kesug.com/frontend/user_profile.php?id=${mhs.id}`, 100);
                    
                    if (qrResult.type !== 'error' && qrResult.src) {
                        const qrImage = new Image();
                        qrImage.crossOrigin = "anonymous";
                        qrImage.src = qrResult.src;
                        
                        await new Promise((resolve) => {
                            const timeout = setTimeout(() => {
                                drawQRFallback();
                                resolve();
                            }, 2000);
                            
                            qrImage.onload = function() {
                                clearTimeout(timeout);
                                ctx.drawImage(qrImage, qrX, qrY, 100, 100);
                                addQRText();
                                resolve();
                            };
                            
                            qrImage.onerror = function() {
                                clearTimeout(timeout);
                                drawQRFallback();
                                resolve();
                            };
                        });
                    } else {
                        drawQRFallback();
                    }
                } catch (qrError) {
                    console.error("QR Error:", qrError);
                    drawQRFallback();
                }
                
                function drawQRFallback() {
                    // QR sederhana sebagai fallback
                    ctx.fillStyle = "#ffffff";
                    ctx.fillRect(qrX, qrY, 100, 100);
                    ctx.fillStyle = "#000000";
                    ctx.fillRect(qrX + 10, qrY + 10, 80, 80);
                    ctx.fillStyle = "#ffffff";
                    ctx.font = "bold 24px Arial";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    ctx.fillText("QR", qrX + 50, qrY + 50);
                    addQRText();
                }
                
                function addQRText() {
                    // Text QR Code
                    ctx.textAlign = "center";
                    ctx.fillStyle = "#1e293b";
                    ctx.font = `bold 12px ${fontPrimary}`;
                    ctx.fillText("SCAN ME", qrX + 50, qrY + 100 + 15);
                    
                    // 11. Waktu cetak
                    ctx.textAlign = "left";
                    ctx.fillStyle = "#64748b";
                    ctx.font = `italic 14px ${fontPrimary}`;
                    ctx.fillText("Dicetak pada: " + getJakartaTime(), 30, 585);
                }
                
                // Selesai menggambar
                finishGeneration();
                
            } catch (error) {
                console.error("Error drawing KTM:", error);
                alert("Terjadi kesalahan saat menggambar kartu.");
                resetUI();
            }
        }

    } catch (error) {
        console.error("Fetch error:", error);
        
        if (error.name === 'AbortError') {
            alert("Permintaan timeout. Pastikan koneksi internet stabil.");
        } else {
            alert("Terjadi kesalahan sistem: " + error.message);
        }
        
        resetUI();
    }

    function finishGeneration() {
        placeholder.style.display = 'none';
        canvas.style.display = 'block';
        canvasWrapper.classList.add('has-result'); 
        loading.style.display = 'none';
        
        try {
            // Tunggu sedikit untuk memastikan canvas selesai dirender
            setTimeout(() => {
                const imageURL = canvas.toDataURL("image/png");
                btnDownload.href = imageURL;
                btnDownload.download = `KTM_${nim}.png`;
                btnDownload.style.display = 'block';
            }, 500);
        } catch (e) {
            console.error("Error generating download link:", e);
        }
        
        resetGenerateButton();
    }
}

function resetUI() { 
    const btn = document.getElementById('generateButton'); 
    btn.disabled = false; 
    btn.innerHTML = '<i class="fas fa-bolt me-2"></i> GENERATE SEKARANG'; 
    document.getElementById('loading').style.display = 'none'; 
}

function resetGenerateButton() { 
    const btn = document.getElementById('generateButton'); 
    btn.disabled = false; 
    btn.innerHTML = '<i class="fas fa-bolt me-2"></i> GENERATE SEKARANG'; 
}

// Polyfill roundRect
if (!CanvasRenderingContext2D.prototype.roundRect) { 
    CanvasRenderingContext2D.prototype.roundRect = function (x, y, w, h, r) { 
        if (w < 2 * r) r = w / 2; 
        if (h < 2 * r) r = h / 2; 
        this.beginPath(); 
        this.moveTo(x+r, y); 
        this.arcTo(x+w, y, x+w, y+h, r); 
        this.arcTo(x+w, y+h, x, y+h, r); 
        this.arcTo(x, y+h, x, y, r); 
        this.arcTo(x, y, x+w, y, r); 
        this.closePath(); 
        return this; 
    }; 
}
</script>

</body>
</html>