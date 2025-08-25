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
	$action = $_POST['action'] ?? '';
	try {
		if ($action === 'reset_status') {
			db()->beginTransaction();
			db()->exec('DELETE FROM votes');
			$upd = db()->exec('UPDATE siswa SET has_voted = 0, voted_at = NULL');
			db()->commit();
			$msg = 'Status voting semua siswa telah direset.';
		} elseif ($action === 'reset_passwords') {
			$upd = db()->exec('UPDATE siswa SET password_hash = MD5(nisn)');
			$msg = 'Password semua siswa direset ke NISN (MD5).';
		} elseif ($action === 'delete_all') {
			db()->beginTransaction();
			// Hapus siswa akan menghapus votes karena FK ON DELETE CASCADE
			db()->exec('DELETE FROM siswa');
			db()->commit();
			$msg = 'Semua data siswa telah dihapus.';
		}
	} catch (Throwable $e) {
		$err = 'Terjadi kesalahan saat eksekusi.';
		if (db()->inTransaction()) { db()->rollBack(); }
	}
}

$totalSiswa = (int)db()->query('SELECT COUNT(*) AS c FROM siswa')->fetch()['c'];
$sudah = (int)db()->query('SELECT COUNT(*) AS c FROM siswa WHERE has_voted = 1')->fetch()['c'];
$totalVotes = (int)db()->query('SELECT COUNT(*) AS c FROM votes')->fetch()['c'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reset Data Siswa | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<h1 class="h4 mb-3">Reset Data Siswa</h1>
		<?php if ($msg): ?><div class="alert alert-success"><?php echo e($msg); ?></div><?php endif; ?>
		<?php if ($err): ?><div class="alert alert-danger"><?php echo e($err); ?></div><?php endif; ?>
		<div class="row g-3">
			<div class="col-md-6">
				<div class="card p-3 h-100">
					<h2 class="h6">Ringkasan</h2>
					<ul class="mb-0">
						<li>Total siswa: <strong><?php echo $totalSiswa; ?></strong></li>
						<li>Sudah memilih: <strong class="text-success"><?php echo $sudah; ?></strong></li>
						<li>Total suara (tabel votes): <strong><?php echo $totalVotes; ?></strong></li>
					</ul>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card p-3 h-100 vstack gap-2">
					<h2 class="h6">Aksi Reset</h2>
					<form method="post" onsubmit="return confirm('Yakin reset status voting semua siswa? Ini akan menghapus semua votes dan mengosongkan status has_voted.')">
						<?php echo csrf_field(); ?>
						<button class="btn btn-outline-secondary w-100" name="action" value="reset_status">Reset Status Voting</button>
					</form>
					<form method="post" onsubmit="return confirm('Yakin reset password semua siswa ke NISN (MD5)?')">
						<?php echo csrf_field(); ?>
						<button class="btn btn-outline-theme w-100" name="action" value="reset_passwords">Reset Password Semua Siswa</button>
					</form>
					<form method="post" onsubmit="return confirm('PERINGATAN: Tindakan ini akan menghapus SEMUA DATA SISWA. Lanjutkan?')">
						<?php echo csrf_field(); ?>
						<button class="btn btn-danger w-100" name="action" value="delete_all">Hapus Semua Siswa</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

