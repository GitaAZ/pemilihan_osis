<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

$isOpen = get_setting('voting_open', '0') === '1';

$rows = [];
if (!$isOpen) {
	$rows = db()->query('SELECT c.nomor_urut, c.nama, COUNT(v.id) AS jumlah
		FROM calon c LEFT JOIN votes v ON v.calon_id = c.id
		GROUP BY c.id, c.nomor_urut, c.nama
		ORDER BY c.nomor_urut')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Hasil Akhir | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css'); ?>">
</head>
<body class="bg-soft-yellow">
	<div class="container py-5">
		<div class="row justify-content-center">
			<div class="col-lg-8">
				<div class="card shadow border-0">
					<div class="card-body p-4">
						<h1 class="h4 mb-3 text-center">Hasil Pemilihan OSIS<br><?php echo SITE_NAME; ?></h1>
						<?php if ($isOpen): ?>
							<div class="alert alert-warning text-center">Hasil belum dapat ditampilkan karena periode voting masih berlangsung.</div>
						<?php else: ?>
							<div class="table-responsive">
								<table class="table table-bordered">
									<thead class="table-light">
										<tr>
											<th>No</th>
											<th>Calon</th>
											<th>Suara</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($rows as $r): ?>
										<tr>
											<td>#<?php echo (int)$r['nomor_urut']; ?></td>
											<td><?php echo e($r['nama']); ?></td>
											<td><?php echo (int)$r['jumlah']; ?></td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
						<div class="text-center mt-3">
							<a class="btn btn-outline-theme" href="<?php echo base_url('index.php'); ?>">Kembali</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

