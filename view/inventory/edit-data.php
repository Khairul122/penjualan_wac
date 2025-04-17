<?php
include 'view/template/header.php';
include 'koneksi.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan'); window.location.href='index.php?page=inventory';</script>";
    exit;
}

$id_inventory = $_GET['id'];

// Ambil data transaksi
$query = mysqli_query($koneksi, "SELECT inventory.*, barang.nama_barang 
                                 FROM inventory 
                                 JOIN barang ON inventory.id_barang = barang.id_barang 
                                 WHERE inventory.id_inventory = '$id_inventory'");

$data = mysqli_fetch_assoc($query);
if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location.href='index.php?page=inventory';</script>";
    exit;
}

// Ambil semua barang
$barang_list = mysqli_query($koneksi, "SELECT * FROM barang");

// Cari stok sebelum transaksi ini
$stok_before = mysqli_query($koneksi, "
    SELECT sisa_stok FROM inventory 
    WHERE id_barang = '{$data['id_barang']}' AND tanggal < '{$data['tanggal']}' 
    ORDER BY tanggal DESC, id_inventory DESC LIMIT 1");
$stok_row = mysqli_fetch_assoc($stok_before);
$stok_sebelumnya = $stok_row ? $stok_row['sisa_stok'] : 0;
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
                <h4 class="card-title">Edit Transaksi Inventory</h4>

                <form action="index.php?page=controller/InventoryController" method="POST" id="form-inventory">
                  <input type="hidden" name="edit_inventory" value="true">
                  <input type="hidden" name="id_inventory" value="<?= $data['id_inventory'] ?>">
                  <input type="hidden" name="kode_transaksi" value="<?= $data['kode_transaksi'] ?>">

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Nama Barang</label>
                        <select name="id_barang" id="id_barang" class="form-control" required onchange="setStokAwal(this.value)">
                          <option value="">-- Pilih Barang --</option>
                          <?php while ($row = mysqli_fetch_assoc($barang_list)) : ?>
                            <option value="<?= $row['id_barang'] ?>" <?= $row['id_barang'] == $data['id_barang'] ? 'selected' : '' ?>>
                              <?= $row['nama_barang'] ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Jenis Transaksi</label>
                        <input type="text" name="jenis_transaksi" id="jenis_transaksi" class="form-control" readonly value="<?= $data['jenis_transaksi'] ?>">
                      </div>
                      <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" required value="<?= $data['jumlah'] ?>" oninput="hitungSisa()">
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Tanggal</label>
                        <input type="datetime-local" name="tanggal" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($data['tanggal'])) ?>">
                      </div>
                      <div class="form-group">
                        <label>Stok Sebelumnya</label>
                        <input type="number" id="stok_sebelumnya" class="form-control" readonly value="<?= $stok_sebelumnya ?>">
                      </div>
                      <div class="form-group">
                        <label>Sisa Stok</label>
                        <input type="number" name="sisa_stok" id="sisa_stok" class="form-control" readonly value="<?= $data['sisa_stok'] ?>">
                      </div>
                      <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" id="keterangan" class="form-control" value="<?= $data['keterangan'] ?>">
                      </div>
                    </div>
                  </div>

                  <button type="submit" class="btn btn-primary mt-3">
                    <i class="mdi mdi-content-save"></i> Simpan Perubahan
                  </button>
                  <a href="index.php?page=inventory" class="btn btn-light mt-3">
                    <i class="mdi mdi-close-circle-outline"></i> Batal
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
<script>
  function hitungSisa() {
    const stok = parseInt(document.getElementById('stok_sebelumnya').value) || 0
    const jumlah = parseInt(document.getElementById('jumlah').value) || 0
    const jenis = document.getElementById('jenis_transaksi').value
    let sisa = jenis === 'masuk' ? stok + jumlah : stok - jumlah
    if (sisa < 0) {
      alert("Jumlah keluar melebihi stok tersedia!")
      document.getElementById('jumlah').value = ''
      sisa = stok
    }
    document.getElementById('sisa_stok').value = sisa
  }

  function setStokAwal(idBarang) {
    fetch(`get_stok.php?id_barang=${idBarang}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById('stok_sebelumnya').value = data.sisa_stok || 0
        hitungSisa()
      })
  }
</script>
</body>
</html>
