<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPK Simulator | PSIK 23 D</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #f8f9fa;       /* Background halaman abu-abu sangat muda agar mata tidak cepat lelah */
            --card-bg: #ffffff;       /* Card putih bersih */
            --text-main: #1e293b;     /* Text hitam keabu-abuan (lebih enak dibaca) */
            --text-muted: #64748b;
            --accent: #4f46e5;        /* Warna utama (Indigo modern) */
            --border-color: #e2e8f0;
        }
        
        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
        }

        /* Navbar Modern Putih */
        .navbar { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(10px); 
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .navbar-brand { color: var(--accent) !important; letter-spacing: -0.5px; }

        /* Card Styles */
        .calculator-card {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08); /* Bayangan lembut */
            overflow: hidden;
            transition: transform 0.2s;
        }

        /* Result Box - Tetap Gradient tapi lebih fresh */
        .result-box {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        /* Hiasan background bulat samar */
        .result-box::before {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 150px; height: 150px; background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .ipk-score { font-size: 4.5rem; font-weight: 800; line-height: 1; letter-spacing: -2px; margin: 10px 0; }
        .ipk-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; opacity: 0.9; }

        /* Input Table */
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
        .table-custom th { 
            color: var(--text-muted); 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            padding: 0 10px 5px 10px; 
            font-weight: 700; 
            letter-spacing: 0.5px;
        }
        
        .table-custom tr td { 
            background: #fff; 
            padding: 5px; 
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        
        /* Rounded corners for rows */
        .table-custom tr td:first-child { border-left: 1px solid var(--border-color); border-top-left-radius: 12px; border-bottom-left-radius: 12px; padding-left: 15px; }
        .table-custom tr td:last-child { border-right: 1px solid var(--border-color); border-top-right-radius: 12px; border-bottom-right-radius: 12px; padding-right: 15px; }
        
        .table-custom tr:hover td { background: #f8fafc; }

        /* Form Inputs yang Bersih */
        .form-control, .form-select {
            background: transparent; 
            border: none; 
            color: var(--text-main); 
            box-shadow: none; 
            font-weight: 600;
            padding: 10px 0;
        }
        
        .form-control::placeholder { color: #cbd5e1; font-weight: 400; }
        
        .form-control:focus, .form-select:focus { 
            background: transparent; 
            box-shadow: none;
            border-bottom: 2px solid var(--accent); 
            border-radius: 0; 
        }
        
        /* Button Add */
        .btn-add { 
            border: 2px dashed #cbd5e1; 
            color: var(--text-muted); 
            width: 100%; 
            padding: 15px; 
            border-radius: 16px; 
            transition: 0.3s; 
            background: #f8fafc; 
            font-weight: 600; 
            margin-top: 15px;
        }
        .btn-add:hover { 
            border-color: var(--accent); 
            color: var(--accent); 
            background: #eef2ff; 
        }

        /* Delete Button */
        .btn-del { 
            color: #ef4444; 
            background: #fee2e2; 
            width: 32px; height: 32px; 
            display: flex; align-items: center; justify-content: center; 
            border-radius: 50%; 
            transition: 0.2s; border: none; 
        }
        .btn-del:hover { background: #ef4444; color: white; transform: scale(1.1); }

        /* Progress Bar */
        .progress-container { 
            height: 10px; 
            background: rgba(255,255,255,0.2); 
            border-radius: 20px; 
            margin-top: 20px; 
            overflow: hidden; 
        }
        .progress-bar { 
            height: 100%; 
            background: #fff; 
            border-radius: 20px; 
            transition: width 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); /* Efek membal saat animasi */
        }
        
        /* Statistic Items */
        .stat-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .text-dark-muted { color: #475569; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-graduation-cap me-2"></i>PSIK 23 D
            </a>
            <a href="features.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold">
                <i class="fas fa-arrow-left me-2"></i> Menu Fitur
            </a>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="row justify-content-center gx-5">
            
            <div class="col-lg-4 mb-4 order-lg-2">
                <div class="calculator-card position-sticky" style="top: 100px; z-index: 10;">
                    <div class="result-box">
                        <div class="ipk-label">Estimasi IP Semester</div>
                        <div class="ipk-score" id="totalIPS">0.00</div>
                        
                        <div class="progress-container">
                            <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                        </div>
                        
                        <div class="mt-3 badge bg-white bg-opacity-25 text-white fw-normal px-3 py-2 rounded-pill" id="statusText">
                            <i class="fas fa-info-circle me-1"></i> Mulai input nilaimu
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="text-muted small fw-bold text-uppercase">Total SKS</div>
                                    <div class="fs-4 fw-bolder text-dark" id="totalSKS">0</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="text-muted small fw-bold text-uppercase">Bobot</div>
                                    <div class="fs-4 fw-bolder text-dark" id="totalBobot">0</div>
                                </div>
                            </div>
                        </div>
                        
                        <button onclick="resetData()" class="btn btn-light text-danger w-100 rounded-pill fw-bold py-2" style="background: #fee2e2;">
                            <i class="fas fa-trash-alt me-2"></i> Reset Semua
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 order-lg-1">
                <div class="calculator-card p-4 p-md-5">
                    <div class="d-flex align-items-center mb-1">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                            <i class="fas fa-list-ul fa-lg"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Daftar Mata Kuliah</h4>
                    </div>
                    <p class="text-muted small ms-5 mb-4">Inputkan mata kuliah yang sedang kamu ambil semester ini.</p>

                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Mata Kuliah</th>
                                <th style="width: 20%; text-align: center;">SKS</th>
                                <th style="width: 25%;">Target Nilai</th>
                                <th style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody id="courseRows">
                            </tbody>
                    </table>

                    <button onclick="addRow()" class="btn-add">
                        <i class="fas fa-plus-circle me-2"></i> Tambah Mata Kuliah Baru
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        // LOGIC TIDAK DIUBAH SAMA SEKALI DARI KODE SEBELUMNYA //
        
        // Konfigurasi Nilai (Standard UNIMED/Umum)
        const gradePoints = {
            'A': 4.00, 'A-': 3.75, 'B+': 3.50, 'B': 3.00, 
            'B-': 2.75, 'C+': 2.50, 'C': 2.00, 'D': 1.00, 'E': 0.00
        };

        let courses = [];

        // Load data saat halaman dibuka
        document.addEventListener('DOMContentLoaded', () => {
            const savedData = localStorage.getItem('psik_ipk_data');
            if (savedData) {
                courses = JSON.parse(savedData);
            } else {
                // Default 3 baris kosong jika belum ada data
                courses = [
                    { name: '', sks: 2, grade: 'A' },
                    { name: '', sks: 3, grade: 'A' },
                    { name: '', sks: 2, grade: 'B+' }
                ];
            }
            renderRows();
            calculate();
        });

        function addRow() {
            courses.push({ name: '', sks: 2, grade: 'A' });
            renderRows();
            calculate();
        }

        function removeRow(index) {
            courses.splice(index, 1);
            renderRows();
            calculate();
        }

        function updateCourse(index, field, value) {
            courses[index][field] = value;
            calculate(); // Hitung ulang setiap ada perubahan
            saveData();
        }

        function saveData() {
            localStorage.setItem('psik_ipk_data', JSON.stringify(courses));
        }

        function resetData() {
            if(confirm("Yakin mau hapus semua data?")) {
                localStorage.removeItem('psik_ipk_data');
                courses = [{ name: '', sks: 2, grade: 'A' }];
                renderRows();
                calculate();
            }
        }

        function calculate() {
            let totalSKS = 0;
            let totalQualityPoints = 0;

            courses.forEach(c => {
                const sks = parseInt(c.sks) || 0;
                const point = gradePoints[c.grade] || 0;
                
                totalSKS += sks;
                totalQualityPoints += (sks * point);
            });

            const ips = totalSKS > 0 ? (totalQualityPoints / totalSKS).toFixed(2) : "0.00";

            // Update UI
            document.getElementById('totalSKS').textContent = totalSKS;
            document.getElementById('totalBobot').textContent = totalQualityPoints.toFixed(2);
            
            // Animasi Angka
            animateValue("totalIPS", parseFloat(document.getElementById('totalIPS').textContent), parseFloat(ips), 500);

            // Update Status & Bar
            const bar = document.getElementById('progressBar');
            const status = document.getElementById('statusText');
            
            // Persentase Bar (Max 4.00)
            const percent = (ips / 4.00) * 100;
            bar.style.width = percent + "%";

            // Warna & Pesan
            if (ips >= 3.51) {
                bar.style.backgroundColor = "#fff"; // White bar on colored card looks clean
                status.innerHTML = "🔥 Cum Laude! Pertahankan bro!";
                status.className = "mt-3 badge bg-success bg-opacity-25 text-white fw-normal px-3 py-2 rounded-pill";
                document.querySelector('.result-box').style.background = "linear-gradient(135deg, #10b981 0%, #059669 100%)"; // Green Theme
            } else if (ips >= 3.00) {
                bar.style.backgroundColor = "#fff";
                status.innerHTML = "✨ Not bad! Semangat dikit lagi.";
                status.className = "mt-3 badge bg-primary bg-opacity-25 text-white fw-normal px-3 py-2 rounded-pill";
                document.querySelector('.result-box').style.background = "linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%)"; // Blue Theme
            } else if (ips >= 2.00) {
                bar.style.backgroundColor = "#fff";
                status.innerHTML = "⚠️ Warning! Ayo kejar lagi.";
                status.className = "mt-3 badge bg-warning bg-opacity-25 text-white fw-normal px-3 py-2 rounded-pill";
                document.querySelector('.result-box').style.background = "linear-gradient(135deg, #f59e0b 0%, #d97706 100%)"; // Yellow Theme
            } else {
                bar.style.backgroundColor = "#fff";
                status.innerHTML = "🚑 Bahaya! Harus remedial nih.";
                status.className = "mt-3 badge bg-danger bg-opacity-25 text-white fw-normal px-3 py-2 rounded-pill";
                document.querySelector('.result-box').style.background = "linear-gradient(135deg, #ef4444 0%, #dc2626 100%)"; // Red Theme
            }
        }

        function renderRows() {
            const tbody = document.getElementById('courseRows');
            tbody.innerHTML = '';

            courses.forEach((course, index) => {
                let gradeOptions = '';
                for (const [grade, point] of Object.entries(gradePoints)) {
                    const selected = course.grade === grade ? 'selected' : '';
                    gradeOptions += `<option value="${grade}" ${selected}>${grade} (${point})</option>`;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <input type="text" class="form-control" placeholder="Contoh: Pemrograman Web" 
                            value="${course.name}" oninput="updateCourse(${index}, 'name', this.value)">
                    </td>
                    <td>
                        <select class="form-select text-center" onchange="updateCourse(${index}, 'sks', this.value)">
                            <option value="1" ${course.sks == 1 ? 'selected' : ''}>1</option>
                            <option value="2" ${course.sks == 2 ? 'selected' : ''}>2</option>
                            <option value="3" ${course.sks == 3 ? 'selected' : ''}>3</option>
                            <option value="4" ${course.sks == 4 ? 'selected' : ''}>4</option>
                            <option value="6" ${course.sks == 6 ? 'selected' : ''}>6</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select fw-bold text-primary" onchange="updateCourse(${index}, 'grade', this.value)">
                            ${gradeOptions}
                        </select>
                    </td>
                    <td class="text-center">
                        <button class="btn-del mx-auto" onclick="removeRow(${index})"><i class="fas fa-times"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function animateValue(id, start, end, duration) {
            if (start === end) return;
            const obj = document.getElementById(id);
            const range = end - start;
            let current = start;
            const increment = end > start ? 0.01 : -0.01;
            const stepTime = Math.abs(Math.floor(duration / (range / 0.01)));
            
            const timer = setInterval(function() {
                current += increment;
                if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                    current = end;
                    clearInterval(timer);
                }
                obj.textContent = current.toFixed(2);
            }, stepTime);
        }
    </script>

</body>
</html>