<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Image as ImageStyle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class InventarisExporter
{
    private const NAMA_KAMPUS    = 'SEKOLAH TINGGI ILMU KOMPUTER';
    private const SINGKATAN      = 'STIKOM TUNAS BANGSA';
    private const SK             = 'SK MENDIKBUD RISTEK RI. NO. 513/E/O/2022 Tanggal 13 Juli 2022';
    private const PRODI          = 'Program Studi: Diploma 3 (D3): Manajemen Informatika, Komputerisasi Akuntansi | Sarjana (S1): Sistem Informasi, Teknik Informatika | Magister (S2): Informatika';
    private const DISCLAIMER     = 'Tidak dibenarkan memindahkan barang-barang yang ada pada daftar ini tanpa sepengetahuan penanggung jawab ruangan ini.';
    private const PEJABAT_KETUA  = 'Dr. Dedy Hartama, S.T., M.Kom';
    private const JABATAN_KETUA  = 'Ketua STIKOM Tunas Bangsa';
    private const PEJABAT_WAKIL  = 'Riki Winanjaya, M.Kom';
    private const JABATAN_WAKIL  = 'Wakil Ketua 2';
    private const LOGO_KIRI      = 'assets/img/logo_kiri.png';
    private const LOGO_KANAN     = 'assets/img/logo_kanan.png';

    public function streamPdf(array $ruanganData): void
    {
        $html = $this->buildHtmlDocument($ruanganData);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'Times New Roman');
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Inventaris_Barang_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    public function streamDocx(array $ruanganData): void
    {
        Settings::setCompatibility(true);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $sectionStyle = [
            'paperSize'    => 'A4',
            'orientation'  => 'portrait',
            'marginTop'    => Converter::cmToTwip(2.5),
            'marginBottom' => Converter::cmToTwip(2.5),
            'marginLeft'   => Converter::cmToTwip(3),
            'marginRight'  => Converter::cmToTwip(2),
        ];

        $section = $phpWord->addSection($sectionStyle);

        $this->addDocxHeader($section);
        $this->addDocxFooter($section);
        $this->addDocxContent($section, $ruanganData);

        $filename = 'Inventaris_Barang_' . date('Ymd_His') . '.docx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'docx_');

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpFile);

        $this->sendFileDownload($tmpFile, $filename, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function streamXlsx(array $ruanganData): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);

        foreach ($ruanganData as $index => $ruang) {
            $sheet = ($index === 0)
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet();

            $sheet->setTitle(substr($ruang['nama_ruangan'], 0, 31));
            $this->populateXlsxSheet($sheet, $ruang);
        }

        $filename = 'Inventaris_Barang_' . date('Ymd_His') . '.xlsx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'xlsx_');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tmpFile);

        $this->sendFileDownload($tmpFile, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function buildPreviewHtml(array $ruanganData): string
    {
        $logoKiri  = file_exists(FCPATH . self::LOGO_KIRI)  ? base_url(self::LOGO_KIRI)  : '';
        $logoKanan = file_exists(FCPATH . self::LOGO_KANAN) ? base_url(self::LOGO_KANAN) : '';

        $html = '';
        foreach ($ruanganData as $ruang) {
            $html .= '<div class="preview-page">';
            $html .= $this->buildKopHtmlPreview($logoKiri, $logoKanan);
            $html .= '<div class="doc-judul">DAFTAR INVENTARIS RUANGAN</div>';
            $html .= $this->buildInfoRuanganHtml($ruang);
            $html .= $this->buildTabelBarangHtml($ruang['barang']);
            $html .= $this->buildFooterHtml();
            $html .= '</div>';
        }

        return $html;
    }

    private function buildHtmlDocument(array $ruanganData): string
    {
        $logoKiriPath  = FCPATH . self::LOGO_KIRI;
        $logoKananPath = FCPATH . self::LOGO_KANAN;
        $logoKiri  = file_exists($logoKiriPath)  ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoKiriPath))  : '';
        $logoKanan = file_exists($logoKananPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoKananPath)) : '';

        $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>';
        $html .= 'body { font-family: "Times New Roman", Times, serif; font-size: 12pt; margin: 0; padding: 0cm; }';
        $html .= '.page { page-break-after: always; padding: 0cm; }';
        $html .= '.page:last-child { page-break-after: avoid; }';
        $html .= '.kop-outer { position: relative; border-bottom: 3px double #000; margin-bottom: 14px; padding-bottom: 6px; min-height: 72px; }';
        $html .= '.kop-logo-kiri { position: absolute; left: 0; top: 0; height: 68px; width: auto; z-index: 0; }';
        $html .= '.kop-logo-kanan { position: absolute; right: 0; top: 4px; height: 45px; width: auto; z-index: 0; }';
        $html .= '.kop-text { position: relative; z-index: 1; text-align: center; padding: 0 80px; }';
        $html .= '.kop-text .nama-kampus { font-size: 15pt; font-weight: bold; margin: 0; line-height: 1.2; }';
        $html .= '.kop-text .nama-singkatan { font-size: 14pt; font-weight: bold; margin: 0; line-height: 1.2; }';
        $html .= '.kop-text .sk { font-size: 9pt; margin: 2px 0 0; }';
        $html .= '.kop-text .prodi { font-size: 8pt; margin: 2px 0 0; }';
        $html .= '.judul { text-align: center; font-size: 14pt; font-weight: bold; margin: 14px 0 10px; }';
        $html .= '.info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }';
        $html .= '.info-table td { padding: 2px 6px; font-size: 12pt; }';
        $html .= '.info-table td.label { width: 130px; font-weight: bold; }';
        $html .= '.info-table td.sep { width: 15px; }';
        $html .= 'table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }';
        $html .= 'table.data th { background: #d3d3d3; border: 1px solid #000; padding: 5px 6px; font-size: 11pt; text-align: center; font-family: "Times New Roman", Times, serif; }';
        $html .= 'table.data td { border: 1px solid #000; padding: 4px 6px; font-size: 11pt; font-family: "Times New Roman", Times, serif; }';
        $html .= 'table.data td.center { text-align: center; }';
        $html .= '.footer-note { font-size: 9pt; font-style: italic; text-align: center; margin-top: 8px; }';
        $html .= '.ttd-table { width: 100%; border-collapse: collapse; margin-top: 8px; }';
        $html .= '.ttd-table td { text-align: center; padding: 4px; font-size: 11pt; }';
        $html .= '.ttd-name { font-weight: bold; }';
        $html .= '</style></head><body>';

        foreach ($ruanganData as $ruang) {
            $html .= '<div class="page">';
            $html .= $this->buildKopHtmlPdf($logoKiri, $logoKanan);
            $html .= '<div class="judul">DAFTAR INVENTARIS RUANGAN</div>';
            $html .= $this->buildInfoRuanganHtmlPdf($ruang);
            $html .= $this->buildTabelBarangHtmlPdf($ruang['barang']);
            $html .= $this->buildFooterHtmlPdf();
            $html .= '</div>';
        }

        $html .= '</body></html>';
        return $html;
    }

    private function buildKopHtmlPreview(string $logoKiri, string $logoKanan): string
    {
        $html  = '<div class="kop-outer">';
        if ($logoKiri)  $html .= '<img class="kop-logo-kiri"  src="' . $logoKiri  . '" alt="Logo Kiri">';
        if ($logoKanan) $html .= '<img class="kop-logo-kanan" src="' . $logoKanan . '" alt="Logo Kanan">';
        $html .= '<div class="kop-center">';
        $html .= '<div class="kop-kampus">' . self::NAMA_KAMPUS . '</div>';
        $html .= '<div class="kop-singkatan">' . self::SINGKATAN . '</div>';
        $html .= '<div class="kop-sk">' . self::SK . '</div>';
        $html .= '<div class="kop-prodi">' . self::PRODI . '</div>';
        $html .= '</div></div>';
        return $html;
    }

    private function buildKopHtmlPdf(string $logoKiri, string $logoKanan): string
    {
        $html  = '<div class="kop-outer">';
        if ($logoKiri)  $html .= '<img class="kop-logo-kiri"  src="' . $logoKiri  . '" alt="Logo Kiri">';
        if ($logoKanan) $html .= '<img class="kop-logo-kanan" src="' . $logoKanan . '" alt="Logo Kanan">';
        $html .= '<div class="kop-text">';
        $html .= '<p class="nama-kampus">' . self::NAMA_KAMPUS . '</p>';
        $html .= '<p class="nama-singkatan">' . self::SINGKATAN . '</p>';
        $html .= '<p class="sk">' . self::SK . '</p>';
        $html .= '<p class="prodi">' . self::PRODI . '</p>';
        $html .= '</div></div>';
        return $html;
    }

    private function buildInfoRuanganHtml(array $ruang): string
    {
        $html  = '<table class="doc-info">';
        $html .= '<tr><td class="lbl">Tipe Ruangan</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['tipe_ruangan']) . '</td></tr>';
        $html .= '<tr><td class="lbl">Lokasi</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['nama_gedung'] . ' / Lantai ' . $ruang['lantai']) . '</td></tr>';
        $html .= '<tr><td class="lbl">Nama Ruangan</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['nama_ruangan']) . '</td></tr>';
        $html .= '</table>';
        return $html;
    }

    private function buildInfoRuanganHtmlPdf(array $ruang): string
    {
        $html  = '<table class="info-table">';
        $html .= '<tr><td class="label">Tipe Ruangan</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['tipe_ruangan']) . '</td></tr>';
        $html .= '<tr><td class="label">Lokasi</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['nama_gedung'] . ' / Lantai ' . $ruang['lantai']) . '</td></tr>';
        $html .= '<tr><td class="label">Nama Ruangan</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['nama_ruangan']) . '</td></tr>';
        $html .= '</table>';
        return $html;
    }

    private function buildTabelBarangHtml(array $barangList): string
    {
        $html  = '<table class="doc-table">';
        $html .= '<thead><tr><th style="width:5%">No</th><th style="width:35%">Nama Barang</th><th style="width:22%">Merek/Ukuran</th><th style="width:8%">Jumlah</th><th style="width:30%">Keterangan</th></tr></thead>';
        $html .= '<tbody>';

        if (empty($barangList)) {
            $html .= '<tr><td colspan="5" style="text-align:center"><em>Tidak ada barang terdaftar.</em></td></tr>';
        } else {
            foreach ($barangList as $idx => $barang) {
                $html .= '<tr>';
                $html .= '<td style="text-align:center">' . ($idx + 1) . '</td>';
                $html .= '<td>' . htmlspecialchars($barang['nama_barang']) . '</td>';
                $html .= '<td>' . htmlspecialchars($barang['merek_ukuran']) . '</td>';
                $html .= '<td style="text-align:center">' . htmlspecialchars($barang['jumlah']) . '</td>';
                $html .= '<td>' . htmlspecialchars($barang['kondisi']) . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function buildTabelBarangHtmlPdf(array $barangList): string
    {
        $html  = '<table class="data">';
        $html .= '<thead><tr>';
        $html .= '<th style="width:5%">No</th>';
        $html .= '<th style="width:35%">Nama Barang</th>';
        $html .= '<th style="width:22%">Merek/Ukuran</th>';
        $html .= '<th style="width:8%">Jumlah</th>';
        $html .= '<th style="width:30%">Keterangan</th>';
        $html .= '</tr></thead><tbody>';

        if (empty($barangList)) {
            $html .= '<tr><td colspan="5" class="center"><em>Tidak ada barang pada ruangan ini.</em></td></tr>';
        } else {
            foreach ($barangList as $idx => $barang) {
                $html .= '<tr>';
                $html .= '<td class="center">' . ($idx + 1) . '</td>';
                $html .= '<td>' . htmlspecialchars($barang['nama_barang']) . '</td>';
                $html .= '<td>' . htmlspecialchars($barang['merek_ukuran']) . '</td>';
                $html .= '<td class="center">' . htmlspecialchars($barang['jumlah']) . '</td>';
                $html .= '<td>' . htmlspecialchars($barang['kondisi']) . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function buildFooterHtml(): string
    {
        $html  = '<div class="doc-footer-note">' . self::DISCLAIMER . '<br>Data ini di Update terakhir pada ' . date('F Y') . '</div>';
        $html .= '<div style="text-align:right;margin-top:12px;font-size:11pt">';
        $html .= '<p>Pematangsiantar, ' . date('d F Y') . '</p><p>Mengetahui</p>';
        $html .= '</div>';
        $html .= '<table class="doc-ttd"><tr>';
        $html .= '<td style="text-align:center;padding-top:50px"><strong>' . self::PEJABAT_KETUA . '</strong><br>' . self::JABATAN_KETUA . '</td>';
        $html .= '<td style="text-align:center;padding-top:50px"><strong>' . self::PEJABAT_WAKIL . '</strong><br>' . self::JABATAN_WAKIL . '</td>';
        $html .= '</tr></table>';
        return $html;
    }

    private function buildFooterHtmlPdf(): string
    {
        $html  = '<div class="footer-note">' . self::DISCLAIMER . '<br>';
        $html .= 'Data ini di Update terakhir pada ' . date('F Y') . '</div>';
        $html .= '<p style="text-align:right;font-size:11pt;margin:12px 0 2px;">Pematangsiantar, ' . date('d F Y') . '</p>';
        $html .= '<p style="text-align:right;font-size:11pt;margin:0 0 60px;">Mengetahui</p>';
        $html .= '<table class="ttd-table"><tr>';
        $html .= '<td><p class="ttd-name">' . self::PEJABAT_KETUA . '</p><p>' . self::JABATAN_KETUA . '</p></td>';
        $html .= '<td><p class="ttd-name">' . self::PEJABAT_WAKIL . '</p><p>' . self::JABATAN_WAKIL . '</p></td>';
        $html .= '</tr></table>';
        return $html;
    }

    private function addDocxHeader(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $logoKiriPath  = FCPATH . self::LOGO_KIRI;
        $logoKananPath = FCPATH . self::LOGO_KANAN;

        $header = $section->addHeader();

        if (file_exists($logoKiriPath)) {
            $header->addImage($logoKiriPath, [
                'height'          => 60,
                'wrappingStyle'   => 'behind',
                'positioning'     => ImageStyle::POSITION_ABSOLUTE,
                'posHorizontal'   => ImageStyle::POSITION_HORIZONTAL_LEFT,
                'posHorizontalRel'=> ImageStyle::POSITION_RELATIVE_TO_MARGIN,
                'posVertical'     => ImageStyle::POSITION_VERTICAL_TOP,
                'posVerticalRel'  => ImageStyle::POSITION_RELATIVE_TO_MARGIN,
            ]);
        }

        if (file_exists($logoKananPath)) {
            $header->addImage($logoKananPath, [
                'height'          => 40,
                'wrappingStyle'   => 'behind',
                'positioning'     => ImageStyle::POSITION_ABSOLUTE,
                'posHorizontal'   => ImageStyle::POSITION_HORIZONTAL_RIGHT,
                'posHorizontalRel'=> ImageStyle::POSITION_RELATIVE_TO_MARGIN,
                'posVertical'     => ImageStyle::POSITION_VERTICAL_TOP,
                'posVerticalRel'  => ImageStyle::POSITION_RELATIVE_TO_MARGIN,
            ]);
        }

        $headerTable = $header->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF']);
        $headerTable->addRow();
        $hCell = $headerTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF', 'valign' => 'center']);
        $hCell->addText(self::NAMA_KAMPUS, ['name' => 'Times New Roman', 'size' => 14, 'bold' => true], ['alignment' => Jc::CENTER]);
        $hCell->addText(self::SINGKATAN,   ['name' => 'Times New Roman', 'size' => 16, 'bold' => true], ['alignment' => Jc::CENTER]);
        $hCell->addText(self::SK,   ['name' => 'Times New Roman', 'size' => 9], ['alignment' => Jc::CENTER]);
        $hCell->addText(self::PRODI, ['name' => 'Times New Roman', 'size' => 8], ['alignment' => Jc::CENTER]);

        $header->addLine(['width' => 5000, 'height' => 10]);
    }

    private function addDocxFooter(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $footer = $section->addFooter();
        $footer->addPreserveText(self::DISCLAIMER, ['name' => 'Times New Roman', 'size' => 9, 'italic' => true], ['alignment' => Jc::CENTER]);
        $footer->addText('Data ini di Update terakhir pada ' . date('F Y'), ['name' => 'Times New Roman', 'size' => 9, 'italic' => true], ['alignment' => Jc::CENTER]);
    }

    private function addDocxContent(\PhpOffice\PhpWord\Element\Section $section, array $ruanganData): void
    {
        $isFirst = true;
        foreach ($ruanganData as $ruang) {
            if (!$isFirst) {
                $section->addPageBreak();
            }
            $isFirst = false;

            $section->addText('DAFTAR INVENTARIS RUANGAN', ['name' => 'Times New Roman', 'size' => 14, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);

            $infoRows = [
                ['Tipe Ruangan', $ruang['tipe_ruangan']],
                ['Lokasi',       $ruang['nama_gedung'] . ' / Lantai ' . $ruang['lantai']],
                ['Nama Ruangan', $ruang['nama_ruangan']],
            ];

            foreach ($infoRows as [$label, $value]) {
                $infoTable = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMarginTop' => 30, 'cellMarginBottom' => 30]);
                $infoTable->addRow();
                $infoTable->addCell(Converter::cmToTwip(4),   ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText($label, ['name' => 'Times New Roman', 'size' => 12, 'bold' => true]);
                $infoTable->addCell(Converter::cmToTwip(0.5), ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(':', ['name' => 'Times New Roman', 'size' => 12]);
                $infoTable->addCell(null,                     ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText($value, ['name' => 'Times New Roman', 'size' => 12]);
            }

            $section->addTextBreak(1);

            $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMarginTop' => 60, 'cellMarginBottom' => 60, 'cellMarginLeft' => 60, 'cellMarginRight' => 60];
            $table = $section->addTable($tableStyle);

            $thStyle = ['bgColor' => 'D3D3D3', 'borderSize' => 6, 'borderColor' => '000000'];
            $thFont  = ['name' => 'Times New Roman', 'size' => 11, 'bold' => true];
            $thPar   = ['alignment' => Jc::CENTER];

            $table->addRow(null, ['tblHeader' => true]);
            $table->addCell(Converter::cmToTwip(1),   $thStyle)->addText('No',           $thFont, $thPar);
            $table->addCell(Converter::cmToTwip(6.5), $thStyle)->addText('Nama Barang',  $thFont, $thPar);
            $table->addCell(Converter::cmToTwip(3.5), $thStyle)->addText('Merek/Ukuran', $thFont, $thPar);
            $table->addCell(Converter::cmToTwip(1.5), $thStyle)->addText('Jumlah',       $thFont, $thPar);
            $table->addCell(Converter::cmToTwip(4),   $thStyle)->addText('Keterangan',   $thFont, $thPar);

            $tdFont = ['name' => 'Times New Roman', 'size' => 11];
            $tdC    = ['alignment' => Jc::CENTER];
            $tdL    = ['alignment' => Jc::LEFT];
            $cellBorder = ['borderSize' => 6, 'borderColor' => '000000'];

            if (empty($ruang['barang'])) {
                $table->addRow();
                $table->addCell(null, array_merge($cellBorder, ['gridSpan' => 5]))->addText('Tidak ada barang pada ruangan ini.', array_merge($tdFont, ['italic' => true]), $tdC);
            } else {
                foreach ($ruang['barang'] as $idx => $barang) {
                    $table->addRow();
                    $table->addCell(Converter::cmToTwip(1),   $cellBorder)->addText($idx + 1,               $tdFont, $tdC);
                    $table->addCell(Converter::cmToTwip(6.5), $cellBorder)->addText($barang['nama_barang'],  $tdFont, $tdL);
                    $table->addCell(Converter::cmToTwip(3.5), $cellBorder)->addText($barang['merek_ukuran'], $tdFont, $tdL);
                    $table->addCell(Converter::cmToTwip(1.5), $cellBorder)->addText($barang['jumlah'],       $tdFont, $tdC);
                    $table->addCell(Converter::cmToTwip(4),   $cellBorder)->addText($barang['kondisi'],      $tdFont, $tdL);
                }
            }

            $section->addTextBreak(2);
            $section->addText('Pematangsiantar, ' . date('d F Y'), ['name' => 'Times New Roman', 'size' => 11], ['alignment' => Jc::RIGHT]);
            $section->addText('Mengetahui', ['name' => 'Times New Roman', 'size' => 11], ['alignment' => Jc::RIGHT]);
            $section->addTextBreak(3);

            $ttdTable = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF']);
            $ttdTable->addRow();
            $ttdTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(self::PEJABAT_KETUA, ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER]);
            $ttdTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(self::PEJABAT_WAKIL, ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER]);
            $ttdTable->addRow();
            $ttdTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(self::JABATAN_KETUA, ['name' => 'Times New Roman', 'size' => 11], ['alignment' => Jc::CENTER]);
            $ttdTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(self::JABATAN_WAKIL, ['name' => 'Times New Roman', 'size' => 11], ['alignment' => Jc::CENTER]);
        }
    }

    private function populateXlsxSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $ruang): void
    {
        $fontTNR = function (int $size = 12, bool $bold = false) {
            return ['font' => ['name' => 'Times New Roman', 'size' => $size, 'bold' => $bold]];
        };

        $centerAlign = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
        $leftAlign   = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,   'vertical' => Alignment::VERTICAL_CENTER]];

        $logoKiriPath  = FCPATH . self::LOGO_KIRI;
        $logoKananPath = FCPATH . self::LOGO_KANAN;
        $row = 1;

        $sheet->getRowDimension($row)->setRowHeight(22);
        $sheet->mergeCells("B{$row}:F{$row}");
        $sheet->setCellValue("B{$row}", self::NAMA_KAMPUS . ' ' . self::SINGKATAN);
        $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(14, true), $centerAlign));
        $row++;

        $sheet->getRowDimension($row)->setRowHeight(14);
        $sheet->mergeCells("B{$row}:F{$row}");
        $sheet->setCellValue("B{$row}", self::SK);
        $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(9), $centerAlign));
        $row++;

        $sheet->getRowDimension($row)->setRowHeight(28);
        $sheet->mergeCells("B{$row}:F{$row}");
        $sheet->setCellValue("B{$row}", 'Program Studi: D3: Manajemen Informatika, Komputerisasi Akuntansi | S1: Sistem Informasi, Teknik Informatika | S2: Informatika');
        $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(8), $centerAlign));
        $sheet->getStyle("B{$row}")->getAlignment()->setWrapText(true);

        $sheet->mergeCells("A1:A3");
        $sheet->getColumnDimension('A')->setWidth(12);
        if (file_exists($logoKiriPath)) {
            $imgKiri = new Drawing();
            $imgKiri->setName('Logo Kiri')->setDescription('Logo Kiri')->setPath($logoKiriPath)->setHeight(60)->setCoordinates('A1')->setOffsetX(4)->setOffsetY(4)->setWorksheet($sheet);
        }

        $sheet->mergeCells("G1:G3");
        $sheet->getColumnDimension('G')->setWidth(12);
        if (file_exists($logoKananPath)) {
            $imgKanan = new Drawing();
            $imgKanan->setName('Logo Kanan')->setDescription('Logo Kanan')->setPath($logoKananPath)->setHeight(60)->setCoordinates('G1')->setOffsetX(4)->setOffsetY(4)->setWorksheet($sheet);
        }

        $sheet->getStyle("A3:G3")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $sheet->mergeCells("B{$row}:F{$row}");
        $sheet->setCellValue("B{$row}", 'DAFTAR INVENTARIS RUANGAN');
        $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(14, true), $centerAlign));
        $sheet->getRowDimension($row)->setRowHeight(24);
        $row += 2;

        foreach ([['Tipe Ruangan', $ruang['tipe_ruangan']], ['Lokasi', $ruang['nama_gedung'] . ' / Lantai ' . $ruang['lantai']], ['Nama Ruangan', $ruang['nama_ruangan']]] as [$label, $value]) {
            $sheet->setCellValue("B{$row}", $label);
            $sheet->setCellValue("C{$row}", ':');
            $sheet->mergeCells("D{$row}:F{$row}");
            $sheet->setCellValue("D{$row}", $value);
            $sheet->getStyle("B{$row}:F{$row}")->applyFromArray($fontTNR(12));
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);
            $row++;
        }
        $row++;

        foreach (['B' => 'No', 'C' => 'Nama Barang', 'D' => 'Merek/Ukuran', 'E' => 'Jumlah', 'F' => 'Keterangan'] as $col => $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
        }
        $sheet->getStyle("B{$row}:F{$row}")->applyFromArray(array_merge(
            $fontTNR(11, true),
            $centerAlign,
            ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]],
            ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]
        ));
        $row++;

        if (empty($ruang['barang'])) {
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->setCellValue("B{$row}", 'Tidak ada barang pada ruangan ini.');
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(11), $centerAlign));
            $sheet->getStyle("B{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        } else {
            foreach ($ruang['barang'] as $idx => $barang) {
                $sheet->setCellValue("B{$row}", $idx + 1);
                $sheet->setCellValue("C{$row}", $barang['nama_barang']);
                $sheet->setCellValue("D{$row}", $barang['merek_ukuran']);
                $sheet->setCellValue("E{$row}", $barang['jumlah']);
                $sheet->setCellValue("F{$row}", $barang['kondisi']);
                $sheet->getStyle("B{$row}:F{$row}")->applyFromArray(array_merge($fontTNR(11), ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]));
                $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }
        }

        $row += 2;

        $sheet->mergeCells("B{$row}:F{$row}");
        $sheet->setCellValue("B{$row}", self::DISCLAIMER);
        $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(9), $centerAlign));
        $sheet->getStyle("B{$row}")->getFont()->setItalic(true);
        $sheet->getStyle("B{$row}")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(30);
        $row++;

        $sheet->mergeCells("B{$row}:F{$row}");
        $sheet->setCellValue("B{$row}", 'Data ini di Update terakhir pada ' . date('F Y'));
        $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(9), $centerAlign));
        $sheet->getStyle("B{$row}")->getFont()->setItalic(true);
        $row++;

        $sheet->mergeCells("E{$row}:F{$row}");
        $sheet->setCellValue("E{$row}", 'Pematangsiantar, ' . date('d F Y'));
        $sheet->getStyle("E{$row}")->applyFromArray($fontTNR(11));
        $row++;

        $sheet->mergeCells("E{$row}:F{$row}");
        $sheet->setCellValue("E{$row}", 'Mengetahui');
        $sheet->getStyle("E{$row}")->applyFromArray($fontTNR(11));
        $row += 4;

        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", self::PEJABAT_KETUA);
        $sheet->mergeCells("E{$row}:F{$row}");
        $sheet->setCellValue("E{$row}", self::PEJABAT_WAKIL);
        $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(11, true), $centerAlign));
        $sheet->getStyle("E{$row}")->applyFromArray(array_merge($fontTNR(11, true), $centerAlign));
        $row++;

        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", self::JABATAN_KETUA);
        $sheet->mergeCells("E{$row}:F{$row}");
        $sheet->setCellValue("E{$row}", self::JABATAN_WAKIL);
        $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(11), $centerAlign));
        $sheet->getStyle("E{$row}")->applyFromArray(array_merge($fontTNR(11), $centerAlign));

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(12);

        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4)->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageMargins()->setTop(1)->setBottom(1)->setLeft(1.2)->setRight(0.8);
        $sheet->getHeaderFooter()->setOddFooter('&C&"Times New Roman,Italic"&8' . self::DISCLAIMER);
    }

    private function sendFileDownload(string $tmpFile, string $filename, string $contentType): void
    {
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }
}
