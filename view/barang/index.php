<?php include('view/template/header.php'); ?>

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

                            <?php
                            include 'koneksi.php';
                            $query = "SELECT * FROM barang";
                            $result = mysqli_query($koneksi, $query);
                            ?>

                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="card-title mb-0">Data Barang</h4>
                                        <a href="index.php?page=tambah-data" class="btn btn-primary btn-sm">
                                            <i class="mdi mdi-plus"></i> Tambah Barang
                                        </a>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="tabelBarang" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode</th>
                                                    <th>Nama</th>
                                                    <th>Kategori</th>
                                                    <th>Satuan</th>
                                                    <th>Harga Beli</th>
                                                    <th>Harga Jual</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                while ($row = mysqli_fetch_assoc($result)) : ?>
                                                    <tr>
                                                        <td><?= $no++; ?></td>
                                                        <td><?= htmlspecialchars($row['kode_barang']); ?></td>
                                                        <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                                                        <td><?= htmlspecialchars($row['kategori']); ?></td>
                                                        <td><?= htmlspecialchars($row['satuan']); ?></td>
                                                        <td>Rp <?= number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                                                        <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                                        <td>
                                                            <a href="index.php?page=edit-data&id=<?= $row['id_barang']; ?>" class="btn btn-warning btn-sm">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </a>
                                                            <a href="index.php?page=controller/ProdukController&method=DELETE&id=<?= $row['id_barang']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="btn btn-danger btn-sm">
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
            $('#tabelBarang').DataTable({
                responsive: true,
                autoWidth: false
            });
        });
    </script>
</body>

</html>