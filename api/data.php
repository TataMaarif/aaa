<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if (!isset($pdo) || !$pdo instanceof PDO) {
    throw new RuntimeException('PDO connection not initialized. Check includes/db.php');
}

// Query alternatif yang lebih aman jika query INNER JOIN bawaan rentan 'Tidak ada data'
$sql = "
    SELECT * FROM sensor_data 
    WHERE id IN (
        SELECT MAX(id) 
        FROM sensor_data 
        GROUP BY node_id
    ) 
    ORDER BY node_id ASC
";

$rows = $pdo->query($sql)->fetchAll();

// Hitung rata-rata dari node yang aktif
$count = count($rows);
if ($count === 0) {
    echo json_encode(['error' => 'Tidak ada data']);
    exit;
}

$avg = [
    'suhu'           => 0,
    'kelembaban'     => 0,
    'kualitas_udara' => 0,
    'kebisingan'     => 0,
];

foreach ($rows as $row) {
    $avg['suhu']           += $row['suhu'];
    $avg['kelembaban']     += $row['kelembaban'];
    $avg['kualitas_udara'] += $row['kualitas_udara'];
    $avg['kebisingan']     += $row['kebisingan'];
}

foreach ($avg as $key => $val) {
    $avg[$key] = round($val / $count, 1);
}

// Tentukan status berdasarkan rata-rata
// -- Suhu: Normal 18-30°C
$avg['status_suhu'] = ($avg['suhu'] < 18) ? 'Rendah'
    : (($avg['suhu'] > 30) ? 'Tinggi' : 'Normal');

// -- Kelembaban: Normal 40-60%RH
$avg['status_kelembaban'] = ($avg['kelembaban'] < 40) ? 'Rendah'
    : (($avg['kelembaban'] > 60) ? 'Tinggi' : 'Normal');

// -- Kualitas Udara IAQ: Baik <100, Sedang 100-150, Buruk >150
$avg['status_udara'] = ($avg['kualitas_udara'] < 100) ? 'Baik'
    : (($avg['kualitas_udara'] <= 150) ? 'Sedang' : 'Buruk');

// -- Kebisingan: Normal <70dB, Sedang 70-85dB, Berbahaya >85dB
$avg['status_bising'] = ($avg['kebisingan'] < 70) ? 'Normal'
    : (($avg['kebisingan'] <= 85) ? 'Sedang' : 'Berbahaya');

// Status keseluruhan
$bahaya = [];
if ($avg['status_suhu']       !== 'Normal') $bahaya[] = 'Suhu';
if ($avg['status_kelembaban'] !== 'Normal') $bahaya[] = 'Kelembaban';
if ($avg['status_udara']      === 'Buruk')  $bahaya[] = 'Kualitas Udara';
if ($avg['status_bising']     === 'Berbahaya') $bahaya[] = 'Kebisingan';

$avg['status_lingkungan'] = empty($bahaya) ? 'AMAN' : 'BAHAYA';
$avg['parameter_bahaya']  = $bahaya;
$avg['node_aktif']        = $count;
$avg['last_update']       = $rows[0]['recorded_at'] ?? '-';

echo json_encode($avg);
?>

