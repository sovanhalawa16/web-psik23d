<?php 
session_start();
// Pastikan path koneksi sesuaikan dengan folder lu
include '../koneksi.php'; 

// AMBIL DATA TERAKHIR DARI DATABASE UNTUK DIEDIT
$q_popup = mysqli_query($koneksi, "SELECT * FROM popup_jadwal ORDER BY id DESC LIMIT 1");
$d_popup = mysqli_fetch_assoc($q_popup);

$id_popup = $d_popup['id'] ?? '';
$subject_lama = $d_popup['subject'] ?? '';
$deskripsi_lama = $d_popup['deskripsi'] ?? '';
$status_lama = $d_popup['status'] ?? 'nonaktif';
$penulis_lama = $d_popup['penulis'] ?? 'Admin Sovan';
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
        }
        .ql-editor {
            min-height: 200px;
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
            <img src="../assets/img/mahasiswa/foto_lu.jpg" class="rounded-circle border border-2 border-white shadow-sm" style="width: 22px; height: 22px; object-fit: cover;" alt="Sovan">
            <span class="small text-white fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Sovantri Putra Paskah Halawa</span>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
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

                    <form id="formPengumumanPopup" onsubmit="submitForm(event)">
                        <input type="hidden" id="id_popup" name="id_popup" value="<?= $id_popup ?>">

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">Judul / Subject Pengumuman <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg rounded-3 fs-6" id="subject" name="subject" value="<?= htmlspecialchars($subject_lama) ?>" placeholder="Contoh: Info Pindah Ruangan Kelas" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">Isi Pengumuman <span class="text-danger">*</span></label>
                            <div id="editor-container"></div>
                            <input type="hidden" id="deskripsi_html" name="deskripsi_html">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-secondary small">Status Tampil <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg rounded-3 fs-6" id="status" name="status" required>
                                    <option value="aktif" <?= $status_lama == 'aktif' ? 'selected' : '' ?>>Aktif (Langsung Muncul)</option>
                                    <option value="nonaktif" <?= $status_lama == 'nonaktif' ? 'selected' : '' ?>>Nonaktif (Sembunyikan)</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-secondary small">Dipublikasikan Oleh <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg rounded-3 fs-6" id="penulis" name="penulis" value="<?= htmlspecialchars($penulis_lama) ?>" required>
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
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Tulis isi pengumuman lu di sini bro...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],                 // Ukuran Teks (Heading)
                    ['bold', 'italic', 'underline', 'strike'],        // Format Teks
                    [{ 'color': [] }, { 'background': [] }],          // Warna Teks & Highlight
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],     // List / Bullet Point
                    [{ 'align': [] }],                                // Rata Kiri/Tengah/Kanan
                    ['link'],                                         // Masukin Link URL
                    ['clean']                                         // Tombol hapus format
                ]
            }
        });

        // Load data lama ke dalam Editor Quill
        var isiLama = <?= json_encode($deskripsi_lama) ?>;
        if(isiLama && isiLama.trim() !== '') {
            quill.root.innerHTML = isiLama;
        }

        function submitForm(event) {
            event.preventDefault(); 
            
            var htmlContent = quill.root.innerHTML;
            
            if(htmlContent === '<p><br></p>') {
                alert("Bro, isi pengumumannya jangan dikosongin dong!");
                return;
            }

            document.getElementById('deskripsi_html').value = htmlContent;

            let id_popup = document.getElementById('id_popup').value;
            let subject = document.getElementById('subject').value;
            let status = document.getElementById('status').value;
            let penulis = document.getElementById('penulis').value;

            let btnSubmit = document.querySelector('button[type="submit"]');
            let originalBtnText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';
            btnSubmit.disabled = true;

            let formData = new FormData();
            formData.append('id_popup', id_popup);
            formData.append('subject', subject);
            formData.append('deskripsi_html', htmlContent);
            formData.append('status', status);
            formData.append('penulis', penulis);

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
                    window.location.href = 'jadwal.php'; 
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