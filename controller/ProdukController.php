<?php
include 'koneksi.php';

// TAMBAH DATA BARANG 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_barang'])) {
    $kode_barang = $_POST['kode_barang'];
    $nama_barang = $_POST['nama_barang'];
    $kategori    = $_POST['kategori'];
    $satuan      = $_POST['satuan'];
    $harga_beli  = $_POST['harga_beli'];
    $harga_jual  = $_POST['harga_jual'];

    $query = "INSERT INTO barang (kode_barang, nama_barang, kategori, satuan, harga_beli, harga_jual)
              VALUES ('$kode_barang', '$nama_barang', '$kategori', '$satuan', '$harga_beli', '$harga_jual')";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data barang berhasil ditambahkan'); window.location.href='index.php?page=barang';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data barang'); window.history.back();</script>";
    }
    exit;
}

// EDIT DATA BARANG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'PUT') {
    $id_barang   = $_POST['id_barang'];
    $kode_barang = $_POST['kode_barang'];
    $nama_barang = $_POST['nama_barang'];
    $kategori    = $_POST['kategori'];
    $satuan      = $_POST['satuan'];
    $harga_beli  = $_POST['harga_beli'];
    $harga_jual  = $_POST['harga_jual'];

    $query = "UPDATE barang SET 
                kode_barang='$kode_barang',
                nama_barang='$nama_barang',
                kategori='$kategori',
                satuan='$satuan',
                harga_beli='$harga_beli',
                harga_jual='$harga_jual'
              WHERE id_barang='$id_barang'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data barang berhasil diperbarui'); window.location.href='index.php?page=barang';</script>";
    } else {
        echo "<script>alert('Gagal mengedit data barang'); window.history.back();</script>";
    }
    exit;
}

// HAPUS DATA BARANG
if ((isset($_GET['id']) && isset($_GET['method']) && $_GET['method'] === 'DELETE') ||
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'DELETE')
) {
    $id_barang = isset($_GET['id']) ? $_GET['id'] : $_POST['id_barang'];

    $query = "DELETE FROM barang WHERE id_barang='$id_barang'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data barang berhasil dihapus'); window.location.href='index.php?page=barang';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data barang'); window.history.back();</script>";
    }
    exit;
}

// Jika tidak ada aksi yang valid
echo "<script>alert('Akses tidak valid'); window.location.href='../index.php?page=barang';</script>";
exit;
