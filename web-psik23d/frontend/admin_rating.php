<?php
session_start();

// SET WAKTU
date_default_timezone_set('Asia/Jakarta');

include '../koneksi.php';

// Cek Session Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// LOGIK HAPUS RATING
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM rating WHERE id='$id'");
    echo "<script>alert('Data rating berhasil dihapus.'); window.location='admin_rating.php';</script>";
}

// AMBIL DATA RATING (Terbaru di atas)
$query = mysqli_query($koneksi, "SELECT * FROM rating ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Data Rating</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-table { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .table thead th { 
            background-color: #f8fafc; color: #64748b; 
            font-weight: 700; text-transform: uppercase; font-size: 0.75rem; 
            border-bottom: 2px solid #e2e8f0; padding: 15px;
        }
        .star-gold { color: #f59e0b; }
        .star-gray { color: #e2e8f0; }
        .avatar-initial { 
            width: 35px; height: 35px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: 700; color: white; background: linear-gradient(135deg, #6366f1, #4f46e5);
        }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-star text-warning me-2"></i> Penilaian User</h3>
            <p class="text-muted small mb-0">Feedback dan rating yang masuk dari website.</p>
        </div>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <?php
        // Hitung rata-rata
        $q_avg = mysqli_query($koneksi, "SELECT AVG(bintang) as rata, COUNT(*) as total FROM rating");
        $data_avg = mysqli_fetch_assoc($q_avg);
        $rata_rata = number_format($data_avg['rata'], 1);
        $total_rating = $data_avg['total'];
    ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center rounded-4">
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3 text-warning">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark"><?= $rata_rata ?> <small class="fs-6 text-muted">/ 5.0</small></h2>
                    <small class="text-secondary">Dari <?= $total_rating ?> Responden</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-table p-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Waktu (WIB)</th>
                        <th width="25%">Nama</th>
                        <th width="15%">Rating</th>
                        <th width="30%">Komentar</th>
                        <th width="5%" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(mysqli_num_rows($query) > 0):
                        while($row = mysqli_fetch_assoc($query)): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div class="small fw-bold text-dark">
                                <?= date('d F Y', strtotime($row['created_at'])) ?>
                            </div>
                            <div class="small text-muted">
                                Pukul <?= date('H:i', strtotime($row['created_at'])) ?> WIB
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-initial me-2">
                                    <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                </div>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($row['nama']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="text-nowrap">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <?php if($i <= $row['bintang']): ?>
                                        <i class="fas fa-star star-gold"></i>
                                    <?php else: ?>
                                        <i class="fas fa-star star-gray"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="ms-1 small fw-bold text-dark">(<?= $row['bintang'] ?>)</span>
                            </div>
                        </td>
                        <td>
                            <?php if(!empty($row['komentar'])): ?>
                                <div class="text-muted small fst-italic">"<?= htmlspecialchars($row['komentar']) ?>"</div>
                            <?php else: ?>
                                <span class="badge bg-light text-secondary border">- Tidak ada komentar -</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-light text-danger rounded-circle border hover-danger" 
                               onclick="return confirm('Yakin ingin menghapus rating ini?')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-star fa-3x mb-3 opacity-25"></i><br>
                                Belum ada penilaian masuk.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>