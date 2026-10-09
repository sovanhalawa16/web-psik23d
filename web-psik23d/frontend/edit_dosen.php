<?php 
include '../backend/cek_session.php'; 
include '../koneksi.php';

if($_SESSION['role'] != 'admin'){ header("location:../index.php"); exit; }

$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM dosen WHERE id='$id'");
$d = mysqli_fetch_assoc($query);

if(!$d) { echo "Data tidak ditemukan!"; exit; }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Data Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0 rounded-4">
                    <div class="card-header bg-warning text-dark fw-bold">
                        ✏️ Edit Data Dosen
                    </div>
                    <div class="card-body p-4">
                        <form action="../backend/proses_edit_dosen.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                            
                            <div class="text-center mb-4">
                                <img src="../assets/img/dosen/<?= $d['foto'] ?>" class="rounded-circle border" width="100" height="100" style="object-fit: cover;">
                                <p class="text-muted small mt-2">Foto Saat Ini</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" value="<?= $d['nama'] ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">NIDN</label>
                                    <input type="text" name="nidn" class="form-control" value="<?= $d['nidn'] ?>" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control" value="<?= $d['jabatan'] ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ganti Foto (Opsional)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti foto.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Link Sinta</label>
                                <input type="url" name="link_sinta" class="form-control" value="<?= $d['link_sinta'] ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Link Google Scholar</label>
                                <input type="url" name="link_scholar" class="form-control" value="<?= $d['link_scholar'] ?>">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success fw-bold">Simpan Perubahan</button>
                                <a href="dosen.php" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>