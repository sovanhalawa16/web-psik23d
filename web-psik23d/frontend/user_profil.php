<?php
session_start();
include '../koneksi.php';

// Cek Login
if(!isset($_SESSION['id'])) { header("location:../index.php"); exit; }
$id_user = $_SESSION['id'];

// 1. AMBIL DATA USER
$q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id_user'");
$user = mysqli_fetch_assoc($q_user);

// 2. AMBIL DATA PENDUKUNG
$q_sg = mysqli_query($koneksi, "SELECT * FROM user_skills_general WHERE user_id='$id_user'");
$q_sc = mysqli_query($koneksi, "SELECT * FROM user_skills_coding WHERE user_id='$id_user'");
$q_sch = mysqli_query($koneksi, "SELECT * FROM user_scholar WHERE user_id='$id_user'");
$scholar = mysqli_fetch_assoc($q_sch); 
$q_pro = mysqli_query($koneksi, "SELECT * FROM user_projects WHERE user_id='$id_user' ORDER BY id DESC");
$q_time = mysqli_query($koneksi, "SELECT * FROM user_timeline WHERE user_id='$id_user' ORDER BY tahun_mulai DESC");
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Profil Lengkap - <?= $user['nama'] ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #334155; }
        
        /* HERO SECTION */
        .profile-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 40px 0 80px;
            color: white;
            border-radius: 0 0 40px 40px;
            margin-bottom: -50px;
        }
        
        .profile-nav-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 10px;
            border: 1px solid #e2e8f0;
        }

        .nav-pills .nav-link {
            color: #64748b; font-weight: 600; border-radius: 12px; padding: 12px 20px;
            margin: 0 5px; transition: all 0.3s;
        }
        .nav-pills .nav-link:hover { background: #f1f5f9; color: #0f172a; }
        .nav-pills .nav-link.active {
            background: #0d6efd; color: white;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        /* CARD STYLES */
        .card-custom {
            border: none; border-radius: 16px; background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #f1f5f9;
            height: 100%; transition: 0.3s;
        }
        .card-custom:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

        /* SKILL BAR */
        .skill-item { padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 10px; background: #fff; }
        .skill-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #eff6ff; color: #3b82f6; border-radius: 10px; font-size: 1.2rem; }

        /* TIMELINE */
        .timeline-item {
            padding-left: 25px; position: relative; border-left: 2px solid #e2e8f0; padding-bottom: 25px;
        }
        .timeline-item::before {
            content: ''; position: absolute; left: -6px; top: 0; width: 10px; height: 10px;
            border-radius: 50%; background: #3b82f6; border: 2px solid white; box-shadow: 0 0 0 2px #e2e8f0;
        }

        /* PROJECT CARD */
        .project-img { height: 160px; object-fit: cover; border-radius: 12px 12px 0 0; }
        
        /* Floating Add Button */
        .btn-add-float {
            position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px;
            border-radius: 50%; background: #0d6efd; color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; box-shadow: 0 10px 25px rgba(13, 110, 253, 0.4);
            z-index: 99; cursor: pointer; transition: 0.3s; border: none;
        }
        .btn-add-float:hover { transform: scale(1.1); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top px-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="user_dashboard.php"><i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="profile-hero text-center">
        <h2 class="fw-bold mb-1">Kelola Profil Lengkap</h2>
        <p class="text-white-50">Lengkapi portofolio, skill, dan riwayatmu disini.</p>
    </div>

    <div class="container" style="padding-bottom: 100px;">
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="profile-nav-card mb-4">
                    <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-skill"><i class="fas fa-bolt me-2"></i>Keahlian (Skill)</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-project"><i class="fas fa-laptop-code me-2"></i>Portofolio</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-timeline"><i class="fas fa-history me-2"></i>Riwayat</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-scholar"><i class="fas fa-graduation-cap me-2"></i>Scholar</button></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="tab-content" id="pills-tabContent">

            <div class="tab-pane fade show active" id="tab-skill">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card card-custom p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold text-dark"><i class="fas fa-star text-warning me-2"></i>Soft Skills</h5>
                                <button class="btn btn-sm btn-light rounded-pill border fw-bold" data-bs-toggle="modal" data-bs-target="#addGeneralSkill"><i class="fas fa-plus"></i> Tambah</button>
                            </div>
                            <?php if(mysqli_num_rows($q_sg) > 0): while($sg = mysqli_fetch_assoc($q_sg)): ?>
                                <div class="skill-item">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold small"><?= $sg['nama_skill'] ?></span>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark border me-2"><?= $sg['persentase'] ?>%</span>
                                            <a href="../backend/profil_skill.php?act=del_general&id=<?= $sg['id'] ?>" class="text-danger" onclick="return confirm('Hapus skill ini?')"><i class="fas fa-trash-alt"></i></a>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: <?= $sg['persentase'] ?>%"></div>
                                    </div>
                                </div>
                            <?php endwhile; else: echo '<p class="text-muted text-center py-3">Belum ada skill umum.</p>'; endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-custom p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold text-dark"><i class="fas fa-code text-primary me-2"></i>Tech Stack</h5>
                                <button class="btn btn-sm btn-light rounded-pill border fw-bold" data-bs-toggle="modal" data-bs-target="#addCodingSkill"><i class="fas fa-plus"></i> Tambah</button>
                            </div>
                            <div class="row g-2">
                                <?php if(mysqli_num_rows($q_sc) > 0): while($sc = mysqli_fetch_assoc($q_sc)): ?>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center p-2 border rounded-3 bg-white">
                                            <div class="skill-icon me-3"><i class="fab fa-<?= strtolower($sc['bahasa']) ?>"></i></div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold small"><?= $sc['bahasa'] ?></div>
                                                <div class="progress mt-1" style="height: 5px;">
                                                    <div class="progress-bar" style="width: <?= $sc['persentase'] ?>%"></div>
                                                </div>
                                            </div>
                                            <a href="../backend/profil_skill.php?act=del_coding&id=<?= $sc['id'] ?>" class="text-danger ms-3" onclick="return confirm('Hapus?')"><i class="fas fa-times"></i></a>
                                        </div>
                                    </div>
                                <?php endwhile; else: echo '<p class="text-muted text-center py-3 col-12">Belum ada skill coding.</p>'; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-project">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary rounded-pill shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addProject"><i class="fas fa-plus me-2"></i>Tambah Project Baru</button>
                </div>
                
                <div class="row g-4">
                    <?php if(mysqli_num_rows($q_pro) > 0): while($p = mysqli_fetch_assoc($q_pro)): ?>
                    <div class="col-md-4">
                        <div class="card card-custom h-100 p-0 overflow-hidden">
                            <img src="../assets/img/projects/<?= $p['gambar_project'] ?>" class="project-img">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border"><?= $p['kategori'] ?></span>
                                    <a href="../backend/profil_project.php?act=delete&id=<?= $p['id'] ?>" class="text-danger" onclick="return confirm('Hapus Project?')"><i class="fas fa-trash"></i></a>
                                </div>
                                <h6 class="fw-bold mb-2"><?= $p['judul_project'] ?></h6>
                                <p class="text-muted small mb-3"><?= substr($p['deskripsi'], 0, 80) ?>...</p>
                                <div class="d-flex gap-2">
                                    <?php if($p['link_github']): ?>
                                        <a href="<?= $p['link_github'] ?>" target="_blank" class="btn btn-sm btn-dark w-100"><i class="fab fa-github"></i> Code</a>
                                    <?php endif; ?>
                                    <?php if($p['link_demo']): ?>
                                        <a href="<?= $p['link_demo'] ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-external-link-alt"></i> Demo</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; else: echo '<div class="col-12"><div class="alert alert-light text-center border">Belum ada project. Yuk pamerkan karyamu!</div></div>'; endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-timeline">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card card-custom p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold">Riwayat Pendidikan & Organisasi</h5>
                                <button class="btn btn-warning rounded-pill shadow-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addTimeline"><i class="fas fa-plus me-2"></i>Tambah</button>
                            </div>
                            
                            <div class="timeline-container mt-3">
                                <?php if(mysqli_num_rows($q_time) > 0): while($t = mysqli_fetch_assoc($q_time)): ?>
                                <div class="timeline-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="fw-bold mb-1"><?= $t['judul'] ?></h6>
                                        <a href="../backend/profil_timeline.php?act=delete&id=<?= $t['id'] ?>" class="text-danger small" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                    <p class="text-primary small mb-1 fw-bold"><?= $t['sub_judul'] ?></p>
                                    <p class="text-muted small mb-2"><i class="far fa-calendar-alt me-1"></i> <?= $t['tahun_mulai'] ?> - <?= $t['tahun_selesai'] ?></p>
                                    <p class="small text-secondary mb-0"><?= $t['deskripsi'] ?></p>
                                </div>
                                <?php endwhile; else: echo '<p class="text-muted text-center">Belum ada riwayat ditambahkan.</p>'; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-scholar">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card card-custom p-4 border-0 bg-white">
                            <div class="text-center mb-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c7/Google_Scholar_logo.svg" width="80" class="mb-3">
                                <h5 class="fw-bold text-dark">Update Statistik Akademik</h5>
                                <p class="text-muted small">Masukkan data sesuai profil Google Scholar Anda.</p>
                            </div>
                            
                            <form action="../backend/profil_scholar.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Link Profil Scholar</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                                        <input type="text" name="link_profil" class="form-control" value="<?= $scholar['link_profil'] ?? '' ?>" placeholder="https://scholar.google.com/citations?user=...">
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-4">
                                        <label class="form-label small text-muted d-block text-center fw-bold">Citations</label>
                                        <input type="number" name="jumlah_sitasi" class="form-control text-center fw-bold fs-5 border-primary" value="<?= $scholar['jumlah_sitasi'] ?? 0 ?>">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small text-muted d-block text-center fw-bold">h-index</label>
                                        <input type="number" name="h_index" class="form-control text-center fw-bold fs-5 border-primary" value="<?= $scholar['h_index'] ?? 0 ?>">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small text-muted d-block text-center fw-bold">i10-index</label>
                                        <input type="number" name="i10_index" class="form-control text-center fw-bold fs-5 border-primary" value="<?= $scholar['i10_index'] ?? 0 ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill py-2">Simpan Statistik</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="addGeneralSkill" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">Tambah Soft Skill</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form action="../backend/profil_skill.php?act=add_general" method="POST">
                    <div class="modal-body">
                        <div class="mb-3"><label>Nama Skill (Contoh: Public Speaking)</label><input type="text" name="nama_skill" class="form-control" required></div>
                        <div class="mb-3"><label>Tingkat Penguasaan (%)</label><input type="range" name="persentase" class="form-range" min="0" max="100" oninput="this.nextElementSibling.value = this.value"><output>50</output></div>
                    </div>
                    <div class="modal-footer border-0"><button type="submit" class="btn btn-success rounded-pill w-100">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCodingSkill" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">Tambah Bahasa/Framework</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form action="../backend/profil_skill.php?act=add_coding" method="POST">
                    <div class="modal-body">
                        <div class="mb-3"><label>Pilih Bahasa</label>
                            <select name="bahasa" class="form-select">
                                <option value="Python">Python</option><option value="PHP">PHP</option><option value="Java">Java</option>
                                <option value="Js">JavaScript</option><option value="Html5">HTML</option><option value="Css3">CSS</option>
                                <option value="React">React</option><option value="Laravel">Laravel</option><option value="Node">Node.js</option>
                                <option value="Android">Android</option><option value="Swift">Swift</option><option value="Golang">Go</option>
                            </select>
                        </div>
                        <div class="mb-3"><label>Tingkat Penguasaan (%)</label><input type="range" name="persentase" class="form-range" min="0" max="100" oninput="this.nextElementSibling.value = this.value"><output>50</output></div>
                    </div>
                    <div class="modal-footer border-0"><button type="submit" class="btn btn-primary rounded-pill w-100">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addProject" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Upload Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../backend/profil_project.php?act=add" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Project</label>
                            <input type="text" name="judul_project" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kategori</label>
                            <select name="kategori" class="form-select">
                                <option value="Web Dev">Web Dev</option>
                                <option value="Mobile App">Mobile App</option>
                                <option value="AI / ML">AI / ML</option>
                                <option value="UI/UX Design">UI/UX Design</option>
                                <option value="IoT">IoT</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi Singkat</label>
                            <textarea name="deskripsi" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Gambar Project (Bisa pilih banyak)</label>
                            <input type="file" name="gambar_project[]" class="form-control" accept="image/*" multiple required>
                            <small class="text-muted">Tahan tombol CTRL untuk memilih lebih dari 1 foto.</small>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Link Github</label>
                                <input type="text" name="link_github" class="form-control" placeholder="Opsional">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Link Demo</label>
                                <input type="text" name="link_demo" class="form-control" placeholder="Opsional">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary rounded-pill w-100">Upload Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addTimeline" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">Tambah Riwayat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form action="../backend/profil_timeline.php?act=add" method="POST">
                    <div class="modal-body">
                        <div class="mb-3"><label>Kategori</label><select name="kategori" class="form-select"><option value="pendidikan">Pendidikan (Sekolah/Kampus)</option><option value="organisasi">Organisasi</option><option value="pekerjaan">Pekerjaan / Magang</option></select></div>
                        <div class="mb-3"><label>Nama Instansi</label><input type="text" name="judul" class="form-control" placeholder="Cth: SMA Negeri 1 Medan" required></div>
                        <div class="mb-3"><label>Jurusan / Jabatan</label><input type="text" name="sub_judul" class="form-control" placeholder="Cth: Ketua OSIS" required></div>
                        <div class="row">
                            <div class="col-6 mb-3"><label>Tahun Mulai</label><input type="number" name="tahun_mulai" class="form-control" placeholder="2020"></div>
                            <div class="col-6 mb-3"><label>Tahun Selesai</label><input type="text" name="tahun_selesai" class="form-control" placeholder="2024 / Sekarang"></div>
                        </div>
                        <div class="mb-3"><label>Deskripsi (Opsional)</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
                    </div>
                    <div class="modal-footer border-0"><button type="submit" class="btn btn-warning rounded-pill w-100">Simpan Riwayat</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>