<?php require_once 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Monitoring Kondisi Lingkungan</title>

    <!-- SB Admin 5 CSS -->
    <link href="../sbadmin5/css/styles.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        :root {
            --card-suhu:      #4f8ef7;
            --card-kelembaban:#2ec4b6;
            --card-udara:     #9b59b6;
            --card-bising:    #f5a623;
            --card-aman:      #27ae60;
            --card-bahaya:    #e74c3c;
            --dark-bg:        #0f1923;
            --sidebar-bg:     #141e2b;
        }

        body { background-color: var(--dark-bg) !important; color: #fff; }
        #layoutSidenav_nav .sb-sidenav { background-color: var(--sidebar-bg) !important; }
        .sb-topnav { background-color: var(--sidebar-bg) !important; } 

        /* Kartu sensor */
        .sensor-card {
            border: none;
            border-radius: 18px;
            padding: 1.4rem 1.6rem 1rem;
            color: #fff;
            position: relative;
            overflow: hidden;
            min-height: 180px;
        }
        .sensor-card .card-label {
            font-size: 1.25rem;
            font-weight: 500;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sensor-card .card-value {
            font-size: 3.5rem;
            font-weight: 700;
            margin: 0.3rem 0 0.8rem;
            line-height: 1;
        }
        .sensor-card .card-range {
            display: flex;
            justify-content: space-between;
            font-size: 1rem;
            opacity: 0.75;
            margin-top: 4px;
        }
        .sensor-card .range-bar {
            width: 100%;
            height: 4px;
            border-radius: 2px;
            background: rgba(255,255,255,0.3);
            position: relative;
            margin: 6px 0 4px;
        }
        .sensor-card .range-dot {
            width: 14px; height: 14px;
            background: #fff;
            border-radius: 50%;
            position: absolute;
            top: 50%; transform: translateY(-50%);
            box-shadow: 0 0 6px rgba(0,0,0,0.4);
            transition: left 0.6s ease;
        }

        .bg-suhu      { background: var(--card-suhu); }
        .bg-kelembaban{ background: var(--card-kelembaban); }
        .bg-udara     { background: var(--card-udara); }
        .bg-bising    { background: var(--card-bising); }

        /* Kartu status */
        .status-card {
            border: none;
            border-radius: 18px;
            padding: 1.2rem 1.6rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .status-card .status-icon {
            font-size: 2rem;
            opacity: 0.9;
        }
        .status-card .status-label {
            font-size: 0.85rem;
            opacity: 0.85;
            margin-bottom: 2px;
        }
        .status-card .status-value {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .status-card .status-detail {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 2px;
        }
        .bg-aman   { background: var(--card-aman); }
        .bg-bahaya { background: var(--card-bahaya); }

        /* Info node aktif */
        .node-info {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
            text-align: right;
            margin-bottom: 0.5rem;
        }
        .node-info span { color: #2ec4b6; font-weight: 600; }

        /* Last update */
        .last-update {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.4);
            margin-top: 0.3rem;
        }

        /* Sidebar active */
        .sb-sidenav-dark .nav-link.active {
            background-color: #2563eb !important;
            border-radius: 8px;
            font-weight: 600; /* Biar teks menu yang lagi aktif kelihatan lebih tebal */
    color: #ffffff !important;
        }
    </style>
</head>
<body class="sb-nav-fixed">

<!-- TOP NAV -->
<nav class="sb-topnav navbar navbar-expand navbar-dark">
    <!-- Tombol pindah ke sini (Paling Atas/Kiri) -->
    <button class="btn btn-link btn-sm ms-3 me-2" id="sidebarToggle">
        <i class="fas fa-bars" style="color:#fff"></i>
    </button>
    
    <!-- Teks Brand pindah setelah tombol -->
    <a class="navbar-brand fw-bold" href="index.php">Lab. Telkom Barat I/04</a>
</nav>

<div id="layoutSidenav">
    <!-- SIDEBAR -->
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav flex-column p-2">
                    <a class="nav-link active px-3 py-2 mb-1" href="index.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-house-chimney"></i></div>
                        Dashboard
                    </a>
                    <a class="nav-link px-3 py-2" href="history.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        History Chart
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- CONTENT -->
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 pt-4">

                <h4 class="fw-bold mb-0">Dashboard</h4>
                <p class="text-muted mb-3" style="font-size:1.25rem">Monitoring Kondisi Lingkungan Laboratorium</p>

                <!-- Info node aktif -->
                <div class="node-info" id="nodeInfo">Memuat data...</div>

                <!-- BARIS KARTU SENSOR -->
                <div class="row g-3 mb-3">
                    <!-- Suhu -->
                    <div class="col-md-6 col-xl-6">
                        <div class="sensor-card bg-suhu">
                            <div class="card-label"><i class="fas fa-temperature-half"></i> Suhu</div>
                            <div class="card-value" id="valSuhu">--</div>
                            <div class="range-bar">
                                <div class="range-dot" id="dotSuhu" style="left:50%"></div>
                            </div>
                            <div class="card-range"><span>18 °C</span><span>30 °C</span></div>
                        </div>
                    </div>
                    <!-- Kelembaban -->
                    <div class="col-md-6 col-xl-6">
                        <div class="sensor-card bg-kelembaban">
                            <div class="card-label"><i class="fas fa-droplet"></i> Kelembaban</div>
                            <div class="card-value" id="valKelembaban">--</div>
                            <div class="range-bar">
                                <div class="range-dot" id="dotKelembaban" style="left:50%"></div>
                            </div>
                            <div class="card-range"><span>40%</span><span>60%</span></div>
                        </div>
                    </div>
                    <!-- Kualitas Udara -->
                    <div class="col-md-6 col-xl-6">
                        <div class="sensor-card bg-udara">
                            <div class="card-label"><i class="fas fa-cloud"></i> Kualitas Udara (IAQ)</div>
                            <div class="card-value" id="valUdara">--</div>
                            <div class="range-bar">
                                <div class="range-dot" id="dotUdara" style="left:50%"></div>
                            </div>
                            <div class="card-range"><span>0</span><span>200</span></div>
                        </div>
                    </div>
                    <!-- Kebisingan -->
                    <div class="col-md-6 col-xl-6">
                        <div class="sensor-card bg-bising">
                            <div class="card-label"><i class="fas fa-volume-high"></i> Kebisingan</div>
                            <div class="card-value" id="valBising">--</div>
                            <div class="range-bar">
                                <div class="range-dot" id="dotBising" style="left:50%"></div>
                            </div>
                            <div class="card-range"><span>30 dB</span><span>100 dB</span></div>
                        </div>
                    </div>
                </div>

                <!-- KARTU STATUS LINGKUNGAN -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="status-card bg-aman" id="statusCard">
                            <div class="status-icon"><i class="fas fa-shield-halved" id="statusIcon"></i></div>
                            <div>
                                <div class="status-label">Status Lingkungan</div>
                                <div class="status-value" id="statusText">Memuat...</div>
                                <div class="status-detail" id="statusDetail"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="last-update" id="lastUpdate"></div>

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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../sbadmin5/js/scripts.js"></script>

<script>
// Fungsi posisi dot pada range bar (0–100%)
function dotPosition(val, min, max) {
    let pct = ((val - min) / (max - min)) * 100;
    pct = Math.min(100, Math.max(0, pct));
    // offset agar dot tidak keluar card (dot 14px, bar width ~100%)
    return `calc(${pct}% - 7px)`;
}

async function loadData() {
    try {
        const res  = await fetch('api/data.php');
        const data = await res.json();

        if (data.error) {
            document.getElementById('statusText').textContent = 'Tidak ada data';
            return;
        }

        // Isi nilai kartu
        document.getElementById('valSuhu').textContent       = data.suhu + ' °C';
        document.getElementById('valKelembaban').textContent  = data.kelembaban + ' %RH';
        document.getElementById('valUdara').textContent       = data.kualitas_udara;
        document.getElementById('valBising').textContent      = data.kebisingan + ' dB';

        // Posisi dot
        document.getElementById('dotSuhu').style.left        = dotPosition(data.suhu, 18, 30);
        document.getElementById('dotKelembaban').style.left  = dotPosition(data.kelembaban, 40, 60);
        document.getElementById('dotUdara').style.left       = dotPosition(data.kualitas_udara, 0, 200);
        document.getElementById('dotBising').style.left      = dotPosition(data.kebisingan, 30, 100);

        // Status lingkungan
        const card   = document.getElementById('statusCard');
        const icon   = document.getElementById('statusIcon');
        const text   = document.getElementById('statusText');
        const detail = document.getElementById('statusDetail');

        if (data.status_lingkungan === 'AMAN') {
            card.className  = 'status-card bg-aman';
            icon.className  = 'fas fa-shield-halved';
            text.textContent = 'AMAN';
            detail.textContent = 'Semua parameter dalam batas normal';
        } else {
            card.className  = 'status-card bg-bahaya';
            icon.className  = 'fas fa-triangle-exclamation';
            text.textContent = 'BAHAYA';
            detail.textContent = 'Parameter melebihi batas: ' + data.parameter_bahaya.join(', ');
        }

        // Info node aktif
        document.getElementById('nodeInfo').innerHTML =
            'Node aktif: <span>' + data.node_aktif + '/2</span>';

        // Last update
        document.getElementById('lastUpdate').textContent =
            'Data terakhir: ' + data.last_update;

    } catch (err) {
        console.error('Gagal memuat data:', err);
    }
}

// Muat saat halaman dibuka
loadData();

// Refresh otomatis tiap 5 menit (sesuai interval kirim ESP32)
setInterval(loadData, 5 * 60 * 1000);
</script>
</body>
</html>
