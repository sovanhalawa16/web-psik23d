<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSIK-FESS | Wall of Confession</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&family=Permanent+Marker&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background-color: #f0f2f5; 
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Patrick Hand', cursive; /* Font tulisan tangan */
            min-height: 100vh;
        }

        /* Header */
        .fess-header {
            background: #fff;
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid #000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .fess-title { font-family: 'Permanent Marker', cursive; color: #d946ef; font-size: 2.5rem; transform: rotate(-2deg); display: inline-block; }
        
        /* Grid Layout */
        .wall-container {
            padding: 30px;
            columns: 4 250px; /* Masonry layout responsive */
            column-gap: 20px;
        }

        /* Sticky Note Card */
        .note-card {
            display: inline-block;
            width: 100%;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 5px 5px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
            word-wrap: break-word;
            font-size: 1.2rem;
            line-height: 1.4;
            color: #1e293b;
        }
        .note-card:hover { transform: scale(1.05) rotate(0deg) !important; z-index: 10; box-shadow: 10px 10px 20px rgba(0,0,0,0.2); }
        
        /* Pin Effect */
        .pin {
            width: 15px; height: 15px;
            border-radius: 50%;
            background: #ef4444;
            position: absolute;
            top: -5px; left: 50%;
            transform: translateX(-50%);
            box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        }

        /* Warna Notes */
        .bg-yellow { background: #fef3c7; transform: rotate(2deg); }
        .bg-pink { background: #fce7f3; transform: rotate(-1deg); }
        .bg-blue { background: #e0f2fe; transform: rotate(1deg); }
        .bg-green { background: #dcfce7; transform: rotate(-2deg); }

        .sender-name { font-weight: bold; font-size: 0.9rem; color: #64748b; margin-top: 15px; display: block; text-align: right; }
        
        /* Floating Action Button */
        .btn-fab {
            position: fixed; bottom: 30px; right: 30px;
            width: 65px; height: 65px;
            background: #d946ef; color: white;
            border-radius: 50%; border: 3px solid #000;
            font-size: 30px; display: flex; align-items: center; justify-content: center;
            box-shadow: 5px 5px 0 #000;
            transition: 0.2s; cursor: pointer;
            z-index: 999;
        }
        .btn-fab:active { transform: translate(3px, 3px); box-shadow: 2px 2px 0 #000; }

        /* Modal Custom */
        .modal-content { border: 3px solid #000; border-radius: 15px; font-family: 'Patrick Hand', cursive; }
        .color-option { width: 30px; height: 30px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; display: inline-block; margin: 0 5px; }
        .color-option.selected { border-color: #000; transform: scale(1.2); }
    </style>
</head>
<body>

    <div class="fess-header">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="features.php" class="btn btn-sm btn-outline-dark rounded-pill"><i class="fas fa-arrow-left"></i> Kembali</a>
            <h1 class="fess-title m-0">confess psik 23 d</h1>
            <div style="width: 80px;"></div> </div>
    </div>

    <div class="wall-container" id="wallBoard">
        <div class="text-center w-100 py-5 text-muted">
            <div class="spinner-border" role="status"></div>
            <p>Memuat curhatan...</p>
        </div>
    </div>

    <div class="btn-fab" data-bs-toggle="modal" data-bs-target="#writeModal">
        <i class="fas fa-pen"></i>
    </div>

    <div class="modal fade" id="writeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-yellow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-3">Tulis Pesan Rahasia 🤫</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="fessForm">
                        <div class="mb-3">
                            <input type="text" id="sender" class="form-control bg-transparent border-0 border-bottom border-dark rounded-0" placeholder="Dari: Anonim (Boleh dikosongkan)" style="box-shadow:none;">
                        </div>
                        <div class="mb-3">
                            <textarea id="message" class="form-control bg-transparent border-0" rows="4" placeholder="Tulis sesuatu... (Max 300 huruf)" maxlength="300" required style="box-shadow:none; font-size: 1.3rem;"></textarea>
                        </div>
                        <div class="mb-3 text-center">
                            <label class="d-block mb-2 small fw-bold">Pilih Warna Kertas:</label>
                            <div class="color-option bg-warning selected" onclick="selectColor('bg-yellow')" style="background-color: #fef3c7;"></div>
                            <div class="color-option" onclick="selectColor('bg-pink')" style="background-color: #fce7f3;"></div>
                            <div class="color-option" onclick="selectColor('bg-blue')" style="background-color: #e0f2fe;"></div>
                            <div class="color-option" onclick="selectColor('bg-green')" style="background-color: #dcfce7;"></div>
                        </div>
                        <input type="hidden" id="selectedColor" value="bg-yellow">
                        
                        <button type="submit" class="btn btn-dark w-100 fw-bold rounded-pill">TEMPEL DI DINDING 📌</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', loadMessages);

        let selectedColorClass = 'bg-yellow';

        function selectColor(colorClass) {
            selectedColorClass = colorClass;
            document.getElementById('selectedColor').value = colorClass;
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            event.target.classList.add('selected');
            document.querySelector('.modal-content').className = `modal-content ${colorClass}`;
        }

        async function loadMessages() {
            try {
                const response = await fetch('../backend/api_get_fess.php');
                // Menggunakan text() dulu baru parse manual untuk menghindari error JSON
                const rawText = await response.text();
                
                // Cari posisi kurung kurawal pertama dan terakhir (Hapus sampah sebelum/sesudah JSON)
                const firstCurly = rawText.indexOf('{');
                const lastCurly = rawText.lastIndexOf('}');
                
                let jsonString = rawText;
                if(firstCurly !== -1 && lastCurly !== -1) {
                    jsonString = rawText.substring(firstCurly, lastCurly + 1);
                }

                const result = JSON.parse(jsonString);
                
                const wall = document.getElementById('wallBoard');
                wall.innerHTML = ''; 

                if (result.status === 'success' && result.data.length > 0) {
                    result.data.forEach(item => {
                        const rotate = (Math.random() * 6 - 3).toFixed(1); 
                        const html = `
                            <div class="note-card ${item.color}" style="transform: rotate(${rotate}deg);">
                                <div class="pin"></div>
                                <div class="note-text">${item.message}</div>
                                <span class="sender-name">
                                    - ${item.sender}, <small class="fw-normal">${item.time_ago}</small>
                                </span>
                            </div>
                        `;
                        wall.innerHTML += html;
                    });
                } else {
                    wall.innerHTML = `<div class="text-center w-100 py-5 text-muted">Belum ada curhatan. Jadilah yang pertama!</div>`;
                }
            } catch (error) {
                console.error('Error load:', error);
            }
        }

        // Handle Submit dengan CLEAN JSON PARSER
        document.getElementById('fessForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Menempelkan...';

            const formData = new FormData();
            formData.append('sender', document.getElementById('sender').value);
            formData.append('message', document.getElementById('message').value);
            formData.append('color', document.getElementById('selectedColor').value);

            try {
                const response = await fetch('../backend/api_submit_fess.php', {
                    method: 'POST',
                    body: formData
                });
                
                // --- PERBAIKAN UTAMA DISINI ---
                // Kita ambil respon sebagai TEXT dulu, bukan JSON langsung
                const rawText = await response.text();
                console.log("Raw Server Response:", rawText); // Cek Console kalo error lagi

                // Bersihkan respon dari "sampah" (Spasi/Enter sebelum JSON)
                const jsonStart = rawText.indexOf('{');
                const jsonEnd = rawText.lastIndexOf('}') + 1;
                
                if (jsonStart === -1) {
                    throw new Error("Respon server bukan JSON valid: " + rawText);
                }

                const cleanJson = rawText.substring(jsonStart, jsonEnd);
                const result = JSON.parse(cleanJson);

                if (result.status === 'success') {
                    const modalEl = document.getElementById('writeModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    
                    this.reset();
                    selectColor('bg-yellow'); 
                    
                    loadMessages(); // Refresh wall
                } else {
                    alert("Gagal: " + result.message);
                }

            } catch (error) {
                console.error("Error submit:", error);
                // Kita reload halaman saja kalau error parsing, karena biasanya data sudah masuk
                location.reload(); 
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>