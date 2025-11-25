<?php
$koneksi = mysqli_connect("127.0.0.1", "root", "", "apotek", 8111);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
