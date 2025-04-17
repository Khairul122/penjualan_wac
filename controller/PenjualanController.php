<?php
include 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_barang'])) {
    $kode = isset($_POST['kode_penjualan']) ? $_POST['kode_penjualan'] : '';
    $tanggal = date('Y-m-d H:i:s');
    $id_user = isset($_POST['id_users']) ? $_POST['id_users'] : (isset($_SESSION['id_users']) ? $_SESSION['id_users'] : null);
    $subtotal = isset($_POST['subtotal']) ? (float) $_POST['subtotal'] : 0;
    $total = isset($_POST['total_harga']) ? (float) $_POST['total_harga'] : 0;
    $bayar = isset($_POST['nominal_bayar']) ? preg_replace("/[^0-9]/", "", $_POST['nominal_bayar']) : 0;
    $kembalian = isset($_POST['kembalian']) ? (float) $_POST['kembalian'] : ($bayar - $total);

    if (!$kode || !$id_user) {
        $msg = !$kode ? 'Kode penjualan kosong' : 'ID user tidak ditemukan';
        echo "<script>alert('Data tidak lengkap: $msg');window.history.back();</script>";
        exit;
    }

    mysqli_begin_transaction($koneksi);
    try {
        $query_penjualan = "INSERT INTO penjualan (kode_penjualan, tanggal, subtotal, total_harga, nominal_bayar, kembalian, id_users)
                      VALUES ('$kode', '$tanggal', '$subtotal', '$total', '$bayar', '$kembalian', '$id_user')";
        
        if (!mysqli_query($koneksi, $query_penjualan)) {
            throw new Exception(mysqli_error($koneksi));
        }
        
        $id_penjualan = mysqli_insert_id($koneksi);
        
        
        foreach ($_POST['id_barang'] as $i => $id_barang) {
            $jumlah = isset($_POST['jumlah'][$i]) ? $_POST['jumlah'][$i] : 0;
            
            $product_query = mysqli_query($koneksi, "SELECT harga_jual FROM barang WHERE id_barang = '$id_barang'");
            $product_data = mysqli_fetch_assoc($product_query);
            $harga_satuan = $product_data['harga_jual'];
            
            $total_harga_item = $harga_satuan * $jumlah;

            $query_detail = "INSERT INTO penjualan_detail (id_penjualan, id_barang, jumlah, harga_satuan, total_harga)
                      VALUES ('$id_penjualan', '$id_barang', '$jumlah', '$harga_satuan', '$total_harga_item')";

            if (!mysqli_query($koneksi, $query_detail)) {
                throw new Exception(mysqli_error($koneksi));
            }
            
            $update_stok = "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = '$id_barang'";
            if (!mysqli_query($koneksi, $update_stok)) {
                throw new Exception(mysqli_error($koneksi));
            }
        }

        mysqli_commit($koneksi);
        echo "<script>alert('Transaksi berhasil disimpan');window.location.href='index.php?page=penjualan';</script>";
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('Gagal menyimpan transaksi: " . $e->getMessage() . "');window.history.back();</script>";
    }
    exit;
}

if (isset($_GET['method']) && $_GET['method'] === 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    mysqli_begin_transaction($koneksi);
    try {
        $details = mysqli_query($koneksi, "SELECT id_barang, jumlah FROM penjualan_detail WHERE id_penjualan = '$id'");
        
        while ($row = mysqli_fetch_assoc($details)) {
            $update_stok = "UPDATE barang SET stok = stok + {$row['jumlah']} WHERE id_barang = '{$row['id_barang']}'";
            if (!mysqli_query($koneksi, $update_stok)) {
                throw new Exception(mysqli_error($koneksi));
            }
        }
        
        mysqli_query($koneksi, "DELETE FROM penjualan_detail WHERE id_penjualan = '$id'");
        if (mysqli_error($koneksi)) {
            throw new Exception(mysqli_error($koneksi));
        }
        
        mysqli_query($koneksi, "DELETE FROM penjualan WHERE id_penjualan = '$id'");
        if (mysqli_error($koneksi)) {
            throw new Exception(mysqli_error($koneksi));
        }
        
        mysqli_commit($koneksi);
        echo "<script>alert('Data penjualan berhasil dihapus');window.location.href='index.php?page=penjualan';</script>";
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('Gagal menghapus data: " . $e->getMessage() . "');window.location.href='index.php?page=penjualan';</script>";
    }
    exit;
}

if (isset($_GET['method']) && $_GET['method'] === 'DETAIL' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $header_query = "SELECT p.*, u.username 
                    FROM penjualan p 
                    JOIN users u ON p.id_users = u.id_users 
                    WHERE p.id_penjualan = '$id'";
    
    $detail_query = "SELECT pd.*, b.nama_barang, b.kode_barang 
                    FROM penjualan_detail pd 
                    JOIN barang b ON pd.id_barang = b.id_barang 
                    WHERE pd.id_penjualan = '$id'";
    
    $header_result = mysqli_query($koneksi, $header_query);
    $detail_result = mysqli_query($koneksi, $detail_query);
    
    $header = mysqli_fetch_assoc($header_result);
    $details = [];
    
    while ($row = mysqli_fetch_assoc($detail_result)) {
        $details[] = $row;
    }
    
    if ($header) {
        echo json_encode([
            'header' => $header,
            'details' => $details
        ]);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
    exit;
}

echo "<script>alert('Akses tidak valid');window.location.href='index.php?page=penjualan';</script>";
exit;
?>