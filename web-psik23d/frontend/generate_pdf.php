<?php
error_reporting(0);

session_start();
include '../koneksi.php';

$matkul = isset($_GET['matkul']) ? mysqli_real_escape_string($koneksi, $_GET['matkul']) : '';

if(empty($matkul)) {
    die("<h3>Mata kuliah belum dipilih bro! Kembali ke halaman sebelumnya.</h3>");
}

// 1. CARI TANGGAL TIAP PERTEMUAN
$q_tgl = mysqli_query($koneksi, "SELECT pertemuan_ke, MIN(waktu_mulai) as tgl FROM absensi_real WHERE nama_mk = '$matkul' GROUP BY pertemuan_ke ORDER BY pertemuan_ke ASC");

$tgl_data = [];
$max_pertemuan = 0;

while($t = mysqli_fetch_assoc($q_tgl)) {
    $p = (int)$t['pertemuan_ke'];
    $tgl_data[$p] = date('d/m', strtotime($t['tgl']));
    if($p > $max_pertemuan) $max_pertemuan = $p;
}
if($max_pertemuan == 0) $max_pertemuan = 1;

// 2. AMBIL SEMUA MAHASISWA
$q_mhs = mysqli_query($koneksi, "SELECT nim, nama FROM users WHERE role = 'mahasiswa' ORDER BY CAST(nim AS UNSIGNED) ASC");
$mahasiswa = [];
while($row = mysqli_fetch_assoc($q_mhs)) {
    $mahasiswa[$row['nim']] = $row;
}

// 3. AMBIL DATA ABSENSI
$q_absen = mysqli_query($koneksi, "SELECT nim, pertemuan_ke, status_absensi FROM absensi_real WHERE nama_mk = '$matkul'");
$absen_data = [];
while($a = mysqli_fetch_assoc($q_absen)) {
    $absen_data[$a['nim']][(int)$a['pertemuan_ke']] = strtolower($a['status_absensi']);
}

