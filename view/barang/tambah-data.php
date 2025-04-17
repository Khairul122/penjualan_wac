<?php
include('view/template/header.php');
include('koneksi.php');

$result = mysqli_query($koneksi, "SELECT MAX(kode_barang) AS kode_terakhir FROM barang");
$data = mysqli_fetch_assoc($result);
$lastCode = $data['kode_terakhir'];

if ($lastCode) {
  $number = (int) substr($lastCode, 3);
  $newNumber = $number + 1;
  $kode_barang = 'BRG' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
} else {
  $kode_barang = 'BRG001';
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
                  <h4 class="card-title">Tambah Data Barang</h4>
                  <form action="index.php?page=controller/ProdukController" method="POST" onsubmit="convertToAngka();">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Kode Barang</label>
                          <input type="text" class="form-control" name="kode_barang" value="<?= $kode_barang ?>" readonly required>
                        </div>
                        <div class="form-group">
                          <label>Nama Barang</label>
                          <input type="text" class="form-control" name="nama_barang" placeholder="Nama Barang" required>
                        </div>
                        <div class="form-group">
                          <label>Kategori</label>
                          <input type="text" class="form-control" name="kategori" placeholder="Kategori (misal: Elektronik)" required>
                        </div>
                        <div class="form-group">
                          <label>Satuan</label>
                          <input type="text" class="form-control" name="satuan" placeholder="pcs / box / liter" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Harga Beli</label>
                          <input type="text" class="form-control rupiah" id="harga_beli" name="harga_beli" placeholder="Rp 0" required>
                        </div>
                        <div class="form-group">
                          <label>Harga Jual</label>
                          <input type="text" class="form-control rupiah" id="harga_jual" name="harga_jual" placeholder="Rp 0" required>
                        </div>
                        <div class="form-group">
                          <label>Stok</label>
                          <input type="number" class="form-control" name="stok" placeholder="Jumlah stok awal" required>
                        </div>
                      </div>
                    </div>

                    <button type="submit" name="tambah_barang" class="btn btn-primary me-2">
                      <i class="mdi mdi-content-save"></i> Simpan
                    </button>
                    <a href="index.php?page=barang" class="btn btn-light">
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
    const formatRupiah = (angka) => {
      let number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

      if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
      }

      rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
      return 'Rp ' + rupiah;
    }

    document.querySelectorAll('.rupiah').forEach(input => {
      input.addEventListener('input', function () {
        this.value = formatRupiah(this.value);
      });
    });

    function convertToAngka() {
      const beli = document.getElementById('harga_beli');
      const jual = document.getElementById('harga_jual');
      beli.value = beli.value.replace(/[^0-9]/g, '');
      jual.value = jual.value.replace(/[^0-9]/g, '');
    }
  </script>
</body>
</html>
