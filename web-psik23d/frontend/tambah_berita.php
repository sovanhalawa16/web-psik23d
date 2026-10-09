<?php 
session_start(); 

// 1. Cek apakah user sudah login
if(!isset($_SESSION['id'])) { 
    header("location:login.php"); 
    exit; 
} 

// 2. LOGIC TOMBOL KEMBALI OTOMATIS
// Defaultnya ke index, tapi kita cek role-nya
$link_kembali = "index.php"; 

if(isset($_SESSION['role'])) {
    if($_SESSION['role'] == 'admin') {
        // Jika Admin, balik ke kelola berita
        $link_kembali = "admin_berita.php";
    } else {
        // Jika Mahasiswa, balik ke dashboard mahasiswa
        $link_kembali = "user_dashboard.php";
    }
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tulis Berita - Sistem Informasi</title>
    
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
        .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="container py-5 animate-up">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="<?= $link_kembali ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    
                    <h4 class="fw-bold mb-0 text-primary">Post Berita Baru</h4>
                </div>

                <div class="premium-card p-4 p-md-5">
                    
                    <form action="../backend/proses_tambah_berita.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-sub">JUDUL POSTINGAN</label>
                            <input type="text" name="judul" class="form-control fw-bold" placeholder="Tulis judul yang menarik..." required autofocus>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-sub">KATEGORI</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="magang">🎓 Magang & Karier</option>
                                    <option value="kkn">🌲 Kegiatan KKN</option>
                                    <option value="prestasi">🏆 Prestasi & Lomba</option>
                                    <option value="project">💻 Project Showcase</option>
                                    <option value="info">📢 Info Umum</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-sub">GAMBAR SAMPUL (Thumbnail)</label>
                                <input type="file" name="gambar" class="form-control" accept="image/*" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-sub">ISI KONTEN</label>
                            <textarea name="isi" class="form-control" rows="10" placeholder="Ceritakan detail kegiatan atau berita di sini..." required></textarea>
                            <div class="form-text text-sub">Gunakan bahasa yang jelas dan mudah dipahami.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i> Terbitkan Sekarang
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