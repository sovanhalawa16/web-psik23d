<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';
include '../backend/pembayaran_helper.php';
date_default_timezone_set('Asia/Jakarta');
if ($_SESSION['role'] != 'admin') { header("location:../index.php"); exit; }

$q_pay = mysqli_query($koneksi, "SELECT p.*, p.pay_slug,
    (SELECT COUNT(*) FROM pembayaran_mhs WHERE pembayaran_id=p.id AND status='berhasil') AS jml_berhasil,
    (SELECT COUNT(*) FROM pembayaran_mhs WHERE pembayaran_id=p.id) AS jml_total
    FROM pembayaran p 
    ORDER BY p.created_at DESC");

$semua_pembayaran = [];
while ($r = mysqli_fetch_assoc($q_pay)) {
    $qc = mysqli_query($koneksi, "SELECT * FROM pembayaran_channel WHERE pembayaran_id='{$r['id']}' ORDER BY urutan ASC");
    $channels = [];
    while ($c = mysqli_fetch_assoc($qc)) $channels[] = $c;
    $r['channels'] = $channels;
    
    $qm = mysqli_query($koneksi, "
        SELECT u.id, u.nama, u.nim, u.foto, u.no_hp, 
               pm.id AS pm_id, pm.slug, pm.status, pm.bukti_bayar, pm.catatan_admin, pm.verified_at
        FROM users u
        LEFT JOIN pembayaran_mhs pm ON pm.user_id = u.id AND pm.pembayaran_id = '{$r['id']}'
        WHERE u.role = 'mahasiswa'
        ORDER BY u.nama ASC
    ");
    $mhs = [];
    while ($m = mysqli_fetch_assoc($qm)) $mhs[] = $m;
    $r['mhs'] = $mhs;
    $semua_pembayaran[] = $r;
}

$stat_berhasil = 0; $stat_gagal = 0; $stat_pending = 0;
$q_stat = mysqli_query($koneksi, "SELECT status, COUNT(*) as c FROM pembayaran_mhs GROUP BY status");
while ($s = mysqli_fetch_assoc($q_stat)) {
    if ($s['status'] === 'berhasil') $stat_berhasil = $s['c'];
    elseif ($s['status'] === 'gagal') $stat_gagal = $s['c'];
    else $stat_pending += $s['c'];
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Pembayaran — Admin</title>
<script>(function(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--bg:#F4F8F1;--bg-alt:#E7EFE3;--card:#FFF;--ink:#0A1A0F;--ink-2:#3D4F42;--ink-muted:#7A8A7F;--border:#0A1A0F;--border-soft:rgba(10,26,15,.12);--accent:#0B7A3E;--accent-2:#14A34E;--accent-on:#FFF;--amber:#F59E0B;--red:#DC2626;--emerald:#10B981}
[data-theme="dark"]{--bg:#0A140D;--bg-alt:#142019;--card:#0F1B14;--ink:#E8F5EC;--ink-2:#A8C4B2;--ink-muted:#6B8577;--border:#E8F5EC;--border-soft:rgba(232,245,236,.12);--accent:#22C55E;--accent-2:#4ADE80;--accent-on:#0A140D}
*,*::before,*::after{box-sizing:border-box}
html,body{margin:0;padding:0;overflow-x:hidden;width:100%;max-width:100%}
body{background:var(--bg);color:var(--ink);font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;line-height:1.5}
img,svg{max-width:100%;height:auto}
.container{max-width:1100px;margin:0 auto;padding:0 20px;width:100%}
.topbar{position:sticky;top:0;z-index:40;background:color-mix(in srgb,var(--bg) 92%,transparent);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-bottom:1.5px solid var(--border);padding:14px 0}
.topbar-inner{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.nav-badge{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--accent);padding:6px 12px;border:1px dashed color-mix(in srgb,var(--accent) 40%,transparent);background:color-mix(in srgb,var(--accent) 6%,transparent)}
.icon-btn{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--ink);padding:8px 12px;border:1.5px solid var(--border);background:var(--card);cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s;white-space:nowrap;text-decoration:none}
.icon-btn:hover{background:var(--accent);color:var(--accent-on);border-color:var(--accent)}
.icon-btn svg{width:14px;height:14px}
.page-header{padding:30px 0 20px;border-bottom:1.5px solid var(--border);margin-bottom:24px}
.page-header h1{font-size:1.5rem;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:10px}
.page-header h1 i{color:var(--accent)}
.page-header p{margin:0;color:var(--ink-muted);font-size:.85rem}
.page-header-inner{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.stat-card{background:var(--card);border:1.5px solid var(--border);padding:16px}
.stat-card .lbl{font-family:'JetBrains Mono',monospace;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--ink-muted);margin-bottom:6px;display:block}
.stat-card .val{font-size:1.5rem;font-weight:800;font-family:'JetBrains Mono',monospace;letter-spacing:-.5px;line-height:1}
.stat-card.accent .val{color:var(--accent)}
.stat-card.emerald .val{color:var(--emerald)}
.stat-card.amber .val{color:var(--amber)}
.stat-card.red .val{color:var(--red)}
.pay-card{background:var(--card);border:1.5px solid var(--border);margin-bottom:20px;transition:all .2s}
.pay-card.inactive{opacity:.65}
.pay-card-head{padding:16px 20px;border-bottom:1px dashed var(--border-soft);display:flex;align-items:center;gap:14px;flex-wrap:wrap;cursor:pointer;user-select:none}
.pay-card-head:hover{background:color-mix(in srgb,var(--accent) 3%,transparent)}
.pay-card-head .expand-icon{width:24px;height:24px;display:flex;align-items:center;justify-content:center;color:var(--ink-muted);transition:transform .3s;flex-shrink:0}
.pay-card.open .pay-card-head .expand-icon{transform:rotate(180deg);color:var(--accent)}
.pay-card-head .info{flex:1;min-width:0}
.pay-card-head .title{font-size:1.05rem;font-weight:800;margin:0 0 3px;color:var(--ink);word-break:break-word}
.pay-card-head .kode{font-family:'JetBrains Mono',monospace;font-size:.68rem;color:var(--accent);font-weight:700;letter-spacing:.5px;word-break:break-all}
.pay-card-head .nominal{font-family:'JetBrains Mono',monospace;font-size:1.1rem;font-weight:800;color:var(--accent);letter-spacing:-.5px;flex-shrink:0}
.pay-card-head .status-pill{font-family:'JetBrains Mono',monospace;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:4px 10px;border:1.5px solid;flex-shrink:0}
.pay-card-head .status-pill.aktif{color:var(--emerald);border-color:var(--emerald)}
.pay-card-head .status-pill.nonaktif{color:var(--ink-muted);border-color:var(--border-soft)}
.pay-card-head .status-pill.ditutup{color:var(--red);border-color:var(--red)}
.pay-card-body{display:none;padding:0 20px 20px}
.pay-card.open .pay-card-body{display:block;animation:fadeDown .25s ease}
@keyframes fadeDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
.pay-actions{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px}
.btn-mini{font-family:'JetBrains Mono',monospace;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;padding:7px 12px;border:1.5px solid var(--border);background:var(--card);color:var(--ink);cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .15s;text-decoration:none}
.btn-mini:hover{background:var(--accent);color:var(--accent-on);border-color:var(--accent)}
.btn-mini.danger:hover{background:var(--red);border-color:var(--red)}
.info-box{background:var(--bg);border:1.5px dashed var(--border-soft);padding:14px 16px;margin-bottom:16px}
.section-label{font-family:'JetBrains Mono',monospace;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--accent);margin-bottom:8px;display:flex;align-items:center;gap:6px}
.info-row{display:flex;gap:10px;padding:8px 0;border-bottom:1px dashed var(--border-soft);font-size:.82rem;align-items:flex-start;flex-wrap:wrap}
.info-row:last-child{border-bottom:none}
.info-row .lbl{color:var(--ink-muted);font-family:'JetBrains Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;min-width:90px;flex-shrink:0;font-weight:700}
.info-row .val{color:var(--ink);font-weight:700;flex:1;word-break:break-word;display:flex;align-items:center;gap:6px}
.info-row .val code{font-family:'JetBrains Mono',monospace;background:var(--bg-alt);padding:2px 8px;font-size:.78rem;font-weight:700;color:var(--accent);letter-spacing:0}
.channels-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:10px}
.channel-card{border:1.5px solid var(--border);padding:12px;background:var(--card)}
.channel-card .jenis{font-family:'JetBrains Mono',monospace;font-size:.7rem;font-weight:800;text-transform:uppercase;color:var(--accent);letter-spacing:1px;margin-bottom:6px;display:flex;align-items:center;gap:6px}
.channel-card .nomor{font-family:'JetBrains Mono',monospace;font-size:.85rem;font-weight:700;color:var(--ink);margin-bottom:3px;word-break:break-all}
.channel-card .nama{font-size:.72rem;color:var(--ink-muted);margin-bottom:8px}
.channel-card img{max-width:100%;border:1px solid var(--border-soft);background:#FFF;padding:4px;cursor:pointer;transition:transform .2s}
.channel-card img:hover{transform:scale(1.03)}
.mhs-section{margin-top:20px;border-top:1.5px solid var(--border);padding-top:16px}
.mhs-section-label{font-family:'JetBrains Mono',monospace;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--ink-muted);margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
.mhs-section-label .counter{color:var(--accent)}
.mhs-row{display:grid;grid-template-columns:44px 1fr auto;gap:12px;padding:10px 0;border-bottom:1px dashed var(--border-soft);align-items:center}
.mhs-row:last-child{border-bottom:none}
.mhs-row img{width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--border-soft)}
.mhs-info{min-width:0}
.mhs-info .name{font-weight:700;font-size:.88rem;margin:0 0 2px;color:var(--ink);word-break:break-word}
.mhs-info .meta{font-family:'JetBrains Mono',monospace;font-size:.68rem;color:var(--ink-muted);letter-spacing:.2px}
.mhs-info .slug{font-family:'JetBrains Mono',monospace;font-size:.65rem;color:var(--accent);letter-spacing:.2px;margin-top:3px;word-break:break-all;display:flex;align-items:center;gap:4px;flex-wrap:wrap}
.mhs-info .slug a{color:var(--accent);text-decoration:none}
.mhs-info .slug a:hover{text-decoration:underline}
.mhs-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
.wa-btn{background:#25D366;color:#FFF;border:none;padding:7px 10px;font-size:.75rem;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:5px;font-family:'JetBrains Mono',monospace;font-weight:700;letter-spacing:.3px}
.wa-btn:hover{background:#128C7E}
.wa-btn.disabled{background:var(--bg-alt);color:var(--ink-muted);cursor:not-allowed}
.acc-btn{background:transparent;border:1.5px solid var(--emerald);color:var(--emerald);padding:6px 10px;font-size:.7rem;cursor:pointer;font-weight:700;font-family:'JetBrains Mono',monospace;transition:all .15s;letter-spacing:.3px}
.acc-btn:hover,.acc-btn.on{background:var(--emerald);color:#FFF}
.fail-btn{background:transparent;border:1.5px solid var(--red);color:var(--red);padding:6px 10px;font-size:.7rem;cursor:pointer;font-weight:700;font-family:'JetBrains Mono',monospace;transition:all .15s;letter-spacing:.3px}
.fail-btn:hover,.fail-btn.on{background:var(--red);color:#FFF}
.status-badge{display:inline-flex;align-items:center;gap:4px;font-family:'JetBrains Mono',monospace;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;padding:4px 8px;border:1.5px solid}
.status-badge.pending{color:var(--amber);border-color:var(--amber);background:color-mix(in srgb,var(--amber) 10%,transparent)}
.status-badge.berhasil{color:var(--emerald);border-color:var(--emerald);background:color-mix(in srgb,var(--emerald) 10%,transparent)}
.status-badge.gagal{color:var(--red);border-color:var(--red);background:color-mix(in srgb,var(--red) 10%,transparent)}
.status-badge.dibebaskan{color:#6366F1;border-color:#6366F1;background:color-mix(in srgb,#6366F1 10%,transparent)}
.empty-state{text-align:center;padding:60px 20px;border:1.5px dashed var(--border-soft);background:var(--card)}
.empty-state i{font-size:3rem;color:var(--ink-muted);opacity:.4;margin-bottom:14px;display:block}
.empty-state h3{font-size:1.1rem;font-weight:800;margin:0 0 6px}
.empty-state p{color:var(--ink-muted);font-size:.85rem;margin:0 0 16px}

/* ============ MODAL FIX — STICKY FOOTER ============ */
.modal-backdrop{background:rgba(15,23,42,.5)!important}
.modal-backdrop.show{backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);opacity:1!important}
.modal-dialog-scrollable .modal-content{
    max-height: calc(100vh - 2rem);
    display: flex;
    flex-direction: column;
}
.modal-dialog-scrollable .modal-body{
    overflow-y: auto;
    flex: 1 1 auto;
    min-height: 0;
}
.modal-content{border:1.5px solid var(--border)!important;border-radius:0!important;background:var(--card)!important;color:var(--ink)!important}
.modal-header{border-bottom:1.5px solid var(--border)!important;padding:16px 20px!important;flex-shrink:0}
.modal-title{font-weight:800!important;font-size:1.05rem!important}
.modal-footer{
    position: sticky !important;
    bottom: 0 !important;
    background: var(--card) !important;
    border-top: 1.5px solid var(--border) !important;
    padding: 14px 20px !important;
    z-index: 10;
    box-shadow: 0 -6px 16px rgba(0,0,0,0.06);
    flex-shrink: 0;
    display: flex !important;
    justify-content: flex-end;
    gap: 8px;
}
.modal-body{padding:20px!important}
.form-label{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--ink-2);margin-bottom:6px}
.form-control,.form-select{background:var(--bg);border:1.5px solid var(--border);color:var(--ink);padding:10px 12px;border-radius:0;font-size:.88rem}
.form-control:focus,.form-select:focus{background:var(--bg);color:var(--ink);border-color:var(--accent);box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 20%,transparent);outline:none}
.form-text{color:var(--ink-muted);font-size:.7rem}
.hint-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:6px}
.hint-chip{font-family:'JetBrains Mono',monospace;font-size:.65rem;background:var(--bg-alt);padding:3px 8px;color:var(--accent);font-weight:700;cursor:pointer;border:1px solid var(--border-soft)}
.hint-chip:hover{background:var(--accent);color:var(--accent-on)}
.channel-form-row{border:1.5px dashed var(--border-soft);padding:12px;margin-bottom:10px;background:var(--bg);position:relative}
.channel-form-row .remove-ch{position:absolute;top:8px;right:8px;background:var(--red);color:#FFF;border:none;width:24px;height:24px;cursor:pointer;font-size:.7rem;display:flex;align-items:center;justify-content:center}
.toast-container{position:fixed;right:20px;bottom:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none}
.toast-item{background:var(--accent);color:var(--accent-on);padding:12px 18px;font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:600;letter-spacing:.4px;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.15);pointer-events:auto;animation:toastIn .3s ease;border-left:4px solid var(--accent-2);max-width:340px}
.toast-item.error{background:var(--red);border-left-color:#FFF}
@keyframes toastIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
.alert-success{background:color-mix(in srgb,var(--accent) 10%,var(--card));border:1.5px solid var(--accent);color:var(--ink);padding:12px 18px;font-family:'JetBrains Mono',monospace;font-size:.75rem;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.deadline-badge{font-family:'JetBrains Mono',monospace;font-size:.62rem;color:var(--ink-muted);background:var(--bg-alt);padding:2px 8px;font-weight:700;letter-spacing:.5px}
.deadline-badge.urgent{color:var(--red);background:color-mix(in srgb,var(--red) 10%,transparent)}
@media (max-width:720px){
    .stats-grid{grid-template-columns:repeat(2,1fr)}
    .page-header h1{font-size:1.2rem}
    .pay-card-head{padding:14px 16px}
    .pay-card-body{padding:0 16px 16px}
    .mhs-row{grid-template-columns:36px 1fr;gap:10px}
    .mhs-row img{width:36px;height:36px}
    .mhs-actions{grid-column:1 / -1;justify-content:flex-start;margin-top:6px}
    .info-row{flex-direction:column;gap:4px}
    .info-row .lbl{min-width:0}
}
</style>
</head>
<body>

<div class="topbar">
    <div class="container topbar-inner">
        <span class="nav-badge">Admin · Pembayaran</span>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="admin_dashboard.php" class="icon-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <button class="icon-btn" onclick="toggleTheme()"><i class="fas fa-moon" id="themeIcon"></i></button>
        </div>
    </div>
</div>

<div class="container" style="padding-bottom:60px">

    <?php if(isset($_GET['msg'])): ?>
    <div class="alert-success" style="margin-top:20px">
        <i class="fas fa-check-circle"></i>
        <?= $_GET['msg'] == 'hapus' ? 'Pembayaran berhasil dihapus!' : 'Pembayaran berhasil disimpan!' ?>
    </div>
    <?php endif; ?>

    <div class="page-header" style="margin-top:20px">
        <div class="page-header-inner">
            <div>
                <h1><i class="fas fa-wallet"></i> Kelola Pembayaran</h1>
                <p>Buat tagihan dan pantau status pembayaran mahasiswa</p>
            </div>
            <button class="icon-btn" onclick="openForm()" style="background:var(--accent);color:var(--accent-on);border-color:var(--accent)">
                <i class="fas fa-plus"></i> Buat Pembayaran
            </button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card accent">
            <span class="lbl">Total Tagihan</span>
            <span class="val"><?= count($semua_pembayaran) ?></span>
        </div>
        <div class="stat-card emerald">
            <span class="lbl">Berhasil</span>
            <span class="val"><?= $stat_berhasil ?></span>
        </div>
        <div class="stat-card amber">
            <span class="lbl">Pending</span>
            <span class="val"><?= $stat_pending ?></span>
        </div>
        <div class="stat-card red">
            <span class="lbl">Gagal</span>
            <span class="val"><?= $stat_gagal ?></span>
        </div>
    </div>

    <?php if(empty($semua_pembayaran)): ?>
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h3>Belum ada pembayaran</h3>
            <p>Klik "Buat Pembayaran" untuk mulai menagih mahasiswa</p>
        </div>
    <?php else: 
        $baseUrl = getBaseUrl();
        foreach($semua_pembayaran as $p):
            $is_aktif = $p['status'] == 'aktif';
            $lunas = (int)$p['jml_berhasil'];
            $total = (int)$p['jml_total'];
            
            $deadline_info = '';
            $is_urgent = false;
            if (!empty($p['deadline'])) {
                $dl = strtotime($p['deadline']);
                $now = time();
                $diff = $dl - $now;
                if ($diff < 0) { $deadline_info = 'Lewat deadline'; $is_urgent = true; }
                elseif ($diff < 86400 * 3) { $deadline_info = 'Deadline ' . date('d M', $dl); $is_urgent = true; }
                else { $deadline_info = 'Deadline ' . date('d M Y', $dl); }
            }
    ?>
    <div class="pay-card <?= $is_aktif ? '' : 'inactive' ?>" 
         data-pay-id="<?= $p['id'] ?>"
         data-nama="<?= htmlspecialchars($p['nama_pembayaran'], ENT_QUOTES) ?>"
         data-kode="<?= htmlspecialchars($p['kode_transaksi'], ENT_QUOTES) ?>"
         data-nominal="<?= $p['nominal'] ?>"
         data-deskripsi="<?= htmlspecialchars($p['deskripsi_template'], ENT_QUOTES) ?>"
         data-deadline="<?= $p['deadline'] ?>"
         data-kategori="<?= htmlspecialchars($p['kategori'] ?? '', ENT_QUOTES) ?>">
        
        <div class="pay-card-head" onclick="toggleCard(this)">
            <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
            <div class="info">
                <h3 class="title"><?= htmlspecialchars($p['nama_pembayaran']) ?></h3>
                <div class="kode">
    <i class="fas fa-hashtag"></i><?= $p['kode_transaksi'] ?>
    <?php if(!empty($p['kategori'])): ?>
        <span style="margin-left:8px;color:var(--ink-muted)">· <?= htmlspecialchars($p['kategori']) ?></span>
    <?php endif; ?>
    <?php if(!empty($p['pay_slug'])): ?>
        <span style="margin-left:8px;color:var(--ink-muted);font-family:'JetBrains Mono',monospace">/<?= htmlspecialchars($p['pay_slug']) ?></span>
    <?php endif; ?>
</div>
            </div>
            <?php if($deadline_info): ?>
            <span class="deadline-badge <?= $is_urgent ? 'urgent' : '' ?>">
                <i class="fas fa-clock"></i> <?= $deadline_info ?>
            </span>
            <?php endif; ?>
            <div class="status-pill <?= $p['status'] ?>"><?= strtoupper($p['status']) ?></div>
            <div class="nominal"><?= formatRupiah($p['nominal']) ?></div>
        </div>
        
        <div class="pay-card-body">
            
            <div class="pay-actions">
                <button class="btn-mini" onclick="openForm(<?= $p['id'] ?>)">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <a class="btn-mini danger" href="../backend/proses_pembayaran.php?action=delete&id=<?= $p['id'] ?>" onclick="return confirm('Hapus pembayaran ini? Semua data mahasiswa akan terhapus.')">
                    <i class="fas fa-trash"></i> Hapus
                </a>
                <button class="btn-mini" onclick="bulkStatus(<?= $p['id'] ?>, 'berhasil')" style="color:var(--emerald);border-color:var(--emerald)">
                    <i class="fas fa-check-double"></i> ACC Semua
                </button>
                <button class="btn-mini" onclick="bulkStatus(<?= $p['id'] ?>, 'pending')">
                    <i class="fas fa-undo"></i> Reset Semua
                </button>
            </div>
            
            <div class="info-box">
                <div class="section-label"><i class="fas fa-info-circle"></i> Informasi</div>
                <div class="info-row">
                    <span class="lbl">Kategori</span>
                    <span class="val"><?= htmlspecialchars($p['kategori'] ?: '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="lbl">Deskripsi</span>
                    <span class="val" style="font-weight:500;font-size:.78rem;color:var(--ink-2);white-space:pre-wrap"><?= htmlspecialchars($p['deskripsi_template']) ?></span>
                </div>
                <?php if(!empty($p['deadline'])): ?>
                <div class="info-row">
                    <span class="lbl">Deadline</span>
                    <span class="val font-mono"><?= date('d M Y', strtotime($p['deadline'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="info-box">
                <div class="section-label"><i class="fas fa-credit-card"></i> Channel Pembayaran (<?= count($p['channels']) ?>)</div>
                <div class="channels-grid">
                    <?php foreach($p['channels'] as $c): ?>
                    <div class="channel-card">
                        <div class="jenis"><i class="fas fa-wallet"></i> <?= htmlspecialchars($c['jenis']) ?></div>
                        <?php if($c['nomor_tujuan']): ?>
                            <div class="nomor"><?= htmlspecialchars($c['nomor_tujuan']) ?></div>
                        <?php endif; ?>
                        <?php if($c['nama_penerima']): ?>
                            <div class="nama">a/n <?= htmlspecialchars($c['nama_penerima']) ?></div>
                        <?php endif; ?>
                        <?php if($c['qris_image'] && file_exists('../assets/img/qris/' . $c['qris_image'])): ?>
                            <img src="../assets/img/qris/<?= $c['qris_image'] ?>" 
                                 onclick="previewImg('../assets/img/qris/<?= $c['qris_image'] ?>')"
                                 alt="QRIS">
                        <?php endif; ?>
                        <?php if($c['catatan']): ?>
                            <div style="font-size:.68rem;color:var(--ink-muted);margin-top:6px"><?= htmlspecialchars($c['catatan']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mhs-section">
                <div class="mhs-section-label">
                    <span><i class="fas fa-users"></i> Status Mahasiswa</span>
                    <span class="counter"><?= $lunas ?> / <?= $total ?> berhasil</span>
                </div>
                
                <?php foreach($p['mhs'] as $m): 
                    $status = $m['status'] ?: 'pending';
                    $fotoPath = (!empty($m['foto']) && $m['foto'] !== 'default.jpg' && $m['foto'] !== 'default.png') 
                        ? '../assets/img/mahasiswa/' . $m['foto'] 
                        : 'https://ui-avatars.com/api/?background=random&name=' . urlencode($m['nama']);
                    $phone = normalizePhone($m['no_hp'] ?? '');
                    $hasWa = !empty($phone);
                    $paySlugFix = !empty($p['pay_slug']) ? $p['pay_slug'] : slugify($p['nama_pembayaran']);
$payUrl = $baseUrl . '/payment/' . $paySlugFix . '/' . ($m['slug'] ?? '');
                ?>
                <div class="mhs-row" data-user-id="<?= $m['id'] ?>">
                    <img src="<?= $fotoPath ?>" 
                         onerror="this.src='https://ui-avatars.com/api/?background=random&name=<?= urlencode($m['nama']) ?>'">
                    
                    <div class="mhs-info">
                        <p class="name"><?= htmlspecialchars($m['nama']) ?></p>
                        <p class="meta"><?= htmlspecialchars($m['nim']) ?> · <?= $m['no_hp'] ?: 'No HP kosong' ?></p>
<?php if(!empty($m['verified_at'])): ?>
<p class="meta" style="color:var(--accent);font-size:.62rem;margin-top:2px">
    <i class="fas fa-clock"></i> <?= date('d M Y, H:i', strtotime($m['verified_at'])) ?> WIB
</p>
<?php endif; ?>
                        <?php if($m['slug']): ?>
                        <div class="slug">
                            <i class="fas fa-link"></i>
                            <a href="<?= $payUrl ?>" target="_blank" style="font-family:'JetBrains Mono',monospace">/bayar/<?= $m['slug'] ?></a>
                            <button class="btn-mini" style="padding:2px 6px;font-size:.58rem" onclick="copyText('<?= $payUrl ?>', this)">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($m['bukti_bayar'])): ?>
                            <div style="font-size:.65rem;color:var(--emerald);margin-top:4px">
                                <i class="fas fa-paperclip"></i> 
                                <a href="../assets/img/bukti_bayar/<?= $m['bukti_bayar'] ?>" target="_blank" style="color:var(--emerald)">Lihat bukti bayar</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mhs-actions">
                        <span class="status-badge <?= $status ?>" data-status-badge>
                            <?php if($status === 'berhasil'): ?><i class="fas fa-check-circle"></i> ACC
                            <?php elseif($status === 'gagal'): ?><i class="fas fa-times-circle"></i> GAGAL
                            <?php elseif($status === 'dibebaskan'): ?><i class="fas fa-shield-alt"></i> BEBAS
                            <?php else: ?><i class="fas fa-clock"></i> PENDING
                            <?php endif; ?>
                        </span>
                        
                        <?php if($hasWa): ?>
                            <button class="wa-btn" onclick="kirimWA(this)"
                                data-nama="<?= htmlspecialchars($m['nama'], ENT_QUOTES) ?>"
                                data-nim="<?= htmlspecialchars($m['nim'], ENT_QUOTES) ?>"
                                data-phone="<?= $phone ?>"
                                data-template="<?= htmlspecialchars($p['deskripsi_template'], ENT_QUOTES) ?>"
                                data-nominal="<?= $p['nominal'] ?>"
                                data-nama-pembayaran="<?= htmlspecialchars($p['nama_pembayaran'], ENT_QUOTES) ?>"
                                data-kode="<?= htmlspecialchars($p['kode_transaksi'], ENT_QUOTES) ?>"
                                data-link="<?= $payUrl ?>"
                                data-deadline="<?= $p['deadline'] ?>"
                                data-kategori="<?= htmlspecialchars($p['kategori'] ?? '', ENT_QUOTES) ?>">
                                <i class="fab fa-whatsapp"></i> WA
                            </button>
                        <?php else: ?>
                            <button class="wa-btn disabled" disabled title="No HP belum diisi"><i class="fas fa-ban"></i></button>
                        <?php endif; ?>
                        
                        <button class="acc-btn <?= $status === 'berhasil' ? 'on' : '' ?>" 
                                onclick="setStatus(<?= $p['id'] ?>, <?= $m['id'] ?>, 'berhasil', this)">
                            <i class="fas fa-check"></i> ACC
                        </button>
                        <button class="fail-btn <?= $status === 'gagal' ? 'on' : '' ?>" 
                                onclick="setStatus(<?= $p['id'] ?>, <?= $m['id'] ?>, 'gagal', this)">
                            <i class="fas fa-times"></i> Gagal
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>

</div>

<!-- ============ MODAL FORM ============ -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Buat Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../backend/proses_pembayaran.php" method="POST" enctype="multipart/form-data" id="formPembayaran" style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden;">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="pay_id" value="">
                    <input type="hidden" name="channels" id="channels_json" value="[]">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Nama Pembayaran</label>
                            <input type="text" name="nama_pembayaran" id="f_nama" class="form-control" 
                                   placeholder="Fotocopy, Sempro, Study Tour..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="kategori" id="f_kategori" class="form-control" 
                                   placeholder="Semester 6" list="kategoriList">
                            <datalist id="kategoriList">
                                <option value="Semester 6">
                                <option value="Sempro">
                                <option value="Wisuda">
                                <option value="Praktikum">
                            </datalist>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Template WA</label>
                        <textarea name="deskripsi_template" id="f_deskripsi" class="form-control" rows="6" required
                                  placeholder="Halo {nama} ({nim}), silakan melakukan pembayaran {nama_pembayaran} sebesar {nominal}."></textarea>
                        <div class="form-text">Klik chip untuk insert placeholder — auto-replace saat kirim WA:</div>
                        <div class="hint-chips">
                            <span class="hint-chip" onclick="insertPlaceholder('{nama}')">{nama}</span>
                            <span class="hint-chip" onclick="insertPlaceholder('{nim}')">{nim}</span>
                            <span class="hint-chip" onclick="insertPlaceholder('{nominal}')">{nominal}</span>
                            <span class="hint-chip" onclick="insertPlaceholder('{nama_pembayaran}')">{nama_pembayaran}</span>
                            <span class="hint-chip" onclick="insertPlaceholder('{kode_transaksi}')">{kode_transaksi}</span>
                            <span class="hint-chip" onclick="insertPlaceholder('{link_pembayaran}')">{link_pembayaran}</span>
                            <span class="hint-chip" onclick="insertPlaceholder('{deadline}')">{deadline}</span>
                            <span class="hint-chip" onclick="insertPlaceholder('{kategori}')">{kategori}</span>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="number" name="nominal" id="f_nominal" class="form-control" 
                                   placeholder="15000" required min="0" step="500">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deadline (Opsional)</label>
                            <input type="date" name="deadline" id="f_deadline" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="f_status" class="form-select">
                            <option value="aktif">🟢 Aktif</option>
                            <option value="nonaktif">🔴 Nonaktif</option>
                            <option value="ditutup">⛔ Ditutup</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Channel Pembayaran</label>
                        <div id="channelsContainer"></div>
                        <button type="button" class="btn-mini" onclick="addChannelRow()">
                            <i class="fas fa-plus"></i> Tambah Channel
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-mini" data-bs-dismiss="modal" style="padding:10px 20px;font-size:.75rem">Batal</button>
                    <button type="submit" class="btn-mini" style="background:var(--accent);color:var(--accent-on);border-color:var(--accent);padding:10px 24px;font-size:.75rem;font-weight:800">
                        <i class="fas fa-save"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL IMG -->
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:transparent!important;border:none!important">
            <img id="imgPreview" src="" style="width:100%">
        </div>
    </div>
</div>

<div class="toast-container" id="toastBox"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleTheme() {
    const html = document.documentElement;
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    document.getElementById('themeIcon').className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}
(function() {
    const t = localStorage.getItem('theme') || 'light';
    document.getElementById('themeIcon').className = t === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
})();

let bsModal;
document.addEventListener('DOMContentLoaded', () => {
    bsModal = new bootstrap.Modal(document.getElementById('formModal'));
});

function openForm(id = null) {
    const form = document.getElementById('formPembayaran');
    form.reset();
    document.getElementById('pay_id').value = '';
    document.getElementById('channelsContainer').innerHTML = '';
    document.getElementById('modalTitle').textContent = 'Buat Pembayaran';
    document.querySelector('#formModal .modal-body').scrollTop = 0;
    
    if (id) {
        fetch('../backend/proses_pembayaran.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                document.getElementById('pay_id').value = data.id;
                document.getElementById('f_nama').value = data.nama_pembayaran;
                document.getElementById('f_deskripsi').value = data.deskripsi_template;
                document.getElementById('f_kategori').value = data.kategori || '';
                document.getElementById('f_nominal').value = data.nominal;
                document.getElementById('f_deadline').value = data.deadline || '';
                document.getElementById('f_status').value = data.status;
                document.getElementById('modalTitle').textContent = 'Edit Pembayaran';
                
                if (data.channels && data.channels.length > 0) {
                    data.channels.forEach(c => addChannelRow(c));
                } else {
                    addChannelRow();
                }
                
                bsModal.show();
                setTimeout(() => document.querySelector('#formModal .modal-body').scrollTop = 0, 100);
            });
        return;
    }
    
    addChannelRow();
    bsModal.show();
    setTimeout(() => document.querySelector('#formModal .modal-body').scrollTop = 0, 100);
}

function addChannelRow(data = null) {
    const container = document.getElementById('channelsContainer');
    const idx = container.children.length;
    const html = `
        <div class="channel-form-row" data-idx="${idx}">
            <button type="button" class="remove-ch" onclick="this.parentElement.remove(); syncChannels();">
                <i class="fas fa-times"></i>
            </button>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.6rem">Jenis</label>
                    <select class="form-select ch-jenis" style="font-size:.8rem;padding:6px 10px" required>
                        <option value="">-- Pilih --</option>
                        <option ${data?.jenis==='DANA'?'selected':''}>DANA</option>
                        <option ${data?.jenis==='GoPay'?'selected':''}>GoPay</option>
                        <option ${data?.jenis==='OVO'?'selected':''}>OVO</option>
                        <option ${data?.jenis==='ShopeePay'?'selected':''}>ShopeePay</option>
                        <option ${data?.jenis==='QRIS Merchant'?'selected':''}>QRIS Merchant</option>
                        <option ${data?.jenis==='Transfer Bank'?'selected':''}>Transfer Bank</option>
                        <option ${data?.jenis==='Lainnya'?'selected':''}>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.6rem">Nomor Tujuan</label>
                    <input type="text" class="form-control ch-nomor" style="font-size:.8rem;padding:6px 10px" 
                           value="${data?.nomor_tujuan || ''}" placeholder="0812xxxx">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.6rem">Atas Nama</label>
                    <input type="text" class="form-control ch-penerima" style="font-size:.8rem;padding:6px 10px" 
                           value="${data?.nama_penerima || ''}" placeholder="Nama Penerima">
                </div>
                <div class="col-md-8">
                    <label class="form-label" style="font-size:.6rem">Catatan (Opsional)</label>
                    <input type="text" class="form-control ch-catatan" style="font-size:.8rem;padding:6px 10px" 
                           value="${data?.catatan || ''}" placeholder="Info tambahan">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.6rem">QRIS (Opsional)</label>
                    <input type="file" class="form-control ch-qris" accept="image/*" name="qris_${idx}" 
                           style="font-size:.75rem;padding:4px 8px">
                </div>
            </div>
            <input type="hidden" class="ch-qris-lama" value="${data?.qris_image || ''}">
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    syncChannels();
}

function syncChannels() {
    const channels = [];
    document.querySelectorAll('.channel-form-row').forEach((row, i) => {
        const jenis = row.querySelector('.ch-jenis').value;
        if (!jenis) return;
        channels.push({
            jenis: jenis,
            nomor_tujuan: row.querySelector('.ch-nomor').value,
            nama_penerima: row.querySelector('.ch-penerima').value,
            catatan: row.querySelector('.ch-catatan').value,
            qris_image: row.querySelector('.ch-qris-lama').value
        });
        const fileInput = row.querySelector('.ch-qris');
        if (fileInput) fileInput.name = 'qris_' + i;
    });
    document.getElementById('channels_json').value = JSON.stringify(channels);
}

document.addEventListener('input', e => {
    if (e.target.closest('.channel-form-row')) syncChannels();
});
document.addEventListener('change', e => {
    if (e.target.closest('.channel-form-row')) syncChannels();
});

function insertPlaceholder(text) {
    const ta = document.getElementById('f_deskripsi');
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    ta.value = ta.value.slice(0, start) + text + ta.value.slice(end);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = start + text.length;
}

function toggleCard(el) {
    el.closest('.pay-card').classList.toggle('open');
}

function kirimWA(btn) {
    const nama = btn.dataset.nama;
    const nim = btn.dataset.nim;
    const phone = btn.dataset.phone;
    let template = btn.dataset.template;
    const nominal = parseInt(btn.dataset.nominal);
    const namaPembayaran = btn.dataset.namaPembayaran;
    const kode = btn.dataset.kode;
    const link = btn.dataset.link;
    const deadline = btn.dataset.deadline;
    const kategori = btn.dataset.kategori;
    
    const nominalFmt = 'Rp ' + nominal.toLocaleString('id-ID');
    const deadlineFmt = deadline ? new Date(deadline).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}) : '-';
    
    let text = template
        .replace(/{nama}/g, nama)
        .replace(/{nim}/g, nim)
        .replace(/{nominal}/g, nominalFmt)
        .replace(/{nominal_plain}/g, nominal.toLocaleString('id-ID'))
        .replace(/{nama_pembayaran}/g, namaPembayaran)
        .replace(/{kode_transaksi}/g, kode)
        .replace(/{link_pembayaran}/g, link)
        .replace(/{deadline}/g, deadlineFmt)
        .replace(/{kategori}/g, kategori || '-');
    
    const url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}

function setStatus(pembayaranId, userId, status, btn) {
    const fd = new FormData();
    fd.append('action', 'set_status');
    fd.append('pembayaran_id', pembayaranId);
    fd.append('user_id', userId);
    fd.append('status', status);
    
    fetch('../backend/proses_pembayaran.php', {method: 'POST', body: fd})
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const row = btn.closest('.mhs-row');
                const badge = row.querySelector('[data-status-badge]');
                const accBtn = row.querySelector('.acc-btn');
                const failBtn = row.querySelector('.fail-btn');
                
                badge.className = 'status-badge ' + status;
                accBtn.classList.remove('on');
                failBtn.classList.remove('on');
                
                if (status === 'berhasil') {
                    badge.innerHTML = '<i class="fas fa-check-circle"></i> ACC';
                    accBtn.classList.add('on');
                    showToast('✓ ACC: ' + row.querySelector('.name').textContent);
                } else if (status === 'gagal') {
                    badge.innerHTML = '<i class="fas fa-times-circle"></i> GAGAL';
                    failBtn.classList.add('on');
                    showToast('✗ Gagal: ' + row.querySelector('.name').textContent);
                }
                updateCounter(pembayaranId);
            }
        })
        .catch(() => showToast('Kesalahan jaringan', true));
}

function bulkStatus(payId, status) {
    const label = status === 'berhasil' ? 'ACC semua mahasiswa?' : 'Reset semua ke pending?';
    if (!confirm(label)) return;
    
    const fd = new FormData();
    fd.append('action', 'bulk_status');
    fd.append('pembayaran_id', payId);
    fd.append('status', status);
    
    fetch('../backend/proses_pembayaran.php', {method: 'POST', body: fd})
        .then(() => { showToast('✓ Berhasil update semua'); location.reload(); });
}

function updateCounter(payId) {
    const card = document.querySelector(`.pay-card[data-pay-id="${payId}"]`);
    if (!card) return;
    const total = card.querySelectorAll('.mhs-row').length;
    const done = card.querySelectorAll('.status-badge.berhasil').length;
    const counter = card.querySelector('.mhs-section-label .counter');
    if (counter) counter.textContent = `${done} / ${total} berhasil`;
}

function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('📋 Tersalin!');
    });
}

function previewImg(src) {
    document.getElementById('imgPreview').src = src;
    new bootstrap.Modal(document.getElementById('imgModal')).show();
}

function showToast(msg, isError) {
    const box = document.getElementById('toastBox');
    const t = document.createElement('div');
    t.className = 'toast-item' + (isError ? ' error' : '');
    t.innerHTML = msg;
    box.appendChild(t);
    setTimeout(() => t.remove(), 2500);
    const all = box.querySelectorAll('.toast-item');
    if (all.length > 3) all[0].remove();
}
</script>
</body>
</html>