<?php
// Ambil foto dari database
$q_banner_ui = mysqli_query($koneksi, "SELECT foto FROM pengumuman ORDER BY id DESC");
$banners = [];
while($row = mysqli_fetch_assoc($q_banner_ui)){
    $banners[] = $row['foto'];
}
?>

<?php if(count($banners) > 0): ?>
<!-- WADAH CAROUSEL BANNER -->
<div class="mt-3 mb-4 animate-up">
    <div id="carouselBannerPengumuman" class="carousel slide shadow-sm" data-bs-ride="carousel" style="border-radius: 16px; overflow: hidden;">
        
        <!-- Indikator Titik-Titik di Bawah -->
        <div class="carousel-indicators mb-2">
            <?php foreach($banners as $index => $b): ?>
                <button type="button" data-bs-target="#carouselBannerPengumuman" data-bs-slide-to="<?= $index ?>" class="<?= $index == 0 ? 'active' : '' ?>" aria-current="true"></button>
            <?php endforeach; ?>
        </div>

        <!-- Foto Banner -->
        <div class="carousel-inner">
            <?php foreach($banners as $index => $b): ?>
                <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>" data-bs-interval="4000">
                    <img src="../assets/img/pengumuman/<?= $b ?>" class="d-block w-100" style="height: 180px; object-fit: cover;" alt="Pengumuman">
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<style>
    /* Bikin indikatornya bundar elegan ala iOS */
    .carousel-indicators [data-bs-target] {
        width: 8px; height: 8px; border-radius: 50%; background-color: #fff; opacity: 0.5; margin: 0 4px;
    }
    .carousel-indicators .active { opacity: 1; transform: scale(1.2); }
</style>
<?php endif; ?>