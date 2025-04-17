<?php
require_once('TCPDF/tcpdf.php');
include 'koneksi.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    echo "<script>alert('ID penjualan tidak ditemukan');window.history.back();</script>";
    exit;
}

$header_query = "SELECT 
                  p.id_penjualan,
                  p.kode_penjualan, 
                  p.tanggal,
                  p.subtotal,
                  p.total_harga,
                  p.nominal_bayar,
                  p.kembalian,
                  u.username
                FROM penjualan p
                LEFT JOIN users u ON p.id_users = u.id_users
                WHERE p.id_penjualan = '$id'";

$header_result = mysqli_query($koneksi, $header_query);
$header = mysqli_fetch_assoc($header_result);

if (!$header) {
    echo "<script>alert('Data penjualan tidak ditemukan');window.history.back();</script>";
    exit;
}

$detail_query = "SELECT 
                  pd.id_penjualan_detail,
                  b.nama_barang,
                  pd.harga_satuan,
                  pd.jumlah,
                  pd.total_harga as subtotal
                FROM penjualan_detail pd
                JOIN barang b ON pd.id_barang = b.id_barang
                WHERE pd.id_penjualan = '$id'
                ORDER BY pd.id_penjualan_detail";

$detail_result = mysqli_query($koneksi, $detail_query);

$count_query = "SELECT SUM(jumlah) as jumlah_total FROM penjualan_detail WHERE id_penjualan = '$id'";
$count_result = mysqli_query($koneksi, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$jumlah_total = $count_data['jumlah_total'];

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Toko Anda');
$pdf->SetTitle('Invoice ' . $header['kode_penjualan']);
$pdf->SetSubject('Invoice Pembelian');
$pdf->SetKeywords('Invoice, Pembelian, PDF');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->SetFont('helvetica', '', 10);

$pdf->AddPage();

$tanggal = new DateTime($header['tanggal']);
$tanggal_format = $tanggal->format('d-m-Y H:i');

$html = '
<style>
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 10px;
        font-size: 12px;
        line-height: 18px;
        font-family: helvetica;
    }
    .title {
        font-size: 18px;
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
    }
    .header {
        margin-bottom: 20px;
    }
    .header table {
        width: 100%;
    }
    .header td {
        vertical-align: top;
        padding: 5px;
    }
    table.items {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    table.items th {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        background-color: #f2f2f2;
    }
    table.items td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    .total-box {
        margin-top: 20px;
        text-align: right;
    }
    .total-box table td:first-child {
        text-align: right;
        padding-right: 10px;
    }
    .total-box table td:last-child {
        text-align: right;
    }
    .footer {
        margin-top: 30px;
        text-align: center;
        font-size: 10px;
    }
</style>

<div class="invoice-box">
    <div class="title">INVOICE PEMBELIAN</div>
    
    <div class="header">
        <table>
            <tr>
                <td width="60%">
                    <strong>Toko Anda</strong><br>
                    Alamat Jalan No. 123<br>
                    Telepon: 021-1234567<br>
                    Email: info@tokoanda.com
                </td>
                <td width="40%">
                    <strong>Nomor Invoice:</strong> ' . $header['kode_penjualan'] . '<br>
                    <strong>Tanggal:</strong> ' . $tanggal_format . '<br>
                    <strong>Kasir:</strong> ' . $header['username'] . '
                </td>
            </tr>
        </table>
    </div>
    
    <table class="items">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
mysqli_data_seek($detail_result, 0);
while ($row = mysqli_fetch_assoc($detail_result)) {
    $html .= '
            <tr>
                <td>' . $no++ . '</td>
                <td>' . $row['nama_barang'] . '</td>
                <td>Rp ' . number_format($row['harga_satuan'], 0, ',', '.') . '</td>
                <td>' . $row['jumlah'] . '</td>
                <td>Rp ' . number_format($row['subtotal'], 0, ',', '.') . '</td>
            </tr>';
}

$html .= '
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                <td><strong>' . $jumlah_total . '</strong></td>
                <td><strong>Rp ' . number_format($header['total_harga'], 0, ',', '.') . '</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="total-box">
        <table width="40%" style="float: right;">
            <tr>
                <td style="text-align: right;"><strong>Total:</strong></td>
                <td>Rp ' . number_format($header['total_harga'], 0, ',', '.') . '</td>
            </tr>
            <tr>
                <td style="text-align: right;"><strong>Nominal Bayar:</strong></td>
                <td>Rp ' . number_format($header['nominal_bayar'], 0, ',', '.') . '</td>
            </tr>
            <tr>
                <td style="text-align: right;"><strong>Kembalian:</strong></td>
                <td>Rp ' . number_format($header['kembalian'], 0, ',', '.') . '</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>
    
    <div class="footer">
        Terima kasih telah berbelanja di Toko Anda.<br>
        Barang yang sudah dibeli tidak dapat dikembalikan.
    </div>
</div>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('invoice_' . $header['kode_penjualan'] . '.pdf', 'I');
?>