<?php 
session_start();
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil semua sesi + mahasiswa di dalamnya
$q_sesi = mysqli_query($koneksi, "
    SELECT s.*,
           (SELECT COUNT(*) FROM sempro_mhs m WHERE m.sesi_id = s.id) AS total_mhs
    FROM sempro_sesi s
    ORDER BY s.hari_tanggal ASC, s.pukul_sesi ASC
");
$semua_sesi = [];
$total_mhs = 0;
$daftar_tanggal = [];
while($row = mysqli_fetch_assoc($q_sesi)) {
    $qm = mysqli_query($koneksi, "SELECT * FROM sempro_mhs WHERE sesi_id='{$row['id']}' ORDER BY pukul_mhs ASC");
    $mhs_list = [];
    while($m = mysqli_fetch_assoc($qm)) { $mhs_list[] = $m; $total_mhs++; }
    $row['mhs_list'] = $mhs_list;
    $semua_sesi[] = $row;
    if(!in_array($row['hari_tanggal'], $daftar_tanggal)) $daftar_tanggal[] = $row['hari_tanggal'];
}
$total_hari = count($semua_sesi);

// Helper untuk label tanggal
$bulan_indo_short = [1=>'JAN','FEB','MAR','APR','MEI','JUN','JUL','AGU','SEP','OKT','NOV','DES'];
$bulan_indo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$hari_indo = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jadwal Sempro — FMIPA UNIMED</title>
<script>
    (function(){
        const t = localStorage.getItem('sempro-theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
    })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* ============ EXPAND TOGGLE ============ */
.expand-toggle{
    display:flex;align-items:center;justify-content:space-between;
    width:100%;margin-top:14px;padding:11px 16px;
    background:var(--bg);border:1.5px dashed var(--border);
    color:var(--accent);cursor:pointer;
    font-family:'JetBrains Mono',monospace;font-size:.7rem;
    font-weight:700;text-transform:uppercase;letter-spacing:1.2px;
    transition:all .2s;
}
.expand-toggle:hover{
    background:color-mix(in srgb,var(--accent) 10%,var(--bg));
    border-color:var(--accent);
    border-style:solid;
}
.expand-toggle .et-label{
    display:inline-flex;align-items:center;gap:7px;
}
.expand-toggle .et-label i{
    font-size:.85rem;
}
.expand-toggle .et-chevron{
    width:16px;height:16px;
    transition:transform .3s cubic-bezier(.16,1,.3,1);
}
.expand-toggle.open{
    background:var(--accent);color:var(--accent-on);
    border-color:var(--accent);border-style:solid;
}
.expand-toggle.open .et-chevron{
    transform:rotate(180deg);
}
*,*::before,*::after{box-sizing:border-box}
:root,[data-theme="light"]{
    --bg:#F4F8F1;--bg-alt:#E7EFE3;--card:#FFFFFF;
    --ink:#0A1A0F;--ink-2:#3D4F42;--ink-muted:#7A8A7F;
    --border:#0A1A0F;--border-soft:rgba(10,26,15,.12);
    --accent:#0B7A3E;--accent-2:#14A34E;--accent-on:#FFFFFF;
    --live:#16A34A;--done:#94A3B8;--dot-bg:rgba(11,122,62,.06);
}
[data-theme="dark"]{
    --bg:#0A140D;--bg-alt:#142019;--card:#0F1B14;
    --ink:#E8F5EC;--ink-2:#A8C4B2;--ink-muted:#6B8577;
    --border:#E8F5EC;--border-soft:rgba(232,245,236,.12);
    --accent:#22C55E;--accent-2:#4ADE80;--accent-on:#0A140D;
    --live:#10B981;--done:#6B7280;--dot-bg:rgba(34,197,94,.06);
}
html,body{margin:0;padding:0;overflow-x:hidden;width:100%;max-width:100%}
body{
    background-color:var(--bg);
    background-image:radial-gradient(circle,var(--dot-bg) 1px,transparent 1px);
    background-size:24px 24px;
    color:var(--ink);font-family:'Plus Jakarta Sans',system-ui,sans-serif;
    font-size:15px;line-height:1.5;-webkit-font-smoothing:antialiased;
    transition:background-color .25s,color .25s;
}
img,svg,video{max-width:100%;height:auto}
.entry>*,.toolbar-inner>*,.letterhead-inner>*{min-width:0}
.container{max-width:1080px;margin:0 auto;padding:0 24px;width:100%}

/* ============ SCROLL PROGRESS ============ */
.scroll-progress{
    position:fixed;top:0;left:0;height:3px;background:var(--accent);
    z-index:9999;width:0%;transition:width .08s linear;
    box-shadow:0 0 8px color-mix(in srgb,var(--accent) 60%,transparent);
}

/* ============ LETTERHEAD ============ */
.letterhead{background:var(--card);border-bottom:3px double var(--border);padding:22px 0 16px;transition:background-color .25s,border-color .25s}
.letterhead-inner{display:flex;align-items:center;gap:22px}
.letterhead-logo{width:92px;height:92px;object-fit:contain;flex-shrink:0}
.letterhead-text{flex:1;text-align:center;line-height:1.25;min-width:0}
.letterhead-text .uni,.letterhead-text .fak,.letterhead-text .jur{font-family:'Instrument Serif',serif;font-weight:400;letter-spacing:.5px;color:var(--ink);margin:0}
.letterhead-text .uni{font-size:1.3rem}
.letterhead-text .fak{font-size:1.15rem}
.letterhead-text .jur{font-size:1.15rem;margin-bottom:6px}
.letterhead-text .addr,.letterhead-text .web{font-family:'Plus Jakarta Sans',sans-serif;font-size:.72rem;font-weight:500;color:var(--ink-2);margin:0;letter-spacing:.2px;overflow-wrap:anywhere}

/* ============ TOPBAR ============ */
.topbar{padding:14px 0;border-bottom:1.5px solid var(--border);transition:border-color .25s}
.topbar-inner{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.nav-badge{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--accent);padding:6px 12px;border:1px dashed color-mix(in srgb,var(--accent) 40%,transparent);background:color-mix(in srgb,var(--accent) 6%,transparent)}
.topbar-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.icon-btn{
    font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;text-transform:uppercase;
    letter-spacing:.8px;color:var(--ink);padding:8px 12px;border:1.5px solid var(--border);
    background:var(--card);cursor:pointer;display:inline-flex;align-items:center;gap:6px;
    transition:all .15s;white-space:nowrap
}
.icon-btn:hover{background:var(--accent);color:var(--accent-on);border-color:var(--accent)}
.icon-btn svg{width:14px;height:14px}
.icon-btn .lbl{display:inline}

/* ============ LIVE BAR ============ */
.live-bar{
    position:sticky;top:0;z-index:49;
    background:var(--live);color:#FFF;
    display:none;align-items:center;gap:14px;
    padding:10px 0;
    font-family:'JetBrains Mono',monospace;
    overflow:hidden;
}
.live-bar.show{display:flex;animation:slideDown .4s ease}
@keyframes slideDown{from{transform:translateY(-100%);opacity:0}to{transform:translateY(0);opacity:1}}
.live-bar-inner{display:flex;align-items:center;gap:14px;width:100%}
.live-bar .pulse{
    width:9px;height:9px;border-radius:50%;background:#FFF;
    box-shadow:0 0 0 0 rgba(255,255,255,.7);animation:pulseWhite 1.5s infinite;flex-shrink:0
}
@keyframes pulseWhite{0%{box-shadow:0 0 0 0 rgba(255,255,255,.6)}70%{box-shadow:0 0 0 10px rgba(255,255,255,0)}100%{box-shadow:0 0 0 0 rgba(255,255,255,0)}}
.live-bar .live-label{font-size:.65rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:3px 8px;background:rgba(0,0,0,.25);border-radius:3px}
.live-bar .live-items{flex:1;overflow:hidden;position:relative;min-width:0}
.live-bar .live-item{font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:pointer;transition:opacity .3s}
.live-bar .live-item:hover{text-decoration:underline}
.live-bar .live-item .mhs-name-live{font-weight:700}
.live-bar .live-item .sep{opacity:.6;margin:0 8px}

/* ============ HERO ============ */
.hero{padding:60px 0 56px;border-bottom:1.5px solid var(--border);position:relative;overflow:hidden}
#particleCanvas{position:absolute;inset:0;pointer-events:none;z-index:0;opacity:.5}
.hero .container{position:relative;z-index:1}
.hero-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:1.2px;color:var(--ink-2);gap:16px;flex-wrap:wrap}
.hero-tag{display:inline-flex;align-items:center;gap:10px}
.hero-tag .dot{width:8px;height:8px;border-radius:50%;background:var(--accent);animation:pulseDot 2s infinite}
@keyframes pulseDot{0%{box-shadow:0 0 0 0 color-mix(in srgb,var(--accent) 60%,transparent)}70%{box-shadow:0 0 0 8px transparent}100%{box-shadow:0 0 0 0 transparent}}
.hero-stats{display:flex;gap:20px;color:var(--ink-muted)}
.hero-stats b{color:var(--accent);font-weight:700}
.hero h1{font-family:'Instrument Serif',serif;font-weight:400;font-size:clamp(3rem,9vw,6rem);line-height:.92;letter-spacing:-.02em;color:var(--ink);margin:0 0 20px}
.hero h1 em{font-style:italic;color:var(--accent)}
.hero p{font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:500;text-transform:uppercase;letter-spacing:2px;color:var(--ink-muted);line-height:1.7;margin:0;max-width:420px}

/* ============ TOOLBAR ============ */
.toolbar{position:sticky;top:0;z-index:48;background:color-mix(in srgb,var(--bg) 92%,transparent);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-bottom:1.5px solid var(--border);padding:14px 0;transition:top .3s}
body.has-live .toolbar{top:40px}
.toolbar-inner{display:flex;gap:12px;align-items:stretch;flex-wrap:wrap}
.search-wrap{position:relative;display:flex;align-items:center;background:var(--card);border:1.5px solid var(--border);flex:1;min-width:0;padding:0 14px;transition:box-shadow .15s,transform .15s}
.search-wrap:focus-within{box-shadow:4px 4px 0 var(--accent);transform:translate(-2px,-2px)}
.search-icon{width:18px;height:18px;flex-shrink:0;color:var(--accent)}
.search-wrap input{flex:1;min-width:0;border:none;outline:none;background:transparent;padding:13px 10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:.88rem;color:var(--ink)}
.search-wrap input::placeholder{color:var(--ink-muted)}
.search-hint{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;padding:3px 7px;border:1px solid var(--border-soft);color:var(--ink-muted);background:var(--bg-alt);flex-shrink:0;letter-spacing:.5px}

/* DATE FILTER DROPDOWN */
.date-filter{position:relative;display:inline-flex;align-items:center;background:var(--card);border:1.5px solid var(--border);flex-shrink:0;padding:0 14px;min-width:200px;transition:box-shadow .15s,transform .15s}
.date-filter:focus-within{box-shadow:4px 4px 0 var(--accent);transform:translate(-2px,-2px)}
.date-filter svg{width:16px;height:16px;color:var(--accent);flex-shrink:0}
.date-filter select{
    flex:1;min-width:0;border:none;outline:none;background:transparent;
    padding:13px 8px;font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.5px;color:var(--ink);cursor:pointer;
    -webkit-appearance:none;-moz-appearance:none;appearance:none;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6' fill='none'><path d='M1 1l4 4 4-4' stroke='%230B7A3E' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/></svg>");
    background-repeat:no-repeat;background-position:right 4px center;padding-right:22px;
}
[data-theme="dark"] .date-filter select{
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6' fill='none'><path d='M1 1l4 4 4-4' stroke='%2322C55E' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/></svg>");
}
.date-filter select option{background:var(--card);color:var(--ink);font-family:'Plus Jakarta Sans',sans-serif;text-transform:none;letter-spacing:0;padding:8px}

.tabs{display:inline-flex;background:var(--card);border:1.5px solid var(--border);flex-shrink:0;overflow:hidden}
.tab{padding:13px 16px;font-family:'JetBrains Mono',monospace;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;background:transparent;border:none;border-right:1.5px solid var(--border);color:var(--ink);cursor:pointer;transition:all .12s;white-space:nowrap}
.tab:last-child{border-right:none}
.tab.active{background:var(--accent);color:var(--accent-on)}
.tab:hover:not(.active){background:var(--bg-alt)}
.view-toggle{display:inline-flex;background:var(--card);border:1.5px solid var(--border);flex-shrink:0;overflow:hidden}
.view-toggle .vt-btn{padding:13px 14px;background:transparent;border:none;border-right:1.5px solid var(--border);color:var(--ink);cursor:pointer;transition:all .12s;display:flex;align-items:center;justify-content:center}
.view-toggle .vt-btn:last-child{border-right:none}
.view-toggle .vt-btn.active{background:var(--accent);color:var(--accent-on)}
.view-toggle .vt-btn:hover:not(.active){background:var(--bg-alt)}
.view-toggle .vt-btn svg{width:16px;height:16px}

/* ============ TIMELINE NAV ============ */
.timeline-nav{
    position:fixed;left:20px;top:50%;transform:translateY(-50%);
    z-index:45;display:flex;flex-direction:column;gap:12px;
    padding:14px 8px;pointer-events:auto;
}
.timeline-nav .tn-dot{
    width:12px;height:12px;border-radius:50%;background:var(--bg-alt);
    border:2px solid var(--border-soft);cursor:pointer;transition:all .25s;
    position:relative
}
.timeline-nav .tn-dot::after{
    content:attr(data-name);position:absolute;left:24px;top:50%;transform:translateY(-50%);
    background:var(--accent);color:var(--accent-on);padding:4px 10px;font-family:'JetBrains Mono',monospace;
    font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
    white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .15s
}
.timeline-nav .tn-dot:hover::after{opacity:1}
.timeline-nav .tn-dot.upcoming{background:color-mix(in srgb,var(--accent) 30%,var(--bg-alt))}
.timeline-nav .tn-dot.live{background:var(--live);animation:pulseLiveDot 1.5s infinite}
.timeline-nav .tn-dot.done{background:var(--done);opacity:.5}
.timeline-nav .tn-dot.active{transform:scale(1.5);background:var(--accent)}
@keyframes pulseLiveDot{0%{box-shadow:0 0 0 0 var(--live)}70%{box-shadow:0 0 0 8px transparent}100%{box-shadow:0 0 0 0 transparent}}

/* ============ DATE SECTION ============ */
main{padding:40px 0 80px}
.date-section{margin-bottom:48px}
.date-header{display:flex;align-items:center;gap:20px;padding-bottom:20px;margin-bottom:24px;border-bottom:1.5px solid var(--border);flex-wrap:wrap}
.date-box{width:72px;height:72px;background:var(--accent);color:var(--accent-on);display:flex;flex-direction:column;justify-content:center;align-items:center;flex-shrink:0;line-height:1}
.date-box .day{font-family:'Instrument Serif',serif;font-size:2rem;font-weight:400;line-height:.9}
.date-box .month{font-family:'JetBrains Mono',monospace;font-size:.65rem;font-weight:700;letter-spacing:2px;margin-top:4px;opacity:.85}
.date-info{flex:1;min-width:0}
.date-info h2{font-family:'Instrument Serif',serif;font-weight:400;font-size:1.5rem;margin:0 0 4px;color:var(--ink);letter-spacing:-.01em}
.date-info p{font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:500;text-transform:uppercase;letter-spacing:.5px;color:var(--ink-2);margin:0;word-break:break-word}
.date-info p .sep{color:var(--ink-muted);margin:0 6px}
.date-progress{margin-top:8px;display:flex;align-items:center;gap:10px}
.date-progress .bar{flex:1;height:4px;background:var(--bg-alt);border:1px solid var(--border-soft);overflow:hidden;max-width:200px}
.date-progress .bar-fill{height:100%;width:0;background:var(--accent);transition:width .8s cubic-bezier(.16,1,.3,1)}
.date-progress .bar-fill.full{background:var(--live)}
.date-progress .txt{font-family:'JetBrains Mono',monospace;font-size:.62rem;font-weight:700;color:var(--ink-muted);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
.date-count{text-align:right;font-family:'JetBrains Mono',monospace}
.date-count .num{display:block;font-size:1.5rem;font-weight:700;color:var(--accent);line-height:1}
.date-count .lbl{display:block;font-size:.65rem;font-weight:600;color:var(--ink-muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px}

/* ============ ENTRY ============ */
.entry{display:grid;grid-template-columns:64px minmax(0,1fr);gap:20px;margin-bottom:22px;align-items:start;position:relative}
.entry-num{font-family:'JetBrains Mono',monospace;font-size:2.6rem;font-weight:700;color:var(--accent);opacity:.22;line-height:1;padding-top:14px;text-align:right;user-select:none;transition:opacity .2s}
.entry:hover .entry-num{opacity:.55}
.entry::before{content:'';position:absolute;left:82px;top:0;bottom:-22px;width:1px;background:linear-gradient(to bottom,var(--border-soft) 0%,var(--border-soft) 50%,transparent 50%);background-size:1px 8px;opacity:.6;pointer-events:none}
.entry:last-child::before{display:none}
.entry.anchor-target{animation:anchorFlash 2s ease}
@keyframes anchorFlash{0%{background:color-mix(in srgb,var(--accent) 20%,transparent)}100%{background:transparent}}

.entry .ticket{
    background:var(--accent);padding:2px;min-width:0;
    clip-path:polygon(0 0,calc(100% - 34px) 0,100% 34px,100% 100%,0 100%);
    animation:ticketUnfold .7s cubic-bezier(.16,1,.3,1) both;
    transform-origin:top center;
}
@keyframes ticketUnfold{
    0%{opacity:0;transform:scaleY(.2) translateY(20px);clip-path:polygon(0 0,100% 0,100% 0,100% 0,0 0)}
    100%{opacity:1;transform:scaleY(1) translateY(0);clip-path:polygon(0 0,calc(100% - 34px) 0,100% 34px,100% 100%,0 100%)}
}
.ticket-inner{background:var(--card);clip-path:polygon(0 0,calc(100% - 32px) 0,100% 32px,100% 100%,0 100%);padding:28px 32px 24px;min-width:0;transition:background-color .25s;position:relative}

.anchor-btn{
    position:absolute;top:14px;right:14px;width:30px;height:30px;
    display:none;align-items:center;justify-content:center;
    background:var(--bg-alt);border:1.5px solid var(--accent);
    color:var(--accent);cursor:pointer;transition:all .15s;z-index:2;
}
.ticket-inner:hover .anchor-btn{display:flex}
.anchor-btn:hover{background:var(--accent);color:var(--accent-on)}
.anchor-btn svg{width:14px;height:14px}

.mhs-name{font-family:'Instrument Serif',serif;font-style:italic;font-weight:400;font-size:1.65rem;line-height:1.15;color:var(--ink);margin:0 0 6px;letter-spacing:-.01em;word-break:break-word}
.mhs-meta{font-family:'JetBrains Mono',monospace;font-size:.78rem;font-weight:500;color:var(--ink-2);margin:0 0 20px;letter-spacing:.2px;word-break:break-word}
.mhs-meta .nim{color:var(--accent);font-weight:700}
.mhs-meta .sep{color:var(--ink-muted);margin:0 6px}
.divider{height:1px;background:var(--border-soft);margin:0 0 20px}
.judul{font-family:'Instrument Serif',serif;font-style:italic;font-weight:400;font-size:1.15rem;line-height:1.5;color:var(--ink);margin:0 0 24px;word-break:break-word}

.info-blocks{display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap}
.info-block{border:1.5px solid var(--border);padding:12px 18px;min-width:118px;background:var(--bg);flex:1;max-width:100%}
.info-block .label{display:block;font-family:'JetBrains Mono',monospace;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--accent);margin-bottom:6px}
.info-block .value{font-family:'JetBrains Mono',monospace;font-size:1.05rem;font-weight:700;color:var(--ink);letter-spacing:-.5px;word-break:break-word;overflow-wrap:anywhere}
.info-block .value .unit{font-size:.68rem;font-weight:500;color:var(--ink-muted);margin-left:4px;letter-spacing:.5px}

.penguji{margin-bottom:20px}
.penguji-label{font-family:'JetBrains Mono',monospace;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--accent);margin-bottom:8px}
.penguji-table{width:100%;border-collapse:collapse;border:1.5px solid var(--border);background:var(--card);table-layout:fixed}
.penguji-table td{padding:10px 14px;font-size:.85rem;color:var(--ink);border-bottom:1px dashed var(--border-soft);vertical-align:top;word-break:break-word;overflow-wrap:anywhere}
.penguji-table tr:last-child td{border-bottom:none}
.penguji-table td.role{width:62px;background:color-mix(in srgb,var(--accent) 12%,var(--bg-alt));font-family:'JetBrains Mono',monospace;font-weight:700;font-size:.72rem;letter-spacing:.5px;color:var(--accent);text-align:center;border-right:1.5px solid var(--border);padding-left:8px;padding-right:8px}

.expand-area{max-height:0;overflow:hidden;transition:max-height .5s cubic-bezier(.16,1,.3,1),opacity .3s;opacity:0}
.expand-area.open{max-height:500px;opacity:1;margin-top:16px;padding-top:16px;border-top:1px dashed var(--border-soft)}
.expand-title{font-family:'JetBrains Mono',monospace;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--accent);margin-bottom:10px}
.expand-actions{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.expand-btn{
    font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;text-transform:uppercase;
    letter-spacing:.6px;padding:9px 14px;border:1.5px solid var(--border);background:var(--card);
    color:var(--ink);cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s;text-decoration:none
}
.expand-btn:hover{background:var(--accent);color:var(--accent-on);border-color:var(--accent)}
.expand-btn.accent{background:var(--accent);color:var(--accent-on);border-color:var(--accent)}
.expand-btn.accent:hover{background:var(--accent-2);border-color:var(--accent-2)}
.expand-btn svg{width:13px;height:13px}
.expand-qr{display:flex;gap:14px;align-items:center}
.expand-qr img{border:1.5px solid var(--accent);background:#FFF;padding:6px;width:100px;height:100px}
.expand-qr p{font-family:'JetBrains Mono',monospace;font-size:.68rem;color:var(--ink-muted);margin:0;line-height:1.6}

/* ============ STATUS / COUNTDOWN ============ */
.status{padding-top:18px;border-top:1px dashed var(--border-soft)}
.status-split{display:flex;align-items:stretch;border:1.5px solid var(--accent);background:var(--card);overflow:hidden;max-width:100%;flex-wrap:wrap}
.status-split .status-label{background:var(--accent);color:var(--accent-on);padding:9px 12px;font-family:'JetBrains Mono',monospace;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;display:flex;align-items:center;gap:6px;flex-shrink:0;white-space:nowrap}
.status-split .status-timer{padding:9px 14px;font-family:'JetBrains Mono',monospace;font-size:.88rem;font-weight:700;color:var(--ink);letter-spacing:.5px;display:flex;align-items:center;flex-wrap:wrap;gap:0 2px;min-width:0}
.status-split .status-timer .unit{font-size:.68rem;font-weight:500;color:var(--ink-muted);margin-left:3px;margin-right:6px;letter-spacing:.5px}
.status-split .status-timer .unit:last-child{margin-right:0}
.status-split .status-timer .num{color:var(--accent);font-weight:700}
.status-split.soon .status-timer .num{animation:blinkNum 1s infinite}
@keyframes blinkNum{50%{opacity:.35}}
.status-live{display:inline-flex;align-items:center;gap:10px;padding:9px 16px;background:var(--live);color:#FFF;border:1.5px solid var(--live);font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:1px;text-transform:uppercase}
.status-live .dot-live{width:8px;height:8px;border-radius:50%;background:#FFF;animation:pulseLive 1.2s infinite}
@keyframes pulseLive{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.85)}}
.status-done{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;background:var(--bg-alt);border:1.5px solid var(--border-soft);color:var(--ink-muted);font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:1px;text-transform:uppercase}

body.view-grid main .date-section{display:block}
body.view-grid .entry-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:22px}
body.view-grid .entry{grid-template-columns:1fr;margin-bottom:0}
body.view-grid .entry-num{display:none}
body.view-grid .entry::before{display:none}
body.view-grid .ticket-inner{padding:22px 24px 20px}
body.view-grid .mhs-name{font-size:1.35rem}
body.view-grid .judul{font-size:1rem;line-height:1.45}

.empty-state{text-align:center;padding:80px 24px;border:1.5px dashed var(--border-soft);background:var(--card)}
.empty-state h3{font-family:'Instrument Serif',serif;font-style:italic;font-weight:400;font-size:1.6rem;margin:0 0 8px;color:var(--ink)}
.empty-state p{font-family:'JetBrains Mono',monospace;font-size:.75rem;color:var(--ink-muted);text-transform:uppercase;letter-spacing:1px;margin:0}

footer{padding:32px 0;border-top:1.5px solid var(--border);text-align:center}
footer p{font-family:'JetBrains Mono',monospace;font-size:.68rem;color:var(--ink-muted);text-transform:uppercase;letter-spacing:1px;margin:0;word-break:break-word}

.toast-container{position:fixed;right:20px;bottom:20px;z-index:9998;display:flex;flex-direction:column;gap:10px;pointer-events:none;max-width:340px}
.toast{
    background:var(--accent);color:var(--accent-on);padding:14px 18px;min-width:260px;
    font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:600;
    letter-spacing:.5px;display:flex;align-items:center;gap:10px;
    pointer-events:auto;cursor:pointer;position:relative;overflow:hidden;
    animation:toastIn .4s cubic-bezier(.16,1,.3,1);border-left:4px solid var(--accent-2)
}
.toast.live{border-left-color:#FFFFFF}
.toast.done{border-left-color:var(--done)}
@keyframes toastIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
.toast.hide{animation:toastOut .3s ease forwards}
@keyframes toastOut{to{transform:translateX(120%);opacity:0}}
.toast-progress{position:absolute;bottom:0;left:0;height:2px;background:var(--accent-2);animation:toastProg 5s linear forwards}
.toast.live .toast-progress{background:#FFFFFF}
.toast.done .toast-progress{background:var(--done)}
@keyframes toastProg{from{width:100%}to{width:0%}}

.confetti-piece{position:fixed;width:8px;height:8px;z-index:9999;pointer-events:none;will-change:transform,opacity}

.watch-mode{
    position:fixed;inset:0;background:#0A1A0F;color:#FFF;z-index:10000;
    display:none;flex-direction:column;justify-content:center;align-items:center;
    padding:40px;text-align:center;
}
.watch-mode.show{display:flex}
.watch-mode .w-label{font-family:'JetBrains Mono',monospace;font-size:.8rem;letter-spacing:3px;text-transform:uppercase;color:#4ADE80;margin-bottom:24px;display:flex;align-items:center;gap:12px}
.watch-mode .w-label .p{width:10px;height:10px;background:#4ADE80;border-radius:50%;animation:pulseWhite 1.5s infinite}
.watch-mode .w-name{font-family:'Instrument Serif',serif;font-style:italic;font-size:clamp(2.5rem,7vw,5rem);line-height:1.05;margin:0 0 20px;color:#FFF}
.watch-mode .w-judul{font-family:'Instrument Serif',serif;font-style:italic;font-size:clamp(1rem,2.5vw,1.6rem);max-width:900px;line-height:1.4;color:#A8C4B2;margin:0 0 40px}
.watch-mode .w-meta{display:flex;gap:30px;flex-wrap:wrap;justify-content:center;font-family:'JetBrains Mono',monospace;font-size:.9rem;color:#FFF;margin-bottom:30px}
.watch-mode .w-meta span b{color:#4ADE80}
.watch-mode .w-countdown{font-family:'JetBrains Mono',monospace;font-size:2rem;font-weight:700;color:#4ADE80;letter-spacing:2px}
.watch-mode .w-exit{position:absolute;top:24px;right:24px;background:transparent;border:1.5px solid rgba(255,255,255,.3);color:#FFF;padding:10px 16px;font-family:'JetBrains Mono',monospace;font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer}
.watch-mode .w-exit:hover{background:#4ADE80;color:#0A1A0F;border-color:#4ADE80}
.watch-mode .w-next{margin-top:30px;font-family:'JetBrains Mono',monospace;font-size:.85rem;color:#A8C4B2;letter-spacing:1px}
.watch-mode .w-next b{color:#FFF}

@media print{
    .topbar,.toolbar,.live-bar,.timeline-nav,.anchor-btn,.expand-area,.toast-container,.scroll-progress,footer{display:none!important}
    body{background:#FFF!important}
    .ticket,.ticket-inner{clip-path:none!important;border:2px solid #0B7A3E!important}
    .entry{grid-template-columns:40px 1fr!important;page-break-inside:avoid}
}

@media(max-width:900px){
    .timeline-nav{display:none}
}
@media(max-width:720px){
    .letterhead .container{padding:0 6px}
    .container{padding:0 16px}
    .letterhead{padding:16px 0 12px}
    .letterhead-inner{gap:10px;align-items:flex-start}
    .letterhead-logo{width:58px;height:58px}
    .letterhead-text{text-align:left}
    .letterhead-text .uni{font-size:.9rem}
    .letterhead-text .fak{font-size:.76rem}
    .letterhead-text .jur{font-size:.76rem;margin-bottom:4px}
    .letterhead-text .addr,.letterhead-text .web{font-size:.56rem;line-height:1.35}
    .topbar{padding:10px 0}
    .topbar-inner{flex-direction:column;align-items:stretch;gap:10px}
    .nav-badge{text-align:center;font-size:.6rem;padding:5px 9px}
    .topbar-actions{justify-content:space-between}
    .icon-btn{font-size:.6rem;padding:7px 9px}
    .icon-btn .lbl{display:none}
    .hero{padding:40px 0 36px}
    .hero-top{margin-bottom:24px;gap:10px}
    .hero-stats{gap:14px;font-size:.68rem}
    .hero p{max-width:100%}
    .toolbar-inner{flex-direction:column;gap:8px}
    .tabs{width:100%}
    .tab{flex:1;padding:11px 4px;font-size:.55rem;letter-spacing:.2px}
    .search-hint{display:none}
    .date-filter{width:100%;min-width:0}
    .view-toggle{width:100%}
    .view-toggle .vt-btn{flex:1}
    .date-header{gap:14px}
    .date-box{width:60px;height:60px}
    .date-box .day{font-size:1.6rem}
    .date-info h2{font-size:1.15rem}
    .date-info p{font-size:.65rem}
    .date-count{display:none}
    .date-progress .bar{max-width:120px}
    .entry{grid-template-columns:34px minmax(0,1fr);gap:10px}
    .entry-num{font-size:1.3rem;padding-top:10px;opacity:.28}
    .entry::before{display:none}
    .ticket-inner{padding:22px 18px 20px}
    .mhs-name{font-size:1.3rem}
    .mhs-meta{font-size:.72rem}
    .judul{font-size:1rem}
    .info-blocks{flex-direction:column;gap:8px}
    .info-block{width:100%;min-width:0;padding:10px 14px}
    .info-block .value{font-size:.95rem}
    .penguji-table td{padding:9px 10px;font-size:.76rem}
    .penguji-table td.role{width:52px;font-size:.62rem;padding-left:5px;padding-right:5px}
    .status-split{flex-direction:column;width:100%}
    .status-split .status-label{font-size:.58rem;padding:8px 10px;letter-spacing:1px;justify-content:center}
    .status-split .status-timer{font-size:.82rem;padding:10px 12px;justify-content:center;width:100%}
    .status-live,.status-done{font-size:.65rem;padding:8px 12px;width:100%;justify-content:center}
    body.view-grid .entry-list{grid-template-columns:1fr}
    .toast-container{left:16px;right:16px;bottom:16px;max-width:none}
    .watch-mode .w-meta{gap:16px;font-size:.72rem}
}
@media(max-width:420px){
    .letterhead-text .uni{font-size:.78rem}
    .letterhead-text .fak{font-size:.66rem}
    .letterhead-text .jur{font-size:.66rem}
    .letterhead-text .addr,.letterhead-text .web{font-size:.5rem}
    .letterhead-logo{width:48px;height:48px}
}
</style>
</head>
<body>

<div class="scroll-progress" id="scrollProgress"></div>

<header class="letterhead">
    <div class="container letterhead-inner">
        <img src="../assets/img/FMIPA.png" alt="Logo UNIMED" class="letterhead-logo" onerror="this.style.display='none'">
        <div class="letterhead-text">
            <p class="uni">UNIVERSITAS NEGERI MEDAN</p>
            <p class="fak">FAKULTAS MATEMATIKA DAN ILMU PENGETAHUAN ALAM</p>
            <p class="jur">JURUSAN MATEMATIKA</p>
            <p class="addr">Jl. Willem Iskandar Psr.V – Kotak Pos No.1589 Medan 20221 Telp.(061) 6625970</p>
            <p class="web">Laman : www.fmipa.unimed.ac.id</p>
        </div>
    </div>
</header>

<div class="topbar">
    <div class="container topbar-inner">
        <span class="nav-badge">Seminar Proposal · Info Publik</span>
        <div class="topbar-actions">
            <button class="icon-btn" id="watchBtn" title="Watch Mode">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span class="lbl">Watch</span>
            </button>
            <button class="icon-btn" id="printBtn" title="Print Poster">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span class="lbl">Print</span>
            </button>
            <button class="icon-btn" id="themeToggle" title="Ganti tema">
                <svg id="themeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>
                <span class="lbl" id="themeLabel">DARK</span>
            </button>
        </div>
    </div>
</div>

<div class="live-bar" id="liveBar">
    <div class="container live-bar-inner">
        <span class="pulse"></span>
        <span class="live-label">LIVE</span>
        <div class="live-items" id="liveItems"></div>
    </div>
</div>

<header class="hero">
    <canvas id="particleCanvas"></canvas>
    <div class="container">
        <div class="hero-top">
            <span class="hero-tag"><span class="dot"></span>Seminar Proposal</span>
            <span class="hero-stats">
                <span><b><?= $total_mhs ?></b> mahasiswa</span>
                <span><b><?= $total_hari ?></b> sesi</span>
            </span>
        </div>
        <h1>Jadwal<br><em>Sempro</em></h1>
        <p>Papan sidang proposal.<br>Live dari admin.</p>
    </div>
</header>

<div class="toolbar">
    <div class="container toolbar-inner">
        <div class="search-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Cari nama, NIM, judul..." autocomplete="off">
            <span class="search-hint">⌘K</span>
        </div>
        <div class="date-filter">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <select id="dateFilter">
                <option value="">Semua Tanggal</option>
                <?php foreach($daftar_tanggal as $tgl): 
                    $ts = strtotime($tgl);
                    $label = $hari_indo[date('l', $ts)] . ', ' . date('d', $ts) . ' ' . $bulan_indo_short[(int)date('n', $ts)];
                ?>
                    <option value="<?= $tgl ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="tabs">
            <button class="tab active" data-filter="all">Semua</button>
            <button class="tab" data-filter="today">Hari Ini</button>
            <button class="tab" data-filter="upcoming">Mendatang</button>
            <button class="tab" data-filter="done">Selesai</button>
        </div>
        <div class="view-toggle">
            <button class="vt-btn active" data-view="list" title="List">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </button>
            <button class="vt-btn" data-view="grid" title="Grid">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </button>
        </div>
    </div>
</div>

<nav class="timeline-nav" id="timelineNav"></nav>

<main>
    <div class="container">
        <?php if(empty($semua_sesi)): ?>
            <div class="empty-state"><h3>Belum ada jadwal</h3><p>Jadwal sempro akan tampil di sini</p></div>
        <?php else: 
            $global_idx = 0;
            foreach($semua_sesi as $sesi):
                $list = $sesi['mhs_list'];
                $ts = strtotime($sesi['hari_tanggal']);
                $hari_nama = $hari_indo[date('l', $ts)];
                $tgl_num = date('d', $ts);
                $bulan_short = $bulan_indo_short[(int)date('n', $ts)];
                $tgl_full = date('d', $ts) . ' ' . $bulan_indo[(int)date('n', $ts)] . ' ' . date('Y', $ts);
                $pukul_sesi_txt = substr($sesi['pukul_sesi'],0,5) . ' WIB';
                $notulen_txt = htmlspecialchars($sesi['notulen'] ?: '-');
        ?>
            <section class="date-section" data-sesi-id="<?= $sesi['id'] ?>" data-tanggal="<?= $sesi['hari_tanggal'] ?>">
                <div class="date-header">
                    <div class="date-box">
                        <span class="day"><?= $tgl_num ?></span>
                        <span class="month"><?= $bulan_short ?></span>
                    </div>
                    <div class="date-info">
                        <h2><?= $hari_nama ?>, <?= $tgl_full ?></h2>
                        <p>Sesi Mulai <?= $pukul_sesi_txt ?><span class="sep">·</span>Notulen: <?= $notulen_txt ?></p>
                        <div class="date-progress">
                            <div class="bar"><div class="bar-fill" data-progress-fill></div></div>
                            <span class="txt" data-progress-txt>0 / <?= count($list) ?> selesai</span>
                        </div>
                    </div>
                    <div class="date-count">
                        <span class="num"><?= count($list) ?></span>
                        <span class="lbl">Mahasiswa</span>
                    </div>
                </div>
                <div class="entry-list">
                <?php if(empty($list)): ?>
                    <div class="empty-state" style="padding:30px 20px;">
                        <p>Belum ada mahasiswa di sesi ini</p>
                    </div>
                <?php else: 
                    $idx = 0;
                    foreach($list as $s): $idx++; $global_idx++;
                        // Pukul selesai = pukul mulai + durasi
                        $start_ts = strtotime($sesi['hari_tanggal'] . ' ' . $s['pukul_mhs']);
                        $durasi = (int)($s['durasi_menit'] ?? 60);
                        $end_ts = $start_ts + ($durasi * 60);
                        $search_text = strtolower($s['nama_mhs'].' '.$s['nim'].' '.$s['prodi'].' '.$s['judul_skripsi'].' '.$s['ruang'].' '.($s['dosen_ps'] ?? '').' '.($s['dosen_pa'] ?? '').' '.($s['dosen_ns1'] ?? '').' '.($s['dosen_ns2'] ?? '').' '.($s['dosen_ns3'] ?? ''));
                        $anchor_id = 'mhs-' . preg_replace('/[^a-z0-9]/','-', strtolower($s['nim']));
                ?>
                    <article class="entry" id="<?= $anchor_id ?>"
                             data-search="<?= htmlspecialchars($search_text, ENT_QUOTES) ?>"
                             data-start="<?= $start_ts ?>" data-end="<?= $end_ts ?>"
                             data-tanggal="<?= $sesi['hari_tanggal'] ?>"
                             data-nama="<?= htmlspecialchars($s['nama_mhs']) ?>"
                             data-idx="<?= $global_idx ?>">
                        <div class="entry-num"><?= str_pad($idx,2,'0',STR_PAD_LEFT) ?></div>
                        <div class="ticket">
                            <div class="ticket-inner">
                                <button class="anchor-btn" data-anchor="<?= $anchor_id ?>" title="Copy link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                </button>
                                <h3 class="mhs-name"><?= htmlspecialchars($s['nama_mhs']) ?></h3>
                                <p class="mhs-meta">
                                    <span class="nim"><?= htmlspecialchars($s['nim']) ?></span>
                                    <span class="sep">·</span><span><?= htmlspecialchars($s['prodi']) ?></span>
                                </p>
                                <div class="divider"></div>
                                <blockquote class="judul">"<?= htmlspecialchars($s['judul_skripsi']) ?>"</blockquote>
                                <div class="info-blocks">
                                    <div class="info-block"><span class="label">Waktu</span><span class="value"><?= substr($s['pukul_mhs'],0,5) ?><span class="unit">WIB</span></span></div>
                                    <div class="info-block"><span class="label">Ruang</span><span class="value"><?= htmlspecialchars($s['ruang']) ?></span></div>
                                </div>
                                <?php if($s['dosen_ps'] || $s['dosen_pa'] || $s['dosen_ns1'] || $s['dosen_ns2'] || $s['dosen_ns3']): ?>
                                <div class="penguji">
                                    <div class="penguji-label">Tim Penguji</div>
                                    <table class="penguji-table">
                                        <?php if($s['dosen_ps']): ?><tr><td class="role">PS</td><td><?= htmlspecialchars($s['dosen_ps']) ?></td></tr><?php endif; ?>
                                        <?php if($s['dosen_pa']): ?><tr><td class="role">PA</td><td><?= htmlspecialchars($s['dosen_pa']) ?></td></tr><?php endif; ?>
                                        <?php if($s['dosen_ns1']): ?><tr><td class="role">NS1</td><td><?= htmlspecialchars($s['dosen_ns1']) ?></td></tr><?php endif; ?>
                                        <?php if($s['dosen_ns2']): ?><tr><td class="role">NS2</td><td><?= htmlspecialchars($s['dosen_ns2']) ?></td></tr><?php endif; ?>
                                        <?php if($s['dosen_ns3']): ?><tr><td class="role">NS3</td><td><?= htmlspecialchars($s['dosen_ns3']) ?></td></tr><?php endif; ?>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <div class="status" data-status></div>
                                <button type="button" class="expand-toggle" data-toggle-expand>
    <span class="et-label">
        Detail
    </span>
    <svg class="et-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"/>
    </svg>
</button>
                                
                                <div class="expand-area">
                                    <div class="expand-title">Aksi Cepat</div>
                                    <div class="expand-actions">
                                        <button class="expand-btn accent" data-wa-share>Salin Format WA</button>
                                        <button class="expand-btn" data-ics='<?= $start_ts ?>' data-ics-end='<?= $end_ts ?>' data-ics-title="<?= htmlspecialchars($s['judul_skripsi']) ?>" data-ics-loc="<?= htmlspecialchars($s['ruang']) ?>">Set Reminder</button>
                                        <button class="expand-btn" data-copy-link>Copy Link</button>
                                    </div>
                                    <div class="expand-qr">
                                        <img data-qr alt="QR" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg'/>">
                                        <p>Scan untuk buka detail jadwal sempro ini di HP.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; endif; ?>
                </div>
            </section>
        <?php endforeach; endif; ?>
        <div class="empty-state" id="emptySearch" style="display:none">
            <h3>Tidak ada hasil</h3><p>Coba kata kunci lain atau ubah filter</p>
        </div>
    </div>
</main>

<footer><div class="container"><p>© 2026 FMIPA UNIMED · Jurusan Matematika · Seminar Proposal Board</p></div></footer>

<div class="toast-container" id="toastContainer"></div>

<div class="watch-mode" id="watchMode">
    <button class="w-exit" id="watchExit">✕ Tutup (Esc)</button>
    <div class="w-label"><span class="p"></span> LIVE NOW</div>
    <h2 class="w-name" id="wName">—</h2>
    <p class="w-judul" id="wJudul">—</p>
    <div class="w-meta" id="wMeta"></div>
    <div class="w-countdown" id="wCountdown">--:--</div>
    <div class="w-next" id="wNext"></div>
</div>

<script>
const $ = s => document.querySelector(s);
const $$ = s => document.querySelectorAll(s);

/* 1. SCROLL PROGRESS */
const scrollProgress = $('#scrollProgress');
window.addEventListener('scroll', () => {
    const h = document.documentElement;
    const pct = (h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100;
    scrollProgress.style.width = pct + '%';
}, {passive: true});

/* 2. THEME TOGGLE */
const themeIcon = $('#themeIcon'), themeLabel = $('#themeLabel');
const ICON_SUN = '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>';
const ICON_MOON = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
function applyTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    localStorage.setItem('sempro-theme', t);
    if (t === 'dark') { themeIcon.innerHTML = ICON_SUN; themeLabel.textContent = 'LIGHT'; }
    else { themeIcon.innerHTML = ICON_MOON; themeLabel.textContent = 'DARK'; }
}
applyTheme(localStorage.getItem('sempro-theme') || 'light');
$('#themeToggle').addEventListener('click', () => {
    applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
});

/* 3. VIEW TOGGLE */
$$('.vt-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        $$('.vt-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const view = btn.dataset.view;
        document.body.classList.toggle('view-grid', view === 'grid');
        localStorage.setItem('sempro-view', view);
    });
});
const savedView = localStorage.getItem('sempro-view') || 'list';
if (savedView === 'grid') {
    document.body.classList.add('view-grid');
    $$('.vt-btn').forEach(b => b.classList.toggle('active', b.dataset.view === 'grid'));
}

/* 4. STATUS + COUNTDOWN + PROGRESS + LIVE + TOAST + CONFETTI */
const statusCache = {};
let currentLive = [];

function renderStatus(box, start, end, entry) {
    const now = Math.floor(Date.now() / 1000);
    const id = entry.dataset.idx;
    let newState;
    if (now < start) {
        const distance = start - now;
        const days = Math.floor(distance / 86400);
        const hours = Math.floor((distance % 86400) / 3600);
        const minutes = Math.floor((distance % 3600) / 60);
        const seconds = distance % 60;
        const isSoon = (days === 0 && hours === 0 && minutes < 60);
        let parts = [];
        if (days > 0) {
            parts.push(`<span class="num">${String(days).padStart(2,'0')}</span><span class="unit">hari</span>`);
            parts.push(`<span class="num">${String(hours).padStart(2,'0')}</span><span class="unit">jam</span>`);
            parts.push(`<span class="num">${String(minutes).padStart(2,'0')}</span><span class="unit">mnt</span>`);
        } else if (hours > 0) {
            parts.push(`<span class="num">${String(hours).padStart(2,'0')}</span><span class="unit">jam</span>`);
            parts.push(`<span class="num">${String(minutes).padStart(2,'0')}</span><span class="unit">mnt</span>`);
            parts.push(`<span class="num">${String(seconds).padStart(2,'0')}</span><span class="unit">dtk</span>`);
        } else {
            parts.push(`<span class="num">${String(minutes).padStart(2,'0')}</span><span class="unit">mnt</span>`);
            parts.push(`<span class="num">${String(seconds).padStart(2,'0')}</span><span class="unit">dtk</span>`);
        }
        box.innerHTML = `<div class="status-split ${isSoon?'soon':''}"><div class="status-label"><i class="fas fa-lock"></i> Terbuka dalam</div><div class="status-timer">${parts.join('')}</div></div>`;
        newState = 'upcoming';
    } else if (now >= start && now <= end) {
        box.innerHTML = `<div class="status-live"><span class="dot-live"></span> Sedang Berlangsung</div>`;
        newState = 'live';
    } else {
        box.innerHTML = `<div class="status-done"><i class="fas fa-check"></i> Selesai</div>`;
        newState = 'done';
    }
    const prev = statusCache[id];
    if (prev && prev !== newState) {
        if (newState === 'live') {
            showToast('live', `🔴 Dimulai! — ${entry.dataset.nama}`);
            confettiBurst(entry);
            const rect = entry.getBoundingClientRect();
            const isFar = rect.top < -200 || rect.top > window.innerHeight;
            if (isFar && !sessionStorage.getItem('autoScrolled-' + id)) {
                entry.scrollIntoView({behavior: 'smooth', block: 'center'});
                sessionStorage.setItem('autoScrolled-' + id, '1');
            }
        } else if (newState === 'done' && prev === 'live') {
            showToast('done', `✓ Selesai — ${entry.dataset.nama}`);
        }
    }
    statusCache[id] = newState;
    return newState;
}

function updateAllStatuses() {
    const entries = $$('.entry');
    const statesBySesi = {};
    entries.forEach(entry => {
        const start = parseInt(entry.dataset.start);
        const end = parseInt(entry.dataset.end);
        const statusBox = entry.querySelector('[data-status]');
        if (!statusBox) return;
        const state = renderStatus(statusBox, start, end, entry);
        const sesiKey = entry.closest('.date-section')?.dataset.sesiId;
        if (sesiKey) {
            if (!statesBySesi[sesiKey]) statesBySesi[sesiKey] = {total: 0, done: 0};
            statesBySesi[sesiKey].total++;
            if (state === 'done') statesBySesi[sesiKey].done++;
        }
        const dot = document.querySelector(`.tn-dot[data-idx="${entry.dataset.idx}"]`);
        if (dot) { dot.classList.remove('upcoming','live','done'); dot.classList.add(state); }
    });
    document.querySelectorAll('.date-section').forEach(sec => {
        const key = sec.dataset.sesiId;
        const s = statesBySesi[key];
        if (!s) return;
        const pct = (s.done / s.total) * 100;
        const fill = sec.querySelector('[data-progress-fill]');
        const txt = sec.querySelector('[data-progress-txt]');
        if (fill) {
            fill.style.width = pct + '%';
            fill.classList.toggle('full', s.done === s.total);
        }
        if (txt) txt.textContent = `${s.done} / ${s.total} selesai`;
    });
    updateLiveBar();
    if (document.body.classList.contains('watch-active')) updateWatchMode();
}

/* 5. LIVE BAR */
const liveBar = $('#liveBar');
const liveItems = $('#liveItems');
let liveCarouselTimer = null;
let liveCarouselIdx = 0;

function updateLiveBar() {
    const liveEntries = [];
    $$('.entry').forEach(e => {
        const s = statusCache[e.dataset.idx];
        if (s === 'live') {
            const idx = e.dataset.idx;
            liveEntries.push({
                id: idx,
                name: e.dataset.nama,
                ruang: e.querySelectorAll('.info-block .value')[1]?.textContent.trim() || '',
waktu: e.querySelectorAll('.info-block .value')[0]?.textContent.trim().replace(/\s*WIB\s*$/i, '') || '',
                el: e
            });
        }
    });
    currentLive = liveEntries;
    if (liveEntries.length === 0) {
        liveBar.classList.remove('show');
        document.body.classList.remove('has-live');
        if (liveCarouselTimer) { clearInterval(liveCarouselTimer); liveCarouselTimer = null; }
        return;
    }
    liveBar.classList.add('show');
    document.body.classList.add('has-live');
    const renderItem = (e) => `<span class="live-item" data-jump="${e.id}"><span class="mhs-name-live">${e.name}</span><span class="sep">·</span>${e.ruang}<span class="sep">·</span>${e.waktu} WIB</span>`;
    if (liveEntries.length === 1) {
        liveItems.innerHTML = renderItem(liveEntries[0]);
    } else {
        liveCarouselIdx = 0;
        liveItems.innerHTML = renderItem(liveEntries[0]);
        if (liveCarouselTimer) clearInterval(liveCarouselTimer);
        liveCarouselTimer = setInterval(() => {
            liveCarouselIdx = (liveCarouselIdx + 1) % liveEntries.length;
            const cur = liveItems.querySelector('.live-item');
            if (cur) cur.style.opacity = '0';
            setTimeout(() => {
                liveItems.innerHTML = renderItem(liveEntries[liveCarouselIdx]);
            }, 200);
        }, 3500);
    }
}
liveBar.addEventListener('click', e => {
    const item = e.target.closest('[data-jump]');
    if (!item) return;
    const target = document.querySelector(`.entry[data-idx="${item.dataset.jump}"]`);
    if (target) {
        target.scrollIntoView({behavior:'smooth', block:'center'});
        target.classList.add('anchor-target');
        setTimeout(() => target.classList.remove('anchor-target'), 2000);
    }
});

/* 6. TOAST */
const toastContainer = $('#toastContainer');
function showToast(type, msg) {
    const t = document.createElement('div');
    t.className = 'toast ' + (type === 'live' ? 'live' : type === 'done' ? 'done' : '');
    t.innerHTML = `<span>${msg}</span><div class="toast-progress"></div>`;
    toastContainer.appendChild(t);
    const all = toastContainer.querySelectorAll('.toast');
    if (all.length > 3) all[0].remove();
    setTimeout(() => {
        t.classList.add('hide');
        setTimeout(() => t.remove(), 320);
    }, 5000);
}

/* 7. CONFETTI */
const CONF_COLORS = ['#0B7A3E', '#16A34A', '#22C55E', '#4ADE80', '#FFB300', '#E8F5EC'];
function confettiBurst(entry) {
    const rect = entry.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 3;
    for (let i = 0; i < 20; i++) {
        const p = document.createElement('div');
        p.className = 'confetti-piece';
        p.style.background = CONF_COLORS[i % CONF_COLORS.length];
        p.style.left = cx + 'px';
        p.style.top = cy + 'px';
        p.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
        document.body.appendChild(p);
        const angle = (Math.PI * 2 * i) / 20 + Math.random() * 0.5;
        const dist = 80 + Math.random() * 120;
        const dx = Math.cos(angle) * dist;
        const dy = Math.sin(angle) * dist - 50;
        p.animate([
            {transform: 'translate(0,0) rotate(0deg) scale(1)', opacity: 1},
            {transform: `translate(${dx}px,${dy}px) rotate(${Math.random()*720-360}deg) scale(0)`, opacity: 0}
        ], {duration: 1200 + Math.random() * 400, easing: 'cubic-bezier(.16,1,.3,1)'});
        setTimeout(() => p.remove(), 1700);
    }
}

/* 8. TIMELINE NAV */
const timelineNav = $('#timelineNav');
$$('.entry').forEach(entry => {
    const dot = document.createElement('div');
    dot.className = 'tn-dot upcoming';
    dot.dataset.idx = entry.dataset.idx;
    dot.dataset.name = entry.dataset.nama;
    dot.addEventListener('click', () => {
        entry.scrollIntoView({behavior:'smooth', block:'center'});
        entry.classList.add('anchor-target');
        setTimeout(() => entry.classList.remove('anchor-target'), 2000);
    });
    timelineNav.appendChild(dot);
});
const dotObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            const id = e.target.dataset.idx;
            $$('.tn-dot').forEach(d => d.classList.toggle('active', d.dataset.idx === id));
        }
    });
}, {threshold: 0.5, rootMargin: '-20% 0px -40% 0px'});
$$('.entry').forEach(e => dotObserver.observe(e));

/* Fungsi helper: buka/tutup expand area + sinkron class toggle button */
function toggleExpand(entry) {
    const expand = entry.querySelector('.expand-area');
    const toggleBtn = entry.querySelector('.expand-toggle');
    const isOpen = expand.classList.contains('open');

    // Tutup semua expand lain (accordion behavior)
    $$('.entry').forEach(en => {
        if (en !== entry) {
            en.querySelector('.expand-area')?.classList.remove('open');
            en.querySelector('.expand-toggle')?.classList.remove('open');
        }
    });

    if (!isOpen) {
        expand.classList.add('open');
        toggleBtn?.classList.add('open');
        // Generate QR on first open
        const qr = expand.querySelector('[data-qr]');
        if (qr && !qr.dataset.generated) {
            const url = location.origin + location.pathname + '#' + entry.id;
            qr.src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(url) + '&color=0B7A3E&bgcolor=FFFFFF';
            qr.dataset.generated = '1';
        }
    } else {
        expand.classList.remove('open');
        toggleBtn?.classList.remove('open');
    }
}

// Klik di tombol toggle
document.addEventListener('click', e => {
    const btn = e.target.closest('[data-toggle-expand]');
    if (!btn) return;
    e.stopPropagation();
    const entry = btn.closest('.entry');
    if (entry) toggleExpand(entry);
});

// Klik di body card (opsional: tetap bisa expand via card)
document.addEventListener('click', e => {
    const entry = e.target.closest('.entry');
    if (!entry) return;
    if (e.target.closest('.expand-area') || e.target.closest('.anchor-btn') || e.target.closest('[data-toggle-expand]')) return;
    toggleExpand(entry);
});
document.addEventListener('click', e => {
    const btn = e.target.closest('.anchor-btn');
    if (!btn) return;
    e.stopPropagation();
    const url = location.origin + '/sempro#' + btn.dataset.anchor;
    navigator.clipboard.writeText(url).then(() => showToast('info', '🔗 Link disalin!'));
});
document.addEventListener('click', e => {
    const btn = e.target.closest('[data-wa-share]');
    if (!btn) return;
    e.stopPropagation();
    const entry = btn.closest('.entry');
    const name = entry.dataset.nama;
    const nim = entry.querySelector('.mhs-meta .nim').textContent;
    const prodi = entry.querySelector('.mhs-meta span:last-child').textContent;
    const judul = entry.querySelector('.judul').textContent.replace(/^"|"$/g,'');
    const blocks = entry.querySelectorAll('.info-block .value');
    const waktu = blocks[0].textContent.trim().replace(/\s*WIB\s*$/i, '');
    const ruang = blocks[1].textContent.trim();
    const pengujiRows = entry.querySelectorAll('.penguji-table tr');
    let penguji = '';
    pengujiRows.forEach(r => {
        const role = r.querySelector('.role').textContent;
        const nm = r.querySelector('td:last-child').textContent;
        penguji += `  ${role}: ${nm}\n`;
    });
    const text = `📢 *SEMINAR PROPOSAL*\n\n👤 *${name}*\n🆔 ${nim}\n🎓 ${prodi}\n\n📖 *Judul:*\n_${judul}_\n\n🕐 Pukul: ${waktu}\n📍 Ruang: ${ruang}\n\n👥 *Tim Penguji:*\n${penguji}\n\n📅 Info lengkap: ${location.origin + '/sempro#' + entry.id}`;
    navigator.clipboard.writeText(text).then(() => showToast('info', '📋 Format WA disalin!'));
});
document.addEventListener('click', e => {
    const btn = e.target.closest('[data-ics]');
    if (!btn) return;
    e.stopPropagation();
    const start = parseInt(btn.dataset.ics);
    const end = parseInt(btn.dataset.icsEnd);
    const title = btn.dataset.icsTitle;
    const loc = btn.dataset.icsLoc;
    const fmt = ts => new Date(ts * 1000).toISOString().replace(/[-:]/g,'').split('.')[0] + 'Z';
    const ics = `BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//FMIPA UNIMED//Sempro//ID\r\nBEGIN:VEVENT\r\nUID:${Date.now()}@sempro\r\nDTSTAMP:${fmt(Math.floor(Date.now()/1000))}\r\nDTSTART:${fmt(start)}\r\nDTEND:${fmt(end)}\r\nSUMMARY:${title}\r\nLOCATION:${loc}\r\nBEGIN:VALARM\r\nTRIGGER:-PT30M\r\nACTION:DISPLAY\r\nDESCRIPTION:Sempro 30 menit lagi\r\nEND:VALARM\r\nEND:VEVENT\r\nEND:VCALENDAR`;
    const blob = new Blob([ics], {type: 'text/calendar'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'sempro.ics'; a.click();
    URL.revokeObjectURL(url);
    showToast('info', '📅 Reminder didownload!');
});
document.addEventListener('click', e => {
    const btn = e.target.closest('[data-copy-link]');
    if (!btn) return;
    e.stopPropagation();
    const entry = btn.closest('.entry');
    const url = location.origin + '/sempro#' + entry.id;
    navigator.clipboard.writeText(url).then(() => showToast('info', '🔗 Link disalin!'));
});

/* 10. HOVER MAGNET */
if (window.matchMedia('(hover: hover)').matches) {
    $$('.entry').forEach(entry => {
        const ticket = entry.querySelector('.ticket');
        const num = entry.querySelector('.entry-num');
        entry.addEventListener('mousemove', e => {
            const rect = entry.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            ticket.style.transform = `translate(${x * 3}px, ${y * 3}px)`;
            if (num) num.style.transform = `translate(${x * -5}px, ${y * -5}px)`;
        });
        entry.addEventListener('mouseleave', () => {
            ticket.style.transform = '';
            if (num) num.style.transform = '';
        });
    });
}

/* 11. SEARCH & FILTER (dengan tanggal) */
let currentFilter = 'all';
let currentTanggal = '';

function applyFilters() {
    const query = $('#searchInput').value.toLowerCase().trim();
    const now = Math.floor(Date.now() / 1000);
    const today = new Date().toISOString().split('T')[0];
    let totalVisible = 0;
    $$('.entry').forEach(entry => {
        const search = entry.dataset.search || '';
        const start = parseInt(entry.dataset.start);
        const end = parseInt(entry.dataset.end);
        const tanggal = entry.dataset.tanggal;
        const matchSearch = query === '' || search.includes(query);
        const matchTanggal = currentTanggal === '' || tanggal === currentTanggal;
        let matchFilter = true;
        if (currentFilter === 'today') matchFilter = (tanggal === today);
        else if (currentFilter === 'upcoming') matchFilter = (now < start);
        else if (currentFilter === 'done') matchFilter = (now > end);
        const visible = matchSearch && matchFilter && matchTanggal;
        if (visible) {
            entry.style.display = '';
            entry.animate([{opacity:0, transform:'translateY(8px)'}, {opacity:1, transform:'translateY(0)'}], {duration: 300, easing:'cubic-bezier(.16,1,.3,1)'});
            totalVisible++;
        } else {
            entry.style.display = 'none';
        }
    });
    $$('.date-section').forEach(sec => {
        const visible = sec.querySelectorAll('.entry:not([style*="display: none"])');
        sec.style.display = visible.length > 0 ? '' : 'none';
    });
    $('#emptySearch').style.display = (totalVisible === 0) ? 'block' : 'none';
}
$('#searchInput').addEventListener('input', applyFilters);
$('#dateFilter').addEventListener('change', e => {
    currentTanggal = e.target.value;
    applyFilters();
});
$$('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        $$('.tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentFilter = tab.dataset.filter;
        applyFilters();
    });
});
document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); $('#searchInput').focus(); }
    if (e.key === 'Escape') {
        const input = $('#searchInput');
        if (document.activeElement === input && input.value) { input.value = ''; applyFilters(); }
        else input.blur();
    }
});

/* 12. WATCH MODE */
const watchMode = $('#watchMode');
function updateWatchMode() {
    if (!document.body.classList.contains('watch-active')) return;
    const now = Math.floor(Date.now() / 1000);
    if (currentLive.length > 0) {
        const live = currentLive[0];
        const entry = live.el;
        const name = entry.dataset.nama;
        const nim = entry.querySelector('.mhs-meta .nim').textContent;
        const judul = entry.querySelector('.judul').textContent.replace(/^"|"$/g,'');
        const blocks = entry.querySelectorAll('.info-block .value');
        const waktu = blocks[0].textContent.trim().replace(/\s*WIB\s*$/i, '');
        const ruang = blocks[1].textContent.trim();
        const end = parseInt(entry.dataset.end);
        const remaining = end - now;
        const m = Math.floor(remaining / 60), s = remaining % 60;
        $('#wName').textContent = name;
        $('#wJudul').textContent = judul;
        $('#wMeta').innerHTML = `<span>🆔 <b>${nim}</b></span><span>🕐 <b>${waktu} WIB</b></span><span>📍 <b>${ruang}</b></span>`;
        $('#wCountdown').textContent = `Sisa ${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        const allEntries = [...$$('.entry')].sort((a,b) => parseInt(a.dataset.start) - parseInt(b.dataset.start));
        const currentIdx = allEntries.indexOf(entry);
        const next = allEntries.slice(currentIdx + 1).find(e => parseInt(e.dataset.start) > now);
        if (next) {
            const nStart = parseInt(next.dataset.start);
            const nDist = nStart - now;
            const nh = Math.floor(nDist / 3600), nm = Math.floor((nDist % 3600) / 60);
            $('#wNext').innerHTML = `Selanjutnya: <b>${next.dataset.nama}</b> · ${nh}j ${nm}m lagi`;
        } else $('#wNext').innerHTML = 'Menunggu jadwal berikutnya';
    } else {
        $('#wName').textContent = 'Tidak ada yang live';
        $('#wJudul').textContent = 'Menunggu jadwal sempro berikutnya dimulai.';
        $('#wMeta').innerHTML = '';
        $('#wCountdown').textContent = '';
        $('#wNext').innerHTML = '';
    }
}
$('#watchBtn').addEventListener('click', () => {
    document.body.classList.add('watch-active');
    watchMode.classList.add('show');
    if (watchMode.requestFullscreen) watchMode.requestFullscreen().catch(()=>{});
    updateWatchMode();
});
$('#watchExit').addEventListener('click', exitWatch);
function exitWatch() {
    document.body.classList.remove('watch-active');
    watchMode.classList.remove('show');
    if (document.fullscreenElement) document.exitFullscreen().catch(()=>{});
}
document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement && document.body.classList.contains('watch-active')) exitWatch();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.body.classList.contains('watch-active')) exitWatch();
});

/* 13. PRINT */
$('#printBtn').addEventListener('click', () => window.print());

/* 14. PARTICLES */
(function() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const canvas = $('#particleCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let W, H, particles = [];
    function resize() {
        const hero = canvas.parentElement;
        W = canvas.width = hero.offsetWidth;
        H = canvas.height = hero.offsetHeight;
    }
    function init() {
        const count = Math.min(28, Math.floor(W / 40));
        particles = [];
        for (let i = 0; i < count; i++) {
            particles.push({
                x: Math.random() * W,
                y: Math.random() * H,
                vx: (Math.random() - 0.5) * 0.15,
                vy: -0.15 - Math.random() * 0.2,
                r: 0.8 + Math.random() * 1.2,
                a: 0.15 + Math.random() * 0.25
            });
        }
    }
    function tick() {
        ctx.clearRect(0, 0, W, H);
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        particles.forEach(p => {
            p.x += p.vx; p.y += p.vy;
            if (p.y < -5) { p.y = H + 5; p.x = Math.random() * W; }
            if (p.x < -5) p.x = W + 5;
            if (p.x > W + 5) p.x = -5;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = isDark ? `rgba(74,222,128,${p.a})` : `rgba(11,122,62,${p.a})`;
            ctx.fill();
        });
        requestAnimationFrame(tick);
    }
    resize(); init(); tick();
    window.addEventListener('resize', () => { resize(); init(); });
})();

/* 15. INITIAL LOAD */
updateAllStatuses();
setInterval(updateAllStatuses, 1000);

$$('.entry').forEach((entry, i) => {
    const ticket = entry.querySelector('.ticket');
    if (ticket) ticket.style.animationDelay = (i * 0.05) + 's';
});

if (location.hash) {
    setTimeout(() => {
        const target = document.querySelector(location.hash);
        if (target) {
            target.scrollIntoView({behavior: 'smooth', block: 'center'});
            target.classList.add('anchor-target');
            setTimeout(() => target.classList.remove('anchor-target'), 2000);
        }
    }, 400);
}
</script>
</body>
</html>