<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: logout.php");
    exit;
}

include 'koneksi.php';

// Tambah user
if (isset($_POST['tambah'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // plaintext (bisa diganti hash di masa depan)
    $role = $_POST['role'];

    $stmt = $koneksi->prepare("INSERT INTO login (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    $stmt->close();
}

// Edit user
if (isset($_POST['edit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $old_username = $_POST['old_username'];

    $stmt = $koneksi->prepare("UPDATE login SET username=?, password=?, role=? WHERE username=?");
    $stmt->bind_param("ssss", $username, $password, $role, $old_username);
    $stmt->execute();
    $stmt->close();
}

// Hapus user
if (isset($_GET['hapus'])) {
    $username = $_GET['hapus'];
    $stmt = $koneksi->prepare("DELETE FROM login WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - Apotek Rakyat</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<!-- NAVBAR -->
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

<!-- SIDEBAR -->
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
                    <a class="nav-link active" href="kelolauser.php" style="color: #212529;">
                        <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                        Kelola User
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div id="layoutSidenav_content">
        <main class="container-fluid px-4">
            <h1 class="mt-4">Kelola User</h1>
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah User</button>

            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Hapus & Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $result = $koneksi->query("SELECT * FROM login");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$no}</td>
                            <td>{$row['username']}</td>
                            <td>{$row['password']}</td>
                            <td>{$row['role']}</td>
                            <td>
                                <a href='kelolauser.php?hapus={$row['username']}' 
                                onclick=\"return confirm('Yakin ingin menghapus user ini?')\" 
                                class='btn btn-danger btn-sm'>
                                <i class='fas fa-trash-alt'></i>
                                </a>
                                <button class='btn btn-warning btn-sm' 
                                        data-bs-toggle='modal' 
                                        data-bs-target='#modalEdit{$row['username']}'>
                                    <i class='fas fa-edit'></i>
                                </button>
                            </td>
                        </tr>";

                        // Modal Edit
                        echo "
                        <div class='modal fade' id='modalEdit{$row['username']}' tabindex='-1'>
                          <div class='modal-dialog'>
                            <form method='post'>
                              <div class='modal-content'>
                                <div class='modal-header'><h5>Edit User</h5></div>
                                <div class='modal-body'>
                                  <input type='hidden' name='old_username' value='{$row['username']}'>
                                  <div class='mb-2'><label>Username</label><input name='username' value='{$row['username']}' class='form-control' required></div>
                                  <div class='mb-2'><label>Password</label><input name='password' value='{$row['password']}' class='form-control' required></div>
                                  <div class='mb-2'><label>Role</label>
                                    <select name='role' class='form-control'>
                                      <option value='admin' " . ($row['role'] == 'admin' ? 'selected' : '') . ">Admin</option>
                                      <option value='kasir' " . ($row['role'] == 'kasir' ? 'selected' : '') . ">Kasir</option>
                                    </select>
                                  </div>
                                </div>
                                <div class='modal-footer'>
                                  <button class='btn btn-success' name='edit'>Simpan</button>
                                  <button class='btn btn-secondary' data-bs-dismiss='modal'>Batal</button>
                                </div>
                              </div>
                            </form>
                          </div>
                        </div>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </main>

        <!-- FOOTER -->
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
        <div class="d-flex justify-content-end small">
          <div class="text-muted">&copy; Apotek Rakyat 2025</div>
        </div>
            </div>
        </footer>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <form method="post">
            <div class="modal-content">
                <div class="modal-header"><h5>Tambah User</h5></div>
                <div class="modal-body">
                    <div class="mb-2"><label>Username</label><input name="username" class="form-control" required></div>
                    <div class="mb-2"><label>Password</label><input name="password" class="form-control" required></div>
                    <div class="mb-2"><label>Role</label>
                        <select name="role" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success mb-3" name="tambah">Simpan</button>
                    <button class="btn btn-secondary mb-3" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
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