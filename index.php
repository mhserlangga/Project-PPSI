<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// Total stok obat
$q_stock = mysqli_query($koneksi, "SELECT COUNT(*) as jenis_obat FROM stock");
if (!$q_stock) {
    die("Query stok gagal: " . mysqli_error($koneksi));
}
$jenis_obat = mysqli_fetch_assoc($q_stock);

// Obat habis
$q_habis = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah_habis FROM stock WHERE stock = 0");
if (!$q_habis) {
    die("Query habis gagal: " . mysqli_error($koneksi));
}
$habis = mysqli_fetch_assoc($q_habis);

// Obat kadaluarsa
$q_kadaluarsa = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah_kadaluarsa FROM stock WHERE kadaluwarsa <= CURDATE()");
if (!$q_kadaluarsa) {
    die("Query kadaluarsa gagal: " . mysqli_error($koneksi));
}
$kadaluarsa = mysqli_fetch_assoc($q_kadaluarsa);


// Pendapatan
$q_pendapatan = mysqli_query($koneksi, "
    SELECT SUM(total_harga) AS total_pendapatan 
    FROM penjualan
    WHERE DATE(tanggal) = CURDATE()
");

$d_pendapatan = mysqli_fetch_assoc($q_pendapatan);
$pendapatan = $d_pendapatan['total_pendapatan'] ?? 0;


// Grafik Penjualan Per Bulan
$q_bulanan = mysqli_query($koneksi, "
    SELECT DATE_FORMAT(tanggal, '%M') AS bulan, SUM(total_harga) AS total
    FROM penjualan
    WHERE YEAR(tanggal) = YEAR(CURDATE())
    GROUP BY MONTH(tanggal)
    ORDER BY MONTH(tanggal)
");

$label_bulan = [];
$data_bulan = [];

while ($row = mysqli_fetch_assoc($q_bulanan)) {
    $label_bulan[] = $row['bulan'];
    $data_bulan[] = $row['total'];
}


// obat paling laris tiap bulan
$q_bulanan = mysqli_query($koneksi, "
    SELECT 
        DATE_FORMAT(penjualan.tanggal, '%M %Y') AS bulan,
        MONTH(penjualan.tanggal) AS bulan_angka,
        YEAR(penjualan.tanggal) AS tahun,
        stock.namaobat,
        SUM(detail_penjualan.jumlah) AS total_jual
    FROM detail_penjualan
    JOIN penjualan ON detail_penjualan.id_penjualan = penjualan.id_penjualan
    JOIN stock ON detail_penjualan.kodeobat = stock.kodeobat
    GROUP BY tahun, bulan_angka, stock.namaobat
    ORDER BY tahun DESC, bulan_angka DESC, total_jual DESC
");

$terlaris = [];
while ($row = mysqli_fetch_assoc($q_bulanan)) {
    $key = $row['tahun'].'-'.$row['bulan_angka'];

    // jika bulan ini belum punya data → set sebagai yang paling laris
    if (!isset($terlaris[$key])) {
        $terlaris[$key] = [
            'bulan' => $row['bulan'],
            'namaobat' => $row['namaobat'],
            'total_jual' => $row['total_jual']
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Beranda - Apotek Rakyat</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand navbar-light" style="background-color: #28d17c;">
    <button class="btn btn-link btn-sm text-white me-2" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand text-white" href="index.php">Apotek Rakyat Sehat Farma</a>
    <ul class="navbar-nav ms-auto">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-white" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user fa-fw"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
        </ul>
    </li>
</ul>
</nav>

<div id="layoutSidenav">
<div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion" id="sidenavAccordion" style="background-color: #d4edda; min-height: 100vh;">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading" style="color: #212529;">Menu Utama</div>
                    <a class="nav-link" href="index.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                        Beranda
                    </a>
                    <a class="nav-link" href="stock.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-pills"></i></div>
                        Stock Obat
                    </a>
                    <a class="nav-link active" href="kasir.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-cash-register"></i></div>
                        Kasir
                    </a>
                    <div class="sb-sidenav-menu-heading" style="color: #212529;">Data</div>
                    <a class="nav-link" href="masuk.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                        Obat Masuk
                    </a>
                    <a class="nav-link active" href="habis.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        Obat Habis
                    </a>
                    <a class="nav-link" href="kadaluwarsa.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-calendar-times"></i></div>
                        Obat Kadaluwarsa
                    </a>
                    <a class="nav-link active" href="supplier.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-truck"></i></div>
                        Supplier
                    </a>
                    <div class="sb-sidenav-menu-heading" style="color: #212529;">Pencatatan</div>
                    <a class="nav-link active" href="laporanmasuk.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                        Laporan Obat Masuk
                    </a>
                    <a class="nav-link active" href="laporanjual.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                        Laporan Obat Terjual
                    </a>
                    <div class="sb-sidenav-menu-heading" style="color: #212529;">Kelola</div>
                    <a class="nav-link active" href="kelolauser.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                        Kelola User
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">
        <main class="container-fluid px-4 mt-4">
            <h4 class="mb-4">Selamat datang di Apotek Rakyat Sehat Farma</h4>
            <div class="row">
                <!-- Total Stok Obat -->
                <div class="col-md-3">
                    <a href="stock.php" style="text-decoration:none;">
                        <div class="card text-white bg-primary mb-3 shadow rounded-2xl">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-white">Obat Tersedia</h5>
                                        <h3 class="text-white"><?= $jenis_obat['jenis_obat'] ?? 0 ?></h3>
                                    </div>
                                    <i class="fas fa-pills fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>


                <!-- Obat Habis -->
                <div class="col-md-3">
                    <a href="habis.php" style="text-decoration:none;">
                        <div class="card text-white bg-danger mb-3 shadow rounded-2xl">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-white">Obat Habis</h5>
                                        <h3 class="text-white"><?= $habis['jumlah_habis'] ?? 0 ?></h3>
                                    </div>
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Obat Kadaluarsa -->
                <div class="col-md-3">
                    <a href="kadaluwarsa.php" style="text-decoration:none;">
                        <div class="card text-white bg-warning mb-3 shadow rounded-2xl">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-white">Obat Kadaluarsa</h5>
                                        <h3 class="text-white"><?= $kadaluarsa['jumlah_kadaluarsa'] ?? 0 ?></h3>
                                    </div>
                                    <i class="fas fa-calendar-times fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>


                <!-- Pendapatan -->
                <div class="col-md-3">
                    <a href="laporanjual.php" style="text-decoration:none;">
                        <div class="card text-white bg-success mb-3 shadow rounded-2xl">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-white">Pendapatan</h5>
                                        <h3 class="text-white">Rp <?= number_format($pendapatan, 0, ',', '.') ?></h3>
                                    </div>
                                    <i class="fas fa-money-bill-wave fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

            </div>

<div class="row mt-4">

<div class="col-md-6">
    <div class="card shadow">

        <!-- CARD HEADER SAMA KAYA TABEL OBAT TERLARIS -->
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Grafik Penjualan Per Bulan</h5>

            <!-- Tombol Download PDF -->
            <button id="downloadGrafik" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-download"></i>
            </button>
        </div>

        <div class="card-body">
            <canvas id="chartPenjualan"></canvas>
        </div>

    </div>
</div>


    <div class="col-md-6">
        <div class="card shadow">
<div class="card-header bg-primary text-white">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Obat Terlaris Setiap Bulannya</h5>

<a href="export_pdf.php?type=terlaris" class="btn btn-primary btn-sm">
    <i class="fa-solid fa-download"></i>
</a>
    </div>
</div>
            </div>
            <div class="card-body" style="max-height: 350px; overflow-y:auto;">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Obat Paling Laris</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terlaris as $data) { ?>
                        <tr>
                            <td><?= $data['bulan'] ?></td>
                            <td><?= $data['namaobat'] ?></td>
                            <td><?= $data['total_jual'] ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
               <div class="d-flex justify-content-end small">
                    <div class="text-muted">&copy; Apotek Rakyat 2025</div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
// Grafik Penjualan Per Bulan
new Chart(document.getElementById('chartPenjualan'), {
    type: 'line',
    data: {
        labels: <?= json_encode($label_bulan); ?>,
        datasets: [{
            label: 'Total Penjualan (Rp)',
            data: <?= json_encode($data_bulan); ?>,
            borderWidth: 2
        }]
    }
});
</script>

<script>
// Download Grafik sebagai PDF
document.getElementById("downloadGrafik").addEventListener("click", async function () {
    const { jsPDF } = window.jspdf;

    // Ambil elemen canvas
    const canvas = document.getElementById("chartPenjualan");

    // Konversi chart ke gambar
    const canvasImage = canvas.toDataURL("image/png", 1.0);

    // Buat dokumen PDF
    const pdf = new jsPDF("landscape");

    // Masukkan gambar ke PDF
    pdf.addImage(canvasImage, "PNG", 10, 10, 270, 150);

    // Download
    pdf.save("grafik-penjualan.pdf");
});
</script>

</body>
</html>
