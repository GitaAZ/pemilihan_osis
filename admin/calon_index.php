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
	$nama = trim($_POST['nama'] ?? '');
	$visi = trim($_POST['visi'] ?? '');
	$misi = trim($_POST['misi'] ?? '');
	$nomor = (int)($_POST['nomor_urut'] ?? 0);
	if ($nama === '' || $visi === '' || $misi === '' || $nomor <= 0) {
		$err = 'Semua field wajib diisi dan nomor urut harus valid.';
	} else {
		$fotoName = null;
		if (!empty($_FILES['foto']['name'])) {
			$fotoName = upload_image($_FILES['foto'], __DIR__ . '/../public/uploads/calon');
			if (!$fotoName) { $err = 'Format/unggah foto tidak valid.'; }
		}
		if (!$err) {
			$stmt = db()->prepare('INSERT INTO calon (nama, foto, visi, misi, nomor_urut) VALUES (?, ?, ?, ?, ?)');
			$stmt->execute([$nama, $fotoName, $visi, $misi, $nomor]);
			$msg = 'Calon ditambahkan.';
		}
	}
}

if (isset($_GET['delete'])) {
	$id = (int)$_GET['delete'];
	$stmt = db()->prepare('DELETE FROM calon WHERE id = ?');
	$stmt->execute([$id]);
	redirect('calon_index.php');
}

$list = db()->query('SELECT * FROM calon ORDER BY nomor_urut')->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kelola Calon | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<h1 class="h4 mb-3">Kelola Calon</h1>
		<?php if ($msg): ?><div class="alert alert-success"><?php echo e($msg); ?></div><?php endif; ?>
		<?php if ($err): ?><div class="alert alert-danger"><?php echo e($err); ?></div><?php endif; ?>
		<div class="row g-3">
			<div class="col-md-5">
				<div class="card p-3">
					<h2 class="h6">Tambah Calon</h2>
					<form method="post" enctype="multipart/form-data" class="vstack gap-2">
						<?php echo csrf_field(); ?>
						<div>
							<label class="form-label">Nama</label>
							<input type="text" name="nama" class="form-control" required>
						</div>
						<div>
							<label class="form-label">Nomor Urut</label>
							<input type="number" name="nomor_urut" class="form-control" required>
						</div>
						<div>
							<label class="form-label">Foto</label>
							<input type="file" name="foto" class="form-control" accept="image/*">
						</div>
						<div>
							<label class="form-label">Visi</label>
							<textarea name="visi" class="form-control" rows="2" required></textarea>
						</div>
						<div>
							<label class="form-label">Misi</label>
							<textarea name="misi" class="form-control" rows="3" required></textarea>
						</div>
						<button class="btn btn-theme">Simpan</button>
					</form>
				</div>
			</div>
			<div class="col-md-7">
				<div class="card p-3">
					<h2 class="h6">Daftar Calon</h2>
					<div class="table-responsive">
						<table class="table table-bordered">
							<thead class="table-light">
								<tr>
									<th>No</th>
									<th>Nama</th>
									<th>Foto</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($list as $c): ?>
								<tr>
									<td>#<?php echo (int)$c['nomor_urut']; ?></td>
									<td><?php echo e($c['nama']); ?></td>
									<td>
										<?php if ($c['foto']): ?>
											<img src="<?php echo base_url('../public/uploads/calon/' . e($c['foto'])); ?>" width="60">
										<?php endif; ?>
									</td>
									<td>
										<a class="btn btn-sm btn-outline-danger" href="?delete=<?php echo (int)$c['id']; ?>" onclick="return confirm('Hapus calon ini?');">Hapus</a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

