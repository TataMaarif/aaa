<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

// 1. Tangkap filter hari dari dropdown frontend (default 1 hari kalau kosong)
$hari = isset($_GET['hari']) ? (int)$_GET['hari'] : 1;

// 2. Trik SQL: Kelompokkan waktu secara paksa per 30 menit (1800 detik)
$sql = "
    SELECT
        FROM_UNIXTIME((UNIX_TIMESTAMP(recorded_at) DIV 1800) * 1800) AS time_bucket,
        ROUND(AVG(suhu), 1)           AS suhu,
        ROUND(AVG(kelembaban), 1)     AS kelembaban,
        ROUND(AVG(kualitas_udara), 1) AS kualitas_udara,
        ROUND(AVG(kebisingan), 1)     AS kebisingan
    FROM sensor_data
    WHERE recorded_at >= NOW() - INTERVAL $hari DAY
    GROUP BY time_bucket
    ORDER BY time_bucket ASC
";

$rows = $pdo->query($sql)->fetchAll();

// Format untuk Chart.js
$labels         = [];
$suhu           = [];
$kelembaban     = [];
$kualitas_udara = [];
$kebisingan     = [];

foreach ($rows as $row) {
    // 3. Format Labelnya ditambahin Tanggal/Bulan biar kalau rentang 7 hari nggak bingung
    // Contoh hasil: "12 May, 16:30"
    $labels[]         = date('d M, H:i', strtotime($row['time_bucket'])); 
    $suhu[]           = $row['suhu'];
    $kelembaban[]     = $row['kelembaban'];
    $kualitas_udara[] = $row['kualitas_udara'];
    $kebisingan[]     = $row['kebisingan'];
}

echo json_encode([
    'labels'         => $labels,
    'suhu'           => $suhu,
    'kelembaban'     => $kelembaban,
    'kualitas_udara' => $kualitas_udara,
    'kebisingan'     => $kebisingan,
]);
?>