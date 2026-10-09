<?php
header('Content-Type: application/json');
include '../koneksi.php';

// Ambil semua nama mahasiswa (urut abjad biar rapi awalnya)
$query = mysqli_query($koneksi, "SELECT nama FROM users WHERE role='mahasiswa' ORDER BY nama ASC");

$names = [];
if(mysqli_num_rows($query) > 0) {
    while($row = mysqli_fetch_assoc($query)) {
        $names[] = $row['nama'];
    }
    echo json_encode(['status' => 'success', 'data' => $names]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
}
?>