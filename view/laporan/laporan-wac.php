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
if ($query_pimpinan && mysqli_num_rows($query_pimpinan) > 0) {
    $row_pimpinan = mysqli_fetch_assoc($query_pimpinan);
    $pimpinan = $row_pimpinan['username'];
}

class PDFWAC extends TCPDF
{
    protected $header_title;
    protected $header_subtitle;
    protected $header_periode;
    protected $header_company_name;
    protected $header_company_address;
    protected $header_company_phone;

    public function setCustomHeaderData($title, $subtitle, $periode, $company_name, $company_address, $company_phone)
    {
        $this->header_title = $title;
        $this->header_subtitle = $subtitle;
        $this->header_periode = $periode;
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
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, $this->header_periode, 0, 1, 'C');
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

function hitung_wac($koneksi, $id_barang, $from_date, $to_date)
{
    $query = mysqli_query($koneksi, "SELECT i.jumlah, b.harga_beli, i.tanggal, i.kode_transaksi, 
                                   i.keterangan, i.jenis_transaksi, i.sisa_stok
                             FROM inventory i
                             JOIN barang b ON i.id_barang = b.id_barang
                             WHERE i.id_barang = '$id_barang'
                               AND (i.tanggal <= '$to_date')
                             ORDER BY i.tanggal ASC");

    $jumlah_total = 0;
    $nilai_total = 0;
    $nilai_wac = 0;
    $riwayat = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $jumlah = (int)$row['jumlah'];
        $harga = (float)$row['harga_beli'];
        $tanggal = $row['tanggal'];
        $kode = $row['kode_transaksi'];
        $keterangan = $row['keterangan'];
        $jenis = $row['jenis_transaksi'];
        $sisa_stok = (int)$row['sisa_stok'];

        if ($jenis == 'masuk') {
            $nilai = $jumlah * $harga;
            $nilai_total += $nilai;
            $jumlah_total += $jumlah;

            if ($jumlah_total > 0) {
                $nilai_wac = $nilai_total / $jumlah_total;
            }
        } else {
            $nilai = $jumlah * $nilai_wac;
            $nilai_total -= $nilai;
            $jumlah_total -= $jumlah;
        }

        if (strtotime($tanggal) >= strtotime($from_date)) {
            $riwayat[] = [
                'tanggal' => $tanggal,
                'kode' => $kode,
                'keterangan' => $keterangan,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'harga' => $harga,
                'nilai' => $nilai,
                'stok_saat_ini' => $jumlah_total,
                'nilai_total' => $nilai_total,
                'nilai_wac' => $nilai_wac
            ];
        }
    }

    return [
        'nilai_wac_akhir' => $nilai_wac,
        'jumlah_akhir' => $jumlah_total,
        'nilai_akhir' => $nilai_total,
        'riwayat' => $riwayat
    ];
}

$pdf = new PDFWAC('L', 'mm', 'A4', true, 'UTF-8', false);

$pdf->setCustomHeaderData(
    'LAPORAN WEIGHTED AVERAGE COST (WAC)',
    'Perhitungan Nilai Rata-Rata Tertimbang Persediaan Barang',
    'Periode: ' . date('d F Y', strtotime($from)) . ' s.d ' . date('d F Y', strtotime($to)),
    $company_name,
    $company_address,
    $company_phone
);

$pdf->SetCreator('Sistem WAC');
$pdf->SetAuthor('Admin WAC');
$pdf->SetTitle('Laporan WAC');
$pdf->SetSubject('Laporan WAC Periode ' . date('d/m/Y', strtotime($from)) . ' - ' . date('d/m/Y', strtotime($to)));
$pdf->SetKeywords('Laporan, WAC, Inventory');

$pdf->SetMargins(10, 60, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);

$pdf->SetAutoPageBreak(TRUE, 45);

$pdf->AddPage();

$barang_query = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");

if (!$barang_query) {
    $pdf->Cell(0, 10, 'Error: ' . mysqli_error($koneksi), 0, 1, 'C');
    $pdf->Output('Error_Laporan_WAC.pdf', 'I');
    exit;
}

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Daftar Perhitungan WAC Per Barang', 0, 1, 'L');
$pdf->Ln(2);

$has_data = false;

while ($barang = mysqli_fetch_assoc($barang_query)) {
    $id = $barang['id_barang'];
    $hasil_wac = hitung_wac($koneksi, $id, $from, $to);

    if (count($hasil_wac['riwayat']) > 0) {
        $has_data = true;

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetFillColor(41, 128, 185);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, $barang['kode_barang'] . ' - ' . $barang['nama_barang'], 1, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(40, 6, 'Kategori', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(60, 6, $barang['kategori'], 0, 0, 'L');

        $pdf->Cell(40, 6, 'Satuan', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(60, 6, $barang['satuan'], 0, 1, 'L');

        $pdf->Cell(40, 6, 'WAC Terakhir', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(60, 6, 'Rp ' . number_format($hasil_wac['nilai_wac_akhir'], 0, ',', '.'), 0, 0, 'L');

        $pdf->Cell(40, 6, 'Total Stok', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(60, 6, $hasil_wac['jumlah_akhir'] . ' ' . $barang['satuan'], 0, 1, 'L');

        $pdf->Ln(2);

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(52, 152, 219);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(10, 7, 'No', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Tanggal', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Kode Transaksi', 1, 0, 'C', true);
        $pdf->Cell(60, 7, 'Keterangan', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Jenis', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Jumlah', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Harga', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Nilai', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Stok', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'WAC', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $no_detail = 1;

        foreach ($hasil_wac['riwayat'] as $item) {
            $fill = $no_detail % 2 == 0;
            $bgColor = $fill ? 240 : 255;
            $pdf->SetFillColor($bgColor, $bgColor, $bgColor);

            $pdf->Cell(10, 7, $no_detail++, 1, 0, 'C', $fill);
            $pdf->Cell(25, 7, date('d-m-Y', strtotime($item['tanggal'])), 1, 0, 'C', $fill);
            $pdf->Cell(35, 7, $item['kode'], 1, 0, 'L', $fill);
            $pdf->Cell(60, 7, $item['keterangan'], 1, 0, 'L', $fill);

            if ($item['jenis'] == 'masuk') {
                $pdf->SetTextColor(0, 128, 0);
                $pdf->Cell(20, 7, 'Masuk', 1, 0, 'C', $fill);
                $pdf->SetTextColor(0, 0, 0);
            } else {
                $pdf->SetTextColor(255, 0, 0);
                $pdf->Cell(20, 7, 'Keluar', 1, 0, 'C', $fill);
                $pdf->SetTextColor(0, 0, 0);
            }

            $pdf->Cell(20, 7, $item['jumlah'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, 'Rp ' . number_format($item['harga'], 0, ',', '.'), 1, 0, 'R', $fill);
            $pdf->Cell(30, 7, 'Rp ' . number_format($item['nilai'], 0, ',', '.'), 1, 0, 'R', $fill);
            $pdf->Cell(20, 7, $item['stok_saat_ini'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, 'Rp ' . number_format($item['nilai_wac'], 0, ',', '.'), 1, 1, 'R', $fill);
        }

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(41, 128, 185);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(200, 7, 'NILAI AKHIR', 1, 0, 'R', true);
        $pdf->Cell(20, 7, $hasil_wac['jumlah_akhir'], 1, 0, 'C', true);
        $pdf->Cell(60, 7, 'Rp ' . number_format($hasil_wac['nilai_wac_akhir'], 0, ',', '.'), 1, 1, 'R', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(10);
    }
}

if (!$has_data) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 20, 'Tidak ada data WAC dalam periode yang dipilih', 0, 1, 'C');
}

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

$pdf->Output('Laporan_WAC_' . date('Y-m-d') . '.pdf', 'I');
exit;
