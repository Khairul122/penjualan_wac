<?php
include 'koneksi.php';
include 'view/template/header.php';
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
                        <div class="col-sm-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Laporan</h4>
                                    <div class="row">

                                        <!-- Laporan WAC -->
                                        <div class="col-md-6 col-xl-3">
                                            <div class="card shadow-sm p-3 mb-4">
                                                <h5 class="card-title">Laporan WAC</h5>
                                                <form action="index.php" method="GET">
                                                    <input type="hidden" name="page" value="laporan-wac">
                                                    <div class="form-group">
                                                        <label>Dari Tanggal</label>
                                                        <input type="date" name="from" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Sampai Tanggal</label>
                                                        <input type="date" name="to" class="form-control" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary w-100 mt-2">Lihat Laporan</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Laporan Penjualan -->
                                        <div class="col-md-6 col-xl-3">
                                            <div class="card shadow-sm p-3 mb-4">
                                                <h5 class="card-title">Laporan Penjualan</h5>
                                                <form action="index.php" method="GET">
                                                    <input type="hidden" name="page" value="laporan-penjualan">
                                                    <div class="form-group">
                                                        <label>Dari Tanggal</label>
                                                        <input type="date" name="from" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Sampai Tanggal</label>
                                                        <input type="date" name="to" class="form-control" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-success w-100 mt-2">Lihat Laporan</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Laporan Barang -->
                                        <?php
                                        $kategori_q = mysqli_query($koneksi, "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
                                        ?>
                                        <div class="col-md-6 col-xl-3">
                                            <div class="card shadow-sm p-3 mb-4">
                                                <h5 class="card-title">Laporan Barang</h5>
                                                <form action="index.php" method="GET">
                                                    <input type="hidden" name="page" value="laporan-barang">
                                                    <div class="form-group">
                                                        <label>Kategori</label>
                                                        <select name="kategori" class="form-control">
                                                            <option value="">-- Semua Kategori --</option>
                                                            <?php while ($row = mysqli_fetch_assoc($kategori_q)) : ?>
                                                                <option value="<?= htmlspecialchars($row['kategori']) ?>"><?= htmlspecialchars($row['kategori']) ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-warning w-100 mt-2">Lihat Laporan</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Laporan Inventory -->
                                        <div class="col-md-6 col-xl-3">
                                            <div class="card shadow-sm p-3 mb-4">
                                                <h5 class="card-title">Laporan Inventory</h5>
                                                <form action="index.php" method="GET">
                                                    <input type="hidden" name="page" value="laporan-inventory">
                                                    <div class="form-group">
                                                        <label>Dari Tanggal</label>
                                                        <input type="date" name="from" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Sampai Tanggal</label>
                                                        <input type="date" name="to" class="form-control" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-info w-100 mt-2">Lihat Laporan</button>
                                                </form>
                                            </div>
                                        </div>

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