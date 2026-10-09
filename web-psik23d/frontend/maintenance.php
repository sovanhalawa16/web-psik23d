<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pembaruan Sistem - PSIK 23 D</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- iOS SYSTEM FONT & BACKGROUND --- */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #000000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden; /* Mencegah layar scroll/geser */
            color: #ffffff;
            position: relative; /* Fixed point untuk card */
        }

        /* --- SUBTLE BLURRED ORBS (APPLE STYLE) --- */
        .ambient-light {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 1;
            opacity: 0.6;
            animation: pulse 8s infinite alternate;
        }
        .light-blue { width: 300px; height: 300px; background: #0A84FF; top: -50px; left: -50px; }
        .light-purple { width: 400px; height: 400px; background: #5E5CE6; bottom: -100px; right: -100px; animation-delay: -4s; }

        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 0.4; }
            100% { transform: scale(1.2); opacity: 0.8; }
        }

        /* --- iOS STYLE GLASS CARD --- */
        .ios-card {
            position: absolute; /* Kunci posisinya di tengah absolut */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Menjaga tetap di center layar */
            z-index: 10;
            background: rgba(28, 28, 30, 0.65);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 40px 30px;
            text-align: center;
            max-width: 550px;
            width: 90%;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* --- APPLE UPDATE ICON --- */
        .update-icon {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #e5e5ea, #8e8e93);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
            animation: spinSlow 8s linear infinite;
        }
        @keyframes spinSlow { 100% { transform: rotate(360deg); } }

        /* --- TYPOGRAPHY --- */
        h1 { font-weight: 700; font-size: 1.8rem; margin-bottom: 10px; letter-spacing: -0.5px; }
        p { color: #ebebf5; font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px; opacity: 0.8; font-weight: 400; }

        /* --- IT TERMINAL / CONSOLE WIDGET (VS CODE THEME) --- */
        .console-widget {
            background: #1e1e1e; 
            border: 1px solid #333;
            border-radius: 12px;
            width: 100%;
            height: 180px; /* Tinggi absolut */
            min-height: 180px; /* Cegah penyusutan */
            max-height: 180px; /* Cegah memanjang */
            text-align: left;
            overflow: hidden; /* Sembunyikan elemen yang berlebih */
            position: relative;
            margin-bottom: 25px;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.5), 0 10px 20px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
        }
        /* Mac Window Header */
        .console-header {
            background: #2d2d2d;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            border-bottom: 1px solid #111;
            z-index: 2; /* Biar teks gak nabrak atas */
        }
        .mac-dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot-red { background: #ff5f56; }
        .dot-yel { background: #ffbd2e; }
        .dot-grn { background: #27c93f; }
        .console-title { color: #858585; font-size: 0.7rem; font-family: sans-serif; margin-left: auto; margin-right: auto; letter-spacing: 0.5px;}

        /* Code Area */
        .console-body {
            padding: 12px;
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end; 
        }
        .console-text {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 0.75rem;
            line-height: 1.5;
            display: flex;
            flex-direction: column;
            gap: 2px;
            width: 100%;
        }
        .console-line {
            opacity: 0;
            animation: typeLine 0.15s forwards;
            white-space: nowrap; /* Cegah word wrap yang ngerusak layout vertikal */
            overflow: hidden; /* Sembunyikan kalau teks kepanjangan */
            text-overflow: ellipsis; /* Tambah ... kalau kepanjangan */
            width: 100%;
        }
        @keyframes typeLine { to { opacity: 1; } }

        /* SYNTAX HIGHLIGHTING COLORS */
        .sy-kw { color: #c678dd; font-weight: bold; } /* Keyword (Purple) */
        .sy-fn { color: #61afef; } /* Function (Blue) */
        .sy-st { color: #98c379; } /* String (Green) */
        .sy-vr { color: #e06c75; } /* Variable (Red) */
        .sy-cm { color: #7f848e; font-style: italic; } /* Comment (Grey) */
        .sy-tg { color: #e06c75; } /* Tag (Red) */
        .sy-at { color: #d19a66; } /* Attribute (Orange) */
        .sy-op { color: #56b6c2; } /* Operator (Cyan) */
        .sy-nm { color: #d19a66; } /* Number (Orange) */
        .sy-sys{ color: #30D158; } /* System Log (iOS Green) */

        /* --- PROGRESS BAR --- */
        .progress-wrapper { width: 100%; margin-bottom: 25px; }
        .progress-bar-bg { background: rgba(255,255,255,0.15); border-radius: 10px; height: 6px; overflow: hidden; }
        .progress-fill { height: 100%; width: 0%; background: #0A84FF; border-radius: 10px; transition: width 0.5s ease-out; box-shadow: 0 0 10px rgba(10,132,255,0.5);}
        .progress-text { font-size: 0.75rem; color: #8e8e93; margin-top: 8px; font-weight: 500; letter-spacing: 0.5px; }

        /* --- iOS BUTTON --- */
        .ios-btn {
            background: #ffffff;
            color: #000000;
            border: none;
            padding: 14px 0;
            width: 100%;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .ios-btn:active { background: #e5e5ea; transform: scale(0.98); }

    </style>
</head>
<body>

    <div class="ambient-light light-blue"></div>
    <div class="ambient-light light-purple"></div>

    <div class="ios-card">
        <i class="fas fa-gear update-icon"></i>
        
        <h1>Pembaruan Sistem</h1>
        <p>Sistem <strong>PSIK 23 D</strong> sedang dalam tahap pemeliharaan. Fitur sedang dikompilasi dan disinkronisasi.</p>
        
        <div class="console-widget">
            <div class="console-header">
                <div class="mac-dot dot-red"></div>
                <div class="mac-dot dot-yel"></div>
                <div class="mac-dot dot-grn"></div>
                <div class="console-title">server_compiler.sh</div>
            </div>
            <div class="console-body">
                <div class="console-text" id="consoleOutput"></div>
            </div>
        </div>

        <div class="progress-wrapper">
            <div class="progress-bar-bg">
                <div class="progress-fill" id="progressBar"></div>
            </div>
            <div class="progress-text" id="progressText">Menyinkronkan data inti...</div>
        </div>

        <a href="https://wa.me/6281397079456" target="_blank" class="ios-btn" style="text-decoration: none;">
            <i class="fab fa-whatsapp" style="color: #25D366; font-size: 1.4rem; margin-right: 8px;"></i> 
            Klik untuk Info Lebih Lanjut
        </a>
    </div>

    <script>
        // 1. KUMPULAN KODE UNTUK ANIMASI (PHP, React, System Logs)
        const codeSnippets = [
            // SYSTEM LOGS
            `<span class="sy-sys">[SYS] Memulai inisialisasi server...</span>`,
            `<span class="sy-sys">[SYS] Ekstraksi modul utama: core_psik23.zip</span>`,
            
            // PHP LARAVEL STYLE
            `<span class="sy-op">&lt;?php</span>`,
            `<span class="sy-kw">namespace</span> App\\Controllers;`,
            `<span class="sy-kw">use</span> App\\Models\\Jadwal;`,
            `<span class="sy-kw">class</span> <span class="sy-fn">SystemUpdater</span> {`,
            `  <span class="sy-kw">public function</span> <span class="sy-fn">syncData</span>() {`,
            `    <span class="sy-cm">// Memperbarui tabel jadwal dan absensi</span>`,
            `    <span class="sy-vr">$jadwal</span> = Jadwal::<span class="sy-fn">all</span>();`,
            `    <span class="sy-kw">foreach</span>(<span class="sy-vr">$jadwal</span> <span class="sy-kw">as</span> <span class="sy-vr">$j</span>) {`,
            `       <span class="sy-vr">$j</span>-&gt;<span class="sy-fn">updateStatus</span>(<span class="sy-nm">true</span>);`,
            `    }`,
            `    <span class="sy-kw">return</span> <span class="sy-fn">response</span>()-&gt;<span class="sy-fn">json</span>([<span class="sy-st">'msg'</span> =&gt; <span class="sy-st">'OK'</span>]);`,
            `  }`,
            `}`,
            
            // SYSTEM LOGS
            `<span class="sy-sys">[SYS] Kompilasi PHP selesai. Memuat React DOM...</span>`,
            
            // REACT / JSX
            `<span class="sy-kw">import</span> React, { useState, useEffect } <span class="sy-kw">from</span> <span class="sy-st">'react'</span>;`,
            `<span class="sy-kw">export const</span> <span class="sy-fn">AppUI</span> = () =&gt; {`,
            `  <span class="sy-kw">const</span> [<span class="sy-vr">loading</span>, <span class="sy-fn">setLoading</span>] = <span class="sy-fn">useState</span>(<span class="sy-nm">true</span>);`,
            `  <span class="sy-fn">useEffect</span>(() =&gt; {`,
            `    <span class="sy-cm">/* Fetching UI configs */</span>`,
            `    <span class="sy-fn">fetchData</span>().<span class="sy-fn">then</span>(() =&gt; <span class="sy-fn">setLoading</span>(<span class="sy-nm">false</span>));`,
            `  }, []);`,
            `  <span class="sy-kw">return</span> (`,
            `    &lt;<span class="sy-tg">div</span> <span class="sy-at">className</span>=<span class="sy-st">"app-container"</span>&gt;`,
            `      {<span class="sy-vr">loading</span> ? &lt;<span class="sy-tg">Spinner</span> /&gt; : &lt;<span class="sy-tg">Dashboard</span> /&gt;}`,
            `    &lt;/<span class="sy-tg">div</span>&gt;`,
            `  );`,
            `};`,
            
            // SYSTEM LOGS
            `<span class="sy-sys">[SYS] Build sukses. Mengoptimasi cache...</span>`,
            `<span class="sy-sys">[SYS] Membersihkan RAM... 100%</span>`,
            `<span class="sy-sys">[SYS] Memulai ulang layanan HTTP...</span>`
        ];

        const consoleOutput = document.getElementById('consoleOutput');
        let codeIndex = 0;

        function renderCodeLine() {
            const line = document.createElement('div');
            line.className = 'console-line';
            line.innerHTML = codeSnippets[codeIndex];
            
            consoleOutput.appendChild(line);
            
            // Batasi jumlah baris secara ketat jadi 6 baris maksimal (jangan 7, biar gak luber)
            if (consoleOutput.children.length > 6) {
                consoleOutput.removeChild(consoleOutput.firstChild);
            }
            
            codeIndex++;
            
            if(codeIndex >= codeSnippets.length) {
                codeIndex = 0;
                setTimeout(renderCodeLine, 1000); 
            } else {
                const randomTime = Math.floor(Math.random() * 350) + 150;
                setTimeout(renderCodeLine, randomTime);
            }
        }
        
        setTimeout(renderCodeLine, 1000);

        // 2. PROGRESS BAR LOGIC (Stuck at 99%)
        setTimeout(() => {
            const bar = document.getElementById('progressBar');
            const text = document.getElementById('progressText');
            
            let targetProgress = 99; 
            bar.style.width = targetProgress + '%';
            
            let current = 0;
            let interval = setInterval(() => {
                if(current >= targetProgress) {
                    clearInterval(interval);
                    text.innerText = "Menyelesaikan tahap akhir... (99%)";
                } else {
                    current++;
                    text.innerText = `Memperbarui sistem... ${current}%`;
                }
            }, 30); 
        }, 500);
    </script>
</body>
</html>