<?php
session_start();
$koneksi = mysqli_connect("127.0.0.1", "root", "", "apotek", 8111);
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (isset($_POST['simpan'])) {
    $kodeobat     = mysqli_real_escape_string($koneksi, $_POST['kodeobat']);
    $batchnumber  = mysqli_real_escape_string($koneksi, $_POST['batchnumber']);
    $namaobat     = mysqli_real_escape_string($koneksi, $_POST['namaobat']);
    $stock        = mysqli_real_escape_string($koneksi, $_POST['stock']);
    $kadaluwarsa  = mysqli_real_escape_string($koneksi, $_POST['kadaluwarsa']);
    $harga        = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $idsupplier   = mysqli_real_escape_string($koneksi, $_POST['idsupplier']); // 🔹 ambil dari form
    
    $tanggal_input = date("Y-m-d");

    // Tambahkan idsupplier di query
    $query = "INSERT INTO stock 
              (kodeobat, idsupplier, batchnumber, namaobat, stock, kadaluwarsa, harga, tanggal_input)
              VALUES
              ('$kodeobat', '$idsupplier', '$batchnumber', '$namaobat', '$stock', '$kadaluwarsa', '$harga', '$tanggal_input')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: masuk.php?pesan=sukses");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}
?>
