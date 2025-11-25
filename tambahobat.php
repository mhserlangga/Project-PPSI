<?php
include 'koneksi.php'; // koneksi ke database

if (isset($_POST['simpan'])) {
    $kode = $_POST['kodeobat'];
    $batchnumber = $_POST['batchnumber'];
    $nama = $_POST['namaobat'];
    $stock = $_POST['stock'];
    $kadaluwarsa = $_POST['kadaluwarsa'];
    $harga = $_POST['harga'];
    $idsupplier = $_POST['idsupplier']; 
    $tanggal_input = date("Y-m-d");

    // Proses upload gambar
    $namaFile = $_FILES['gambar']['name'];
    $tmpName = $_FILES['gambar']['tmp_name'];

    // nama file baru supaya unik
    $namaBaru = time() . "_" . $namaFile;

    $folder = "uploads/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    move_uploaded_file($tmpName, $folder . $namaBaru);

    // Simpan data ke tabel stock
    $query = "INSERT INTO stock (kodeobat, batchnumber, namaobat, stock, kadaluwarsa, harga, tanggal_input, idsupplier, gambar)
              VALUES ('$kode', '$batchnumber', '$nama', '$stock', '$kadaluwarsa', '$harga', '$tanggal_input', '$idsupplier', '$namaBaru')";

    mysqli_query($koneksi, $query);

    header("Location: masuk.php?pesan=sukses");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Obat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .btn-custom {
            background-color: #28d17c;
            color: white;
        }

        .btn-custom:hover {
            background-color: #22ba6f;
            color: white;
        }

        .bg-custom {
            background-color: #28d17c;
            color: white;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg border-0 rounded-lg">
        <div class="card-header bg-custom">
            <h4 class="mb-0">Tambah Obat Baru</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="tambahobat.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="kodeobat">Kode Obat</label>
                    <input type="text" name="kodeobat" id="kodeobat" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="batchnumber">No Batch</label>
                    <input type="text" name="batchnumber" id="batchnumber" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="namaobat">Nama Obat</label>
                    <input type="text" name="namaobat" id="namaobat" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" name="stock" id="stock" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="kadaluwarsa">Tanggal Kadaluwarsa</label>
                    <input type="date" name="kadaluwarsa" id="kadaluwarsa" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" name="harga" id="harga" class="form-control" required>
                </div>
                <!-- Tambahan: Pilihan Supplier -->
                <div class="form-group">
                    <label for="idsupplier">Supplier</label>
                    <select name="idsupplier" id="idsupplier" class="form-control" required>
                        <option value="">-- Pilih Supplier --</option>
                        <?php
                        // ambil data supplier dari database
                        $supplier = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY namasupplier ASC");
                        while ($s = mysqli_fetch_assoc($supplier)) {
                            echo "<option value='".$s['idsupplier']."'>".$s['namasupplier']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gambar">Gambar Obat</label>
                    <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" required>
                </div>

                <div class="text-right">
                    <a href="masuk.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="simpan" class="btn btn-custom">
                        <i class="fa fa-save"></i> Simpan Obat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FontAwesome (optional for icons) -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

</body>
</html>
