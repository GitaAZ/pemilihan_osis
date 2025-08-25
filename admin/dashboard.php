<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/util.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');
ensure_session_security();

// Ringkasan cepat
$totalSiswa = (int)db()->query('SELECT COUNT(*) AS c FROM siswa')->fetch()['c'];
$sudah = (int)db()->query('SELECT COUNT(*) AS c FROM siswa WHERE has_voted = 1')->fetch()['c'];
$belum = $totalSiswa - $sudah;

$votingOpen = get_setting('voting_open', '0') === '1';

$calon = db()->query('SELECT c.id, c.nama, c.nomor_urut, (
	SELECT COUNT(*) FROM votes v WHERE v.calon_id = c.id
) AS jumlah FROM calon c ORDER BY c.nomor_urut')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Dashboard | <?php echo SITE_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('../public/assets/css/styles.css'); ?>">
</head>
<body>
	<?php include __DIR__ . '/../includes/nav.php'; ?>
	<div class="container">
		<div class="admin-banner mb-3 d-flex align-items-center justify-content-between">
			<h1 class="h5 mb-0">Dashboard Admin</h1>
			<span id="badgeOpen" class="badge <?php echo $votingOpen ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $votingOpen ? 'Voting Dibuka' : 'Voting Ditutup'; ?></span>
		</div>
		<div class="d-flex justify-content-end mb-3 gap-2">
			<button class="btn btn-outline-theme btn-sm btn-icon" id="togglePresentation">
				<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 7h18M3 17h18M7 7v10m10-10v10" stroke="#234" stroke-width="2" stroke-linecap="round"/></svg>
				Mode Presentasi
			</button>
			<button class="btn btn-outline-theme btn-sm btn-icon" id="downloadPng">
				<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16" stroke="#234" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				Unduh PNG
			</button>
		</div>
		<div class="row g-3 mb-4">
			<div class="col-md-4">
				<div class="card stat-card p-3">
					<div class="stat-label">Total Siswa</div>
					<div id="statTotal" class="stat-value"><?php echo $totalSiswa; ?></div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card stat-card p-3">
					<div class="stat-label">Sudah Memilih</div>
					<div id="statSudah" class="stat-value text-success"><?php echo $sudah; ?></div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card stat-card p-3">
					<div class="stat-label">Belum Memilih</div>
					<div id="statBelum" class="stat-value text-danger"><?php echo $belum; ?></div>
				</div>
			</div>
		</div>

		<div class="chart-grid">
			<div class="card chart-card">
				<h2 class="h5">Perolehan Suara Real-time</h2>
				<div class="chart-wrapper" style="height: 420px; position: relative;">
					<div id="chartSkeleton" class="skeleton" style="position:absolute; inset:0; border-radius:12px; display:none;"></div>
					<canvas id="chart"></canvas>
				</div>
				<div class="mt-3">
					<a class="btn btn-outline-theme me-2 btn-icon" href="hasil_export.php?format=csv">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16" stroke="#234" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						Unduh CSV
					</a>
					<button class="btn btn-theme btn-icon" id="downloadPdf">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 4h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="#234" stroke-width="2"/><path d="M14 4v5h5" stroke="#234" stroke-width="2"/></svg>
						Unduh PDF
					</button>
				</div>
			</div>
			<div class="card chart-card">
				<h2 class="h5">Legenda Calon</h2>
				<?php if (count($calon) === 0): ?>
					<div class="empty-state">Belum ada data calon. Tambahkan calon pada menu Kelola Calon.</div>
				<?php else: ?>
					<ol id="legendList" class="mb-0"></ol>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
	<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
	<script>
	const initial = <?php echo json_encode(array_map(function($c){return ['label'=>'#'.$c['nomor_urut'].' '.$c['nama'],'jumlah'=>(int)$c['jumlah']];}, $calon)); ?>;
	const ctx = document.getElementById('chart');
	Chart.register(ChartDataLabels);
	// Hitung skala awal agar ada ruang di atas label
	const initialMax = Math.max(1, ...initial.map(d => d.jumlah));
	const initialSuggestedMax = initialMax + Math.ceil(initialMax * 0.12) + 1;

	const chart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: initial.map(d=>d.label),
			datasets: [{
				label: 'Jumlah Suara',
				data: initial.map(d=>d.jumlah),
				backgroundColor: ['#A3DC9A','#DEE791','#FFF9BD','#FFD6BA'],
				borderColor: '#234',
				borderWidth: 1
			}]
		},
		options: {
			maintainAspectRatio: false,
			layout: { padding: { top: 24 } },
			scales: { y: { beginAtZero: true, suggestedMax: initialSuggestedMax, ticks: { font: { size: 14, weight: '600' } } }, x: { ticks: { font: { size: 14, weight: '600' } } } },
			plugins: {
				legend: { display: false },
				datalabels: {
					anchor: 'end', align: 'top', color: '#123', offset: 6, clamp: true, clip: false,
					font: { weight: '700', size: 12 },
					formatter: (v) => `${v}`
				},
				tooltip: { callbacks: { label: (ctx) => `${ctx.parsed.y} suara` } }
			}
		}
	});



	document.getElementById('downloadPdf').addEventListener('click', async () => {
		const { jsPDF } = window.jspdf;
		const pdf = new jsPDF({ orientation: 'landscape' });
		pdf.text('Hasil Perolehan Suara - <?php echo SITE_NAME; ?>', 10, 10);
		const canvas = document.getElementById('chart');
		const img = canvas.toDataURL('image/png', 1.0);
		pdf.addImage(img, 'PNG', 10, 20, 270, 120);
		pdf.save('hasil-voting.pdf');
	});

	// Download PNG
	document.getElementById('downloadPng').addEventListener('click', () => {
		const canvas = document.getElementById('chart');
		const link = document.createElement('a');
		link.download = 'hasil-voting.png';
		link.href = canvas.toDataURL('image/png', 1.0);
		link.click();
	});

	// Presentation Mode Toggle
	const toggleBtn = document.getElementById('togglePresentation');
	toggleBtn.addEventListener('click', () => {
		document.documentElement.classList.toggle('presentation-mode');
		document.body.classList.toggle('presentation-mode');
	});

	async function refreshData() {
		try {
			const res = await fetch('../public/api/admin_results.php', { cache: 'no-store' });
			if (!res.ok) return;
			const json = await res.json();
			// Update stats
			document.getElementById('statTotal').textContent = json.summary.totalSiswa;
			document.getElementById('statSudah').textContent = json.summary.sudah;
			document.getElementById('statBelum').textContent = json.summary.belum;
			const badge = document.getElementById('badgeOpen');
			badge.textContent = json.summary.votingOpen ? 'Voting Dibuka' : 'Voting Ditutup';
			badge.className = 'badge ' + (json.summary.votingOpen ? 'bg-success' : 'bg-secondary');
			// Update chart
			chart.data.labels = json.candidates.map(d=>d.label);
			const values = json.candidates.map(d=>d.jumlah);
			chart.data.datasets[0].data = values;
			const maxVal = Math.max(1, ...values);
			chart.options.scales.y.suggestedMax = maxVal + Math.ceil(maxVal * 0.12) + 1;
			chart.update('none');
			// Update legend (horizontal)
			const legend = document.getElementById('legendList');
			legend.innerHTML = '';
			json.candidates.forEach((d) => {
				const li = document.createElement('li');
				li.className = 'mb-0';
				li.textContent = `${d.label} (${d.jumlah} suara)`;
				legend.appendChild(li);
			});
		} catch {}
	}
	setInterval(refreshData, 5000);
	</script>
</body>
</html>

