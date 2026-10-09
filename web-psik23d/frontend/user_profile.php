<?php
include '../koneksi.php';

// Tangkap ID dari URL
$id_user = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

// Validasi ID
if(empty($id_user)) {
    header("location:../index.php");
    exit;
}

// 1. AMBIL DATA UTAMA USER
$q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id_user'");
$user = mysqli_fetch_assoc($q_user);

if(!$user) {
    echo "<div style='text-align:center; padding:50px;'><h3>User tidak ditemukan!</h3><a href='../index.php'>Kembali</a></div>";
    exit;
}

// 2. AMBIL DATA PENDUKUNG
$q_sg = mysqli_query($koneksi, "SELECT * FROM user_skills_general WHERE user_id='$id_user'");
$q_sc = mysqli_query($koneksi, "SELECT * FROM user_skills_coding WHERE user_id='$id_user'");
$q_sch = mysqli_query($koneksi, "SELECT * FROM user_scholar WHERE user_id='$id_user'");
$scholar = mysqli_fetch_assoc($q_sch);
$q_pro = mysqli_query($koneksi, "SELECT * FROM user_projects WHERE user_id='$id_user' ORDER BY id DESC");
$q_time = mysqli_query($koneksi, "SELECT * FROM user_timeline WHERE user_id='$id_user' ORDER BY tahun_mulai DESC");

// Query untuk project gallery
$q_gal = mysqli_query($koneksi, "SELECT * FROM project_gallery WHERE project_id IN (SELECT id FROM user_projects WHERE user_id='$id_user')");
$gallery_data = [];
while($gal = mysqli_fetch_assoc($q_gal)) {
    if(!isset($gallery_data[$gal['project_id']])) {
        $gallery_data[$gal['project_id']] = [];
    }
    $gallery_data[$gal['project_id']][] = $gal;
}

// Path foto profil & Cover
$foto_profil = (!empty($user['foto']) && $user['foto'] != 'default.png') 
                ? "../assets/img/mahasiswa/" . $user['foto'] 
                : "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=0d6efd&color=fff&size=200";

$cover_photo = (!empty($user['cover_photo']) && $user['cover_photo'] != 'default_cover.jpg')
                ? "../assets/img/cover/" . $user['cover_photo']
                : "https://source.unsplash.com/1600x400/?technology,code,abstract,university";

// FUNGSI BANTUAN WARNA & ICON
function getRandomColor($index) {
    $colors = ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'];
    return $colors[$index % count($colors)];
}

function getSoftSkillIcon($name) {
    $name = strtolower($name);
    if (strpos($name, 'team') !== false || strpos($name, 'kerjasama') !== false) return 'fa-users';
    if (strpos($name, 'lead') !== false || strpos($name, 'pimpin') !== false) return 'fa-crown';
    if (strpos($name, 'time') !== false || strpos($name, 'waktu') !== false) return 'fa-clock';
    if (strpos($name, 'public') !== false || strpos($name, 'bicara') !== false || strpos($name, 'komunikasi') !== false) return 'fa-comments';
    if (strpos($name, 'problem') !== false || strpos($name, 'masalah') !== false) return 'fa-puzzle-piece';
    if (strpos($name, 'creative') !== false || strpos($name, 'kreatif') !== false) return 'fa-lightbulb';
    return 'fa-check-circle'; // Default icon
}

function getLangColor($name) {
    $name = strtolower($name);
    $map = [
        'php' => '#777BB4', 'javascript' => '#F7DF1E', 'js' => '#F7DF1E', 
        'html' => '#E34F26', 'css' => '#1572B6', 'python' => '#3776AB', 
        'java' => '#007396', 'c++' => '#00599C', 'mysql' => '#4479A1', 
        'react' => '#61DAFB', 'flutter' => '#02569B', 'laravel' => '#FF2D20'
    ];
    return isset($map[$name]) ? $map[$name] : '#6c757d';
}

