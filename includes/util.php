<?php
require_once __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function e(string $str): string {
	return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ensure_csrf_token(): void {
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
}

function csrf_token(): string {
	ensure_csrf_token();
	return $_SESSION['csrf_token'];
}

function csrf_field(): string {
	return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$token = $_POST['_token'] ?? '';
		if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
			http_response_code(419);
			die('Token CSRF tidak valid.');
		}
	}
}

function redirect(string $path): void {
	header('Location: ' . base_url($path));
	exit;
}

function try_password_verify(string $passwordPlain, string $hash): bool {
	// Dukung bcrypt (password_hash) dan fallback MD5 untuk akun awal
	if (strlen($hash) > 0 && ($hash[0] === '$')) {
		return password_verify($passwordPlain, $hash);
	}
	// Fallback MD5
	return md5($passwordPlain) === $hash;
}

function upload_image(array $file, string $targetDir): ?string {
	if (!isset($file['error']) || is_array($file['error'])) {
		return null;
	}
	if ($file['error'] !== UPLOAD_ERR_OK) {
		return null;
	}
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mime = $finfo->file($file['tmp_name']);
	$allowed = [
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/webp' => 'webp',
	];
	if (!isset($allowed[$mime])) {
		return null;
	}
	if (!is_dir($targetDir)) {
		mkdir($targetDir, 0777, true);
	}
	$ext = $allowed[$mime];
	$name = bin2hex(random_bytes(8)) . '.' . $ext;
	$dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $name;
	if (!move_uploaded_file($file['tmp_name'], $dest)) {
		return null;
	}
	return $name;
}

?>

