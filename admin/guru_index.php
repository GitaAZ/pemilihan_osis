<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');
ensure_session_security();

$msg = '';
$err = '';

// Tambah / Update guru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$action = $_POST['action'] ?? '';
	if ($action === 'create' || $action === 'update') {
		$id = (int)($_POST['id'] ?? 0);
		$nama = trim($_POST['nama'] ?? '');
		$username = trim($_POST['username'] ?? '');
		$kelas = trim($_POST['kelas'] ?? '');
		$password = $_POST['password'] ?? '';
		if ($nama === '' || $username === '' || $kelas === '') {
			$err = 'Nama, username, dan kelas wajib diisi.';
		} else {
			try {
				if ($action === 'create') {
					$hash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : md5($username);
					$stmt = db()->prepare("INSERT INTO users (username, nama, role, kelas, password_hash) VALUES (?, ?, 'walikelas', ?, ?)");
					$stmt->execute([$username, $nama, $kelas, $hash]);
					$msg = 'Guru ditambahkan.';
				} else {
					if ($password !== '') {
						$hash = password_hash($password, PASSWORD_BCRYPT);
						$stmt = db()->prepare('UPDATE users SET username = ?, nama = ?, kelas = ?, password_hash = ? WHERE id = ? AND role = "walikelas"');
						$stmt->execute([$username, $nama, $kelas, $hash, $id]);
					} else {
						$stmt = db()->prepare('UPDATE users SET username = ?, nama = ?, kelas = ? WHERE id = ? AND role = "walikelas"');
						$stmt->execute([$username, $nama, $kelas, $id]);
					}
					$msg = 'Guru diperbarui.';
				}
			} catch (Throwable $e) {
				$err = 'Gagal menyimpan: kemungkinan username sudah dipakai.';
			}
		}
	} elseif ($action === 'reset') {
		$id = (int)($_POST['id'] ?? 0);
		$u = db()->prepare('SELECT username FROM users WHERE id = ? AND role = "walikelas"');
		$u->execute([$id]);
		$row = $u->fetch();
		if ($row) {
			$hash = md5($row['username']);
			$upd = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
			$upd->execute([$hash, $id]);
			$msg = 'Password direset ke username (MD5).' ;
		}
	}
}

// Hapus guru
if (isset($_GET['delete'])) {
	$id = (int)$_GET['delete'];
	$stmt = db()->prepare("DELETE FROM users WHERE id = ? AND role = 'walikelas'");
	$stmt->execute([$id]);
	redirect('guru_index.php');
}

$list = db()->query("SELECT id, username, nama, kelas FROM users WHERE role = 'walikelas' ORDER BY nama")->fetchAll();
// Ambil daftar kelas dari data siswa (terhubung dengan CSV impor)
$kelasOptions = db()->query('SELECT DISTINCT kelas FROM siswa ORDER BY kelas')->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kelola Guru | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<h1 class="h4 mb-3">Kelola Guru (Wali Kelas)</h1>
		<?php if ($msg): ?><div class="alert alert-success"><?php echo e($msg); ?></div><?php endif; ?>
		<?php if ($err): ?><div class="alert alert-danger"><?php echo e($err); ?></div><?php endif; ?>
		<div class="row g-3">
			<div class="col-md-5">
				<div class="card p-3">
					<h2 class="h6">Tambah / Edit Guru</h2>
					<form method="post" class="vstack gap-2">
						<?php echo csrf_field(); ?>
						<input type="hidden" name="id" id="form_id" value="">
						<div>
							<label class="form-label">Nama</label>
							<input type="text" name="nama" id="form_nama" class="form-control" required>
						</div>
						<div>
							<label class="form-label">Username</label>
							<input type="text" name="username" id="form_username" class="form-control" required>
						</div>
						<div>
							<label class="form-label">Kelas</label>
							<select name="kelas" id="form_kelas" class="form-select" required>
								<option value="" disabled selected>Pilih kelas dari data siswa</option>
								<?php foreach ($kelasOptions as $k): ?>
									<option value="<?php echo e($k); ?>"><?php echo e($k); ?></option>
								<?php endforeach; ?>
							</select>
							<div class="form-text">Daftar kelas bersumber dari data siswa (impor CSV).</div>
						</div>
						<div>
							<label class="form-label">Password (opsional saat edit)</label>
							<input type="password" name="password" id="form_password" class="form-control" placeholder="kosongkan jika tidak diubah">
						</div>
						<div class="d-flex gap-2">
							<button class="btn btn-theme" name="action" value="create" onclick="return setAction('create')">Tambah</button>
							<button class="btn btn-outline-theme" name="action" value="update" onclick="return setAction('update')">Simpan Perubahan</button>
						</div>
					</form>
				</div>
			</div>
			<div class="col-md-7">
				<div class="card p-3">
					<h2 class="h6">Daftar Guru</h2>
					<div class="table-responsive">
						<table class="table table-bordered align-middle">
							<thead class="table-light">
								<tr>
									<th>Nama</th>
									<th>Username</th>
									<th>Kelas</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($list as $g): ?>
								<tr>
									<td><?php echo e($g['nama']); ?></td>
									<td><?php echo e($g['username']); ?></td>
									<td><?php echo e($g['kelas']); ?></td>
									<td class="d-flex gap-2">
										<button class="btn btn-sm btn-theme" onclick='fillForm(<?php echo (int)$g['id']; ?>, <?php echo json_encode($g['nama']); ?>, <?php echo json_encode($g['username']); ?>, <?php echo json_encode($g['kelas']); ?>)'>Edit</button>
										<form method="post" onsubmit="return confirm('Reset password ke MD5(username)?')" class="d-inline">
											<?php echo csrf_field(); ?>
											<input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
											<button class="btn btn-sm btn-outline-secondary" name="action" value="reset">Reset Password</button>
										</form>
										<a class="btn btn-sm btn-outline-danger" href="?delete=<?php echo (int)$g['id']; ?>" onclick="return confirm('Hapus akun guru ini?')">Hapus</a>
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
	<script>
	function fillForm(id, nama, username, kelas) {
		document.getElementById('form_id').value = id;
		document.getElementById('form_nama').value = nama;
		document.getElementById('form_username').value = username;
		document.getElementById('form_kelas').value = kelas;
		document.getElementById('form_password').value = '';
	}
	function setAction(act) {
		document.activeElement.value = act;
		return true;
	}
	</script>
</body>
</html>

