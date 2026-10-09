<?php 
session_start();
include '../koneksi.php'; 

// Ambil semua data absensi
$query = mysqli_query($koneksi, "SELECT * FROM absensi_real ORDER BY waktu_mulai DESC, nama_lengkap ASC");

// Data untuk filter
$q_matkul = mysqli_query($koneksi, "SELECT DISTINCT nama_mk FROM absensi_real ORDER BY nama_mk ASC");
$q_dosen = mysqli_query($koneksi, "SELECT DISTINCT dosen_pengampu FROM absensi_real ORDER BY dosen_pengampu ASC");
$q_pertemuan = mysqli_query($koneksi, "SELECT DISTINCT pertemuan_ke FROM absensi_real ORDER BY pertemuan_ke ASC");
$q_mhs = mysqli_query($koneksi, "SELECT DISTINCT nama_lengkap FROM absensi_real ORDER BY nama_lengkap ASC");
$q_tipe = mysqli_query($koneksi, "SELECT DISTINCT tipe_kelas FROM absensi_real WHERE tipe_kelas IS NOT NULL ORDER BY tipe_kelas ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Absensi - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; padding-bottom: 2rem; }
        
        .header-soft {
            background: linear-gradient(120deg, #6366f1, #8b5cf6);
            padding: 2rem 1rem 1.8rem;
            border-radius: 0 0 2rem 2rem;
            color: white;
            text-align: center;
            box-shadow: 0 8px 20px rgba(99,102,241,0.2);
            margin-bottom: 2rem;
        }

        .glass-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(2px);
            border-radius: 1.5rem;
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.5);
            padding: 1.8rem;
        }

        .filter-box {
            background: #f8fafc;
            border-radius: 1.2rem;
            padding: 1.5rem;
            margin-bottom: 1.8rem;
            border: 1px solid #eef2ff;
        }
        .filter-box label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }
        .filter-box .form-select, .filter-box .form-control {
            border-radius: 0.8rem;
            font-size: 0.85rem;
            border-color: #e2e8f0;
        }

        .table-custom {
            border-collapse: separate;
            border-spacing: 0 0.6rem;
            width: 100%;
        }
        .table-custom thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem 0.8rem;
            border: none;
        }
        .table-custom tbody tr {
            background: white;
            border-radius: 1rem;
            transition: all 0.2s;
        }
        .table-custom tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -10px rgba(0,0,0,0.1);
        }
        .table-custom tbody td {
            background: white;
            padding: 1rem 0.8rem;
            vertical-align: middle;
            border-top: 1px solid #f0f2f5;
            border-bottom: 1px solid #f0f2f5;
            font-size: 0.85rem;
        }
        .table-custom tbody td:first-child {
            border-left: 1px solid #f0f2f5;
            border-top-left-radius: 1rem;
            border-bottom-left-radius: 1rem;
        }
        .table-custom tbody td:last-child {
            border-right: 1px solid #f0f2f5;
            border-top-right-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }

        .badge-status {
            padding: 0.35rem 0.8rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-hadir { background: #d1fae5; color: #059669; }
        .status-terlambat { background: #fef3c7; color: #d97706; }
        .status-izin { background: #e0f2fe; color: #0284c7; }
        .status-sakit { background: #f1f5f9; color: #475569; }
        .status-alpha { background: #fee2e2; color: #dc2626; }

        .btn-action {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: 0.2s;
            border: none;
            margin: 0 2px;
        }
        .btn-edit-status { background: #e0e7ff; color: #4f46e5; }
        .btn-edit-status:hover { background: #4f46e5; color: white; transform: scale(1.05); }
        .btn-edit-kelas { background: #fff1e6; color: #f97316; }
        .btn-edit-kelas:hover { background: #f97316; color: white; transform: scale(1.05); }
        .btn-hapus { background: #fee2e2; color: #dc2626; }
        .btn-hapus:hover { background: #dc2626; color: white; transform: scale(1.05); }

        .dataTables_filter input {
            border-radius: 2rem !important;
            padding: 0.5rem 1rem !important;
            border: 1px solid #e2e8f0 !important;
            background: #f8fafc;
        }
        .dataTables_length select {
            border-radius: 2rem !important;
            padding: 0.3rem 1rem !important;
        }
    </style>
</head>
<body>

<div class="header-soft">
    <h4 class="fw-bold mb-1"><i class="fas fa-database me-2"></i>Database Rekap Absensi</h4>
    <p class="opacity-75 small mb-0">Kelola Data Presensi Kelas PSIK 23 D</p>
</div>

<div class="container">
    <div class="glass-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-table-list text-primary me-2"></i>Data Master Absensi</h5>
            <div class="d-flex gap-2">
                <a href="admin_dashboard.php" class="btn btn-light fw-semibold rounded-pill border px-4 shadow-sm">Kembali</a>
                <a href="input_absensi.php" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-2"></i> Tambah Kelas
                </a>
            </div>
        </div>

        <!-- Filter Box -->
        <div class="filter-box">
            <div class="row g-3">
                <div class="col-md-2">
                    <label>Dari Tanggal</label>
                    <input type="date" id="min_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Sampai Tanggal</label>
                    <input type="date" id="max_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Mahasiswa</label>
                    <select id="filter_mhs" class="form-select">
                        <option value="">Semua</option>
                        <?php while($r = mysqli_fetch_assoc($q_mhs)) echo "<option value='".htmlspecialchars($r['nama_lengkap'])."'>".htmlspecialchars($r['nama_lengkap'])."</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Mata Kuliah</label>
                    <select id="filter_matkul" class="form-select">
                        <option value="">Semua</option>
                        <?php while($r = mysqli_fetch_assoc($q_matkul)) echo "<option value='".htmlspecialchars($r['nama_mk'])."'>".htmlspecialchars($r['nama_mk'])."</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Tipe Kelas</label>
                    <select id="filter_tipe" class="form-select">
                        <option value="">Semua</option>
                        <?php while($r = mysqli_fetch_assoc($q_tipe)) echo "<option value='".htmlspecialchars($r['tipe_kelas'])."'>".htmlspecialchars($r['tipe_kelas'])."</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Pertemuan</label>
                    <select id="filter_pertemuan" class="form-select">
                        <option value="">Semua</option>
                        <?php while($r = mysqli_fetch_assoc($q_pertemuan)) echo "<option value='{$r['pertemuan_ke']}'>Pertemuan {$r['pertemuan_ke']}</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Dosen</label>
                    <select id="filter_dosen" class="form-select">
                        <option value="">Semua</option>
                        <?php while($r = mysqli_fetch_assoc($q_dosen)) echo "<option value='".htmlspecialchars($r['dosen_pengampu'])."'>".htmlspecialchars($r['dosen_pengampu'])."</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Status</label>
                    <select id="filter_status" class="form-select">
                        <option value="">Semua</option>
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpha">Alpha</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-danger w-100 fw-semibold rounded-pill py-2" onclick="resetFilter()">
                        <i class="fas fa-sync-alt me-2"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabel -->
        <div class="table-responsive">
            <table id="tabelAbsensi" class="table table-custom w-100">
                <thead>
                    <tr>
                        <th>Waktu Mulai</th>
                        <th>Mata Kuliah & Dosen</th>
                        <th>Tipe Kelas</th>
                        <th>NIM / Nama Mahasiswa</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr id="row_<?= $row['id'] ?>" 
                            data-tanggal="<?= date('Y-m-d', strtotime($row['waktu_mulai'])) ?>"
                            data-matkul="<?= htmlspecialchars($row['nama_mk']) ?>"
                            data-tipe="<?= htmlspecialchars($row['tipe_kelas']) ?>"
                            data-dosen="<?= htmlspecialchars($row['dosen_pengampu']) ?>"
                            data-mhs="<?= htmlspecialchars($row['nama_lengkap']) ?>"
                            data-pertemuan="<?= $row['pertemuan_ke'] ?>"
                            data-status="<?= strtolower($row['status_absensi']) ?>">
                            <td>
                                <span class="d-block fw-semibold text-dark"><?= date('d M Y', strtotime($row['waktu_mulai'])) ?></span>
                                <span class="small text-muted"><?= date('H:i', strtotime($row['waktu_mulai'])) ?> WIB</span>
                            </td>
                            <td>
                                <span class="d-block fw-semibold text-dark"><?= htmlspecialchars($row['nama_mk']) ?></span>
                                <span class="small text-primary fw-semibold">Pertemuan <?= $row['pertemuan_ke'] ?></span>
                                <span class="small text-muted d-block mt-1"><i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($row['dosen_pengampu']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-semibold">
                                    <?= htmlspecialchars($row['tipe_kelas']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="d-block fw-semibold text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                                <span class="small text-muted"><?= htmlspecialchars($row['nim']) ?></span>
                            </td>
                            <td>
                                <span class="badge-status status-<?= strtolower($row['status_absensi']) ?>" id="status_badge_<?= $row['id'] ?>">
                                    <?= htmlspecialchars($row['status_absensi']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn-action btn-edit-status me-1" title="Edit Status Mahasiswa" 
                                    onclick="bukaModalEdit(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['nama_lengkap'])) ?>', '<?= addslashes(htmlspecialchars($row['nama_mk'])) ?>', '<?= strtolower($row['status_absensi']) ?>')">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <a href="edit_absensi.php?id=<?= $row['id'] ?>" class="btn-action btn-edit-kelas me-1" title="Edit Seluruh Data Pertemuan">
                                    <i class="fas fa-chalkboard-user"></i>
                                </a>
                                <button class="btn-action btn-hapus" title="Hapus Data" 
                                    onclick="hapusData(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['nama_lengkap'])) ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data absensi</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Status -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Edit Status Absen</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="small text-muted mb-4">Ubah kehadiran <strong id="edit_nama" class="text-dark"></strong> untuk mata kuliah <strong id="edit_mk" class="text-primary"></strong>.</p>
                <input type="hidden" id="edit_id">
                <label class="form-label small fw-bold">Pilih Status Baru:</label>
                <select id="edit_status" class="form-select form-select-lg rounded-3 mb-3">
                    <option value="hadir">Hadir</option>
                    <option value="terlambat">Terlambat</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alpha">Alpha</option>
                </select>
                <button type="button" class="btn btn-primary w-100 rounded-pill fw-bold py-2" onclick="simpanEdit()">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let table;
    let modalEditObj;

    $(document).ready(function() {
        modalEditObj = new bootstrap.Modal(document.getElementById('modalEdit'));

        // Inisialisasi DataTables tanpa explicit columns, hanya columnDefs untuk kolom aksi
        table = $('#tabelAbsensi').DataTable({
            language: {
                search: "Cepat Cari:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan"
            },
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 5, orderable: false } // kolom Aksi (index 5) tidak bisa di-sort
            ],
            // Jika terjadi error, jangan hancurkan tabel
            destroy: true,
            // Pastikan tidak ada autoWidth yang bisa mengganggu
            autoWidth: false
        });

        // Filter kustom
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, counter) {
            let minDate = $('#min_date').val();
            let maxDate = $('#max_date').val();
            let fMatkul = $('#filter_matkul').val().toLowerCase();
            let fTipe = $('#filter_tipe').val().toLowerCase();
            let fDosen = $('#filter_dosen').val().toLowerCase();
            let fMhs = $('#filter_mhs').val().toLowerCase();
            let fPertemuan = $('#filter_pertemuan').val();
            let fStatus = $('#filter_status').val().toLowerCase();

            let tr = $(settings.aoData[dataIndex].nTr);
            let rowDate = tr.data('tanggal');
            let rowMatkul = tr.data('matkul') ? tr.data('matkul').toString().toLowerCase() : '';
            let rowTipe = tr.data('tipe') ? tr.data('tipe').toString().toLowerCase() : '';
            let rowDosen = tr.data('dosen') ? tr.data('dosen').toString().toLowerCase() : '';
            let rowMhs = tr.data('mhs') ? tr.data('mhs').toString().toLowerCase() : '';
            let rowPertemuan = tr.data('pertemuan') ? tr.data('pertemuan').toString() : '';
            let rowStatus = tr.data('status') ? tr.data('status').toString().toLowerCase() : '';

            if (minDate && rowDate < minDate) return false;
            if (maxDate && rowDate > maxDate) return false;
            if (fMatkul && rowMatkul !== fMatkul) return false;
            if (fTipe && rowTipe !== fTipe) return false;
            if (fDosen && rowDosen !== fDosen) return false;
            if (fMhs && rowMhs !== fMhs) return false;
            if (fPertemuan && rowPertemuan !== fPertemuan) return false;
            if (fStatus && rowStatus !== fStatus) return false;

            return true;
        });

        // Trigger redraw saat filter berubah
        $('#min_date, #max_date, #filter_matkul, #filter_tipe, #filter_dosen, #filter_mhs, #filter_pertemuan, #filter_status').on('change', function() {
            table.draw();
        });
    });

    function resetFilter() {
        $('#min_date, #max_date, #filter_matkul, #filter_tipe, #filter_dosen, #filter_mhs, #filter_pertemuan, #filter_status').val('');
        table.search('').draw();
    }

    function bukaModalEdit(id, nama, mk, statusLama) {
        $('#edit_id').val(id);
        $('#edit_nama').text(nama);
        $('#edit_mk').text(mk);
        $('#edit_status').val(statusLama);
        modalEditObj.show();
    }

    function simpanEdit() {
        let id = $('#edit_id').val();
        let statusBaru = $('#edit_status').val();

        let formData = new FormData();
        formData.append('action', 'edit');
        formData.append('id', id);
        formData.append('status_absensi', statusBaru);

        fetch('../backend/crud_absensi.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'sukses') {
                modalEditObj.hide();
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Status berhasil diubah!', showConfirmButton: false, timer: 2000 });
                $('#status_badge_' + id).removeClass().addClass('badge-status status-' + statusBaru).text(statusBaru);
                $('#row_' + id).attr('data-status', statusBaru);
                table.draw();
            } else {
                Swal.fire('Error', data.pesan, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
        });
    }

    function hapusData(id, nama) {
        Swal.fire({
            title: 'Hapus Data?',
            html: `Yakin mau hapus data absensi <b>${nama}</b> permanen?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData();
                formData.append('action', 'hapus');
                formData.append('id', id);

                fetch('../backend/crud_absensi.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'sukses') {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Data musnah!', showConfirmButton: false, timer: 2000 });
                        table.row($('#row_' + id)).remove().draw();
                    } else {
                        Swal.fire('Error', data.pesan, 'error');
                    }
                });
            }
        });
    }
</script>
</body>
</html>