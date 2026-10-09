<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

$q_banner = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengumuman - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: sans-serif; }
        .premium-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .banner-preview { width: 100%; height: 150px; object-fit: cover; border-radius: 12px; border: 2px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="jadwal.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
                    <h4 class="fw-bold mb-0 text-primary">Banner Pengumuman</h4>
                </div>

                <!-- FORM UPLOAD -->
                <div class="premium-card p-4 mb-4">
                    <form action="../backend/proses_pengumuman.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="tambah">
                        <label class="form-label fw-bold small text-muted">UPLOAD BANNER BARU (Bisa Lebih Dari 1 Sekaligus)</label>
                        <div class="input-group">
                            <input type="file" name="foto[]" class="form-control" accept="image/*" multiple required>
                            <button class="btn btn-primary fw-bold px-4" type="submit"><i class="fas fa-upload me-2"></i>Upload</button>
                        </div>
                        <div class="form-text mt-2 small">Format ideal: Landscape (Misal 16:9 atau 1000x400px).</div>
                    </form>
                </div>

                <!-- LIST BANNER AKTIF -->
                <div class="premium-card p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-images me-2 text-primary"></i>Banner Sedang Aktif</h6>
                    <div class="row g-3">
                        <?php if(mysqli_num_rows($q_banner) == 0): ?>
                            <div class="col-12 text-center text-muted fst-italic py-4">Belum ada banner pengumuman.</div>
                        <?php else: ?>
                            <?php while($b = mysqli_fetch_assoc($q_banner)): ?>
                            <div class="col-md-6">
                                <div class="position-relative">
                                    <img src="../assets/img/pengumuman/<?= $b['foto'] ?>" class="banner-preview shadow-sm">
                                    <a href="../backend/proses_pengumuman.php?action=hapus&id=<?= $b['id'] ?>" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle shadow" onclick="return confirm('Hapus banner ini?')" style="width: 30px; height: 30px; display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>