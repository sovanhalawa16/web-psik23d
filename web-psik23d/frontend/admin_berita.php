<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$query = mysqli_query($koneksi, "SELECT posts.*, users.nama as penulis, users.role 
                                 FROM posts JOIN users ON posts.id_user = users.id 
                                 ORDER BY posts.tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - Admin</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bs-body-bg); transition: background 0.3s; }
        .premium-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            background: var(--bs-body-bg);
        }
        .table-card img { object-fit: cover; border-radius: 8px; }
        .badge-soft-info { background: rgba(13, 202, 240, 0.15); color: #0dcaf0; }
        .badge-soft-success { background: rgba(25, 135, 84, 0.15); color: #198754; }
        .badge-soft-warning { background: rgba(255, 193, 7, 0.15); color: #ffc107; }
        .badge-soft-primary { background: rgba(13, 110, 253, 0.15); color: #0d6efd; }
        
        /* Animasi */
        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg border-bottom sticky-top bg-body" style="backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin_dashboard.php"><i class="fas fa-arrow-left me-2"></i> Kembali</a>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold d-none d-md-block">Manajemen Berita</span>
                <button class="btn btn-outline-secondary btn-sm rounded-circle" style="width:35px;height:35px;" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container py-5 animate-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Daftar Postingan</h3>
                <p class="text-secondary mb-0">Kelola semua berita, pengumuman, dan artikel.</p>
            </div>
            <a href="tambah_berita.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                <i class="fas fa-plus me-2"></i> Buat Baru
            </a>
        </div>

        <div class="card premium-card overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-card">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4">Info</th>
                                <th>Kategori</th>
                                <th>Penulis</th>
                                <th>Tanggal</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query) > 0): ?>
                                <?php while($p = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/img/berita/<?= $p['gambar'] ?>" width="60" height="45" class="me-3">
                                            <div>
                                                <a href="detail_berita.php?id=<?= $p['id'] ?>" target="_blank" class="text-decoration-none fw-bold text-body">
                                                    <?= substr($p['judul'], 0, 40) ?>...
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $k = $p['kategori'];
                                            $cls = 'primary';
                                            if($k=='info')$cls='info'; if($k=='magang')$cls='success'; if($k=='kkn')$cls='warning';
                                        ?>
                                        <span class="badge badge-soft-<?= $cls ?> px-3 py-2 rounded-pill"><?= strtoupper($k) ?></span>
                                    </td>
                                    <td>
                                        <div class="small fw-bold"><?= $p['penulis'] ?></div>
                                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem;"><?= $p['role'] ?></div>
                                    </td>
                                    <td class="text-secondary small"><?= date('d M Y', strtotime($p['tanggal'])) ?></td>
                                    <td class="text-end pe-4">
                                        <a href="edit_berita.php?id=<?= $p['id'] ?>" class="btn btn-light btn-sm text-warning"><i class="fas fa-pen"></i></a>
                                        <a href="../backend/proses_hapus_berita.php?id=<?= $p['id'] ?>&gambar=<?= $p['gambar'] ?>" 
                                           class="btn btn-light btn-sm text-danger" onclick="return confirm('Hapus permanen?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-bs-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            updateIcon(next);
        }
        function updateIcon(theme) {
            const icon = document.getElementById('themeIcon');
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
        updateIcon(localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>