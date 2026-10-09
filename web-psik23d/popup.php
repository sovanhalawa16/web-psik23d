<?php
// Pastikan variabel $koneksi sudah tersedia
if (isset($koneksi)) {
    $q_pop = mysqli_query($koneksi, "SELECT * FROM popup_info WHERE status='aktif' ORDER BY id DESC LIMIT 1");
    
    if ($q_pop && mysqli_num_rows($q_pop) > 0) {
        $popup_data = mysqli_fetch_assoc($q_pop);
        
        // Setup Warna & Icon
        $bgClass = 'bg-primary'; 
        $iconClass = 'fa-info';
        $btnClass = 'btn-primary';
        
        if($popup_data['jenis'] == 'peringatan') { 
            $bgClass = 'bg-warning'; 
            $iconClass = 'fa-exclamation'; 
            $btnClass = 'btn-warning text-white';
        }
        elseif($popup_data['jenis'] == 'event') { 
            $bgClass = 'bg-success'; 
            $iconClass = 'fa-calendar-alt'; 
            $btnClass = 'btn-success';
        }
        elseif($popup_data['jenis'] == 'penting') { 
            $bgClass = 'bg-danger'; 
            $iconClass = 'fa-bell'; 
            $btnClass = 'btn-danger';
        }
?>

    <div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; max-width: 350px; margin: 0 auto;">
                
                <!-- Header Compact -->
                <div class="position-relative">
                    <div class="<?= $bgClass ?> p-3 text-white position-relative" style="border-radius: 12px 12px 0 0;">
                        <!-- Checkbox "Heningkan 8 jam" di pojok kanan atas (menggantikan tombol X) -->
                        <div class="position-absolute" style="top: 10px; right: 10px;">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="dontShowCheckbox" 
                                       style="width: 40px; height: 20px; cursor: pointer;">
                            </div>
                        </div>
                        
                        <!-- Icon dan judul jenis -->
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 32px; height: 32px;">
                                <i class="fas <?= $iconClass ?> fs-6"></i>
                            </div>
                            <span class="fw-bold fs-6"><?= strtoupper($popup_data['jenis']) ?></span>
                        </div>
                        
                        <!-- Label kecil untuk checkbox -->
                        <div class="position-absolute" style="top: 32px; right: 8px;">
                            <label for="dontShowCheckbox" class="text-white" style="font-size: 0.65rem; cursor: pointer;">
                                Heningkan 8 jam
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Body konten compact -->
                <div class="modal-body p-3">
                    
                    <!-- Badge Penulis kecil -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($popup_data['penulis']) ?>
                        </span>
                    </div>

                    <!-- Judul -->
                    <h6 class="fw-bold text-dark mb-2 fs-6">
                        <?= htmlspecialchars($popup_data['judul']) ?>
                    </h6>

                    <!-- Isi Konten dengan scroll kecil -->
                    <div class="text-muted mb-3" style="max-height: 120px; overflow-y: auto; line-height: 1.5; font-size: 0.8rem;">
                        <?= nl2br(htmlspecialchars($popup_data['isi'])) ?>
                    </div>

                    <!-- Tombol Link jika ada -->
                    <?php if (!empty($popup_data['link_url'])): ?>
                        <a href="<?= $popup_data['link_url'] ?>" target="_blank" 
                           class="btn <?= $btnClass ?> btn-sm w-100 rounded-pill fw-bold mb-2 d-flex align-items-center justify-content-center gap-1 py-2"
                           style="font-size: 0.85rem;">
                            <span><?= !empty($popup_data['link_label']) ? htmlspecialchars($popup_data['link_label']) : 'Buka Link' ?></span>
                            <i class="fas fa-arrow-right fs-6"></i>
                        </a>
                    <?php endif; ?>

                    <!-- Tombol OK untuk menutup -->
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 rounded-pill py-2 fw-medium mt-1" 
                            data-bs-dismiss="modal" style="font-size: 0.85rem;">
                        <i class="fas fa-check me-1"></i>Mengerti
                    </button>
                </div>

            </div>
        </div>
    </div>

    <style>
        /* Ukuran Modal Compact */
        .modal-dialog {
            max-width: 350px;
            padding: 0 10px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 576px) {
            .modal-dialog {
                max-width: 320px;
                margin: 0 auto;
            }
            
            .modal-content {
                border-radius: 10px !important;
                max-width: 320px !important;
            }
            
            .modal-body {
                padding: 1rem !important;
            }
            
            .modal-body div[style*="max-height"] {
                max-height: 100px !important;
            }
            
            /* Perkecil switch di mobile */
            .form-check-input {
                width: 36px !important;
                height: 18px !important;
            }
        }
        
        /* Desktop Responsive */
        @media (min-width: 577px) {
            .modal-dialog {
                max-width: 350px;
            }
        }

        /* Animasi Muncul */
        .modal.fade .modal-dialog { 
            transform: scale(0.9); 
            opacity: 0; 
            transition: transform 0.2s ease-out, opacity 0.2s; 
        }
        .modal.show .modal-dialog { 
            transform: scale(1); 
            opacity: 1; 
        }

        /* Scrollbar Custom Kecil */
        .modal-body div::-webkit-scrollbar {
            width: 3px;
        }
        .modal-body div::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        
        /* Style untuk switch checkbox */
        .form-switch .form-check-input {
            background-color: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
        }
        
        .form-switch .form-check-input:checked {
            background-color: #ffffff;
            border-color: #ffffff;
        }
        
        .form-switch .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        /* Dark Mode Compact */
        body.dark-mode #infoModal .modal-content { 
            background-color: #1e293b; 
            border: 1px solid #334155 !important; 
        }
        body.dark-mode #infoModal .text-dark { 
            color: #f1f5f9 !important; 
        }
        body.dark-mode #infoModal .text-muted { 
            color: #94a3b8 !important; 
        }
        body.dark-mode #infoModal .bg-light { 
            background-color: rgba(30, 41, 59, 0.5) !important; 
            border-color: #475569 !important; 
        }
        body.dark-mode #infoModal .btn-outline-secondary {
            color: #cbd5e1;
            border-color: #475569;
        }
        body.dark-mode #infoModal .btn-outline-secondary:hover {
            background-color: #334155;
        }
        
        /* Style untuk label checkbox di dark mode */
        body.dark-mode #infoModal label[for="dontShowCheckbox"] {
            color: #e2e8f0 !important;
        }
        
        /* Style untuk switch di dark mode */
        body.dark-mode .form-switch .form-check-input {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        body.dark-mode .form-switch .form-check-input:checked {
            background-color: #60a5fa;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var popupEl = document.getElementById('infoModal');
            
            if (popupEl) {
                // Cek LocalStorage - 8 jam
                const hideUntil = localStorage.getItem('popup_hide_until');
                const now = new Date().getTime();

                if (hideUntil && now < hideUntil) { 
                    return; 
                }

                // Tampilkan Popup
                var myPopup = new bootstrap.Modal(popupEl);
                
                // Delay sedikit agar tidak langsung muncul
                setTimeout(() => {
                    myPopup.show();
                }, 800);

                // Logic switch checkbox - langsung tutup saat diaktifkan
                const checkbox = document.getElementById('dontShowCheckbox');
                if (checkbox) {
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            // Simpan untuk 8 jam
                            const eightHours = 8 * 60 * 60 * 1000;
                            const expiryTime = new Date().getTime() + eightHours;
                            localStorage.setItem('popup_hide_until', expiryTime);
                            
                            // Tutup popup otomatis
                            setTimeout(() => {
                                myPopup.hide();
                            }, 300);
                        } else {
                            // Hapus dari localStorage jika dimatikan
                            localStorage.removeItem('popup_hide_until');
                        }
                    });
                }

                // Logic saat popup ditutup dengan tombol "Mengerti"
                popupEl.addEventListener('hide.bs.modal', function () {
                    // Jika checkbox aktif saat popup ditutup, simpan preferensi
                    if (checkbox && checkbox.checked) {
                        const eightHours = 8 * 60 * 60 * 1000;
                        const expiryTime = new Date().getTime() + eightHours;
                        localStorage.setItem('popup_hide_until', expiryTime);
                    }
                });
                
                // Tambahkan juga event untuk klik di luar modal
                popupEl.addEventListener('click', function(e) {
                    if (e.target === popupEl) {
                        if (checkbox && checkbox.checked) {
                            const eightHours = 8 * 60 * 60 * 1000;
                            const expiryTime = new Date().getTime() + eightHours;
                            localStorage.setItem('popup_hide_until', expiryTime);
                        }
                    }
                });
            }
        });
    </script>

<?php
    }
}
?>