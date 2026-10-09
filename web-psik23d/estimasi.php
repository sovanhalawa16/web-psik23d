<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Estimasi Project IT</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00529C; 
            --secondary: #F26522; 
            --dark: #003366;
            --bg-body: #F4F7F9;
            --text-main: #2C3E50;
            --border: #E5E8E8;
            --card-bg: rgba(255, 255, 255, 0.95);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }

        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            color: var(--text-main);
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
        }

        /* Ornamen Background */
        .bg-glow { position: fixed; top: -10%; left: -10%; width: 500px; height: 500px; background: rgba(255,255,255,0.08); filter: blur(120px); border-radius: 50%; z-index: 0; pointer-events: none; }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }

        /* Bagian Kiri (Form Input) */
        .form-section {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .header-title h2 { color: var(--primary); font-size: 28px; margin-bottom: 5px; }
        .header-title p { color: #7F8C8D; font-size: 14px; margin-bottom: 30px; }

        .group-title { font-size: 16px; font-weight: 700; color: var(--secondary); margin: 25px 0 15px; border-bottom: 1px dashed var(--border); padding-bottom: 10px; text-transform: uppercase; }

        /* Custom Radio & Checkbox Card Style */
        .option-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        
        .option-card {
            position: relative;
            background: #F8F9F9;
            border: 2px solid var(--border);
            border-radius: 14px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .option-card:hover { border-color: var(--primary); background: #fff; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        
        .option-card input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
        
        .option-card .title { font-weight: 700; font-size: 14px; color: var(--text-main); margin-bottom: 5px; }
        .option-card .desc { font-size: 12px; color: #7F8C8D; font-weight: 500; }
        .option-card .icon-check { position: absolute; top: 15px; right: 15px; color: transparent; font-size: 16px; transition: 0.3s; }

        /* Jika Dipilih (Checked) */
        .option-card input:checked ~ .title { color: var(--primary); }
        .option-card input:checked ~ .icon-check { color: var(--secondary); }
        .option-card.selected { border-color: var(--secondary); background: #FFF3ED; }

        /* Bagian Kanan (Ringkasan Harga Sticky) */
        .summary-section {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            height: fit-content;
            position: sticky;
            top: 40px;
            border: 2px solid var(--primary);
        }

        .summary-title { font-size: 18px; font-weight: 700; color: var(--primary); margin-bottom: 20px; text-align: center; }
        
        .total-box { background: var(--primary); color: white; padding: 20px; border-radius: 16px; text-align: center; margin-bottom: 20px; box-shadow: 0 10px 20px rgba(0, 82, 156, 0.2); }
        .total-box h4 { font-size: 13px; font-weight: 500; opacity: 0.9; margin-bottom: 5px; }
        .total-box h1 { font-size: 32px; font-weight: 700; margin-bottom: 10px; }
        .total-box .time { background: rgba(255,255,255,0.2); display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; }

        .breakdown-list { list-style: none; margin-bottom: 20px; max-height: 250px; overflow-y: auto; padding-right: 5px;}
        .breakdown-list::-webkit-scrollbar { width: 5px; }
        .breakdown-list::-webkit-scrollbar-thumb { background: #BDC3C7; border-radius: 10px; }

        .breakdown-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed var(--border); font-size: 13px; }
        .breakdown-item:last-child { border-bottom: none; }
        .breakdown-name { color: #7F8C8D; font-weight: 600; max-width: 60%; }
        .breakdown-val { color: var(--text-main); font-weight: 700; text-align: right; }

        .btn-order { width: 100%; background: var(--secondary); color: white; border: none; padding: 15px; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; transition: 0.3s; box-shadow: 0 8px 15px rgba(242, 101, 34, 0.3); text-transform: uppercase; }
        .btn-order:hover { background: #d9531e; transform: translateY(-2px); }

        @media (max-width: 900px) {
            .container { grid-template-columns: 1fr; }
            .summary-section { position: relative; top: 0; }
            .form-section { padding: 25px; }
        }
    </style>
</head>
<body>

    <div class="bg-glow"></div>

    <div class="container">
        
        <div class="form-section">
            <div class="header-title">
                <h2>Project Cost Estimator</h2>
                <p>Pilih spesifikasi aplikasi yang Anda butuhkan, sistem akan menghitung estimasi biaya dan waktu pengerjaan secara otomatis.</p>
            </div>

            <form id="estimatorForm">
                
                <div class="group-title"><i class="fa-solid fa-display"></i> 1. Platform Utama</div>
                <div class="option-grid">
                    <label class="option-card" onclick="selectCard(this, 'platform')">
                        <input type="radio" name="platform" value="5000000" data-time="14" data-name="Web Application" onchange="calculate()">
                        <i class="fa-solid fa-circle-check icon-check"></i>
                        <div class="title">Web App</div>
                        <div class="desc">Sistem berbasis browser</div>
                    </label>
                    <label class="option-card" onclick="selectCard(this, 'platform')">
                        <input type="radio" name="platform" value="10000000" data-time="30" data-name="Mobile App (Android/iOS)" onchange="calculate()">
                        <i class="fa-solid fa-circle-check icon-check"></i>
                        <div class="title">Mobile App</div>
                        <div class="desc">Aplikasi Android & iOS</div>
                    </label>
                    <label class="option-card" onclick="selectCard(this, 'platform')">
                        <input type="radio" name="platform" value="14000000" data-time="40" data-name="Web + Mobile App" onchange="calculate()">
                        <i class="fa-solid fa-circle-check icon-check"></i>
                        <div class="title">Web + Mobile</div>
                        <div class="desc">Paket lengkap terintegrasi</div>
                    </label>
                </div>

                <div class="group-title"><i class="fa-solid fa-code"></i> 2. Teknologi (Tech Stack)</div>
                <div class="option-grid">
                    <label class="option-card" onclick="selectCard(this, 'stack')">
                        <input type="radio" name="stack" value="0" data-time="0" data-name="Standard Stack (PHP/MySQL)" onchange="calculate()">
                        <i class="fa-solid fa-circle-check icon-check"></i>
                        <div class="title">Standard (PHP)</div>
                        <div class="desc">Cepat & stabil (Free)</div>
                    </label>
                    <label class="option-card" onclick="selectCard(this, 'stack')">
                        <input type="radio" name="stack" value="3000000" data-time="7" data-name="Modern Stack (React/Node.js)" onchange="calculate()">
                        <i class="fa-solid fa-circle-check icon-check"></i>
                        <div class="title">Modern (React.js)</div>
                        <div class="desc">Sangat cepat & interaktif</div>
                    </label>
                    <label class="option-card" onclick="selectCard(this, 'stack')">
                        <input type="radio" name="stack" value="2000000" data-time="5" data-name="Mobile Framework (Flutter)" onchange="calculate()">
                        <i class="fa-solid fa-circle-check icon-check"></i>
                        <div class="title">Flutter (Mobile)</div>
                        <div class="desc">Cross-platform UI</div>
                    </label>
                </div>

                <div class="group-title"><i class="fa-solid fa-palette"></i> 3. Kualitas Desain UI/UX</div>
                <div class="option-grid">
                    <label class="option-card" onclick="selectCard(this, 'design')">
                        <input type="radio" name="design" value="0" data-time="0" data-name="Standard UI Template" onchange="calculate()">
                        <i class="fa-solid fa-circle-check icon-check"></i>
                        <div class="title">Standard Template</div>
                        <div class="desc">Desain rapi & fungsional</div>
                    </label>
                    <label class="option-card" onclick="selectCard(this, 'design')">
                        <input type="radio" name="design" value="3500000" data-time="10" data-name="Custom Premium UI/UX" onchange="calculate()">
                        <i class="fa-solid fa-circle-check icon-check"></i>
                        <div class="title">Custom UI/UX</div>
                        <div class="desc">Desain eksklusif & modern</div>
                    </label>
                </div>

                <div class="group-title"><i class="fa-solid fa-list-check"></i> 4. Fitur Tambahan (Bisa pilih banyak)</div>
                <div class="option-grid">
                    <label class="option-card" onclick="toggleCard(this)">
                        <input type="checkbox" name="features" value="500000" data-time="2" data-name="Sistem Login & Keamanan" onchange="calculate()">
                        <i class="fa-solid fa-check icon-check"></i>
                        <div class="title">Login & Register</div>
                        <div class="desc">+ Rp 500.000</div>
                    </label>
                    <label class="option-card" onclick="toggleCard(this)">
                        <input type="checkbox" name="features" value="2500000" data-time="7" data-name="Admin Dashboard Panel" onchange="calculate()">
                        <i class="fa-solid fa-check icon-check"></i>
                        <div class="title">Admin Dashboard</div>
                        <div class="desc">+ Rp 2.500.000</div>
                    </label>
                    <label class="option-card" onclick="toggleCard(this)">
                        <input type="checkbox" name="features" value="2000000" data-time="5" data-name="Payment Gateway Integration" onchange="calculate()">
                        <i class="fa-solid fa-check icon-check"></i>
                        <div class="title">Payment Gateway</div>
                        <div class="desc">+ Rp 2.000.000</div>
                    </label>
                    <label class="option-card" onclick="toggleCard(this)">
                        <input type="checkbox" name="features" value="3000000" data-time="7" data-name="Realtime Chat System" onchange="calculate()">
                        <i class="fa-solid fa-check icon-check"></i>
                        <div class="title">Realtime Chat</div>
                        <div class="desc">+ Rp 3.000.000</div>
                    </label>
                </div>

            </form>
        </div>

        <div class="summary-section">
            <div class="summary-title">Ringkasan Estimasi</div>
            
            <div class="total-box">
                <h4>Total Biaya Project</h4>
                <h1 id="totalPrice">Rp 0</h1>
                <div class="time"><i class="fa-regular fa-clock"></i> Estimasi: <span id="totalTime">0</span> Hari</div>
            </div>

            <ul class="breakdown-list" id="breakdownList">
                <li class="breakdown-item" style="justify-content: center; color: #BDC3C7;">Belum ada spesifikasi yang dipilih</li>
            </ul>

            <button class="btn-order" onclick="hubungiWA()"><i class="fa-brands fa-whatsapp"></i> Konsultasi Sekarang</button>
        </div>

    </div>

    <script>
        // Fungsi Format Uang Rupiah
        const formatRupiah = (angka) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        };

        // Fungsi Styling Radio Button (Biar cuma 1 yg nyala per grup)
        function selectCard(element, groupName) {
            let cards = document.querySelectorAll(`input[name="${groupName}"]`);
            cards.forEach(card => card.parentElement.classList.remove('selected'));
            element.classList.add('selected');
        }

        // Fungsi Styling Checkbox (Bisa nyala/mati banyak)
        function toggleCard(element) {
            let checkbox = element.querySelector('input');
            // Timeout dipakai karena event onclick label dan onchange input kadang bentrok
            setTimeout(() => {
                if(checkbox.checked) element.classList.add('selected');
                else element.classList.remove('selected');
            }, 10);
        }

        // LOGIKA UTAMA PERHITUNGAN
        function calculate() {
            let totalBiaya = 0;
            let totalWaktu = 0;
            let breakdownHTML = '';

            // Ambil semua input yang di-cek (Radio & Checkbox)
            const inputs = document.querySelectorAll('#estimatorForm input:checked');
            
            if(inputs.length === 0) {
                document.getElementById('breakdownList').innerHTML = '<li class="breakdown-item" style="justify-content: center; color: #BDC3C7;">Belum ada spesifikasi yang dipilih</li>';
                document.getElementById('totalPrice').innerText = 'Rp 0';
                document.getElementById('totalTime').innerText = '0';
                return;
            }

            inputs.forEach(input => {
                let harga = parseInt(input.value);
                let waktu = parseInt(input.getAttribute('data-time'));
                let nama = input.getAttribute('data-name');

                totalBiaya += harga;
                totalWaktu += waktu;

                // Tambahkan ke daftar rincian
                breakdownHTML += `
                    <li class="breakdown-item">
                        <span class="breakdown-name">${nama}</span>
                        <span class="breakdown-val">${harga === 0 ? 'Free' : formatRupiah(harga)}</span>
                    </li>
                `;
            });

            // Update DOM HTML
            document.getElementById('totalPrice').innerText = formatRupiah(totalBiaya);
            document.getElementById('totalTime').innerText = totalWaktu;
            document.getElementById('breakdownList').innerHTML = breakdownHTML;
        }

        // Tombol Konsultasi WA
        function hubungiWA() {
            let total = document.getElementById('totalPrice').innerText;
            if(total === 'Rp 0') {
                alert("Silakan pilih spesifikasi aplikasi terlebih dahulu!");
                return;
            }
            let pesan = `Halo Mas, saya sudah cek di Kalkulator Estimasi. Saya butuh aplikasi dengan estimasi harga ${total}. Bisa kita diskusi lebih lanjut?`;
            let noWA = "6281234567890"; // GANTI DENGAN NOMOR WA LU BRO
            window.open(`https://api.whatsapp.com/send?phone=${noWA}&text=${encodeURIComponent(pesan)}`, '_blank');
        }
    </script>
</body>
</html>