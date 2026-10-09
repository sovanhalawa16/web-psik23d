<?php include '../backend/cek_session.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Upload Kegiatan</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card col-md-6 mx-auto shadow">
            <div class="card-header bg-primary text-white">Upload Foto Kegiatan</div>
            <div class="card-body">
                <form action="../backend/proses_tambah_kegiatan.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Pilih Foto</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Upload</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>