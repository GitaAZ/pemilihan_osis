<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/background.php';

ensure_session_security();
$user = current_user();
$role = $user['role'] ?? 'guest';
?>

<?php print_logo_background(); ?>
<nav class="topbar border-bottom bg-white mb-3">
	<div class="container d-flex align-items-center justify-content-between py-2">
		<button class="btn btn-outline-theme" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">Menu</button>
		<a class="fw-bold text-decoration-none text-dark" href="<?php echo base_url('../public/index.php'); ?>">OSIS | <?php echo SITE_NAME; ?></a>
		<div class="d-none d-md-block">
			<?php if ($role !== 'guest'): ?>
				<span class="me-3">Halo, <?php echo e($user['nama'] ?? ''); ?></span>
				<a class="btn btn-sm btn-outline-theme" href="<?php echo base_url('../auth/logout.php'); ?>">Logout</a>
			<?php endif; ?>
		</div>
	</div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="sidebarLabel">Menu</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<div class="nav flex-column">
			<?php if ($role === 'admin'): ?>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/dashboard.php'); ?>">Dashboard</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/siswa_import.php'); ?>">Kelola Siswa</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/siswa_reset.php'); ?>">Reset Siswa</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/guru_index.php'); ?>">Kelola Guru</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/calon_index.php'); ?>">Kelola Calon</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/admin_users.php'); ?>">Kelola Admin</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/settings.php'); ?>">Pengaturan</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/hasil.php'); ?>">Hasil</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../admin/reset_password.php'); ?>">Reset Password</a>
			<?php elseif ($role === 'walikelas'): ?>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../walikelas/dashboard.php'); ?>">Dashboard</a>
			<?php elseif ($role === 'siswa'): ?>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../siswa/voting.php'); ?>">Voting</a>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../siswa/konfirmasi.php'); ?>">Konfirmasi</a>
			<?php else: ?>
				<a class="btn btn-outline-theme w-100 mb-2" href="<?php echo base_url('../public/index.php'); ?>">Beranda</a>
			<?php endif; ?>
			<?php if ($role !== 'guest'): ?>
				<hr>
				<a class="btn btn-theme w-100" href="<?php echo base_url('../auth/logout.php'); ?>">Logout</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>

<script>
window.AppToast = (function(){
  const container = document.getElementById('toastContainer');
  function show(message, type = 'success', timeout = 3500){
    if (!container) return;
    const el = document.createElement('div');
    el.className = 'toast-item ' + type;
    el.innerHTML = `<span>${message}</span><button class="toast-close" aria-label="Tutup">×</button>`;
    container.appendChild(el);
    const close = () => { el.remove(); };
    el.querySelector('.toast-close').addEventListener('click', close);
    setTimeout(close, timeout);
  }
  return { show };
})();
</script>


