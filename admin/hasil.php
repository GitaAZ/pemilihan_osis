<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');
ensure_session_security();

$calon = db()->query('SELECT c.id, c.nama, c.nomor_urut, (
	SELECT COUNT(*) FROM votes v WHERE v.calon_id = c.id
) AS jumlah FROM calon c ORDER BY c.nomor_urut')->fetchAll();
$totalSuara = 0; foreach ($calon as $row) { $totalSuara += (int)$row['jumlah']; }

?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Hasil Voting | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<div class="print-header" style="display:none">
			<h1>Hasil Pemilihan OSIS - <?php echo SITE_NAME; ?></h1>
			<div class="subtitle">Periode <?php echo date('Y'); ?>/<?php echo date('Y')+1; ?></div>
		</div>
		<h1 class="h4 mb-3">Hasil Voting</h1>
		<div class="table-responsive">
			<table class="table table-bordered table-striped align-middle">
				<thead class="table-light">
					<tr>
						<th>No</th>
						<th>Calon</th>
						<th>Suara</th>
						<th>%</th>
					</tr>
				</thead>
				<tbody>
					<?php $ranked = $calon; usort($ranked, function($a,$b){ return (int)$b['jumlah'] <=> (int)$a['jumlah']; }); ?>
					<?php foreach ($ranked as $c): ?>
					<tr>
						<td>#<?php echo (int)$c['nomor_urut']; ?></td>
						<td><?php echo e($c['nama']); ?></td>
						<td>
							<div><?php echo (int)$c['jumlah']; ?></div>
							<?php $pct = $totalSuara > 0 ? ((int)$c['jumlah'] / $totalSuara) * 100 : 0; ?>
							<div class="mini-bar mt-1"><span style="right: <?php echo max(0, 100 - $pct); ?>%"></span></div>
						</td>
						<td><?php echo number_format($pct, 2); ?>%</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="d-flex gap-2 mt-3">
			<a class="btn btn-outline-theme" href="dashboard.php">Kembali</a>
			<button class="btn btn-theme" onclick="window.print()">Cetak</button>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

