<?php
session_start();
include '../koneksi.php';

$matkul = isset($_GET['matkul']) ? mysqli_real_escape_string($koneksi, $_GET['matkul']) : '';

if(empty($matkul)) {
    die("<h3>Mata kuliah belum dipilih! <a href='cetak_presensi.php'>Kembali</a></h3>");
}

// Ambil daftar pertemuan unik
$query = mysqli_query($koneksi, "SELECT DISTINCT 
    pertemuan_ke, 
    metode_pembelajaran, 
    tipe_kelas, 
    ruangan_link, 
    waktu_mulai, 
    waktu_selesai
    FROM absensi_real 
    WHERE nama_mk = '$matkul' 
    ORDER BY pertemuan_ke ASC");

if (!$query || mysqli_num_rows($query) == 0) {
    die("<h3>Tidak ada data pertemuan untuk mata kuliah ini.</h3>");
}

$data_pertemuan = [];
while($row = mysqli_fetch_assoc($query)) {
    $pertemuan = $row['pertemuan_ke'];
    $data_pertemuan[$pertemuan] = [
        'metode' => $row['metode_pembelajaran'],
        'tipe' => $row['tipe_kelas'],
        'ruangan_link' => $row['ruangan_link'],
        'waktu_mulai' => $row['waktu_mulai'],
        'waktu_selesai' => $row['waktu_selesai']
    ];
}

// Hitung jumlah hadir per pertemuan
foreach ($data_pertemuan as $pertemuan => &$data) {
    $q_hadir = mysqli_query($koneksi, "SELECT COUNT(DISTINCT nim) as total FROM absensi_real 
        WHERE nama_mk = '$matkul' 
        AND pertemuan_ke = $pertemuan 
        AND (status_absensi = 'hadir' OR status_absensi = 'terlambat')");
    $row_hadir = mysqli_fetch_assoc($q_hadir);
    $data['total_hadir'] = $row_hadir['total'] ?? 0;
}
unset($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Perkuliahan - <?= htmlspecialchars($matkul) ?></title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 12px; 
            color: #000; 
            margin: 0; 
            padding: 0; 
            line-height: 1.4;
        }
        
        /* Kop Surat */
        .kop-surat { 
            width: 100%; 
            border-bottom: 3px solid #000; 
            padding-bottom: 8px; 
            margin-bottom: 20px; 
            text-align: center; 
        }
        .kop-surat td { vertical-align: middle; }
        .kop-teks h1 { margin: 0; font-size: 14px; font-weight: bold; }
        .kop-teks h2 { margin: 3px 0; font-size: 13px; font-weight: bold; }
        .kop-teks p { margin: 0; font-size: 11px; }
        .logo { width: 70px; height: auto; }
        
        .judul { text-align: center; margin-bottom: 25px; }
        .judul h3 { margin: 0; font-size: 14px; text-decoration: underline; text-transform: uppercase; }
        .judul p { margin: 5px 0 0 0; font-size: 12px; font-weight: bold; }

        /* Kartu Pertemuan */
        .pertemuan-card {
            margin-bottom: 20px;
            page-break-inside: avoid;
            border: 1px solid #333;
            background: #fff;
            border-radius: 4px;
            overflow: hidden;
        }
        .pertemuan-title {
            background: #e9ecef;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #333;
            text-transform: uppercase;
        }
        table.pertemuan-detail {
            width: 100%;
            border-collapse: collapse;
        }
        table.pertemuan-detail td {
            border: 1px solid #333;
            padding: 8px 12px;
            vertical-align: top;
        }
        table.pertemuan-detail td.label {
            width: 30%;
            background-color: #f4f6f9;
            font-weight: bold;
        }
        .status-valid {
            background-color: #d1fae5;
            color: #059669;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            font-size: 11px;
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
            <td width="15%"><img src="../assets/img/FMIPA.png" class="logo" alt="Logo FMIPA"></td> 
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

    <div class="judul">
        <h3>BERITA ACARA PERKULIAHAN</h3>
        <p>Mata Kuliah: <?= htmlspecialchars($matkul) ?> | Kelas: PSIK 23 D | Semester: Genap</p>
    </div>

    <!-- LOOPING PERTEMUAN (tiap pertemuan jadi tabel sendiri) -->
    <?php foreach ($data_pertemuan as $pertemuan => $data): 
        $is_online = (strtolower($data['metode']) == 'online');
        $label_ruangan = $is_online ? 'Link Perkuliahan' : 'Ruangan';
        $waktu_mulai = date('d/m/Y H:i', strtotime($data['waktu_mulai']));
        $waktu_selesai = date('d/m/Y H:i', strtotime($data['waktu_selesai']));
        $ruangan_tampil = !empty($data['ruangan_link']) ? htmlspecialchars($data['ruangan_link']) : '-';
    ?>
    <div class="pertemuan-card">
        <div class="pertemuan-title">PERTEMUAN KE - <?= $pertemuan ?></div>
        <table class="pertemuan-detail">
            <tr><td class="label">Metode Pembelajaran</td><td><?= ucfirst(htmlspecialchars($data['metode'])) ?></td></tr>
            <tr><td class="label">Tipe Kelas</td><td><?= htmlspecialchars($data['tipe']) ?></td></tr>
            <tr><td class="label"><?= $label_ruangan ?></td><td><?= $ruangan_tampil ?></td></tr>
            <tr><td class="label">Waktu Mulai</td><td><?= $waktu_mulai ?> WIB</td></tr>
            <tr><td class="label">Waktu Berakhir</td><td><?= $waktu_selesai ?> WIB</td></tr>
            <tr><td class="label">Total Mahasiswa Hadir</td><td><?= $data['total_hadir'] ?> orang</td></tr>
            <tr><td class="label">Status</td><td><span class="status-valid">VALID</span></td></tr>
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