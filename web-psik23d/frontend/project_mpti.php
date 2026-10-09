<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusBot - PSIK 23 D</title>
    <!-- Font & Icons Premium -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ==========================================
           UI/UX PREMIUM CHATBOT (iOS Style)
        ========================================== */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #e2e8f0; 
            height: 100vh; margin: 0;
            display: flex; justify-content: center; align-items: center;
        }

        .chat-container {
            width: 100%; max-width: 420px; height: 100vh;
            background: #ffffff; display: flex; flex-direction: column;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }

        @media (min-width: 450px) {
            .chat-container { height: 92vh; border-radius: 24px; overflow: hidden; border: 5px solid #ffffff; }
        }

        /* HEADER PREMIUM - Gradient Kaca Es */
        .chat-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white; padding: 18px 20px;
            display: flex; align-items: center; gap: 15px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.15); z-index: 10;
        }
        .bot-avatar {
            width: 48px; height: 48px; border-radius: 50%;
            background: #ffffff; color: #2563eb;
            display: flex; justify-content: center; align-items: center;
            font-size: 1.5rem; border: 2px solid rgba(255,255,255,0.8);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative;
        }
        /* Titik Hijau Online */
        .bot-avatar::after {
            content: ''; position: absolute; bottom: 2px; right: 2px;
            width: 12px; height: 12px; background: #10b981;
            border-radius: 50%; border: 2px solid #ffffff;
        }

        .header-info h5 { margin: 0; font-weight: 800; font-size: 1.1rem; letter-spacing: 0.5px; }
        .header-info p { margin: 0; font-size: 0.75rem; opacity: 0.9; font-weight: 500; }

        /* AREA CHAT UTAMA */
        .chat-body {
            flex: 1; padding: 20px; background: #f8fafc;
            overflow-y: auto; display: flex; flex-direction: column; gap: 15px;
            scroll-behavior: smooth;
        }
        .chat-body::-webkit-scrollbar { width: 6px; }
        .chat-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* DESAIN BUBBLE PESAN */
        .msg-bubble {
            max-width: 85%; padding: 12px 18px; border-radius: 20px;
            font-size: 0.9rem; line-height: 1.5; position: relative;
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            word-wrap: break-word; font-weight: 500;
        }
        @keyframes popIn { 
            0% { opacity: 0; transform: scale(0.9) translateY(10px); } 
            100% { opacity: 1; transform: scale(1) translateY(0); } 
        }

        /* Bubble User (Kanan) */
        .msg-user {
            align-self: flex-end; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff; border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }

        /* Bubble Bot (Kiri) */
        .msg-bot {
            align-self: flex-start; background: #ffffff;
            color: #334155; border-bottom-left-radius: 4px;
            border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        /* EFEK ANIMASI NGETIK (TYPING INDICATOR) */
        .typing-indicator {
            display: flex; gap: 4px; padding: 15px 18px; align-items: center;
            width: fit-content; border-bottom-left-radius: 4px;
        }
        .dot {
            width: 7px; height: 7px; background: #94a3b8;
            border-radius: 50%; animation: typingBounce 1.4s infinite ease-in-out both;
        }
        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typingBounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        /* QUICK MENU (TOMBOL PINTAS) */
        .quick-replies {
            padding: 10px 15px; background: #ffffff;
            display: flex; gap: 10px; overflow-x: auto;
            border-top: 1px solid #f1f5f9; scrollbar-width: none;
        }
        .quick-replies::-webkit-scrollbar { display: none; }
        .btn-quick {
            background: #eff6ff; color: #2563eb;
            border: 1px solid #bfdbfe; border-radius: 20px;
            padding: 8px 16px; font-size: 0.8rem; font-weight: 700;
            white-space: nowrap; cursor: pointer; transition: 0.2s;
            box-shadow: 0 2px 5px rgba(37,99,235,0.05);
        }
        .btn-quick:hover { background: #dbeafe; transform: translateY(-2px); }
        .btn-quick:active { transform: scale(0.95); background: #3b82f6; color: white; }

        /* AREA INPUT BAWAH */
        .chat-footer {
            padding: 15px 20px; background: #ffffff;
            display: flex; gap: 12px; align-items: center;
            border-top: 1px solid #f1f5f9;
        }
        .chat-input {
            flex: 1; border: 1px solid #e2e8f0; border-radius: 25px;
            padding: 12px 20px; font-size: 0.9rem; outline: none;
            background: #f8fafc; transition: 0.3s; font-weight: 500;
        }
        .chat-input:focus { border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
        
        .btn-send {
            width: 48px; height: 48px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white; border: none; display: flex; justify-content: center; align-items: center;
            cursor: pointer; box-shadow: 0 4px 15px rgba(37,99,235,0.3); transition: 0.2s; flex-shrink: 0;
        }
        .btn-send:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(37,99,235,0.4); }
        .btn-send:active { transform: scale(0.9); }
    </style>
</head>
<body>

    <div class="chat-container">
        <!-- HEADER -->
        <div class="chat-header">
            <div class="bot-avatar"><i class="fas fa-robot"></i></div>
            <div class="header-info">
                <h5>CampusBot</h5>
                <p>Asisten Cerdas Kampus</p>
            </div>
        </div>

        <!-- AREA PESAN -->
        <div class="chat-body" id="chatBody">
            <!-- Pesan Pembuka -->
            <div class="msg-bubble msg-bot">
                Halo! 👋 Saya <b>CampusBot</b>, ada yang bisa saya bantu?
            </div>
        </div>

        <!-- MENU PINTAS -->
        <div class="quick-replies">
            <button class="btn-quick" onclick="sendQuick('Jadwal kuliah hari ini?')">📅 Jadwal Hari Ini</button>
            <button class="btn-quick" onclick="sendQuick('Siapa dosen MPTI?')">👨‍🏫 Dosen MPTI</button>
            <button class="btn-quick" onclick="sendQuick('Jam buka perpustakaan?')">📚 Info Perpus</button>
            <button class="btn-quick" onclick="sendQuick('Kapan deadline tugas?')">📝 Tugas</button>
        </div>

        <!-- AREA KETIK -->
        <div class="chat-footer">
            <input type="text" id="userInput" class="chat-input" placeholder="Ketik pertanyaan Anda..." onkeypress="handleEnter(event)" autocomplete="off">
            <button class="btn-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- ==========================================
         LOGIKA JAVASCRIPT (CLIENT-SIDE BRAIN - SOP MPTI)
    ========================================== -->
    <script>
        const chatBody = document.getElementById('chatBody');
        const userInput = document.getElementById('userInput');

        // Scroll otomatis ke paling bawah
        function scrollToBottom() {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // Tampilkan pesan ke layar
        function appendMessage(sender, text) {
            const div = document.createElement('div');
            div.className = `msg-bubble ${sender === 'user' ? 'msg-user' : 'msg-bot'}`;
            div.innerHTML = text;
            chatBody.appendChild(div);
            scrollToBottom();
        }

        // Animasi "Typing..."
        function showTypingIndicator() {
            const div = document.createElement('div');
            div.className = 'msg-bubble msg-bot typing-indicator';
            div.id = 'typingIndicator';
            div.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
            chatBody.appendChild(div);
            scrollToBottom();
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }

        // Fungsi klik tombol shortcut
        function sendQuick(text) {
            userInput.value = text;
            sendMessage();
        }

        // Fungsi Enter keyboard
        function handleEnter(e) {
            if (e.key === 'Enter') sendMessage();
        }

        // Alur pengiriman pesan
        function sendMessage() {
            const text = userInput.value.trim();
            if (text === '') return;

            // 1. Munculin chat user
            appendMessage('user', text);
            userInput.value = '';

            // 2. Munculin animasi ngetik (mikir)
            showTypingIndicator();

            // 3. Jawab setelah jeda waktu (1 detik biar natural)
            setTimeout(() => {
                removeTypingIndicator();
                const balasan = processBotBrain(text);
                appendMessage('bot', balasan);
            }, 1000); 
        }

        // ==========================================
        // OTAK BOT: SESUAI SOP HARDCODE DARI DOSEN
        // ==========================================
        function processBotBrain(rawInput) {
            // Ubah teks ke huruf kecil semua biar gak sensitif
            const input = rawInput.toLowerCase();

            // 1. Jadwal kuliah hari ini?
            if (input.includes('jadwal') && input.includes('hari ini')) {
                return "Hari ini ada mata kuliah Manajemen Proyek TI pukul 13.00 - 15.30 WIB di ruangan 77.3.08 Lab Ilkom";
            }
            
            // 2. Jam berapa kuliah NLP?
            else if (input.includes('jam') && input.includes('nlp')) {
                return "Mata kuliah NLP dimulai pukul 10.30 WIB";
            }
            
            // 3. Siapa dosen MPTI?
            else if (input.includes('dosen') && input.includes('mpti')) {
                return "Dosen Manajemen Proyek TI adalah Bu Sri Dewi, S.Kom., M.Kom.";
            }
            
            // 4. Kapan deadline tugas?
            else if (input.includes('deadline')) {
                return "Deadline tugas Manajemen Proyek adalah hari Jumat";
            }
            
            // 5. Dimana ruang kelas NLP?
            else if (input.includes('ruang') && input.includes('nlp')) {
                return "Ruang kelas NLP berada di gedung 77 lantai 1 tepatnya di ruangan 77.1.08 Lab Ilkom";
            }
            
            // 6. Kapan jadwal ujian?
            else if (input.includes('ujian')) {
                return "Ujian dilaksanakan sesuai jadwal akademik minggu depan";
            }
            
            // 7. Jam buka perpustakaan?
            else if (input.includes('jam') && (input.includes('perpus') || input.includes('perpustakaan'))) {
                return "Perpustakaan buka pukul 08.00 – 17.00 WIB";
            }
            
            // 8. Ada kegiatan kampus apa minggu ini?
            else if (input.includes('kegiatan')) {
                return "Minggu ini ada seminar teknologi pada hari Sabtu";
            }
            
            // 9. Bagaimana cara menghubungi admin kampus?
            else if (input.includes('admin') || input.includes('hubungi')) {
                return "Anda dapat menghubungi admin melalui email resmi kampus";
            }
            
            // 10. Apakah ada tugas minggu ini?
            else if (input.includes('tugas') && input.includes('minggu ini')) {
                return "Ya, terdapat tugas pada mata kuliah MPTI";
            }
            
            // FALLBACK
            else {
                return "Maaf, pertanyaan tidak tersedia. Silakan pilih menu yang tersedia.";
            }
        }
    </script>
</body>
</html>