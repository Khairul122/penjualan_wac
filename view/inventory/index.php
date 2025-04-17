<?php
include 'view/template/header.php';
include 'koneksi.php';

$result = mysqli_query($koneksi, "SELECT inventory.*, barang.nama_barang FROM inventory 
                                  JOIN barang ON inventory.id_barang = barang.id_barang 
                                  ORDER BY inventory.tanggal DESC");
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
                                        <h4 class="card-title mb-0">Data Transaksi Inventory</h4>
                                        <a href="index.php?page=tambah-inventory" class="btn btn-primary btn-sm">
                                            <i class="mdi mdi-plus"></i> Tambah Transaksi
                                        </a>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="tabelInventory" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode Transaksi</th>
                                                    <th>Nama Barang</th>
                                                    <th>Jenis</th>
                                                    <th>Jumlah</th>
                                                    <th>Sisa Stok</th>
                                                    <th>Tanggal</th>
                                                    <th>Keterangan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                while ($row = mysqli_fetch_assoc($result)) : ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td>
                                                        <td><?= htmlspecialchars($row['kode_transaksi']) ?></td>
                                                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= $row['jenis_transaksi'] === 'masuk' ? 'success' : 'danger' ?>">
                                                                <?= ucfirst($row['jenis_transaksi']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $row['jumlah'] ?></td>
                                                        <td><?= $row['sisa_stok'] ?></td>
                                                        <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                                                        <td><?= nl2br(htmlspecialchars($row['keterangan'])) ?></td>
                                                        <td>
                                                            <a href="index.php?page=edit-inventory&id=<?= $row['id_inventory']; ?>" class="btn btn-warning btn-sm">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </a>
                                                            <a href="index.php?page=controller/InventoryController&method=DELETE&id=<?= $row['id_inventory']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="btn btn-danger btn-sm">
                                                                <i class="mdi mdi-delete"></i>
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
            $('#tabelInventory').DataTable({
                responsive: true,
                autoWidth: false
            });
        });
    </script>
</body>

</html>