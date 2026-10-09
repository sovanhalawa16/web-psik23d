<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$query = mysqli_query($koneksi, "SELECT * FROM tugas ORDER BY deadline ASC");
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tugas - Admin</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.3s; }
        .premium-card { border-radius: 16px; border: 1px solid var(--bs-border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="navbar border-bottom sticky-top bg-body" style="backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin_dashboard.php"><i class="fas fa-arrow-left me-2"></i> Dashboard</a>
            <button class="btn btn-outline-secondary btn-sm rounded-circle" style="width:35px;height:35px;" onclick="toggleTheme()"><i class="fas fa-moon" id="themeIcon"></i></button>
        </div>
    </nav>

    <div class="container py-5 animate-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Daftar Tugas & Deadline</h3>
                <p class="text-secondary mb-0">Pantau tenggat waktu tugas mahasiswa.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTugas">
                <i class="fas fa-plus me-2"></i> Tugas Baru
            </button>
        </div>

        <div class="card premium-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Mata Kuliah & Judul</th>
                            <th>Deadline</th>
                            <th>Status Waktu</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query) > 0): ?>
                            <?php while($t = mysqli_fetch_assoc($query)): 
                                $deadline = strtotime($t['deadline']);
                                $now = time();
                                $diff = $deadline - $now;
                                $days = floor($diff / (60 * 60 * 24));
                                
                                if($diff < 0) { $badge = "<span class='badge bg-danger bg-opacity-10 text-danger px-3 py-2'>Expired</span>"; } 
                                elseif($days == 0) { $badge = "<span class='badge bg-warning bg-opacity-10 text-warning px-3 py-2'>Hari Ini!</span>"; } 
                                else { $badge = "<span class='badge bg-success bg-opacity-10 text-success px-3 py-2'>$days Hari Lagi</span>"; }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= $t['mata_kuliah'] ?></div>
                                    <div class="text-secondary small"><?= $t['judul'] ?></div>
                                    <?php if($t['link_pengumpulan']): ?>
                                        <a href="<?= $t['link_pengumpulan'] ?>" target="_blank" class="small text-primary text-decoration-none"><i class="fas fa-external-link-alt me-1"></i> Link Kumpul</a>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y, H:i', $deadline) ?> WIB</td>
                                <td><?= $badge ?></td>
                                <td class="text-end pe-4">
                                    <a href="../backend/proses_hapus_tugas.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted">Tidak ada tugas aktif.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTugas" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-4">
                    <form action="../backend/proses_tambah_tugas.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">MATA KULIAH</label>
                            <input type="text" name="mata_kuliah" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">JUDUL TUGAS</label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">DEADLINE</label>
                            <input type="datetime-local" name="deadline" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">LINK PENGUMPULAN (Opsional)</label>
                            <input type="url" name="link" class="form-control" placeholder="https://...">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3">Simpan Tugas</button>
                    </form>
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
            document.getElementById('themeIcon').className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
        document.getElementById('themeIcon').className = (localStorage.getItem('theme') || 'light') === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    </script>
</body>
</html>