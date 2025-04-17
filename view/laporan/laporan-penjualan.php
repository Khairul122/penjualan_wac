<?php
ob_start();

require_once('tcpdf/tcpdf.php');
include 'koneksi.php';

$from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

$company_name = "Grosir Serena AD";
$company_address = "Buluh Kasok, Kabupaten Padang Pariaman, Sumatera Barat";
$company_phone = "";

$query_pimpinan = mysqli_query($koneksi, "SELECT username FROM users WHERE level='pimpinan' LIMIT 1");
$pimpinan = "Pimpinan";
if($query_pimpinan && mysqli_num_rows($query_pimpinan) > 0) {
    $row_pimpinan = mysqli_fetch_assoc($query_pimpinan);
    $pimpinan = $row_pimpinan['username'];
}

class PDFWAC extends TCPDF {
    protected $header_title;
    protected $header_subtitle;
    protected $header_periode;
    protected $header_company_name;
    protected $header_company_address;
    protected $header_company_phone;
    
    public function setCustomHeaderData($title, $subtitle, $periode, $company_name, $company_address, $company_phone) {
        $this->header_title = $title;
        $this->header_subtitle = $subtitle;
        $this->header_periode = $periode;
        $this->header_company_name = $company_name;
        $this->header_company_address = $company_address;
        $this->header_company_phone = $company_phone;
    }
    
    public function Header() {
        $this->SetFont('helvetica', 'B', 16);
        
        $this->Cell(0, 7, $this->header_company_name, 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, $this->header_company_address, 0, 1, 'C');
        if(!empty($this->header_company_phone)) {
            $this->Cell(0, 5, $this->header_company_phone, 0, 1, 'C');
        }
        
        $this->Line(10, 30, $this->getPageWidth() - 10, 30);
        
        $this->Ln(10); // Memberikan jarak 10px
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, $this->header_title, 0, 1, 'C');
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 8, $this->header_subtitle, 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, $this->header_periode, 0, 1, 'C');
        $this->Ln(3);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $currentDateTime = date('d-m-Y H:i:s');
        $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . ' - Dicetak pada: ' . $currentDateTime, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new PDFWAC('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->setCustomHeaderData(
    'LAPORAN PENJUALAN',
    'Detail Transaksi Penjualan Barang',
    'Periode: ' . date('d F Y', strtotime($from)) . ' s.d ' . date('d F Y', strtotime($to)),
    $company_name,
    $company_address,
    $company_phone
);

$pdf->SetCreator('Sistem WAC');
$pdf->SetAuthor('Admin WAC');
$pdf->SetTitle('Laporan Penjualan');
$pdf->SetSubject('Laporan Penjualan Periode ' . date('d/m/Y', strtotime($from)) . ' - ' . date('d/m/Y', strtotime($to)));
$pdf->SetKeywords('Laporan, Penjualan, WAC');

$pdf->SetMargins(10, 60, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);

$pdf->SetAutoPageBreak(TRUE, 45);

$pdf->AddPage();

