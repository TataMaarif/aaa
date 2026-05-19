<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/phpMQTT.php'; // Pastikan library phpMQTT sudah di folder includes

$server   = 'localhost'; 
$port     = 1883;
$username = ''; 
$password = ''; 
$client_id = 'PHP-MQTT-Bridge-K3';

$mqtt = new bluerhinos\phpMQTT($server, $port, $client_id);

if(!$mqtt->connect(true, NULL, $username, $password)) {
    exit("Gagal terhubung ke Mosquitto Broker!\n");
}

echo "Jembatan MQTT-MySQL K3 Laboratorium Aktif... Menunggu data...\n";

// Daftarkan topik K3 sesuai struktur sistem kita
$topics['lab/k3/sensor/#'] = array('qos' => 0, 'function' => 'procMsg');
$mqtt->subscribe($topics, 0);

// Array penampung sementara (Buffer) data per Node sebelum di-insert ke database
$data_buffer = [];

while($mqtt->proc()) {
    // Loop terus berjalan menanti pesan MQTT
}

$mqtt->close();

function procMsg($topic, $msg) {
    global $pdo, $data_buffer;
    
    echo "Pesan masuk di topik [{$topic}]: {$msg}\n";
    
    // Pecah topik untuk mengambil jenis sensor (Contoh: lab/k3/sensor/suhu)
    // Jika ESP32 kamu mengirim dengan format multi-node, pastikan topik menyertakan node_id
    // Untuk mempermudah, kita asumsikan data ini dari Node 1 terlebih dahulu
    $node_id = 1; 
    
    if ($topic == 'lab/k3/sensor/suhu')           $data_buffer[$node_id]['suhu'] = (float)$msg;
    if ($topic == 'lab/k3/sensor/kelembapan')     $data_buffer[$node_id]['kelembaban'] = (float)$msg;
    if ($topic == 'lab/k3/sensor/kualitas_udara') $data_buffer[$node_id]['kualitas_udara'] = (float)$msg;
    if ($topic == 'lab/k3/sensor/kebisingan_db')  $data_buffer[$node_id]['kebisingan'] = (float)$msg;
    
    // Jika semua parameter dalam satu rentang waktu dari node tersebut sudah terkumpul lengkap
    if (isset($data_buffer[$node_id]['suhu']) && 
        isset($data_buffer[$node_id]['kelembaban']) && 
        isset($data_buffer[$node_id]['kualitas_udara']) && 
        isset($data_buffer[$node_id]['kebisingan'])) {
        
        $suhu = $data_buffer[$node_id]['suhu'];
        $kelembaban = $data_buffer[$node_id]['kelembaban'];
        $kualitas_udara = $data_buffer[$node_id]['kualitas_udara'];
        $kebisingan = $data_buffer[$node_id]['kebisingan'];
        
        // --- KLASIFIKASI STATUS SESUAI ATURAN LOGIKA DI data.php KAMU ---
        $status_suhu = ($suhu < 18) ? 'Rendah' : (($suhu > 30) ? 'Tinggi' : 'Normal');
        $status_kelembaban = ($kelembaban < 40) ? 'Rendah' : (($kelembaban > 60) ? 'Tinggi' : 'Normal');
        $status_udara = ($kualitas_udara < 100) ? 'Baik' : (($kualitas_udara <= 150) ? 'Sedang' : 'Buruk');
        $status_bising = ($kebisingan < 70) ? 'Normal' : (($kebisingan <= 85) ? 'Sedang' : 'Berbahaya');
        
        try {
            // SQL Insert mengikuti query tabel sensor_data di monitoring.sql milikmu
            $sql = "INSERT INTO sensor_data 
                    (node_id, suhu, kelembaban, kualitas_udara, kebisingan, 
                     status_suhu, status_kelembaban, status_udara, status_bising, recorded_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $node_id, $suhu, $kelembaban, $kualitas_udara, $kebisingan,
                $status_suhu, $status_kelembaban, $status_udara, $status_bising
            ]);
            
            echo ">>> [SUKSES] Data Node {$node_id} berhasil disimpan ke database MySQL!\n";
        } catch (Exception $e) {
            echo ">>> [ERROR] Gagal simpan ke database: " . $e->getMessage() . "\n";
        }
        
        // Bersihkan buffer untuk pembacaan siklus berikutnya dari ESP32
        unset($data_buffer[$node_id]);
    }
}