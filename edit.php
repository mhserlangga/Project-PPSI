<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// Ambil kode obat dari URL
$kodeobat = $_GET['kodeobat'];

// Ambil data obat + supplier
$query = "
    SELECT stock.*, supplier.namasupplier 
    FROM stock 
    LEFT JOIN supplier ON stock.idsupplier = supplier.idsupplier 
    WHERE stock.kodeobat = '$kodeobat'
";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query error: " . mysqli_error($koneksi));
}

$data = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {

    $kode = $_POST['kode'];
    $batchnumber = $_POST['batchnumber'];
    $nama = $_POST['nama'];
    $id_supplier = $_POST['supplier'];
    
    $tambah_stok = $_POST['stock'];
    $stok_lama = $data['stock'];
    $stock = $stok_lama + $tambah_stok;

    $kadaluwarsa = $_POST['kadaluwarsa'];
    $harga = $_POST['harga'];

    // === Upload Gambar ===
    $gambar_lama = $data['gambar'];
    $gambar_baru = $gambar_lama; // default tidak berubah

    if (!empty($_FILES['gambar']['name'])) {

        $nama_file = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        $folder = "uploads/";

        // hapus file lama jika ada
        if ($gambar_lama != "" && file_exists("uploads/" . $gambar_lama)) {
            unlink("uploads/" . $gambar_lama);
        }

        $gambar_baru = uniqid() . "_" . $nama_file;
        move_uploaded_file($tmp, $folder . $gambar_baru);
    }

    // Update data
    $update = mysqli_query($koneksi, "UPDATE stock SET 
        kodeobat = '$kode',
        batchnumber = '$batchnumber',
        namaobat = '$nama',
        idsupplier = '$id_supplier',
        stock = '$stock',
        kadaluwarsa = '$kadaluwarsa',
        harga = '$harga',
        gambar = '$gambar_baru'
        WHERE kodeobat = '$kodeobat'
    ");

    if ($update) {
        echo "<script>alert('Berhasil update data obat'); window.location='stock.php';</script>";
    } else {
        echo "<script>alert('Gagal update: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Obat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .btn-custom { background-color: #28d17c; color: white; }
        .btn-custom:hover { background-color: #22ba6f; color: white; }
        .bg-custom { background-color: #28d17c; color: white; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-custom text-white">
            <h4 class="mb-0">Edit Data Obat</h4>
        </div>

        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Kode Obat</label>
                    <input type="text" class="form-control" name="kode" value="<?= $data['kodeobat']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">No Batch</label>
                    <input type="text" class="form-control" name="batchnumber" value="<?= $data['batchnumber']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Obat</label>
                    <input type="text" class="form-control" name="nama" value="<?= $data['namaobat']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <select class="form-control" name="supplier" required>
                        <option value="">-- Pilih Supplier --</option>
                        <?php
                        $supplier_query = mysqli_query($koneksi, "SELECT * FROM supplier");
                        while ($s = mysqli_fetch_assoc($supplier_query)) {
                            $selected = ($s['idsupplier'] == $data['idsupplier']) ? 'selected' : '';
                            echo "<option value='{$s['idsupplier']}' $selected>{$s['namasupplier']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tambah Jumlah Stok</label>
                    <input type="number" class="form-control" name="stock" placeholder="Masukkan jumlah stok baru" required>
                    <small class="text-muted">Stok saat ini: <?= $data['stock']; ?></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Kadaluwarsa</label>
                    <input type="date" class="form-control" name="kadaluwarsa" value="<?= $data['kadaluwarsa']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" class="form-control" name="harga" value="<?= $data['harga']; ?>" required>
                </div>

                <!-- Gambar Obat -->
                <div class="mb-3">
                    <label class="form-label">Gambar Obat</label><br>

                    <?php if ($data['gambar'] != "") { ?>
                        <img src="uploads/<?= $data['gambar']; ?>" 
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                        <br><small class="text-muted">Gambar saat ini</small><br><br>
                    <?php } ?>

                    <input type="file" name="gambar" class="form-control">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar</small>
                </div>

                <button type="submit" name="update" class="btn btn-success">Simpan Perubahan</button>
                <a href="stock.php" class="btn btn-secondary">Kembali</a>

            </form>
        </div>
    </div>
</div>

</body>
</html>
