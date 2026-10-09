<?php 
include '../backend/data_jadwal.php'; 
include '../backend/cek_session.php'; 
if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah - Admin Premium</title>
    
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --bg-body: #f8fafc; --bg-card: #ffffff; --text-main: #0f172a; --text-sub: #64748b; --border-color: #e2e8f0; --accent-color: #6366f1; }
        [data-bs-theme="dark"] { --bg-body: #020617; --bg-card: #1e293b; --text-main: #f8fafc; --text-sub: #94a3b8; --border-color: #334155; --accent-color: #818cf8; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); transition: background 0.3s; }
        .premium-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow: hidden; }
        .animate-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .badge-day { min-width: 80px; text-align: center; font-weight: 600; }
        
        /* Animasi Pulse */
        @keyframes pulse-green { 0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); } 100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); } }
        .animate-pulse { animation: pulse-green 2s infinite; }
        
        @keyframes blink-animation { 50% { opacity: 0.3; } }
        .blink { animation: blink-animation 1s infinite; }
    </style>
</head>
<body>

    <nav class="navbar border-bottom sticky-top" style="background: var(--bg-card); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold" style="color: var(--accent-color);" href="admin_dashboard.php">
                <i class="fas fa-arrow-left me-2"></i> Dashboard
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold d-none d-md-block">Jadwal Kuliah</span>
                <button class="btn btn-outline-secondary btn-sm rounded-circle" style="width:35px;height:35px;" onclick="toggleTheme()"><i class="fas fa-moon" id="themeIcon"></i></button>
            </div>
        </div>
    </nav>

    <div class="container py-5 animate-up">
        
        <!-- Header Kreatif Diperbarui -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-4 rounded-4 shadow-sm border" style="background: var(--bg-card); border-color: var(--border-color);">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle" style="background: rgba(99, 102, 241, 0.1); color: var(--accent-color);">
                    <i class="fas fa-calendar-alt fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1" style="background: linear-gradient(45deg, var(--accent-color), #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Jadwal Kuliah PSIK 23 D</h3>
                    <p class="text-sub mb-0 small"><i class="fas fa-info-circle me-1"></i>Kelola jadwal perkuliahan dan bagikan format WA dengan satu klik ✨</p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap mt-3 mt-md-0">
                <button onclick="copyJadwalWA(this)" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2" title="Salin Jadwal ke WhatsApp">
                    <i class="fab fa-whatsapp fs-5"></i> Salin Jadwal
                </button>
                
                <a href="kelola_pengumuman.php" class="btn btn-warning rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2 text-dark">
                    <i class="fas fa-bullhorn fs-5"></i> Kelola Popup
                </a>

                <a href="tambah_jadwal.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2" style="background-color: var(--accent-color); border-color: var(--accent-color);">
                    <i class="fas fa-calendar-plus fs-5"></i> Tambah
                </a>
                <a href="admin_pengumuman.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2" style="background-color: var(--accent-color); border-color: var(--accent-color);">
                    <i class="fas fa-calendar-plus fs-5"></i> Pengumuman (Header)
                </a>
            </div>
        </div>

        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-main);">
                    <thead style="background: var(--bg-body);">
                        <tr>
                            <th class="py-3 ps-4">Waktu & Hari</th>
                            <th>Mata Kuliah</th>
                            <th>Dosen & Lokasi</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($semua_jadwal) && count($semua_jadwal) > 0): ?>
                            <?php foreach($semua_jadwal as $j): 
                                // LOGIKA PENGHITUNGAN TANGGAL & WAKTU ABSOLUT
                                $map_en = ['Senin'=>'Monday','Selasa'=>'Tuesday','Rabu'=>'Wednesday','Kamis'=>'Thursday','Jumat'=>'Friday','Sabtu'=>'Saturday','Minggu'=>'Sunday'];
                                $bulan_indo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                
                                $h_en = $map_en[$j['hari']];
                                $is_today = (date('l') == $h_en);
                                
                                $jam_mulai_arr = explode(':', $j['jam_mulai']);
                                $jam_selesai_arr = explode(':', $j['jam_selesai']);
                                
                                $tgl_time = $is_today ? time() : strtotime("next $h_en");
                                
                                $start_time = mktime((int)$jam_mulai_arr[0], (int)$jam_mulai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
                                $end_time = mktime((int)$jam_selesai_arr[0], (int)$jam_selesai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
                                
                                // Roll-over otomatis ke minggu depan jika jadwal hari ini sudah lewat/berakhir
                                if ($is_today && time() > $end_time) {
                                    $tgl_time = strtotime("next $h_en");
                                    $start_time = mktime((int)$jam_mulai_arr[0], (int)$jam_mulai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
                                    $end_time = mktime((int)$jam_selesai_arr[0], (int)$jam_selesai_arr[1], 0, date('m', $tgl_time), date('d', $tgl_time), date('Y', $tgl_time));
                                }
                                
                                $tgl_format = date('d', $tgl_time) . ' ' . $bulan_indo[(int)date('m', $tgl_time)] . ' ' . date('Y', $tgl_time);
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="text-primary fw-bold mb-1" style="font-size: 0.85rem;">
                                        <i class="fas fa-calendar-day me-1"></i> Hari/Tgl: <?= $j['hari'] ?>, <?= $tgl_format ?>
                                    </div>
                                    <div class="font-monospace fw-bold fs-6 text-sub">
                                        <i class="far fa-clock me-1"></i> <?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?> WIB
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold fs-5 mb-1" style="color: var(--text-main);"><?= $j['mata_kuliah'] ?></div>
                                    
                                    <!-- HITUNG MUNDUR (COUNTDOWN) -->
                                    <div class="countdown-timer font-monospace small" data-start="<?= $start_time ?>" data-end="<?= $end_time ?>"></div>
                                    
                                    <div class="small text-sub mt-2">
                                        <span class="badge bg-light text-dark border px-2 me-1">Pert. <?= $j['pertemuan_ke'] ?></span>
                                        <i class="fas fa-book mx-1"></i> Smt <?= $j['semester'] ?> • <?= $j['sks'] ?> SKS
                                        <br>
                                        <span class="font-monospace text-muted" style="font-size: 0.75rem;">Kode: <?= $j['kode_mk'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-user-tie text-sub me-2"></i>
                                        <span class="fw-bold"><?= $j['nama_dosen'] ?></span>
                                    </div>
                                    
                                    <?php if($j['jenis_kelas'] == 'online'): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 mb-1">
                                            <i class="fas fa-video me-1"></i> ONLINE
                                        </span>
                                        <?php if(!empty($j['link_online'])): ?>
                                            <br>
                                            <a href="<?= $j['link_online'] ?>" target="_blank" class="small text-decoration-none fw-bold text-primary mt-1 d-inline-block">
                                                <i class="fas fa-external-link-alt me-1"></i> Buka Link
                                            </a>
                                        <?php else: ?>
                                            <br><small class="text-muted fst-italic">Link kosong</small>
                                        <?php endif; ?>
                                        
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3">
                                            <i class="fas fa-map-marker-alt me-1"></i> <?= $j['ruangan'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit_jadwal.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-warning text-dark border shadow-sm me-1" title="Edit Jadwal"><i class="fas fa-edit"></i></a>
                                    <a href="../backend/proses_hapus_jadwal.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-light text-danger border" onclick="return confirm('Hapus jadwal <?= $j['mata_kuliah'] ?>?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-5"><p class="fw-bold text-sub">Belum ada jadwal.</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-bs-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            updateIcons(next);
        }
        function updateIcons(theme) { document.getElementById('themeIcon').className = theme === 'dark' ? 'fas fa-sun fs-5' : 'fas fa-moon fs-5'; }
        updateIcons(localStorage.getItem('theme') || 'light');

        // ==== LOGIKA COUNTDOWN REALTIME ====
        function initCountdowns() {
            const timers = document.querySelectorAll('.countdown-timer');
            
            function update() {
                const now = new Date().getTime();

                timers.forEach(timer => {
                    const start = parseInt(timer.getAttribute('data-start')) * 1000;
                    const end = parseInt(timer.getAttribute('data-end')) * 1000;

                    if (now < start) {
                        const distance = start - now;
                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        
                        let timeStr = '';
                        if(days > 0) timeStr += days + ' Hari ';
                        if(hours > 0) timeStr += hours + ' Jam ';
                        if(minutes > 0) timeStr += minutes + ' Menit ';
                        timeStr += seconds + ' Detik';
                        
                        timer.innerHTML = `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-50 px-2 py-1"><i class="fas fa-stopwatch me-1"></i> Dimulai dalam: ${timeStr}</span>`;
                    } else if (now >= start && now <= end) {
                        timer.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-50 px-2 py-1"><i class="fas fa-circle text-success blink me-1"></i> Sedang Berlangsung</span>`;
                    } else {
                        timer.innerHTML = `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-50 px-2 py-1"><i class="fas fa-check-circle me-1"></i> Telah Berakhir</span>`;
                    }
                });
            }

            update(); 
            setInterval(update, 1000); 
        }
        
        initCountdowns();

        // ==== LOGIKA COPY TEXT JADWAL WA ====
        function copyJadwalWA(btn) {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin fs-5"></i> Menyalin...';
            
            const jadwalData = <?php echo json_encode(isset($semua_jadwal) ? $semua_jadwal : []); ?>;
            const adminId = "<?php echo isset($_SESSION['id']) ? $_SESSION['id'] : '1'; ?>"; 
            
            if (jadwalData.length === 0) {
                alert('Tidak ada jadwal untuk disalin!');
                btn.innerHTML = originalHtml;
                return;
            }

            const formatter = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
            const dateHeader = formatter.format(new Date());
            
            // Format waktu cetak lengkap dengan detik (contoh: 07.23.45 am)
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12; 
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            hours = hours < 10 ? '0' + hours : hours; 
            const timeString = `${hours}.${minutes}.${seconds} ${ampm}`;
            const cetakString = `${dateHeader}, ${timeString}`;
            
            // GENERATE DIGITAL SIGNATURE ID PANJANG (Contoh: AUTH-1-8F9A2B-382910)
            const randomHex = Array.from({length: 8}, () => Math.floor(Math.random() * 16).toString(16)).join('').toUpperCase();
            const timeStampTail = now.getTime().toString().slice(-6);
            const digitalSignID = `AUTH-${adminId}-${randomHex}-${timeStampTail}`;
            
            const smt = jadwalData.length > 0 && jadwalData[0].semester ? jadwalData[0].semester : '6';
            const blokHeader = `Perkuliahan Blok 2\nSemester ${smt}\nWeek 1`;
            
            let textResult = `${dateHeader}\n${blokHeader}\n\n`;
            
            jadwalData.forEach(j => {
                const jamMulai = j.jam_mulai ? j.jam_mulai.substring(0,5).replace(':', '.') : '';
                const jamSelesai = j.jam_selesai ? j.jam_selesai.substring(0,5).replace(':', '.') : '';
                
                textResult += `Pertemuan ${j.pertemuan_ke || '-'}\n`;
                textResult += `\`${j.mata_kuliah || '-'}\`\n`; 
                textResult += `> Bobot: ${j.sks || '-'} SKS\n`;
                textResult += `> Kode MK: ${j.kode_mk || '-'}\n`;
                textResult += `> Pukul: ${jamMulai} - ${jamSelesai} WIB\n`;
                
                if(j.jenis_kelas === 'online') {
                    textResult += `> Link: ${j.link_online ? j.link_online : '-'}\n`;
                } else {
                    textResult += `> Ruang: ${j.ruangan || '-'}\n`;
                }
                
                textResult += `> Dosen Pengampu: ${j.nama_dosen || '-'}\n`;
                
                if (j.catatan && j.catatan.trim() !== '') {
                    textResult += `> Catatan: ${j.catatan}\n`;
                }
                
                textResult += `\n`;
            });
            
            // Tambahan info cetak dan selengkapnya sesuai permintaan
            textResult += `_dicetak otomatis pada: ${cetakString}_\n`;
            textResult += `_dicetak oleh: ${digitalSignID}_\n`;  
            textResult += `baca selengkapnya:\nhttps://psik23d.kesug.com/jadwal_kelas`;
            
            navigator.clipboard.writeText(textResult).then(() => {
                btn.innerHTML = '<i class="fas fa-check fs-5"></i> Disalin!';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-dark');
                
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-dark');
                    btn.classList.add('btn-success');
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin: ', err);
                alert('Gagal menyalin jadwal ke clipboard.');
                btn.innerHTML = originalHtml;
            });
        }
    </script>
</body>
</html>