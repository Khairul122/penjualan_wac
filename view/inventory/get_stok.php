<?php
include 'koneksi.php';

if (isset($_GET['id_barang']) && !empty($_GET['id_barang'])) {
    $id_barang = mysqli_real_escape_string($koneksi, $_GET['id_barang']);
    
    $query = "SELECT sisa_stok FROM inventory WHERE id_barang = '$id_barang' ORDER BY tanggal DESC, id_inventory DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        echo json_encode([
            'sisa_stok' => (int)$data['sisa_stok'],
            'status' => 'success'
        ]);
    } else {
        echo json_encode([
            'sisa_stok' => 0,
            'status' => 'no_data',
            'query' => $query
        ]);
    }
} else {
    echo json_encode([
        'sisa_stok' => 0,
        'status' => 'no_id_barang'
    ]);
}
?>