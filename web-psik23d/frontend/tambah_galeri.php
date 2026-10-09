<?php include '../backend/cek_session.php'; ?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto - Admin Premium</title>
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
        :root { --bg-body: #f1f5f9; --bg-card: #ffffff; --text-main: #1e293b; --text-sub: #64748b; --border-color: #e2e8f0; }
        [data-bs-theme="dark"] { --bg-body: #020617; --bg-card: #1e293b; --text-main: #f8fafc; --text-sub: #94a3b8; --border-color: #334155; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); height: 100vh; display: flex; align-items: center; justify-content: center; transition: background 0.3s; }
        .premium-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 500px; }
        .upload-area { border: 2px dashed var(--border-color); border-radius: 12px; padding: 2rem; text-align: center; cursor: pointer; transition: 0.3s; }
        .upload-area:hover { border-color: #6366f1; background: rgba(99, 102, 241, 0.05); }
    </style>
</head>
<body>

    <div class="premium-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Upload Foto Galeri</h5>
            <a href="galeri.php" class="btn btn-sm btn-outline-secondary rounded-circle"><i class="fas fa-times"></i></a>
        </div>

        <form action="../backend/proses_tambah_galeri.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="upload-area w-100" for="fileInput">
                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                    <h6 class="fw-bold">Klik untuk Pilih Foto</h6>
                    <p class="text-sub small mb-0">Format: JPG, PNG (Max 2MB)</p>
                    <input type="file" id="fileInput" name="foto" class="d-none" accept="image/*" required onchange="previewFile()">
                </label>
                <div id="previewName" class="text-center mt-2 small fw-bold text-success"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">Upload Sekarang</button>
        </form>
    </div>

    <script>
        function previewFile() {
            const file = document.getElementById('fileInput').files[0];
            if(file) document.getElementById('previewName').innerText = "Terpilih: " + file.name;
        }
    </script>
</body>
</html>