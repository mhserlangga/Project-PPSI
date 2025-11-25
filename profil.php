<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$query = mysqli_query($koneksi, "SELECT * FROM login WHERE username='$username'");
$data = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Akun</title>
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
            <h4 class="mb-0">Informasi Akun</h4>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($data['username']) ?>" readonly>
            </div>

            <div class="text-right">
                <a href="index.php" class="btn btn-custom">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
