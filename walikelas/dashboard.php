<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('walikelas');
ensure_session_security();

$user = current_user();
$kelas = $user['kelas'] ?? '';

$stmt = db()->prepare('SELECT nisn, nama, has_voted, voted_at FROM siswa WHERE kelas = ? ORDER BY nama');
$stmt->execute([$kelas]);
$siswa = $stmt->fetchAll();
$total = count($siswa);
$sudah = 0;
foreach ($siswa as $row) { if ((int)$row['has_voted'] === 1) { $sudah++; } }
$belum = $total - $sudah;

?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard Wali Kelas | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<div class="teacher-banner mb-3 d-flex align-items-center justify-content-between">
			<div>
				<div class="small text-muted">Kelas</div>
				<div class="h5 mb-0"><?php echo e($kelas); ?></div>
			</div>
			<div class="d-flex gap-3">
				<div><div class="small text-muted">Total</div><div class="h5 mb-0"><?php echo $total; ?></div></div>
				<div><div class="small text-muted">Sudah</div><div class="h5 text-success mb-0"><?php echo $sudah; ?></div></div>
				<div><div class="small text-muted">Belum</div><div class="h5 text-danger mb-0"><?php echo $belum; ?></div></div>
			</div>
		</div>
		<form class="row g-2 mb-3 align-items-center">
			<div class="col-auto">
				<select name="filter" class="form-select" onchange="this.form.submit()">
					<?php $filter = $_GET['filter'] ?? 'all'; ?>
					<option value="all" <?php echo $filter==='all'?'selected':''; ?>>Tampilkan Semua</option>
					<option value="belum" <?php echo $filter==='belum'?'selected':''; ?>>Hanya Belum Memilih</option>
					<option value="sudah" <?php echo $filter==='sudah'?'selected':''; ?>>Hanya Sudah Memilih</option>
				</select>
			</div>
			<div class="col">
				<input type="search" class="form-control" id="searchInput" placeholder="Cari nama atau NISN...">
			</div>
		</form>
		<div class="table-responsive" style="max-height: 70vh;">
			<table class="table table-bordered align-middle" id="tableSiswa">
				<thead class="table-light">
					<tr>
						<th class="sticky">NISN</th>
						<th class="sticky">Nama</th>
						<th class="sticky">Status</th>
						<th class="sticky">Waktu</th>
					</tr>
				</thead>
				<tbody>
					<?php $filter = $_GET['filter'] ?? 'all'; foreach ($siswa as $s): ?>
					<?php if ($filter==='belum' && (int)$s['has_voted']===1) continue; ?>
					<?php if ($filter==='sudah' && (int)$s['has_voted']===0) continue; ?>
					<tr>
						<td><?php echo e($s['nisn']); ?></td>
						<td><?php echo e($s['nama']); ?></td>
						<td>
							<?php if ((int)$s['has_voted'] === 1): ?>
								<span class="status-chip success">✅ sudah memilih</span>
							<?php else: ?>
								<span class="status-chip danger">❌ belum memilih</span>
							<?php endif; ?>
						</td>
						<td><?php echo e($s['voted_at'] ?: '-'); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
	const searchInput = document.getElementById('searchInput');
	const table = document.getElementById('tableSiswa');
	if (searchInput && table) {
		searchInput.addEventListener('input', () => {
			const q = searchInput.value.toLowerCase();
			for (const row of table.tBodies[0].rows) {
				const nisn = row.cells[0].innerText.toLowerCase();
				const nama = row.cells[1].innerText.toLowerCase();
				row.style.display = (nisn.includes(q) || nama.includes(q)) ? '' : 'none';
			}
		});
	}
	</script>
</body>
</html>

