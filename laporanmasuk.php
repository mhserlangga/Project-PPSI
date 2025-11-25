<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// Ambil bulan dari query string, default bulan ini
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$displayBulan = date('F Y', strtotime($bulan . '-01'));

$data = mysqli_query($koneksi, "
    SELECT * FROM stock 
    WHERE DATE_FORMAT(tanggal_input, '%Y-%m') = '$bulan'
    ORDER BY tanggal_input ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Pembukuan Obat Masuk - Apotek Rakyat</title>
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
        <a class="nav-link dropdown-toggle text-white" href="#" id="userMenu" data-bs-toggle="dropdown">
          <i class="fas fa-user fa-fw"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
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
                    <a class="nav-link active" href="masuk.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                        Obat Masuk
                    </a>
                    <a class="nav-link active" href="habis.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        Obat Habis
                    </a>
                    <a class="nav-link active" href="kadaluwarsa.php" style="color: #212529;">
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
    <main class="container-fluid px-4">
      <h1 class="mt-4">Pencatatan Obat Masuk</h1>
      
      <div class="row mb-3">
        <div class="col-md-4">
          <form method="get" class="d-flex">
            <input type="month" name="bulan" class="form-control me-2" value="<?= $bulan ?>">
            <button type="submit" class="btn btn-success"><i class="fas fa-search"></i> Tampilkan</button>
          </form>
        </div>
        <div class="col-md-8 text-md-end mt-2 mt-md-0">
          <a href="export_pdf.php?bulan=<?= $bulan ?>" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
          </a>
        </div>
      </div>

      <h5>Data Bulan: <strong><?= $displayBulan ?></strong></h5>
      <div class="card mb-4">
        <div class="card-body">
          <table id="datatablesSimple" class="table table-bordered">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal Masuk</th>
                <th>Kode Obat</th>
                <th>No Batch</th>
                <th>Nama Obat</th>
                <th>Stock</th>
                <th>Kadaluwarsa</th>
                <th>Harga</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $no = 1;
                if (mysqli_num_rows($data) > 0) {
                    while ($d = mysqli_fetch_assoc($data)) { ?>
                      <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $d['tanggal_input'] ?></td>
                        <td><?= htmlspecialchars($d['kodeobat']) ?></td>
                        <td><?= htmlspecialchars($d['batchnumber']) ?></td>
                        <td><?= htmlspecialchars($d['namaobat']) ?></td>
                        <td><?= $d['stock'] ?></td>
                        <td><?= htmlspecialchars($d['kadaluwarsa']) ?></td>
                        <td class="text-end"><?= number_format($d['harga'],0,',','.') ?></td>
                      </tr>
                  <?php }
                } else { ?>
                  <tr>
                    <td colspan="7" class="text-center py-3">Tidak ada data untuk bulan ini.</td>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="js/scripts.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    new simpleDatatables.DataTable("#datatablesSimple");
  });
</script>
</body>
</html>