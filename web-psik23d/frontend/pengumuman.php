<?php include '../backend/data_pengumuman.php'; ?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengumuman - Admin Premium</title>
    
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
        /* --- THEME SYSTEM (Rose Accent) --- */
        :root {
            --bg-body: #fff1f2; /* Rose Tint Light */
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border-color: #e2e8f0;
            --accent-color: #e11d48; /* Rose 600 */
        }

        [data-bs-theme="dark"] {
            --bg-body: #020617;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-color: #334155;
            --accent-color: #fb7185; /* Rose 400 */
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); transition: background 0.3s; }
        
        .premium-card { 
            background: var(--bg-card); 
            border: 1px solid var(--border-color); 
            border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.02); 
            overflow: hidden; 
        }

        /* Badge Styles */
        .badge-soft { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px; }
        .badge-urgent { background: rgba(225, 29, 72, 0.1); color: #e11d48; border: 1px solid rgba(225, 29, 72, 0.2); }
        .badge-info { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border: 1px solid rgba(14, 165, 233, 0.2); }
        .badge-event { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }

        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="navbar border-bottom sticky-top" style="background: var(--bg-card); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold" style="color: var(--accent-color);" href="admin_dashboard.php">
                <i class="fas fa-arrow-left me-2"></i> Dashboard
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold d-none d-md-block">Papan Pengumuman</span>
                <button class="btn btn-outline-secondary btn-sm rounded-circle" style="width:35px;height:35px;" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container py-5 animate-up">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">📢 Kelola Pengumuman</h3>
                <p class="text-sub mb-0">Informasi penting untuk mahasiswa PSIK 23 D.</p>
            </div>
            <a href="tambah_pengumuman.php" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold" style="background-color: var(--accent-color); border: none;">
                <i class="fas fa-bullhorn me-2"></i> Buat Baru
            </a>
        </div>

        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-main);">
                    <thead style="background: var(--bg-body);">
                        <tr>
                            <th class="py-3 ps-4">Judul & Isi</th>
                            <th>Kategori</th>
                            <th>Tanggal Posting</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($semua_pengumuman) && !empty($semua_pengumuman)): ?>
                            <?php foreach($semua_pengumuman as $p): ?>
                            <tr>
                                <td class="ps-4" style="max-width: 350px;">
                                    <div class="fw-bold mb-1 fs-6"><?= $p['title'] ?></div>
                                    <p class="text-sub small mb-0 text-truncate" style="max-width: 300px;">
                                        <?= substr(strip_tags($p['content']), 0, 80) ?>...
                                    </p>
                                </td>
                                <td>
                                    <?php 
                                        $badgeClass = 'badge-soft badge-info';
                                        $icon = 'fa-info-circle';
                                        if($p['type'] == 'urgent') { $badgeClass = 'badge-soft badge-urgent'; $icon = 'fa-exclamation-triangle'; }
                                        if($p['type'] == 'event') { $badgeClass = 'badge-soft badge-event'; $icon = 'fa-calendar-check'; }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <i class="fas <?= $icon ?> me-1"></i> <?= strtoupper($p['type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center text-sub small">
                                        <i class="far fa-clock me-2"></i>
                                        <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="../backend/proses_hapus_pengumuman.php?id=<?= $p['id'] ?>" 
                                       class="btn btn-sm btn-light text-danger border" 
                                       onclick="return confirm('Hapus pengumuman ini secara permanen?')"
                                       title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-sub mb-2"><i class="fas fa-bullhorn fa-3x opacity-25"></i></div>
                                    <p class="text-sub fw-bold">Belum ada pengumuman dibuat.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
            updateIcons(next);
        }

        function updateIcons(theme) {
            const icon = document.getElementById('themeIcon');
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
        
        updateIcons(localStorage.getItem('theme') || 'light');
    </script>

</body>
</html>