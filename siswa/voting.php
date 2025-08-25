<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('siswa');
ensure_session_security();

$votingOpen = get_setting('voting_open', '0') === '1';
$user = current_user();

// Cek apakah sudah memilih
$stmtUser = db()->prepare('SELECT has_voted FROM siswa WHERE nisn = ?');
$stmtUser->execute([$user['nisn']]);
$u = $stmtUser->fetch();
if ($u && (int)$u['has_voted'] === 1) {
	redirect('konfirmasi.php');
}

$stmt = db()->query('SELECT * FROM calon ORDER BY nomor_urut ASC');
$calon = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Voting | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/background.php'; print_logo_background(); ?>
	<div class="container pb-5">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<h1 class="h4 mb-0">Pilih Calon OSIS</h1>
			<div class="d-flex align-items-center gap-2">
				<?php if ($votingOpen): ?>
					<span class="badge bg-success">Voting Dibuka</span>
				<?php else: ?>
					<span class="badge bg-secondary">Voting Ditutup</span>
				<?php endif; ?>
				<a class="btn btn-outline-theme btn-sm" href="<?php echo base_url('../auth/logout.php'); ?>">Logout</a>
			</div>
		</div>
		<div class="student-banner mb-3">
			<div class="row g-3 align-items-center">
				<div class="col-md-4">
					<div class="small text-muted">NISN</div>
					<div class="fw-bold"><?php echo e($user['nisn']); ?></div>
				</div>
				<div class="col-md-4">
					<div class="small text-muted">Nama</div>
					<div class="fw-bold"><?php echo e($user['nama']); ?></div>
				</div>
				<div class="col-md-4">
					<div class="small text-muted">Kelas</div>
					<div class="fw-bold"><?php echo e($user['kelas']); ?></div>
				</div>
			</div>
			<div class="small text-muted mt-2">Pastikan identitas Anda sudah benar sebelum memilih.</div>
		</div>
		<?php if (!$votingOpen): ?>
			<div class="alert alert-warning">Periode voting belum dibuka atau sudah ditutup.</div>
		<?php else: ?>
			<form method="post" action="submit_vote.php" class="row g-3" id="formVoting">
				<?php echo csrf_field(); ?>
				<?php if (count($calon) === 0): ?>
					<div class="col-12">
						<div class="empty-state">Belum ada data calon OSIS. Silakan hubungi panitia/administrator.</div>
					</div>
				<?php endif; ?>
				<?php foreach ($calon as $c): ?>
				<div class="col-md-6 col-lg-4">
					<div class="candidate-card h-100" data-candidate-id="<?php echo (int)$c['id']; ?>">
						<?php if (!empty($c['foto'])): ?>
							<img src="<?php echo base_url('../public/uploads/calon/' . e($c['foto'])); ?>" class="candidate-photo" alt="Foto <?php echo e($c['nama']); ?>">
						<?php endif; ?>
						<div class="candidate-body">
							<span class="candidate-number">#<?php echo (int)$c['nomor_urut']; ?></span>
							<div class="candidate-name"><?php echo e($c['nama']); ?></div>
							<div class="candidate-meta mb-2"><strong>Visi:</strong> <?php echo nl2br(e($c['visi'])); ?></div>
							<div class="candidate-meta"><strong>Misi:</strong> <?php echo nl2br(e($c['misi'])); ?></div>
						</div>
						<div class="candidate-actions d-flex justify-content-between align-items-center">
							<div class="form-check">
								<input class="form-check-input" type="radio" name="calon_id" id="calon_<?php echo (int)$c['id']; ?>" value="<?php echo (int)$c['id']; ?>" required>
								<label class="form-check-label" for="calon_<?php echo (int)$c['id']; ?>">Pilih calon ini</label>
							</div>
							<button type="button" class="btn btn-ghost-theme" data-bs-toggle="modal" data-bs-target="#detail_<?php echo (int)$c['id']; ?>">Detail</button>
						</div>
					</div>
				</div>
				<!-- Modal Detail -->
				<div class="modal fade" id="detail_<?php echo (int)$c['id']; ?>" tabindex="-1" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">#<?php echo (int)$c['nomor_urut']; ?> - <?php echo e($c['nama']); ?></h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="row g-3 align-items-start">
									<div class="col-md-5">
										<?php if (!empty($c['foto'])): ?>
											<img src="<?php echo base_url('../public/uploads/calon/' . e($c['foto'])); ?>" alt="Foto <?php echo e($c['nama']); ?>" class="img-fluid rounded shadow-sm">
										<?php endif; ?>
									</div>
									<div class="col-md-7">
										<h6 class="mb-2">Visi</h6>
										<p><?php echo nl2br(e($c['visi'])); ?></p>
										<h6 class="mb-2">Misi</h6>
										<p><?php echo nl2br(e($c['misi'])); ?></p>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Tutup</button>
							</div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
				<div class="col-12 d-none d-lg-block">
					<button class="btn btn-theme w-100">Kirim Pilihan</button>
				</div>
				<div class="sticky-submit">
					<button class="btn btn-theme w-100">Kirim Pilihan</button>
				</div>
			</form>
		<?php endif; ?>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
	const form = document.getElementById('formVoting');
	if (form) {
		form.addEventListener('change', (e) => {
			if (e.target && e.target.name === 'calon_id') {
				document.querySelectorAll('.candidate-card').forEach(el => el.classList.remove('selected'));
				const id = e.target.value;
				const card = document.querySelector('.candidate-card[data-candidate-id="'+id+'"]');
				if (card) card.classList.add('selected');
			}
		});
	}
	</script>
</body>
</html>

