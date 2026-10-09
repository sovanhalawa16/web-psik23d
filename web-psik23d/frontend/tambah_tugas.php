<?php 
session_start();
include '../koneksi.php'; 

// Ambil data Dosen untuk dropdown
$q_dosen = mysqli_query($koneksi, "SELECT id, nama FROM dosen ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tugas Baru - Admin PSIK 23 D</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
        body { background-color: #f4f7fb; font-family: 'Plus Jakarta Sans', sans-serif; color: #334155; }
        
        .header-modern {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); padding: 30px 0 25px;
            color: white; text-align: center; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2); margin-bottom: 30px;
        }

        .custom-card {
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 40px; margin-bottom: 30px;
        }

        .form-label { font-weight: 700; font-size: 0.85rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control, .form-select { border-radius: 12px; padding: 12px; border: 1.5px solid #e2e8f0; font-size: 0.95rem; }
        .form-control:focus, .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }

        .section-title { font-weight: 800; color: #0f172a; font-size: 1.1rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px dashed #e2e8f0; }

        /* Editor */
        #editor-subject { min-height: 80px; background: white; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
        #editor-deskripsi { min-height: 150px; background: white; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
        .ql-toolbar { border-top-left-radius: 12px; border-top-right-radius: 12px; background: #f8fafc; }
    </style>
</head>
<body>

    <div class="header-modern">
        <div class="container position-relative">
            <h3 class="fw-bolder mb-1">Buat Tugas Baru</h3>
            <p class="small opacity-75 mb-0">Publikasikan tugas ke mahasiswa PSIK 23 D</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold px-3">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="custom-card">
                    <form id="formTugas" onsubmit="simpanTugas(event)" enctype="multipart/form-data">
                        
                        <div class="section-title"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Dasar</div>

                        <div class="mb-4">
                            <label class="form-label">Upload Foto / Video Cover <span class="text-muted text-lowercase">(Opsional, untuk slide tampilan tugas)</span></label>
                            <input class="form-control" type="file" id="file_media" name="file_media[]" multiple accept="image/*,video/*">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Subject Tugas <span class="text-danger">*</span></label>
                            <div id="editor-subject"></div>
                            <input type="hidden" id="subject_html" name="subject_html">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mata_kuliah" name="mata_kuliah" placeholder="Contoh: Pemrograman Web Lanjut" required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Kode MK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_mk" name="kode_mk" required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Bobot SKS <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="bobot_sks" name="bobot_sks" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="" disabled selected>Pilih Semester</option>
                                    <?php for($i=1;$i<=8;$i++) echo "<option value='$i'>Semester $i</option>"; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Dosen Pengampu <span class="text-danger">*</span></label>
                                <select class="form-select" id="dosen_id" name="dosen_id" required>
                                    <option value="" disabled selected>Pilih Dosen</option>
                                    <?php while($d = mysqli_fetch_assoc($q_dosen)): ?>
                                        <option value="<?= $d['id'] ?>"><?= $d['nama'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="section-title mt-2"><i class="fas fa-tags text-warning me-2"></i>Format & Klasifikasi Tugas</div>

                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_tugas" name="jenis_tugas" required>
                                    <option value="Individu">Individu</option>
                                    <option value="Kelompok">Kelompok</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bentuk/Tipe Tugas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-warning" id="tipe_tugas" name="tipe_tugas" placeholder="Contoh: Soal Ngoding, Makalah, CBR..." required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tugas Ke- <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tugas_ke" name="tugas_ke" required>
                            </div>
                        </div>

                        <div class="row mb-4 p-3 bg-light rounded-3 border">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <label class="form-label"><i class="fas fa-file-upload me-2 text-primary"></i>File Lampiran Soal <span class="text-muted">(Opsional)</span></label>
                                <input class="form-control" type="file" id="lampiran_tipe" name="lampiran_tipe" accept="image/*,.pdf,.doc,.docx,.zip">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-link me-2 text-info"></i>Link Soal/Tugas <span class="text-muted">(Opsional)</span></label>
                                <input type="url" class="form-control" id="link_lampiran_tipe" name="link_lampiran_tipe" placeholder="https://...">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Sifat Tugas <span class="text-danger">*</span></label>
                                <select class="form-select" id="sifat_tugas" name="sifat_tugas" required>
                                    <option value="Wajib">Wajib</option>
                                    <option value="Opsional">Opsional</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Butuh Presentasi? <span class="text-danger">*</span></label>
                                <select class="form-select" id="butuh_presentasi" name="butuh_presentasi" required>
                                    <option value="Tidak">Tidak</option>
                                    <option value="Ya">Ya</option>
                                </select>
                            </div>
                        </div>

                        <div class="section-title mt-2"><i class="fas fa-clock text-danger me-2"></i>Waktu & Pengumpulan</div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-success">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="waktu_mulai" name="waktu_mulai" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-danger">Batas Waktu (Deadline) <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="waktu_selesai" name="waktu_selesai" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Toleransi Keterlambatan <span class="text-danger">*</span></label>
                                <select class="form-select" id="toleransi_waktu_opsi" onchange="toggleToleransi()" required>
                                    <option value="Tidak">Tidak (0 Menit)</option>
                                    <option value="Ya">Ya, berikan toleransi</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-none" id="wrap_menit_toleransi">
                                <label class="form-label">Berapa Menit? <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="toleransi_waktu" name="toleransi_waktu" value="0" min="0">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Auto Close Deadline? <span class="text-danger">*</span></label>
                                <select class="form-select" id="auto_close" name="auto_close" required>
                                    <option value="Ya">Ya (Link ditutup otomatis)</option>
                                    <option value="Tidak">Tidak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Butuh Upload File? <span class="text-danger">*</span></label>
                                <select class="form-select" id="butuh_upload" name="butuh_upload" onchange="toggleUploadLink()" required>
                                    <option value="Ya">Ya</option>
                                    <option value="Tidak">Tidak</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4" id="wrap_link_pengumpulan">
                            <div class="col-md-5">
                                <label class="form-label text-primary">Tempat Kumpul</label>
                                <input type="text" class="form-control" id="tempat_pengumpulan" name="tempat_pengumpulan" placeholder="Contoh: Google Drive">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label text-info">Link Pengumpulan</label>
                                <input type="url" class="form-control" id="link_pengumpulan" name="link_pengumpulan" placeholder="https://...">
                            </div>
                        </div>

                        <div class="section-title mt-4"><i class="fas fa-book-open text-info me-2"></i>Referensi Materi</div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label">Ada Referensi Materi?</label>
                                <select class="form-select border-info" id="ada_referensi" name="ada_referensi" onchange="toggleReferensi()">
                                    <option value="Tidak">Tidak Ada</option>
                                    <option value="Ya">Ya, Sertakan Referensi</option>
                                </select>
                            </div>
                            <div class="col-md-8 d-none" id="wrap_referensi">
                                <div class="p-3 bg-info bg-opacity-10 border border-info rounded-3">
                                    <div class="row">
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <label class="form-label"><i class="fas fa-file-pdf me-1"></i> File Materi</label>
                                            <input type="file" class="form-control form-control-sm" id="file_referensi" name="file_referensi" accept="image/*,.pdf,.doc,.docx,.ppt,.pptx,.zip">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="fas fa-link me-1"></i> Link Materi</label>
                                            <input type="url" class="form-control form-control-sm" id="link_referensi" name="link_referensi" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-4"><i class="fas fa-align-left text-success me-2"></i>Catatan Tambahan</div>

                        <div class="mb-5">
                            <label class="form-label">Deskripsi Tambahan</label>
                            <div id="editor-deskripsi"></div>
                            <input type="hidden" id="deskripsi_html" name="deskripsi_html">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-3 shadow-lg fs-5" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border: none;">
                            <i class="fas fa-paper-plane me-2"></i> Publikasikan Tugas
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function toggleToleransi() {
            let opsi = document.getElementById('toleransi_waktu_opsi').value;
            let wrapMenit = document.getElementById('wrap_menit_toleransi');
            let inputMenit = document.getElementById('toleransi_waktu');
            if(opsi === 'Ya') { wrapMenit.classList.remove('d-none'); inputMenit.required = true; } 
            else { wrapMenit.classList.add('d-none'); inputMenit.value = '0'; inputMenit.required = false; }
        }

        function toggleUploadLink() {
            let opsi = document.getElementById('butuh_upload').value;
            let wrapLink = document.getElementById('wrap_link_pengumpulan');
            if(opsi === 'Ya') { wrapLink.classList.remove('d-none'); } 
            else { 
                wrapLink.classList.add('d-none'); 
                document.getElementById('link_pengumpulan').value = ''; 
                document.getElementById('tempat_pengumpulan').value = ''; 
            }
        }

        // JS BARU UNTUK REFERENSI
        function toggleReferensi() {
            let opsi = document.getElementById('ada_referensi').value;
            let wrapRef = document.getElementById('wrap_referensi');
            if(opsi === 'Ya') { wrapRef.classList.remove('d-none'); } 
            else { 
                wrapRef.classList.add('d-none'); 
                document.getElementById('file_referensi').value = ''; 
                document.getElementById('link_referensi').value = ''; 
            }
        }

        // Setup Quill
        var quillSubject = new Quill('#editor-subject', { theme: 'snow', placeholder: 'Ketik subject tugas di sini...', modules: { toolbar: [ ['bold', 'italic', 'underline', 'strike'], [{ 'color': [] }, { 'background': [] }], ['clean'] ] } });
        var quillDesc = new Quill('#editor-deskripsi', { theme: 'snow', placeholder: 'Tuliskan instruksi tambahan...', modules: { toolbar: [ [{ 'header': [1, 2, 3, false] }], ['bold', 'italic', 'underline', 'strike'], [{ 'color': [] }, { 'background': [] }], [{ 'list': 'ordered'}, { 'list': 'bullet' }], [{ 'align': [] }], ['link'], ['clean'] ] } });

        function simpanTugas(event) {
            event.preventDefault();
            var subjectHtml = quillSubject.root.innerHTML;
            var descHtml = quillDesc.root.innerHTML;

            if(subjectHtml === '<p><br></p>') { Swal.fire('Oops!','Subject tugas wajib diisi bro!','warning'); return; }

            let btnSubmit = document.querySelector('button[type="submit"]');
            let originalBtnText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memproses...';
            btnSubmit.disabled = true;

            let formData = new FormData(document.getElementById('formTugas'));
            formData.set('subject_html', subjectHtml);
            formData.set('deskripsi_html', descHtml === '<p><br></p>' ? '' : descHtml);

            fetch('../backend/proses_simpan_tugas.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                btnSubmit.innerHTML = originalBtnText; btnSubmit.disabled = false;
                if(data.status === 'sukses') {
                    Swal.fire('Mantap!', data.pesan, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.pesan, 'error');
                }
            }).catch(error => {
                Swal.fire('Error Jaringan!', error.message, 'error');
                btnSubmit.innerHTML = originalBtnText; btnSubmit.disabled = false;
            });
        }
    </script>
</body>
</html>