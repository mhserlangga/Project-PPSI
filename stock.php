<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// Ambil data stok + supplier
$data = mysqli_query($koneksi, "
    SELECT stock.*, supplier.namasupplier 
    FROM stock 
    LEFT JOIN supplier ON stock.idsupplier = supplier.idsupplier
    WHERE stock.stock > 0 
    AND stock.kadaluwarsa > CURDATE()
    ORDER BY stock.namaobat ASC
");

if (!$data) {
    die('Query Error: ' . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Stock Obat - Apotek Rakyat</title>
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
                    <a class="nav-link active" href="stock.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-pills"></i></div>
                        Stock Obat
                    </a>
                    <a class="nav-link" href="kasir.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-cash-register"></i></div>
                        Kasir
                    </a>
                    <div class="sb-sidenav-menu-heading" style="color: #212529;">Data</div>
                    <a class="nav-link" href="masuk.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                        Obat Masuk
                    </a>
                    <a class="nav-link" href="habis.php" style="color: #212529;">
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
                    <a class="nav-link" href="laporanmasuk.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                        Laporan Obat Masuk
                    </a>
                    <a class="nav-link" href="laporanjual.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                        Laporan Obat Terjual
                    </a>
                    <div class="sb-sidenav-menu-heading" style="color: #212529;">Kelola</div>
                    <a class="nav-link" href="kelolauser.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                        Kelola User
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">
        <main class="container-fluid px-4">
            <h1 class="mt-4">Stock Obat</h1>
            <div class="card mb-4">
                <div class="card-body">
                    <table id="datatablesSimple" class="table table-bordered">
<thead>
    <tr>
        <th>Kode</th>
        <th>No Batch</th>
        <th>Nama</th>
        <th>Supplier</th>
        <th>Stock</th>
        <th>Tanggal Kadaluwarsa</th>
        <th>Harga</th>
        <th>Gambar</th>
        <th>Hapus & Edit</th>
    </tr>
</thead>
<tbody>
<?php
while ($d = mysqli_fetch_array($data)) {

$gambar = !empty($d['gambar']) ? "uploads/" . $d['gambar'] : "img/noimage.png";

?>
    <tr>
        <td><?= $d['kodeobat']; ?></td>
        <td><?= $d['batchnumber']; ?></td>
        <td><?= $d['namaobat']; ?></td>
        <td><?= $d['namasupplier'] ?? '-'; ?></td>
        <td><?= $d['stock']; ?></td>
        <td><?= $d['kadaluwarsa']; ?></td>
        <td><?= number_format($d['harga'], 0, ',', '.'); ?></td>
        <td>
            <img src="<?= $gambar ?>" width="60" style="border-radius:5px;">
        </td>
        <td>
            <a href="hapus.php?id=<?= $d['kodeobat']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')" class="btn btn-danger btn-sm">
                <i class="fas fa-trash-alt"></i>
            </a>
            <a href="edit.php?kodeobat=<?= $d['kodeobat']; ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i>
            </a>
        </td>
    </tr>
<?php } ?>
</tbody>
                    </table>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dataTable = new simpleDatatables.DataTable("#datatablesSimple");
    });
</script>
</body>
</html>
