<?php
include 'koneksi.php';
$id = $_GET['id'];
mysqli_query($koneksi, "DELETE FROM stock WHERE kodeobat='$id'");
header("Location: stock.php");
?>
