<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

if($_SESSION['role'] != 'admin'){ exit("Akses Ditolak"); }

// --- 1. AUTO CREATE TABLE (Biar lu ga repot ke phpMyAdmin) ---
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS playlist_jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    file_lagu VARCHAR(255) NOT NULL,
    urutan INT NOT NULL
)");
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS pengaturan_jadwal (
    nama_setting VARCHAR(50) PRIMARY KEY,
    nilai VARCHAR(255) NOT NULL
)");
mysqli_query($koneksi, "INSERT IGNORE INTO pengaturan_jadwal (nama_setting, nilai) VALUES ('autoplay_musik', '0')");

// --- 2. LOGIKA CRUD & SAKLAR AUTOPLAY ---
$pesan = '';

// Toggle Autoplay
if(isset($_POST['toggle_autoplay'])){
    $nilai_baru = $_POST['autoplay_status'] == '1' ? '0' : '1';
    mysqli_query($koneksi, "UPDATE pengaturan_jadwal SET nilai='$nilai_baru' WHERE nama_setting='autoplay_musik'");
    header("Location: kelola_lagu.php"); exit;
}

// Tambah Lagu
if(isset($_POST['tambah_lagu'])){
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    
    // Upload File MP3
    $nama_file = $_FILES['file_lagu']['name'];
    $tmp_file = $_FILES['file_lagu']['tmp_name'];
    $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    
    if($ext == 'mp3' || $ext == 'wav' || $ext == 'ogg'){
        $nama_file_baru = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $nama_file);
        $path = '../assets/music/' . $nama_file_baru;
        
        if(move_uploaded_file($tmp_file, $path)){
            // Cari urutan terakhir
            $q_max = mysqli_query($koneksi, "SELECT MAX(urutan) AS max_urutan FROM playlist_jadwal");
            $max = mysqli_fetch_assoc($q_max)['max_urutan'] ?? 0;
            $urutan_baru = $max + 1;
            
            mysqli_query($koneksi, "INSERT INTO playlist_jadwal (judul, file_lagu, urutan) VALUES ('$judul', '$nama_file_baru', '$urutan_baru')");
            $pesan = '<div class="alert alert-success">Lagu berhasil ditambahkan!</div>';
        } else {
            $pesan = '<div class="alert alert-danger">Gagal upload file. Pastikan folder assets/music/ ada dan writable.</div>';
        }
    } else {
        $pesan = '<div class="alert alert-danger">Format file harus MP3, WAV, atau OGG!</div>';
    }
}

// Hapus Lagu
if(isset($_GET['hapus'])){
    $id_hapus = (int)$_GET['hapus'];
    $q_file = mysqli_query($koneksi, "SELECT file_lagu FROM playlist_jadwal WHERE id='$id_hapus'");
    if($r_file = mysqli_fetch_assoc($q_file)){
        $file_path = '../assets/music/' . $r_file['file_lagu'];
        if(file_exists($file_path)) unlink($file_path); // Hapus file fisik
        mysqli_query($koneksi, "DELETE FROM playlist_jadwal WHERE id='$id_hapus'");
    }
    header("Location: kelola_lagu.php"); exit;
}

// Geser Urutan (Naik/Turun)
if(isset($_GET['geser']) && isset($_GET['id'])){
    $arah = $_GET['geser'];
    $id = (int)$_GET['id'];
    
    $q_current = mysqli_query($koneksi, "SELECT urutan FROM playlist_jadwal WHERE id='$id'");
    $current = mysqli_fetch_assoc($q_current);
    
    if($current){
        $urutan_sekarang = $current['urutan'];
        if($arah == 'naik'){
            $q_target = mysqli_query($koneksi, "SELECT id, urutan FROM playlist_jadwal WHERE urutan < $urutan_sekarang ORDER BY urutan DESC LIMIT 1");
        } else {
            $q_target = mysqli_query($koneksi, "SELECT id, urutan FROM playlist_jadwal WHERE urutan > $urutan_sekarang ORDER BY urutan ASC LIMIT 1");
        }
        
        $target = mysqli_fetch_assoc($q_target);
        if($target){
            // Tukar urutan
            $id_target = $target['id'];
            $urutan_target = $target['urutan'];
            mysqli_query($koneksi, "UPDATE playlist_jadwal SET urutan='$urutan_target' WHERE id='$id'");
            mysqli_query($koneksi, "UPDATE playlist_jadwal SET urutan='$urutan_sekarang' WHERE id='$id_target'");
        }
    }
    header("Location: kelola_lagu.php"); exit;
}

