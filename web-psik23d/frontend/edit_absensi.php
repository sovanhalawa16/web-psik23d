<?php 
session_start();
include '../koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: tampil_absensi.php");
    exit;
}

// Ambil salah satu record untuk informasi grup
$query_group = mysqli_query($koneksi, "SELECT * FROM absensi_real WHERE id = $id");
$group = mysqli_fetch_assoc($query_group);
if (!$group) {
    header("Location: tampil_absensi.php");
    exit;
}

// Ambil semua data dalam grup yang sama
$nama_mk = $group['nama_mk'];
$kode_mk = $group['kode_mk'];
$pertemuan_ke = $group['pertemuan_ke'];
$semester = $group['semester'];
$blok = $group['blok'];
$waktu_mulai = $group['waktu_mulai'];

$query_mhs = mysqli_query($koneksi, "SELECT * FROM absensi_real 
    WHERE nama_mk = '$nama_mk' 
    AND kode_mk = '$kode_mk' 
    AND pertemuan_ke = $pertemuan_ke 
    AND semester = '$semester' 
    AND blok = '$blok' 
    AND waktu_mulai = '$waktu_mulai' 
    ORDER BY nama_lengkap ASC");

$q_dosen = mysqli_query($koneksi, "SELECT nama FROM dosen ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Absensi - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; margin: 0; padding-bottom: 90px; }
        .header-soft {
            background: linear-gradient(120deg, #6366f1, #8b5cf6);
            padding: 2rem 1rem 1.8rem;
            border-radius: 0 0 2rem 2rem;
            color: white;
            text-align: center;
            box-shadow: 0 8px 20px rgba(99,102,241,0.2);
        }
        .glass-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(2px);
            border-radius: 1.5rem;
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.5);
            padding: 1.8rem;
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 0.4rem;
        }
        .form-control, .form-select {
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            padding: 0.7rem 1rem;
            font-size: 0.9rem;
        }
        .mhs-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f2f5;
        }
        .mhs-foto {
            width: 48px;
            height: 48px;
            border-radius: 30px;
            object-fit: cover;
        }
        .btn-status-group .btn {
            font-size: 0.7rem;
            padding: 5px 10px;
            border-radius: 30px !important;
            margin: 0 2px;
        }
        .btn-hapus-mhs {
            background: #fff1f0;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 30px;
            color: #f97316;
        }
        .sticky-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            border-top: 1px solid #eef2ff;
            padding: 1rem;
            z-index: 100;
        }
    </style>
</head>
<body>

<div class="header-soft">
    <h4 class="fw-bold mb-1"><i class="fas fa-edit me-2"></i>Edit Absensi</h4>
    <p class="opacity-75 small mb-0">Perbaharui data pertemuan & kehadiran</p>
</div>

