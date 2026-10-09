<?php 
include '../koneksi.php';
include '../backend/pembayaran_helper.php';
date_default_timezone_set('Asia/Jakarta');

$DEBUG = false;

function avatarSvg($nama, $size = 200) {
    $parts = preg_split('/\s+/', trim($nama));
    if (count($parts) >= 2) {
        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    } else {
        $initials = strtoupper(substr($parts[0] ?? '?', 0, 2));
    }
    $colors = ['#0B7A3E', '#2563EB', '#7C3AED', '#DB2777', '#DC2626', '#F59E0B', '#EA580C', '#0891B2'];
    $color = $colors[abs(crc32($nama)) % count($colors)];
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">'
         . '<rect width="'.$size.'" height="'.$size.'" fill="'.$color.'"/>'
         . '<text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" '
         . 'font-family="system-ui,sans-serif" font-size="'.($size*0.42).'" '
         . 'font-weight="800" fill="#FFFFFF">'.htmlspecialchars($initials).'</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function getPaymentLogo($jenis) {
    $j = strtolower(trim($jenis));
    $map = ['dana'=>'dana','gopay'=>'gopay','go-pay'=>'gopay','gojek'=>'gopay','ovo'=>'ovo','shopeepay'=>'shopeepay','shopee'=>'shopeepay','qris'=>'qris'];
    $fileBase = null;
    foreach ($map as $key => $file) {
        if (strpos($j, $key) !== false) { $fileBase = $file; break; }
    }
    if (!$fileBase) return null;
    $exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    foreach ($exts as $ext) {
        $path = __DIR__ . '/../assets/img/payment/' . $fileBase . '.' . $ext;
        if (file_exists($path)) return '/assets/img/payment/' . $fileBase . '.' . $ext;
    }
    return null;
}

function logoFallbackSvg($jenis, $size = 80) {
    $j = strtolower(trim($jenis));
    if (strpos($j, 'dana') !== false) $brand = ['#118EEA', 'DANA'];
    elseif (strpos($j, 'gopay') !== false || strpos($j, 'go-pay') !== false || strpos($j, 'gojek') !== false) $brand = ['#00AED6', 'gopay'];
    elseif (strpos($j, 'ovo') !== false) $brand = ['#4C3494', 'OVO'];
    elseif (strpos($j, 'shopee') !== false) $brand = ['#EE4D2D', 'Shopee'];
    elseif (strpos($j, 'qris') !== false) $brand = ['#E30613', 'QRIS'];
    else $brand = ['#0B7A3E', strtoupper(substr($jenis, 0, 6))];
    $bg = $brand[0]; $text = $brand[1];
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">'
         . '<rect width="'.$size.'" height="'.$size.'" rx="'.($size*0.22).'" fill="'.$bg.'"/>'
         . '<text x="50%" y="52%" text-anchor="middle" dominant-baseline="central" '
         . 'font-family="system-ui,sans-serif" font-size="'.($size*0.26).'" font-weight="800" fill="#FFF">'
         . htmlspecialchars($text).'</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function getBrandLogo($jenis, $size = 80) {
    $file = getPaymentLogo($jenis);
    if ($file) return $file;
    return logoFallbackSvg($jenis, $size);
}

$paySlug = mysqli_real_escape_string($koneksi, $_GET['pay'] ?? '');
$slug = mysqli_real_escape_string($koneksi, $_GET['slug'] ?? '');

if (empty($slug)) { http_response_code(404); die('Link tidak valid'); }

if (empty($paySlug)) {
    $qr = mysqli_query($koneksi, "SELECT p.pay_slug FROM pembayaran_mhs pm JOIN pembayaran p ON pm.pembayaran_id = p.id WHERE pm.slug = '$slug'");
    if (mysqli_num_rows($qr) > 0) {
        $row = mysqli_fetch_assoc($qr);
        $ps = $row['pay_slug'] ?: 'pembayaran';
        header("Location: /payment/$ps/$slug", true, 301);
        exit;
    }
    http_response_code(404); die('Link tidak valid');
}

