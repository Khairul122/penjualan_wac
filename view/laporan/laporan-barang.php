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

$pdf->setCustomHeaderData(
    'LAPORAN DATA BARANG',
    'Daftar Barang dan Informasi Harga',
    $company_name,
    $company_address,
    $company_phone
);

$pdf->SetCreator('Sistem WAC');
$pdf->SetAuthor('Admin WAC');
$pdf->SetTitle('Laporan Data Barang');
$pdf->SetSubject('Laporan Data Barang');
$pdf->SetKeywords('Laporan, Barang, Inventory');

$pdf->SetMargins(10, 60, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);

$pdf->SetAutoPageBreak(TRUE, 45);

$pdf->AddPage();

$kategori_filter = $_GET['kategori'] ?? '';

if (!empty($kategori_filter)) {
    $barang_query = mysqli_query($koneksi, "SELECT * FROM barang WHERE kategori = '".mysqli_real_escape_string($koneksi, $kategori_filter)."' ORDER BY kategori, nama_barang ASC");
} else {
    $barang_query = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY kategori, nama_barang ASC");
}

if (!$barang_query) {
    $pdf->Cell(0, 10, 'Error: ' . mysqli_error($koneksi), 0, 1, 'C');
    $pdf->Output('Error_Laporan_Barang.pdf', 'I');
    exit;
}

$inventory_data = [];
$inventory_query = mysqli_query($koneksi, "
    SELECT id_barang, MAX(id_inventory) as last_id 
    FROM inventory 
    GROUP BY id_barang
");

if ($inventory_query) {
    while ($row = mysqli_fetch_assoc($inventory_query)) {
        $detail_query = mysqli_query($koneksi, "
            SELECT sisa_stok 
            FROM inventory 
            WHERE id_inventory = '{$row['last_id']}'
        ");
        if ($detail_query && $detail = mysqli_fetch_assoc($detail_query)) {
            $inventory_data[$row['id_barang']] = $detail['sisa_stok'];
        } else {
            $inventory_data[$row['id_barang']] = 0;
        }
    }
}

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Data Barang per ' . date('d F Y'), 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Kode Barang', 1, 0, 'C', true);
$pdf->Cell(70, 8, 'Nama Barang', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Kategori', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Satuan', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Stok', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Harga Beli', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Harga Jual', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$no = 1;
$total_nilai_beli = 0;
$total_nilai_jual = 0;
$total_stok = 0;

$current_kategori = '';

while ($barang = mysqli_fetch_assoc($barang_query)) {
    if ($current_kategori != $barang['kategori']) {
        $current_kategori = $barang['kategori'];
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(210, 220, 220);
        $pdf->SetFont('helvetica', '', 9);
        
        $label_kategori = !empty($_GET['kategori']) ? $_GET['kategori'] : 'Semua Kategori';
        
        $pdf->MultiCell(
            270,
            8,
            'Kategori: ' . $label_kategori,
            1,
            'L',
            true,
            1,
            '',
            '',
            true,
            0,
            false,
            true,
            8,
            'M'
        );
        $pdf->SetFont('helvetica', '', 9);
    }

    $fill = $no % 2 == 0;
    $bgColor = $fill ? 240 : 255;
    $pdf->SetFillColor($bgColor, $bgColor, $bgColor);

    $harga_beli = $barang['harga_beli'];
    $harga_jual = $barang['harga_jual'];
    $stok = isset($inventory_data[$barang['id_barang']]) ? $inventory_data[$barang['id_barang']] : 0;

    $pdf->Cell(10, 7, $no++, 1, 0, 'C', $fill);
    $pdf->Cell(30, 7, $barang['kode_barang'], 1, 0, 'C', $fill);
    $pdf->Cell(70, 7, $barang['nama_barang'], 1, 0, 'L', $fill);
    $pdf->Cell(35, 7, $barang['kategori'], 1, 0, 'L', $fill);
    $pdf->Cell(20, 7, $barang['satuan'], 1, 0, 'C', $fill);
    $pdf->Cell(25, 7, $stok, 1, 0, 'C', $fill);
    $pdf->Cell(40, 7, 'Rp ' . number_format($harga_beli, 0, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell(40, 7, 'Rp ' . number_format($harga_jual, 0, ',', '.'), 1, 1, 'R', $fill);

    $total_nilai_beli += $harga_beli;
    $total_nilai_jual += $harga_jual;
    $total_stok += $stok;
}

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(41, 128, 185);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(165, 8, 'TOTAL NILAI BARANG', 1, 0, 'R', true);
$pdf->Cell(25, 8, $total_stok, 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Rp ' . number_format($total_nilai_beli, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell(40, 8, 'Rp ' . number_format($total_nilai_jual, 0, ',', '.'), 1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Informasi Tambahan:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 6, '- Total Jumlah Barang: ' . ($no - 1) . ' item', 0, 1, 'L');
$pdf->Cell(0, 6, '- Total Stok Tersedia: ' . $total_stok . ' unit', 0, 1, 'L');
$pdf->Cell(0, 6, '- Total Nilai Persediaan (Harga Beli): Rp ' . number_format($total_nilai_beli * $total_stok, 0, ',', '.'), 0, 1, 'L');

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

$pdf->Output('Laporan_Barang_' . date('Y-m-d') . '.pdf', 'I');
exit;