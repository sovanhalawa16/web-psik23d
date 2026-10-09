<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

// Cek Role Admin
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

// Ambil Data Dosen
$query = "SELECT * FROM dosen ORDER BY nama ASC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dosen - Admin Premium</title>
    
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
        /* GLOBAL VARIABLES */
        :root {
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-sub: #64748b;
            --border-color: #e2e8f0;
            --accent-color: #10b981; /* Emerald Green for Dosen */
        }

        [data-bs-theme="dark"] {
            --bg-body: #020617;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-color: #334155;
            --accent-color: #34d399;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); transition: background 0.3s; }

        /* COMPONENTS */
        .premium-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow: hidden; }
        
        .img-avatar { width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 2px solid var(--border-color); }
        
        /* MODAL KHUSUS DOSEN */
        .modal-content { background-color: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); }
        .detail-foto-large { 
            width: 140px; height: 140px; object-fit: cover; border-radius: 50%; 
            border: 4px solid var(--accent-color); padding: 3px; background: var(--bg-card);
        }

        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="navbar border-bottom sticky-top" style="background: var(--bg-card); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="admin_dashboard.php"><i class="fas fa-arrow-left me-2"></i> Dashboard</a>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold d-none d-md-block">Data Dosen Pengampu</span>
                <button class="btn btn-outline-secondary btn-sm rounded-circle" style="width:35px;height:35px;" onclick="toggleTheme()"><i class="fas fa-moon" id="themeIcon"></i></button>
            </div>
        </div>
    </nav>

    <div class="container py-5 animate-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Dosen & Staff</h3>
                <p class="text-sub mb-0">Total: <span class="fw-bold text-success"><?= mysqli_num_rows($result) ?></span> Pengajar Aktif</p>
            </div>
            <a href="tambah_dosen.php" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
                <i class="fas fa-plus me-2"></i> Tambah Dosen
            </a>
        </div>

        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-main);">
                    <thead style="background: var(--bg-body);">
                        <tr>
                            <th class="py-3 ps-4">No</th>
                            <th>Foto</th>
                            <th>NIDN</th>
                            <th>Nama Lengkap & Jabatan</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php $no=1; while($d = mysqli_fetch_assoc($result)): 
                                $foto_src = "../assets/img/dosen/" . ($d['foto'] ?: 'default_dosen.png');
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-sub"><?= $no++ ?></td>
                                <td><img src="<?= $foto_src ?>" class="img-avatar"></td>
                                <td class="fw-bold font-monospace text-success"><?= $d['nidn'] ?></td>
                                <td>
                                    <span class="fw-bold"><?= $d['nama'] ?></span> <br>
                                    <span class="small text-sub"><i class="fas fa-briefcase me-1"></i> <?= $d['jabatan'] ?: 'Dosen Pengampu' ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-light text-primary btn-detail"
                                            data-foto="<?= $foto_src ?>"
                                            data-nama="<?= $d['nama'] ?>"
                                            data-nidn="<?= $d['nidn'] ?>"
                                            data-jabatan="<?= $d['jabatan'] ?>"
                                            data-sinta="<?= $d['link_sinta'] ?>"
                                            data-scholar="<?= $d['link_scholar'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="edit_dosen.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-light text-warning"><i class="fas fa-edit"></i></a>
                                        <a href="../backend/proses_hapus_dosen.php?id=<?= $d['id'] ?>&foto=<?= $d['foto'] ?>" 
                                           class="btn btn-sm btn-light text-danger" 
                                           onclick="return confirm('Hapus data dosen ini?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-sub">Belum ada data dosen.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-success"><i class="fas fa-chalkboard-teacher me-2"></i>Profil Pengajar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="btn-close-modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    
                    <img src="" id="d-foto" class="detail-foto-large mb-3">
                    
                    <h4 class="fw-bold mb-1" id="d-nama">Nama Dosen</h4>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill mb-4 px-3" id="d-jabatan">
                        Jabatan
                    </span>

                    <div class="row g-3 justify-content-center mb-4">
                        <div class="col-6 col-md-5">
                            <div class="p-3 rounded-3 border h-100" style="background: var(--bg-body);">
                                <small class="text-sub text-uppercase fw-bold" style="font-size: 0.7rem;">NIDN</small>
                                <div class="fw-bold fs-5 mt-1 font-monospace" id="d-nidn">-</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-sub small fw-bold text-uppercase letter-spacing-1 mb-3">Link Penelitian</h6>
                    <div class="d-flex justify-content-center gap-2 flex-wrap" id="d-links-area">
                        </div>

                </div>
                <div class="modal-footer border-top-0 justify-content-center pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-5" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal Logic
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', function() {
                const d = this.dataset;
                document.getElementById('d-foto').src = d.foto;
                document.getElementById('d-nama').innerText = d.nama;
                document.getElementById('d-nidn').innerText = d.nidn;
                document.getElementById('d-jabatan').innerText = d.jabatan || 'Dosen Pengampu';

                const linksArea = document.getElementById('d-links-area');
                linksArea.innerHTML = ''; 
                
                if(d.sinta) linksArea.innerHTML += `<a href="${d.sinta}" target="_blank" class="btn btn-warning btn-sm fw-bold rounded-pill px-3 shadow-sm"><i class="fas fa-chart-bar me-1"></i> SINTA</a>`;
                if(d.scholar) linksArea.innerHTML += `<a href="${d.scholar}" target="_blank" class="btn btn-primary btn-sm fw-bold rounded-pill px-3 shadow-sm"><i class="fas fa-graduation-cap me-1"></i> Scholar</a>`;
                if(!d.sinta && !d.scholar) linksArea.innerHTML = '<span class="text-sub small fst-italic">Tidak ada link terlampir.</span>';

                modal.show();
            });
        });

        // Theme Toggle
        function toggleTheme() {
            const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            updateIcons(next);
        }
        function updateIcons(theme) {
            document.getElementById('themeIcon').className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            const closeBtn = document.getElementById('btn-close-modal');
            if(theme === 'dark') closeBtn.classList.add('btn-close-white'); else closeBtn.classList.remove('btn-close-white');
        }
        updateIcons(localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>