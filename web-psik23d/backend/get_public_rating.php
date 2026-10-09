<?php
header('Content-Type: application/json');
include '../koneksi.php';

// Ambil 15 rating terbaru untuk di-looping di frontend
$query = "SELECT * FROM rating ORDER BY created_at DESC LIMIT 15";
$result = mysqli_query($koneksi, $query);

$data = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'nama' => htmlspecialchars($row['nama']), // Nama Asli
            'bintang' => (int)$row['bintang'],
            // Potong komentar biar gak kepanjangan di popup kecil
            'komentar' => htmlspecialchars(substr($row['komentar'], 0, 70)) . (strlen($row['komentar']) > 70 ? '...' : ''),
            // Format Waktu: 12 Jan, 14:30
            'waktu' => date('d M, H:i', strtotime($row['created_at']))
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'empty']);
}
?>