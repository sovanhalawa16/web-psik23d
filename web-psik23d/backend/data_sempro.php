<?php
// backend/data_sempro.php
// Ambil semua data sempro, urut dari yang terdekat

$q_sempro = mysqli_query($koneksi, "SELECT * FROM sempro ORDER BY hari_tanggal ASC, pukul_mhs ASC");
$semua_sempro = [];
if($q_sempro) {
    while($row = mysqli_fetch_assoc($q_sempro)) {
        $semua_sempro[] = $row;
    }
}
?>