$q = mysqli_query($koneksi, "
    SELECT pm.*, p.nama_pembayaran, p.pay_slug, p.deskripsi_template, p.nominal, p.deadline, 
           p.kategori, p.kode_transaksi, p.status AS pay_status, p.id AS pay_id,
           u.nama, u.nim, u.foto
    FROM pembayaran_mhs pm
    JOIN pembayaran p ON pm.pembayaran_id = p.id
    JOIN users u ON pm.user_id = u.id
    WHERE pm.slug = '$slug'
");
if (mysqli_num_rows($q) == 0) { http_response_code(404); die('Transaksi tidak ditemukan'); }
$data = mysqli_fetch_assoc($q);

$channels = [];
$qc = mysqli_query($koneksi, "SELECT * FROM pembayaran_channel WHERE pembayaran_id='{$data['pay_id']}' ORDER BY urutan ASC");
while ($c = mysqli_fetch_assoc($qc)) $channels[] = $c;

$q_stat = mysqli_query($koneksi, "SELECT COUNT(*) as total, SUM(CASE WHEN status='berhasil' THEN 1 ELSE 0 END) as paid FROM pembayaran_mhs WHERE pembayaran_id='{$data['pay_id']}'");
$stat = mysqli_fetch_assoc($q_stat);
$paid_count = (int)($stat['paid'] ?? 0);
$total_count = (int)($stat['total'] ?? 0);

$paid_list = [];
$q_pl = mysqli_query($koneksi, "SELECT u.nama, u.foto, u.nim, pm.verified_at FROM pembayaran_mhs pm JOIN users u ON pm.user_id=u.id WHERE pm.pembayaran_id='{$data['pay_id']}' AND pm.status='berhasil' ORDER BY pm.verified_at DESC LIMIT 15");
while ($r = mysqli_fetch_assoc($q_pl)) $paid_list[] = $r;

$admin_wa = '';
$q_wa = mysqli_query($koneksi, "SELECT nilai FROM pengaturan_jadwal WHERE nama_setting='admin_wa_pembayaran'");
if ($q_wa && mysqli_num_rows($q_wa) > 0) $admin_wa = mysqli_fetch_assoc($q_wa)['nilai'];
if (empty($admin_wa)) {
    foreach ($channels as $c) {
        if (!empty($c['nomor_tujuan'])) { $admin_wa = normalizePhone($c['nomor_tujuan']); break; }
    }
}

$fotoPath = '';
if (!empty($data['foto']) && !in_array($data['foto'], ['default.jpg','default.png','default.jpeg',''])) {
    $fullPath = __DIR__ . '/../assets/img/mahasiswa/' . $data['foto'];
    if (file_exists($fullPath)) $fotoPath = '/assets/img/mahasiswa/' . $data['foto'];
}
if (empty($fotoPath)) $fotoPath = avatarSvg($data['nama'], 200);

$deskripsiRender = renderTemplate($data['deskripsi_template'] ?? '', [
    'nama'            => $data['nama'],
    'nim'             => $data['nim'],
    'nominal'         => $data['nominal'],
    'nama_pembayaran' => $data['nama_pembayaran'],
    'kode_transaksi'  => $data['kode_transaksi'],
    'link_pembayaran' => getBaseUrl() . '/payment/' . $data['pay_slug'] . '/' . $slug,
    'deadline'        => $data['deadline'],
    'kategori'        => $data['kategori'],
]);

$is_lunas = $data['status'] === 'berhasil';
$is_gagal = $data['status'] === 'gagal';
$is_bebas = $data['status'] === 'dibebaskan';

$namaDepan = explode(' ', trim($data['nama']))[0];
$nominalFmt = number_format((int)$data['nominal'], 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pembayaran — <?= htmlspecialchars($data['nama']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
:root {
    --green:#0B7A3E; --green-dark:#0A6B36; --red:#DC2626; --amber:#F59E0B;
    --ink:#0A0A0A; --ink-2:#4A4A4A; --muted:#8A8A8A;
    --border:#ECECEC; --bg:#F7F7F7; --card:#FFF;
    --shadow: 0 1px 2px rgba(0,0,0,.03), 0 1px 3px rgba(0,0,0,.04);
}
body.dark {
    --green:#22C55E; --green-dark:#16A34A;
    --ink:#F5F5F5; --ink-2:#B0B0B0; --muted:#707070;
    --border:#2A2A2A; --bg:#0A0A0A; --card:#161616;
    --shadow: 0 1px 2px rgba(0,0,0,.4);
}
html,body{overflow-x:hidden}
body{
    font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
    background:var(--bg);color:var(--ink);font-size:15px;line-height:1.5;
    min-height:100vh;-webkit-font-smoothing:antialiased;
    transition:background-color .3s, color .3s;
}
.wrap{max-width:440px;margin:0 auto;padding:20px 16px 40px}

/* ========== NOTIFICATION POPUP ========== */
.notif-container{
    position:fixed;top:0;left:0;right:0;
    z-index:500;pointer-events:none;
    display:flex;flex-direction:column;align-items:center;
    padding:12px 12px 0;
}
.notif{
    background:var(--card);
    border-radius:16px;
    box-shadow:0 8px 30px -6px rgba(0,0,0,.15), 0 2px 8px -2px rgba(0,0,0,.08);
    padding:12px 16px 12px 12px;
    display:flex;align-items:center;gap:12px;
    max-width:400px;width:100%;
    border:1px solid var(--border);
    position:relative;overflow:hidden;
    transform:translateY(-120%);opacity:0;
    transition:transform .55s cubic-bezier(.16,1,.3,1), opacity .4s;
    pointer-events:auto;
}
.notif.show{transform:translateY(0);opacity:1;}
.notif.hide{
    transform:translateY(-120%);opacity:0;
    transition:transform .5s cubic-bezier(.4,0,.6,1), opacity .35s;
}
.notif-avatar-wrap{position:relative;flex-shrink:0;}
.notif-avatar{
    width:44px;height:44px;border-radius:50%;object-fit:cover;
    background:var(--border);display:block;
}
.notif-check{
    position:absolute;bottom:-2px;right:-2px;
    width:18px;height:18px;border-radius:50%;
    background:var(--green);color:#FFF;
    display:flex;align-items:center;justify-content:center;
    font-size:.55rem;border:2px solid var(--card);
    box-shadow:0 1px 3px rgba(0,0,0,.15);
}
.notif-body{flex:1;min-width:0}
.notif-name{
    font-size:.82rem;font-weight:800;color:var(--ink);
    margin-bottom:2px;line-height:1.2;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.notif-text{
    font-size:.7rem;color:var(--muted);font-weight:500;
    line-height:1.3;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.notif-text b{color:var(--green);font-weight:700}
.notif-time{
    font-size:.6rem;color:var(--muted);font-weight:600;
    font-family:"SF Mono",Consolas,monospace;
    margin-top:2px;letter-spacing:.3px;
}
.notif-progress{
    position:absolute;bottom:0;left:0;
    height:2px;background:var(--green);
    width:0;
    transition:width var(--notif-duration, 3s) linear;
    opacity:.6;
}
.notif.show .notif-progress{width:100%;}

/* ========== HEADER BAR ========== */
.header-bar{
    display:flex;align-items:center;justify-content:space-between;
    padding:12px 0 20px;gap:12px;
}
.header-back{
    width:38px;height:38px;border-radius:12px;
    background:var(--card);border:1px solid var(--border);
    color:var(--ink);display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:.85rem;transition:all .15s;text-decoration:none;
}
.header-back:hover{background:var(--ink);color:var(--card)}
.header-title{flex:1;text-align:center}
.header-title .t1{
    font-size:.68rem;font-weight:700;color:var(--muted);
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:2px;
}
.header-title .t2{
    font-size:.95rem;font-weight:800;color:var(--ink);
    letter-spacing:-.01em;
}
.header-theme{
    width:38px;height:38px;border-radius:12px;
    background:var(--card);border:1px solid var(--border);
    color:var(--ink);display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:.85rem;transition:all .15s;
}
.header-theme:hover{background:var(--ink);color:var(--card)}

/* ========== HERO ========== */
.hero{
    background:var(--card);border-radius:24px;padding:28px 24px;
    margin-bottom:16px;box-shadow:var(--shadow);
    text-align:center;
}
.hero-avatar{
    width:72px;height:72px;border-radius:50%;object-fit:cover;
    margin:0 auto 14px;display:block;background:var(--border);
}
.hero-name{
    font-size:1rem;font-weight:700;color:var(--ink);margin-bottom:2px;
    letter-spacing:-.01em;word-break:break-word;
}
.hero-nim{
    font-size:.72rem;color:var(--muted);font-weight:600;
    font-family:"SF Mono",Consolas,monospace;letter-spacing:.3px;
    margin-bottom:20px;
}
.hero-divider{height:1px;background:var(--border);margin-bottom:20px}

/* KETERANGAN — dashed box */
.desc-box{
    border: 1.5px dashed var(--border);
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 18px;
    text-align: left;
    background: color-mix(in srgb, var(--green) 4%, transparent);
}
.desc-box-head{
    font-size:.6rem;font-weight:700;color:var(--green);
    text-transform:uppercase;letter-spacing:1px;
    margin-bottom:4px;display:flex;align-items:center;gap:5px;
}
.desc-box-head i{font-size:.65rem}
.desc-box-body{
    font-size:.72rem;line-height:1.5;color:var(--ink-2);
    font-weight:500;white-space:pre-wrap;word-break:break-word;
}

.hero-amount-lbl{
    font-size:.68rem;font-weight:700;color:var(--muted);
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;
}
.hero-amount{
    font-size:2.4rem;font-weight:800;color:var(--ink);
    letter-spacing:-.03em;line-height:1;
    display:flex;align-items:baseline;justify-content:center;gap:6px;
    font-variant-numeric:tabular-nums;
}
.hero-amount .cur{font-size:1rem;font-weight:600;color:var(--muted);letter-spacing:0}
.hero-title{
    font-size:.85rem;font-weight:600;color:var(--ink-2);
    margin-top:10px;margin-bottom:18px;
}
.hero-status{
    display:inline-flex;align-items:center;gap:6px;
    font-family:"SF Mono",Consolas,monospace;
    font-size:.65rem;font-weight:700;
    padding:6px 14px;border-radius:50px;
    text-transform:uppercase;letter-spacing:.5px;
}
.hero-status.ok{background:#DCFCE7;color:#166534}
.hero-status.no{background:#FEE2E2;color:#991B1B}
.hero-status.wait{background:#FEF3C7;color:#92400E}
.hero-status.free{background:#E0E7FF;color:#4338CA}

/* ========== ACTION BUTTONS (2 TOMBOL) ========== */
.action-buttons{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
    margin-bottom:16px;
}
.act-btn{
    padding:14px 12px;
    border-radius:14px;
    border:none;
    font-family:inherit;
    font-size:.82rem;
    font-weight:700;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    transition:all .2s;
    position:relative;
    overflow:hidden;
}
.act-btn i{font-size:.9rem}

/* Tombol Bayar - Primary */
.act-btn.pay{
    background:var(--green);color:#FFF;
    box-shadow:0 4px 14px -4px color-mix(in srgb, var(--green) 50%, transparent);
}
.act-btn.pay:hover{
    background:var(--green-dark);
    transform:translateY(-1px);
    box-shadow:0 6px 18px -4px color-mix(in srgb, var(--green) 55%, transparent);
}
.act-btn.pay:active{transform:translateY(0)}
.act-btn.pay.open{background:var(--green-dark)}

/* Tombol Lihat Bayar - Secondary */
.act-btn.view{
    background:var(--card);color:var(--ink);
    border:1.5px solid var(--border);
}
.act-btn.view:hover{
    border-color:var(--green);
    color:var(--green);
    transform:translateY(-1px);
}
.act-btn.view:active{transform:translateY(0)}
.act-btn.view.open{
    background:var(--ink);
    color:var(--bg);
    border-color:var(--ink);
}
.act-btn .badge{
    background:color-mix(in srgb, var(--green) 15%, transparent);
    color:var(--green);
    font-size:.65rem;
    font-weight:800;
    padding:2px 7px;
    border-radius:50px;
    font-family:"SF Mono",Consolas,monospace;
    line-height:1;
}
.act-btn.view.open .badge{
    background:rgba(255,255,255,.2);
    color:#FFF;
}

/* ========== EXPANDABLE SECTIONS ========== */
.expandable{
    max-height:0;
    overflow:hidden;
    opacity:0;
    margin-bottom:0;
    transition: max-height .55s cubic-bezier(.16,1,.3,1), 
                opacity .35s ease, 
                margin-bottom .35s ease;
}
.expandable.on{
    max-height:1500px;
    opacity:1;
    margin-bottom:16px;
}

.section-head{
    display:flex;align-items:center;justify-content:space-between;
    margin-bottom:12px;padding:0 4px;
}
.section-title{
    font-size:.85rem;font-weight:700;color:var(--ink);
    letter-spacing:-.01em;
}
.section-sub{
    font-size:.68rem;font-weight:600;color:var(--muted);
    font-family:"SF Mono",Consolas,monospace;
}

/* ========== METHODS ========== */
.methods{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.method{
    background:var(--card);border:1.5px solid var(--border);
    border-radius:16px;padding:18px 12px;
    display:flex;flex-direction:column;align-items:center;gap:12px;
    cursor:pointer;font-family:inherit;
    transition:all .15s ease;
    box-shadow:var(--shadow);
}
.method:hover{
    border-color:var(--green);
    box-shadow:0 4px 12px -2px rgba(11,122,62,.15);
    transform:translateY(-1px);
}
.method:active{transform:translateY(0)}
.method-logo{
    width:56px;height:56px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    background:var(--card);border-radius:12px;overflow:hidden;
}
.method-logo img{width:100%;height:100%;object-fit:contain}
.method-name{
    font-size:.82rem;font-weight:700;color:var(--ink);
    text-align:center;line-height:1.2;
}

/* ========== PAID LIST (EXPANDABLE) ========== */
.paid-list-card{
    background:var(--card);border-radius:16px;padding:16px;
    box-shadow:var(--shadow);
}
.paid-item{
    display:flex;align-items:center;gap:12px;
    padding:10px 0;border-bottom:1px solid var(--border);
}
.paid-item:first-child{padding-top:0}
.paid-item:last-child{border-bottom:none;padding-bottom:0}
.paid-item img{
    width:38px;height:38px;border-radius:50%;object-fit:cover;
    flex-shrink:0;background:var(--border);
}
.paid-item-info{flex:1;min-width:0}
.paid-item-name{
    font-size:.82rem;font-weight:700;color:var(--ink);
    margin-bottom:2px;word-break:break-word;line-height:1.2;
}
.paid-item-time{
    font-size:.65rem;color:var(--muted);
    font-family:"SF Mono",Consolas,monospace;
}
.paid-item-check{
    width:22px;height:22px;border-radius:50%;
    background:#DCFCE7;color:#16A34A;
    display:flex;align-items:center;justify-content:center;
    font-size:.65rem;flex-shrink:0;
}
.paid-empty{
    text-align:center;padding:24px 16px;color:var(--muted);
    font-size:.78rem;font-style:italic;
}

/* ========== PROGRESS ========== */
.progress-simple{
    background:var(--card);border-radius:14px;padding:14px 18px;
    box-shadow:var(--shadow);margin-bottom:16px;
    display:flex;align-items:center;gap:14px;
}
.progress-bar-wrap{flex:1;height:6px;background:var(--border);border-radius:3px;overflow:hidden}
.progress-bar-fill{
    height:100%;background:var(--green);border-radius:3px;
    width:0;transition:width 1s cubic-bezier(.16,1,.3,1);
}
.progress-label{
    font-family:"SF Mono",Consolas,monospace;font-size:.7rem;
    font-weight:700;color:var(--green);white-space:nowrap;
}

/* ========== NOTICE ========== */
.notice{
    background:var(--card);border-radius:20px;padding:32px 24px;
    text-align:center;box-shadow:var(--shadow);margin-bottom:16px;
}
.notice-ic{
    width:64px;height:64px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:1.5rem;margin:0 auto 16px;
}
.notice.ok .notice-ic{background:#DCFCE7;color:#16A34A}
.notice.no .notice-ic{background:#FEE2E2;color:#DC2626}
.notice.warn .notice-ic{background:#FEF3C7;color:#F59E0B}
.notice.free .notice-ic{background:#E0E7FF;color:#4338CA}
.notice h3{font-size:1.05rem;font-weight:800;margin-bottom:6px;color:var(--ink)}
.notice p{font-size:.82rem;color:var(--muted);line-height:1.5}

/* ========== SHEET ========== */
.sheet-overlay{
    position:fixed;inset:0;background:rgba(0,0,0,.5);
    z-index:600;opacity:0;pointer-events:none;
    transition:opacity .3s;display:flex;align-items:flex-end;justify-content:center;
}
.sheet-overlay.on{opacity:1;pointer-events:auto}
.sheet{
    background:var(--bg);width:100%;max-width:520px;
    border-radius:24px 24px 0 0;padding:16px 20px 32px;
    max-height:92vh;overflow-y:auto;
    transform:translateY(100%);
    transition:transform .35s cubic-bezier(.16,1,.3,1);
    position:relative;
}
.sheet-overlay.on .sheet{transform:translateY(0)}
.sheet-handle{
    width:40px;height:4px;background:var(--border);border-radius:2px;
    margin:0 auto 16px;
}
.sheet-close{
    position:absolute;top:14px;right:16px;width:32px;height:32px;
    border-radius:50%;background:var(--border);color:var(--ink);
    border:none;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    font-size:.75rem;
}

/* ========== TIMER ========== */
.timer-simple{
    background:var(--card);border-radius:16px;padding:20px;
    text-align:center;margin-bottom:16px;box-shadow:var(--shadow);
}
.timer-simple-lbl{
    font-size:.68rem;font-weight:700;color:var(--muted);
    text-transform:uppercase;letter-spacing:1.2px;margin-bottom:8px;
}
.timer-simple-val{
    font-family:"SF Mono",Consolas,monospace;font-size:2rem;
    font-weight:700;color:var(--ink);letter-spacing:-.02em;
    line-height:1;font-variant-numeric:tabular-nums;
}
.timer-simple-val.warn{color:var(--amber)}
.timer-simple-val.danger{color:var(--red)}
.timer-simple-bar{
    height:3px;background:var(--border);border-radius:2px;
    overflow:hidden;margin-top:14px;
}
.timer-simple-fill{
    height:100%;background:var(--green);border-radius:2px;
    transition:width 1s linear, background-color .3s;
}
.timer-simple-fill.warn{background:var(--amber)}
.timer-simple-fill.danger{background:var(--red)}

.detail-head{
    display:flex;align-items:center;gap:14px;
    padding-bottom:16px;margin-bottom:16px;
    border-bottom:1px solid var(--border);
}
.detail-logo{
    width:52px;height:52px;flex-shrink:0;
    background:var(--card);border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    overflow:hidden;border:1px solid var(--border);
}
.detail-logo img{width:100%;height:100%;object-fit:contain}
.detail-info{flex:1;min-width:0}
.detail-lbl{
    font-size:.68rem;font-weight:600;color:var(--muted);
    text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;
}
.detail-name{font-size:.95rem;font-weight:700;color:var(--ink)}

.info-grid{
    background:var(--card);border-radius:14px;padding:16px;
    margin-bottom:14px;border:1px solid var(--border);
}
.info-row{
    display:flex;justify-content:space-between;align-items:center;
    padding:8px 0;border-bottom:1px dashed var(--border);
    font-size:.8rem;gap:10px;
}
.info-row:last-child{border-bottom:none;padding-bottom:0}
.info-row .lbl{
    color:var(--muted);font-weight:600;font-size:.72rem;flex-shrink:0;
}
.info-row .val{
    color:var(--ink);font-weight:700;text-align:right;
    word-break:break-word;
    font-family:"SF Mono",Consolas,monospace;font-size:.78rem;
}
.info-row .val.highlight{
    color:var(--green);font-weight:800;font-size:.9rem;
}

.qr-simple{
    background:var(--card);border-radius:16px;padding:20px;
    text-align:center;margin-bottom:14px;border:1px solid var(--border);
}
.qr-simple img{
    width:100%;max-width:260px;border-radius:12px;
    display:block;margin:0 auto;background:#FFF;
}
.qr-err{
    color:var(--red);font-weight:700;padding:24px 16px;
    font-size:.82rem;background:#FEF2F2;border-radius:10px;
    border:1px dashed #FCA5A5;
}
.pay-with{
    display:flex;align-items:center;justify-content:center;gap:10px;
    padding:12px;background:var(--card);border-radius:12px;
    margin-bottom:12px;border:1px solid var(--border);
}
.pay-with-lbl{font-size:.75rem;color:var(--muted);font-weight:600}
.pay-with-logo{height:28px;width:auto;max-width:80px;object-fit:contain}

.copy-box{
    background:var(--card);border-radius:14px;padding:16px;
    margin-bottom:12px;border:1px solid var(--border);
}
.copy-lbl{
    font-size:.65rem;font-weight:700;color:var(--muted);
    text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;
}
.copy-val{display:flex;align-items:center;justify-content:space-between;gap:10px}
.copy-num{
    font-family:"SF Mono",Consolas,monospace;font-size:1.1rem;
    font-weight:700;color:var(--ink);letter-spacing:.3px;
    word-break:break-all;
}
.copy-btn{
    background:var(--ink);color:var(--bg);
    border:none;width:36px;height:36px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;flex-shrink:0;font-size:.8rem;transition:all .15s;
}
.copy-btn:hover{background:var(--green);color:#FFF}
.copy-btn:active{transform:scale(.95)}
.copy-name{
    font-size:.72rem;color:var(--muted);margin-top:8px;font-weight:500;
}

.warn-box{
    background:#FFFBEB;border:1px solid #FDE68A;border-radius:12px;
    padding:14px;display:flex;gap:10px;align-items:flex-start;
    font-size:.8rem;color:#92400E;line-height:1.5;font-weight:600;
}
body.dark .warn-box{
    background:rgba(245,158,11,.1);
    border-color:rgba(245,158,11,.3);
    color:#FCD34D;
}
.warn-box i{color:var(--amber);margin-top:2px;flex-shrink:0;font-size:.85rem}
.warn-box b{font-family:"SF Mono",Consolas,monospace;font-weight:800}
.note{
    background:var(--card);border-radius:10px;padding:12px 14px;
    font-size:.8rem;color:var(--ink-2);line-height:1.5;
    margin-top:12px;font-style:italic;border:1px solid var(--border);
}

.cta{
    width:100%;padding:16px;border-radius:14px;border:none;
    background:var(--green);color:#FFF;font-family:inherit;
    font-size:.95rem;font-weight:700;cursor:pointer;
    display:flex;align-items:center;justify-content:center;gap:10px;
    transition:background .15s;
}
.cta:hover{background:var(--green-dark)}
.cta:active{transform:scale(.99)}

.foot{
    text-align:center;padding:28px 0 8px;
    font-size:.68rem;color:var(--muted);
    font-weight:600;letter-spacing:.5px;text-transform:uppercase;
}
.foot i{color:var(--green);margin-right:4px}

.modal-ov{
    position:fixed;inset:0;background:rgba(0,0,0,.55);
    display:none;align-items:center;justify-content:center;
    padding:20px;z-index:1000;
}
.modal-ov.on{display:flex}
.modal-box{
    background:var(--card);border-radius:24px;
    padding:32px 24px;max-width:340px;width:100%;text-align:center;
}
.modal-ic{
    width:64px;height:64px;border-radius:50%;
    background:#FEE2E2;color:var(--red);
    display:flex;align-items:center;justify-content:center;
    font-size:1.6rem;margin:0 auto 20px;
}
.modal-box h3{font-size:1.1rem;font-weight:800;margin-bottom:8px;color:var(--ink)}
.modal-box p{font-size:.82rem;color:var(--muted);line-height:1.5;margin-bottom:24px}
.btn-reload{
    width:100%;padding:14px;border-radius:12px;border:none;
    background:var(--green);color:#FFF;font-family:inherit;
    font-size:.9rem;font-weight:700;cursor:pointer;
    display:flex;align-items:center;justify-content:center;gap:8px;
}

.toast{
    position:fixed;left:50%;bottom:24px;
    transform:translateX(-50%) translateY(80px);
    background:var(--ink);color:var(--bg);
    padding:12px 22px;border-radius:50px;
    font-size:.8rem;font-weight:600;opacity:0;
    transition:all .3s ease;z-index:2000;pointer-events:none;
    display:flex;align-items:center;gap:8px;
}
.toast.on{opacity:1;transform:translateX(-50%) translateY(0)}
.toast i{color:#4ADE80;font-size:.85rem}

@media (max-width:400px){
    .hero-amount{font-size:2rem}
    .wrap{padding:16px 14px 40px}
    .notif{padding:10px 14px 10px 10px}
    .notif-avatar{width:40px;height:40px}
    .notif-name{font-size:.78rem}
    .notif-text{font-size:.65rem}
    .act-btn{font-size:.75rem;padding:12px 8px;gap:6px}
    .act-btn i{font-size:.8rem}
    .act-btn .badge{font-size:.6rem;padding:2px 6px}
}
</style>
</head>
<body>

<!-- NOTIFICATION CONTAINER -->
<div class="notif-container" id="notifContainer"></div>

<div class="wrap">

<!-- HEADER BAR -->
<div class="header-bar">
    <a href="javascript:history.back()" class="header-back"><i class="fas fa-arrow-left"></i></a>
    <div class="header-title">
        <div class="t1">Halaman Pembayaran</div>
        <div class="t2">PSIK 23 D</div>
    </div>
    <button class="header-theme" onclick="toggleDark()" title="Toggle Theme">
        <i class="fas fa-moon" id="darkIcon"></i>
    </button>
</div>

<!-- HERO / PROFILE -->
<div class="hero">
    <img class="hero-avatar" 
         src="<?= htmlspecialchars($fotoPath) ?>" 
         alt="<?= htmlspecialchars($data['nama']) ?>"
         onerror="this.onerror=null;this.src='<?= avatarSvg($data['nama'], 200) ?>'">
    <div class="hero-name"><?= htmlspecialchars($data['nama']) ?></div>
    <div class="hero-nim"><?= htmlspecialchars($data['nim']) ?></div>
    <div class="hero-divider"></div>

    <!-- KETERANGAN -->
    <?php if(!empty(trim($deskripsiRender)) && !$is_lunas && !$is_bebas): ?>
    <div class="desc-box">
        <div class="desc-box-head"><i class="fas fa-info-circle"></i> Keterangan</div>
        <div class="desc-box-body"><?= nl2br(htmlspecialchars($deskripsiRender)) ?></div>
    </div>
    <?php endif; ?>

    <div class="hero-amount-lbl">Total Tagihan Kamu</div>
    <div class="hero-amount">
        <span class="cur">Rp</span>
        <span><?= $nominalFmt ?></span>
    </div>
    <div class="hero-title"><?= htmlspecialchars($data['nama_pembayaran']) ?></div>
    <?php if($is_lunas): ?>
        <span class="hero-status ok">✓ LUNAS</span>
    <?php elseif($is_gagal): ?>
        <span class="hero-status no">✗ GAGAL</span>
    <?php elseif($is_bebas): ?>
        <span class="hero-status free">★ BEBAS</span>
    <?php else: ?>
        <span class="hero-status wait">⏱ PENDING</span>
    <?php endif; ?>
</div>

<?php if($is_lunas): ?>
    <div class="notice ok">
        <div class="notice-ic"><i class="fas fa-check"></i></div>
        <h3>Pembayaran Lunas</h3>
        <p>Terima kasih sudah menyelesaikan pembayaran! 🎉</p>
    </div>
<?php elseif($is_bebas): ?>
    <div class="notice free">
        <div class="notice-ic"><i class="fas fa-shield-alt"></i></div>
        <h3>Kamu Dibebaskan</h3>
        <p>Kamu tidak perlu membayar tagihan ini.</p>
    </div>
<?php elseif(empty($channels)): ?>
    <div class="notice warn">
        <div class="notice-ic"><i class="fas fa-exclamation"></i></div>
        <h3>Belum Ada Metode</h3>
        <p>Admin belum menambahkan metode pembayaran.</p>
    </div>
<?php else: ?>

    <!-- ACTION BUTTONS — 2 TOMBOL SEJAJAR -->
    <div class="action-buttons">
        <button class="act-btn pay" id="btnPay" onclick="togglePay()">
            <i class="fas fa-credit-card"></i>
            <span>Bayar Sekarang</span>
        </button>
        <button class="act-btn view" id="btnView" onclick="togglePaid()">
            <i class="fas fa-users"></i>
            <span>Sudah Bayar</span>
            <span class="badge"><?= $paid_count ?></span>
        </button>
    </div>

    <!-- METHODS EXPANDABLE -->
    <div class="expandable" id="methodsContainer">
        <div class="section-head">
            <div class="section-title">Pilih Metode Pembayaran</div>
            <div class="section-sub"><?= count($channels) ?> metode</div>
        </div>
        <div class="methods">
            <?php foreach($channels as $c): 
                $logoUrl = getBrandLogo($c['jenis'], 80);
            ?>
            <button class="method" onclick="pilihMetode(<?= (int)$c['id'] ?>)">
                <div class="method-logo">
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($c['jenis']) ?>">
                </div>
                <div class="method-name"><?= htmlspecialchars($c['jenis']) ?></div>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PAID LIST EXPANDABLE -->
    <div class="expandable" id="paidContainer">
        <div class="section-head">
            <div class="section-title">Sudah Bayar</div>
            <div class="section-sub"><?= $paid_count ?> dari <?= $total_count ?></div>
        </div>
        <div class="paid-list-card">
            <div id="paidListContainer">
            <?php if(empty($paid_list)): ?>
                <div class="paid-empty">Belum ada yang bayar. Jadi yang pertama! 🚀</div>
            <?php else: 
                foreach($paid_list as $p):
                    $pFoto = '';
                    if (!empty($p['foto']) && !in_array($p['foto'], ['default.jpg','default.png','default.jpeg',''])) {
                        $pFull = __DIR__ . '/../assets/img/mahasiswa/' . $p['foto'];
                        if (file_exists($pFull)) $pFoto = '/assets/img/mahasiswa/' . $p['foto'];
                    }
                    if (empty($pFoto)) $pFoto = avatarSvg($p['nama'], 100);
            ?>
                <div class="paid-item">
                    <img src="<?= htmlspecialchars($pFoto) ?>" alt=""
                         onerror="this.onerror=null;this.src='<?= avatarSvg($p['nama'], 100) ?>'">
                    <div class="paid-item-info">
                        <div class="paid-item-name"><?= htmlspecialchars($p['nama']) ?></div>
                        <div class="paid-item-time"><?= !empty($p['verified_at']) ? date('d M Y, H:i', strtotime($p['verified_at'])) . ' WIB' : 'Terverifikasi' ?></div>
                    </div>
                    <div class="paid-item-check"><i class="fas fa-check"></i></div>
                </div>
            <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

<?php endif; ?>

<!-- PROGRESS -->
<?php if($total_count > 0 && !$is_lunas && !$is_bebas): ?>
<div class="progress-simple">
    <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="progressFill" style="width:<?= round($paid_count / max($total_count, 1) * 100) ?>%"></div>
    </div>
    <div class="progress-label"><?= $paid_count ?>/<?= $total_count ?> bayar</div>
</div>
<?php endif; ?>

<div class="foot"><i class="fas fa-lock"></i>Transaksi Aman · PSIK 23 D</div>

</div>

<!-- SHEET DETAIL -->
<div class="sheet-overlay" id="sheetOverlay">
    <div class="sheet" id="sheet">
        <div class="sheet-handle"></div>
        <button class="sheet-close" onclick="tutupSheet()"><i class="fas fa-times"></i></button>
        
        <div class="timer-simple">
            <div class="timer-simple-lbl">Selesaikan Dalam</div>
            <div class="timer-simple-val" id="timerVal">05:00</div>
            <div class="timer-simple-bar">
                <div class="timer-simple-fill" id="timerFill" style="width:100%"></div>
            </div>
        </div>

        <div class="detail-head">
            <div class="detail-logo" id="payLogo"></div>
            <div class="detail-info">
                <div class="detail-lbl">Metode Pembayaran</div>
                <div class="detail-name" id="payName">—</div>
            </div>
        </div>
        
        <div id="payBody"></div>

        <button class="cta" onclick="sayaBayar()">
            <i class="fas fa-check-circle"></i> Saya Sudah Bayar
        </button>
    </div>
</div>

<div class="modal-ov" id="expiredOv">
    <div class="modal-box">
        <div class="modal-ic"><i class="fas fa-hourglass-end"></i></div>
        <h3>Waktu Habis</h3>
        <p>Sesi pembayaran sudah berakhir. Silakan muat ulang halaman.</p>
        <button class="btn-reload" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Muat Ulang
        </button>
    </div>
</div>

<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastTxt">Berhasil</span></div>

<script>
const CHANNELS = <?= json_encode(array_map(function($c) {
    return [
        'id' => (int)$c['id'],
        'jenis' => $c['jenis'],
        'nomor' => $c['nomor_tujuan'],
        'penerima' => $c['nama_penerima'],
        'qris' => $c['qris_image'],
        'catatan' => $c['catatan'],
        'logo_url' => getBrandLogo($c['jenis'], 80)
    ];
}, $channels)) ?>;

const PAY = {
    nama: <?= json_encode($data['nama']) ?>,
    nim: <?= json_encode($data['nim']) ?>,
    nominal: <?= json_encode($nominalFmt) ?>,
    kode: <?= json_encode($data['kode_transaksi']) ?>,
    pembayaran: <?= json_encode($data['nama_pembayaran']) ?>,
    adminWa: <?= json_encode($admin_wa) ?>
};

const PAID_LIST = <?= json_encode(array_map(function($p) {
    $foto = '';
    if (!empty($p['foto']) && !in_array($p['foto'], ['default.jpg','default.png','default.jpeg',''])) {
        $fullPath = __DIR__ . '/../assets/img/mahasiswa/' . $p['foto'];
        if (file_exists($fullPath)) $foto = '/assets/img/mahasiswa/' . $p['foto'];
    }
    if (empty($foto)) $foto = avatarSvg($p['nama'], 100);
    
    return [
        'nama' => $p['nama'],
        'foto' => $foto,
        'verified_at' => $p['verified_at'],
        'verified_fmt' => !empty($p['verified_at']) 
            ? date('d M Y, H:i', strtotime($p['verified_at'])) 
            : 'Baru saja'
    ];
}, $paid_list)) ?>;

let selected = null, timerInt = null, timeLeft = 300;
const TOTAL = 300;

// ⚙️ CONFIG NOTIFIKASI
const NOTIF_CONFIG = {
    initialDelay: 2000,
    showDuration: 3000,
    betweenDelay: 6000,
    exitDuration: 500,
};

// ========== DARK MODE ==========
function initDark() {
    const saved = localStorage.getItem('payment-dark');
    if (saved === '1') {
        document.body.classList.add('dark');
        document.getElementById('darkIcon').className = 'fas fa-sun';
    }
}
function toggleDark() {
    document.body.classList.toggle('dark');
    const isDark = document.body.classList.contains('dark');
    localStorage.setItem('payment-dark', isDark ? '1' : '0');
    document.getElementById('darkIcon').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    showToast(isDark ? '🌙 Dark mode' : '☀️ Light mode');
}
initDark();

// ========== TOGGLE PAY / PAID ==========
function togglePay() {
    const methods = document.getElementById('methodsContainer');
    const btnPay = document.getElementById('btnPay');
    const paid = document.getElementById('paidContainer');
    const btnView = document.getElementById('btnView');
    
    const isOpen = methods.classList.contains('on');
    
    // Tutup yang lain dulu
    paid.classList.remove('on');
    btnView.classList.remove('open');
    
    if (isOpen) {
        methods.classList.remove('on');
        btnPay.classList.remove('open');
    } else {
        methods.classList.add('on');
        btnPay.classList.add('open');
        setTimeout(() => methods.scrollIntoView({behavior:'smooth', block:'start'}), 200);
    }
}

function togglePaid() {
    const paid = document.getElementById('paidContainer');
    const btnView = document.getElementById('btnView');
    const methods = document.getElementById('methodsContainer');
    const btnPay = document.getElementById('btnPay');
    
    const isOpen = paid.classList.contains('on');
    
    // Tutup yang lain dulu
    methods.classList.remove('on');
    btnPay.classList.remove('open');
    
    if (isOpen) {
        paid.classList.remove('on');
        btnView.classList.remove('open');
    } else {
        paid.classList.add('on');
        btnView.classList.add('open');
        setTimeout(() => paid.scrollIntoView({behavior:'smooth', block:'start'}), 200);
    }
}

// ========== NOTIFICATION POPUP ==========
let notifIndex = 0;
let notifTimeout = null;
let currentNotifEl = null;

function showNextNotif() {
    if (PAID_LIST.length === 0) return;
    const p = PAID_LIST[notifIndex];
    
    if (currentNotifEl) {
        currentNotifEl.classList.remove('show');
        currentNotifEl.classList.add('hide');
        const old = currentNotifEl;
        setTimeout(() => old.remove(), NOTIF_CONFIG.exitDuration);
    }

    const el = document.createElement('div');
    el.className = 'notif';
    el.style.setProperty('--notif-duration', (NOTIF_CONFIG.showDuration / 1000) + 's');
    
    const namaShort = p.nama.split(' ').slice(0, 2).join(' ');
    
    el.innerHTML = `
        <div class="notif-avatar-wrap">
            <img class="notif-avatar" src="${p.foto}" alt="" 
                 onerror="this.src='https://ui-avatars.com/api/?background=0B7A3E&color=fff&name=${encodeURIComponent(p.nama)}'">
            <div class="notif-check"><i class="fas fa-check"></i></div>
        </div>
        <div class="notif-body">
            <div class="notif-name">${escHtml(namaShort)}</div>
            <div class="notif-text">berhasil melakukan pembayaran <b>${escHtml(PAY.pembayaran)}</b></div>
            <div class="notif-time">${escHtml(p.verified_fmt)} WIB</div>
        </div>
        <div class="notif-progress"></div>
    `;
    
    document.getElementById('notifContainer').appendChild(el);
    currentNotifEl = el;
    
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            el.classList.add('show');
        });
    });
    
    notifTimeout = setTimeout(() => {
        el.classList.remove('show');
        el.classList.add('hide');
        setTimeout(() => {
            el.remove();
            if (currentNotifEl === el) currentNotifEl = null;
        }, NOTIF_CONFIG.exitDuration);
        
        notifIndex = (notifIndex + 1) % PAID_LIST.length;
        
        setTimeout(() => {
            showNextNotif();
        }, NOTIF_CONFIG.betweenDelay);
    }, NOTIF_CONFIG.showDuration);
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// ========== OPEN SHEET ==========
function pilihMetode(id) {
    const m = CHANNELS.find(x => x.id === id);
    if (!m) return;
    selected = m;

    document.getElementById('payLogo').innerHTML = `<img src="${m.logo_url}" alt="">`;
    document.getElementById('payName').textContent = m.jenis;

    let h = '';
    
    h += `<div class="info-grid">
        <div class="info-row">
            <span class="lbl">ID Transaksi</span>
            <span class="val">${escHtml(PAY.kode)}</span>
        </div>
        <div class="info-row">
            <span class="lbl">Nominal</span>
            <span class="val highlight">Rp ${escHtml(PAY.nominal)}</span>
        </div>
        <div class="info-row">
            <span class="lbl">Metode</span>
            <span class="val">${escHtml(m.jenis)}</span>
        </div>
    </div>`;
    
    if (m.qris) {
        const qrisUrl = '/assets/img/qris/' + m.qris;
        h += `<div class="qr-simple">
            <img src="${qrisUrl}" alt="QRIS" 
                 onerror="this.onerror=null;this.outerHTML='<div class=&quot;qr-err&quot;><i class=&quot;fas fa-exclamation-triangle&quot;></i><br>QRIS gagal dimuat</div>'">
        </div>`;
        h += `<div class="pay-with">
            <span class="pay-with-lbl">Bayar menggunakan</span>
            <img class="pay-with-logo" src="${m.logo_url}" alt="">
        </div>`;
    }
    
    if (m.nomor) {
        h += `<div class="copy-box">
            <div class="copy-lbl">Nomor Tujuan</div>
            <div class="copy-val">
                <span class="copy-num">${escHtml(m.nomor)}</span>
                <button class="copy-btn" onclick="copyIt('${escJs(m.nomor)}')"><i class="fas fa-copy"></i></button>
            </div>
            ${m.penerima ? `<div class="copy-name">a/n ${escHtml(m.penerima)}</div>` : ''}
        </div>`;
    }
    
    if (m.catatan) h += `<div class="note">${escHtml(m.catatan)}</div>`;
    
    h += `<div class="warn-box"><i class="fas fa-exclamation-triangle"></i><div>Transfer tepat <b>Rp ${escHtml(PAY.nominal)}</b> agar mudah diverifikasi</div></div>`;

    document.getElementById('payBody').innerHTML = h;
    document.getElementById('sheetOverlay').classList.add('on');
    document.body.style.overflow = 'hidden';
    startTimer();
}

function tutupSheet() {
    document.getElementById('sheetOverlay').classList.remove('on');
    document.body.style.overflow = '';
    if (timerInt) clearInterval(timerInt);
    selected = null;
}

// ========== TIMER ==========
function startTimer() {
    timeLeft = TOTAL;
    updateTimer();
    if (timerInt) clearInterval(timerInt);
    timerInt = setInterval(() => {
        timeLeft--;
        updateTimer();
        if (timeLeft <= 0) {
            clearInterval(timerInt);
            document.getElementById('expiredOv').classList.add('on');
        }
    }, 1000);
}

function updateTimer() {
    const m = Math.floor(timeLeft / 60), s = timeLeft % 60;
    const el = document.getElementById('timerVal');
    const fill = document.getElementById('timerFill');
    if (!el || !fill) return;
    
    el.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    fill.style.width = (timeLeft / TOTAL * 100) + '%';
    
    el.classList.remove('warn','danger');
    fill.classList.remove('warn','danger');
    if (timeLeft <= 60) { el.classList.add('danger'); fill.classList.add('danger'); }
    else if (timeLeft <= 120) { el.classList.add('warn'); fill.classList.add('warn'); }
}

// ========== SAYA SUDAH BAYAR ==========
function sayaBayar() {
    if (!selected) { showToast('Pilih metode dulu'); return; }
    if (!PAY.adminWa) { showToast('Nomor admin belum di-set'); return; }
    let msg = `Halo Admin, saya sudah melakukan pembayaran:\n\n`;
    msg += `📌 *${PAY.pembayaran}*\n`;
    msg += `🆔 ${PAY.nim} — ${PAY.nama}\n`;
    msg += `💰 Rp ${PAY.nominal}\n`;
    msg += `🔖 ${PAY.kode}\n`;
    msg += `💳 ${selected.jenis}`;
    if (selected.nomor) msg += ` → ${selected.nomor}`;
    msg += `\n\nSilakan di cek ya, terima kasih 🙏`;
    window.open(`https://wa.me/${PAY.adminWa}?text=${encodeURIComponent(msg)}`, '_blank');
}

// ========== COPY ==========
function copyIt(text) {
    const fb = () => {
        const t = document.createElement('textarea');
        t.value = text; document.body.appendChild(t);
        t.select(); document.execCommand('copy'); t.remove();
        showToast('📋 Nomor tersalin!');
    };
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => showToast('📋 Nomor tersalin!')).catch(fb);
    } else fb();
}

// ========== LIVE POLLING ==========
function pollCounter() {
    fetch(location.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(r => r.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            
            const newList = doc.querySelector('#paidListContainer');
            const oldList = document.querySelector('#paidListContainer');
            if (newList && oldList && newList.innerHTML !== oldList.innerHTML) {
                oldList.innerHTML = newList.innerHTML;
            }
            
            const newFill = doc.querySelector('#progressFill');
            const oldFill = document.querySelector('#progressFill');
            if (newFill && oldFill && newFill.style.width !== oldFill.style.width) {
                oldFill.style.width = newFill.style.width;
            }
            
            // Update badge di tombol kanan
            const newBadge = doc.querySelector('#btnView .badge');
            const oldBadge = document.querySelector('#btnView .badge');
            if (newBadge && oldBadge && newBadge.textContent !== oldBadge.textContent) {
                oldBadge.textContent = newBadge.textContent;
            }
        })
        .catch(() => {});
}

// ========== TOAST ==========
function showToast(msg) {
    const t = document.getElementById('toast');
    document.getElementById('toastTxt').textContent = msg;
    t.classList.add('on');
    clearTimeout(t._to);
    t._to = setTimeout(() => t.classList.remove('on'), 2200);
}

// ========== ESC / BACKDROP ==========
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') tutupSheet();
});
document.getElementById('sheetOverlay').addEventListener('click', e => {
    if (e.target.id === 'sheetOverlay') tutupSheet();
});

// ========== HELPERS ==========
function escJs(s) { if(!s) return ''; return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }

// ========== INIT ==========
window.addEventListener('load', () => {
    if (PAID_LIST.length > 0) {
        setTimeout(showNextNotif, NOTIF_CONFIG.initialDelay);
    }
    setTimeout(pollCounter, 5000);
    setInterval(pollCounter, 15000);
});
</script>

</body>
</html>