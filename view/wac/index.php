<?php
include 'view/template/header.php';
include 'koneksi.php';

$barang_query = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");

function hitung_wac($koneksi, $id_barang) {
    $query = mysqli_query($koneksi, "SELECT i.jumlah, b.harga_beli, i.tanggal, i.kode_transaksi, 
                                     i.keterangan, i.jenis_transaksi, i.sisa_stok
                               FROM inventory i
                               JOIN barang b ON i.id_barang = b.id_barang
                               WHERE i.id_barang = '$id_barang'
                               ORDER BY i.tanggal ASC");

    $jumlah_total = 0;
    $nilai_total = 0;
    $nilai_wac = 0;
    $riwayat = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $jumlah = (int)$row['jumlah'];
        $harga = (float)$row['harga_beli'];
        $tanggal = $row['tanggal'];
        $kode = $row['kode_transaksi'];
        $keterangan = $row['keterangan'];
        $jenis = $row['jenis_transaksi'];
        $sisa_stok = (int)$row['sisa_stok'];
        
        if ($jenis == 'masuk') {
            $nilai = $jumlah * $harga;
            $nilai_total += $nilai;
            $jumlah_total += $jumlah;
            
            if ($jumlah_total > 0) {
                $nilai_wac = $nilai_total / $jumlah_total;
            }
        } else {
            $nilai = $jumlah * $nilai_wac;
            $nilai_total -= $nilai;
            $jumlah_total -= $jumlah;
        }

        $riwayat[] = [
            'tanggal' => $tanggal,
            'kode' => $kode,
            'keterangan' => $keterangan,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'harga' => $harga,
            'nilai' => $nilai,
            'stok_saat_ini' => $jumlah_total,
            'nilai_total' => $nilai_total,
            'nilai_wac' => $nilai_wac
        ];
    }

    return [
        'nilai_wac_akhir' => $nilai_wac, 
        'jumlah_akhir' => $jumlah_total, 
        'nilai_akhir' => $nilai_total, 
        'riwayat' => $riwayat
    ];
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
                <h4 class="card-title">Perhitungan WAC (Weighted Average Cost)</h4>
                
                <?php while ($barang = mysqli_fetch_assoc($barang_query)) : ?>
                  <?php
                    $id = $barang['id_barang'];
                    $hasil_wac = hitung_wac($koneksi, $id);
                    
                    if (count($hasil_wac['riwayat']) > 0):
                  ?>
                  <div class="mb-4">
                    <h5 class="bg-primary text-white p-2">
                      <?= $barang['kode_barang'] ?> - <?= $barang['nama_barang'] ?>
                    </h5>
                    <p>
                      <b>WAC Saat Ini:</b> Rp <?= number_format($hasil_wac['nilai_wac_akhir'], 0, ',', '.') ?> | 
                      <b>Total Stok:</b> <?= $hasil_wac['jumlah_akhir'] ?> unit
                    </p>
                    
                    <div class="table-responsive">
                      <table class="table table-bordered">
                        <thead class="bg-light">
                          <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Transaksi</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Nilai</th>
                            <th>Stok</th>
                            <th>Total Nilai</th>
                            <th>WAC</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                            $no = 1; 
                            foreach ($hasil_wac['riwayat'] as $item): 
                          ?>
                            <tr>
                              <td><?= $no++ ?></td>
                              <td><?= date('d-m-Y', strtotime($item['tanggal'])) ?></td>
                              <td><?= $item['kode'] ?></td>
                              <td>
                                <?php if ($item['jenis'] == 'masuk'): ?>
                                  <span class="text-success">Masuk</span>
                                <?php else: ?>
                                  <span class="text-danger">Keluar</span>
                                <?php endif; ?>
                              </td>
                              <td><?= $item['jumlah'] ?></td>
                              <td>
                                Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                              </td>
                              <td>
                                Rp <?= number_format($item['nilai'], 0, ',', '.') ?>
                              </td>
                              <td><?= $item['stok_saat_ini'] ?></td>
                              <td>
                                Rp <?= number_format($item['nilai_total'], 0, ',', '.') ?>
                              </td>
                              <td>
                                Rp <?= number_format($item['nilai_wac'], 0, ',', '.') ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-info text-white">
                          <tr>
                            <td colspan="7" class="text-right"><b>WAC Akhir</b></td>
                            <td><?= $hasil_wac['jumlah_akhir'] ?></td>
                            <td>Rp <?= number_format($hasil_wac['nilai_akhir'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($hasil_wac['nilai_wac_akhir'], 0, ',', '.') ?></td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                  <?php endif; ?>
                <?php endwhile; ?>
                
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