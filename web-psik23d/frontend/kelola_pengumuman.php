<?php 
session_start();
// Pastikan path koneksi sesuaikan dengan folder lu
include '../koneksi.php'; 

// =========================================================================
// JURUS RAHASIA: AUTO CREATE KOLOM BARU JIKA BELUM ADA DI DATABASE
// =========================================================================
$cek_kolom_video = mysqli_query($koneksi, "SHOW COLUMNS FROM popup_jadwal LIKE 'status_video'");
if(mysqli_num_rows($cek_kolom_video) == 0) {
    mysqli_query($koneksi, "ALTER TABLE popup_jadwal ADD status_video VARCHAR(20) DEFAULT 'nonaktif' AFTER status");
}

$cek_kolom_gambar = mysqli_query($koneksi, "SHOW COLUMNS FROM popup_jadwal LIKE 'status_gambar'");
if(mysqli_num_rows($cek_kolom_gambar) == 0) {
    mysqli_query($koneksi, "ALTER TABLE popup_jadwal ADD status_gambar VARCHAR(20) DEFAULT 'nonaktif' AFTER status_video, ADD file_gambar VARCHAR(255) NULL AFTER status_gambar");
}

// AMBIL DATA TERAKHIR DARI DATABASE UNTUK DIEDIT
$q_popup = mysqli_query($koneksi, "SELECT * FROM popup_jadwal ORDER BY id DESC LIMIT 1");
$d_popup = mysqli_fetch_assoc($q_popup);

$id_popup = $d_popup['id'] ?? '';
$subject_lama = $d_popup['subject'] ?? '';
$deskripsi_lama = $d_popup['deskripsi'] ?? '';
$status_lama = $d_popup['status'] ?? 'nonaktif';
$status_video_lama = $d_popup['status_video'] ?? 'nonaktif'; 
$status_gambar_lama = $d_popup['status_gambar'] ?? 'nonaktif'; // <-- Status Mode Gambar
$file_gambar_lama = $d_popup['file_gambar'] ?? '';             // <-- Nama File Gambar Lama
$penulis_lama = $d_popup['penulis'] ?? 'Admin Sovan';

