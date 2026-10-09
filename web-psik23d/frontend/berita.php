<?php 
include '../koneksi.php'; 

// LOGIKA PENCARIAN & FILTER
$where = "WHERE 1=1"; // Default ambil semua
if(isset($_GET['kategori']) && $_GET['kategori'] != "") {
    $kat = $_GET['kategori'];
    $where .= " AND kategori = '$kat'";
}
if(isset($_GET['cari'])) {
    $cari = $_GET['cari'];
    $where .= " AND judul LIKE '%$cari%'";
}

// QUERY DATA + JOIN USER (Biar nama penulis muncul)
$query = "SELECT posts.*, users.nama as penulis, users.foto as foto_penulis 
          FROM posts 
          JOIN users ON posts.id_user = users.id 
          $where 
          ORDER BY posts.tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita & Kegiatan Kelas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-berita { border: none; border-radius: 16px; transition: 0.3s; overflow: hidden; background: white; }
        .card-berita:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        .thumb-img { height: 220px; object-fit: cover; width: 100%; }
        .badge-cat { font-size: 0.75rem; font-weight: 600; padding: 6px 12px; border-radius: 30px; letter-spacing: 0.5px; }
        
        /* Warna Badge Kategori */
        .bg-info-soft { background: #e0f2fe; color: #0284c7; }
        .bg-magang { background: #dcfce7; color: #16a34a; }
        .bg-kkn { background: #fef9c3; color: #ca8a04; }
        .bg-prestasi { background: #fee2e2; color: #dc2626; }
        .bg-project { background: #f3e8ff; color: #9333ea; }

        .search-box { background: white; border-radius: 50px; padding: 5px 5px 5px 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-search { border: none; outline: none; box-shadow: none; }
        .form-search:focus { box-shadow: none; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-5 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php"><i class="fas fa-arrow-left me-2"></i> Kembali ke Home</a>
        </div>
    </nav>

    <div class="container pb-5">
        
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">📰 Kabar Kelas PSIK 23 D</h2>
            <p class="text-muted mb-4">Informasi akademik, cerita magang, hingga dokumentasi kegiatan seru.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <form action="" method="GET">
                        <div class="search-box d-flex align-items-center">
                            <i class="fas fa-search text-muted"></i>
                            <input type="text" name="cari" class="form-control form-search" placeholder="Cari berita apa hari ini..." value="<?= $_GET['cari'] ?? '' ?>">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cari</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 d-flex flex-wrap justify-content-center gap-2">
                <a href="berita.php" class="btn btn-sm <?= !isset($_GET['kategori']) ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill px-3">Semua</a>
                <a href="?kategori=info" class="btn btn-sm <?= ($_GET['kategori']??'')=='info' ? 'btn-info text-white' : 'btn-outline-info' ?> rounded-pill px-3">Info</a>
                <a href="?kategori=magang" class="btn btn-sm <?= ($_GET['kategori']??'')=='magang' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3">Magang</a>
                <a href="?kategori=kkn" class="btn btn-sm <?= ($_GET['kategori']??'')=='kkn' ? 'btn-warning' : 'btn-outline-warning' ?> rounded-pill px-3">KKN</a>
                <a href="?kategori=project" class="btn btn-sm <?= ($_GET['kategori']??'')=='project' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">Project</a>
            </div>
        </div>

        <div class="row g-4">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($p = mysqli_fetch_assoc($result)): ?>
                    
                    <?php 
                        $cat_class = 'bg-secondary text-white';
                        if($p['kategori'] == 'info') $cat_class = 'bg-info-soft';
                        if($p['kategori'] == 'magang') $cat_class = 'bg-magang';
                        if($p['kategori'] == 'kkn') $cat_class = 'bg-kkn';
                        if($p['kategori'] == 'prestasi') $cat_class = 'bg-prestasi';
                        if($p['kategori'] == 'project') $cat_class = 'bg-project';
                        
                        // Foto Penulis Default
                        $foto_penulis = !empty($p['foto_penulis']) ? $p['foto_penulis'] : 'default.png';
                    ?>

                    <div class="col-md-4">
                        <div class="card card-berita h-100 shadow-sm">
                            <div class="position-relative">
                                <img src="../assets/img/berita/<?= $p['gambar'] ?>" class="thumb-img" alt="Thumbnail">
                                <span class="badge position-absolute top-0 start-0 m-3 <?= $cat_class ?> badge-cat shadow-sm">
                                    <?= strtoupper($p['kategori']) ?>
                                </span>
                            </div>
                            
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-2 text-muted small">
                                    <i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($p['tanggal'])) ?>
                                </div>
                                
                                <h5 class="card-title fw-bold mb-3">
                                    <a href="detail_berita.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark stretched-link">
                                        <?= $p['judul'] ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text text-muted small mb-4">
                                    <?= substr(strip_tags($p['isi']), 0, 100) ?>...
                                </p>

                                <div class="mt-auto d-flex align-items-center border-top pt-3">
                                    <img src="../assets/img/mahasiswa/<?= $foto_penulis ?>" class="rounded-circle me-2" width="35" height="35" style="object-fit: cover;">
                                    <small class="fw-bold text-secondary"><?= $p['penulis'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-3 opacity-25">
                    <h5 class="text-muted">Belum ada berita di kategori ini.</h5>
                    <a href="berita.php" class="btn btn-outline-primary btn-sm mt-2">Reset Filter</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p class="mb-0 small">© 2025 PSIK 23 D - Official Class News</p>
    </footer>

</body>
</html>