$summary_query = mysqli_query($koneksi, "
    SELECT 
        p.kode_penjualan, 
        p.tanggal, 
        u.username as kasir,
        p.nominal_bayar,
        p.kembalian,
        p.total_harga,
        COUNT(pd.id_penjualan_detail) as jumlah_item
    FROM penjualan p
    JOIN penjualan_detail pd ON p.id_penjualan = pd.id_penjualan
    LEFT JOIN users u ON p.id_users = u.id_users
    WHERE DATE(p.tanggal) BETWEEN '$from' AND '$to'
    GROUP BY p.id_penjualan, p.kode_penjualan, p.tanggal, u.username, p.nominal_bayar, p.kembalian, p.total_harga
    ORDER BY p.tanggal ASC
");

if (!$summary_query) {
    $pdf->Cell(0, 10, 'Error: ' . mysqli_error($koneksi), 0, 1, 'C');
    $pdf->Output('Error_Laporan.pdf', 'I');
    exit;
}

$detail_query = mysqli_query($koneksi, "
    SELECT 
        p.id_penjualan,
        p.kode_penjualan, 
        p.tanggal, 
        b.kode_barang,
        b.nama_barang, 
        pd.jumlah, 
        pd.harga_satuan, 
        pd.total_harga
    FROM penjualan p
    JOIN penjualan_detail pd ON p.id_penjualan = pd.id_penjualan
    JOIN barang b ON pd.id_barang = b.id_barang
    WHERE DATE(p.tanggal) BETWEEN '$from' AND '$to'
    ORDER BY p.tanggal ASC, p.kode_penjualan ASC
");

if (!$detail_query) {
    $pdf->Cell(0, 10, 'Error: ' . mysqli_error($koneksi), 0, 1, 'C');
    $pdf->Output('Error_Laporan.pdf', 'I');
    exit;
}

$detail_data = [];
while ($row = mysqli_fetch_assoc($detail_query)) {
    $id_penjualan = $row['id_penjualan'];
    if (!isset($detail_data[$id_penjualan])) {
        $detail_data[$id_penjualan] = [];
    }
    $detail_data[$id_penjualan][] = $row;
}

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Ringkasan Penjualan', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Tanggal', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Kode Penjualan', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Jumlah Item', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Kasir', 1, 0, 'C', true);
$pdf->Cell(45, 8, 'Total', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$no = 1;
$grand_total = 0;
$total_transaksi = 0;
$total_items = 0;

while ($row = mysqli_fetch_assoc($summary_query)) {
    $fill = $no % 2 == 0;
    $bgColor = $fill ? 240 : 255;
    $pdf->SetFillColor($bgColor, $bgColor, $bgColor);
    
    $tanggal_indo = date('d-m-Y', strtotime($row['tanggal']));
    
    $pdf->Cell(10, 7, $no++, 1, 0, 'C', $fill);
    $pdf->Cell(30, 7, $tanggal_indo, 1, 0, 'C', $fill);
    $pdf->Cell(40, 7, $row['kode_penjualan'], 1, 0, 'L', $fill);
    $pdf->Cell(25, 7, $row['jumlah_item'] . ' item', 1, 0, 'C', $fill);
    $pdf->Cell(30, 7, $row['kasir'], 1, 0, 'L', $fill);
    $pdf->Cell(45, 7, 'Rp ' . number_format($row['total_harga'], 0, ',', '.'), 1, 1, 'R', $fill);
    
    $grand_total += $row['total_harga'];
    $total_transaksi++;
    $total_items += $row['jumlah_item'];
}

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(41, 128, 185);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(135, 7, 'TOTAL PENJUALAN (' . $total_transaksi . ' Transaksi / ' . $total_items . ' Item)', 1, 0, 'R', true);
$pdf->Cell(45, 7, 'Rp ' . number_format($grand_total, 0, ',', '.'), 1, 1, 'R', true);
$pdf->SetTextColor(0, 0, 0);

$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Detail Transaksi Penjualan', 0, 1, 'L');

$penjualan_query = mysqli_query($koneksi, "
    SELECT 
        p.id_penjualan,
        p.kode_penjualan, 
        p.tanggal, 
        u.username as kasir,
        p.nominal_bayar,
        p.kembalian,
        p.total_harga
    FROM penjualan p
    LEFT JOIN users u ON p.id_users = u.id_users
    WHERE DATE(p.tanggal) BETWEEN '$from' AND '$to'
    ORDER BY p.tanggal ASC
");

if (!$penjualan_query) {
    $pdf->Cell(0, 10, 'Error: ' . mysqli_error($koneksi), 0, 1, 'C');
    $pdf->Output('Error_Laporan.pdf', 'I');
    exit;
}

while ($penjualan = mysqli_fetch_assoc($penjualan_query)) {
    $id_penjualan = $penjualan['id_penjualan'];
    
    if (!isset($detail_data[$id_penjualan])) continue;
    
    $pdf->SetFillColor(52, 73, 94);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, 'Transaksi: ' . $penjualan['kode_penjualan'] . ' (' . date('d-m-Y H:i', strtotime($penjualan['tanggal'])) . ')', 1, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(30, 6, 'Kasir', 0, 0, 'L');
    $pdf->Cell(5, 6, ':', 0, 0, 'C');
    $pdf->Cell(50, 6, $penjualan['kasir'], 0, 0, 'L');
    
    $pdf->Cell(30, 6, 'Total', 0, 0, 'L');
    $pdf->Cell(5, 6, ':', 0, 0, 'C');
    $pdf->Cell(50, 6, 'Rp ' . number_format($penjualan['total_harga'], 0, ',', '.'), 0, 1, 'L');
    
    $pdf->Cell(30, 6, 'Bayar', 0, 0, 'L');
    $pdf->Cell(5, 6, ':', 0, 0, 'C');
    $pdf->Cell(50, 6, 'Rp ' . number_format($penjualan['nominal_bayar'], 0, ',', '.'), 0, 0, 'L');
    
    $pdf->Cell(30, 6, 'Kembalian', 0, 0, 'L');
    $pdf->Cell(5, 6, ':', 0, 0, 'C');
    $pdf->Cell(50, 6, 'Rp ' . number_format($penjualan['kembalian'], 0, ',', '.'), 0, 1, 'L');
    
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(52, 152, 219);
    $pdf->SetTextColor(255, 255, 255);
    
    $pdf->Cell(10, 7, 'No', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Kode', 1, 0, 'C', true);
    $pdf->Cell(60, 7, 'Nama Barang', 1, 0, 'C', true);
    $pdf->Cell(20, 7, 'Jumlah', 1, 0, 'C', true);
    $pdf->Cell(35, 7, 'Harga', 1, 0, 'C', true);
    $pdf->Cell(35, 7, 'Subtotal', 1, 1, 'C', true);
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    
    $no_detail = 1;
    $subtotal = 0;
    
    foreach ($detail_data[$id_penjualan] as $detail) {
        $fill = $no_detail % 2 == 0;
        $bgColor = $fill ? 240 : 255;
        $pdf->SetFillColor($bgColor, $bgColor, $bgColor);
        
        $pdf->Cell(10, 7, $no_detail++, 1, 0, 'C', $fill);
        $pdf->Cell(30, 7, $detail['kode_barang'], 1, 0, 'L', $fill);
        $pdf->Cell(60, 7, $detail['nama_barang'], 1, 0, 'L', $fill);
        $pdf->Cell(20, 7, $detail['jumlah'], 1, 0, 'C', $fill);
        $pdf->Cell(35, 7, 'Rp ' . number_format($detail['harga_satuan'], 0, ',', '.'), 1, 0, 'R', $fill);
        $pdf->Cell(35, 7, 'Rp ' . number_format($detail['total_harga'], 0, ',', '.'), 1, 1, 'R', $fill);
        
        $subtotal += $detail['total_harga'];
    }
    
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(41, 128, 185);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(155, 7, 'TOTAL', 1, 0, 'R', true);
    $pdf->Cell(35, 7, 'Rp ' . number_format($subtotal, 0, ',', '.'), 1, 1, 'R', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(5);
}

$pdf->Ln(10);
$tanggal_sekarang = date('d F Y');
$pdf->SetFont('helvetica', '', 10);

$pdf->SetX(140);
$pdf->Cell(0, 2, "Buluh Kasok, " . $tanggal_sekarang, 0, 1, 'L');

$pdf->Ln(2);
$pdf->SetX(140);
$pdf->Cell(0, 2, "Pimpinan", 0, 1, 'L');

$pdf->Ln(15);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetX(140);
$pdf->Cell(0, 5, $pimpinan, 0, 1, 'L');


ob_end_clean();

$pdf->Output('Laporan_Penjualan_' . date('Y-m-d') . '.pdf', 'I');
exit;