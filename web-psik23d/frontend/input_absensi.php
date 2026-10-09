<?php 
session_start();
include '../koneksi.php'; 

// Ambil data mahasiswa
$q_mhs = mysqli_query($koneksi, "SELECT nim, nama, foto FROM users WHERE role='mahasiswa' ORDER BY nama ASC");
$q_dosen = mysqli_query($koneksi, "SELECT nama FROM dosen ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Absensi - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
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
            transition: all 0.2s;
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
            transition: 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.2);
        }
        .mhs-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f2f5;
            transition: 0.2s;
        }
        .mhs-foto {
            width: 48px;
            height: 48px;
            border-radius: 30px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .mhs-nama {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 2px;
            color: #0f172a;
        }
        .mhs-nim {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 500;
        }
        .btn-status-group .btn {
            font-size: 0.7rem;
            padding: 5px 10px;
            border-radius: 30px !important;
            margin: 0 2px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-status-group .btn-check:checked+.btn {
            transform: scale(1.02);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-hapus-mhs {
            background: #fff1f0;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 30px;
            color: #f97316;
            transition: 0.2s;
        }
        .btn-hapus-mhs:hover {
            background: #f97316;
            color: white;
            transform: scale(1.05);
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
        .badge-counter {
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 40px;
            padding: 4px 14px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        @media (max-width: 768px) {
            .mhs-row { flex-wrap: wrap; }
            .btn-status-group { margin-top: 8px; }
        }
    </style>
</head>
<body>

<div class="header-soft">
    <h4 class="fw-bold mb-1"><i class="fas fa-pen-alt me-2"></i>Input Kehadiran</h4>
    <p class="opacity-75 small mb-0">Rekam absensi kelas dengan mudah & cepat</p>
</div>

<div class="container mt-4">
    <form action="../backend/proses_input_absensi.php" method="POST" id="formAbsensi">
        
        <!-- Detail Perkuliahan -->
        <div class="glass-card">
            <h6 class="fw-bold mb-3"><i class="fas fa-chalkboard-user me-2 text-primary"></i>Informasi Kelas</h6>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Nama Mata Kuliah</label>
                    <input type="text" name="nama_mk" class="form-control" placeholder="Misal: Pemrograman Web" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kode MK</label>
                    <input type="text" name="kode_mk" class="form-control" placeholder="KOM123" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dosen Pengampu</label>
                    <select name="dosen_pengampu" class="form-select" required>
                        <option value="" disabled selected>-- Pilih Dosen --</option>
                        <?php while($d = mysqli_fetch_assoc($q_dosen)): ?>
                            <option value="<?= htmlspecialchars($d['nama']) ?>"><?= htmlspecialchars($d['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Semester</label>
                    <input type="text" name="semester" class="form-control" placeholder="Genap 23/24" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Blok</label>
                    <select name="blok" class="form-select">
                        <option value="1">Blok 1</option>
                        <option value="2">Blok 2</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">SKS</label>
                    <input type="number" name="sks" class="form-control" placeholder="2 / 3" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipe Kelas <span class="text-muted small">(bisa diisi manual)</span></label>
                    <input list="tipeList" name="tipe_kelas" id="tipe_kelas" class="form-control" placeholder="Pilih atau ketik" required>
                    <datalist id="tipeList">
                        <option>Teori</option>
                        <option>Praktek</option>
                        <option>UTS</option>
                        <option>UAS</option>
                        <option>Presentasi MR 2</option>
                        <option>Review</option>
                        <option>Studi Kasus</option>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pertemuan ke-</label>
                    <input type="number" name="pertemuan_ke" class="form-control" placeholder="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Metode Pembelajaran</label>
                    <select name="metode_pembelajaran" id="metode_pembelajaran" class="form-select" required>
                        <option value="offline">Offline (Tatap Muka)</option>
                        <option value="online">Online (Daring)</option>
                        <option value="asynchronous">Asynchronous (Mandiri)</option>
                        <option value="kelas batal">Kelas Batal</option>
                    </select>
                </div>
                <div class="col-md-3" id="divRuangan">
                    <label class="form-label" id="labelRuangan">Ruangan / Link</label>
                    <input type="text" name="ruangan_link" id="ruangan_link" class="form-control" placeholder="Contoh: 77.1.08 / meet.google.com/...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Waktu Mulai</label>
                    <input type="datetime-local" name="waktu_mulai" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Waktu Selesai</label>
                    <input type="datetime-local" name="waktu_selesai" class="form-control" required>
                </div>
            </div>
        </div>

        <!-- Daftar Mahasiswa & Status -->
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-users me-2 text-primary"></i>Presensi Mahasiswa</h6>
                <span class="badge-counter" id="totalCount"></span>
            </div>
            <div id="listMahasiswa">
                <?php 
                $no = 0;
                while($mhs = mysqli_fetch_assoc($q_mhs)): 
                    $foto = (!empty($mhs['foto']) && $mhs['foto'] != 'default.jpg') ? '../assets/img/mahasiswa/'.$mhs['foto'] : 'https://ui-avatars.com/api/?background=6366f1&color=fff&name='.urlencode($mhs['nama']);
                    $nim_safe = preg_replace('/[^a-zA-Z0-9]/', '', $mhs['nim']);
                ?>
                <div class="mhs-row" id="row_<?= $nim_safe ?>">
                    <img src="<?= $foto ?>" class="mhs-foto" alt="foto">
                    <div class="flex-grow-1">
                        <div class="mhs-nama"><?= htmlspecialchars($mhs['nama']) ?></div>
                        <div class="mhs-nim">NIM: <?= htmlspecialchars($mhs['nim']) ?></div>
                    </div>
                    <input type="hidden" name="mhs_nama[]" value="<?= htmlspecialchars($mhs['nama']) ?>">
                    <input type="hidden" name="mhs_nim[]" value="<?= htmlspecialchars($mhs['nim']) ?>">
                    
                    <div class="btn-status-group d-flex flex-wrap">
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="hadir_<?= $nim_safe ?>" value="hadir" checked>
                        <label class="btn btn-outline-success" for="hadir_<?= $nim_safe ?>"><i class="fas fa-check-circle"></i> Hadir</label>
                        
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="telat_<?= $nim_safe ?>" value="terlambat">
                        <label class="btn btn-outline-warning" for="telat_<?= $nim_safe ?>"><i class="fas fa-clock"></i> Telat</label>
                        
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="izin_<?= $nim_safe ?>" value="izin">
                        <label class="btn btn-outline-info" for="izin_<?= $nim_safe ?>">Izin</label>
                        
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="sakit_<?= $nim_safe ?>" value="sakit">
                        <label class="btn btn-outline-secondary" for="sakit_<?= $nim_safe ?>">Sakit</label>
                        
                        <input type="radio" class="btn-check" name="status_absen_<?= $nim_safe ?>" id="alpha_<?= $nim_safe ?>" value="alpha">
                        <label class="btn btn-outline-danger" for="alpha_<?= $nim_safe ?>"><i class="fas fa-times-circle"></i> Alpha</label>
                    </div>
                    
                    <button type="button" class="btn-hapus-mhs" onclick="hapusMhs('row_<?= $nim_safe ?>', '<?= addslashes(htmlspecialchars($mhs['nama'])) ?>')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Footer sticky -->
        <div class="sticky-footer">
            <div class="container d-flex justify-content-end gap-3">
                <a href="admin_dashboard.php" class="btn btn-light px-4 rounded-pill fw-semibold border">Batal</a>
                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-semibold shadow-sm">
                    <i class="fas fa-save me-2"></i> Simpan Absensi
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // update total mahasiswa
    function updateCount() {
        let count = document.querySelectorAll('.mhs-row').length;
        document.getElementById('totalCount').innerText = count + ' mahasiswa';
    }
    updateCount();

    // hapus baris mahasiswa
    function hapusMhs(rowId, nama) {
        Swal.fire({
            title: 'Hapus dari absensi?',
            text: `Yakin ingin menghapus ${nama} dari daftar presensi ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f97316',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                let row = document.getElementById(rowId);
                row.style.transition = '0.2s';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    updateCount();
                    Swal.fire('Terhapus', `${nama} dikeluarkan dari form`, 'success');
                }, 150);
            }
        });
    }

    // logic ruangan wajib / tidak berdasarkan metode
    const metodeSelect = document.getElementById('metode_pembelajaran');
    const ruanganInput = document.getElementById('ruangan_link');
    const labelRuangan = document.getElementById('labelRuangan');

    function toggleRuangan() {
        const metode = metodeSelect.value;
        if (metode === 'offline' || metode === 'online') {
            ruanganInput.required = true;
            labelRuangan.innerHTML = 'Ruangan / Link <span class="text-danger">*</span>';
            ruanganInput.placeholder = 'Wajib diisi (contoh: Lab 1 / meet.google.com)';
        } else {
            ruanganInput.required = false;
            labelRuangan.innerHTML = 'Ruangan / Link (opsional)';
            ruanganInput.placeholder = 'Tidak wajib untuk async / batal';
        }
    }
    metodeSelect.addEventListener('change', toggleRuangan);
    toggleRuangan();
</script>
</body>
</html>