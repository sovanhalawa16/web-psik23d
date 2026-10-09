<?php 
include '../koneksi.php'; 

// Cek ID Berita
if(!isset($_GET['id'])) { header("location:berita.php"); exit; }
$id = $_GET['id'];

// Ambil Data Berita + Penulis
$query = "SELECT posts.*, users.nama as penulis, users.foto as foto_penulis 
          FROM posts 
          JOIN users ON posts.id_user = users.id 
          WHERE posts.id = '$id'";
$result = mysqli_query($koneksi, $query);
$d = mysqli_fetch_assoc($result);

// Kalau berita gak ketemu
if(!$d) { echo "Berita tidak ditemukan!"; exit; }

// Tambah View Counter (Opsional, biar keren)
mysqli_query($koneksi, "UPDATE posts SET views = views + 1 WHERE id='$id'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $d['judul'] ?> - PSIK 23 D</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .hero-detail { height: 400px; width: 100%; object-fit: cover; border-radius: 20px; }
        .content-body { font-size: 1.1rem; line-height: 1.8; color: #334155; }
        .author-box { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <nav class="navbar navbar-light bg-white border-bottom shadow-sm fixed-top">
        <div class="container">
            <a class="btn btn-light rounded-pill fw-bold text-secondary" href="berita.php">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Berita
            </a>
            <span class="navbar-brand fw-bold fs-6">Kabar PSIK 23 D</span>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="row justify-content-center">
            
            <div class="col-lg-8">
                <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill"><?= strtoupper($d['kategori']) ?></span>
                <h1 class="fw-bold mb-3"><?= $d['judul'] ?></h1>
                
                <div class="d-flex align-items-center mb-4 text-muted small">
                    <span class="me-3"><i class="far fa-calendar me-2"></i> <?= date('d F Y', strtotime($d['tanggal'])) ?></span>
                    <span class="me-3"><i class="far fa-eye me-2"></i> <?= $d['views'] ?> Dilihat</span>
                </div>

                <img src="../assets/img/berita/<?= $d['gambar'] ?>" class="hero-detail shadow mb-5" alt="Cover Berita">

                <div class="content-body bg-white p-4 p-md-5 rounded-4 shadow-sm mb-5">
                    <?= nl2br($d['isi']) ?>
                </div>

                <div class="author-box d-flex align-items-center mb-5">
                    <?php $foto_penulis = !empty($d['foto_penulis']) ? $d['foto_penulis'] : 'default.png'; ?>
                    <img src="../assets/img/mahasiswa/<?= $foto_penulis ?>" class="rounded-circle me-3" width="60" height="60" style="object-fit: cover;">
                    <div>
                        <small class="text-muted d-block">Ditulis oleh:</small>
                        <h6 class="fw-bold mb-0"><?= $d['penulis'] ?></h6>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <h5 class="fw-bold mb-4">Berita Terbaru Lainnya</h5>
                <?php 
                    $q_lain = mysqli_query($koneksi, "SELECT * FROM posts WHERE id != '$id' ORDER BY tanggal DESC LIMIT 4");
                    while($l = mysqli_fetch_assoc($q_lain)):
                ?>
                <div class="card border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-4">
                            <img src="../assets/img/berita/<?= $l['gambar'] ?>" class="img-fluid h-100 object-fit-cover" style="min-height: 80px;">
                        </div>
                        <div class="col-8">
                            <div class="card-body py-2 pe-2 ps-3">
                                <h6 class="card-title fw-bold mb-1 small">
                                    <a href="detail_berita.php?id=<?= $l['id'] ?>" class="text-decoration-none text-dark"><?= $l['judul'] ?></a>
                                </h6>
                                <small class="text-muted" style="font-size: 0.7rem;"><?= date('d M Y', strtotime($l['tanggal'])) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        </div>
    </div>

    <footer class="bg-white text-center py-4 mt-5 border-top">
        <p class="mb-0 small text-muted">© 2025 PSIK 23 D</p>
    </footer>

</body>
</html>