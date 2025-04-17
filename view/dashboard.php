<?php include('view/template/header.php'); ?>

<body class="with-welcome-text">
  <div class="container-scroller">
    <?php include 'template/navbar.php'; ?>
    <div class="container-fluid page-body-wrapper">
      <?php include 'view/template/setting_panel.php'; ?>
      <?php include 'view/template/sidebar.php'; ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row mb-4">
            <div class="col-12">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                      <h4 class="card-title card-title-dash">Selamat Datang, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Pengguna'; ?>!</h4>
                      <p class="card-subtitle card-subtitle-dash">
                        Ini adalah dashboard sistem informasi Grosir Serena AD. 
                        <?php 
                          $jam = date('H');
                          if ($jam >= 5 && $jam < 12) {
                            echo "Selamat pagi!";
                          } elseif ($jam >= 12 && $jam < 15) {
                            echo "Selamat siang!";
                          } elseif ($jam >= 15 && $jam < 18) {
                            echo "Selamat sore!";
                          } else {
                            echo "Selamat malam!";
                          }
                        ?>
                      </p>
                    </div>
                    <div>
                      <div class="btn-wrapper">
                        <div class="btn-group">
                          <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-calendar"></i> <?php echo date('d F Y'); ?>
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">Hari Ini</a>
                            <a class="dropdown-item" href="#">Minggu Ini</a>
                            <a class="dropdown-item" href="#">Bulan Ini</a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <?php
            include 'koneksi.php';
            
            $query_barang = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang");
            $total_barang = 0;
            if ($query_barang) {
              $data_barang = mysqli_fetch_assoc($query_barang);
              $total_barang = $data_barang['total'];
            }
            
            $query_penjualan = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM penjualan");
            $total_penjualan = 0;
            if ($query_penjualan) {
              $data_penjualan = mysqli_fetch_assoc($query_penjualan);
              $total_penjualan = $data_penjualan['total'];
            }
            
            $query_pendapatan = mysqli_query($koneksi, "SELECT SUM(total_harga) as total FROM penjualan");
            $total_pendapatan = 0;
            if ($query_pendapatan) {
              $data_pendapatan = mysqli_fetch_assoc($query_pendapatan);
              $total_pendapatan = $data_pendapatan['total'] ?: 0;
            }
            
            $today = date('Y-m-d');
            $query_today = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM penjualan WHERE DATE(tanggal) = '$today'");
            $transaksi_hari_ini = 0;
            if ($query_today) {
              $data_today = mysqli_fetch_assoc($query_today);
              $transaksi_hari_ini = $data_today['total'];
            }
            
            $last_month = date('Y-m-d', strtotime('-1 month'));
            $query_comparison = mysqli_query($koneksi, "
                SELECT 
                    (SELECT COUNT(*) FROM barang WHERE id_barang IN 
                        (SELECT id_barang FROM inventory WHERE tanggal >= '$last_month')
                    ) as barang_last_month,
                    (SELECT COUNT(*) FROM penjualan WHERE tanggal >= '$last_month') as penjualan_last_month,
                    (SELECT SUM(total_harga) FROM penjualan WHERE tanggal >= '$last_month') as pendapatan_last_month,
                    (SELECT COUNT(*) FROM penjualan WHERE tanggal >= '$today') as penjualan_yesterday
            ");
            
            $comp_data = mysqli_fetch_assoc($query_comparison);
            
            $barang_percent = 0;
            if ($comp_data['barang_last_month'] > 0) {
                $barang_percent = round((($total_barang - $comp_data['barang_last_month']) / $comp_data['barang_last_month']) * 100, 2);
            }
            
            $penjualan_percent = 0;
            if ($comp_data['penjualan_last_month'] > 0) {
                $penjualan_percent = round((($total_penjualan - $comp_data['penjualan_last_month']) / $comp_data['penjualan_last_month']) * 100, 2);
            }
            
            $pendapatan_percent = 0;
            if ($comp_data['pendapatan_last_month'] > 0) {
                $pendapatan_percent = round((($total_pendapatan - $comp_data['pendapatan_last_month']) / $comp_data['pendapatan_last_month']) * 100, 2);
            }
            
            $transaksi_percent = 0;
            if ($comp_data['penjualan_yesterday'] > 0) {
                $transaksi_percent = round((($transaksi_hari_ini - $comp_data['penjualan_yesterday']) / $comp_data['penjualan_yesterday']) * 100, 2);
            }
            
            $status_cards = [
              [
                'title' => 'Total Barang',
                'value' => $total_barang,
                'icon' => 'mdi-cube-outline',
                'color' => 'primary',
                'percent' => $barang_percent . '%',
                'trend' => $barang_percent >= 0 ? 'up' : 'down',
                'subtitle' => 'Dibandingkan Bulan Lalu'
              ],
              [
                'title' => 'Total Penjualan',
                'value' => $total_penjualan,
                'icon' => 'mdi-cart-outline',
                'color' => 'info',
                'percent' => $penjualan_percent . '%',
                'trend' => $penjualan_percent >= 0 ? 'up' : 'down',
                'subtitle' => 'Dibandingkan Bulan Lalu'
              ],
              [
                'title' => 'Pendapatan',
                'value' => 'Rp ' . number_format($total_pendapatan, 0, ',', '.'),
                'icon' => 'mdi-currency-usd',
                'color' => 'success',
                'percent' => $pendapatan_percent . '%',
                'trend' => $pendapatan_percent >= 0 ? 'up' : 'down',
                'subtitle' => 'Dibandingkan Bulan Lalu'
              ],
              [
                'title' => 'Transaksi Hari Ini',
                'value' => $transaksi_hari_ini,
                'icon' => 'mdi-receipt',
                'color' => 'warning',
                'percent' => $transaksi_percent . '%',
                'trend' => $transaksi_percent >= 0 ? 'up' : 'down',
                'subtitle' => 'Dibandingkan Kemarin'
              ]
            ];
            
            foreach ($status_cards as $card) {
              echo '<div class="col-sm-6 col-md-6 col-lg-3 mb-4">';
              echo '<div class="card card-rounded">';
              echo '<div class="card-body">';
              echo '<div class="d-flex align-items-center justify-content-between mb-3">';
              echo '<div class="d-flex align-items-center">';
              echo '<div class="avatar avatar-md bg-light-' . $card['color'] . ' rounded">';
              echo '<i class="mdi ' . $card['icon'] . ' text-' . $card['color'] . ' fs-4"></i>';
              echo '</div>';
              echo '<div class="ms-3">';
              echo '<h6 class="mb-0">' . $card['title'] . '</h6>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
              echo '<div class="row">';
              echo '<div class="col-12">';
              echo '<h2 class="mb-0 fw-bold">' . $card['value'] . '</h2>';
              echo '<p class="text-muted mb-0 mt-2">';
              echo '<span class="badge badge-' . ($card['trend'] == 'up' ? 'success' : 'danger') . ' me-1">';
              echo '<i class="mdi mdi-arrow-' . ($card['trend'] == 'up' ? 'up' : 'down') . '-bold"></i> ' . $card['percent'] . '</span> ';
              echo $card['subtitle'];
              echo '</p>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
            }
            ?>
          </div>

          <div class="row">
            <div class="col-lg-8 mb-4">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                      <h4 class="card-title card-title-dash">Grafik Penjualan</h4>
                      <p class="card-subtitle card-subtitle-dash">Penjualan 6 bulan terakhir</p>
                    </div>
                    <div class="dropdown">
                      <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="mdi mdi-calendar"></i> Filter
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="#">6 Bulan Terakhir</a></li>
                        <li><a class="dropdown-item" href="#">1 Tahun Terakhir</a></li>
                        <li><a class="dropdown-item" href="#">2 Tahun Terakhir</a></li>
                      </ul>
                    </div>
                  </div>
                  <div>
                    <canvas id="salesChart" style="height: 300px;"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 mb-4">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title card-title-dash">Produk Terlaris</h4>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                        Bulan Ini
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                        <li><a class="dropdown-item" href="#">Hari Ini</a></li>
                        <li><a class="dropdown-item" href="#">Minggu Ini</a></li>
                        <li><a class="dropdown-item" href="#">Bulan Ini</a></li>
                      </ul>
                    </div>
                  </div>
                  <div>
                    <canvas id="topProductsChart" style="height: 300px;"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12 mb-4">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                      <h4 class="card-title card-title-dash">Transaksi Terbaru</h4>
                      <p class="card-subtitle card-subtitle-dash">Daftar transaksi penjualan terbaru</p>
                    </div>
                    <div>
                      <a href="index.php?page=penjualan" class="btn btn-primary btn-lg text-white">
                        <i class="mdi mdi-eye"></i> Lihat Semua
                      </a>
                    </div>
                  </div>
                  <div class="table-responsive mt-3">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th class="border-bottom-0">Kode</th>
                          <th class="border-bottom-0">Tanggal</th>
                          <th class="border-bottom-0">Total</th>
                          <th class="border-bottom-0">Kasir</th>
                          <th class="border-bottom-0">Status</th>
                          <th class="border-bottom-0">Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $query_recent = mysqli_query($koneksi, "
                          SELECT p.*, u.username 
                          FROM penjualan p 
                          JOIN users u ON p.id_users = u.id_users 
                          ORDER BY p.tanggal DESC 
                          LIMIT 5
                        ");
                        
                        if ($query_recent && mysqli_num_rows($query_recent) > 0) {
                          while ($row = mysqli_fetch_assoc($query_recent)) {
                            echo '<tr>';
                            echo '<td>' . $row['kode_penjualan'] . '</td>';
                            echo '<td>' . date('d/m/Y H:i', strtotime($row['tanggal'])) . '</td>';
                            echo '<td>Rp ' . number_format($row['total_harga'], 0, ',', '.') . '</td>';
                            echo '<td>' . $row['username'] . '</td>';
                            echo '<td><span class="badge bg-success">Selesai</span></td>';
                            echo '<td>';
                            echo '<a href="index.php?page=detail-penjualan&id=' . $row['id_penjualan'] . '" class="btn btn-sm btn-info text-white"><i class="mdi mdi-eye"></i></a>';
                            echo '</td>';
                            echo '</tr>';
                          }
                        } else {
                          echo '<tr><td colspan="6" class="text-center">Tidak ada data transaksi</td></tr>';
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                      <h4 class="card-title card-title-dash">Peringatan Stok</h4>
                      <p class="card-subtitle card-subtitle-dash">Daftar barang dengan stok menipis</p>
                    </div>
                    <div>
                      <a href="index.php?page=inventory" class="btn btn-primary btn-lg text-white">
                        <i class="mdi mdi-eye"></i> Kelola Inventory
                      </a>
                    </div>
                  </div>
                  <div class="table-responsive mt-3">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th class="border-bottom-0">Kode</th>
                          <th class="border-bottom-0">Nama Barang</th>
                          <th class="border-bottom-0">Kategori</th>
                          <th class="border-bottom-0">Stok Saat Ini</th>
                          <th class="border-bottom-0">Status</th>
                          <th class="border-bottom-0">Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $query_stok = mysqli_query($koneksi, "
                          SELECT b.*, i.sisa_stok
                          FROM barang b
                          JOIN (
                            SELECT id_barang, MAX(id_inventory) as latest_id
                            FROM inventory
                            GROUP BY id_barang
                          ) latest ON b.id_barang = latest.id_barang
                          JOIN inventory i ON latest.latest_id = i.id_inventory
                          WHERE i.sisa_stok < 10
                          ORDER BY i.sisa_stok ASC
                          LIMIT 5
                        ");
                        
                        if ($query_stok && mysqli_num_rows($query_stok) > 0) {
                          while ($row = mysqli_fetch_assoc($query_stok)) {
                            echo '<tr>';
                            echo '<td>' . $row['kode_barang'] . '</td>';
                            echo '<td>' . $row['nama_barang'] . '</td>';
                            echo '<td>' . $row['kategori'] . '</td>';
                            echo '<td>' . $row['sisa_stok'] . '</td>';
                            
                            $status_class = 'danger';
                            $status_text = 'Kritis';
                            
                            if ($row['sisa_stok'] >= 5) {
                              $status_class = 'warning';
                              $status_text = 'Menipis';
                            }
                            
                            echo '<td><span class="badge bg-' . $status_class . '">' . $status_text . '</span></td>';
                            echo '<td>';
                            echo '<a href="index.php?page=tambah-stok&id=' . $row['id_barang'] . '" class="btn btn-sm btn-success text-white"><i class="mdi mdi-plus"></i> Tambah Stok</a>';
                            echo '</td>';
                            echo '</tr>';
                          }
                        } else {
                          echo '<tr><td colspan="6" class="text-center">Tidak ada barang dengan stok menipis</td></tr>';
                        }
                        ?>
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
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php
      $months = [];
      $sales_data = [];
      
      for($i = 5; $i >= 0; $i--) {
          $month = date('M', strtotime("-$i month"));
          $year_month = date('Y-m', strtotime("-$i month"));
          array_push($months, $month);
          
          $query_monthly = mysqli_query($koneksi, "
              SELECT COUNT(*) as count 
              FROM penjualan 
              WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$year_month'
          ");
          
          if($query_monthly && $monthly_data = mysqli_fetch_assoc($query_monthly)) {
              array_push($sales_data, $monthly_data['count']);
          } else {
              array_push($sales_data, 0);
          }
      }
      
      $top_products_query = mysqli_query($koneksi, "
          SELECT b.nama_barang, SUM(pd.jumlah) as total_sold
          FROM penjualan_detail pd
          JOIN barang b ON pd.id_barang = b.id_barang
          JOIN penjualan p ON pd.id_penjualan = p.id_penjualan
          WHERE p.tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          GROUP BY pd.id_barang
          ORDER BY total_sold DESC
          LIMIT 4
      ");
      
      $product_names = [];
      $product_sales = [];
      
      if($top_products_query) {
          while($product = mysqli_fetch_assoc($top_products_query)) {
              array_push($product_names, $product['nama_barang']);
              array_push($product_sales, $product['total_sold']);
          }
      }
      
      if(empty($product_names)) {
          $product_names = ['Tidak ada data'];
          $product_sales = [0];
      }
      ?>
      
      const salesCtx = document.getElementById('salesChart').getContext('2d');
      const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
          labels: <?php echo json_encode($months); ?>,
          datasets: [{
            label: 'Penjualan',
            data: <?php echo json_encode($sales_data); ?>,
            fill: false,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
      
      const productsCtx = document.getElementById('topProductsChart').getContext('2d');
      const topProductsChart = new Chart(productsCtx, {
        type: 'doughnut',
        data: {
          labels: <?php echo json_encode($product_names); ?>,
          datasets: [{
            label: 'Produk Terlaris',
            data: <?php echo json_encode($product_sales); ?>,
            backgroundColor: [
              'rgba(255, 99, 132, 0.7)',
              'rgba(54, 162, 235, 0.7)',
              'rgba(255, 206, 86, 0.7)',
              'rgba(75, 192, 192, 0.7)'
            ],
            borderColor: [
              'rgba(255, 99, 132, 1)',
              'rgba(54, 162, 235, 1)',
              'rgba(255, 206, 86, 1)',
              'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
    });
  </script>
</body>

</html>