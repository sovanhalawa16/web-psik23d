<?php 
session_start();
include '../koneksi.php'; 

date_default_timezone_set('Asia/Jakarta');

// Cek apakah mahasiswa sudah login (Pakai session dari rekap_absen.php)
if(!isset($_SESSION['nim_mhs_login'])) {
    header("Location: /rekap_absen"); // Lempar balik ke home kalau belum login
    exit;
}

$nim_mhs = $_SESSION['nim_mhs_login'];
$nama_mhs = $_SESSION['nama_mhs_login'];

// Ambil data untuk opsi filter Mata Kuliah
$q_matkul = mysqli_query($koneksi, "SELECT DISTINCT nama_mk FROM absensi_real WHERE nim = '$nim_mhs' ORDER BY nama_mk ASC");

// Ambil seluruh riwayat absensi mahasiswa ini
$q_riwayat = mysqli_query($koneksi, "SELECT * FROM absensi_real WHERE nim = '$nim_mhs' ORDER BY waktu_mulai DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Filter Absensi - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; padding-bottom: 70px; overflow-x: hidden; /* Cegah geser kanan-kiri */ }
        
        /* --- HEADER MAKIN SLIM --- */
        .app-header { background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 12px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .logo-header { width: 38px; height: 38px; object-fit: contain; } /* Ukuran disamaratakan */
        .header-text { line-height: 1.2; }
        .greeting-time { font-size: 0.7rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; }
        .greeting-name { font-size: 0.75rem; font-weight: 700; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }

        /* HEADER JUDUL HALAMAN */
        .page-title-box { padding: 25px 20px 5px; text-align: center; }
        .page-title { font-size: 1.5rem; font-weight: 900; color: #0f172a; margin-bottom: 5px; }
        .page-subtitle { font-size: 0.8rem; font-weight: 600; color: #64748b; }

        /* BENTO CONTAINER */
        .bento-container { padding: 15px; display: flex; flex-direction: column; gap: 15px; }
        .bento-card { background: #ffffff; border-radius: 20px; padding: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.03); border: 1px solid #f8fafc; width: 100%; box-sizing: border-box; }

        /* FILTER FORM (CLEAN UI) */
        .filter-label { font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block; }
        .filter-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.85rem; font-weight: 600; padding: 12px 10px; color: #334155; box-shadow: none; transition: 0.3s; width: 100%; }
        .filter-input:focus { background: #ffffff; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); outline: none; }
        
        /* CUSTOM LIST HASIL PENCARIAN (PERBAIKAN RESPONSIVE 100%) */
        #tabelRiwayat_wrapper { width: 100%; overflow: hidden; padding: 0; margin: 0; }
        .table-custom { border-collapse: separate; border-spacing: 0 10px; margin-top: -10px !important; width: 100% !important; table-layout: fixed; }
        .table-custom thead { display: none; }
        .table-custom tbody tr { background-color: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: transform 0.2s; border-radius: 16px; border: 1px solid #e2e8f0; display: block; width: 100%; margin-bottom: 10px; }
        .table-custom tbody tr:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
        .table-custom tbody td { border: none; padding: 12px; background: transparent; display: block; width: 100%; box-sizing: border-box; }
        
        /* CALENDAR ICON STYLE */
        .cal-icon { background: #f1f5f9; border-radius: 12px; width: 45px; height: 45px; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; flex-shrink: 0; }
        .cal-day { font-size: 1rem; font-weight: 900; color: #0f172a; }
        .cal-month { font-size: 0.6rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; }

        /* RECORD DETAILS */
        .rec-details { flex-grow: 1; padding: 0 10px; overflow: hidden; min-width: 0; /* FIX buat HP */ }
        .rec-mk { font-size: 0.85rem; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; width: 100%; }
        .rec-time { font-size: 0.7rem; font-weight: 700; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* STATUS BADGE */
        .status-badge { padding: 5px 10px; border-radius: 10px; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0; text-align: center; min-width: 65px; }
        .bg-h { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
        .bg-t { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .bg-i { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
        .bg-s { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .bg-a { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

        /* --- BOTTOM NAVBAR (SLIM & TIGHT STYLE) --- */
        .bottom-nav { 
            position: fixed; 
            bottom: 0; 
            left: 0; 
            width: 100%; 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px); 
            border-top: 1px solid rgba(0,0,0,0.08); 
            display: flex; 
            justify-content: space-around; 
            align-items: center; 
            /* 👇 Padding ditekan biar lebih langsing */
            padding: 8px 10px 10px 10px; 
            z-index: 1050; 
            box-shadow: 0 -2px 15px rgba(0,0,0,0.05); 
        }

        .nav-item { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            text-decoration: none; 
            color: #94a3b8; 
            font-size: 0.6rem; /* Font lebih kecil dikit biar slim */
            font-weight: 700; 
            gap: 2px; /* Jarak icon ke teks dirapetin */
            transition: all 0.2s ease; 
        }

        .nav-item i { 
            font-size: 1.2rem; /* Icon dikecilin dikit biar gak bongsor */
            transition: transform 0.2s ease; 
        }

        .nav-item.active { color: #3b82f6; }
        /* Efek naik dikit pas aktif tapi gak lebay */
        .nav-item.active i { transform: translateY(-2px); }
        .nav-item:hover { color: #3b82f6; text-decoration: none; }
        
        /* DATA TABLES OVERRIDE */
        .dataTables_empty { font-weight: 800; color: #94a3b8; padding: 20px !important; border: none !important; text-align: center; }
    </style>
</head>
<body>

    <div class="app-header">
        <div class="d-flex align-items-center gap-2">
            <img src="../assets/img/FMIPA.png" alt="Logo Unimed" class="logo-header">
            <div class="header-text text-start">
                <div class="greeting-time">Selamat <?= (date('H')<11?'Pagi':(date('H')<15?'Siang':(date('H')<18?'Sore':'Malam'))) ?> 👋</div>
                <div class="greeting-name"><?= htmlspecialchars($nama_mhs) ?></div>
            </div>
        </div>
        
        <div>
            <img src="../assets/img/logo_kelas.jpg" alt="Logo Kelas" class="logo-header rounded-circle shadow-sm">
        </div>
    </div>
    <div class="page-title-box">
        <h2 class="page-title">Investigasi Kelas 🔍</h2>
        <div class="page-subtitle">Saring detail riwayat kehadiranmu</div>
    </div>

    <div class="bento-container">
        
        <div class="bento-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bolder m-0 text-dark"><i class="fas fa-sliders-h text-primary me-2"></i>Parameter Filter</h6>
                <button type="button" class="btn btn-light btn-sm rounded-pill fw-bold text-danger border" onclick="resetFilter()">
                    <i class="fas fa-sync-alt"></i> Reset
                </button>
            </div>
            
            <div class="row g-2"> <div class="col-12">
                    <label class="filter-label">Mata Kuliah</label>
                    <select id="filter_mk" class="filter-input">
                        <option value="">Semua Mata Kuliah</option>
                        <?php while($mk = mysqli_fetch_assoc($q_matkul)) echo "<option value='".htmlspecialchars($mk['nama_mk'])."'>".htmlspecialchars($mk['nama_mk'])."</option>"; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="filter-label">Status</label>
                    <select id="filter_status" class="filter-input">
                        <option value="">Semua</option>
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Telat</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpha">Alpha</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="filter-label">Pertemuan Ke</label>
                    <select id="filter_pertemuan" class="filter-input">
                        <option value="">Semua</option>
                        <?php for($i=1; $i<=16; $i++) echo "<option value='$i'>Pert. $i</option>"; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="filter-label">Dari Tanggal</label>
                    <input type="date" id="filter_start" class="filter-input">
                </div>
                <div class="col-6">
                    <label class="filter-label">Sampai Tanggal</label>
                    <input type="date" id="filter_end" class="filter-input">
                </div>
            </div>
        </div>

        <div style="width: 100%; overflow: hidden;">
            <h6 class="fw-bolder mb-3 ms-2 text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Hasil Pencarian</h6>
            
            <table id="tabelRiwayat" class="table table-custom m-0">
                <thead><tr><th>Data</th></tr></thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($q_riwayat)): 
                        $status = strtolower($row['status_absensi']);
                        $bg = 'bg-s';
                        if($status == 'hadir') $bg = 'bg-h';
                        elseif($status == 'terlambat') $bg = 'bg-t';
                        elseif($status == 'izin') $bg = 'bg-i';
                        elseif($status == 'alpha') $bg = 'bg-a';
                    ?>
                    <tr data-mk="<?= htmlspecialchars($row['nama_mk']) ?>" 
                        data-status="<?= $status ?>" 
                        data-pertemuan="<?= $row['pertemuan_ke'] ?>"
                        data-tanggal="<?= date('Y-m-d', strtotime($row['waktu_mulai'])) ?>">
                        
                        <td>
                            <div class="d-flex align-items-center w-100">
                                <div class="cal-icon shadow-sm">
                                    <span class="cal-day"><?= date('d', strtotime($row['waktu_mulai'])) ?></span>
                                    <span class="cal-month"><?= date('M', strtotime($row['waktu_mulai'])) ?></span>
                                </div>
                                
                                <div class="rec-details">
                                    <div class="rec-mk" title="<?= htmlspecialchars($row['nama_mk']) ?>">
                                        <?= htmlspecialchars($row['nama_mk']) ?>
                                    </div>
                                    <div class="rec-time">
                                        <i class="far fa-clock me-1"></i> <?= date('H:i', strtotime($row['waktu_mulai'])) ?> WIB 
                                        <span class="mx-1">•</span> 
                                        Pert. <?= $row['pertemuan_ke'] ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <span class="status-badge <?= $bg ?>"><?= $status ?></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

    <div class="bottom-nav">
        <a href="/rekap_absen" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="/filter_absen" class="nav-item active">
            <i class="fas fa-search"></i>
            <span>Filter</span>
        </a>
        <a href="/peringkat_absen" class="nav-item">
            <i class="fas fa-trophy"></i>
            <span>Peringkat</span>
        </a>
        <a href="?logout=true" class="nav-item text-danger">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
        </a>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables (Mode Siluman tanpa Search Box & Pagination atas)
            let table = $('#tabelRiwayat').DataTable({
                dom: 't', // 't' artinya cuma tabelnya doang yang di-render
                order: [], // Jangan di-order ulang, biarin urut waktu terbaru
                pageLength: -1, // Tampil semua tanpa terpotong page
                language: {
                    emptyTable: "Data tidak ditemukan. Coba ganti filternya bro!"
                }
            });

            // LOGIKA FILTER REALTIME (CLIENT-SIDE)
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex, rowData, counter) {
                    // Ambil nilai dari inputan
                    let fMk = $('#filter_mk').val().toLowerCase();
                    let fStatus = $('#filter_status').val().toLowerCase();
                    let fPertemuan = $('#filter_pertemuan').val();
                    let fStart = $('#filter_start').val();
                    let fEnd = $('#filter_end').val();

                    // Ambil data-atribut dari <tr> tabel
                    let tr = $(settings.aoData[dataIndex].nTr);
                    let rMk = tr.data('mk').toString().toLowerCase();
                    let rStatus = tr.data('status').toString().toLowerCase();
                    let rPertemuan = tr.data('pertemuan').toString();
                    let rDate = tr.data('tanggal').toString();

                    // Cek kecocokan satu-satu (Return false kalau ada yang gak cocok)
                    if (fMk && rMk !== fMk) return false;
                    if (fStatus && rStatus !== fStatus) return false;
                    if (fPertemuan && rPertemuan !== fPertemuan) return false;
                    if (fStart && rDate < fStart) return false;
                    if (fEnd && rDate > fEnd) return false;

                    return true; // Lolos semua filter = Tampilkan!
                }
            );

            // Trigger gambar ulang pas ada input yang berubah
            $('#filter_mk, #filter_status, #filter_pertemuan, #filter_start, #filter_end').on('change', function() {
                table.draw();
            });
        });

        // Fungsi Tombol Reset
        function resetFilter() {
            $('#filter_mk').val('');
            $('#filter_status').val('');
            $('#filter_pertemuan').val('');
            $('#filter_start').val('');
            $('#filter_end').val('');
            $('#tabelRiwayat').DataTable().draw();
        }
    </script>
</body>
</html>