<div class="container mt-4">
    <form action="../backend/proses_edit_absensi.php" method="POST" id="formEditAbsensi">
        <input type="hidden" name="grup_id" value="<?= $id ?>">
        
        <div class="glass-card">
            <h6 class="fw-bold mb-3"><i class="fas fa-chalkboard-user me-2 text-primary"></i>Informasi Kelas</h6>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Nama Mata Kuliah</label>
                    <input type="text" name="nama_mk" class="form-control" value="<?= htmlspecialchars($group['nama_mk']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kode MK</label>
                    <input type="text" name="kode_mk" class="form-control" value="<?= htmlspecialchars($group['kode_mk']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dosen Pengampu</label>
                    <select name="dosen_pengampu" class="form-select" required>
                        <option value="" disabled>-- Pilih Dosen --</option>
                        <?php while($d = mysqli_fetch_assoc($q_dosen)): ?>
                            <option value="<?= htmlspecialchars($d['nama']) ?>" <?= ($group['dosen_pengampu'] == $d['nama']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Semester</label>
                    <input type="text" name="semester" class="form-control" value="<?= htmlspecialchars($group['semester']) ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Blok</label>
                    <select name="blok" class="form-select">
                        <option value="1" <?= $group['blok'] == '1' ? 'selected' : '' ?>>Blok 1</option>
                        <option value="2" <?= $group['blok'] == '2' ? 'selected' : '' ?>>Blok 2</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">SKS</label>
                    <input type="number" name="sks" class="form-control" value="<?= $group['sks'] ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipe Kelas</label>
                    <input list="tipeList" name="tipe_kelas" class="form-control" value="<?= htmlspecialchars($group['tipe_kelas']) ?>" required>
                    <datalist id="tipeList">
                        <option>Teori</option><option>Praktek</option><option>UTS</option><option>UAS</option>
                        <option>Presentasi MR 2</option><option>Review</option>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pertemuan ke-</label>
                    <input type="number" name="pertemuan_ke" class="form-control" value="<?= $group['pertemuan_ke'] ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Metode Pembelajaran</label>
                    <select name="metode_pembelajaran" id="metode_pembelajaran" class="form-select" required>
                        <option value="offline" <?= $group['metode_pembelajaran'] == 'offline' ? 'selected' : '' ?>>Offline</option>
                        <option value="online" <?= $group['metode_pembelajaran'] == 'online' ? 'selected' : '' ?>>Online</option>
                        <option value="asynchronous" <?= $group['metode_pembelajaran'] == 'asynchronous' ? 'selected' : '' ?>>Asynchronous</option>
                        <option value="kelas batal" <?= $group['metode_pembelajaran'] == 'kelas batal' ? 'selected' : '' ?>>Kelas Batal</option>
                    </select>
                </div>
                <div class="col-md-3" id="divRuangan">
                    <label class="form-label" id="labelRuangan">Ruangan / Link</label>
                    <input type="text" name="ruangan_link" id="ruangan_link" class="form-control" value="<?= htmlspecialchars($group['ruangan_link']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Waktu Mulai</label>
                    <input type="datetime-local" name="waktu_mulai" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($group['waktu_mulai'])) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Waktu Selesai</label>
                    <input type="datetime-local" name="waktu_selesai" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($group['waktu_selesai'])) ?>" required>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <h6 class="fw-bold mb-3"><i class="fas fa-users me-2 text-primary"></i>Status Kehadiran</h6>
            <div id="listMahasiswa">
                <?php while($mhs = mysqli_fetch_assoc($query_mhs)): 
                    $foto = 'https://ui-avatars.com/api/?background=6366f1&color=fff&name='.urlencode($mhs['nama_lengkap']);
                    $nim_safe = preg_replace('/[^a-zA-Z0-9]/', '', $mhs['nim']);
                    // Ambil status dari database
                    $status_saat_ini = $mhs['status_absensi'];
                ?>
                <div class="mhs-row" id="row_<?= $nim_safe ?>">
                    <img src="<?= $foto ?>" class="mhs-foto" alt="foto">
                    <div class="flex-grow-1">
                        <div class="fw-bold"><?= htmlspecialchars($mhs['nama_lengkap']) ?></div>
                        <div class="small text-muted">NIM: <?= htmlspecialchars($mhs['nim']) ?></div>
                    </div>
                    <input type="hidden" name="mhs_id[]" value="<?= $mhs['id'] ?>">
                    <input type="hidden" name="mhs_nim[]" value="<?= htmlspecialchars($mhs['nim']) ?>">
                    
                    <div class="btn-status-group d-flex flex-wrap">
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="hadir_<?= $nim_safe ?>" value="hadir" <?= $status_saat_ini == 'hadir' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-success" for="hadir_<?= $nim_safe ?>">Hadir</label>
                        
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="telat_<?= $nim_safe ?>" value="terlambat" <?= $status_saat_ini == 'terlambat' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-warning" for="telat_<?= $nim_safe ?>">Telat</label>
                        
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="izin_<?= $nim_safe ?>" value="izin" <?= $status_saat_ini == 'izin' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-info" for="izin_<?= $nim_safe ?>">Izin</label>
                        
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="sakit_<?= $nim_safe ?>" value="sakit" <?= $status_saat_ini == 'sakit' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-secondary" for="sakit_<?= $nim_safe ?>">Sakit</label>
                        
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="alpha_<?= $nim_safe ?>" value="alpha" <?= $status_saat_ini == 'alpha' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-danger" for="alpha_<?= $nim_safe ?>">Alpha</label>
                    </div>
                    
                    <button type="button" class="btn-hapus-mhs" onclick="hapusMhs(<?= $mhs['id'] ?>, this)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="sticky-footer">
            <div class="container d-flex justify-content-end gap-3">
                <a href="tampil_absensi.php" class="btn btn-light px-4 rounded-pill fw-semibold border">Batal</a>
                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-semibold shadow-sm">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function hapusMhs(id, btn) {
        Swal.fire({
            title: 'Hapus mahasiswa?',
            text: 'Data kehadirannya akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f97316',
            confirmButtonText: 'Ya, hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'hapus_ids[]';
                hiddenInput.value = id;
                document.getElementById('formEditAbsensi').appendChild(hiddenInput);
                btn.closest('.mhs-row').remove();
                Swal.fire('Terhapus', 'Mahasiswa dikeluarkan dari pertemuan ini', 'success');
            }
        });
    }

    const metodeSelect = document.getElementById('metode_pembelajaran');
    const ruanganInput = document.getElementById('ruangan_link');
    const labelRuangan = document.getElementById('labelRuangan');

    function toggleRuangan() {
        const metode = metodeSelect.value;
        if (metode === 'offline' || metode === 'online') {
            ruanganInput.required = true;
            labelRuangan.innerHTML = 'Ruangan / Link <span class="text-danger">*</span>';
        } else {
            ruanganInput.required = false;
            labelRuangan.innerHTML = 'Ruangan / Link (opsional)';
        }
    }
    metodeSelect.addEventListener('change', toggleRuangan);
    toggleRuangan();
</script>
</body>
</html>