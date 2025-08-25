<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('siswa');
ensure_session_security();

$user = current_user();

// Ambil pilihan terakhir untuk info
$stmt = db()->prepare('SELECT c.nama, c.nomor_urut FROM votes v JOIN calon c ON c.id = v.calon_id WHERE v.nisn = ?');
$stmt->execute([$user['nisn']]);
$pilihan = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Konfirmasi | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/background.php'; print_logo_background(); ?>
	<div class="container py-5">
		<div class="row justify-content-center">
			<div class="col-md-8 col-lg-7">
				<div class="card shadow border-0">
					<div class="card-body p-4 text-center">
						<h1 class="h4 mb-3">Terima kasih, suara Anda telah tercatat.</h1>
						<?php if ($pilihan): ?>
							<p class="mb-1">Pilihan Anda:</p>
							<p class="fw-bold">#<?php echo (int)$pilihan['nomor_urut']; ?> - <?php echo e($pilihan['nama']); ?></p>
						<?php endif; ?>
						<p class="text-muted">Anda tidak dapat memilih lagi.</p>
						<a class="btn btn-outline-theme" href="<?php echo base_url('../auth/logout.php'); ?>">Keluar</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

