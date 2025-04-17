<?php
include 'view/template/header.php';
include 'koneksi.php';

$query = "SELECT 
            p.kode_penjualan, 
            p.tanggal, 
            p.subtotal,
            p.total_harga,
            p.nominal_bayar,
            p.kembalian,
            u.username,
            p.id_penjualan,
            COUNT(pd.id_penjualan_detail) as jumlah_item,
            SUM(pd.jumlah) as jumlah_total
          FROM penjualan p
          LEFT JOIN penjualan_detail pd ON p.id_penjualan = pd.id_penjualan 
          LEFT JOIN barang b ON pd.id_barang = b.id_barang 
          LEFT JOIN users u ON p.id_users = u.id_users
          GROUP BY p.kode_penjualan, p.tanggal, p.subtotal, p.total_harga, p.nominal_bayar, p.kembalian, u.username, p.id_penjualan
          ORDER BY p.tanggal DESC";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    echo "Error: " . mysqli_error($koneksi);
    exit;
}
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
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="card-title mb-0">Data Penjualan</h4>
                                        <a href="index.php?page=tambah-penjualan" class="btn btn-primary btn-sm">
                                            <i class="mdi mdi-plus"></i> Tambah
                                        </a>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="tabelPenjualan" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode</th>
                                                    <th>Tanggal</th>
                                                    <th>Jumlah Item</th>
                                                    <th>Total Harga</th>
                                                    <th>Nominal Bayar</th>
                                                    <th>Kembalian</th>
                                                    <th>Kasir</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                while ($row = mysqli_fetch_assoc($result)) : ?>
                                                    <tr>
                                                        <td><?= $no++; ?></td>
                                                        <td><?= htmlspecialchars($row['kode_penjualan']); ?></td>
                                                        <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                                                        <td><?= $row['jumlah_item']; ?> (<?= $row['jumlah_total']; ?> pcs)</td>
                                                        <td>Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                                        <td>Rp <?= number_format($row['nominal_bayar'], 0, ',', '.'); ?></td>
                                                        <td>Rp <?= number_format($row['kembalian'], 0, ',', '.'); ?></td>
                                                        <td><?= htmlspecialchars($row['username']); ?></td>
                                                        <td>
                                                            <a href="index.php?page=detail-penjualan&id=<?= $row['id_penjualan']; ?>" class="btn btn-info btn-sm">
                                                                <i class="mdi mdi-eye"></i>
                                                            </a>
                                                            <a href="index.php?page=controller/PenjualanController&action=delete&id=<?= $row['id_penjualan'] ?>"
                                                                onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                                                class="btn btn-danger btn-sm">
                                                                <i class="mdi mdi-delete"></i> Hapus
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
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
    <script src="assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabelPenjualan').DataTable({
                responsive: true,
                autoWidth: false
            });
        });
    </script>
</body>

</html>