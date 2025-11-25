<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'koneksi.php';

// Cek role dan tentukan kemana tombol kembali diarahkan
$kembaliLink = ($_SESSION['role'] === 'kasir') ? 'logout.php' : 'index.php';

// Ambil data stock untuk dropdown obat
$obatList = mysqli_query($koneksi, "SELECT * FROM stock");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Kasir - Apotek Rakyat</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <style>
    .btn-custom {
        background-color: #28d17c;
        color: white;
    }
    .btn-custom:hover {
        background-color: #22ba6f;
        color: white;
    }
    </style>
    <!-- jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<div id="layoutSidenav_content">
    <main class="container-fluid px-4">
       <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 10px;">
            <i class="fas fa-user-circle fa-2x" style="color: #888; margin-right: 8px;"></i>
            <span style="font-weight: bold;">Kasir =  <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>

        <h1 class="mt-4">Kasir Apotek Rakyat Sehat Farma</h1>
        <div class="card mb-4">
            <div class="card-body">
                <a href="<?= $kembaliLink ?>" class="btn btn-secondary mb-3">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <form action="proses_transaksi.php" method="POST" id="formKasir">
                    <div class="table-responsive">
                        <table class="table" id="obatTable">
                            <thead>
                                <tr>
                                    <th>Obat</th>
                                    <th>Stock</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Total</th>
                                    <th>Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="transaksi-body">
                                <!-- Baris transaksi dinamis -->
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-custom" onclick="tambahBaris()">+ Tambah Obat</button>
                    </div>

                    <hr>
                    <div class="mb-3">
                        <label for="total_harga">Total Harga</label>
                        <input type="text" name="total_harga" id="total_harga" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="uang_bayar">Uang Bayar</label>
                        <input type="number" name="uang_bayar" id="uang_bayar" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="kembalian">Kembalian</label>
                        <input type="text" name="kembalian" id="kembalian" class="form-control" readonly>
                    </div>
                    <button type="submit" class="btn btn-custom"><i class="fa fa-check"></i> Proses</button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
// Ambil list obat dari PHP
const obatList = <?php
$obats = [];
while ($row = mysqli_fetch_assoc($obatList)) {
    $obats[] = [
        'kodeobat' => $row['kodeobat'],
        'namaobat' => $row['namaobat'],
        'harga' => $row['harga'],
        'stok' => $row['stock']
    ];
}
echo json_encode($obats);
?>;

$(document).ready(function() {
    function initSelect2(el) {
        $(el).select2({
            placeholder: "--Pilih Obat--",
            allowClear: true,
            width: '100%'
        })
        .on('change', function() {
            updateHarga(this);
        });
    }

    window.tambahBaris = function() {
        const tbody = document.getElementById("transaksi-body");
        const tr = document.createElement("tr");

        let options = obatList.map(o =>
            `<option value="${o.kodeobat}" data-harga="${o.harga}" data-stok="${o.stok}">${o.namaobat}</option>`
        ).join('');

        tr.innerHTML = `
            <td>
                <select name="obat[]" class="form-select obat select-obat">
                    <option></option>
                    ${options}
                </select>
            </td>
            <td><input type="text" class="form-control stok" readonly></td>
            <td><input type="number" name="jumlah[]" class="form-control jumlah" value="1" min="1" oninput="hitungSubtotal(this)"></td>
            <td><input type="text" name="harga_satuan[]" class="form-control harga" readonly></td>
            <td><input type="text" name="subtotal[]" class="form-control subtotal" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)"><i class="fas fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        initSelect2(tr.querySelector('.select-obat'));
    };

    window.updateHarga = function(selectEl) {
        const harga = selectEl.selectedOptions[0]?.getAttribute("data-harga") || 0;
        const stok = selectEl.selectedOptions[0]?.getAttribute("data-stok") || 0;

        const tr = selectEl.closest("tr");
        tr.querySelector(".harga").value = harga;
        tr.querySelector(".stok").value = stok;

        hitungSubtotal(selectEl);
    };

    window.hitungSubtotal = function(inputEl) {
        const tr = inputEl.closest("tr");
        const jumlahEl = tr.querySelector(".jumlah");
        const jumlah = parseInt(jumlahEl.value) || 0;
        const harga = parseInt(tr.querySelector(".harga").value) || 0;

        const stok = parseInt(tr.querySelector(".stok").value) || 0;

        if (jumlah > stok) {
            alert(`Stok tidak mencukupi. Stok tersedia: ${stok}`);
            jumlahEl.value = stok;
            return;
        }

        const subtotal = jumlah * harga;
        tr.querySelector(".subtotal").value = subtotal;
        hitungTotal();
    };

    window.hitungTotal = function() {
        let total = 0;
        document.querySelectorAll(".subtotal").forEach(el => {
            total += parseInt(el.value) || 0;
        });
        $('#total_harga').val(total);
        const bayar = parseInt($('#uang_bayar').val()) || 0;
        $('#kembalian').val(bayar - total);
    };

    window.hapusBaris = function(button) {
        button.closest("tr").remove();
        hitungTotal();
    };

    $('#uang_bayar').on('input', hitungTotal);
});
</script>
</body>
</html>
