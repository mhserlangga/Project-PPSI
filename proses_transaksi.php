<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// **Tidak ada echo atau spasi sebelum ini**

// Autoload Dompdf (sama persis seperti di pembukuan.php)
require __DIR__ . '/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include 'koneksi.php';

// Ambil data dari form
$kodeObatArr = $_POST['obat']        ?? [];
$jumlahArr   = $_POST['jumlah']      ?? [];
$hargaArr    = $_POST['harga_satuan']?? [];
$subtotalArr = $_POST['subtotal']    ?? [];
$totalHarga  = (int) ($_POST['total_harga'] ?? 0);
$uangBayar   = (int) ($_POST['uang_bayar']  ?? 0);
$kembalian   = $uangBayar - $totalHarga;

// Simpan ke penjualan
$hasil_penjualan = mysqli_query($koneksi, "
    INSERT INTO penjualan (tanggal, total_harga, uang_bayar, kembalian)
    VALUES (NOW(), $totalHarga, $uangBayar, $kembalian)
");

if (!$hasil_penjualan) {
    die("Gagal simpan penjualan: " . mysqli_error($koneksi));
}

$id_penjualan = mysqli_insert_id($koneksi);


// Simpan detail untuk tiap obat
for ($i = 0; $i < count($kodeObatArr); $i++) {
  $kd  = mysqli_real_escape_string($koneksi, $kodeObatArr[$i]);
  $jml = (int)$jumlahArr[$i];
  $htg = (int)$hargaArr[$i];
  $sub = (int)$subtotalArr[$i];

  // Simpan ke tabel transaksi
$hasil_detail = mysqli_query($koneksi, "
    INSERT INTO detail_penjualan (id_penjualan, kodeobat, jumlah, harga, subtotal)
    VALUES ($id_penjualan, '$kd', $jml, $htg, $sub)
");

if (!$hasil_detail) {
    echo "Gagal simpan detail: " . mysqli_error($koneksi) . "<br>";
}


  // Kurangi stok obat
  mysqli_query($koneksi, "
      UPDATE stock 
      SET stock = stock - $jml 
      WHERE kodeobat = '$kd'
  ");
}

// Bangun HTML struk
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: monospace;
      font-size: 11px;
      text-align: center;
    }
    .header, .footer {
      margin-bottom: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 5px;
    }
    th, td {
      text-align: left;
      padding: 2px 0;
    }
    th {
      border-bottom: 1px dashed #000;
    }
    tfoot td {
      padding-top: 5px;
    }
    .text-right { text-align: right; }
    .text-left  { text-align: left; }
    .text-center { text-align: center; }
    .line {
      border-top: 1px dashed #000;
      margin: 5px 0;
    }
  </style>
</head>
<body>
  <div class="header">
    <strong>Apotek Rakyat Sehat Farma</strong><br>
    Jl. Ceger Raya No.23<br>
    Tangerang Selatan<br>
    Telp: 021-33972529<br>
  </div>

  <div class="text-center">
    <?= date('d-m-Y H:i') ?><br>
    Kasir: <?= htmlspecialchars($_SESSION['username']) ?>
  </div>

  <div class="line"></div>

  <table>
    <thead>
      <tr>
        <th>Obat</th>
        <th class="text-center">Qty</th>
        <th class="text-right">Harga</th>
        <th class="text-right">Sub</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($kodeObatArr as $i => $kd): 
        $res = mysqli_fetch_assoc(mysqli_query(
          $koneksi, "SELECT namaobat FROM stock WHERE kodeobat='" . mysqli_real_escape_string($koneksi, $kd) . "'"
        ));
        $nama = $res['namaobat'] ?? '-';
        $jml  = (int)$jumlahArr[$i];
        $htg  = (int)$hargaArr[$i];
        $sub  = (int)$subtotalArr[$i];
      ?>
      <tr>
        <td><?= htmlspecialchars($nama) ?></td>
        <td class="text-center"><?= $jml ?></td>
        <td class="text-right"><?= number_format($htg,0,',','.') ?></td>
        <td class="text-right"><?= number_format($sub,0,',','.') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="line"></div>
  <table>
    <tr>
      <td class="text-left">Total</td>
      <td class="text-right"><?= number_format($totalHarga,0,',','.') ?></td>
    </tr>
    <tr>
      <td class="text-left">Bayar</td>
      <td class="text-right"><?= number_format($uangBayar,0,',','.') ?></td>
    </tr>
    <tr>
      <td class="text-left">Kembali</td>
      <td class="text-right"><?= number_format($kembalian,0,',','.') ?></td>
    </tr>
  </table>

  <div class="line"></div>
  <div class="footer">
    <em>Terima kasih atas kepercayaan Anda.</em><br>
    <strong>Semoga lekas sembuh!</strong>
  </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Bersihkan buffer sebelum render
if (ob_get_length()) {
    ob_end_clean();
}

// Setup Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$customPaper = [0, 0, 226.77, 841.89]; // 58mm x panjang dinamis (sekitar 29.7cm)
$dompdf->setPaper($customPaper);
$dompdf->render();

// Kirim header dan output PDF inline
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="struk_'. date('Ymd_His') .'.pdf"');
echo $dompdf->output();
exit;
