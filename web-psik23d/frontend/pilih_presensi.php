<?php
session_start();
include '../koneksi.php'; // Sesuaikan path koneksi

// Ambil daftar Mata Kuliah unik dari absensi_real
$q_matkul = mysqli_query($koneksi, "SELECT DISTINCT nama_mk FROM absensi_real ORDER BY nama_mk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Dokumen - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .card-box { 
            background: white; 
            padding: 35px 30px; 
            border-radius: 28px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            width: 100%; 
            max-width: 500px;
            transition: transform 0.2s;
        }
        .card-box:hover {
            transform: translateY(-5px);
        }
        .icon-pdf { 
            font-size: 3.5rem; 
            color: #ef4444; 
            margin-bottom: 15px; 
        }
        .icon-berita { 
            font-size: 3rem; 
            color: #10b981; 
            margin-bottom: 15px; 
        }
        .btn-pdf { background-color: #dc2626; border: none; transition: 0.2s; }
        .btn-pdf:hover { background-color: #b91c1c; transform: scale(1.02); }
        .btn-berita { background-color: #059669; border: none; transition: 0.2s; }
        .btn-berita:hover { background-color: #047857; transform: scale(1.02); }
        hr { margin: 25px 0; background: #e2e8f0; height: 1px; }
        .form-label { font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .form-select { border-radius: 14px; padding: 12px; border: 1.5px solid #e2e8f0; }
    </style>
</head>
<body>

    <div class="card-box text-center">
        <div class="mb-3">
            <i class="fas fa-print icon-pdf"></i>
            <i class="fas fa-book-open icon-berita ms-3"></i>
        </div>
        <h4 class="fw-bold mb-2">Cetak Dokumen Kelas</h4>
        <p class="text-muted small mb-4">Pilih mata kuliah, lalu klik tombol dokumen yang diinginkan</p>
        
        <form method="GET" class="text-start" id="formCetak">
            <div class="mb-4">
                <label class="form-label">Mata Kuliah</label>
                <select name="matkul" id="matkul" class="form-select" required>
                    <option value="">-- Pilih Mata Kuliah --</option>
                    <?php while($row = mysqli_fetch_assoc($q_matkul)): ?>
                        <option value="<?= htmlspecialchars($row['nama_mk']) ?>"><?= htmlspecialchars($row['nama_mk']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-grid gap-3">
                <button type="button" id="btnPresensi" class="btn btn-pdf btn-lg fw-bold py-2" disabled>
                    <i class="fas fa-table-list me-2"></i> Cetak Rekap Presensi (PDF)
                </button>
                <button type="button" id="btnBerita" class="btn btn-berita btn-lg fw-bold py-2" disabled>
                    <i class="fas fa-book me-2"></i> Cetak Berita Acara Perkuliahan
                </button>
            </div>
        </form>
        <hr>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
        </a>
    </div>

    <script>
        const matkulSelect = document.getElementById('matkul');
        const btnPresensi = document.getElementById('btnPresensi');
        const btnBerita = document.getElementById('btnBerita');

        function updateButtons() {
            const selected = matkulSelect.value;
            if (selected !== '') {
                btnPresensi.disabled = false;
                btnBerita.disabled = false;
            } else {
                btnPresensi.disabled = true;
                btnBerita.disabled = true;
            }
        }

        matkulSelect.addEventListener('change', updateButtons);
        updateButtons();

        btnPresensi.addEventListener('click', function() {
            const matkul = matkulSelect.value;
            if (matkul) {
                window.open('generate_pdf.php?matkul=' + encodeURIComponent(matkul), '_blank');
            }
        });

        btnBerita.addEventListener('click', function() {
            const matkul = matkulSelect.value;
            if (matkul) {
                window.open('berita_acara_perkuliahan.php?matkul=' + encodeURIComponent(matkul), '_blank');
            }
        });
    </script>
</body>
</html>