<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');
ensure_session_security();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$nisn = trim($_POST['nisn'] ?? '');
	if ($nisn === '') {
		$err = 'NISN wajib diisi.';
	} else {
		$hash = md5($nisn);
		$stmt = db()->prepare('UPDATE siswa SET password_hash = ? WHERE nisn = ?');
		$stmt->execute([$hash, $nisn]);
		if ($stmt->rowCount() > 0) {
			$msg = 'Password direset ke NISN.';
		} else {
			$err = 'Siswa tidak ditemukan.';
		}
	}
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reset Password Siswa | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<h1 class="h4 mb-3">Reset Password Siswa</h1>
		<?php if ($msg): ?><div class="alert alert-success"><?php echo e($msg); ?></div><?php endif; ?>
		<?php if ($err): ?><div class="alert alert-danger"><?php echo e($err); ?></div><?php endif; ?>
		<div class="card p-3">
			<form method="post" class="vstack gap-2">
				<?php echo csrf_field(); ?>
				<label class="form-label">NISN</label>
				<input type="text" name="nisn" class="form-control" required>
				<button class="btn btn-theme">Reset</button>
			</form>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

