<?php
include 'koneksi.php';
include 'view/template/header.php';

if (!isset($_GET['id'])) {
  echo "<script>alert('ID tidak ditemukan');window.location.href='../../index.php?page=barang';</script>";
  exit;
}

$id = $_GET['id'];
$query = "SELECT * FROM barang WHERE id_barang = '$id'";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
  echo "<script>alert('Data tidak ditemukan');window.location.href='../../index.php?page=barang';</script>";
  exit;
}

$data = mysqli_fetch_assoc($result);
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
                  <h4 class="card-title">Edit Data Barang</h4>
                  <form class="forms-sample" method="POST" action="index.php?page=controller/ProdukController">
                    <input type="hidden" name="method" value="PUT">
                    <input type="hidden" name="id_barang" value="<?= $data['id_barang'] ?>">

                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Kode Barang</label>
                          <input type="text" class="form-control" name="kode_barang" value="<?= htmlspecialchars($data['kode_barang']) ?>" required>
                        </div>
                        <div class="form-group">
                          <label>Nama Barang</label>
                          <input type="text" class="form-control" name="nama_barang" value="<?= htmlspecialchars($data['nama_barang']) ?>" required>
                        </div>
                        <div class="form-group">
                          <label>Kategori</label>
                          <input type="text" class="form-control" name="kategori" value="<?= htmlspecialchars($data['kategori']) ?>" required>
                        </div>
                        <div class="form-group">
                          <label>Satuan</label>
                          <input type="text" class="form-control" name="satuan" value="<?= htmlspecialchars($data['satuan']) ?>" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Harga Beli</label>
                          <input type="number" step="0.01" class="form-control" name="harga_beli" value="<?= $data['harga_beli'] ?>" required>
                        </div>
                        <div class="form-group">
                          <label>Harga Jual</label>
                          <input type="number" step="0.01" class="form-control" name="harga_jual" value="<?= $data['harga_jual'] ?>" required>
                        </div>
                      </div>
                    </div>

                    <button type="submit" class="btn btn-success me-2">
                      <i class="mdi mdi-check"></i> Simpan Perubahan
                    </button>
                    <a href="../../index.php?page=barang" class="btn btn-light">
                      <i class="mdi mdi-arrow-left"></i> Kembali
                    </a>
                  </form>
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
