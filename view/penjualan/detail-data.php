<?php
include 'view/template/header.php';
include 'koneksi.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    echo "<script>alert('ID penjualan tidak ditemukan');window.location.href='index.php?page=penjualan';</script>";
    exit;
}

$header_query = "SELECT 
                  p.id_penjualan,
                  p.kode_penjualan, 
                  p.tanggal,
                  p.subtotal,
                  p.total_harga,
                  p.nominal_bayar,
                  p.kembalian,
                  u.username
                FROM penjualan p
                LEFT JOIN users u ON p.id_users = u.id_users
                WHERE p.id_penjualan = '$id'";

$header_result = mysqli_query($koneksi, $header_query);
$header = mysqli_fetch_assoc($header_result);

if (!$header) {
    echo "<script>alert('Data penjualan tidak ditemukan');window.location.href='index.php?page=penjualan';</script>";
    exit;
}

$detail_query = "SELECT 
                  pd.id_penjualan_detail,
                  b.nama_barang,
                  pd.harga_satuan,
                  pd.jumlah,
                  pd.total_harga as subtotal
                FROM penjualan_detail pd
                JOIN barang b ON pd.id_barang = b.id_barang
                WHERE pd.id_penjualan = '$id'
                ORDER BY pd.id_penjualan_detail";

$detail_result = mysqli_query($koneksi, $detail_query);

$count_query = "SELECT SUM(jumlah) as jumlah_total FROM penjualan_detail WHERE id_penjualan = '$id'";
$count_result = mysqli_query($koneksi, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$jumlah_total = $count_data['jumlah_total'];

$tanggal = new DateTime($header['tanggal']);
$bulan_indo = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember"
];
$tanggal_indo = $tanggal->format('d') . " " . $bulan_indo[$tanggal->format('n') - 1] . " " . $tanggal->format('Y H:i');
?>

<body class="with-welcome-text">
    <div class="container-scroller">
        <?php include 'view/template/navbar.php'; ?>
        <div class="container-fluid page-body-wrapper">
            <?php include 'view/template/setting_panel.php'; ?>
            <?php include 'view/template/sidebar.php'; ?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-sm-12">

                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="card-title mb-0">Detail Penjualan</h4>
                                        <a href="index.php?page=penjualan" class="btn btn-secondary btn-sm">
                                            <i class="mdi mdi-arrow-left"></i> Kembali
                                        </a>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="30%"><strong>Kode Penjualan</strong></td>
                                                    <td>: <?= htmlspecialchars($header['kode_penjualan']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal</strong></td>
                                                    <td>: <?= $tanggal_indo; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kasir</strong></td>
                                                    <td>: <?= htmlspecialchars($header['username']); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="30%"><strong>Subtotal</strong></td>
                                                    <td>: Rp <?= number_format($header['subtotal'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Harga</strong></td>
                                                    <td>: Rp <?= number_format($header['total_harga'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pembayaran</strong></td>
                                                    <td>: Rp <?= number_format($header['nominal_bayar'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kembalian</strong></td>
                                                    <td>: Rp <?= number_format($header['kembalian'], 0, ',', '.'); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Produk</th>
                                                    <th>Harga Satuan</th>
                                                    <th>Jumlah</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                while ($row = mysqli_fetch_assoc($detail_result)) : ?>
                                                    <tr>
                                                        <td><?= $no++; ?></td>
                                                        <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                                                        <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                                                        <td><?= $row['jumlah']; ?></td>
                                                        <td>Rp <?= number_format($row['subtotal'], 0, ',', '.'); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3" class="text-right">Total</th>
                                                    <th><?= $jumlah_total; ?></th>
                                                    <th>Rp <?= number_format($header['total_harga'], 0, ',', '.'); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="text-right mt-4">
                                        <a href="index.php?page=invoice&id=<?= $header['id_penjualan']; ?>" class="btn btn-primary" target="_blank">
                                            <i class="mdi mdi-printer"></i> Cetak Invoice
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'view/template/script.php'; ?>
</body>
</html>