<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$q = mysqli_query($koneksi, "SELECT * FROM popup_info ORDER BY id DESC LIMIT 1");
$data = mysqli_fetch_assoc($q);

// Default value biar gak error
if(!$data) {
    $data = ['status'=>'nonaktif', 'judul'=>'', 'isi'=>'', 'link_url'=>'', 'link_label'=>'', 'penulis'=>$_SESSION['nama'], 'jenis'=>'info'];
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Popup Info</title>
    
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Desain Card & Form */
        body { background-color: var(--bs-body-bg); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; }
        .sidebar-wrapper { width: 260px; height: 100dvh; background: var(--bs-body-bg); position: fixed; border-right: 1px solid var(--bs-border-color); } 
        /* ... (Anggap CSS Sidebar sama seperti sebelumnya) ... */
        
        .preview-card {
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            min-height: 200px;
        }
        
        .form-check-input:checked { background-color: #10b981; border-color: #10b981; }
        .form-control, .form-select { border-radius: 10px; padding: 10px 15px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="fas fa-bullhorn me-2 text-primary"></i>Kelola Popup</h3>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card p-4">
                    <form action="../backend/proses_popup.php" method="POST">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Status</label>
                                <div class="d-flex gap-2">
                                    <input type="radio" class="btn-check" name="status" id="aktif" value="aktif" <?= $data['status']=='aktif'?'checked':'' ?>>
                                    <label class="btn btn-outline-success w-100 fw-bold" for="aktif"><i class="fas fa-check-circle me-1"></i> ON</label>

                                    <input type="radio" class="btn-check" name="status" id="nonaktif" value="nonaktif" <?= $data['status']=='nonaktif'?'checked':'' ?>>
                                    <label class="btn btn-outline-danger w-100 fw-bold" for="nonaktif"><i class="fas fa-times-circle me-1"></i> OFF</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Jenis Info</label>
                                <select name="jenis" class="form-select">
                                    <option value="info" <?= $data['jenis']=='info'?'selected':'' ?>>ℹ️ Informasi (Biru)</option>
                                    <option value="peringatan" <?= $data['jenis']=='peringatan'?'selected':'' ?>>⚠️ Peringatan (Kuning)</option>
                                    <option value="event" <?= $data['jenis']=='event'?'selected':'' ?>>🎉 Kegiatan/Event (Hijau)</option>
                                    <option value="penting" <?= $data['jenis']=='penting'?'selected':'' ?>>🔥 Sangat Penting (Merah)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Penulis</label>
                                <input type="text" name="penulis" class="form-control bg-light" value="<?= !empty($data['penulis']) ? $data['penulis'] : $_SESSION['nama'] ?>" readonly>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-uppercase text-muted">Judul Popup</label>
                                <input type="text" name="judul" class="form-control fw-bold" value="<?= $data['judul'] ?>" placeholder="Contoh: Pembayaran Uang Kas" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Isi Pesan</label>
                            <textarea name="isi" class="form-control" rows="4" placeholder="Tulis pengumuman di sini..." required><?= $data['isi'] ?></textarea>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <h6 class="fw-bold small text-primary mb-3"><i class="fas fa-link me-2"></i>Tombol Aksi (Opsional)</h6>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" name="link_label" class="form-control form-control-sm" value="<?= $data['link_label'] ?>" placeholder="Label (Cth: Daftar Sekarang)">
                                </div>
                                <div class="col-md-7">
                                    <input type="text" name="link_url" class="form-control form-control-sm" value="<?= $data['link_url'] ?>" placeholder="URL (https://...)">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i> Simpan & Tampilkan Popup
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>