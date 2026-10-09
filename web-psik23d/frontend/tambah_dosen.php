<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dosen - Admin Premium</title>
    
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --bg-body: #f1f5f9; --bg-card: #ffffff; --text-main: #1e293b; --text-sub: #64748b; --border-color: #e2e8f0; }
        [data-bs-theme="dark"] { --bg-body: #020617; --bg-card: #1e293b; --text-main: #f8fafc; --text-sub: #94a3b8; --border-color: #334155; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); transition: background 0.3s; }
        .premium-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .form-control, .form-select { background-color: var(--bg-body); border-color: var(--border-color); color: var(--text-main); padding: 0.7rem 1rem; border-radius: 8px; }
        .form-control:focus, .form-select:focus { background-color: var(--bg-card); border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); color: var(--text-main); }
        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="container py-5 animate-up">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="dosen.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
                    <h4 class="fw-bold mb-0 text-success">Tambah Dosen Baru</h4>
                </div>

                <div class="premium-card p-4 p-md-5">
                    <form action="../backend/proses_tambah_dosen.php" method="POST" enctype="multipart/form-data">
                        
                        <h6 class="text-uppercase fw-bold text-sub mb-3 small letter-spacing-1">Informasi Dasar</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nama Lengkap & Gelar</label>
                                <input type="text" name="nama" class="form-control" required placeholder="Cth: Dr. Budi Santoso, M.Kom">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">NIP / NIDN</label>
                                <input type="text" name="nidn" class="form-control" placeholder="Nomor Induk Dosen">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Jabatan Akademik</label>
                                <input type="text" name="jabatan" class="form-control" placeholder="Cth: Lektor Kepala">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Bidang Keahlian</label>
                                <input type="text" name="bidang_ahli" class="form-control" placeholder="Cth: AI & Data Science">
                            </div>
                        </div>

                        <h6 class="text-uppercase fw-bold text-sub mb-3 small letter-spacing-1">Profil Akademik</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Mata Kuliah Utama</label>
                                <input type="text" name="mata_kuliah" class="form-control" placeholder="Cth: Algoritma Pemrograman">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-warning">Link Sinta Score</label>
                                <input type="url" name="link_sinta" class="form-control" placeholder="https://sinta...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-primary">Link Google Scholar</label>
                                <input type="url" name="link_scholar" class="form-control" placeholder="https://scholar...">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Foto Profil</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <div class="form-text text-sub">Format: JPG/PNG, Rasio 1:1 disarankan.</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success py-3 fw-bold rounded-3 shadow-sm">
                                <i class="fas fa-save me-2"></i> Simpan Data Dosen
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>