// FUNGSI WARNA & ICON UNTUK TIMELINE
function getTimelineIcon($kategori, $judul) {
    $judul_lower = strtolower($judul);
    $kategori = strtolower($kategori);
    
    // Cek berdasarkan judul/teks dulu
    if (strpos($judul_lower, 'sd') !== false || strpos($judul_lower, 'sekolah dasar') !== false) {
        return ['fas fa-school', '#dc3545']; // Merah
    }
    if (strpos($judul_lower, 'smp') !== false || strpos($judul_lower, 'sekolah menengah pertama') !== false) {
        return ['fas fa-school', '#0d6efd']; // Biru
    }
    if (strpos($judul_lower, 'sma') !== false || strpos($judul_lower, 'smk') !== false || 
    strpos($judul_lower, 'sekolah menengah') !== false || strpos($judul_lower, 'madrasah') !== false) {
    return ['fas fa-school', '#4A90E2']; // Biru Muda Kemendikbud - School icon
}
    if (strpos($judul_lower, 'kuliah') !== false || strpos($judul_lower, 'universitas') !== false || 
        strpos($judul_lower, 'mahasiswa') !== false || strpos($judul_lower, 'perguruan tinggi') !== false) {
        return ['fas fa-university', '#6610f2']; // Ungu
    }
    
    // Kalo ga ketemu, pakai kategori
    switch($kategori) {
        case 'pendidikan':
            return ['fas fa-graduation-cap', '#0d6efd']; // Biru
        case 'organisasi':
            return ['fas fa-users', '#fd7e14']; // Orange
        case 'pekerjaan':
            return ['fas fa-briefcase', '#6c757d']; // Abu-abu
        default:
            return ['fas fa-calendar-alt', '#6f42c1']; // Ungu
    }
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Lengkap - <?= htmlspecialchars($user['nama']) ?> | PSIK 23 D</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/devicons/devicon@v2.15.1/devicon.min.css">

    <style>
        :root {
            --primary: #0d6efd;
            --body-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        [data-bs-theme="dark"] {
            --body-bg: #0f172a;
            --card-bg: #1e293b;
            --border-color: #334155;
            --shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        body {
            background: var(--body-bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* FLEXBOX FOOTER FIX */
        .main-wrapper {
            flex: 1 0 auto;
        }

        /* HEADER & HERO */
        .profile-header {
            position: relative;
            /* DESKTOP: Jarak bawah besar */
            margin-bottom: 180px; 
        }

        .cover-photo {
            height: 350px;
            width: 100%;
            object-fit: cover;
            border-radius: 0 0 50% 50% / 40px;
            mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
        }

        .profile-avatar-wrapper {
            position: absolute;
            /* DESKTOP: Geser ke bawah */
            bottom: -160px; 
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 100%;
            z-index: 10;
        }

        .profile-avatar {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 5px solid var(--card-bg);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            object-fit: cover;
            background: var(--card-bg);
            margin-bottom: 15px;
        }

        /* NAVIGATION PILLS */
        .nav-pills .nav-link {
            border-radius: 50px;
            padding: 10px 25px;
            margin: 0 5px;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            transition: all 0.3s;
        }
        
        .nav-pills .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        /* CARDS */
        .card-custom {
            border: none;
            border-radius: 20px;
            background: var(--card-bg);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            height: 100%;
            transition: transform 0.3s ease;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        /* TABLE SCHOLAR */
        .scholar-table th {
            font-weight: 600;
            color: #64748b;
            border-bottom: 2px solid var(--border-color);
        }
        .scholar-table td {
            vertical-align: middle;
            padding: 15px 10px;
            font-size: 1.05rem;
        }
		
        /* ICON COLORS FOR PROFILE SECTION */
.fa-user { color: #4285F4 !important; } /* Blue - Google Blue */
.fa-id-card { color: #34A853 !important; } /* Green - Google Green */
.fa-mars { color: #4285F4 !important; } /* Blue - Male */
.fa-venus { color: #E91E63 !important; } /* Pink - Female */
.fa-envelope { color: #EA4335 !important; } /* Red - Google Red */
.fa-cake-candles { color: #FBBC05 !important; } /* Yellow - Google Yellow */
.fa-user-check { color: #0DCAF0 !important; } /* Cyan - Bootstrap Info */

/* Social Media Icons Colors */
.social-icon {
    transition: transform 0.3s ease;
}
.social-icon:hover {
    transform: scale(1.1);
}

/* Social Platform Colors */
.fa-instagram { color: #E1306C !important; }
.fa-linkedin { color: #0077B5 !important; }
.fa-github { color: #333333 !important; }
.fa-facebook { color: #1877F2 !important; }
.fa-x-twitter { color: #000000 !important; }
.fa-threads { color: #000000 !important; }
.fa-globe { color: #4F46E5 !important; }
        /* SKILLS */
        .skill-icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .progress {
            height: 10px;
            border-radius: 10px;
            background-color: var(--border-color);
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }
        
        /* Animation Shimmer for Progress Bar */
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: translateX(-100%);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }

        /* CAROUSEL STYLES */
        .carousel-indicators [data-bs-target] {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin: 0 4px;
            background-color: rgba(255,255,255,0.5);
        }

        .carousel-indicators .active {
            background-color: white;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 40px;
            height: 40px;
            background: rgba(0,0,0,0.3);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
            background: rgba(0,0,0,0.5);
        }

        /* TIMELINE STYLES */
        .timeline-item {
            padding-left: 35px;
            position: relative;
            border-left: 3px solid var(--border-color);
            padding-bottom: 30px;
            margin-left: 20px;
        }

        .timeline-item:last-child {
            border-left: 3px solid transparent;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -11px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary);
            border: 4px solid var(--card-bg);
            box-shadow: 0 0 0 3px var(--border-color);
            z-index: 1;
        }

        /* FOOTER */
        footer {
            margin-top: auto; /* Pushes footer to bottom */
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .cover-photo { height: 250px; }
            .profile-avatar { width: 140px; height: 140px; }
            
            /* MOBILE FIX: SAYA TAMBAH JARAKNYA AGAR MAKIN TURUN */
            .profile-avatar-wrapper { bottom: -130px; } 
            
            /* SAYA TAMBAH JARAK HEADER BIAR TULISAN GA KETUTUP */
            .profile-header { margin-bottom: 160px; }
            
            .carousel-control-prev,
            .carousel-control-next {
                width: 30px;
                height: 30px;
            }
            
            .timeline-item {
                margin-left: 10px;
                padding-left: 25px;
            }
            
            .timeline-item::before {
                width: 16px;
                height: 16px;
                left: -9px;
            }
        }
/* Timeline Icon Wrapper */
.icon-wrapper {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.2rem;
}
    </style>
</head>
<body>

<div class="main-wrapper">
    <nav class="navbar navbar-expand-lg fixed-top px-4 py-3" style="background: rgba(255,255,255,0.8); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); z-index: 1000;">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="../index.php">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="toggleTheme()">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
        </div>
    </nav>

    <div class="profile-header">
        <img src="<?= $cover_photo ?>" class="cover-photo">
        
        <div class="profile-avatar-wrapper">
            <img src="<?= $foto_profil ?>" class="profile-avatar">
            
            <div class="px-3">
                <h2 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user['nama']) ?></h2>
                <?php if(!empty($user['motto'])): ?>
                    <p class="text-muted fst-italic mb-0">"<?= htmlspecialchars($user['motto']) ?>"</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mb-4">
        <ul class="nav nav-pills justify-content-center" id="profileTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="about-tab" data-bs-toggle="pill" data-bs-target="#about" type="button">
                    Tentang Saya
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="skills-tab" data-bs-toggle="pill" data-bs-target="#skills" type="button">
                    Keahlian
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="projects-tab" data-bs-toggle="pill" data-bs-target="#projects" type="button">
                    Portofolio
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="timeline-tab" data-bs-toggle="pill" data-bs-target="#timeline" type="button">
                    Riwayat
                </button>
            </li>
            <?php if($scholar): ?>
            <li class="nav-item">
                <button class="nav-link" id="scholar-tab" data-bs-toggle="pill" data-bs-target="#scholar" type="button">
                    Scholar
                </button>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="container pb-5">
        <div class="tab-content" id="profileTabsContent">
            
            <div class="tab-pane fade show active" id="about">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card card-custom p-4 p-md-5 text-center">
                
                <h4 class="fw-bold text-primary mb-4">Profil Pribadi</h4>
                <div class="row g-4 mb-4 text-start justify-content-center">
                    <!-- Nama Lengkap -->
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-3 border rounded-3">
                            <div class="me-3 fs-4">
                                <i class="fas fa-user"></i> <!-- Biru -->
                            </div>
                            <div>
                                <small class="text-muted d-block">Nama Lengkap</small>
                                <span class="fw-bold"><?= htmlspecialchars($user['nama']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- NIM -->
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-3 border rounded-3">
                            <div class="me-3 fs-4">
                                <i class="fas fa-id-card"></i> <!-- Hijau -->
                            </div>
                            <div>
                                <small class="text-muted d-block">NIM</small>
                                <span class="fw-bold"><?= htmlspecialchars($user['nim']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Jenis Kelamin -->
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-3 border rounded-3">
                            <div class="me-3 fs-4">
                                <i class="fas fa-<?= ($user['jenis_kelamin'] ?? '') == 'L' ? 'mars' : 'venus' ?>"></i>
                                <!-- Biru untuk Laki-laki, Pink untuk Perempuan -->
                            </div>
                            <div>
                                <small class="text-muted d-block">Jenis Kelamin</small>
                                <span class="fw-bold">
                                    <?php 
                                    if(isset($user['jenis_kelamin'])) {
                                        echo $user['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan';
                                    } else {
                                        echo 'Belum diisi';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-3 border rounded-3">
                            <div class="me-3 fs-4">
                                <i class="fas fa-envelope"></i> <!-- Merah -->
                            </div>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <span class="fw-bold"><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tempat Tanggal Lahir -->
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-3 border rounded-3">
                            <div class="me-3 fs-4">
                                <i class="fas fa-cake-candles"></i> <!-- Kuning -->
                            </div>
                            <div>
                                <small class="text-muted d-block">Tempat, Tanggal Lahir</small>
                                <span class="fw-bold">
                                    <?= htmlspecialchars($user['tempat_lahir']) ?>, <?= date('d M Y', strtotime($user['tanggal_lahir'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Mahasiswa -->
                    <div class="col-md-10">
                        <div class="d-flex align-items-center p-3 border rounded-3">
                            <div class="me-3 fs-4">
                                <i class="fas fa-user-check"></i> <!-- Cyan -->
                            </div>
                            <div>
                                <small class="text-muted d-block">Status Mahasiswa</small>
                                <span class="fw-bold text-uppercase" style="color: #198754;">
                                    AKTIF
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-5">

                <h4 class="fw-bold text-primary mb-3">Motto Hidup</h4>
                <figure class="text-center mb-0">
                    <blockquote class="blockquote">
                        <p class="fs-5 fst-italic">"<?= htmlspecialchars($user['motto'] ?? 'Belum ada motto') ?>"</p>
                    </blockquote>
                </figure>

                <hr class="my-5">

                <h4 class="fw-bold text-primary mb-4">Social Media</h4>
                <div class="d-flex justify-content-center flex-wrap gap-4">
                    <?php if(!empty($user['instagram'])): ?>
                        <a href="<?= $user['instagram'] ?>" target="_blank" class="text-decoration-none" title="Instagram">
                            <i class="fab fa-instagram fa-3x"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if(!empty($user['linkedin'])): ?>
                        <a href="<?= $user['linkedin'] ?>" target="_blank" class="text-decoration-none" title="LinkedIn">
                            <i class="fab fa-linkedin fa-3x"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if(!empty($user['github'])): ?>
                        <a href="<?= $user['github'] ?>" target="_blank" class="text-decoration-none" title="GitHub">
                            <i class="fab fa-github fa-3x"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if(!empty($user['facebook'])): ?>
                        <a href="<?= $user['facebook'] ?>" target="_blank" class="text-decoration-none" title="Facebook">
                            <i class="fab fa-facebook fa-3x"></i>
                        </a>
                    <?php endif; ?>

                    <?php if(!empty($user['twitter'])): ?>
                        <a href="<?= $user['twitter'] ?>" target="_blank" class="text-decoration-none" title="X (Twitter)">
                            <i class="fa-brands fa-x-twitter fa-3x"></i>
                        </a>
                    <?php endif; ?>

                    <?php if(!empty($user['threads'])): ?>
                        <a href="<?= $user['threads'] ?>" target="_blank" class="text-decoration-none" title="Threads">
                            <i class="fa-brands fa-threads fa-3x"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if(!empty($user['website'])): ?>
                        <a href="<?= $user['website'] ?>" target="_blank" class="text-decoration-none" title="Website">
                            <i class="fas fa-globe fa-3x"></i>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>
            <div class="tab-pane fade" id="skills">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card card-custom p-4 h-100">
                            <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="fas fa-brain text-primary me-2"></i>Soft Skills</h5>
                            <?php 
                            mysqli_data_seek($q_sg, 0);
                            $i = 0;
                            while($sg = mysqli_fetch_assoc($q_sg)): 
                                $color = getRandomColor($i++);
                            ?>
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center">
                                        <i class="fas <?= getSoftSkillIcon($sg['nama_skill']) ?> me-2" style="color: <?= $color ?>"></i>
                                        <span class="fw-semibold"><?= $sg['nama_skill'] ?></span>
                                    </div>
                                    <span style="color: <?= $color ?>; font-weight:bold;"><?= $sg['persentase'] ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $sg['persentase'] ?>%; background-color: <?= $color ?>;"></div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-custom p-4 h-100">
                            <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="fas fa-code text-primary me-2"></i>Programming Skills</h5>
                            <div class="row g-3">
                                <?php 
                                mysqli_data_seek($q_sc, 0);
                                while($sc = mysqli_fetch_assoc($q_sc)): 
                                    $langColor = getLangColor($sc['bahasa']);
                                    // Mapping icon class for devicon or fontawesome fallback
                                    $iconClass = strtolower($sc['bahasa']);
                                    if($iconClass == 'c++') $iconClass = 'cplusplus';
                                    if($iconClass == 'html') $iconClass = 'html5';
                                    if($iconClass == 'css') $iconClass = 'css3';
                                ?>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 border rounded-3 bg-white">
                                        <div class="skill-icon-box me-3" style="background-color: <?= $langColor ?>20;">
                                            <?php if(in_array($iconClass, ['cplusplus', 'html5', 'css3', 'python', 'java', 'php', 'javascript', 'mysql'])): ?>
                                                <i class="devicon-<?= $iconClass ?>-plain" style="color: <?= $langColor ?>"></i>
                                            <?php else: ?>
                                                <i class="fab fa-<?= strtolower($sc['bahasa']) ?>" style="color: <?= $langColor ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="fw-bold"><?= $sc['bahasa'] ?></span>
                                                <span class="fw-bold" style="color: <?= $langColor ?>"><?= $sc['persentase'] ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?= $sc['persentase'] ?>%; background-color: <?= $langColor ?>;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="projects">
                <div class="row g-4">
                    <?php 
                    mysqli_data_seek($q_pro, 0);
                    if(mysqli_num_rows($q_pro) > 0): 
                        while($p = mysqli_fetch_assoc($q_pro)): 
                            $pid = $p['id'];
                            $gallery = $gallery_data[$pid] ?? [];
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card card-custom overflow-hidden h-100">
                                <div class="position-relative overflow-hidden" style="height: 200px;">
                                    <?php if(count($gallery) > 1): ?>
                                        <div id="carouselProject<?= $pid ?>" class="carousel slide h-100" data-bs-ride="carousel">
                                            <div class="carousel-inner h-100">
                                                <?php foreach($gallery as $index => $foto): ?>
                                                    <div class="carousel-item h-100 <?= $index == 0 ? 'active' : '' ?>">
                                                        <img src="../assets/img/projects/<?= $foto['foto'] ?>" 
                                                             class="d-block w-100 h-100" 
                                                             style="object-fit: cover;"
                                                             alt="Project <?= htmlspecialchars($p['judul_project']) ?> - Slide <?= $index + 1 ?>">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselProject<?= $pid ?>" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselProject<?= $pid ?>" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                            <div class="carousel-indicators">
                                                <?php foreach($gallery as $index => $foto): ?>
                                                    <button type="button" data-bs-target="#carouselProject<?= $pid ?>" 
                                                            data-bs-slide-to="<?= $index ?>" 
                                                            class="<?= $index == 0 ? 'active' : '' ?>"></button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <img src="../assets/img/projects/<?= $p['gambar_project'] ?>" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;"
                                             alt="<?= $p['judul_project'] ?>">
                                    <?php endif; ?>
                                    
                                    <span class="badge bg-primary bg-opacity-10 text-primary position-absolute top-0 end-0 m-3 px-3 py-2">
                                        <?= $p['kategori'] ?>
                                    </span>
                                </div>
                                
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-2"><?= htmlspecialchars($p['judul_project']) ?></h5>
                                    <p class="text-muted small mb-3" style="min-height: 60px;"><?= htmlspecialchars($p['deskripsi']) ?></p>
                                    <div class="mt-3">
                                        <?php if($p['link_github']): ?>
                                            <a href="<?= $p['link_github'] ?>" target="_blank" class="btn btn-sm btn-dark rounded-pill me-1"><i class="fab fa-github"></i> Code</a>
                                        <?php endif; ?>
                                        <?php if($p['link_demo']): ?>
                                            <a href="<?= $p['link_demo'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-external-link-alt"></i> Demo</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <div class="col-12 text-center py-5">
                            <div class="card card-custom p-5">
                                <i class="fas fa-folder-open fa-4x text-muted mb-4 opacity-25"></i>
                                <h5 class="fw-bold mb-3">Belum Ada Project</h5>
                                <p class="text-muted mb-0">Portofolio project belum ditambahkan</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="timeline" role="tabpanel">
                <?php 
                mysqli_data_seek($q_time, 0);
                if(mysqli_num_rows($q_time) > 0): 
                ?>
                    <div class="row">
                        <div class="col-lg-10 mx-auto">
                            <div class="card card-custom p-4">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h5 class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <i class="fas fa-history text-primary"></i>
                                        <span>Riwayat Pendidikan & Organisasi</span>
                                    </h5>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                        <?php 
                                        $q_time_count = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM user_timeline WHERE user_id='$id_user'");
                                        $count_time = mysqli_fetch_assoc($q_time_count);
                                        echo $count_time['total'] . ' Riwayat';
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="timeline-container">
    <?php while($t = mysqli_fetch_assoc($q_time)): 
        // Ambil icon dan warna dari fungsi
        list($icon, $color) = getTimelineIcon($t['kategori'], $t['judul']);
        
    ?>
        <div class="timeline-item">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-wrapper" style="background-color: <?= $color ?>20; border: 1px solid <?= $color ?>30;">
                                <i class="<?= $icon ?>" style="color: <?= $color ?>"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($t['judul']) ?></h6>
                        <p class="small fw-bold mb-0" style="color: <?= $color ?>"><?= htmlspecialchars($t['sub_judul']) ?></p>
                    </div>
                </div>
                <span class="badge" style="background-color: <?= $color ?>20; color: <?= $color ?>; border: 1px solid <?= $color ?>30;">
                    <?= ucfirst($t['kategori']) ?>
                </span>
            </div>
            
            <div class="ms-5 ps-3">
                <p class="text-muted small mb-2">
                    <i class="far fa-calendar-alt me-1"></i> 
                    <span class="fw-semibold"><?= $t['tahun_mulai'] ?></span> 
                    - 
                    <span class="fw-semibold"><?= $t['tahun_selesai'] ?></span>
                </p>
                
                <?php if(!empty($t['deskripsi'])): ?>
                    <div class="alert alert-light p-3 mt-2" style="border-left: 3px solid <?= $color ?>;">
                        <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($t['deskripsi'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="card card-custom p-5">
                            <i class="fas fa-history fa-4x text-muted mb-4 opacity-25"></i>
                            <h5 class="fw-bold mb-3">Belum Ada Riwayat</h5>
                            <p class="text-muted mb-0">Riwayat pendidikan dan organisasi belum ditambahkan</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($scholar): ?>
            <div class="tab-pane fade" id="scholar">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card card-custom p-4">
                            <div class="d-flex align-items-center mb-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c7/Google_Scholar_logo.svg" width="40" class="me-3">
                                <h4 class="mb-0 fw-bold">Dikutip oleh</h4>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table scholar-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40%;"></th>
                                            <th class="text-end">Semua</th>
                                            <th class="text-end">Sejak 2020</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Kutipan</td>
                                            <td class="text-end fw-bold text-dark"><?= $scholar['jumlah_sitasi'] ?></td>
                                            <td class="text-end fw-bold text-dark"><?= $scholar['jumlah_sitasi'] ?></td> </tr>
                                        <tr>
                                            <td>indeks-h</td>
                                            <td class="text-end fw-bold text-dark"><?= $scholar['h_index'] ?></td>
                                            <td class="text-end fw-bold text-dark"><?= $scholar['h_index'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>indeks-i10</td>
                                            <td class="text-end fw-bold text-dark"><?= $scholar['i10_index'] ?></td>
                                            <td class="text-end fw-bold text-dark"><?= $scholar['i10_index'] ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-center mt-4">
                                <a href="<?= $scholar['link_profil'] ?>" target="_blank" class="btn btn-outline-primary rounded-pill">
                                    Lihat Profil Lengkap <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div> 

<footer class="py-4 text-center">
    <div class="container">
        <p class="mb-0 text-muted small">
            &copy; 2026 <strong>PSIK 23 D</strong> oleh <?= htmlspecialchars($user['nama']) ?>.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleTheme() {
        const html = document.documentElement;
        const current = html.getAttribute('data-bs-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        document.getElementById('themeIcon').className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    // Auto-rotate carousels every 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const carousels = document.querySelectorAll('.carousel');
        carousels.forEach(carousel => {
            carousel.setAttribute('data-bs-interval', '5000');
        });
    });
</script>

</body>
</html>