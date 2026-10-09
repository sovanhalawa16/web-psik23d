<?php
// Mencegah error jika session belum dimulai
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- 1. BACKEND AJAX (BYPASS INFINITYFREE) ---
if (isset($_POST['action_chat'])) {
    // JURUS KASTA DEWA: Bersihkan semua output HTML dari jadwal_kelas.php biar JSON gak error!
    if(ob_get_length()) { ob_clean(); } 
    
    include '../koneksi.php'; // Panggil koneksi DB
    
    $nama_saya = $_SESSION['nama'] ?? 'Anonim'; // Ambil nama dari session login

    if ($_POST['action_chat'] == 'send') {
        $pesan = mysqli_real_escape_string($koneksi, htmlspecialchars($_POST['pesan']));
        $waktu = date('Y-m-d H:i:s');
        if(!empty($pesan)) {
            mysqli_query($koneksi, "INSERT INTO global_chat (nama, pesan, waktu) VALUES ('$nama_saya', '$pesan', '$waktu')");
        }
        echo json_encode(['status' => 'success']);
    } 
    elseif ($_POST['action_chat'] == 'load') {
        // Ambil 50 chat terakhir dan JOIN dengan tabel users untuk tarik foto asli
        $q_chat = mysqli_query($koneksi, "
            SELECT c.*, u.foto 
            FROM (SELECT * FROM global_chat ORDER BY id DESC LIMIT 50) c 
            LEFT JOIN users u ON c.nama = u.nama 
            ORDER BY c.id ASC
        ");
        
        $html_chat = '';
        while($c = mysqli_fetch_assoc($q_chat)) {
            $waktu_chat = date('H:i', strtotime($c['waktu']));
            $is_me = ($c['nama'] == $nama_saya);
            
            $fotoPath = (!empty($c['foto']) && $c['foto'] !== 'default.jpg' && $c['foto'] !== 'default.png') ? '../assets/img/mahasiswa/' . $c['foto'] : 'https://ui-avatars.com/api/?background=random&name=' . urlencode($c['nama']);
            
            if($is_me) {
                // CHAT SAYA (Di Kanan, Warna Biru)
                $html_chat .= '
                <div class="d-flex flex-row-reverse align-items-end mb-3 gap-2">
                    <img src="'.$fotoPath.'" class="rounded-circle shadow-sm" style="width: 28px; height: 28px; min-width: 28px; object-fit: cover;" onerror="this.src=\'https://ui-avatars.com/api/?background=random&name='.urlencode($c['nama']).'\'">
                    <div class="bg-primary text-white p-2 rounded-4 shadow-sm" style="border-bottom-right-radius: 4px !important; max-width: 75%; padding-left: 12px; padding-right: 12px;">
                        <div class="fw-bold" style="font-size: 0.85rem; line-height: 1.3;">'.$c['pesan'].'</div>
                        <div class="text-end text-white-50 mt-1" style="font-size: 0.6rem;">'.$waktu_chat.' <i class="fas fa-check-double ms-1"></i></div>
                    </div>
                </div>';
            } else {
                // CHAT TEMAN (Di Kiri, Warna Putih)
                $html_chat .= '
                <div class="d-flex align-items-end mb-3 gap-2">
                    <img src="'.$fotoPath.'" class="rounded-circle shadow-sm" style="width: 28px; height: 28px; min-width: 28px; object-fit: cover;" onerror="this.src=\'https://ui-avatars.com/api/?background=random&name='.urlencode($c['nama']).'\'">
                    <div class="bg-white border border-secondary border-opacity-10 p-2 rounded-4 shadow-sm" style="border-bottom-left-radius: 4px !important; max-width: 75%; padding-left: 12px; padding-right: 12px;">
                        <div class="fw-bolder text-primary mb-1" style="font-size: 0.7rem;">'.$c['nama'].'</div>
                        <div class="text-dark fw-bold" style="font-size: 0.85rem; line-height: 1.3;">'.$c['pesan'].'</div>
                        <div class="text-start text-muted mt-1" style="font-size: 0.6rem;">'.$waktu_chat.'</div>
                    </div>
                </div>';
            }
        }
        echo json_encode(['html' => $html_chat]);
    }
    exit; // Wajib exit biar HTML bawah gak kebawa ke JSON
}
?>

<style>
    /* Tombol Bulet Melayang di KANAN BAWAH (Di atas tombol WA) */
    .chat-fab {
        position: fixed; bottom: 85px; right: 20px; z-index: 1050;
        width: 55px; height: 55px; border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #4f46e5); color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; box-shadow: 0 5px 20px rgba(59, 130, 246, 0.4);
        cursor: pointer; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .chat-fab:hover { transform: scale(1.1) translateY(-5px); }
    .chat-fab .badge-notif {
        position: absolute; top: 0; right: 0; width: 14px; height: 14px;
        background: #ef4444; border-radius: 50%; border: 2px solid white;
    }
    
    /* Panel Chat (Disesuaikan posisinya ke Kanan) */
    .chat-panel {
        position: fixed; bottom: 85px; right: 20px; width: 340px; max-width: calc(100vw - 40px);
        height: 500px; max-height: calc(100vh - 120px);
        background: #f8fafc; border-radius: 24px; box-shadow: 0 15px 40px rgba(0,0,0,0.25);
        z-index: 1050; display: flex; flex-direction: column; overflow: hidden;
        transform: translateY(20px) scale(0.9); opacity: 0; pointer-events: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(255,255,255,0.8);
    }
    .chat-panel.active { transform: translateY(0) scale(1); opacity: 1; pointer-events: auto; }
    
    .chat-header {
        background: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;
        border-bottom: 1px solid rgba(0,0,0,0.05); z-index: 2; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .chat-body { flex: 1; padding: 15px; overflow-y: auto; scroll-behavior: smooth; }
    .chat-body::-webkit-scrollbar { width: 4px; }
    .chat-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    .chat-footer {
        background: white; padding: 12px 15px; border-top: 1px solid rgba(0,0,0,0.05); z-index: 2;
    }
</style>

<div class="chat-fab" id="chatFab" onclick="toggleChat()">
    <i class="fas fa-comment-dots"></i>
    <div class="badge-notif blink" id="chatNotif"></div>
</div>

<div class="chat-panel" id="chatPanel">
    <div class="chat-header">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="fas fa-globe-asia fs-6"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bolder text-dark" style="font-size: 0.95rem;">Tongkrongan 23 D</h6>
                <div class="small text-muted fw-bold" style="font-size: 0.65rem;"><span class="text-success blink">●</span> Live Global Chat</div>
            </div>
        </div>
        <button class="btn btn-sm btn-light rounded-circle text-muted shadow-sm" onclick="toggleChat()"><i class="fas fa-times"></i></button>
    </div>
    
    <div class="chat-body" id="chatMessagesArea">
        <div class="text-center my-5 text-muted small fw-bold">
            <i class="fas fa-spinner fa-spin fs-4 mb-2 text-primary"></i><br>Memuat obrolan...
        </div>
    </div>
    
    <div class="chat-footer">
        <form id="formKirimChat" class="d-flex gap-2" onsubmit="kirimChat(event)">
            <input type="text" id="inputPesanChat" class="form-control rounded-pill bg-light border-0 px-4 fw-bold" style="font-size: 0.85rem;" placeholder="Ketik pesan lu..." required autocomplete="off">
            <button type="submit" id="btnKirimChat" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px;">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<script>
    const chatPanel = document.getElementById('chatPanel');
    const chatArea = document.getElementById('chatMessagesArea');
    const chatFab = document.getElementById('chatFab');
    const chatNotif = document.getElementById('chatNotif');
    let chatInterval = null;

    function toggleChat() {
        chatPanel.classList.toggle('active');
        if(chatPanel.classList.contains('active')) {
            // DIBUKA: Matikan notif merah, ubah ikon, mulai polling
            chatNotif.style.display = 'none';
            chatFab.innerHTML = '<i class="fas fa-times"></i>'; 
            chatFab.style.background = '#64748b'; 
            
            loadChat(true); // Load & paksa scroll bawah
            // SMART POLLING: 4 Detik sekali
            chatInterval = setInterval(() => loadChat(false), 4000); 
        } else {
            // DITUTUP: Matikan polling biar InfinityFree GAK MARAH
            chatFab.innerHTML = '<i class="fas fa-comment-dots"></i>';
            chatFab.style.background = 'linear-gradient(135deg, #3b82f6, #4f46e5)';
            clearInterval(chatInterval);
        }
    }

    function loadChat(forceScroll = false) {
        const formData = new FormData();
        formData.append('action_chat', 'load');

        // TEMBAK KE FILE BACKEND LOGIC
        fetch('chat_global_logic.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            let isScrolledToBottom = chatArea.scrollHeight - chatArea.clientHeight <= chatArea.scrollTop + 50;
            
            chatArea.innerHTML = data.html;
            
            if(isScrolledToBottom || forceScroll) {
                chatArea.scrollTop = chatArea.scrollHeight;
            }
        }).catch(e => console.log('Chat refresh standby'));
    }

    function kirimChat(e) {
        e.preventDefault();
        const input = document.getElementById('inputPesanChat');
        const pesan = input.value;
        const btn = document.getElementById('btnKirimChat');
        
        if(pesan.trim() === '') return;

        input.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const formData = new FormData();
        formData.append('action_chat', 'send');
        formData.append('pesan', pesan);

        // TEMBAK KE FILE BACKEND LOGIC
        fetch('chat_global_logic.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            input.value = '';
            input.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            input.focus();
            
            loadChat(true); // Paksa refresh & scroll
        });
    }
</script>