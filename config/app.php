<?php
// Konfigurasi aplikasi

define('SITE_NAME', 'SMA SWASTA PEMBDA 1 GUNUNGSITOLI');

// Base URL (opsional). Jika menggunakan Laragon dengan folder pemilihan_osis di www
// sesuaikan bila perlu, atau kosongkan agar relatif.
$BASE_URL = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

function base_url(string $path = ''): string {
	global $BASE_URL;
	$prefix = ($BASE_URL === '/' || $BASE_URL === '\\') ? '' : $BASE_URL;
	return $prefix . '/' . ltrim($path, '/');
}

?>

