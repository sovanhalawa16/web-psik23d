<?php 
include '../backend/cek_session.php'; 
include '../backend/data_dosen.php'; 
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal - Admin Premium</title>
    
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --bg-body: #f1f5f9; --bg-card: #ffffff; --text-main: #1e293b; --text-sub: #64748b; --border-color: #e2e8f0; --accent: #6366f1; }
        [data-bs-theme="dark"] { --bg-body: #020617; --bg-card: #1e293b; --text-main: #f8fafc; --text-sub: #94a3b8; --border-color: #334155; --accent: #818cf8; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); transition: background 0.3s; }
        .premium-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .form-control, .form-select { background-color: var(--bg-body); border-color: var(--border-color); color: var(--text-main); padding: 0.7rem 1rem; border-radius: 8px; }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="container py-5 animate-up">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="jadwal.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
                    <h4 class="fw-bold mb-0" style="color: var(--accent);">Input Jadwal Kuliah</h4>
                </div>

                <div class="premium-card p-4 p-md-5">
                    <form action="../backend/proses_tambah_jadwal.php" method="POST" enctype="multipart/form-data">
                        
                        <!-- BARIS 1: Blok, Semester, SKS -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">BLOK</label>
                                <select name="blok" class="form-select" required>
                                    <option value="Blok 1">Blok 1</option>
                                    <option value="Blok 2">Blok 2</option>
                                    <option value="Blok 3">Blok 3</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">SEMESTER</label>
                                <select name="semester" class="form-select" required>
                                    <?php for($i=1; $i<=8; $i++): ?><option value="<?= $i ?>">Smt <?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">SKS</label>
                                <input type="number" name="sks" class="form-control" value="2" required>
                            </div>
                        </div>

                        <!-- BARIS 2: Pertemuan & Jenis Kelas (BARU) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-sub">PERTEMUAN KE</label>
                                <select name="pertemuan_ke" class="form-select" required>
                                    <?php for($i=1; $i<=16; $i++): ?><option value="<?= $i ?>">Ke-<?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-sub">KATEGORI KELAS</label>
                                <select name="kategori_kelas" class="form-select" required>
                                    <option value="Teori">Teori</option>
                                    <option value="Praktek">Praktek</option>
                                    <option value="UTS">UTS (Ujian Tengah Semester)</option>
                                    <option value="UAS">UAS (Ujian Akhir Semester)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">KODE MK</label>
                                <input type="text" name="kode_mk" class="form-control" placeholder="3KOM..." required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-sub">NAMA MATA KULIAH</label>
                                <input type="text" name="mata_kuliah" class="form-control" placeholder="Cth: Rekayasa Perangkat Lunak" required>
                            </div>
                        </div>

                        <hr class="border-secondary border-opacity-10 my-4">

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">HARI</label>
                                <select name="hari" class="form-select" required>
                                    <option value="Senin">Senin</option><option value="Selasa">Selasa</option><option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option><option value="Jumat">Jumat</option><option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">JAM MULAI</label>
                                <input type="time" name="jam_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">JAM SELESAI</label>
                                <input type="time" name="jam_selesai" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-sub">DOSEN PENGAMPU</label>
                            <select name="dosen_id" class="form-select" required>
                                <option value="">-- Pilih Dosen --</option>
                                <?php foreach($semua_dosen as $d): ?><option value="<?= $d['id'] ?>"><?= $d['nama'] ?></option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3 p-3 rounded-3 border bg-light bg-opacity-10">
                            <label class="form-label fw-bold small text-sub d-block mb-3">METODE PEMBELAJARAN</label>
                            
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelas" id="offline" value="offline" checked onchange="toggleJenisKelas()">
                                <label class="form-check-label fw-bold text-success" for="offline"><i class="fas fa-building me-1"></i> OFFLINE (Tatap Muka)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelas" id="online" value="online" onchange="toggleJenisKelas()">
                                <label class="form-check-label fw-bold text-primary" for="online"><i class="fas fa-video me-1"></i> ONLINE (Daring)</label>
                            </div>
                        </div>

                        <div id="inputOffline">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-sub">RUANGAN</label>
                                <input type="text" name="ruangan" class="form-control" placeholder="Cth: Lab Komputer 1 / RK. 02">
                            </div>
                        </div>

                        <div id="inputOnline" style="display: none;">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-sub">LINK MEETING (Zoom/Gmeet)</label>
                                <input type="text" name="link_online" class="form-control" placeholder="https://meet.google.com/...">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-sub">FOTO/VIDEO KELAS (Opsional)</label>
                            <input type="file" name="foto_ruangan[]" class="form-control" accept="image/*,video/*" multiple>
                            <div class="form-text small opacity-75">Tahan tombol CTRL/Pilih Semua untuk upload lebih dari 1 file foto atau video. Berlaku untuk kelas Offline maupun Online.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-sub">CATATAN (Opsional)</label>
                            <input type="text" name="catatan" class="form-control" placeholder="Catatan tambahan...">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3" style="background-color: var(--accent); border-color: var(--accent);">
                            <i class="fas fa-save me-2"></i> Simpan Jadwal
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleJenisKelas() {
            const isOnline = document.getElementById('online').checked;
            const divOffline = document.getElementById('inputOffline');
            const divOnline = document.getElementById('inputOnline');

            if (isOnline) {
                divOffline.style.display = 'none';
                divOnline.style.display = 'block';
            } else {
                divOffline.style.display = 'block';
                divOnline.style.display = 'none';
            }
        }
    </script>
</body>
</html>