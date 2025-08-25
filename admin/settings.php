<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');
ensure_session_security();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$votingOpen = isset($_POST['voting_open']) ? '1' : '0';
	set_setting('voting_open', $votingOpen);
	$msg = 'Pengaturan disimpan.';
}

$isOpen = get_setting('voting_open', '0') === '1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Pengaturan | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<h1 class="h4 mb-3">Pengaturan</h1>
		<?php if ($msg): ?><div class="alert alert-success"><?php echo e($msg); ?></div><?php endif; ?>
		<form method="post" class="card p-3">
			<?php echo csrf_field(); ?>
			<div class="form-check form-switch">
				<input class="form-check-input" type="checkbox" role="switch" id="open" name="voting_open" <?php echo $isOpen ? 'checked' : ''; ?>>
				<label class="form-check-label" for="open">Buka Voting</label>
			</div>
			<button class="btn btn-theme mt-3">Simpan</button>
		</form>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

