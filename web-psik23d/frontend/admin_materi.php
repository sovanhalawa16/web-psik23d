<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$query = mysqli_query($koneksi, "SELECT materials.*, users.nama as pengupload 
                                 FROM materials JOIN users ON materials.uploaded_by = users.id 
                                 ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Materi - Admin</title>
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
        .file-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 1.2rem; }
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
                <h3 class="fw-bold mb-1">Bank Materi & Tugas</h3>
                <p class="text-secondary mb-0">Repository file perkuliahan.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalUpload">
                <i class="fas fa-cloud-upload-alt me-2"></i> Upload Baru
            </button>
        </div>

        <div class="card premium-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">File</th>
                            <th>Mata Kuliah</th>
                            <th>Semester</th>
                            <th>Oleh</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query) > 0): ?>
                            <?php while($m = mysqli_fetch_assoc($query)): 
                                $ext = pathinfo($m['file'], PATHINFO_EXTENSION);
                                $icon = 'fa-file'; $bg = 'bg-secondary'; $col = 'text-white';
                                if($ext=='pdf') { $icon='fa-file-pdf'; $bg='bg-danger'; }
                                if(in_array($ext, ['doc','docx'])) { $icon='fa-file-word'; $bg='bg-primary'; }
                                if(in_array($ext, ['ppt','pptx'])) { $icon='fa-file-powerpoint'; $bg='bg-warning'; $col='text-dark'; }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="file-icon <?= $bg ?> <?= $col ?> bg-opacity-10 text-opacity-100">
                                            <i class="fas <?= $icon ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= $m['judul'] ?></div>
                                            <a href="../assets/files/<?= $m['file'] ?>" target="_blank" class="small text-decoration-none">Download File</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold text-secondary"><?= $m['mata_kuliah'] ?></td>
                                <td><span class="badge bg-light text-dark border">Semester <?= $m['semester'] ?></span></td>
                                <td class="small"><?= $m['pengupload'] ?></td>
                                <td class="text-end pe-4">
                                    <a href="../backend/proses_hapus_materi.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada materi diupload.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUpload" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Upload Materi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-4">
                    <form action="../backend/proses_tambah_materi.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">JUDUL MATERI</label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary">MATA KULIAH</label>
                                <input type="text" name="mata_kuliah" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary">SEMESTER</label>
                                <select name="semester" class="form-select">
                                    <?php for($i=1; $i<=8; $i++): ?><option value="<?= $i ?>">Smt <?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">FILE (PDF/DOC/PPT)</label>
                            <input type="file" name="file_materi" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">DESKRIPSI (Opsional)</label>
                            <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3">Upload Sekarang</button>
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