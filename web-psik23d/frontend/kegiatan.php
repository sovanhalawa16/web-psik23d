<?php include '../backend/cek_session.php'; include '../backend/data_kegiatan.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Galeri Kegiatan</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4"><div class="container"><a class="navbar-brand" href="admin_dashboard.php">Admin Panel</a><a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Kembali</a></div></nav>
    
    <div class="container">
        <div class="d-flex justify-content-between mb-4">
            <h3>📸 Galeri Kegiatan (Coverflow)</h3>
            <a href="tambah_kegiatan.php" class="btn btn-primary">+ Upload Foto Kegiatan</a>
        </div>
        <div class="row">
            <?php foreach($semua_kegiatan as $k): ?>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <img src="../assets/img/kegiatan/<?= $k['foto'] ?>" class="card-img-top" style="height:150px; object-fit:cover;">
                    <div class="card-body p-2"><a href="../backend/proses_hapus_kegiatan.php?id=<?= $k['id'] ?>&foto=<?= $k['foto'] ?>" class="btn btn-danger btn-sm w-100" onclick="return confirm('Hapus?')">Hapus</a></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>