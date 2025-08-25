<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$format = $_GET['format'] ?? 'csv';

$rows = db()->query('SELECT c.nomor_urut, c.nama, COUNT(v.id) AS jumlah
FROM calon c LEFT JOIN votes v ON v.calon_id = c.id
GROUP BY c.id, c.nomor_urut, c.nama
ORDER BY c.nomor_urut')->fetchAll();

if ($format === 'csv') {
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="hasil_voting.csv"');
	$out = fopen('php://output', 'w');
	fputcsv($out, ['Nomor Urut', 'Nama Calon', 'Jumlah Suara']);
	foreach ($rows as $r) {
		fputcsv($out, [$r['nomor_urut'], $r['nama'], $r['jumlah']]);
	}
	fclose($out);
	exit;
}

http_response_code(400);
echo 'Format tidak didukung';

