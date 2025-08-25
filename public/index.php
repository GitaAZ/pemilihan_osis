<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css'); ?>">
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
	<?php include __DIR__ . '/../includes/background.php'; print_photo_background(); ?>
	<div class="container position-relative" style="z-index:1;">
		<div class="row justify-content-center">
			<div class="col-md-8 col-lg-6">
				<?php if (file_exists(__DIR__.'/img/logo_sekolah.png') || file_exists(__DIR__.'/img/logo_osis.png')): ?>
				<div class="text-center mb-3 d-flex align-items-center justify-content-center gap-3">
					<?php if (file_exists(__DIR__.'/img/logo_sekolah.png')): ?><img src="<?php echo base_url('img/logo_sekolah.png'); ?>" height="64" alt="Logo Sekolah" style="border-radius:8px;"><?php endif; ?>
					<?php if (file_exists(__DIR__.'/img/logo_osis.png')): ?><img src="<?php echo base_url('img/logo_osis.png'); ?>" height="64" alt="Logo OSIS" style="border-radius:8px;"><?php endif; ?>
				</div>
				<?php endif; ?>
				<div class="card shadow border-0 card-login">
					<div class="card-body p-4">
						<h1 class="h4 text-center mb-4 fw-bold text-dark">PEMILIHAN PENGURUS HARIAN OSIS<br>SMA SWASTA PEMBDA 1 GUNUNGSITOLI<br>PERIODE 2025/2026</h1>
						<div class="row g-3">
							<div class="col-12">
								<a class="btn w-100 btn-theme" href="<?php echo base_url('../auth/login_siswa.php'); ?>">Login Siswa</a>
							</div>
							<div class="col-12 col-md-6">
								<a class="btn w-100 btn-outline-theme" href="<?php echo base_url('../auth/login_walikelas.php'); ?>">Login Wali Kelas</a>
							</div>
							<div class="col-12 col-md-6">
								<a class="btn w-100 btn-outline-theme" href="<?php echo base_url('../auth/login_admin.php'); ?>">Login Admin</a>
							</div>
						</div>
						<hr class="my-4">
						<p class="text-center small text-muted mb-0">Gunakan NISN untuk siswa. Password awal sama dengan NISN.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

