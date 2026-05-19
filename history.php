<?php require_once 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>History Chart - Monitoring</title>

    <link href="../sbadmin5/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        :root {
            --dark-bg:    #0f1923;
            --sidebar-bg: #141e2b;
            --card-bg:    #1a2535;
        }
        body { background-color: var(--dark-bg) !important; color: #fff; }
        #layoutSidenav_nav .sb-sidenav { background-color: var(--sidebar-bg) !important; }
        .sb-topnav { background-color: var(--sidebar-bg) !important; }

        .chart-card {
            background: var(--card-bg);
            border: none;
            border-radius: 16px;
            padding: 1.4rem;
            margin-bottom: 1.5rem;
        }
        .chart-card .chart-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sb-sidenav-dark .nav-link.active {
            background-color: #2563eb !important;
            border-radius: 8px;
        }
        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-bar select, .filter-bar input {
            background: var(--card-bg);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        .filter-bar button {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 0.85rem;
            cursor: pointer;
        }
    </style>
</head>
<body class="sb-nav-fixed">

<nav class="sb-topnav navbar navbar-expand navbar-dark">
    <button class="btn btn-link btn-sm ms-3 me-2" id="sidebarToggle">
        <i class="fas fa-bars" style="color:#fff"></i>
    </button>
    <a class="navbar-brand fw-bold" href="index.php">Lab. Telkom Barat I/04</a>
</nav>

<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav flex-column p-2">
                    <a class="nav-link px-3 py-2 mb-1" href="index.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-house-chimney"></i></div>
                        Dashboard
                    </a>
                    <a class="nav-link active px-3 py-2" href="history.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        History Chart
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 pt-4">

                <h4 class="fw-bold mb-0">History Chart</h4>
                <p class="text-muted mb-3" style="font-size:0.9rem">Riwayat data sensor 24 jam terakhir (rata-rata 2 node)</p>

                <!-- Filter rentang waktu (opsional, untuk pengembangan berikutnya) -->
                <div class="filter-bar">
                    <span style="font-size:0.85rem; opacity:0.6">Rentang:</span>
                    <select id="rangeSelect" onchange="loadCharts()">
                       <option value="1">24 Jam Terakhir</option>
                        <option value="3">3 Hari Terakhir</option>
                        <option value="5">5 Hari Terakhir</option>
                        <option value="7">7 Hari Terakhir</option>
                    </select>
                </div>

                <!-- Chart Suhu -->
                <div class="chart-card">
                    <div class="chart-title" style="color:#4f8ef7">
                        <i class="fas fa-temperature-half"></i> Suhu (°C)
                    </div>
                    <canvas id="chartSuhu" height="80"></canvas>
                </div>

                <!-- Chart Kelembaban -->
                <div class="chart-card">
                    <div class="chart-title" style="color:#2ec4b6">
                        <i class="fas fa-droplet"></i> Kelembaban (%RH)
                    </div>
                    <canvas id="chartKelembaban" height="80"></canvas>
                </div>

                <!-- Chart Kualitas Udara -->
                <div class="chart-card">
                    <div class="chart-title" style="color:#9b59b6">
                        <i class="fas fa-cloud"></i> Kualitas Udara (IAQ)
                    </div>
                    <canvas id="chartUdara" height="80"></canvas>
                </div>

                <!-- Chart Kebisingan -->
                <div class="chart-card">
                    <div class="chart-title" style="color:#f5a623">
                        <i class="fas fa-volume-high"></i> Kebisingan (dB)
                    </div>
                    <canvas id="chartBising" height="80"></canvas>
                </div>

            </div>
        </main>

        <footer class="py-3 mt-auto" style="background:var(--sidebar-bg)">
            <div class="container-fluid px-4">
                <div class="text-muted small text-center">
                    &copy; <?= date('Y') ?> Monitoring Kondisi Lingkungan Laboratorium
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../sbadmin5/js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Simpan instance chart agar bisa di-destroy saat refresh
let charts = {};

function makeChart(id, label, color, labels, data) {
    if (charts[id]) charts[id].destroy();

    const ctx = document.getElementById(id).getContext('2d');
    charts[id] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                borderColor: color,
                backgroundColor: color + '22',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: color,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2535',
                    titleColor: '#fff',
                    bodyColor: '#ccc',
                }
            },
            scales: {
                x: {
                    ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 11 } },
                    grid:  { color: 'rgba(255,255,255,0.05)' }
                },
                y: {
                    ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 11 } },
                    grid:  { color: 'rgba(255,255,255,0.08)' }
                }
            }
        }
    });
}

async function loadCharts() {
    const hari = document.getElementById('rangeSelect').value; // Ambil nilai 1, 3, 5, atau 7
    try {
        // Ganti ?jam= jadi ?hari=
        const res  = await fetch('api/history.php?hari=' + hari); 
        const data = await res.json();

        makeChart('chartSuhu',       'Suhu (°C)',       '#4f8ef7', data.labels, data.suhu);
        makeChart('chartKelembaban', 'Kelembaban (%RH)','#2ec4b6', data.labels, data.kelembaban);
        makeChart('chartUdara',      'IAQ',             '#9b59b6', data.labels, data.kualitas_udara);
        makeChart('chartBising',     'Kebisingan (dB)', '#f5a623', data.labels, data.kebisingan);

    } catch (err) {
        console.error('Gagal memuat histori:', err);
    }
}

loadCharts();
</script>
</body>
</html>
