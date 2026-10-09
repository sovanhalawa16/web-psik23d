<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

// Cek Role Admin
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

// Ambil Data Mahasiswa
$query = "SELECT * FROM users WHERE role='mahasiswa' ORDER BY nama ASC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - Premium Admin</title>
    
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
        /* --- GLOBAL STYLE (Sama dengan Dashboard) --- */
        :root {
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-sub: #64748b;
            --border-color: #e2e8f0;
        }

        [data-bs-theme="dark"] {
            --bg-body: #020617;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-color: #334155;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            transition: background 0.3s;
        }

        /* CARD & TABLE */
        .premium-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            overflow: hidden;
        }

        .img-avatar {
            width: 45px; height: 45px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--border-color);
        }

        /* MODAL STYLES */
        .modal-content {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }
        
        .detail-foto-large {
            width: 140px; height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid var(--border-color);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        /* ANIMATION */
        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="navbar border-bottom sticky-top" style="background: var(--bg-card); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="admin_dashboard.php">
                <i class="fas fa-arrow-left me-2"></i> Dashboard
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold d-none d-md-block">Data Mahasiswa</span>
                <button class="btn btn-outline-secondary btn-sm rounded-circle" style="width:35px;height:35px;" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container py-5 animate-up">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Manajemen Mahasiswa</h3>
                <p class="text-sub mb-0">Total: <span class="fw-bold text-primary"><?= mysqli_num_rows($result) ?></span> Mahasiswa Terdaftar</p>
            </div>
            <a href="tambah_mahasiswa.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                <i class="fas fa-user-plus me-2"></i> Tambah Baru
            </a>
        </div>

        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-main);">
                    <thead style="background: var(--bg-body);">
                        <tr>
                            <th class="py-3 ps-4">No</th>
                            <th>Profil</th>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php $no=1; while($m = mysqli_fetch_assoc($result)): 
                                $foto_src = "../assets/img/mahasiswa/" . ($m['foto'] ?: 'default.png');
                                // Format TTL
                                $ttl = ($m['tempat_lahir'] ? $m['tempat_lahir'] : '-') . ', ' . ($m['tanggal_lahir'] ? date('d M Y', strtotime($m['tanggal_lahir'])) : '-');
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-sub"><?= $no++ ?></td>
                                <td><img src="<?= $foto_src ?>" class="img-avatar"></td>
                                <td class="fw-bold font-monospace"><?= $m['nim'] ?></td>
                                <td>
                                    <span class="fw-bold"><?= $m['nama'] ?></span>
                                    <?php if($m['jabatan'] != 'Anggota'): ?>
                                        <br><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill" style="font-size: 0.65rem;"><?= $m['jabatan'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sub"><?= $m['email'] ?: '<small>-</small>' ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-light text-primary btn-detail"
                                            data-foto="<?= $foto_src ?>"
                                            data-nama="<?= $m['nama'] ?>"
                                            data-nim="<?= $m['nim'] ?>"
                                            data-email="<?= $m['email'] ?: '-' ?>"
                                            data-ttl="<?= $ttl ?>"
                                            data-alamat="<?= $m['alamat_asal'] ?: '-' ?>"
                                            data-skill="<?= $m['skill'] ?: '-' ?>"
                                            data-motto="<?= $m['motto'] ?: '-' ?>"
                                            data-ig="<?= $m['instagram'] ?>"
                                            data-fb="<?= $m['facebook'] ?>"
                                            data-gh="<?= $m['github'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <a href="edit_mahasiswa.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-light text-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="../backend/proses_hapus_mhs.php?id=<?= $m['id'] ?>" 
                                           class="btn btn-sm btn-light text-danger" 
                                           onclick="return confirm('Hapus data <?= $m['nama'] ?> secara permanen?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-sub">Belum ada data mahasiswa.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-id-card me-2"></i>Detail Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="btn-close-modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4 text-center border-end-md" style="border-color: var(--border-color) !important;">
                            <img src="" id="d-foto" class="detail-foto-large mb-3">
                            <h5 class="fw-bold mb-1" id="d-nama">Nama</h5>
                            <p class="text-sub font-monospace mb-3" id="d-nim">NIM</p>
                            
                            <div class="d-flex justify-content-center gap-2 mb-3" id="d-sosmed-area">
                                </div>
                        </div>

                        <div class="col-md-8 ps-md-4">
                            <h6 class="fw-bold text-primary text-uppercase small mb-3 letter-spacing-1">Biodata Diri</h6>
                            <table class="table table-borderless table-sm mb-4" style="color: var(--text-main);">
                                <tr>
                                    <td width="30%" class="text-sub"><i class="fas fa-envelope me-2 w-25"></i>Email</td>
                                    <td class="fw-bold" id="d-email">-</td>
                                </tr>
                                <tr>
                                    <td class="text-sub"><i class="fas fa-calendar-day me-2 w-25"></i>TTL</td>
                                    <td class="fw-bold" id="d-ttl">-</td>
                                </tr>
                                <tr>
                                    <td class="text-sub"><i class="fas fa-map-marker-alt me-2 w-25"></i>Alamat</td>
                                    <td class="fw-bold" id="d-alamat">-</td>
                                </tr>
                            </table>

                            <h6 class="fw-bold text-primary text-uppercase small mb-2 letter-spacing-1">Skill & Keahlian</h6>
                            <div id="d-skill-area" class="mb-4"></div>

                            <h6 class="fw-bold text-primary text-uppercase small mb-2 letter-spacing-1">Motto</h6>
                            <div class="p-3 rounded-3" style="background: var(--bg-body); font-style: italic; color: var(--text-sub);" id="d-motto">
                                -
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- LOGIC MODAL DETAIL ---
        document.addEventListener("DOMContentLoaded", function(){
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            const closeBtn = document.getElementById('btn-close-modal');

            // Handle Theme di Tombol Close Modal (Bootstrap defaultnya hitam)
            function updateModalCloseBtn() {
                const theme = localStorage.getItem('theme');
                if(theme === 'dark') closeBtn.classList.add('btn-close-white');
                else closeBtn.classList.remove('btn-close-white');
            }

            document.querySelectorAll('.btn-detail').forEach(btn => {
                btn.addEventListener('click', function(){
                    // Ambil Data
                    const d = this.dataset;

                    // Isi Modal
                    document.getElementById('d-foto').src = d.foto;
                    document.getElementById('d-nama').innerText = d.nama;
                    document.getElementById('d-nim').innerText = d.nim;
                    document.getElementById('d-email').innerText = d.email;
                    document.getElementById('d-ttl').innerText = d.ttl;
                    document.getElementById('d-alamat').innerText = d.alamat;
                    document.getElementById('d-motto').innerText = '"' + d.motto + '"';

                    // Render Skill (Pecah string koma jadi badge)
                    const skillArea = document.getElementById('d-skill-area');
                    skillArea.innerHTML = '';
                    if(d.skill && d.skill !== '-'){
                        d.skill.split(',').forEach(s => {
                            skillArea.innerHTML += `<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 me-1 mb-1">${s.trim()}</span>`;
                        });
                    } else {
                        skillArea.innerHTML = '<span class="text-sub small">- Tidak ada data -</span>';
                    }

                    // Render Sosmed
                    const sosmedArea = document.getElementById('d-sosmed-area');
                    sosmedArea.innerHTML = '';
                    const btnStyle = "btn btn-sm rounded-circle d-flex align-items-center justify-content-center border";
                    const size = "width:32px; height:32px;";
                    
                    if(d.ig) sosmedArea.innerHTML += `<a href="${d.ig}" target="_blank" class="${btnStyle} btn-outline-danger" style="${size}"><i class="fab fa-instagram"></i></a>`;
                    if(d.fb) sosmedArea.innerHTML += `<a href="${d.fb}" target="_blank" class="${btnStyle} btn-outline-primary" style="${size}"><i class="fab fa-facebook-f"></i></a>`;
                    if(d.gh) sosmedArea.innerHTML += `<a href="${d.gh}" target="_blank" class="${btnStyle} btn-outline-dark" style="${size}"><i class="fab fa-github"></i></a>`;

                    updateModalCloseBtn();
                    modal.show();
                });
            });
        });

        // --- THEME TOGGLE LOGIC ---
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-bs-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            updateIcons(next);
            
            // Update tombol close modal jika sedang terbuka
            const closeBtn = document.getElementById('btn-close-modal');
            if(next === 'dark') closeBtn.classList.add('btn-close-white');
            else closeBtn.classList.remove('btn-close-white');
        }

        function updateIcons(theme) {
            const icon = document.getElementById('themeIcon');
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
        
        // Init Icon
        updateIcons(localStorage.getItem('theme') || 'light');
    </script>

</body>
</html>