// Bikin format waktu pertinggal
date_default_timezone_set('Asia/Jakarta');
$waktu_lama = isset($d_popup['created_at']) ? date('d M Y, H:i', strtotime($d_popup['created_at'])) . ' WIB' : 'Belum ada data';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengumuman Popup - PSIK 23 D</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        body { 
            background-color: #f4f7fb; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #334155; 
            overflow-x: hidden; 
            padding-top: 0; 
        }

        /* --- HEADER STYLES --- */
        .header-modern {
            background: linear-gradient(135deg, #4f46e5 0%, #0284c7 100%); 
            padding: 25px 0 20px; 
            color: white; 
            text-align: center; 
            border-bottom-left-radius: 25px; 
            border-bottom-right-radius: 25px;
            position: relative; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.15); 
            margin-bottom: 30px;
        }
        .bg-shape-1 { position: absolute; width: 150px; height: 150px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; top: -50px; left: -30px; filter: blur(2px); }
        .bg-shape-2 { position: absolute; width: 100px; height: 100px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; bottom: -20px; right: -20px; filter: blur(1px); }

        /* --- CARD STYLES --- */
        .custom-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 30px;
        }

        /* --- EDITOR STYLES --- */
        .ql-container {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 0.95rem;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            background: #fff;
        }
        .ql-toolbar {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .ql-editor {
            min-height: 250px;
        }
        
        /* CUSTOM TOGGLE SWITCH ALA IOS */
        .form-switch .form-check-input {
            width: 3.5em;
            height: 1.8em;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-switch .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }
        
        .area-transition {
            transition: all 0.4s ease-in-out;
        }
    </style>
</head>
<body>

    <div class="header-modern">
        <div class="bg-shape-1"></div>
        <div class="bg-shape-2"></div>
        <div class="container position-relative z-1 mt-2 d-flex justify-content-center align-items-center gap-3">
            <img src="../assets/img/FMIPA.png" alt="Logo FMIPA" style="width: 50px; height: auto;">
            <div class="text-start">
                <h4 class="fw-bolder mb-1" style="letter-spacing: -0.5px;">Kelola Pengumuman</h4>
                <p class="small opacity-75 mb-0 fw-medium">PSIK 23 D - Universitas Negeri Medan</p>
                <p class="small mb-0 fw-bold text-warning fst-italic" style="letter-spacing: 0.5px;">Dashboard Admin</p>
            </div>
        </div>
        
        <div class="container position-relative z-1 mt-3 pt-2 border-top border-white border-opacity-25 d-flex justify-content-center align-items-center gap-2">
            <span class="small text-white opacity-75" style="font-size: 0.7rem;">Dev by</span>
            <img src="../assets/img/mahasiswa/foto_lu.jpg" onerror="this.src='https://ui-avatars.com/api/?background=random&name=Sovan'" class="rounded-circle border border-2 border-white shadow-sm" style="width: 22px; height: 22px; object-fit: cover;" alt="Sovan">
            <span class="small text-white fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Sovantri Putra Paskah Halawa</span>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10"> 
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="jadwal.php" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold px-3">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Jadwal
                    </a>
                </div>

                <div class="custom-card">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 45px;">
                            <i class="fas fa-bullhorn fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bolder mb-0 text-dark">Edit Pengumuman Popup</h5>
                            <p class="text-muted small mb-0">Perbarui informasi pengumuman yang akan muncul di jadwal.</p>
                        </div>
                    </div>

                    <form id="formPengumumanPopup" onsubmit="submitForm(event)" enctype="multipart/form-data">
                        <input type="hidden" id="id_popup" name="id_popup" value="<?= $id_popup ?>">

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">Judul / Subject Pengumuman <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg rounded-3 fs-6" id="subject" name="subject" value="<?= htmlspecialchars($subject_lama) ?>" placeholder="Contoh: Info Pindah Ruangan Kelas" required>
                        </div>

                        <div class="row bg-light rounded-4 p-3 mb-4 border border-secondary border-opacity-10">
                            <div class="col-md-4 mb-3 mb-md-0 border-end">
                                <label class="form-label fw-bold text-secondary small">Status Popup <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm rounded-3 fw-bold" id="status" name="status" required>
                                    <option value="aktif" <?= $status_lama == 'aktif' ? 'selected' : '' ?>>Aktif (Langsung Muncul)</option>
                                    <option value="nonaktif" <?= $status_lama == 'nonaktif' ? 'selected' : '' ?>>Nonaktif (Sembunyikan)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3 mb-md-0 border-end">
                                <label class="form-label fw-bold text-secondary small mb-2 d-block">Tampilkan Video Kucing</label>
                                <div class="form-check form-switch ms-1 d-flex align-items-center">
                                    <input class="form-check-input mt-0 shadow-sm" type="checkbox" id="status_video" <?= $status_video_lama == 'aktif' ? 'checked' : '' ?>>
                                    <label class="form-check-label ms-2 fw-bolder text-dark small" for="status_video" id="label_video">
                                        <?= $status_video_lama == 'aktif' ? 'ON' : 'OFF' ?>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small mb-2 d-block">Mode "Hanya Gambar"</label>
                                <div class="form-check form-switch ms-1 d-flex align-items-center">
                                    <input class="form-check-input mt-0 shadow-sm" type="checkbox" id="status_gambar" <?= $status_gambar_lama == 'aktif' ? 'checked' : '' ?>>
                                    <label class="form-check-label ms-2 fw-bolder text-dark small" for="status_gambar" id="label_gambar">
                                        <?= $status_gambar_lama == 'aktif' ? 'ON (Cuma Gambar)' : 'OFF (Pakai Teks)' ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="area_teks" class="area-transition" style="<?= $status_gambar_lama == 'aktif' ? 'display:none;' : 'display:block;' ?>">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small">Isi Pengumuman (Teks) <span class="text-danger">*</span></label>
                                <div id="editor-container"></div>
                                <input type="hidden" id="deskripsi_html" name="deskripsi_html">
                            </div>
                        </div>

                        <div id="area_gambar" class="area-transition" style="<?= $status_gambar_lama == 'aktif' ? 'display:block;' : 'display:none;' ?>">
                            <div class="mb-4 p-4 border border-dashed rounded-4 bg-light text-center">
                                <i class="fas fa-image text-muted opacity-50 mb-3" style="font-size: 3rem;"></i>
                                <label class="form-label fw-bold text-dark d-block">Upload Gambar Poster/Pamflet</label>
                                <p class="small text-muted mb-3">Format JPG, PNG, atau WEBP. Jika upload baru, file lama otomatis terhapus.</p>
                                
                                <input type="file" class="form-control form-control-lg rounded-3 fs-6 mx-auto shadow-sm" id="file_gambar" accept="image/jpeg, image/png, image/webp" style="max-width: 400px;">
                                
                                <div class="mt-4 pt-3 border-top" id="preview_container" style="<?= empty($file_gambar_lama) ? 'display:none;' : '' ?>">
                                    <span class="d-block small fw-bold text-secondary mb-2" id="preview_text">Gambar Saat Ini:</span>
                                    <img id="preview_image" src="<?= !empty($file_gambar_lama) ? '../assets/img/popup/'.$file_gambar_lama : '' ?>" class="img-thumbnail rounded-4 shadow-sm" style="max-height: 250px; object-fit: contain;">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">Penulis <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg rounded-3 fs-6" id="penulis" name="penulis" value="<?= htmlspecialchars($penulis_lama) ?>" required>
                        </div>

                        <div class="alert alert-info border-0 bg-info bg-opacity-10 d-flex align-items-center rounded-3 mb-4">
                            <i class="fas fa-clock text-info fs-4 me-3"></i>
                            <div class="small">
                                <strong class="text-dark">Terakhir Disimpan (Pertinggal):</strong><br>
                                <span class="text-muted fw-bold"><?= $waktu_lama ?></span>
                            </div>
                        </div>

                        <div class="text-end border-top pt-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold px-5 shadow-sm">
                                <i class="fas fa-save me-2"></i> Perbarui Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 pt-3 border-top border-secondary border-opacity-10 pb-4">
            <span class="text-muted fw-bold" style="font-size: 0.75rem;">&copy; 2026 PSIK 23 D</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    
    <script>
        // --- LOGIKA LIVE PREVIEW UPLOAD GAMBAR ---
        document.getElementById('file_gambar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewContainer = document.getElementById('preview_container');
                    const previewImage = document.getElementById('preview_image');
                    const previewText = document.getElementById('preview_text');
                    
                    previewContainer.style.display = 'block';
                    previewImage.src = event.target.result;
                    previewText.innerText = "Gambar Baru Yang Akan Menimpa Gambar Lama:";
                    previewText.classList.replace('text-secondary', 'text-primary');
                }
                reader.readAsDataURL(file);
            }
        });

        // --- LOGIKA ANIMASI TOGGLE SWITCH ---
        const saklarVideo = document.getElementById('status_video');
        const labelVideo = document.getElementById('label_video');
        
        saklarVideo.addEventListener('change', function() {
            labelVideo.innerText = this.checked ? 'ON' : 'OFF';
            labelVideo.style.color = this.checked ? '#10b981' : '#dc2626';
        });
        labelVideo.style.color = saklarVideo.checked ? '#10b981' : '#dc2626';

        const saklarGambar = document.getElementById('status_gambar');
        const labelGambar = document.getElementById('label_gambar');
        const areaTeks = document.getElementById('area_teks');
        const areaGambar = document.getElementById('area_gambar');

        saklarGambar.addEventListener('change', function() {
            labelGambar.innerText = this.checked ? 'ON (Cuma Gambar)' : 'OFF (Pakai Teks)';
            labelGambar.style.color = this.checked ? '#10b981' : '#dc2626';
            
            if(this.checked) {
                areaTeks.style.display = 'none';
                areaGambar.style.display = 'block';
            } else {
                areaTeks.style.display = 'block';
                areaGambar.style.display = 'none';
            }
        });
        labelGambar.style.color = saklarGambar.checked ? '#10b981' : '#dc2626';

        // =================================================================
        // TOOLBAR ALA MICROSOFT WORD (QUILL)
        // =================================================================
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Tulis isi pengumuman lu di sini bro...',
            modules: {
                toolbar: [
                    [{ 'font': [] }, { 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],        
                    [{ 'color': [] }, { 'background': [] }],          
                    [{ 'script': 'sub'}, { 'script': 'super' }],      
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],     
                    [{ 'indent': '-1'}, { 'indent': '+1' }],          
                    [{ 'align': [] }],                                
                    ['blockquote', 'code-block'],
                    ['link'],                                         
                    ['clean']                                         
                ]
            }
        });

        var isiLama = <?= json_encode($deskripsi_lama) ?>;
        if(isiLama && isiLama.trim() !== '') {
            quill.root.innerHTML = isiLama;
        }

        // =================================================================
        // PROSES SUBMIT KE BACKEND
        // =================================================================
        function submitForm(event) {
            event.preventDefault(); 
            
            let id_popup = document.getElementById('id_popup').value;
            let subject = document.getElementById('subject').value;
            let status = document.getElementById('status').value;
            let penulis = document.getElementById('penulis').value;
            
            let status_video = document.getElementById('status_video').checked ? 'aktif' : 'nonaktif';
            let status_gambar = document.getElementById('status_gambar').checked ? 'aktif' : 'nonaktif';

            let formData = new FormData();
            formData.append('id_popup', id_popup);
            formData.append('subject', subject);
            formData.append('status', status);
            formData.append('status_video', status_video);
            formData.append('status_gambar', status_gambar);
            formData.append('penulis', penulis);

            if (status_gambar === 'nonaktif') {
                let htmlContent = quill.root.innerHTML;
                htmlContent = htmlContent.replace(/^(<p><br><\/p>\s*)+/, '');
                htmlContent = htmlContent.replace(/(<p><br><\/p>\s*)+$/, '');
                
                if(htmlContent === '' || htmlContent === '<p><br></p>') {
                    alert("Bro, isi pengumumannya jangan dikosongin dong kalau lu pakai mode teks!");
                    return;
                }
                formData.append('deskripsi_html', htmlContent);
            } else {
                formData.append('deskripsi_html', quill.root.innerHTML); 
                
                let fileInput = document.getElementById('file_gambar');
                if (fileInput.files.length > 0) {
                    formData.append('file_gambar', fileInput.files[0]);
                }
            }

            let btnSubmit = document.querySelector('button[type="submit"]');
            let originalBtnText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';
            btnSubmit.disabled = true;

            fetch('../backend/proses_simpan_popup.php', {
                method: 'POST',
                body: formData 
            })
            .then(response => response.json()) 
            .then(data => {
                btnSubmit.innerHTML = originalBtnText;
                btnSubmit.disabled = false;

                if(data.status === 'sukses') {
                    alert(data.pesan); 
                    window.location.reload(); 
                } else {
                    alert(data.pesan); 
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan saat ngirim data bro!');
                btnSubmit.innerHTML = originalBtnText;
                btnSubmit.disabled = false;
            });
        }
    </script>
</body>
</html>