<?php
include 'view/template/header.php';
include 'koneksi.php';

$result = mysqli_query($koneksi, "SELECT MAX(kode_penjualan) AS kode_terakhir FROM penjualan");
$data = mysqli_fetch_assoc($result);
$lastCode = $data['kode_terakhir'];

if ($lastCode) {
    $number = (int) substr($lastCode, 3);
    $newNumber = $number + 1;
    $kode_penjualan = 'PJL' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
} else {
    $kode_penjualan = 'PJL001';
}

$produk_query = "SELECT b.*, 
                 COALESCE(
                     (SELECT i.sisa_stok 
                      FROM inventory i 
                      WHERE i.id_barang = b.id_barang 
                      ORDER BY i.id_inventory DESC 
                      LIMIT 1), 0
                 ) as stok_tersedia
                 FROM barang b 
                 ORDER BY b.nama_barang";
$produk = mysqli_query($koneksi, $produk_query);

$bulan_indo = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];
$tgl = date('d');
$bln = $bulan_indo[date('n') - 1];
$thn = date('Y');
$tanggal_indo = "$tgl $bln $thn";
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
                <h4 class="card-title d-flex justify-content-between align-items-center">
                  Tambah Penjualan <span class="badge bg-primary fw-bold"><?= $tanggal_indo ?></span>
                </h4>
                <form action="index.php?page=controller/PenjualanController" method="POST" id="form-submit">
                  <div class="row">
                    <div class="col-md-4">
                      <label>Kode Penjualan</label>
                      <input type="text" name="kode_penjualan" class="form-control" value="<?= $kode_penjualan ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label>Produk</label>
                      <select id="produk" class="form-control">
                        <option value="">-- Pilih Produk --</option>
                        <?php while ($row = mysqli_fetch_assoc($produk)) : ?>
                          <option value="<?= $row['id_barang'] ?>"
                                  data-nama="<?= $row['nama_barang'] ?>"
                                  data-harga="<?= $row['harga_jual'] ?>"
                                  data-stok="<?= $row['stok_tersedia'] ?>">
                            <?= $row['nama_barang'] ?> (Stok: <?= $row['stok_tersedia'] ?>)
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label>Jumlah</label>
                      <input type="number" id="jumlah" class="form-control" min="1">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                      <button type="button" onclick="tambahBarang()" class="btn btn-primary w-100">
                        <i class="mdi mdi-cart-plus"></i> Tambah
                      </button>
                    </div>
                  </div>

                  <hr>

                  <div class="table-responsive">
                    <table class="table table-bordered mt-3" id="tabel-transaksi">
                      <thead>
                        <tr>
                          <th>Produk</th>
                          <th>Stok Tersedia</th>
                          <th>Harga</th>
                          <th>Jumlah</th>
                          <th>Subtotal</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>

                    <div class="row mt-3">
                      <div class="col-md-4 ms-auto">
                        <div class="form-group">
                          <label>Total Harga</label>
                          <input type="text" id="total_harga_display" class="form-control" readonly>
                          <input type="hidden" name="total_harga" id="total_harga">
                        </div>
                        <div class="form-group">
                          <label>Nominal Bayar</label>
                          <input type="text" id="nominal_bayar" name="nominal_bayar" class="form-control rupiah" required>
                        </div>
                        <div class="form-group">
                          <label>Kembalian</label>
                          <input type="text" id="kembalian" class="form-control" readonly>
                        </div>
                        <input type="hidden" name="id_users" value="<?= $_SESSION['id_users'] ?>">
                        <button type="submit" class="btn btn-success w-100 mt-2" id="btn-simpan">
                          <i class="mdi mdi-check"></i> Simpan Transaksi
                        </button>
                      </div>
                    </div>
                  </div>
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
  let barangList = [];
  let total = 0;

  function formatRupiah(angka) {
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  function getStokTersedia(idBarang) {
    const produk = document.getElementById('produk');
    for (let i = 0; i < produk.options.length; i++) {
      if (produk.options[i].value === idBarang) {
        return parseInt(produk.options[i].getAttribute('data-stok'));
      }
    }
    return 0;
  }

  function tambahBarang() {
    const produk = document.getElementById('produk');
    const jumlah = parseInt(document.getElementById('jumlah').value);
    const selected = produk.options[produk.selectedIndex];

    if (!produk.value || !jumlah || jumlah < 1) {
      alert('Pilih produk dan masukkan jumlah yang valid');
      return;
    }

    const id = produk.value;
    const nama = selected.getAttribute('data-nama');
    const harga = parseInt(selected.getAttribute('data-harga'));
    const stok = parseInt(selected.getAttribute('data-stok'));

    if (stok <= 0) {
      alert('Stok produk habis');
      return;
    }

    if (jumlah > stok) {
      alert(`Jumlah melebihi stok tersedia (${stok})`);
      return;
    }

    const existingIndex = barangList.findIndex(item => item.id_barang === id);
    if (existingIndex >= 0) {
      const newJumlah = barangList[existingIndex].jumlah + jumlah;
      if (newJumlah > stok) {
        alert(`Total jumlah melebihi stok tersedia (${stok})`);
        return;
      }
      barangList[existingIndex].jumlah = newJumlah;
      barangList[existingIndex].subtotal = harga * newJumlah;
    } else {
      const subtotal = harga * jumlah;
      barangList.push({
        id_barang: id,
        nama: nama,
        harga: harga,
        jumlah: jumlah,
        subtotal: subtotal,
        stok_tersedia: stok
      });
    }

    renderTabel();
    
    produk.selectedIndex = 0;
    document.getElementById('jumlah').value = '';
  }

  function renderTabel() {
    const tbody = document.querySelector('#tabel-transaksi tbody');
    tbody.innerHTML = '';
    total = 0;

    barangList.forEach((item, index) => {
      total += item.subtotal;
      const sisaStok = item.stok_tersedia - item.jumlah;
      tbody.innerHTML += `
        <tr>
          <td>
            <input type="hidden" name="id_barang[]" value="${item.id_barang}">
            ${item.nama}
          </td>
          <td>
            <span class="badge ${sisaStok <= 0 ? 'bg-danger' : sisaStok <= 10 ? 'bg-warning' : 'bg-success'}">
              ${formatNumber(sisaStok)}
            </span>
          </td>
          <td>${formatRupiah(item.harga)}</td>
          <td>
            <input type="hidden" name="jumlah[]" value="${item.jumlah}">
            <div class="d-flex align-items-center">
              <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="updateJumlah(${index}, -1)">-</button>
              <span class="mx-2">${item.jumlah}</span>
              <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="updateJumlah(${index}, 1)">+</button>
            </div>
          </td>
          <td>
            <input type="hidden" name="subtotal[]" value="${item.subtotal}">
            ${formatRupiah(item.subtotal)}
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="hapusItem(${index})">
              <i class="mdi mdi-delete"></i>
            </button>
          </td>
        </tr>`;
    });

    document.getElementById('total_harga_display').value = formatRupiah(total);
    document.getElementById('total_harga').value = total;
    
    const nominalBayar = document.getElementById('nominal_bayar');
    if (nominalBayar.value) {
      let bayar = parseInt(nominalBayar.value.replace(/[^0-9]/g, '')) || 0;
      let kembalian = bayar - total;
      document.getElementById('kembalian').value = formatRupiah(kembalian < 0 ? 0 : kembalian);
    }
    
    document.getElementById('btn-simpan').disabled = barangList.length === 0;
  }

  function updateJumlah(index, perubahan) {
    const item = barangList[index];
    const jumlahBaru = item.jumlah + perubahan;
    
    if (jumlahBaru <= 0) {
      hapusItem(index);
      return;
    }
    
    if (jumlahBaru > item.stok_tersedia) {
      alert(`Jumlah tidak boleh melebihi stok tersedia (${item.stok_tersedia})`);
      return;
    }
    
    item.jumlah = jumlahBaru;
    item.subtotal = item.harga * jumlahBaru;
    renderTabel();
  }

  function hapusItem(index) {
    barangList.splice(index, 1);
    renderTabel();
  }

  document.getElementById('nominal_bayar').addEventListener('input', function () {
    let value = this.value.replace(/[^0-9]/g, '');
    this.value = formatNumber(value);
    
    let bayar = parseInt(value) || 0;
    let kembalian = bayar - total;
    document.getElementById('kembalian').value = formatRupiah(kembalian < 0 ? 0 : kembalian);
  });

  document.getElementById('form-submit').addEventListener('submit', function (e) {
    if (barangList.length === 0) {
      e.preventDefault();
      alert('Belum ada barang yang ditambahkan!');
      return;
    }
    
    let bayar = parseInt(document.getElementById('nominal_bayar').value.replace(/[^0-9]/g, '')) || 0;
    if (bayar < total) {
      e.preventDefault();
      alert('Nominal bayar tidak boleh kurang dari total!');
      return;
    }
  });
  
  document.getElementById('btn-simpan').disabled = true;
</script>
</body>