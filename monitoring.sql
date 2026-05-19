-- Jalankan script ini di phpMyAdmin
-- Menu: SQL → paste → klik Go

CREATE DATABASE IF NOT EXISTS monitoring_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE monitoring_db;

CREATE TABLE IF NOT EXISTS sensor_data (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    node_id         TINYINT NOT NULL COMMENT '1 atau 2',
    suhu            FLOAT NOT NULL COMMENT 'Celsius',
    kelembaban      FLOAT NOT NULL COMMENT 'Persen RH',
    kualitas_udara  FLOAT NOT NULL COMMENT 'IAQ index',
    kebisingan      FLOAT NOT NULL COMMENT 'dB',
    status_suhu     VARCHAR(10) NOT NULL COMMENT 'Normal/Tinggi/Rendah',
    status_kelembaban VARCHAR(10) NOT NULL COMMENT 'Normal/Tinggi/Rendah',
    status_udara    VARCHAR(10) NOT NULL COMMENT 'Baik/Sedang/Buruk',
    status_bising   VARCHAR(10) NOT NULL COMMENT 'Normal/Sedang/Berbahaya',
    recorded_at     DATETIME NOT NULL,
    INDEX idx_node  (node_id),
    INDEX idx_time  (recorded_at)
) ENGINE=InnoDB;

-- Data dummy untuk testing tampilan dashboard
INSERT INTO sensor_data
    (node_id, suhu, kelembaban, kualitas_udara, kebisingan,
     status_suhu, status_kelembaban, status_udara, status_bising, recorded_at)
VALUES
-- Node 1
(1, 25.4, 55.2, 80,  65.3, 'Normal', 'Normal', 'Baik',   'Normal',   NOW() - INTERVAL 30 MINUTE),
(1, 26.1, 57.0, 95,  68.0, 'Normal', 'Normal', 'Baik',   'Normal',   NOW() - INTERVAL 60 MINUTE),
(1, 27.8, 60.5, 130, 72.5, 'Normal', 'Tinggi', 'Sedang', 'Sedang',   NOW() - INTERVAL 90 MINUTE),
(1, 24.9, 52.1, 75,  60.1, 'Normal', 'Normal', 'Baik',   'Normal',   NOW() - INTERVAL 120 MINUTE),
(1, 28.3, 63.0, 160, 80.2, 'Tinggi', 'Tinggi', 'Sedang', 'Berbahaya',NOW() - INTERVAL 150 MINUTE),
(1, 25.0, 54.0, 70,  63.4, 'Normal', 'Normal', 'Baik',   'Normal',   NOW() - INTERVAL 180 MINUTE),
-- Node 2
(2, 24.8, 54.0, 85,  67.1, 'Normal', 'Normal', 'Baik',   'Normal',   NOW() - INTERVAL 30 MINUTE),
(2, 25.5, 56.3, 90,  69.5, 'Normal', 'Normal', 'Baik',   'Normal',   NOW() - INTERVAL 60 MINUTE),
(2, 26.9, 58.7, 110, 70.8, 'Normal', 'Normal', 'Sedang', 'Sedang',   NOW() - INTERVAL 90 MINUTE),
(2, 24.2, 51.0, 72,  61.3, 'Normal', 'Normal', 'Baik',   'Normal',   NOW() - INTERVAL 120 MINUTE),
(2, 27.5, 61.2, 145, 78.9, 'Normal', 'Tinggi', 'Sedang', 'Sedang',   NOW() - INTERVAL 150 MINUTE),
(2, 24.7, 53.5, 68,  62.0, 'Normal', 'Normal', 'Baik',   'Normal',   NOW() - INTERVAL 180 MINUTE);
