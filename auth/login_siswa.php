<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_session_security();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$nisn = trim($_POST['nisn'] ?? '');
	$password = $_POST['password'] ?? '';
	if ($nisn === '' || $password === '') {
		$error = 'Harap isi NISN dan password.';
	} else {
		$stmt = db()->prepare('SELECT * FROM siswa WHERE nisn = ? LIMIT 1');
		$stmt->execute([$nisn]);
		$user = $stmt->fetch();
		if ($user && try_password_verify($password, $user['password_hash'])) {
			$_SESSION['user'] = [
				'role' => 'siswa',
				'nisn' => $user['nisn'],
				'nama' => $user['nama'],
				'kelas' => $user['kelas'],
			];
			if ((int)$user['has_voted'] === 1) {
				redirect('../siswa/konfirmasi.php');
			} else {
				redirect('../siswa/voting.php');
			}
		} else {
			$error = 'NISN atau password salah.';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Siswa | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/background.php'; print_photo_background(); ?>
	<div class="container py-5 position-relative" style="z-index:1; min-height:100vh; display:flex; align-items:center;">
		<div class="row justify-content-center w-100">
			<div class="col-md-6 col-lg-5">
				<div class="card card-login border-0">
					<div class="card-body p-4">
						<h1 class="h4 text-center mb-4 fw-bold text-dark">Login Siswa</h1>
						<?php if (!empty($_GET['timeout'])): ?>
							<div class="alert alert-warning">Sesi berakhir otomatis. Silakan login kembali.</div>
						<?php endif; ?>
						<?php if ($error): ?>
							<div class="alert alert-danger"><?php echo e($error); ?></div>
						<?php endif; ?>
						<form method="post" class="vstack gap-3">
							<?php echo csrf_field(); ?>
							<div>
								<label class="form-label">NISN</label>
								<input type="text" name="nisn" class="form-control" required>
							</div>
							<div>
								<label class="form-label">Password</label>
								<input type="password" name="password" class="form-control" required>
							</div>
							<button class="btn btn-theme w-100">Masuk</button>
						</form>
						<hr class="my-4">
						<a class="btn btn-outline-theme w-100" href="<?php echo base_url('../public/index.php'); ?>">Kembali</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

