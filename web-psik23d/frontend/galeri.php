<?php 
include '../backend/cek_session.php'; 
include '../backend/data_galeri.php'; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Galeri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">Admin Panel</a>
            <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-images me-2"></i>Galeri Foto Slider</h4>
            <a href="tambah_galeri.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Upload Foto</a>
        </div>

        <div class="row">
            <?php foreach($semua_galeri as $g): ?>
            <div class="col-md-3 col-6 mb-4">
                <div class="card shadow-sm h-100 border-0">
                    <img src="../assets/img/galeri/<?= $g['foto'] ?>" class="card-img-top" 
                         style="height: 150px; object-fit: cover; cursor: pointer;">
                    
                    <div class="card-body p-2 text-center">
                        <small class="text-muted d-block mb-2" style="font-size: 0.7rem">
                            Uploaded: <?= date('d/m/Y', strtotime($g['created_at'])) ?>
                        </small>
                        
                        <a href="../backend/proses_hapus_galeri.php?id=<?= $g['id'] ?>&foto=<?= $g['foto'] ?>" 
                           class="btn btn-danger btn-sm w-100" 
                           onclick="return confirm('Hapus foto ini?')">
                           <i class="fas fa-trash-alt"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if(empty($semua_galeri)): ?>
                <div class="col-12 text-center text-muted py-5 border rounded bg-white">
                    <i class="far fa-image fa-3x mb-3 text-secondary"></i>
                    <p>Belum ada foto yang diupload.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>