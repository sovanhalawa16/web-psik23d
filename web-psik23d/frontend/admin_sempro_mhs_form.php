<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$default_sesi = isset($_GET['sesi_id']) ? (int)$_GET['sesi_id'] : 0;

$data = [
    'sesi_id' => $default_sesi,
    'nama_mhs' => '', 'nim' => '', 'prodi' => 'Ilmu Komputer',
    'judul_skripsi' => '', 'pukul_mhs' => '08:30', 'durasi_menit' => 60,
    'ruang' => '', 'dosen_ps' => '', 'dosen_pa' => '',
    'dosen_ns1' => '', 'dosen_ns2' => '', 'dosen_ns3' => ''
];
$is_edit = false;
if($id > 0) {
    $q = mysqli_query($koneksi, "SELECT * FROM sempro_mhs WHERE id='$id'");
    if(mysqli_num_rows($q) > 0) { $data = mysqli_fetch_assoc($q); $is_edit = true; $default_sesi = $data['sesi_id']; }
}

// Ambil semua sesi untuk dropdown
$qs = mysqli_query($koneksi, "SELECT * FROM sempro_sesi ORDER BY hari_tanggal ASC, pukul_sesi ASC");
$semua_sesi = [];
while($r = mysqli_fetch_assoc($qs)) { $semua_sesi[] = $r; }
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $is_edit ? 'Edit' : 'Tambah' ?> Mahasiswa Sempro</title>
<script>(function(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--bg:#F4F8F1;--card:#FFF;--ink:#0A1A0F;--ink-2:#3D4F42;--ink-muted:#7A8A7F;--border:#0A1A0F;--border-soft:rgba(10,26,15,.12);--accent:#0B7A3E}
[data-bs-theme="dark"]{--bg:#0A140D;--card:#0F1B14;--ink:#E8F5EC;--ink-2:#A8C4B2;--ink-muted:#6B8577;--border:#E8F5EC;--border-soft:rgba(232,245,236,.12);--accent:#22C55E}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--ink);padding:40px 0}
.wrap{max-width:760px;margin:0 auto;padding:0 24px}
.card-form{background:var(--card);border:1.5px solid var(--border);padding:32px 28px}
.card-form h2{margin:0 0 6px;font-weight:800;font-size:1.4rem}
.card-form .sub{margin:0 0 24px;font-family:'JetBrains Mono',monospace;font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--ink-muted)}
.form-label{font-family:'JetBrains Mono',monospace;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--ink-2);margin-bottom:6px}
.form-control,.form-select{background:var(--bg);border:1.5px solid var(--border);color:var(--ink);padding:11px 14px;border-radius:0}
.form-control:focus,.form-select:focus{background:var(--bg);color:var(--ink);border-color:var(--accent);box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 20%,transparent)}
.section-title{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--accent);margin:24px 0 14px;padding-bottom:8px;border-bottom:1.5px dashed var(--border-soft);display:flex;align-items:center;gap:8px}
.btn-primary{background:var(--accent);border:1.5px solid var(--accent);color:#FFF;font-family:'JetBrains Mono',monospace;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:14px;width:100%;margin-top:20px}
.btn-primary:hover{filter:brightness(1.1)}
.btn-back{font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--ink);text-decoration:none;padding:8px 14px;border:1.5px solid var(--border);background:var(--card);display:inline-flex;align-items:center;gap:6px;margin-bottom:16px}
.btn-back:hover{background:var(--ink);color:var(--card)}
</style>
</head>
<body>
<div class="wrap">
  <a href="admin_sempro.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
  <div class="card-form">
    <h2><?= $is_edit ? 'Edit' : 'Tambah' ?> Mahasiswa Sempro</h2>
    <p class="sub">Assign mahasiswa ke sesi yang sudah dibuat</p>
    <form action="../backend/proses_sempro.php" method="POST">
      <input type="hidden" name="action" value="save_mhs">
      <input type="hidden" name="id" value="<?= $is_edit ? $data['id'] : '' ?>">

      <div class="mb-3">
        <label class="form-label">Pilih Sesi</label>
        <select name="sesi_id" class="form-select" required>
          <option value="">-- Pilih Sesi --</option>
          <?php foreach($semua_sesi as $s): 
            $ts = strtotime($s['hari_tanggal']);
            $label = date('d M Y', $ts) . ' · Pukul ' . substr($s['pukul_sesi'],0,5);
          ?>
            <option value="<?= $s['id'] ?>" <?= $default_sesi == $s['id'] ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="section-title"><i class="fas fa-user-graduate"></i> Data Mahasiswa</div>

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Nama Mahasiswa</label>
          <input type="text" name="nama_mhs" class="form-control" value="<?= htmlspecialchars($data['nama_mhs']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">NIM</label>
          <input type="text" name="nim" class="form-control" value="<?= htmlspecialchars($data['nim']) ?>" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Program Studi</label>
          <input type="text" name="prodi" class="form-control" value="<?= htmlspecialchars($data['prodi']) ?>" required>
        </div>
        <div class="col-12">
          <label class="form-label">Judul Skripsi</label>
          <textarea name="judul_skripsi" rows="3" class="form-control" required><?= htmlspecialchars($data['judul_skripsi']) ?></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label">Pukul Mulai</label>
          <input type="time" name="pukul_mhs" class="form-control" value="<?= substr($data['pukul_mhs'],0,5) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Durasi (menit)</label>
          <input type="number" name="durasi_menit" class="form-control" value="<?= $data['durasi_menit'] ?>" min="15" max="180" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Ruang</label>
          <input type="text" name="ruang" class="form-control" value="<?= htmlspecialchars($data['ruang']) ?>" placeholder="77.1.04" required>
        </div>
      </div>

      <div class="section-title"><i class="fas fa-user-tie"></i> Tim Penguji</div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">PS (Pembimbing Skripsi)</label>
          <input type="text" name="dosen_ps" class="form-control" value="<?= htmlspecialchars($data['dosen_ps']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">PA (Penguji Anggota)</label>
          <input type="text" name="dosen_pa" class="form-control" value="<?= htmlspecialchars($data['dosen_pa']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">NS 1</label>
          <input type="text" name="dosen_ns1" class="form-control" value="<?= htmlspecialchars($data['dosen_ns1']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">NS 2</label>
          <input type="text" name="dosen_ns2" class="form-control" value="<?= htmlspecialchars($data['dosen_ns2']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">NS 3</label>
          <input type="text" name="dosen_ns3" class="form-control" value="<?= htmlspecialchars($data['dosen_ns3']) ?>">
        </div>
      </div>

      <button type="submit" class="btn-primary"><i class="fas fa-save me-2"></i><?= $is_edit ? 'Simpan Perubahan' : 'Tambahkan Mahasiswa' ?></button>
    </form>
  </div>
</div>
</body>
</html>