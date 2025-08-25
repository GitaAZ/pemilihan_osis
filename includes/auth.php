<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Timeout sesi (detik)
$SESSION_TIMEOUT_SECONDS = 1800; // 30 menit

function ensure_session_security(): void {
	global $SESSION_TIMEOUT_SECONDS;
	if (!isset($_SESSION['initiated'])) {
		$_SESSION['initiated'] = true;
		session_regenerate_id(true);
	}
	// Auto logout jika idle melebihi batas
	$now = time();
	if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity'] > $SESSION_TIMEOUT_SECONDS)) {
		$_SESSION = [];
		if (ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
		}
		session_destroy();
		header('Location: ' . base_url('auth/login_siswa.php?timeout=1'));
		exit;
	}
	$_SESSION['last_activity'] = $now;
}

function is_logged_in(): bool {
	return isset($_SESSION['user']);
}

function current_user() {
	return $_SESSION['user'] ?? null;
}

function require_role(string $role): void {
	ensure_session_security();
	if (!is_logged_in() || ($_SESSION['user']['role'] ?? '') !== $role) {
		header('Location: ' . base_url('auth/login_' . $role . '.php'));
		exit;
	}
}

function require_any_role(array $roles): void {
	ensure_session_security();
	if (!is_logged_in() || !in_array(($_SESSION['user']['role'] ?? ''), $roles, true)) {
		header('Location: ' . base_url('public/index.php'));
		exit;
	}
}

function get_setting(string $name, $default = null) {
	try {
		$stmt = db()->prepare('SELECT value FROM settings WHERE name = ? LIMIT 1');
		$stmt->execute([$name]);
		$row = $stmt->fetch();
		return $row ? $row['value'] : $default;
	} catch (Throwable $e) {
		return $default;
	}
}

function set_setting(string $name, string $value): bool {
	$stmt = db()->prepare('INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
	return $stmt->execute([$name, $value]);
}

?>

