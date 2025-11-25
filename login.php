<?php
session_start(); // Mulai session kalau mau pakai session
$koneksi = mysqli_connect("127.0.0.1", "root", "", "apotek", 8111);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Cek login
if (isset($_SESSION['username'], $_SESSION['role'])) {
    if ($_SESSION['role'] === 'kasir') {
        header("Location: kasir.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // ambil juga kolom role
    $sql = "SELECT username, password, role FROM login 
            WHERE username='$username' 
              AND password='$password' 
            LIMIT 1";
    $res = mysqli_query($koneksi, $sql);

    if (mysqli_num_rows($res) === 1) {
        $user = mysqli_fetch_assoc($res);
        // (jika nanti pakai password_hash, ganti cek-nya ke password_verify)
        if ($password === $user['password']) {
            // simpan session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            // redirect sesuai role
            if ($user['role'] === 'kasir') {
                header("Location: kasir.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Password salah";
        }
    } else {
        $error = "Username tidak ditemukan";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login - Apotek Rakyat</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #28d17c;
        }

    .btn-custom {
        background-color: #00796b;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
    }

    .btn-custom:hover {
        background-color: #1ea865;
    }
    </style>
</head>
<body>
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header text-center">
                                    <img src="img/logo.png" alt="Logo" style="max-height: 80px;" class="mb-2"> 
                                    <h3 class="text-center font-weight-light my-2">Apotek Rakyat Sehat Farma</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($error)) : ?>
                                        <div class="alert alert-danger text-center">
                                            <?= $error ?>
                                        </div>
                                    <?php endif; ?>
                                    <form method="post" autocomplete="off">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="username" id="InputUsername" type="text" placeholder="Username" required 
                                                autocomplete="username" />
                                            <label for="InputUsername">Username</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="password" id="inputPassword" type="password" placeholder="Password" 
                                                required autocomplete="password" />
                                            <label for="inputPassword">Password</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <button class="btn btn-custom" name="login" type="submit">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
