<?php
// Ambil data foto mahasiswa (kecuali admin)
$q_mhs_marquee = mysqli_query($koneksi, "SELECT nama, foto FROM users WHERE role='mahasiswa' ORDER BY RAND()");
$marquee_fotos = [];

while($row = mysqli_fetch_assoc($q_mhs_marquee)) {
    $fotoPath = '';
    // Cek apakah punya foto custom
    if(!empty($row['foto']) && $row['foto'] !== 'default.jpg' && $row['foto'] !== 'default.png') {
        $fotoPath = '../assets/img/mahasiswa/' . $row['foto'];
    } else {
        // Fallback inisial nama kalau gak ada foto
        $fotoPath = 'https://ui-avatars.com/api/?background=random&name=' . urlencode($row['nama']);
    }
    $marquee_fotos[] = $fotoPath;
}

// Duplikat array fotonya 2x lipat biar animasi berputarnya gak pernah putus (Seamless Infinite Loop)
$marquee_fotos = array_merge($marquee_fotos, $marquee_fotos);
?>

<style>
    .avatar-marquee-wrapper {
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
        position: relative;
        padding: 5px 0;
        margin-top: 10px;
        margin-bottom: 10px;
        /* Efek blur transparan di ujung kiri & kanan */
        -webkit-mask-image: linear-gradient(90deg, transparent, #000 15%, #000 85%, transparent);
        mask-image: linear-gradient(90deg, transparent, #000 15%, #000 85%, transparent);
    }
    
    .avatar-marquee-track {
        display: inline-flex;
        width: max-content;
        /* Kecepatan scroll, bisa lu kecilin/gedein angka 30s ini */
        animation: marqueeScroll 30s linear infinite; 
    }

    .avatar-marquee-track img {
        width: 40px; 
        height: 40px; 
        border-radius: 50%;
        object-fit: cover;
        margin-right: -10px; /* Bikin fotonya agak tumpang tindih dikit biar elegan */
        border: 2px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }

    @keyframes marqueeScroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); } 
    }
</style>

<div class="avatar-marquee-wrapper">
    <div class="avatar-marquee-track">
        <?php foreach($marquee_fotos as $foto): ?>
            <img src="<?= $foto ?>" onerror="this.src='https://ui-avatars.com/api/?background=random&name=P'" alt="Mhs">
        <?php endforeach; ?>
    </div>
</div>