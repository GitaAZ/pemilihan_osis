<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('siswa');
verify_csrf();

$user = current_user();
$votingOpen = get_setting('voting_open', '0') === '1';
if (!$votingOpen) {
	die('Voting belum dibuka.');
}

$calonId = (int)($_POST['calon_id'] ?? 0);
if ($calonId <= 0) {
	die('Pilihan tidak valid.');
}

// Cek sudah memilih?
$stmt = db()->prepare('SELECT has_voted FROM siswa WHERE nisn = ?');
$stmt->execute([$user['nisn']]);
$s = $stmt->fetch();
if (!$s) {
	die('Sesi tidak valid.');
}
if ((int)$s['has_voted'] === 1) {
	redirect('konfirmasi.php');
}

// Transaksi
try {
	db()->beginTransaction();
	// validasi calon
	$chk = db()->prepare('SELECT id FROM calon WHERE id = ?');
	$chk->execute([$calonId]);
	if (!$chk->fetch()) {
		throw new RuntimeException('Calon tidak ditemukan.');
	}
	$ins = db()->prepare('INSERT INTO votes (nisn, calon_id) VALUES (?, ?)');
	$ins->execute([$user['nisn'], $calonId]);
	$upd = db()->prepare('UPDATE siswa SET has_voted = 1, voted_at = NOW() WHERE nisn = ?');
	$upd->execute([$user['nisn']]);
	db()->commit();
} catch (Throwable $e) {
	db()->rollBack();
	// Jika duplicate vote (unique nisn), arahkan ke konfirmasi
	redirect('konfirmasi.php');
}

redirect('konfirmasi.php');

