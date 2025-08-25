<?php
require_once __DIR__ . '/../config/app.php';

/**
 * Menampilkan layer background foto jika tersedia di public/img
 * Cari berurutan: background.webp/jpg/png, bg.webp/jpg/png
 */
function print_photo_background(): void {
	$candidates = [
		'background.webp','background.jpg','background.png',
		'bg.webp','bg.jpg','bg.png',
	];
	$found = null;
	foreach ($candidates as $name) {
		$path = __DIR__ . '/../public/img/' . $name;
		if (file_exists($path)) { $found = $name; break; }
	}
	if ($found === null) { return; }
	$imgUrl = base_url('../public/img/' . $found);
	echo '<div class="bg-photo" style="background-image:url(' . htmlspecialchars($imgUrl, ENT_QUOTES) . ');"></div>';
	echo '<div class="bg-overlay"></div>';
}

/**
 * Menampilkan background logo OSIS sebagai watermark profesional
 * Berlaku untuk halaman akun (admin, wali kelas, siswa)
 */
function print_logo_background(): void {
	$logoPath = __DIR__ . '/../public/img/logo_osis.png';
	if (!file_exists($logoPath)) { return; }
	$imgUrl = base_url('../public/img/logo_osis.png');
	// Layer watermark logo + overlay lembut + vignette
	echo '<div class="bg-logo-mark" style="background-image:url(' . htmlspecialchars($imgUrl, ENT_QUOTES) . ');"></div>';
	echo '<div class="bg-overlay"></div>';
	echo '<div class="bg-vignette"></div>';
}

?>

