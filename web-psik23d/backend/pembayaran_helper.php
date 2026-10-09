<?php
// ============================================
// HELPER FUNCTIONS UNTUK SISTEM PEMBAYARAN
// ============================================

/**
 * Buat slug dari teks apapun (spasi → -, karakter khusus dihapus)
 */
function slugify($text, $maxLength = 60) {
    $text = strtolower(trim($text));
    // Ganti karakter non-alfanumerik jadi -
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    // Buang dash berlebih
    $text = preg_replace('/-+/', '-', $text);
    // Buang dash di awal/akhir
    $text = trim($text, '-');
    // Potong kalau kepanjangan
    if (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength);
        $text = rtrim($text, '-');
    }
    return $text ?: 'item';
}

/**
 * Generate slug unik untuk pembayaran (dari nama_pembayaran)
 * Contoh: "Fotocopy Modul" → "fotocopy-modul"
 * Kalau duplikat → "fotocopy-modul-2", dst.
 */
function generatePaySlug($nama_pembayaran) {
    global $koneksi;
    $baseSlug = slugify($nama_pembayaran, 50);
    $slug = $baseSlug;
    $counter = 1;
    
    while (true) {
        $esc = mysqli_real_escape_string($koneksi, $slug);
        $cek = mysqli_query($koneksi, "SELECT id FROM pembayaran WHERE pay_slug='$esc'");
        if (mysqli_num_rows($cek) == 0) break;
        $counter++;
        $slug = $baseSlug . '-' . $counter;
    }
    
    return $slug;
}

/**
 * Generate slug unik untuk mahasiswa
 * Format: {nama-slug}-{6-char-random}
 * Contoh: "sovantri-putra-paskah-halawa-a1b2c3"
 */
function generateSlugMahasiswa($nama, $maxNamaLength = 40) {
    global $koneksi;
    $namaSlug = slugify($nama, $maxNamaLength);
    $unique = substr(bin2hex(random_bytes(4)), 0, 6);
    $slug = $namaSlug . '-' . $unique;
    
    // Pastikan unik, retry kalau bentrok
    $esc = mysqli_real_escape_string($koneksi, $slug);
    $cek = mysqli_query($koneksi, "SELECT id FROM pembayaran_mhs WHERE slug='$esc'");
    if (mysqli_num_rows($cek) > 0) {
        return generateSlugMahasiswa($nama, $maxNamaLength);
    }
    
    return $slug;
}

/**
 * Legacy: generate slug random 12-char (untuk kompatibilitas)
 */
function generateSlug($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $slug = '';
    for ($i = 0; $i < $length; $i++) {
        $slug .= $chars[random_int(0, strlen($chars) - 1)];
    }
    global $koneksi;
    $cek = mysqli_query($koneksi, "SELECT id FROM pembayaran_mhs WHERE slug='$slug'");
    if (mysqli_num_rows($cek) > 0) return generateSlug($length);
    return $slug;
}

/**
 * Generate kode transaksi: PAY-YYYYMMDD-XXXXXX
 */
function generateKodeTransaksi() {
    return 'PAY-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Format rupiah: Rp 15.000
 */
function formatRupiah($n) {
    return 'Rp ' . number_format((int)$n, 0, ',', '.');
}

/**
 * Format nominal tanpa prefix Rp (untuk template)
 */
function formatNominalTemplate($n) {
    return number_format((int)$n, 0, ',', '.');
}

/**
 * Replace placeholder di template
 */
function renderTemplate($template, $data) {
    $replacements = [
        '{nama}'            => $data['nama'] ?? '',
        '{nim}'             => $data['nim'] ?? '',
        '{nominal}'         => formatRupiah($data['nominal'] ?? 0),
        '{nominal_plain}'   => formatNominalTemplate($data['nominal'] ?? 0),
        '{nama_pembayaran}' => $data['nama_pembayaran'] ?? '',
        '{kode_transaksi}'  => $data['kode_transaksi'] ?? '',
        '{link_pembayaran}' => $data['link_pembayaran'] ?? '',
        '{deadline}'        => isset($data['deadline']) && $data['deadline'] 
                                ? date('d M Y', strtotime($data['deadline'])) 
                                : '-',
        '{kategori}'        => $data['kategori'] ?? '-',
    ];
    return str_replace(array_keys($replacements), array_values($replacements), $template);
}

/**
 * Base URL dari request
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

/**
 * Normalisasi nomor HP ke format WA internasional (62xxx)
 */
function normalizePhone($phone) {
    $phone = preg_replace('/\D/', '', $phone ?? '');
    if (!$phone) return '';
    if (substr($phone, 0, 1) === '0') return '62' . substr($phone, 1);
    if (substr($phone, 0, 2) === '62') return $phone;
    if (substr($phone, 0, 1) === '8') return '62' . $phone;
    return $phone;
}

/**
 * Build URL pembayaran publik
 */
function buildPaymentUrl($pay_slug, $mhs_slug) {
    return getBaseUrl() . '/payment/' . $pay_slug . '/' . $mhs_slug;
}
?>