<?php
/**
 * SKRIP LOGIKA GRAFIK & CHART (GAYA VISUAL BARU)
 */

if (!isset($koneksi)) {
    die("<div class='alert alert-danger'>Error: Koneksi database tidak ditemukan di user_grafik.php</div>");
}

// ==========================================
// 1. DATA MAHASISWA (GENDER)
// ==========================================
$qm_l = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='mahasiswa' AND (jenis_kelamin='L' OR jenis_kelamin='Laki-laki')");
$qm_p = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='mahasiswa' AND (jenis_kelamin='P' OR jenis_kelamin='Perempuan')");
$dm_l = ($qm_l) ? mysqli_fetch_assoc($qm_l)['total'] : 0;
$dm_p = ($qm_p) ? mysqli_fetch_assoc($qm_p)['total'] : 0;

// ==========================================
// 2. DATA DOSEN (GENDER)
// ==========================================
$qd_l = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM dosen WHERE jenis_kelamin='L' OR jenis_kelamin='Laki-laki'");
$qd_p = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM dosen WHERE jenis_kelamin='P' OR jenis_kelamin='Perempuan'");
$dd_l = ($qd_l) ? mysqli_fetch_assoc($qd_l)['total'] : 0;
$dd_p = ($qd_p) ? mysqli_fetch_assoc($qd_p)['total'] : 0;

// ==========================================
// 3. DATA DOSEN (JABATAN) - AUTO CLEANING
// ==========================================
$q_jab = mysqli_query($koneksi, "SELECT jabatan FROM dosen");
$temp_stats = []; 

if ($q_jab) {
    while($r = mysqli_fetch_assoc($q_jab)) {
        $raw_text = $r['jabatan']; 
        if (strpos($raw_text, '/') !== false) {
            $parts = explode('/', $raw_text);
            $clean_text = trim(end($parts));
        } else {
            $clean_text = trim($raw_text);
        }
        if (empty($clean_text) || $clean_text == '-') { $clean_text = 'Lainnya'; }
        if (isset($temp_stats[$clean_text])) { $temp_stats[$clean_text]++; } else { $temp_stats[$clean_text] = 1; }
    }
}
arsort($temp_stats); // Urutkan dari terbanyak
$label_jabatan = array_keys($temp_stats);
$data_jabatan = array_values($temp_stats);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // --- PALET WARNA BARU (Meniru Gambar Contoh) ---
    // Hijau Muda, Oranye, Biru Tua, Kuning Emas, Merah, Abu-abu Biru
    const chartColors = ['#a7f3d0', '#f97316', '#3b4252', '#f59e0b', '#ef4444', '#94a3b8'];

    // --- KONFIGURASI UMUM ---
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#64748b'; // Warna teks label

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom', // Legenda di bawah agar rapi
                labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                titleColor: '#1e293b',
                bodyColor: '#334155',
                borderColor: '#e2e8f0',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                displayColors: true,
                boxPadding: 6
            }
        },
        borderWidth: 2,
        borderColor: '#ffffff', // Garis putih pemisah antar slice
    };

    // ==========================================
    // 1. CHART MAHASISWA (DONUT - Gaya Kiri)
    // ==========================================
    const ctxMhs = document.getElementById('chartMahasiswa');
    if (ctxMhs) {
        let dataMhs = [<?= $dm_l ?>, <?= $dm_p ?>];
        if(dataMhs[0] === 0 && dataMhs[1] === 0) dataMhs = [1, 1]; // Dummy jika kosong

        // Logika Offset: Slice terbesar akan sedikit keluar
        const maxVal = Math.max(...dataMhs);
        const offsets = dataMhs.map(val => (val === maxVal && val > 0) ? 20 : 0);

        new Chart(ctxMhs, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: dataMhs,
                    // Pakai 2 warna pertama dari palet baru
                    backgroundColor: ['#3b82f6', '#ec4899'], // Biru & Pink
                    hoverOffset: 25, // Efek hover lebih besar
                    offset: offsets // Terapkan offset pada slice terbesar
                }]
            },
            options: {
                ...commonOptions,
                cutout: '60%', // Lubang tengah sedikit lebih kecil
                plugins: {
                    ...commonOptions.plugins,
                    // Hapus plugin centerText agar tengahnya kosong persis contoh
                }
            }
        });
    }

    // ==========================================
    // 2. CHART DOSEN GENDER (DONUT - Gaya Kiri)
    // ==========================================
    const ctxDosenGender = document.getElementById('chartDosenGender');
    if (ctxDosenGender) {
        let dataDG = [<?= $dd_l ?>, <?= $dd_p ?>];
        if(dataDG[0] === 0 && dataDG[1] === 0) dataDG = [1, 1];

        const maxVal = Math.max(...dataDG);
        const offsets = dataDG.map(val => (val === maxVal && val > 0) ? 20 : 0);

        new Chart(ctxDosenGender, {
            type: 'doughnut',
            data: {
                labels: ['Bapak', 'Ibu'],
                datasets: [{
                    data: dataDG,
                    // Pakai warna Hijau Muda & Biru Tua
                    backgroundColor: [chartColors[0], chartColors[2]], 
                    hoverOffset: 25,
                    offset: offsets
                }]
            },
            options: {
                ...commonOptions,
                cutout: '60%'
            }
        });
    }

    // ==========================================
    // 3. CHART JABATAN DOSEN (BAR CHART - Gaya Kanan)
    // ==========================================
    const ctxDosenJabatan = document.getElementById('chartDosenJabatan');
    if (ctxDosenJabatan) {
        let labelJ = <?= json_encode($label_jabatan) ?>;
        let dataJ = <?= json_encode($data_jabatan) ?>;
        if(labelJ.length === 0) { labelJ = ["No Data"]; dataJ = [0]; }

        new Chart(ctxDosenJabatan, {
            type: 'bar', // UBAH JADI TYPE BAR
            data: {
                labels: labelJ, 
                datasets: [{
                    label: 'Jumlah Dosen',
                    data: dataJ,
                    // Gunakan seluruh palet warna agar tiap batang beda warna
                    backgroundColor: chartColors,
                    borderRadius: 6, // Sudut batang membulat
                    borderWidth: 0, // Hapus border putih
                    barPercentage: 0.7, // Lebar batang
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, // Sembunyikan legenda untuk Bar Chart
                    tooltip: commonOptions.plugins.tooltip
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9', // Warna grid tipis
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 1, // Skala bilangan bulat
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false }, // Hapus grid vertikal
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }
</script>