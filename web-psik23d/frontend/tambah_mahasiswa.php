<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa - Admin</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .premium-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 500px; }
        .form-control { background-color: var(--bg-body); border-color: var(--border-color); color: var(--text-main); padding: 0.8rem 1rem; border-radius: 8px; }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    </style>
</head>
<body>

    <div class="premium-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-user-plus fa-lg"></i>
            </div>
            <h4 class="fw-bold">Tambah Mahasiswa</h4>
            <p class="text-sub">Masukkan data mahasiswa baru.</p>
        </div>

        <form action="../backend/proses_tambah.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="fw-bold small mb-1">NIM</label>
                <input type="number" name="nim" class="form-control" required placeholder="Contoh: 230101001" autofocus>
            </div>
            <div class="mb-4">
                <label class="fw-bold small mb-1">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required placeholder="Nama sesuai KTM">
            </div>
            
            <input type="hidden" name="jabatan" value="Anggota">
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3">Simpan Data</button>
                <a href="mahasiswa.php" class="btn btn-light text-secondary py-2 rounded-3">Batal</a>
            </div>
        </form>
    </div>

</body>
</html>