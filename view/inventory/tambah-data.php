<?php
include 'view/template/header.php';
include 'koneksi.php';

$result = mysqli_query($koneksi, "SELECT MAX(kode_transaksi) AS kode_terakhir FROM inventory");
$data = mysqli_fetch_assoc($result);
$lastCode = $data['kode_terakhir'];

if ($lastCode) {
    $number = (int) substr($lastCode, 3);
    $newNumber = $number + 1;
    $kode_transaksi = 'INV' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
} else {
    $kode_transaksi = 'INV001';
}

$barang = mysqli_query($koneksi, "SELECT * FROM barang");
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
                <h4 class="card-title">Tambah Transaksi Inventory</h4>

                <form action="index.php?page=controller/InventoryController" method="POST" id="form-inventory">
                  <input type="hidden" name="kode_transaksi" value="<?= $kode_transaksi ?>">

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Nama Barang</label>
                        <select name="id_barang" id="id_barang" class="form-control" required onchange="getStokTerakhir(this.value)">
                          <option value="">-- Pilih Barang --</option>
                          <?php while ($row = mysqli_fetch_assoc($barang)) : ?>
                            <option value="<?= $row['id_barang'] ?>"><?= $row['nama_barang'] ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Jenis Transaksi</label>
                        <select name="jenis_transaksi" id="jenis_transaksi" class="form-control" required onchange="hitungSisa()">
                          <option value="masuk">Masuk</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" required oninput="hitungSisa()">
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Tanggal</label>
                        <input type="datetime-local" name="tanggal" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                      </div>
                      <div class="form-group">
                        <label>Stok Sebelumnya</label>
                        <input type="number" id="stok_sebelumnya" class="form-control" readonly>
                      </div>
                      <div class="form-group">
                        <label>Sisa Stok</label>
                        <input type="number" name="sisa_stok" id="sisa_stok" class="form-control" readonly>
                      </div>
                      <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: retur penjualan, koreksi stok, pembelian awal..."></textarea>
                      </div>
                    </div>
                  </div>

                  <button type="submit" name="tambah_inventory" class="btn btn-primary mt-3">
                    <i class="mdi mdi-content-save"></i> Simpan
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
  function getStokTerakhir(idBarang) {
    if (idBarang) {
      fetch(`index.php?page=controller/InventoryController&get_stok=1&id_barang=${idBarang}`)
        .then(res => res.json())
        .then(data => {
          document.getElementById('stok_sebelumnya').value = data.sisa_stok || 0;
          hitungSisa();
        })
        .catch(error => {
          console.log('Error:', error);
          document.getElementById('stok_sebelumnya').value = 0;
          hitungSisa();
        });
    } else {
      document.getElementById('stok_sebelumnya').value = '';
      document.getElementById('sisa_stok').value = '';
    }
  }

  function hitungSisa() {
    const stok = parseInt(document.getElementById('stok_sebelumnya').value) || 0;
    const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
    const jenis = document.getElementById('jenis_transaksi').value;
    
    let sisa = jenis === 'masuk' ? stok + jumlah : stok - jumlah;
    
    if (jenis === 'keluar' && sisa < 0) {
      alert("Jumlah keluar melebihi stok tersedia!");
      document.getElementById('jumlah').value = '';
      sisa = stok;
    }
    
    document.getElementById('sisa_stok').value = sisa >= 0 ? sisa : stok;
  }
</script>
</body>
</html>