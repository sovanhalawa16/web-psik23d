<?php
session_start();

// 1. SET WAKTU JAKARTA (Wajib Paling Atas)
date_default_timezone_set('Asia/Jakarta');

include '../koneksi.php';

if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

// ==========================================
// LOGIK BALAS PESAN (UPDATE DATABASE)
// ==========================================
if (isset($_POST['kirim_balasan'])) {
    $id_laporan = mysqli_real_escape_string($koneksi, $_POST['id_laporan']);
    $email_tujuan = mysqli_real_escape_string($koneksi, $_POST['email_tujuan']);
    $isi_balasan = mysqli_real_escape_string($koneksi, $_POST['isi_balasan']);

    // Update isi balasan & ubah status jadi 'Dibalas'
    $query_update = "UPDATE laporan SET balasan='$isi_balasan', status='Dibalas' WHERE id='$id_laporan'";
    
    if (mysqli_query($koneksi, $query_update)) {
        echo "<script>
            alert('✅ Balasan berhasil disimpan! Status laporan diperbarui.');
            window.location='admin_laporan.php';
        </script>";
    } else {
        echo "<script>alert('❌ Gagal menyimpan balasan: " . mysqli_error($koneksi) . "');</script>";
    }
}

// ==========================================
// LOGIK HAPUS LAPORAN
// ==========================================
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM laporan WHERE id='$id'");
    echo "<script>
        alert('🗑️ Data laporan berhasil dihapus.');
        window.location='admin_laporan.php';
    </script>";
}

// AMBIL DATA LAPORAN (Urutkan dari yang terbaru)
$query = mysqli_query($koneksi, "SELECT * FROM laporan ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Laporan</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-table { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .table thead th { 
            background-color: #f8fafc; color: #64748b; 
            font-weight: 700; text-transform: uppercase; 
            font-size: 0.75rem; letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0; 
            padding-top: 15px; padding-bottom: 15px;
        }
        .avatar-initial { 
            width: 40px; height: 40px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: 700; color: white; font-size: 1rem;
        }
        .ticket-badge {
            background: #1e293b; color: #e2e8f0; 
            font-family: monospace; font-size: 0.8rem; 
            padding: 4px 8px; border-radius: 6px;
        }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-inbox text-primary me-2"></i> Laporan Masuk</h3>
            <p class="text-muted small mb-0">Monitor aspirasi dan aduan mahasiswa secara real-time.</p>
        </div>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-table p-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">ID Tiket</th>
                        <th width="25%">Pengirim</th>
                        <th width="10%">Kategori</th>
                        <th width="25%">Isi Pesan</th>
                        <th width="10%">Status</th>
                        <th width="10%" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(mysqli_num_rows($query) > 0):
                        while($row = mysqli_fetch_assoc($query)): 
                            // Warna Avatar Berdasarkan Kategori
                            $initial = strtoupper(substr($row['nama'], 0, 1));
                            $bgAvatar = 'bg-primary'; 
                            if($row['jenis_laporan'] == 'Pengaduan') $bgAvatar = 'bg-danger';
                            if($row['jenis_laporan'] == 'Saran') $bgAvatar = 'bg-success';
                            if($row['jenis_laporan'] == 'Pertanyaan') $bgAvatar = 'bg-info';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <span class="ticket-badge">#<?= htmlspecialchars($row['ticket_id']) ?></span>
                            <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                <?= date('d/m/y H:i', strtotime($row['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-initial <?= $bgAvatar ?> me-3 shadow-sm">
                                    <?= $initial ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if($row['jenis_laporan'] == 'Pertanyaan'): ?>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill">Tanya</span>
                            <?php elseif($row['jenis_laporan'] == 'Saran'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill">Saran</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill">Aduan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px; color: #64748b;">
                                <?= htmlspecialchars($row['deskripsi']) ?>
                            </div>
                        </td>
                        <td>
                            <?php if($row['status'] == 'Pending'): ?>
                                <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                            <?php else: ?>
                                <span class="badge bg-primary rounded-pill px-3"><i class="fas fa-check-circle me-1"></i> Dibalas</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-primary rounded-circle shadow-sm me-1" 
                                    data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id'] ?>" title="Lihat & Balas">
                                <i class="fas fa-reply"></i>
                            </button>
                            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle" 
                               onclick="return confirm('Yakin ingin menghapus laporan ini? Data yang dihapus tidak bisa dikembalikan.')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalDetail<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow-lg">
                                <div class="modal-header bg-light border-0">
                                    <h5 class="modal-title fw-bold">
                                        <i class="fas fa-ticket-alt text-primary me-2"></i> Tiket: <?= $row['ticket_id'] ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    
                                    <div class="d-flex align-items-start mb-4 p-3 bg-light rounded-3 border">
                                        <div class="avatar-initial <?= $bgAvatar ?> me-3" style="width:50px; height:50px; font-size:1.2rem;">
                                            <?= $initial ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($row['nama']) ?></h6>
                                            <small class="text-muted d-block"><?= htmlspecialchars($row['email']) ?></small>
                                            <div class="mt-2">
                                                <span class="badge bg-secondary"><?= $row['jenis_laporan'] ?></span>
                                                <small class="text-muted ms-2"><i class="far fa-calendar me-1"></i> <?= date('d F Y, H:i', strtotime($row['created_at'])) ?> WIB</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="fw-bold text-secondary small text-uppercase mb-2">Isi Pesan:</label>
                                        <div class="p-3 bg-white rounded-3 border border-secondary border-opacity-25 text-dark" style="min-height: 80px; font-size: 0.95rem; line-height: 1.6;">
                                            <?= nl2br(htmlspecialchars($row['deskripsi'])) ?>
                                        </div>
                                    </div>

                                    <hr class="border-secondary border-opacity-10 my-4">

                                    <form method="POST">
                                        <input type="hidden" name="id_laporan" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="email_tujuan" value="<?= $row['email'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="fw-bold text-primary small text-uppercase mb-2">
                                                <i class="fas fa-edit me-1"></i> Balasan Admin:
                                            </label>
                                            <textarea name="isi_balasan" class="form-control border-primary shadow-sm" rows="5" placeholder="Ketik balasan anda di sini..." required><?= $row['balasan'] ?></textarea>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle text-info"></i> Pesan ini akan bisa dilihat user saat mereka mengecek status tiket.
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-secondary" data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" name="kirim_balasan" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                                <i class="fas fa-paper-plane me-2"></i> Kirim & Tandai Selesai
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-3"><i class="fas fa-inbox fa-4x opacity-25"></i></div>
                                <h6 class="fw-bold">Belum ada laporan masuk</h6>
                                <small>Laporan dari mahasiswa akan muncul di sini.</small>
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