// Hitung rekap per mahasiswa (untuk bagian detail)
$rekap_per_mhs = [];
foreach ($mahasiswa as $nim => $mhs) {
    $hadir = 0; $izin = 0; $sakit = 0; $alpha = 0;
    if (isset($absen_data[$nim])) {
        foreach ($absen_data[$nim] as $status) {
            if ($status == 'hadir' || $status == 'terlambat') $hadir++;
            elseif ($status == 'izin') $izin++;
            elseif ($status == 'sakit') $sakit++;
            elseif ($status == 'alpha') $alpha++;
        }
    }
    $persentase = ($max_pertemuan > 0) ? round(($hadir / $max_pertemuan) * 100, 2) : 0;
    $rekap_per_mhs[$nim] = [
        'nama' => $mhs['nama'],
        'nim' => $nim,
        'hadir' => $hadir,
        'izin' => $izin,
        'sakit' => $sakit,
        'alpha' => $alpha,
        'persentase' => $persentase
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Presensi PDF - <?= htmlspecialchars($matkul) ?></title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; }
        
        .kop-surat { width: 100%; border-bottom: 3px solid #000; padding-bottom: 8px; margin-bottom: 20px; text-align: center; }
        .kop-surat td { vertical-align: middle; }
        .kop-teks h1 { margin: 0; font-size: 16px; font-weight: bold; }
        .kop-teks h2 { margin: 3px 0; font-size: 14px; font-weight: bold; }
        .kop-teks p { margin: 0; font-size: 11px; }
        .logo { width: 75px; height: auto; }
        
        .judul { text-align: center; margin-bottom: 15px; line-height: 1.4; }
        .judul h3 { margin: 0; font-size: 14px; text-decoration: underline; text-transform: uppercase; }
        .judul p { margin: 3px 0; font-size: 12px; font-weight: bold;}

        /* Tabel Presensi Utama */
        table.data { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        table.data th { background-color: #e2e8f0; font-weight: bold; -webkit-print-color-adjust: exact; }
        table.data td.kiri { text-align: left; padding-left: 10px; }
        
        .badge-status { font-weight: bold; font-size: 11px; }
        .H { color: #15803d; } .T { color: #b45309; } .A { color: #b91c1c; } .S { color: #1d4ed8; } .I { color: #6d28d9; } .- { color: #94a3b8; } 

        /* Tabel Detail per Mahasiswa - Compact & Elegan */
        .subjudul { text-align: center; margin-top: 30px; margin-bottom: 15px; }
        .subjudul h4 { margin: 0; font-size: 13px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        
        .mhs-card {
            margin-bottom: 12px;
            page-break-inside: avoid;
            border: 1px solid #aaa;
            border-radius: 4px;
            overflow: hidden;
        }
        .mhs-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .mhs-card th, .mhs-card td {
            border: none;
            padding: 4px 8px;
            font-size: 9px;
            vertical-align: middle;
        }
        .mhs-card .header-card {
            background-color: #e9ecef;
            font-weight: bold;
            border-bottom: 1px solid #aaa;
        }
        .mhs-card .header-card th {
            font-size: 10px;
            text-align: left;
            background-color: #f1f3f5;
        }
        .mhs-card .row-label {
            background-color: #f8fafc;
            width: 35%;
            font-weight: 600;
            border-right: 1px solid #dee2e6;
        }
        .mhs-card .row-value {
            text-align: left;
        }
        .mhs-card .persen {
            font-weight: bold;
            color: #0b5e42;
            background-color: #d1fae5;
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
        }
        .mhs-card .keterangan {
            background-color: #f9f9f9;
            font-size: 8px;
            text-align: left;
            border-top: 1px solid #ddd;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="kop-surat">
        <tr>
            <td width="15%"><img src="../assets/img/FMIPA.png" class="logo" alt="Logo"></td> 
            <td width="70%" class="kop-teks">
                <h1>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h1>
                <h2>UNIVERSITAS NEGERI MEDAN</h2>
                <p>FAKULTAS MATEMATIKA DAN ILMU PENGETAHUAN ALAM</p>
                <p>PROGRAM STUDI PENDIDIKAN ILMU KOMPUTER</p>
                <p>Jalan Willem Iskandar Psr. V Medan Estate, Medan, Indonesia, 20221</p>
            </td>
            <td width="15%"><img src="../assets/img/logo_kelas.jpg" class="logo" style="border-radius:50%;" alt="Logo Kelas"></td>
        </tr>
    </table>

    <!-- JUDUL UTAMA -->
    <div class="judul">
        <h3>REKAP PRESENSI DIGITAL MAHASISWA</h3>
        <p>Kelas: PSIK 23 D &nbsp;|&nbsp; Semester: Genap &nbsp;|&nbsp; Mata Kuliah: <?= htmlspecialchars($matkul) ?></p>
    </div>

    <!-- TABEL PERTAMA: REKAP PER PERTEMUAN (kolom NIM lebih rapat) -->
    <table class="data">
        <thead>
            <tr>
                <th rowspan="2" width="5%">No</th>
                <th rowspan="2" width="10%">NIM</th>
                <th rowspan="2" width="25%">Nama Mahasiswa</th>
                <th colspan="<?= $max_pertemuan ?>">Pertemuan Ke-</th>
            </tr>
            <tr>
                <?php for($i=1; $i<=$max_pertemuan; $i++): ?>
                    <th width="3%">P<?= $i ?><br><small><?= isset($tgl_data[$i]) ? $tgl_data[$i] : '-' ?></small></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($mahasiswa as $nim => $mhs): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($mhs['nim']) ?></td>
                <td class="kiri"><?= htmlspecialchars($mhs['nama']) ?></td>
                <?php for($i=1; $i<=$max_pertemuan; $i++): 
                    $stat = isset($absen_data[$nim][$i]) ? $absen_data[$nim][$i] : '';
                    $inisial = '-';
                    if ($stat == 'hadir') $inisial = 'H';
                    elseif ($stat == 'terlambat') $inisial = 'T';
                    elseif ($stat == 'sakit') $inisial = 'S';
                    elseif ($stat == 'izin') $inisial = 'I';
                    elseif ($stat == 'alpha') $inisial = 'A';
                ?>
                    <td><span class="badge-status <?= $inisial ?>"><?= $inisial ?></span></td>
                <?php endfor; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ========== BAGIAN REKAP DETAIL PER MAHASISWA (COMPACT & RAPI) ========== -->
    <div class="subjudul">
        <h4>REKAP DETAIL KEHADIRAN PER MAHASISWA</h4>
    </div>

    <?php foreach ($rekap_per_mhs as $data): ?>
    <div class="mhs-card">
        <table>
            <!-- Header Nama dan NIM -->
            <tr class="header-card">
                <th colspan="2"><?= htmlspecialchars($data['nama']) ?> (<?= htmlspecialchars($data['nim']) ?>)</th>
            </tr>
            <!-- Baris Hadir -->
            <tr>
                <td class="row-label">Jumlah Hadir</td>
                <td class="row-value"><?= $data['hadir'] ?> kali</td>
            </tr>
            <!-- Baris Izin -->
            <tr>
                <td class="row-label">Jumlah Izin</td>
                <td class="row-value"><?= $data['izin'] ?> kali</td>
            </tr>
            <!-- Baris Sakit -->
            <tr>
                <td class="row-label">Jumlah Sakit</td>
                <td class="row-value"><?= $data['sakit'] ?> kali</td>
            </tr>
            <!-- Baris Alpha -->
            <tr>
                <td class="row-label">Tanpa Keterangan (Alpha)</td>
                <td class="row-value"><?= $data['alpha'] ?> kali</td>
            </tr>
            <!-- Baris Persentase -->
            <tr>
                <td class="row-label">Persentase Kehadiran</td>
                <td class="row-value"><span class="persen"><?= number_format($data['persentase'], 2) ?>%</span></td>
            </tr>
            <!-- Baris Keterangan -->
            <tr class="keterangan">
                <td colspan="2">
                    Keterangan: Persentase = (Hadir / Total Pertemuan) × 100%. Bobot nilai absensi 5%.
                </td>
            </tr>
        </table>
    </div>
    <?php endforeach; ?>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>