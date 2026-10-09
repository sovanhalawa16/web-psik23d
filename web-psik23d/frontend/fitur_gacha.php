<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Team Gacha | PSIK 23 D</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --secondary-gradient: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
            --card-shadow: 0 10px 40px -10px rgba(0,0,0,0.1);
            --hover-shadow: 0 20px 40px -5px rgba(0,0,0,0.15);
        }

        body { 
            background-color: #f8f9fa;
            background-image: radial-gradient(#e0e7ff 1px, transparent 1px);
            background-size: 24px 24px;
            color: #334155; 
            font-family: 'Poppins', sans-serif; 
            min-height: 100vh; 
            padding-bottom: 80px;
        }
        
        .gacha-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        /* Header Styling */
        .gacha-header { text-align: center; margin-bottom: 50px; position: relative; }
        .gacha-title { 
            font-family: 'Poppins', sans-serif; 
            font-weight: 800;
            font-size: 2.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
            letter-spacing: -1px;
        }
        .gacha-subtitle { font-size: 0.95rem; color: #64748b; font-weight: 400; }
        
        /* Control Panel */
        .control-panel { 
            background: white; 
            border-radius: 24px; 
            padding: 40px; 
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }
        .control-panel::before {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 150px; height: 150px; background: #e0e7ff; border-radius: 50%; opacity: 0.5;
        }
        
        /* Mode Switcher */
        .nav-pills { background: #f1f5f9; padding: 5px; border-radius: 16px; margin-bottom: 30px; }
        .nav-pills .nav-link { 
            color: #64748b; font-weight: 600; border-radius: 12px; transition: all 0.3s ease; border: none;
        }
        .nav-pills .nav-link:hover { color: #4f46e5; background: rgba(255,255,255,0.5); }
        .nav-pills .nav-link.active { 
            background: white; color: #4f46e5; box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        /* Inputs & Selects */
        .form-label { font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 8px; }
        .form-control, .form-select { 
            background: #f8fafc; border: 2px solid #e2e8f0; color: #334155; 
            border-radius: 12px; padding: 12px 15px; font-weight: 500; transition: 0.2s;
        }
        .form-control:focus, .form-select:focus { 
            background: white; border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); 
        }
        
        /* Gacha Button */
        .btn-gacha { 
            background: var(--primary-gradient); color: white; 
            font-family: 'Rajdhani', sans-serif; font-weight: 700; width: 100%; 
            padding: 14px; border-radius: 12px; border: none; font-size: 1.3rem; 
            letter-spacing: 1px; box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
        }
        .btn-gacha:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.5); }
        .btn-gacha:active { transform: translateY(1px); }

        /* Loading */
        .loading-text { 
            color: #6366f1; display: none; text-align: center; margin-top: 25px; 
            font-weight: 600; letter-spacing: 1px; animation: pulse 1.5s infinite;
        }
        @keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }

        /* Results */
        .result-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 25px; margin-top: 50px; }
        
        .group-card { 
            background: white; border-radius: 20px; overflow: hidden; 
            box-shadow: var(--card-shadow); transition: 0.3s; border: 1px solid #f1f5f9;
            animation: popUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            opacity: 0; transform: scale(0.9);
        }
        .group-card:hover { transform: translateY(-5px); box-shadow: var(--hover-shadow); border-color: #e0e7ff; }
        
        .group-header { 
            background: var(--secondary-gradient); padding: 15px 20px; 
            display: flex; justify-content: space-between; align-items: center;
            color: white; font-family: 'Rajdhani', sans-serif; letter-spacing: 1px;
        }
        .group-title { font-weight: 700; font-size: 1.1rem; }
        .group-badge { 
            background: rgba(255,255,255,0.2); font-size: 0.8rem; padding: 4px 10px; 
            border-radius: 20px; font-weight: 600; backdrop-filter: blur(5px);
        }
        
        /* Members */
        .group-body { padding: 20px; }
        .member-item { 
            padding: 8px 0; border-bottom: 1px dashed #e2e8f0; color: #475569; 
            font-size: 0.95rem; font-weight: 500; display: flex; align-items: center;
        }
        .member-item::before {
            content: '\f007'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            margin-right: 10px; color: #cbd5e1; font-size: 0.8rem;
        }
        .member-item:last-child { border-bottom: none; }

        /* Copy Button */
        .btn-copy-wa { 
            position: fixed; bottom: 30px; right: 30px; background: #25D366; color: white; 
            width: 65px; height: 65px; border-radius: 50%; display: none; 
            align-items: center; justify-content: center; font-size: 28px; 
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.4); border: 4px solid white;
            z-index: 100; cursor: pointer; transition: 0.3s;
        }
        .btn-copy-wa:hover { transform: rotate(15deg) scale(1.1); }

        @keyframes popUp { to { opacity: 1; transform: scale(1); } }
        
        .custom-alert { background: #eff6ff; border: 1px solid #dbeafe; color: #1e40af; border-radius: 12px; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="gacha-container">
    <div class="gacha-header">
        <h1 class="gacha-title">TEAM GACHA</h1>
        <p class="gacha-subtitle">Generator Kelompok Otomatis • Fair Play • No Settingan</p>
    </div>

    <div class="control-panel">
        <ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="mode-kelas-tab" data-bs-toggle="pill" data-bs-target="#mode-kelas" type="button" role="tab" onclick="setMode('kelas')">
                    <i class="fas fa-university me-2"></i>PSIK 23 D
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mode-umum-tab" data-bs-toggle="pill" data-bs-target="#mode-umum" type="button" role="tab" onclick="setMode('umum')">
                    <i class="fas fa-edit me-2"></i>INPUT MANUAL
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="mode-kelas" role="tabpanel">
                <div class="alert custom-alert d-flex align-items-center mb-4">
                    <i class="fas fa-cloud-download-alt me-3 fs-4"></i>
                    <div>
                        <strong>Database Terhubung</strong><br>
                        Data akan diambil otomatis dari server PSIK 23 D.
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="mode-umum" role="tabpanel">
                <div class="mb-4">
                    <label class="form-label">DAFTAR NAMA (PISAHKAN DENGAN ENTER)</label>
                    <textarea id="manualNames" class="form-control" rows="5" placeholder="Contoh:&#10;Budi&#10;Siti&#10;Joko&#10;..."></textarea>
                </div>
            </div>
        </div>

        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">METODE GACHA</label>
                <select class="form-select" id="gachaMethod" onchange="updateInputLabel()">
                    <option value="byGroup">Berdasarkan Jumlah Kelompok</option>
                    <option value="byMember">Berdasarkan Jumlah Anggota</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label" id="inputLabel">MAU BERAPA KELOMPOK?</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 border-2" style="border-radius: 12px 0 0 12px; border-color: #e2e8f0;">
                        <i class="fas fa-sort-numeric-up text-muted"></i>
                    </span>
                    <input type="number" id="inputValue" class="form-control border-start-0 ps-0" style="border-radius: 0 12px 12px 0;" placeholder="Contoh: 5" min="1">
                </div>
            </div>

            <div class="col-md-3">
                <button onclick="startGacha()" class="btn-gacha">
                    <i class="fas fa-dice me-2"></i> GACHA!
                </button>
            </div>
        </div>
        
        <div id="loadingAnim" class="loading-text">
            <i class="fas fa-circle-notch fa-spin me-2"></i> SEDANG MENGHITUNG...
        </div>
    </div>

    <div id="resultArea" class="result-grid"></div>
    
    <div class="mt-5 text-center">
        <a href="features.php" class="text-secondary text-decoration-none small" style="opacity: 0.6; transition: 0.3s;">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Menu Fitur
        </a>
    </div>
</div>

<button class="btn-copy-wa" id="btnCopy" onclick="copyToClipboard()" title="Salin ke WhatsApp">
    <i class="fab fa-whatsapp"></i>
</button>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="liveToast" class="toast align-items-center text-bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body fw-bold">
        <i class="fas fa-check-circle me-2"></i> Berhasil disalin!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let currentMode = 'kelas';
    let finalGroups = [];

    function setMode(mode) {
        currentMode = mode;
    }

    // Ubah Label Input berdasarkan Pilihan Dropdown
    function updateInputLabel() {
        const method = document.getElementById('gachaMethod').value;
        const label = document.getElementById('inputLabel');
        const input = document.getElementById('inputValue');

        if (method === 'byGroup') {
            label.innerText = "MAU BERAPA KELOMPOK?";
            input.placeholder = "Contoh: 5 (Kelompok)";
        } else {
            label.innerText = "MAU BERAPA ORANG/KELOMPOK?";
            input.placeholder = "Contoh: 4 (Orang)";
        }
    }

    // Algoritma Fisher-Yates Shuffle
    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    async function startGacha() {
        const inputValue = parseInt(document.getElementById('inputValue').value);
        const gachaMethod = document.getElementById('gachaMethod').value;
        const resultArea = document.getElementById('resultArea');
        const loading = document.getElementById('loadingAnim');
        const btnCopy = document.getElementById('btnCopy');

        // Validasi Input
        if (!inputValue || inputValue < 1) {
            alert("Masukkan angka yang valid!");
            return;
        }

        resultArea.innerHTML = ''; 
        btnCopy.style.display = 'none';
        loading.style.display = 'block';

        let names = [];

        // 1. Ambil Data Nama (Sama seperti sebelumnya)
        if (currentMode === 'kelas') {
            try {
                const response = await fetch('../backend/api_get_all_names.php');
                const result = await response.json();
                if (result.status === 'success') {
                    names = result.data;
                } else {
                    alert("Gagal mengambil data kelas.");
                    loading.style.display = 'none';
                    return;
                }
            } catch (error) {
                // Fallback Demo
                console.log("Mode Offline/Demo"); 
                names = ["Andi", "Budi", "Citra", "Doni", "Eka", "Fani", "Gita", "Hadi", "Indra", "Joni", "Kiki", "Lina", "Mina", "Nina", "Oki"];
            }
        } else {
            const text = document.getElementById('manualNames').value;
            names = text.split('\n').map(n => n.trim()).filter(n => n !== '');
            if (names.length === 0) {
                alert("Masukkan minimal satu nama!");
                loading.style.display = 'none';
                return;
            }
        }

        // 2. HITUNG LOGIC BERDASARKAN METODE
        let totalGroupsNeeded = 0;

        if (gachaMethod === 'byGroup') {
            // Logic Lama: User minta X kelompok
            if (inputValue > names.length) {
                alert(`Jumlah kelompok (${inputValue}) tidak boleh lebih banyak dari jumlah orang (${names.length})!`);
                loading.style.display = 'none';
                return;
            }
            totalGroupsNeeded = inputValue;

        } else if (gachaMethod === 'byMember') {
            // Logic Baru: User minta X orang per kelompok
            // Rumus: Total Orang / Orang per Kelompok (dibulatkan ke atas)
            if (inputValue > names.length) {
                alert(`Jumlah anggota per kelompok (${inputValue}) tidak boleh lebih banyak dari total orang (${names.length})!`);
                loading.style.display = 'none';
                return;
            }
            totalGroupsNeeded = Math.ceil(names.length / inputValue);
        }

        // 3. Eksekusi
        setTimeout(() => {
            loading.style.display = 'none';
            processGacha(names, totalGroupsNeeded);
        }, 1500);
    }

    function processGacha(names, groupCount) {
        const resultArea = document.getElementById('resultArea');
        
        const shuffledNames = shuffleArray(names);
        const groups = Array.from({ length: groupCount }, () => []);
        
        // Distribusi Zig-Zag
        shuffledNames.forEach((name, index) => {
            const groupIndex = index % groupCount;
            groups[groupIndex].push(name);
        });

        finalGroups = groups; 

        // Render HTML
        groups.forEach((group, index) => {
            let memberHtml = '';
            const memberCount = group.length;

            group.forEach(member => {
                memberHtml += `<div class="member-item">${member}</div>`;
            });

            const html = `
                <div class="group-card">
                    <div class="group-header">
                        <span class="group-title">KELOMPOK ${index + 1}</span>
                        <span class="group-badge">${memberCount} Orang</span>
                    </div>
                    <div class="group-body">
                        ${memberHtml}
                    </div>
                </div>
            `;
            resultArea.innerHTML += html;
        });

        document.getElementById('btnCopy').style.display = 'flex';
    }

    function copyToClipboard() {
        if (finalGroups.length === 0) return;

        let text = "*HASIL PEMBAGIAN KELOMPOK PSIK 23 D* 🎲\n\n";
        
        finalGroups.forEach((group, index) => {
            text += `*🔥 KELOMPOK ${index + 1} (${group.length} Orang)*\n`;
            group.forEach(member => {
                text += `- ${member}\n`;
            });
            text += `\n`;
        });
        
        text += "_Generated by PSIK 23 D Web System_";

        navigator.clipboard.writeText(text).then(() => {
            const toastLiveExample = document.getElementById('liveToast');
            const toast = new bootstrap.Toast(toastLiveExample);
            toast.show();
        });
    }
</script>

</body>
</html>