// Ambil Data
$q_lagu = mysqli_query($koneksi, "SELECT * FROM playlist_jadwal ORDER BY urutan ASC");
$q_auto = mysqli_query($koneksi, "SELECT nilai FROM pengaturan_jadwal WHERE nama_setting='autoplay_musik'");
$autoplay_aktif = mysqli_fetch_assoc($q_auto)['nilai'] == '1';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Playlist Musik - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .card-premium { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.03); padding: 25px; margin-bottom: 25px; }
        .btn-naik { color: #10b981; background: #dcfce7; border:none; padding: 5px 10px; border-radius: 8px; transition: 0.3s;}
        .btn-naik:hover { background: #10b981; color: white; }
        .btn-turun { color: #ef4444; background: #fee2e2; border:none; padding: 5px 10px; border-radius: 8px; transition: 0.3s;}
        .btn-turun:hover { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bolder m-0"><i class="fas fa-music text-primary me-2"></i>Kelola Playlist Musik</h3>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary rounded-pill fw-bold"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
        </div>

        <?= $pesan ?>

        <div class="card-premium d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bolder mb-1">Auto-Play Musik Jadwal</h5>
                <p class="text-muted small m-0">Jika aktif (ON), musik akan otomatis berputar saat mahasiswa membuka jadwal kelas.</p>
            </div>
            <form method="POST" class="m-0">
                <input type="hidden" name="autoplay_status" value="<?= $autoplay_aktif ? '1' : '0' ?>">
                <?php if($autoplay_aktif): ?>
                    <button type="submit" name="toggle_autoplay" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm"><i class="fas fa-volume-up me-2"></i>Autoplay: ON</button>
                <?php else: ?>
                    <button type="submit" name="toggle_autoplay" class="btn btn-secondary fw-bold rounded-pill px-4 shadow-sm"><i class="fas fa-volume-mute me-2"></i>Autoplay: OFF</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-premium">
                    <h5 class="fw-bolder mb-3">Tambah Lagu Baru</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Judul Lagu / Penyanyi</label>
                            <input type="text" name="judul" class="form-control rounded-3" placeholder="Contoh: Hindia - Evaluasi" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small">File MP3</label>
                            <input type="file" name="file_lagu" class="form-control rounded-3" accept=".mp3, .wav, .ogg" required>
                        </div>
                        <button type="submit" name="tambah_lagu" class="btn btn-primary w-100 fw-bold rounded-pill shadow-sm"><i class="fas fa-upload me-2"></i>Upload Lagu</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card-premium">
                    <h5 class="fw-bolder mb-3">Daftar Urutan Playlist</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Urutan</th>
                                    <th>Judul Lagu</th>
                                    <th class="text-center">Geser</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $total_lagu = mysqli_num_rows($q_lagu);
                                while($l = mysqli_fetch_assoc($q_lagu)): 
                                ?>
                                <tr>
                                    <td class="fw-bold text-muted">#<?= $no ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($l['judul']) ?></td>
                                    <td class="text-center">
                                        <?php if($no > 1): ?>
                                            <a href="kelola_lagu.php?geser=naik&id=<?= $l['id'] ?>" class="btn-naik me-1" title="Geser ke Atas"><i class="fas fa-arrow-up"></i></a>
                                        <?php endif; ?>
                                        <?php if($no < $total_lagu): ?>
                                            <a href="kelola_lagu.php?geser=turun&id=<?= $l['id'] ?>" class="btn-turun" title="Geser ke Bawah"><i class="fas fa-arrow-down"></i></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="kelola_lagu.php?hapus=<?= $l['id'] ?>" class="btn btn-sm btn-outline-danger rounded-3 fw-bold" onclick="return confirm('Yakin mau hapus lagu ini?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php $no++; endwhile; ?>
                                <?php if($total_lagu == 0): ?>
                                    <tr><td colspan="4" class="text-center text-muted fst-italic py-4">Belum ada lagu. Yuk upload!</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>