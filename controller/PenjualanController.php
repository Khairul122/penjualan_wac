<?php

$requestUri = $_SERVER['REQUEST_URI'];
$isDirectAccess = (strpos($requestUri, 'PenjualanController.php') !== false);

if ($isDirectAccess && !isset($_SERVER['HTTP_REFERER'])) {
    header('location:../index.php');
    exit;
}

include_once 'koneksi.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_penjualan = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    mysqli_begin_transaction($koneksi);
    
    try {
        $query_kode = "SELECT kode_penjualan FROM penjualan WHERE id_penjualan = '$id_penjualan'";
        $result_kode = mysqli_query($koneksi, $query_kode);
        
        if (mysqli_num_rows($result_kode) == 0) {
            throw new Exception("Data penjualan tidak ditemukan");
        }
        
        $row_kode = mysqli_fetch_assoc($result_kode);
        $kode_penjualan = $row_kode['kode_penjualan'];
        
        $query_detail = "SELECT id_barang, jumlah FROM penjualan_detail WHERE id_penjualan = '$id_penjualan'";
        $result_detail = mysqli_query($koneksi, $query_detail);
        
        while ($row_detail = mysqli_fetch_assoc($result_detail)) {
            $barang_id = $row_detail['id_barang'];
            $qty = $row_detail['jumlah'];
            
            $query_stok = "SELECT sisa_stok FROM inventory 
                          WHERE id_barang = '$barang_id' 
                          ORDER BY id_inventory DESC LIMIT 1";
            $result_stok = mysqli_query($koneksi, $query_stok);
            
            if (mysqli_num_rows($result_stok) > 0) {
                $row_stok = mysqli_fetch_assoc($result_stok);
                $stok_terbaru = $row_stok['sisa_stok'];
                $sisa_stok = $stok_terbaru + $qty; 
                
                $query_inventory = "INSERT INTO inventory (id_barang, kode_transaksi, jenis_transaksi, jumlah, sisa_stok, keterangan) 
                                   VALUES ('$barang_id', '$kode_penjualan', 'masuk', '$qty', '$sisa_stok', 'Pembatalan penjualan')";
                
                if (!mysqli_query($koneksi, $query_inventory)) {
                    throw new Exception("Error saat update inventory: " . mysqli_error($koneksi));
                }
            } else {
                throw new Exception("Stok produk tidak ditemukan di inventory");
            }
        }
        
        $query_delete_detail = "DELETE FROM penjualan_detail WHERE id_penjualan = '$id_penjualan'";
        if (!mysqli_query($koneksi, $query_delete_detail)) {
            throw new Exception("Error saat menghapus detail penjualan: " . mysqli_error($koneksi));
        }
        
        $query_delete_penjualan = "DELETE FROM penjualan WHERE id_penjualan = '$id_penjualan'";
        if (!mysqli_query($koneksi, $query_delete_penjualan)) {
            throw new Exception("Error saat menghapus penjualan: " . mysqli_error($koneksi));
        }
        
        mysqli_commit($koneksi);

        echo "<script>
            alert('Data penjualan berhasil dihapus!');
            window.location.href = 'index.php?page=penjualan';
        </script>";
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);

        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.location.href = 'index.php?page=penjualan';
        </script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_penjualan = mysqli_real_escape_string($koneksi, $_POST['kode_penjualan']);
    $id_users = mysqli_real_escape_string($koneksi, $_POST['id_users']);
    $total_harga = mysqli_real_escape_string($koneksi, $_POST['total_harga']);
    $nominal_bayar = preg_replace('/[^0-9]/', '', $_POST['nominal_bayar']);
    $kembalian = $nominal_bayar - $total_harga;
    
    $id_barang = $_POST['id_barang'];
    $jumlah = $_POST['jumlah'];
    $subtotal = $_POST['subtotal'];
    
    mysqli_begin_transaction($koneksi);
    
    try {
        $query_penjualan = "INSERT INTO penjualan (kode_penjualan, total_harga, nominal_bayar, kembalian, id_users) 
                            VALUES ('$kode_penjualan', '$total_harga', '$nominal_bayar', '$kembalian', '$id_users')";
        
        if (!mysqli_query($koneksi, $query_penjualan)) {
            throw new Exception("Error: " . mysqli_error($koneksi));
        }

        $id_penjualan = mysqli_insert_id($koneksi);

        for ($i = 0; $i < count($id_barang); $i++) {
            $barang_id = mysqli_real_escape_string($koneksi, $id_barang[$i]);
            $qty = mysqli_real_escape_string($koneksi, $jumlah[$i]);
            $sub_total = mysqli_real_escape_string($koneksi, $subtotal[$i]);
 
            $query_harga = "SELECT harga_jual FROM barang WHERE id_barang = '$barang_id'";
            $result_harga = mysqli_query($koneksi, $query_harga);
            $row_harga = mysqli_fetch_assoc($result_harga);
            $harga_satuan = $row_harga['harga_jual'];
  
            $query_detail = "INSERT INTO penjualan_detail (id_penjualan, id_barang, jumlah, harga_satuan, total_harga) 
                            VALUES ('$id_penjualan', '$barang_id', '$qty', '$harga_satuan', '$sub_total')";
            
            if (!mysqli_query($koneksi, $query_detail)) {
                throw new Exception("Error: " . mysqli_error($koneksi));
            }

            $query_stok = "SELECT sisa_stok FROM inventory 
                          WHERE id_barang = '$barang_id' 
                          ORDER BY id_inventory DESC LIMIT 1";
            $result_stok = mysqli_query($koneksi, $query_stok);
            
            if (mysqli_num_rows($result_stok) > 0) {
                $row_stok = mysqli_fetch_assoc($result_stok);
                $stok_terbaru = $row_stok['sisa_stok'];
                $sisa_stok = $stok_terbaru - $qty;
                
                $query_inventory = "INSERT INTO inventory (id_barang, kode_transaksi, jenis_transaksi, jumlah, sisa_stok, keterangan) 
                                   VALUES ('$barang_id', '$kode_penjualan', 'keluar', '$qty', '$sisa_stok', 'Penjualan produk')";
                
                if (!mysqli_query($koneksi, $query_inventory)) {
                    throw new Exception("Error: " . mysqli_error($koneksi));
                }
            } else {
                throw new Exception("Stok produk tidak ditemukan di inventory");
            }
        }

        echo "<script>
            alert('Transaksi berhasil disimpan!');
            window.location.href = 'index.php?page=penjualan';
        </script>";
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);

        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.location.href = 'index.php?page=tambah-penjualan';
        </script>";
        exit;
    }
}