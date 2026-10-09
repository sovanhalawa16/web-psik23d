<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = ['hari_tanggal' => date('Y-m-d'), 'pukul_sesi' => '08:30', 'notulen' => ''];
$is_edit = false;
if($id > 0) {
    $q = mysqli_query($koneksi, "SELECT * FROM sempro_sesi WHERE id='$id'");
    if(mysqli_num_rows($q) > 0) { $data = mysqli_fetch_assoc($q); $is_edit = true; }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $is_edit ? 'Edit' : 'Buat' ?> Sesi Sempro</title>
<script>(function(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--bg:#F4F8F1;--card:#FFF;--ink:#0A1A0F;--ink-2:#3D4F42;--ink-muted:#7A8A7F;--border:#0A1A0F;--border-soft:rgba(10,26,15,.12);--accent:#0B7A3E}
[data-bs-theme="dark"]{--bg:#0A140D;--card:#0F1B14;--ink:#E8F5EC;--ink-2:#A8C4B2;--ink-muted:#6B8577;--border:#E8F5EC;--border-soft:rgba(232,245,236,.12);--accent:#22C55E}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;padding:40px 0}
.wrap{max-width:640px;margin:0 auto;padding:0 24px}
.card-form{background:var(--card);border:1.5px solid var(--border);padding:32px 28px}
.card-form h2{margin:0 0 6px;font-weight:800;font-size:1.4rem}
.card-form .sub{margin:0 0 24px;font-family:'JetBrains Mono',monospace;font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--ink-muted)}
.form-label{font-family:'JetBrains Mono',monospace;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--ink-2);margin-bottom:6px}
.form-control,.form-select{background:var(--bg);border:1.5px solid var(--border);color:var(--ink);padding:11px 14px;border-radius:0}
.form-control:focus,.form-select:focus{background:var(--bg);color:var(--ink);border-color:var(--accent);box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 20%,transparent)}
.btn-primary{background:var(--accent);border:1.5px solid var(--accent);color:#FFF;font-family:'JetBrains Mono',monospace;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:14px;width:100%}
.btn-primary:hover{background:color-mix(in srgb,var(--accent) 85%,black);border-color:color-mix(in srgb,var(--accent) 85%,black)}
.btn-back{font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--ink);text-decoration:none;padding:8px 14px;border:1.5px solid var(--border);background:var(--card);display:inline-flex;align-items:center;gap:6px;margin-bottom:16px}
.btn-back:hover{background:var(--ink);color:var(--card)}
</style>
</head>
<body>
<div class="wrap">
  <a href="admin_sempro.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
  <div class="card-form">
    <h2><?= $is_edit ? 'Edit' : 'Buat' ?> Sesi Sempro</h2>
    <p class="sub">Info sesi untuk 1 hari pelaksanaan</p>
    <form action="../backend/proses_sempro.php" method="POST">
      <input type="hidden" name="action" value="save_sesi">
      <input type="hidden" name="id" value="<?= $is_edit ? $data['id'] : '' ?>">
      <div class="mb-3">
        <label class="form-label">Hari / Tanggal</label>
        <input type="date" name="hari_tanggal" class="form-control" value="<?= $data['hari_tanggal'] ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Pukul Mulai Sesi</label>
        <input type="time" name="pukul_sesi" class="form-control" value="<?= substr($data['pukul_sesi'],0,5) ?>" required>
      </div>
      <div class="mb-4">
        <label class="form-label">Notulen</label>
        <input type="text" name="notulen" class="form-control" value="<?= htmlspecialchars($data['notulen']) ?>" placeholder="Nama notulen yang bertugas..." required>
      </div>
      <button type="submit" class="btn-primary"><i class="fas fa-save me-2"></i><?= $is_edit ? 'Simpan Perubahan' : 'Simpan Sesi' ?></button>
    </form>
  </div>
</div>
</body>
</html>