<?php
header('Content-Type: application/json');
include '../koneksi.php';

// Ambil pesan terbaru paling atas
$query = mysqli_query($koneksi, "SELECT * FROM confessions ORDER BY id DESC LIMIT 50");

$data = [];
while($row = mysqli_fetch_assoc($query)) {
    // Format waktu biar enak dibaca (Contoh: "2 jam yang lalu")
    $row['time_ago'] = date('d M Y, H:i', strtotime($row['created_at'])); 
    $data[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $data]);
?>