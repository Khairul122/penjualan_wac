<?php
ob_start();

require_once('tcpdf/tcpdf.php');
include 'koneksi.php';

$company_name = "Grosir Serena AD";
$company_address = "Buluh Kasok, Kabupaten Padang Pariaman, Sumatera Barat";
$company_phone = "";

$query_pimpinan = mysqli_query($koneksi, "SELECT username FROM users WHERE level='pimpinan' LIMIT 1");
$pimpinan = "Pimpinan";
if ($query_pimpinan && mysqli_num_rows($query_pimpinan) > 0) {
    $row_pimpinan = mysqli_fetch_assoc($query_pimpinan);
    $pimpinan = $row_pimpinan['username'];
}

class PDFWAC extends TCPDF
{
    protected $header_title;
    protected $header_subtitle;
    protected $header_company_name;
    protected $header_company_address;
    protected $header_company_phone;

    public function setCustomHeaderData($title, $subtitle, $company_name, $company_address, $company_phone)
    {
        $this->header_title = $title;
        $this->header_subtitle = $subtitle;
        $this->header_company_name = $company_name;
        $this->header_company_address = $company_address;
        $this->header_company_phone = $company_phone;
    }

    public function Header()
    {
        $this->SetFont('helvetica', 'B', 16);

        $this->Cell(0, 7, $this->header_company_name, 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, $this->header_company_address, 0, 1, 'C');
        if (!empty($this->header_company_phone)) {
            $this->Cell(0, 5, $this->header_company_phone, 0, 1, 'C');
        }

        $this->Line(10, 30, $this->getPageWidth() - 10, 30);

        $this->Ln(10);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, $this->header_title, 0, 1, 'C');
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 8, $this->header_subtitle, 0, 1, 'C');
        $this->Ln(3);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $currentDateTime = date('d-m-Y H:i:s');
        $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . ' - Dicetak pada: ' . $currentDateTime, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new PDFWAC('L', 'mm', 'A4', true, 'UTF-8', false);

$fromDate = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d');
$toDate = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

$periode = '';
if ($fromDate == $toDate) {
    $periode = 'Tanggal ' . date('d F Y', strtotime($fromDate));
} else {
    $periode = 'Periode ' . date('d F Y', strtotime($fromDate)) . ' - ' . date('d F Y', strtotime($toDate));
}

$pdf->setCustomHeaderData(
    'LAPORAN DATA INVENTORY',
    $periode,
    $company_name,
    $company_address,
    $company_phone
);

$pdf->SetCreator('Sistem WAC');
$pdf->SetAuthor('Admin WAC');
$pdf->SetTitle('Laporan Data Inventory');
$pdf->SetSubject('Laporan Data Inventory');
$pdf->SetKeywords('Laporan, Inventory, Barang');

$pdf->SetMargins(10, 60, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);

$pdf->SetAutoPageBreak(TRUE, 45);

$pdf->AddPage();

$inventory_query = mysqli_query($koneksi, "
    SELECT i.*, b.kode_barang, b.nama_barang, b.kategori, b.satuan, b.harga_beli, b.harga_jual
    FROM inventory i
    JOIN barang b ON i.id_barang = b.id_barang
    WHERE DATE(i.tanggal) BETWEEN '".mysqli_real_escape_string($koneksi, $fromDate)."' AND '".mysqli_real_escape_string($koneksi, $toDate)."'
    ORDER BY i.tanggal DESC
");

if (!$inventory_query) {
    $pdf->Cell(0, 10, 'Error: ' . mysqli_error($koneksi), 0, 1, 'C');
    $pdf->Output('Error_Laporan_Inventory.pdf', 'I');
    exit;
}

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Data Transaksi Inventory', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Tanggal', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Kode Trans', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Jenis', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Kode Barang', 1, 0, 'C', true);
$pdf->Cell(60, 8, 'Nama Barang', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Stok Akhir', 1, 0, 'C', true);
$pdf->Cell(55, 8, 'Keterangan', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$no = 1;
$total_masuk = 0;
$total_keluar = 0;

while ($data = mysqli_fetch_assoc($inventory_query)) {
    $fill = $no % 2 == 0;
    $bgColor = $fill ? 240 : 255;
    $pdf->SetFillColor($bgColor, $bgColor, $bgColor);

    $tanggal = date('d-m-Y H:i', strtotime($data['tanggal']));
    
    if ($data['jenis_transaksi'] == 'masuk') {
        $total_masuk += $data['jumlah'];
    } else {
        $total_keluar += $data['jumlah'];
    }

    $pdf->Cell(10, 7, $no++, 1, 0, 'C', $fill);
    $pdf->Cell(30, 7, $tanggal, 1, 0, 'C', $fill);
    $pdf->Cell(25, 7, $data['kode_transaksi'], 1, 0, 'C', $fill);
    
    $jenis_transaksi = ucfirst($data['jenis_transaksi']);
    $textColor = ($data['jenis_transaksi'] == 'masuk') ? '0,128,0' : '220,50,50';
    $pdf->SetTextColor($textColor);
    $pdf->Cell(20, 7, $jenis_transaksi, 1, 0, 'C', $fill);
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->Cell(30, 7, $data['kode_barang'], 1, 0, 'C', $fill);
    $pdf->Cell(60, 7, $data['nama_barang'], 1, 0, 'L', $fill);
    $pdf->Cell(20, 7, $data['jumlah'], 1, 0, 'C', $fill);
    $pdf->Cell(20, 7, $data['sisa_stok'], 1, 0, 'C', $fill);
    $pdf->Cell(55, 7, $data['keterangan'], 1, 1, 'L', $fill);
}

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(41, 128, 185);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(175, 8, 'TOTAL TRANSAKSI', 1, 0, 'R', true);
$pdf->Cell(40, 8, 'Masuk: ' . $total_masuk, 1, 0, 'C', true);
$pdf->Cell(55, 8, 'Keluar: ' . $total_keluar, 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Informasi Tambahan:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 6, '- Total Jumlah Transaksi: ' . ($no - 1) . ' transaksi', 0, 1, 'L');
$pdf->Cell(0, 6, '- Total Barang Masuk: ' . $total_masuk . ' unit', 0, 1, 'L');
$pdf->Cell(0, 6, '- Total Barang Keluar: ' . $total_keluar . ' unit', 0, 1, 'L');
$pdf->Cell(0, 6, '- Total Perubahan Stok: ' . ($total_masuk - $total_keluar) . ' unit', 0, 1, 'L');

$pdf->Ln(10);
$tanggal_sekarang = date('d F Y');
$pdf->SetFont('helvetica', '', 10);

$ttdX = 200;

$pdf->SetXY($ttdX, $pdf->GetY());
$pdf->Cell(0, 5, "Buluh Kasok, " . $tanggal_sekarang, 0, 1, 'L');

$pdf->Ln(2);
$pdf->SetX($ttdX);
$pdf->Cell(0, 5, "Pimpinan", 0, 1, 'L');

$pdf->Ln(15);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetX($ttdX);
$pdf->Cell(0, 5, $pimpinan, 0, 1, 'L');

ob_end_clean();

$pdf->Output('Laporan_Inventory_' . date('Y-m-d') . '.pdf', 'I');
exit;   