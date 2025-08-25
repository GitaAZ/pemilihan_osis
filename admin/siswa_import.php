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
	if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
		$err = 'Gagal mengunggah file CSV.';
	} else {
		$tmp = $_FILES['csv']['tmp_name'];
		$handle = fopen($tmp, 'r');
		if (!$handle) {
			$err = 'Tidak dapat membaca file.';
		} else {
			$header = fgetcsv($handle);
			// Ekspektasi header: nisn,nama,kelas
			$count = 0;
			while (($row = fgetcsv($handle)) !== false) {
				$nisn = trim($row[0] ?? '');
				$nama = trim($row[1] ?? '');
				$kelas = trim($row[2] ?? '');
				if ($nisn === '' || $nama === '' || $kelas === '') { continue; }
				$defaultHash = md5($nisn);
				$stmt = db()->prepare('INSERT INTO siswa (nisn, nama, kelas, password_hash) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nama=VALUES(nama), kelas=VALUES(kelas)');
				$stmt->execute([$nisn, $nama, $kelas, $defaultHash]);
				$count++;
			}
			fclose($handle);
			$msg = "Berhasil impor $count siswa.";
		}
	}
}

$total = (int)db()->query('SELECT COUNT(*) AS c FROM siswa')->fetch()['c'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kelola Siswa | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<h1 class="h4 mb-3">Kelola Siswa</h1>
		<?php if ($msg): ?><div class="alert alert-success"><?php echo e($msg); ?></div><?php endif; ?>
		<?php if ($err): ?><div class="alert alert-danger"><?php echo e($err); ?></div><?php endif; ?>
		<div class="card p-3 mb-3">
			<form method="post" enctype="multipart/form-data" class="vstack gap-3">
				<?php echo csrf_field(); ?>
				<div>
					<label class="form-label">Impor CSV (kolom: nisn,nama,kelas)</label>
					<input type="file" name="csv" class="form-control" accept=".csv" required>
				</div>
				<button class="btn btn-theme">Unggah & Impor</button>
			</form>
		</div>
		<div class="card p-3">
			<div>Total siswa saat ini: <strong><?php echo $total; ?></strong></div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

