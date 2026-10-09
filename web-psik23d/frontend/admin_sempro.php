<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

// Ambil semua sesi + jumlah mahasiswa
$q = mysqli_query($koneksi, "
    SELECT s.*, 
           (SELECT COUNT(*) FROM sempro_mhs m WHERE m.sesi_id = s.id) AS total_mhs
    FROM sempro_sesi s
    ORDER BY s.hari_tanggal ASC, s.pukul_sesi ASC
");
$semua_sesi = [];
while($r = mysqli_fetch_assoc($q)) { $semua_sesi[] = $r; }
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Sempro - Admin</title>
<script>(function(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--bg:#F4F8F1;--card:#FFF;--ink:#0A1A0F;--ink-2:#3D4F42;--ink-muted:#7A8A7F;--border:#0A1A0F;--border-soft:rgba(10,26,15,.12);--accent:#0B7A3E}
[data-bs-theme="dark"]{--bg:#0A140D;--card:#0F1B14;--ink:#E8F5EC;--ink-2:#A8C4B2;--ink-muted:#6B8577;--border:#E8F5EC;--border-soft:rgba(232,245,236,.12);--accent:#22C55E}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh}
.mono{font-family:'JetBrains Mono',monospace}
.sesi-card{background:var(--card);border:1.5px solid var(--border);margin-bottom:20px;position:relative}
.sesi-head{display:flex;align-items:center;gap:16px;padding:18px 22px;border-bottom:1.5px dashed var(--border-soft);flex-wrap:wrap}
.sesi-date{background:var(--accent);color:#FFF;width:64px;height:64px;display:flex;flex-direction:column;justify-content:center;align-items:center;flex-shrink:0}
.sesi-date .d{font-family:'JetBrains Mono',monospace;font-size:1.6rem;font-weight:700;line-height:1}
.sesi-date .m{font-family:'JetBrains Mono',monospace;font-size:.62rem;font-weight:700;letter-spacing:1.5px;margin-top:3px;opacity:.9}
.sesi-meta{flex:1;min-width:0}
.sesi-meta h3{margin:0 0 4px;font-weight:800;font-size:1.15rem}
.sesi-meta p{margin:0;font-family:'JetBrains Mono',monospace;font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;color:var(--ink-2)}
.sesi-actions{display:flex;gap:6px;flex-wrap:wrap}
.btn-mini{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:7px 12px;border:1.5px solid var(--border);background:var(--card);color:var(--ink);text-decoration:none;display:inline-flex;align-items:center;gap:5px;cursor:pointer;transition:all .15s}
.btn-mini:hover{background:var(--accent);color:#FFF;border-color:var(--accent)}
.btn-mini.danger:hover{background:#DC2626;border-color:#DC2626}
.btn-mini.primary{background:var(--accent);color:#FFF;border-color:var(--accent)}
.btn-mini.primary:hover{filter:brightness(1.1)}
.mhs-list{padding:14px 22px 18px}
.mhs-row{display:grid;grid-template-columns:60px 1fr auto;gap:14px;padding:12px 0;border-bottom:1px dashed var(--border-soft);align-items:center}
.mhs-row:last-child{border-bottom:none}
.mhs-time{font-family:'JetBrains Mono',monospace;font-size:1rem;font-weight:700;color:var(--accent)}
.mhs-info h4{margin:0 0 2px;font-weight:700;font-size:.95rem}
.mhs-info .meta{font-family:'JetBrains Mono',monospace;font-size:.7rem;color:var(--ink-muted)}
.mhs-info .judul{font-size:.78rem;color:var(--ink-2);font-style:italic;margin-top:3px;line-height:1.35}
.mhs-acts{display:flex;gap:4px}
.empty-sesi{padding:22px;text-align:center;color:var(--ink-muted);font-family:'JetBrains Mono',monospace;font-size:.75rem;text-transform:uppercase;letter-spacing:1px}
.top-header{background:var(--card);border-bottom:1.5px solid var(--border);padding:22px 0;margin-bottom:24px}
.top-header-inner{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;max-width:1080px;margin:0 auto;padding:0 24px}
.top-header h1{margin:0;font-weight:800;font-size:1.5rem}
.top-header p{margin:4px 0 0;font-family:'JetBrains Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:1px;color:var(--ink-muted)}
.wrap{max-width:1080px;margin:0 auto;padding:0 24px 80px}
.alert-success{background:color-mix(in srgb,var(--accent) 12%,var(--card));border:1.5px solid var(--accent);color:var(--ink);padding:12px 18px;font-family:'JetBrains Mono',monospace;font-size:.78rem;font-weight:600;margin-bottom:20px}
@media(max-width:720px){
  .mhs-row{grid-template-columns:1fr;gap:8px}
  .mhs-acts{justify-content:flex-start}
  .sesi-head{padding:14px 16px;gap:12px}
  .sesi-date{width:54px;height:54px}
  .mhs-list{padding:12px 16px 14px}
}
</style>
</head>
<body>

<div class="top-header">
  <div class="top-header-inner">
    <div>
      <h1><i class="fas fa-chalkboard-teacher me-2" style="color:var(--accent)"></i>Kelola Sempro</h1>
      <p>Admin Panel · FMIPA UNIMED</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="admin_dashboard.php" class="btn-mini"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <a href="admin_sempro_form.php" class="btn-mini primary"><i class="fas fa-plus"></i> Buat Sesi Baru</a>
    </div>
  </div>
</div>

<div class="wrap">

<?php if(isset($_GET['msg'])): ?>
  <div class="alert-success">
    <i class="fas fa-check-circle me-2"></i>
    <?= $_GET['msg'] == 'hapus' ? 'Data berhasil dihapus!' : 'Data berhasil disimpan!' ?>
  </div>
<?php endif; ?>

<?php if(empty($semua_sesi)): ?>
  <div class="sesi-card">
    <div class="empty-sesi">
      <i class="fas fa-calendar-plus" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:14px"></i>
      Belum ada sesi sempro. Klik "Buat Sesi Baru" untuk mulai.
    </div>
  </div>
<?php else: 
  $bulan_indo = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  $hari_indo = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
  foreach($semua_sesi as $s):
    $ts = strtotime($s['hari_tanggal']);
    $tgl_num = date('d', $ts);
    $bln_short = strtoupper($bulan_indo[(int)date('n', $ts)]);
    $hari_nama = $hari_indo[date('l', $ts)];
    $tgl_full = $hari_nama . ', ' . date('d', $ts) . ' ' . $bulan_indo[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    
    // Ambil mahasiswa di sesi ini
    $qm = mysqli_query($koneksi, "SELECT * FROM sempro_mhs WHERE sesi_id='{$s['id']}' ORDER BY pukul_mhs ASC");
?>
  <div class="sesi-card">
    <div class="sesi-head">
      <div class="sesi-date">
        <span class="d"><?= $tgl_num ?></span>
        <span class="m"><?= $bln_short ?></span>
      </div>
      <div class="sesi-meta">
        <h3><?= $tgl_full ?></h3>
        <p>
          <i class="far fa-clock"></i> Mulai <?= substr($s['pukul_sesi'],0,5) ?> WIB
          &nbsp;·&nbsp;
          <i class="fas fa-user-pen"></i> <?= htmlspecialchars($s['notulen'] ?: '-') ?>
          &nbsp;·&nbsp;
          <i class="fas fa-users"></i> <?= $s['total_mhs'] ?> mahasiswa
        </p>
      </div>
      <div class="sesi-actions">
        <a href="admin_sempro_mhs_form.php?sesi_id=<?= $s['id'] ?>" class="btn-mini primary"><i class="fas fa-user-plus"></i> Tambah Mhs</a>
        <a href="admin_sempro_form.php?id=<?= $s['id'] ?>" class="btn-mini"><i class="fas fa-edit"></i> Edit Sesi</a>
        <a href="../backend/proses_sempro.php?action=delete_sesi&id=<?= $s['id'] ?>" class="btn-mini danger" onclick="return confirm('Hapus sesi ini? Semua mahasiswa di dalamnya akan ikut terhapus.')"><i class="fas fa-trash"></i> Hapus</a>
      </div>
    </div>
    <div class="mhs-list">
      <?php if(mysqli_num_rows($qm) === 0): ?>
        <div class="empty-sesi" style="padding:14px 0"><i class="fas fa-inbox me-2"></i>Belum ada mahasiswa di sesi ini</div>
      <?php else: 
        while($m = mysqli_fetch_assoc($qm)): ?>
          <div class="mhs-row">
            <div class="mhs-time"><?= substr($m['pukul_mhs'],0,5) ?></div>
            <div class="mhs-info">
              <h4><?= htmlspecialchars($m['nama_mhs']) ?> <span class="mono" style="font-size:.75rem;font-weight:500;color:var(--ink-muted)">· <?= htmlspecialchars($m['nim']) ?></span></h4>
              <div class="meta"><?= htmlspecialchars($m['prodi']) ?> · <?= htmlspecialchars($m['ruang']) ?></div>
              <div class="judul">"<?= htmlspecialchars($m['judul_skripsi']) ?>"</div>
            </div>
            <div class="mhs-acts">
              <a href="admin_sempro_mhs_form.php?id=<?= $m['id'] ?>" class="btn-mini"><i class="fas fa-edit"></i></a>
              <a href="../backend/proses_sempro.php?action=delete_mhs&id=<?= $m['id'] ?>" class="btn-mini danger" onclick="return confirm('Hapus data mahasiswa ini?')"><i class="fas fa-trash"></i></a>
            </div>
          </div>
        <?php endwhile; 
      endif; ?>
    </div>
  </div>
<?php endforeach; endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>