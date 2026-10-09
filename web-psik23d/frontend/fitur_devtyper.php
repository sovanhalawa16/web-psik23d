<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevTyper | Speed Coding Test</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #1e1e1e;
            --bg-darker: #121212;
            --accent: #007acc; /* VS Code Blue */
            --text-gray: #858585;
            --text-white: #d4d4d4;
            --success: #4ec9b0;
            --error: #f44747;
        }

        body {
            background-color: var(--bg-darker);
            color: var(--text-white);
            font-family: 'Fira Code', monospace;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* IDE Container */
        .ide-window {
            width: 90%;
            max-width: 900px;
            background-color: var(--bg-dark);
            border-radius: 10px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            border: 1px solid #333;
            overflow: hidden;
            position: relative;
        }

        /* Header Window */
        .ide-header {
            background-color: #252526;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #333;
        }
        .window-dots { display: flex; gap: 8px; }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background: #ff5f56; }
        .dot-yellow { background: #ffbd2e; }
        .dot-green { background: #27c93f; }
        .filename { font-size: 0.9rem; color: var(--text-gray); }

        /* Game Area */
        .game-area {
            padding: 30px;
            position: relative;
        }

        /* Stats Bar */
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.2rem;
            color: var(--accent);
        }
        .stat-item span { color: var(--text-white); font-weight: bold; font-size: 1.5rem; }

        /* Code Display */
        .code-display {
            font-size: 1.5rem;
            line-height: 1.6;
            user-select: none;
            white-space: pre-wrap;
            min-height: 150px;
            color: #6a6a6a; /* Warna teks belum diketik */
            position: relative;
        }
        
        .char-correct { color: var(--success); text-shadow: 0 0 5px rgba(78, 201, 176, 0.5); }
        .char-wrong { color: var(--error); text-decoration: underline; background: rgba(244, 71, 71, 0.2); }
        .cursor { border-right: 2px solid var(--accent); animation: blink 1s infinite; }

        @keyframes blink { 50% { border-color: transparent; } }

        /* Input Tersembunyi (Agar keyboard HP muncul) */
        #hiddenInput {
            position: absolute;
            opacity: 0;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            cursor: default;
        }

        /* Overlay Result */
        .result-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(18, 18, 18, 0.95);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            backdrop-filter: blur(5px);
        }
        .result-score { font-size: 4rem; font-weight: bold; color: var(--accent); font-family: 'Rajdhani', sans-serif; }
        .result-label { font-size: 1rem; color: var(--text-gray); margin-bottom: 20px; }
        
        .btn-restart {
            background: var(--accent);
            color: white;
            border: none;
            padding: 10px 30px;
            font-family: 'Rajdhani', sans-serif;
            font-weight: bold;
            font-size: 1.2rem;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn-restart:hover { background: #0098ff; transform: scale(1.05); }

        .back-link {
            position: absolute;
            top: 20px; left: 20px;
            color: var(--text-gray);
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-link:hover { color: var(--accent); }
        
        /* Blur Start Overlay */
        .start-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(30, 30, 30, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            backdrop-filter: blur(2px);
        }
        .click-to-start {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.5rem;
            color: var(--text-white);
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse { 50% { opacity: 0.5; } }

    </style>
</head>
<body>

    <a href="features.php" class="back-link"><i class="fas fa-arrow-left"></i> KEMBALI</a>

    <div class="text-center mb-4">
        <h1 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; letter-spacing: 2px;">
            <i class="fas fa-bolt text-warning"></i> DEV<span style="color: var(--accent);">TYPER</span>
        </h1>
        <p style="color: var(--text-gray); font-size: 0.9rem;">Uji kecepatan jari codingmu!</p>
    </div>

    <div class="ide-window" onclick="focusInput()">
        <div class="ide-header">
            <div class="window-dots">
                <div class="dot dot-red"></div>
                <div class="dot dot-yellow"></div>
                <div class="dot dot-green"></div>
            </div>
            <div class="filename" id="langLabel">script.js</div>
            <div style="width: 40px;"></div>
        </div>

        <div class="game-area">
            <div class="stats-container">
                <div class="stat-item">Waktu: <span id="timer">30</span>s</div>
                <div class="stat-item">WPM: <span id="wpm">0</span></div>
                <div class="stat-item">Akurasi: <span id="accuracy">100</span>%</div>
            </div>

            <div class="code-display" id="codeDisplay">
                </div>
            
            <input type="text" id="hiddenInput" autocomplete="off" autocapitalize="off" spellcheck="false">
            
            <div class="start-overlay" id="startOverlay">
                <div class="click-to-start">KLIK UNTUK MULAI</div>
            </div>
        </div>

        <div class="result-overlay" id="resultScreen">
            <div class="result-score" id="finalWPM">0</div>
            <div class="result-label">Words Per Minute (WPM)</div>
            
            <div class="d-flex gap-4 mb-4 text-white">
                <div class="text-center">
                    <div class="fs-4 fw-bold text-success" id="finalAcc">0%</div>
                    <small class="text-secondary">Akurasi</small>
                </div>
                <div class="text-center">
                    <div class="fs-4 fw-bold text-warning" id="finalChars">0</div>
                    <small class="text-secondary">Karakter</small>
                </div>
            </div>

            <button class="btn-restart" onclick="resetGame()">
                <i class="fas fa-redo-alt me-2"></i> COBA LAGI
            </button>
        </div>
    </div>

    <script>
        // Database Snippet Coding
        const snippets = [
            { lang: "php", code: '<?php echo "Hello World"; ?>' },
            { lang: "javascript", code: 'function sum(a, b) { return a + b; }' },
            { lang: "css", code: 'body { background-color: #000; color: #fff; }' },
            { lang: "cpp", code: '#include <iostream>\nusing namespace std;' },
            { lang: "python", code: 'def greet(name):\n    print(f"Hello, {name}")' },
            { lang: "sql", code: 'SELECT * FROM users WHERE active = 1;' },
            { lang: "html", code: '<div class="container">\n  <h1>Title</h1>\n</div>' },
            { lang: "javascript", code: 'const arr = [1, 2, 3];\narr.map(x => x * 2);' },
            { lang: "php", code: '$conn = mysqli_connect($host, $user, $pass);' }
        ];

        let currentCode = "";
        let currentIndex = 0;
        let timeLeft = 30;
        let timerInterval = null;
        let isPlaying = false;
        let mistakes = 0;
        let charactersTyped = 0;

        const codeDisplay = document.getElementById('codeDisplay');
        const hiddenInput = document.getElementById('hiddenInput');
        const timerEl = document.getElementById('timer');
        const wpmEl = document.getElementById('wpm');
        const accEl = document.getElementById('accuracy');
        const startOverlay = document.getElementById('startOverlay');
        const resultScreen = document.getElementById('resultScreen');
        const langLabel = document.getElementById('langLabel');

        function initGame() {
            // Pilih snippet acak
            const random = snippets[Math.floor(Math.random() * snippets.length)];
            currentCode = random.code;
            langLabel.textContent = "main." + (random.lang === 'cpp' ? 'cpp' : (random.lang === 'python' ? 'py' : random.lang));
            
            // Render Code Spans
            codeDisplay.innerHTML = '';
            currentCode.split('').forEach(char => {
                const span = document.createElement('span');
                span.innerText = char;
                codeDisplay.appendChild(span);
            });
            
            // Reset variables
            currentIndex = 0;
            timeLeft = 30;
            mistakes = 0;
            charactersTyped = 0;
            timerEl.innerText = timeLeft;
            wpmEl.innerText = 0;
            accEl.innerText = 100;
            hiddenInput.value = '';
            
            // Tambahkan kursor di awal
            if(codeDisplay.firstChild) codeDisplay.firstChild.classList.add('cursor');
        }

        function focusInput() {
            if (!isPlaying && resultScreen.style.display !== 'flex') {
                startGame();
            }
            hiddenInput.focus();
        }

        function startGame() {
            isPlaying = true;
            startOverlay.style.display = 'none';
            hiddenInput.focus();
            
            timerInterval = setInterval(() => {
                if (timeLeft > 0) {
                    timeLeft--;
                    timerEl.innerText = timeLeft;
                    
                    // Hitung WPM Realtime
                    // WPM = (Characters / 5) / TimeInMinutes
                    const timeElapsed = 30 - timeLeft;
                    const wpm = Math.round(((currentIndex / 5) / (timeElapsed / 60)) || 0);
                    if(wpm < 200) wpmEl.innerText = wpm; // Cegah lonjakan gila di awal
                } else {
                    endGame();
                }
            }, 1000);
        }

        hiddenInput.addEventListener('input', (e) => {
            if (!isPlaying) return;

            const inputChar = e.data; // Karakter yang diketik user
            const spans = codeDisplay.querySelectorAll('span');
            const currentChar = currentCode[currentIndex];

            // Handle Backspace (Sedikit tricky di mobile, kita reset input value)
            // Di sini kita asumsikan user ngetik maju terus (Simple Version)
            // Kalau mau handle backspace kompleks, butuh logic array history.
            // Biar gampang: Salah ya salah, lanjut terus.

            if (inputChar === null) { // Indikasi backspace di beberapa browser
                return; 
            }

            // Validasi Input
            // Jika enter, kita anggap user ngetik '\n'
            // Di textarea input type text gak bisa enter, jadi snippet kita hindari enter kompleks
            // Tapi untuk amannya kita replace enter visuals
            
            if (currentIndex < spans.length) {
                // Hapus cursor lama
                spans[currentIndex].classList.remove('cursor');

                if (e.target.value.slice(-1) === currentChar) {
                    spans[currentIndex].classList.add('char-correct');
                } else {
                    spans[currentIndex].classList.add('char-wrong');
                    mistakes++;
                }
                
                currentIndex++;
                charactersTyped++;

                // Tambah cursor ke next char
                if (currentIndex < spans.length) {
                    spans[currentIndex].classList.add('cursor');
                } else {
                    // Selesai snippet sebelum waktu habis -> Tambah snippet baru atau End?
                    // Kita buat END saja biar simpel (Sprint Mode)
                    endGame();
                }

                // Update Akurasi
                const accuracy = Math.round(((charactersTyped - mistakes) / charactersTyped) * 100);
                accEl.innerText = accuracy;
            }
        });

        // Cegah backspace (Hardcore Mode) - Optional
        hiddenInput.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                e.preventDefault(); // Gak boleh hapus, harus teliti!
            }
        });

        function endGame() {
            clearInterval(timerInterval);
            isPlaying = false;
            
            // Hitung Final WPM
            const timeUsed = 30 - timeLeft;
            const grossWPM = Math.round((charactersTyped / 5) / (timeUsed / 60));
            const netWPM = Math.round(((charactersTyped - mistakes) / 5) / (timeUsed / 60));
            const finalAccuracy = charactersTyped > 0 ? Math.round(((charactersTyped - mistakes) / charactersTyped) * 100) : 0;

            document.getElementById('finalWPM').innerText = netWPM < 0 ? 0 : netWPM;
            document.getElementById('finalAcc').innerText = finalAccuracy + "%";
            document.getElementById('finalChars').innerText = charactersTyped;

            resultScreen.style.display = 'flex';
        }

        function resetGame() {
            resultScreen.style.display = 'none';
            startOverlay.style.display = 'flex';
            initGame();
        }

        // Initialize first run
        initGame();

    </script>
</body>
</html>