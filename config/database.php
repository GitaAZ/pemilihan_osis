<?php
// Koneksi database menggunakan PDO

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'pemilihan_osis';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';

try {
	$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
	die('Gagal koneksi database: ' . htmlspecialchars($e->getMessage()));
}

function db(): PDO {
	global $pdo;
	return $pdo;
}

?>

