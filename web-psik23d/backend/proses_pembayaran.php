<?php
include '../koneksi.php';
include 'pembayaran_helper.php';
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ==================================================
// SIMPAN PEMBAYARAN (CREATE/UPDATE)
// ==================================================
if ($action == 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pembayaran']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi_template']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori'] ?? '');
    $nominal = (int)$_POST['nominal'];
    $deadline = !empty($_POST['deadline']) ? mysqli_real_escape_string($koneksi, $_POST['deadline']) : null;
    $status = mysqli_real_escape_string($koneksi, $_POST['status'] ?? 'aktif');
    $admin_id = (int)($_SESSION['id'] ?? 0);
    
    $channels = json_decode($_POST['channels'] ?? '[]', true);

    if ($id > 0) {
        // ============ UPDATE ============
        // Cek apakah nama_pembayaran berubah → regenerate pay_slug
        $q_old = mysqli_query($koneksi, "SELECT nama_pembayaran, pay_slug FROM pembayaran WHERE id='$id'");
        $old = mysqli_fetch_assoc($q_old);
        $pay_slug = $old['pay_slug'];
        
        if ($old['nama_pembayaran'] !== $_POST['nama_pembayaran'] || empty($pay_slug)) {
            // Regenerate pay_slug dengan cek unik (exclude current id)
            $baseSlug = slugify($nama, 50);
            $slug = $baseSlug;
            $counter = 1;
            while (true) {
                $esc = mysqli_real_escape_string($koneksi, $slug);
                $cek = mysqli_query($koneksi, "SELECT id FROM pembayaran WHERE pay_slug='$esc' AND id != '$id'");
                if (mysqli_num_rows($cek) == 0) break;
                $counter++;
                $slug = $baseSlug . '-' . $counter;
            }
            $pay_slug = $slug;
        }
        
        $q = "UPDATE pembayaran SET 
                nama_pembayaran='$nama', 
                pay_slug='$pay_slug',
                deskripsi_template='$deskripsi',
                kategori='$kategori', 
                nominal='$nominal',
                deadline=" . ($deadline ? "'$deadline'" : "NULL") . ",
                status='$status'
              WHERE id='$id'";
        mysqli_query($koneksi, $q);
        $pay_id = $id;
        
        mysqli_query($koneksi, "DELETE FROM pembayaran_channel WHERE pembayaran_id='$pay_id'");
    } else {
        // ============ INSERT ============
        $kode = generateKodeTransaksi();
        $pay_slug = generatePaySlug($nama);
        $q = "INSERT INTO pembayaran 
                (kode_transaksi, nama_pembayaran, pay_slug, deskripsi_template, kategori,
                 nominal, deadline, status, created_by)
              VALUES 
                ('$kode', '$nama', '$pay_slug', '$deskripsi', '$kategori',
                 '$nominal', " . ($deadline ? "'$deadline'" : "NULL") . ", '$status', '$admin_id')";
        mysqli_query($koneksi, $q);
        $pay_id = mysqli_insert_id($koneksi);
    }

    // ============ CHANNELS ============
    if (!empty($channels)) {
        foreach ($channels as $idx => $c) {
            $jenis = mysqli_real_escape_string($koneksi, $c['jenis'] ?? '');
            $nomor = mysqli_real_escape_string($koneksi, $c['nomor_tujuan'] ?? '');
            $penerima = mysqli_real_escape_string($koneksi, $c['nama_penerima'] ?? '');
            $qris = mysqli_real_escape_string($koneksi, $c['qris_image'] ?? '');
            $catatan = mysqli_real_escape_string($koneksi, $c['catatan'] ?? '');
            
            if (!empty($_FILES['qris_' . $idx]['name'])) {
                $ext = strtolower(pathinfo($_FILES['qris_' . $idx]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                    $nama_file = 'qris_' . time() . '_' . $idx . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    $target_dir = '../assets/img/qris/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                    if (move_uploaded_file($_FILES['qris_' . $idx]['tmp_name'], $target_dir . $nama_file)) {
                        $qris = $nama_file;
                    }
                }
            }
            
            if (empty($jenis)) continue;
            
            mysqli_query($koneksi, "INSERT INTO pembayaran_channel 
                (pembayaran_id, jenis, nomor_tujuan, nama_penerima, qris_image, catatan, urutan)
                VALUES 
                ('$pay_id', '$jenis', '$nomor', '$penerima', '$qris', '$catatan', '$idx')");
        }
    }

    // ============ AUTO-ASSIGN MAHASISWA ============
    if ($id == 0) {
        // Pembayaran baru → assign semua mahasiswa
        $q_mhs = mysqli_query($koneksi, "SELECT id, nama FROM users WHERE role='mahasiswa'");
        while ($m = mysqli_fetch_assoc($q_mhs)) {
            $uid = (int)$m['id'];
            $slug = generateSlugMahasiswa($m['nama']);
            $slug_esc = mysqli_real_escape_string($koneksi, $slug);
            mysqli_query($koneksi, "INSERT IGNORE INTO pembayaran_mhs 
                (pembayaran_id, user_id, slug, status) 
                VALUES ('$pay_id', '$uid', '$slug_esc', 'pending')");
        }
    } else {
        // Edit → cek mahasiswa baru yang belum punya entry
        $q_mhs = mysqli_query($koneksi, "SELECT u.id, u.nama FROM users u 
            LEFT JOIN pembayaran_mhs pm ON pm.user_id=u.id AND pm.pembayaran_id='$pay_id'
            WHERE u.role='mahasiswa' AND pm.id IS NULL");
        while ($m = mysqli_fetch_assoc($q_mhs)) {
            $uid = (int)$m['id'];
            $slug = generateSlugMahasiswa($m['nama']);
            $slug_esc = mysqli_real_escape_string($koneksi, $slug);
            mysqli_query($koneksi, "INSERT INTO pembayaran_mhs 
                (pembayaran_id, user_id, slug, status) 
                VALUES ('$pay_id', '$uid', '$slug_esc', 'pending')");
        }
    }

    header("location:../frontend/admin_pembayaran.php?msg=sukses");
    exit;
}

// ==================================================
// AMBIL DETAIL PEMBAYARAN (AJAX)
// ==================================================
if ($action == 'get') {
    $id = (int)$_GET['id'];
    $q = mysqli_query($koneksi, "SELECT * FROM pembayaran WHERE id='$id'");
    $pay = mysqli_fetch_assoc($q);
    
    $qc = mysqli_query($koneksi, "SELECT * FROM pembayaran_channel WHERE pembayaran_id='$id' ORDER BY urutan ASC");
    $channels = [];
    while ($c = mysqli_fetch_assoc($qc)) $channels[] = $c;
    $pay['channels'] = $channels;
    
    header('Content-Type: application/json');
    echo json_encode($pay);
    exit;
}

// ==================================================
// HAPUS PEMBAYARAN
// ==================================================
if ($action == 'delete') {
    $id = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM pembayaran WHERE id='$id'");
    header("location:../frontend/admin_pembayaran.php?msg=hapus");
    exit;
}

// ==================================================
// SET STATUS PER MAHASISWA (AJAX)
// ==================================================
if ($action == 'set_status') {
    $pembayaran_id = (int)$_POST['pembayaran_id'];
    $user_id = (int)$_POST['user_id'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');
    $admin_id = (int)($_SESSION['id'] ?? 0);
    $now = date('Y-m-d H:i:s');

    if (!in_array($status, ['pending','berhasil','gagal','dibebaskan'])) {
        echo json_encode(['status' => 'error', 'msg' => 'Status tidak valid']);
        exit;
    }

    $cek = mysqli_query($koneksi, "SELECT * FROM pembayaran_mhs 
        WHERE pembayaran_id='$pembayaran_id' AND user_id='$user_id'");
    
    if (mysqli_num_rows($cek) > 0) {
        $existing = mysqli_fetch_assoc($cek);
        $pm_id = $existing['id'];
        $from_status = $existing['status'];
        
        mysqli_query($koneksi, "UPDATE pembayaran_mhs SET 
            status='$status', 
            catatan_admin='$catatan',
            verified_by='$admin_id',
            verified_at='$now',
            updated_at='$now'
            WHERE id='$pm_id'");
    } else {
        // Insert mahasiswa baru (format slug baru)
        $q_nama = mysqli_query($koneksi, "SELECT nama FROM users WHERE id='$user_id'");
        $nama_mhs = mysqli_fetch_assoc($q_nama)['nama'] ?? 'user';
        $slug = mysqli_real_escape_string($koneksi, generateSlugMahasiswa($nama_mhs));
        
        mysqli_query($koneksi, "INSERT INTO pembayaran_mhs 
            (pembayaran_id, user_id, slug, status, catatan_admin, verified_by, verified_at)
            VALUES ('$pembayaran_id', '$user_id', '$slug', '$status', '$catatan', '$admin_id', '$now')");
        $pm_id = mysqli_insert_id($koneksi);
        $from_status = null;
    }
    
    // Log
    mysqli_query($koneksi, "INSERT INTO pembayaran_log 
        (pembayaran_mhs_id, from_status, to_status, changed_by, catatan)
        VALUES ('$pm_id', " . ($from_status ? "'$from_status'" : "NULL") . ", '$status', '$admin_id', '$catatan')");

    echo json_encode(['status' => 'success']);
    exit;
}

// ==================================================
// BULK STATUS
// ==================================================
if ($action == 'bulk_status') {
    $pembayaran_id = (int)$_POST['pembayaran_id'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $admin_id = (int)($_SESSION['id'] ?? 0);
    $now = date('Y-m-d H:i:s');
    
    mysqli_query($koneksi, "UPDATE pembayaran_mhs SET 
        status='$status', verified_by='$admin_id', verified_at='$now'
        WHERE pembayaran_id='$pembayaran_id'");
    
    echo json_encode(['status' => 'success']);
    exit;
}

// ==================================================
// UPLOAD BUKTI DARI HALAMAN PUBLIK
// ==================================================
if ($action == 'upload_bukti') {
    $slug = mysqli_real_escape_string($koneksi, $_POST['slug']);
    $cek = mysqli_query($koneksi, "SELECT id FROM pembayaran_mhs WHERE slug='$slug'");
    if (mysqli_num_rows($cek) == 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Transaksi tidak ditemukan']);
        exit;
    }
    $pm = mysqli_fetch_assoc($cek);
    $pm_id = $pm['id'];
    
    if (!empty($_FILES['bukti']['name'])) {
        $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','pdf'])) {
            $nama_file = 'bukti_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target_dir = '../assets/img/bukti_bayar/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_dir . $nama_file)) {
                mysqli_query($koneksi, "UPDATE pembayaran_mhs SET bukti_bayar='$nama_file' WHERE id='$pm_id'");
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
        echo json_encode(['status' => 'error', 'msg' => 'Format file tidak didukung']);
        exit;
    }
    echo json_encode(['status' => 'error', 'msg' => 'Tidak ada file']);
    exit;
}

header("location:../frontend/admin_pembayaran.php");
exit;
?>