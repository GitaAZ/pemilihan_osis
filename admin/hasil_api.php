<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');
header('Content-Type: application/json; charset=utf-8');

try {
	$totalSiswa = (int)db()->query('SELECT COUNT(*) AS c FROM siswa')->fetch()['c'];
	$sudah = (int)db()->query('SELECT COUNT(*) AS c FROM siswa WHERE has_voted = 1')->fetch()['c'];
	$belum = $totalSiswa - $sudah;
	$votingOpen = get_setting('voting_open', '0') === '1';

	$calon = db()->query('SELECT c.id, c.nama, c.nomor_urut, (
		SELECT COUNT(*) FROM votes v WHERE v.calon_id = c.id
	) AS jumlah FROM calon c ORDER BY c.nomor_urut')->fetchAll();

	$response = [
		'summary' => [
			'totalSiswa' => $totalSiswa,
			'sudah' => $sudah,
			'belum' => $belum,
			'votingOpen' => $votingOpen,
		],
		'candidates' => array_map(function($c){
			return [
				'label' => '#' . $c['nomor_urut'] . ' ' . $c['nama'],
				'jumlah' => (int)$c['jumlah'],
			];
		}, $calon),
	];
	echo json_encode($response);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['error' => 'failed']);
}