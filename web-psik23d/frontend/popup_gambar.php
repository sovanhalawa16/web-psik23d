<?php
// 1. Koneksi Database
include '../koneksi.php';

// 2. Ambil data pengumuman aktif
$query_gambar = mysqli_query($koneksi, "SELECT * FROM popup_jadwal WHERE status = 'aktif' ORDER BY id DESC LIMIT 1");
$data_gambar = mysqli_fetch_assoc($query_gambar);

if (!$data_gambar) return;

$status_gambar = $data_gambar['status_gambar'] ?? 'nonaktif';
$file_gambar = $data_gambar['file_gambar'] ?? '';

// JIKA BUKAN MODE GAMBAR ATAU FILE KOSONG, BERHENTI DISINI!
if ($status_gambar !== 'aktif' || empty($file_gambar)) {
    return;
}
?>

<style>
    /* Modal Super Bersih Ala iOS */
    #modalGambarUtama .modal-content {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        /* JURUS BIAR GAMBAR PAS DI TENGAH & ADA SPASI KIRI KANAN DI HP */
        width: 90% !important;
        max-width: 450px !important;
        margin: 0 auto !important;
    }
    .img-popup-custom {
        width: 100%;
        height: auto;
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        cursor: pointer;
        transition: transform 0.3s ease;
        display: block;
    }
    .img-popup-custom:hover {
        transform: scale(1.02);
    }
    .custom-backdrop-gambar {
        background-color: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
    }
</style>

<div class="modal fade" id="modalGambarUtama" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <img src="../assets/img/popup/<?= $file_gambar ?>" class="img-popup-custom" id="btnTutupGambar" alt="Pengumuman Kelas" title="Klik untuk menutup">
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalGambarEl = document.getElementById('modalGambarUtama');
    if(!modalGambarEl) return;

    // Inisialisasi Modal
    const modalGambar = new bootstrap.Modal(modalGambarEl, {
        backdrop: true, 
        keyboard: true  
    });

    // ==========================================================
    // JURUS PEMBUNUH BUG: TUTUP MANUAL BIAR TOMBOL BISA DIKLIK!
    // ==========================================================
    document.getElementById('btnTutupGambar').addEventListener('click', function() {
        modalGambar.hide();
    });

    // BERSIH-BERSIH: Pastikan setelah modal nutup, gak ada overlay gaib yang nyangkut
    modalGambarEl.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(bg => bg.remove()); // Sikat habis sisa backdrop transparan!
    });
    // ==========================================================

    // Fungsi Pemicu
    window.triggerPopupGambar = function() {
        modalGambar.show();
        
        // Ganti warna backdrop biar dramatis
        setTimeout(() => {
            const backdrop = document.querySelector('.modal-backdrop');
            if(backdrop) backdrop.classList.add('custom-backdrop-gambar');
        }, 50);
    };

    // JURUS ANTI-NYANGKUT SIDIK JARI
    let cekLockScreen = setInterval(() => {
        const lockScreen = document.getElementById('iosLockScreen');
        if (!lockScreen || lockScreen.classList.contains('unlocked')) {
            clearInterval(cekLockScreen); // Matikan radar
            setTimeout(() => {
                window.triggerPopupGambar();
            }, 800); // Delay 0.8 detik biar animasinya elegan
        }
    }, 1000);
});
</script>