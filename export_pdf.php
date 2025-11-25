<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include 'koneksi.php';

// Ambil parameter
$bulan = $_GET['bulan'] ?? date('Y-m');
$type  = $_GET['type']  ?? 'masuk';

// Set judul, query, kolom tabel
if ($type === 'jual') {

    $judul = "Laporan Penjualan Obat";

    $sql = "
        SELECT 
            p.tanggal,
            d.kodeobat,
            s.batchnumber,
            s.namaobat,
            d.jumlah,
            d.subtotal
        FROM detail_penjualan d
        JOIN penjualan p ON d.id_penjualan = p.id_penjualan
        LEFT JOIN stock s ON d.kodeobat = s.kodeobat
        WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = '$bulan'
        ORDER BY p.tanggal ASC, d.id_detail ASC
    ";

    $cols = ['No','Tanggal','Kode','No Batch','Nama Obat','Jumlah','Subtotal'];

} elseif ($type === 'terlaris') {

    $judul = "Laporan Obat Terlaris";
$tahun = substr($bulan, 0, 4);
$sql = "
    SELECT 
        bulan,
        namaobat,
        total_jual
    FROM (
        SELECT 
            DATE_FORMAT(p.tanggal, '%M %Y') AS bulan,
            s.namaobat,
            SUM(d.jumlah) AS total_jual,
            ROW_NUMBER() OVER (
                PARTITION BY YEAR(p.tanggal), MONTH(p.tanggal)
                ORDER BY SUM(d.jumlah) DESC
            ) AS rn
        FROM detail_penjualan d
        JOIN penjualan p ON d.id_penjualan = p.id_penjualan
        LEFT JOIN stock s ON d.kodeobat = s.kodeobat
        WHERE YEAR(p.tanggal) = '$tahun'
        GROUP BY YEAR(p.tanggal), MONTH(p.tanggal), s.namaobat
    ) AS x
    WHERE rn = 1
    ORDER BY STR_TO_DATE(bulan, '%M %Y') ASC
";

    $cols = ['No','Bulan','Nama Obat','Jumlah Terjual'];

} else {

    $judul = "Laporan Obat Masuk";

    $sql = "
        SELECT 
            tanggal_input AS tanggal,
            kodeobat,
            batchnumber,
            namaobat,
            stock AS jumlah,
            kadaluwarsa,
            harga
        FROM stock
        WHERE DATE_FORMAT(tanggal_input, '%Y-%m') = '$bulan'
        ORDER BY tanggal_input ASC
    ";

    $cols = ['No','Tanggal','Kode','No Batch','Nama Obat','Jumlah','Kadaluwarsa','Harga'];
}

// Jalankan query
$result = mysqli_query($koneksi, $sql);

// Untuk hitung total subtotal (jual)
$totalSubtotal = 0;

// Mulai output buffer HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $judul ?> <?= date('F Y', strtotime("$bulan-01")) ?></title>

<style>
body {
    font-family: sans-serif;
    font-size: 12px;
}
h2, p { text-align: center; margin: 0; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #333;
    padding: 6px;
}
th { background: #f0f0f0; }
.text-center { text-align: center; }
.text-end { text-align: right; }
.total-row { font-weight: bold; background: #fafafa; }
</style>
</head>

<body>

<h2><?= $judul ?></h2>
<p>Bulan: <strong><?= date('F Y', strtotime("$bulan-01")) ?></strong></p>

<table>
<thead>
<tr>
<?php foreach ($cols as $c) echo "<th>$c</th>"; ?>
</tr>
</thead>

<tbody>
<?php
$no = 1;

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        echo "<tr>";
        echo "<td class='text-center'>{$no}</td>";

        if ($type === 'jual') {

            $totalSubtotal += $row['subtotal'];

            echo "<td>{$row['tanggal']}</td>";
            echo "<td>{$row['kodeobat']}</td>";
            echo "<td>{$row['batchnumber']}</td>";
            echo "<td>{$row['namaobat']}</td>";
            echo "<td class='text-center'>{$row['jumlah']}</td>";
            echo "<td class='text-end'>".number_format($row['subtotal'],0,',','.')."</td>";

        } elseif ($type === 'terlaris') {
            echo "<td>{$row['bulan']}</td>";
            echo "<td>".htmlspecialchars($row['namaobat'])."</td>";
            echo "<td class='text-center'>{$row['total_jual']}</td>";

        } else {

            echo "<td>{$row['tanggal']}</td>";
            echo "<td>{$row['kodeobat']}</td>";
            echo "<td>{$row['batchnumber']}</td>";
            echo "<td>{$row['namaobat']}</td>";
            echo "<td class='text-center'>{$row['jumlah']}</td>";
            echo "<td>{$row['kadaluwarsa']}</td>";
            echo "<td class='text-end'>".number_format($row['harga'],0,',','.')."</td>";

        }

        echo "</tr>";
        $no++;
    }

    // Tambah total jika laporan penjualan
    if ($type === 'jual') {
        echo "
        <tr class='total-row'>
            <td colspan='6' class='text-end'>Total Pendapatan</td>
            <td class='text-end'>".number_format($totalSubtotal,0,',','.')."</td>
        </tr>";
    }

} else {
    echo "<tr><td colspan='".count($cols)."' class='text-center'>Tidak ada data.</td></tr>";
}
?>
</tbody>
</table>

</body>
</html>

<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();

header('Content-Type: application/pdf');
$file = "{$type}_{$bulan}.pdf";
header("Content-Disposition: attachment; filename=\"$file\"");
echo $dompdf->output();
exit;
?>
