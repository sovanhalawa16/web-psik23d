<?php 
include '../backend/cek_session.php'; 
include '../backend/data_dosen.php'; 
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q = mysqli_query($koneksi, "SELECT * FROM jadwal WHERE id='$id'");
$d = mysqli_fetch_assoc($q);

if(!$d) {
    die("<h3>Data jadwal tidak ditemukan!</h3>");
}

// Cek apakah kelas ini online atau offline dari data yang ada di database
$is_online = ($d['jenis_kelas'] == 'online') ? true : false;
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal - Admin Premium</title>
    
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
                    <h4 class="fw-bold mb-0" style="color: var(--accent);">Edit Jadwal Kuliah</h4>
                </div>

                <div class="premium-card p-4 p-md-5">
                    <form action="../backend/proses_edit_jadwal.php" method="POST" enctype="multipart/form-data">
                        
                        <!-- HIDDEN ID JADWAL -->
                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                        <!-- SIMPAN FOTO RUANGAN LAMA BUAT JAGA JAGA KALAU GAK UPLOAD BARU -->
                        <input type="hidden" name="foto_ruangan_lama" value="<?= $d['foto_ruangan'] ?>">

                        <!-- BARIS 1: Blok, Semester, SKS -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">BLOK</label>
                                <select name="blok" class="form-select" required>
                                    <option value="Blok 1" <?= $d['blok']=='Blok 1'?'selected':'' ?>>Blok 1</option>
                                    <option value="Blok 2" <?= $d['blok']=='Blok 2'?'selected':'' ?>>Blok 2</option>
                                    <option value="Blok 3" <?= $d['blok']=='Blok 3'?'selected':'' ?>>Blok 3</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">SEMESTER</label>
                                <select name="semester" class="form-select" required>
                                    <?php for($i=1; $i<=8; $i++): ?>
                                        <option value="<?= $i ?>" <?= $d['semester']==$i?'selected':'' ?>>Smt <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">SKS</label>
                                <input type="number" name="sks" class="form-control" value="<?= $d['sks'] ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 2: Pertemuan & Jenis Kelas -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-sub">PERTEMUAN KE</label>
                                <select name="pertemuan_ke" class="form-select" required>
                                    <?php for($i=1; $i<=16; $i++): ?>
                                        <option value="<?= $i ?>" <?= $d['pertemuan_ke']==$i?'selected':'' ?>>Ke-<?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-sub">KATEGORI KELAS</label>
                                <select name="kategori_kelas" class="form-select" required>
                                    <option value="Teori" <?= ($d['kategori_kelas']=='Teori')?'selected':'' ?>>Teori</option>
                                    <option value="Praktek" <?= ($d['kategori_kelas']=='Praktek')?'selected':'' ?>>Praktek</option>
                                    <option value="UTS" <?= ($d['kategori_kelas']=='UTS')?'selected':'' ?>>UTS (Ujian Tengah Semester)</option>
                                    <option value="UAS" <?= ($d['kategori_kelas']=='UAS')?'selected':'' ?>>UAS (Ujian Akhir Semester)</option>
                                </select>
                            </div>
                        </div>

                        <!-- BARIS 3: Kode MK & Nama MK -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">KODE MK</label>
                                <input type="text" name="kode_mk" class="form-control" value="<?= $d['kode_mk'] ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-sub">NAMA MATA KULIAH</label>
                                <input type="text" name="mata_kuliah" class="form-control" value="<?= $d['mata_kuliah'] ?>" required>
                            </div>
                        </div>

                        <hr class="border-secondary border-opacity-10 my-4">

                        <!-- BARIS 4: Hari & Jam -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">HARI</label>
                                <select name="hari" class="form-select" required>
                                    <?php 
                                    $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                    foreach($days as $day): 
                                    ?>
                                        <option value="<?= $day ?>" <?= $d['hari']==$day?'selected':'' ?>><?= $day ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">JAM MULAI</label>
                                <input type="time" name="jam_mulai" class="form-control" value="<?= $d['jam_mulai'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-sub">JAM SELESAI</label>
                                <input type="time" name="jam_selesai" class="form-control" value="<?= $d['jam_selesai'] ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 5: Dosen -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-sub">DOSEN PENGAMPU</label>
                            <select name="dosen_id" class="form-select" required>
                                <option value="">-- Pilih Dosen --</option>
                                <?php foreach($semua_dosen as $dsn): ?>
                                    <option value="<?= $dsn['id'] ?>" <?= $d['dosen_id']==$dsn['id']?'selected':'' ?>><?= $dsn['nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- BARIS 6: SAKLAR METODE PEMBELAJARAN (ONLINE / OFFLINE) -->
                        <div class="mb-3 p-3 rounded-3 border bg-light bg-opacity-10">
                            <label class="form-label fw-bold small text-sub d-block mb-3">METODE PEMBELAJARAN</label>
                            
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelas" id="offline" value="offline" <?= !$is_online ? 'checked' : '' ?> onchange="toggleJenisKelas()">
                                <label class="form-check-label fw-bold text-success" for="offline"><i class="fas fa-building me-1"></i> OFFLINE (Tatap Muka)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelas" id="online" value="online" <?= $is_online ? 'checked' : '' ?> onchange="toggleJenisKelas()">
                                <label class="form-check-label fw-bold text-primary" for="online"><i class="fas fa-video me-1"></i> ONLINE (Daring)</label>
                            </div>
                        </div>

                        <div id="inputOffline" style="<?= $is_online ? 'display: none;' : 'display: block;' ?>">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-sub">RUANGAN</label>
                                <input type="text" name="ruangan" class="form-control" value="<?= $d['ruangan'] ?>" placeholder="Cth: Lab Komputer 1 / RK. 02">
                            </div>
                        </div>

                        <div id="inputOnline" style="<?= $is_online ? 'display: block;' : 'display: none;' ?>">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-sub">LINK MEETING (Zoom/Gmeet)</label>
                                <input type="text" name="link_online" class="form-control" value="<?= $d['link_online'] ?>" placeholder="https://meet.google.com/...">
                            </div>
                        </div>
                        
                        <!-- BARIS 7: Upload Foto -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-sub">FOTO/VIDEO KELAS (Opsional)</label>
                            <input type="file" name="foto_ruangan[]" class="form-control" accept="image/*,video/*" multiple>
                            <div class="form-text small opacity-75">Biarkan kosong jika tidak ingin mengubah foto/video yang lama.</div>
                        </div>

                        <!-- BARIS 8: Catatan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-sub">CATATAN (Opsional)</label>
                            <input type="text" name="catatan" class="form-control" value="<?= $d['catatan'] ?>" placeholder="Catatan tambahan...">
                        </div>
                        
                        <!-- BARIS 9: Status Kelas -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-danger">STATUS KELAS (SAKLAR)</label>
                            <select name="status_kelas" class="form-select fw-bold border-danger" style="background-color: #fff5f5;">
                                <option value="Aktif" <?= ($d['status_kelas'] == 'Aktif') ? 'selected' : '' ?>>🟢 Aktif (Sesuai Jadwal)</option>
                                <option value="Belajar Mandiri" <?= ($d['status_kelas'] == 'Belajar Mandiri') ? 'selected' : '' ?>>🟡 Belajar Mandiri (Asinkronus)</option>
                                <option value="Dibatalkan" <?= ($d['status_kelas'] == 'Dibatalkan') ? 'selected' : '' ?>>🔴 Dibatalkan (Libur/Kosong)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3" style="background-color: var(--accent); border-color: var(--accent);">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan Jadwal
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Fungsi interaktif buka-tutup kolom Ruangan/Link Online
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