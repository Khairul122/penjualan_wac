<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

if (preg_match('/^controller\//', $page)) {
    $controller = $page . '.php';
    if (file_exists($controller)) {
        include $controller;
        exit;
    } else {
        echo "<script>alert('Controller tidak ditemukan'); window.location.href='index.php?page=login';</script>";
        exit;
    }
}

switch ($page) {
    case 'login':
        include 'view/login.php';
        break;
    case 'dashboard':
        include 'view/dashboard.php';
        break;

    // Barang
    case 'barang':
        include 'view/barang/index.php';
        break;
    case 'tambah-data':
        include 'view/barang/tambah-data.php';
        break;
    case 'edit-data':
        include 'view/barang/edit-data.php';
        break;

    // Penjualan
    case 'penjualan':
        include 'view/penjualan/index.php';
        break;
    case 'tambah-penjualan':
        include 'view/penjualan/tambah-data.php';
        break;
    case 'edit-penjualan':
        include 'view/penjualan/edit-data.php';
        break;
    case 'detail-penjualan':
        include 'view/penjualan/detail-data.php';
        break;
    case 'invoice':
        include 'view/penjualan/invoice.php';
        break;

    // Inventory
    case 'inventory':
        include 'view/inventory/index.php';
        break;
    case 'tambah-inventory':
        include 'view/inventory/tambah-data.php';
        break;
    case 'edit-inventory':
        include 'view/inventory/edit-data.php';
        break;

    // WAC
    case 'wac':
        include 'view/wac/index.php';
        break;

    // Laporan
    case 'laporan':
        include 'view/laporan/index.php';
        break;
    case 'laporan-penjualan':
        include 'view/laporan/laporan-penjualan.php';
        break;
    case 'laporan-inventory':
        include 'view/laporan/laporan-inventory.php';
        break;
    case 'laporan-barang':
        include 'view/laporan/laporan-barang.php';
        break;
    case 'laporan-wac':
        include 'view/laporan/laporan-wac.php';
        break;

    // Default login
    default:
        include 'view/login.php';
        break;
}
