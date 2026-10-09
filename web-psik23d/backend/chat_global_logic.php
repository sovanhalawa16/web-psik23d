<?php
// chat_global_logic.php
session_start();
include '../koneksi.php';

// Pastikan hanya melayani request AJAX
if (isset($_POST['action_chat'])) {
    // Bersihkan buffer untuk memastikan output hanya JSON
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $nama_saya = $_SESSION['nama'] ?? 'Anonim';

    // LOGIKA KIRIM PESAN
    if ($_POST['action_chat'] == 'send') {
        $pesan = mysqli_real_escape_string($koneksi, htmlspecialchars($_POST['pesan']));
        $waktu = date('Y-m-d H:i:s');
        if (!empty($pesan)) {
            mysqli_query($koneksi, "INSERT INTO global_chat (nama, pesan, waktu) VALUES ('$nama_saya', '$pesan', '$waktu')");
        }
        echo json_encode(['status' => 'success']);
        exit;
    } 

    // LOGIKA MUAT PESAN (LOAD)
    elseif ($_POST['action_chat'] == 'load') {
        $q_chat = mysqli_query($koneksi, "
            SELECT c.*, u.foto 
            FROM (SELECT * FROM global_chat ORDER BY id DESC LIMIT 50) c 
            LEFT JOIN users u ON c.nama = u.nama 
            ORDER BY c.id ASC
        ");
        
        $html_chat = '';
        while($c = mysqli_fetch_assoc($q_chat)) {
            $waktu_chat = date('H:i', strtotime($c['waktu']));
            $is_me = ($c['nama'] == $nama_saya);
            
            $fotoPath = (!empty($c['foto']) && $c['foto'] !== 'default.jpg' && $c['foto'] !== 'default.png') 
                        ? '../assets/img/mahasiswa/' . $c['foto'] 
                        : 'https://ui-avatars.com/api/?background=random&name=' . urlencode($c['nama']);
            
            if($is_me) {
                $html_chat .= '
                <div class="d-flex flex-row-reverse align-items-end mb-3 gap-2 px-2">
                    <img src="'.$fotoPath.'" class="rounded-circle shadow-sm" style="width: 28px; height: 28px; min-width: 28px; object-fit: cover;" onerror="this.src=\'https://ui-avatars.com/api/?background=random&name='.urlencode($c['nama']).'\'">
                    <div class="bg-primary text-white p-2 rounded-4 shadow-sm" style="border-bottom-right-radius: 4px !important; max-width: 75%;">
                        <div class="fw-bold" style="font-size: 0.85rem; line-height: 1.3;">'.$c['pesan'].'</div>
                        <div class="text-end text-white-50 mt-1" style="font-size: 0.6rem;">'.$waktu_chat.'</div>
                    </div>
                </div>';
            } else {
                $html_chat .= '
                <div class="d-flex align-items-end mb-3 gap-2 px-2">
                    <img src="'.$fotoPath.'" class="rounded-circle shadow-sm" style="width: 28px; height: 28px; min-width: 28px; object-fit: cover;" onerror="this.src=\'https://ui-avatars.com/api/?background=random&name='.urlencode($c['nama']).'\'">
                    <div class="bg-white border border-secondary border-opacity-10 p-2 rounded-4 shadow-sm" style="border-bottom-left-radius: 4px !important; max-width: 75%;">
                        <div class="fw-bolder text-primary mb-1" style="font-size: 0.7rem;">'.$c['nama'].'</div>
                        <div class="text-dark fw-bold" style="font-size: 0.85rem; line-height: 1.3;">'.$c['pesan'].'</div>
                        <div class="text-start text-muted mt-1" style="font-size: 0.6rem;">'.$waktu_chat.'</div>
                    </div>
                </div>';
            }
        }
        echo json_encode(['html' => $html_chat]);
        exit;
    }
}