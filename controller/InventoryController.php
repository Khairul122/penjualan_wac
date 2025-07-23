<?php
include 'koneksi.php';

if (isset($_GET['get_stok']) && isset($_GET['id_barang'])) {
    $id_barang = mysqli_real_escape_string($koneksi, $_GET['id_barang']);
    $query = "SELECT sisa_stok FROM inventory WHERE id_barang = '$id_barang' ORDER BY tanggal DESC, id_inventory DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        echo json_encode(['sisa_stok' => (int)$data['sisa_stok']]);
    } else {
        echo json_encode(['sisa_stok' => 0]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_inventory'])) {
    $kode_transaksi = $_POST['kode_transaksi'];
    $id_barang = $_POST['id_barang'];
    $jenis = $_POST['jenis_transaksi'];
    $jumlah = (int) $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $stok_q = mysqli_query($koneksi, "SELECT sisa_stok FROM inventory WHERE id_barang='$id_barang' ORDER BY tanggal DESC, id_inventory DESC LIMIT 1");
    $stok_row = mysqli_fetch_assoc($stok_q);
    $stok_sebelumnya = $stok_row ? (int) $stok_row['sisa_stok'] : 0;

    if ($jenis === 'masuk') {
        $sisa_stok = $stok_sebelumnya + $jumlah;
    } else {
        if ($jumlah > $stok_sebelumnya) {
            echo "<script>alert('Stok tidak mencukupi!');window.history.back();</script>";
            exit;
        }
        $sisa_stok = $stok_sebelumnya - $jumlah;
    }

    $query = "INSERT INTO inventory (id_barang, kode_transaksi, jenis_transaksi, tanggal, jumlah, sisa_stok, keterangan)
              VALUES ('$id_barang', '$kode_transaksi', '$jenis', '$tanggal', '$jumlah', '$sisa_stok', '$keterangan')";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Transaksi inventory berhasil disimpan');window.location.href='index.php?page=inventory';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data inventory');window.history.back();</script>";
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_inventory'])) {
    $id_inventory = $_POST['id_inventory'];
    $kode_transaksi = $_POST['kode_transaksi'];
    $id_barang = $_POST['id_barang'];
    $jenis = $_POST['jenis_transaksi'];
    $jumlah = (int) $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $stok_q = mysqli_query($koneksi, "SELECT sisa_stok FROM inventory WHERE id_barang='$id_barang' AND id_inventory != '$id_inventory' ORDER BY tanggal DESC, id_inventory DESC LIMIT 1");
    $stok_row = mysqli_fetch_assoc($stok_q);
    $stok_sebelumnya = $stok_row ? (int) $stok_row['sisa_stok'] : 0;

    if ($jenis === 'masuk') {
        $sisa_stok = $stok_sebelumnya + $jumlah;
    } else {
        if ($jumlah > $stok_sebelumnya) {
            echo "<script>alert('Stok tidak mencukupi!');window.history.back();</script>";
            exit;
        }
        $sisa_stok = $stok_sebelumnya - $jumlah;
    }

    $query = "UPDATE inventory SET 
                id_barang = '$id_barang',
                jenis_transaksi = '$jenis',
                tanggal = '$tanggal',
                jumlah = '$jumlah',
                sisa_stok = '$sisa_stok',
                keterangan = '$keterangan'
              WHERE id_inventory = '$id_inventory'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data inventory berhasil diperbarui');window.location.href='index.php?page=inventory';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data inventory');window.history.back();</script>";
    }
    exit;
}

if (isset($_GET['method']) && $_GET['method'] === 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $delete = mysqli_query($koneksi, "DELETE FROM inventory WHERE id_inventory = '$id'");
    if ($delete) {
        echo "<script>alert('Data inventory berhasil dihapus');window.location.href='index.php?page=inventory';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data');window.history.back();</script>";
    }
    exit;
}

echo "<script>alert('Akses tidak valid');window.location.href='index.php?page=inventory';</script>";
exit;