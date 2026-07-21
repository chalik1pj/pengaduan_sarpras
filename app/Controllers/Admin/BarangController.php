<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BarangModel;
use App\Models\GedungModel;
use App\Models\RuanganModel;

class BarangController extends BaseController
{
    protected BarangModel  $model;
    protected GedungModel  $gedungModel;
    protected RuanganModel $ruanganModel;

    public function __construct()
    {
        $this->model        = new BarangModel();
        $this->gedungModel  = new GedungModel();
        $this->ruanganModel = new RuanganModel();
    }

    public function index()
    {
        $filterGedung  = $this->request->getGet('gedung') ?? '';
        $filterRuangan = $this->request->getGet('ruangan') ?? '';
        $search        = $this->request->getGet('q') ?? '';

        $query = $this->model->withRuanganGedung();

        if (!empty($filterGedung) && is_numeric($filterGedung)) {
            $query->where('gedung.id_gedung', (int)$filterGedung);
        }
        if (!empty($filterRuangan) && is_numeric($filterRuangan)) {
            $query->where('barang.id_ruangan', (int)$filterRuangan);
        }
        if (!empty($search)) {
            $query->like('barang.nama_barang', $search);
        }

        $items = $query->orderBy('gedung.nama_gedung','ASC')->orderBy('ruangan.nama_ruangan','ASC')->orderBy('barang.nama_barang','ASC')->findAll();

        $ruanganList = [];
        if (!empty($filterGedung) && is_numeric($filterGedung)) {
            $ruanganList = $this->ruanganModel->where('id_gedung', (int)$filterGedung)->orderBy('nama_ruangan','ASC')->findAll();
        }

        return view('admin/barang/index', [
            'title'        => 'Manajemen Barang / Fasilitas',
            'items'        => $items,
            'gedung'       => $this->gedungModel->orderBy('nama_gedung','ASC')->findAll(),
            'ruanganList'  => $ruanganList,
            'filterGedung' => $filterGedung,
            'filterRuangan'=> $filterRuangan,
            'search'       => $search,
            'active'       => 'barang',
        ]);
    }

    public function store()
    {
        $rules = [
            'id_ruangan'  => 'required|integer',
            'nama_barang' => 'required|min_length[2]|max_length[150]',
            'jumlah'      => 'required|integer|greater_than[0]',
            'kondisi'     => 'required|max_length[50]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->insert([
            'id_ruangan'   => (int)$this->request->getPost('id_ruangan'),
            'nama_barang'  => $this->request->getPost('nama_barang'),
            'merek_ukuran' => $this->request->getPost('merek_ukuran'),
            'jumlah'       => (int)$this->request->getPost('jumlah'),
            'kondisi'      => $this->request->getPost('kondisi'),
        ]);
        return redirect()->back()->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $rules = [
            'id_ruangan'  => 'required|integer',
            'nama_barang' => 'required|min_length[2]|max_length[150]',
            'jumlah'      => 'required|integer|greater_than[0]',
            'kondisi'     => 'required|max_length[50]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->update($id, [
            'id_ruangan'   => (int)$this->request->getPost('id_ruangan'),
            'nama_barang'  => $this->request->getPost('nama_barang'),
            'merek_ukuran' => $this->request->getPost('merek_ukuran'),
            'jumlah'       => (int)$this->request->getPost('jumlah'),
            'kondisi'      => $this->request->getPost('kondisi'),
        ]);
        return redirect()->back()->with('success', 'Barang berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->back()->with('success', 'Barang berhasil dihapus.');
    }

    // ================================================================
    // HELPER: Ambil data per ruangan sesuai filter
    // Mendukung: ?gedung=1 / ?ruangan=3 / ?ruangan[]=1&ruangan[]=2 / $forceIds=[1,2]
    // ================================================================
    private function getDataCetak(array $forceIds = []): array
    {
        $filterGedung  = $this->request->getGet('gedung') ?? '';
        $filterRuangan = $this->request->getGet('ruangan') ?? '';

        $db = \Config\Database::connect();

        // Query utama: ambil data ruangan beserta barang-barangnya
        $sql = "
            SELECT
                g.id_gedung, g.nama_gedung,
                r.id_ruangan, r.nama_ruangan, r.tipe_ruangan, r.lantai,
                b.id_barang, b.nama_barang, b.merek_ukuran, b.jumlah, b.kondisi
            FROM gedung g
            JOIN ruangan r ON r.id_gedung = g.id_gedung
            LEFT JOIN barang b ON b.id_ruangan = r.id_ruangan
        ";

        $params = [];
        $where  = [];

        if (!empty($forceIds)) {
            // Dipanggil langsung dengan array ID ruangan
            $placeholders = implode(',', array_fill(0, count($forceIds), '?'));
            $where[] = "r.id_ruangan IN ($placeholders)";
            $params  = array_map('intval', $forceIds);
        } else {
            $filterGedung  = $this->request->getGet('gedung') ?? '';
            $filterRuangan = $this->request->getGet('ruangan'); // bisa scalar atau array

            if (!empty($filterGedung) && is_numeric($filterGedung)) {
                $where[] = "g.id_gedung = ?";
                $params[] = (int)$filterGedung;
            }

            if (!empty($filterRuangan)) {
                if (is_array($filterRuangan)) {
                    $ids = array_filter(array_map('intval', $filterRuangan));
                    if (!empty($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $where[] = "r.id_ruangan IN ($placeholders)";
                        $params  = array_merge($params, array_values($ids));
                    }
                } elseif (is_numeric($filterRuangan)) {
                    $where[] = "r.id_ruangan = ?";
                    $params[] = (int)$filterRuangan;
                }
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY g.nama_gedung ASC, r.lantai ASC, r.nama_ruangan ASC, b.nama_barang ASC";

        $rows = $db->query($sql, $params)->getResultArray();

        // Kelompokkan per ruangan
        $ruangan = [];
        foreach ($rows as $row) {
            $kId = $row['id_ruangan'];
            if (!isset($ruangan[$kId])) {
                $ruangan[$kId] = [
                    'id_ruangan'   => $row['id_ruangan'],
                    'nama_ruangan' => $row['nama_ruangan'],
                    'tipe_ruangan' => $row['tipe_ruangan'],
                    'lantai'       => $row['lantai'],
                    'nama_gedung'  => $row['nama_gedung'],
                    'barang'       => [],
                ];
            }
            if (!empty($row['id_barang'])) {
                $ruangan[$kId]['barang'][] = [
                    'nama_barang'  => $row['nama_barang'],
                    'merek_ukuran' => $row['merek_ukuran'] ?? '-',
                    'jumlah'       => $row['jumlah'],
                    'kondisi'      => $row['kondisi'],
                ];
            }
        }

        return array_values($ruangan);
    }

    // ================================================================
    // PREVIEW PAGE — tampilkan halaman preview sebelum download
    // ================================================================
    public function cetakPreview()
    {
        return view('admin/barang/preview', [
            'title'  => 'Preview Cetak Inventaris',
            'gedung' => $this->gedungModel->orderBy('nama_gedung', 'ASC')->findAll(),
            'active' => 'barang',
        ]);
    }

    // ================================================================
    // PREVIEW CONTENT (AJAX) — kembalikan HTML preview untuk ruangan terpilih
    // ================================================================
    public function cetakPreviewContent()
    {
        $ruanganIds = $this->request->getGet('ruangan') ?? [];
        if (!is_array($ruanganIds)) {
            $ruanganIds = $ruanganIds ? [(int)$ruanganIds] : [];
        }
        $ruanganIds = array_values(array_filter(array_map('intval', $ruanganIds)));

        if (empty($ruanganIds)) {
            return $this->response->setJSON(['html' => '', 'count' => 0]);
        }

        $ruanganData = $this->getDataCetak($ruanganIds);
        $html        = $this->buildPreviewHtml($ruanganData);

        return $this->response->setJSON(['html' => $html, 'count' => count($ruanganData)]);
    }

    private function buildPreviewHtml(array $ruanganData): string
    {
        $logoKiri  = file_exists(FCPATH . 'assets/img/logo_kiri.png')  ? base_url('assets/img/logo_kiri.png')  : '';
        $logoKanan = file_exists(FCPATH . 'assets/img/logo_kanan.png') ? base_url('assets/img/logo_kanan.png') : '';

        $html = '';
        foreach ($ruanganData as $ruang) {
            $html .= '<div class="preview-page">';

            // Kop surat — logo absolute behind text
            $html .= '<div class="kop-outer">';
            if ($logoKiri)  { $html .= '<img class="kop-logo-kiri"  src="' . $logoKiri  . '" alt="Logo Kiri">'; }
            if ($logoKanan) { $html .= '<img class="kop-logo-kanan" src="' . $logoKanan . '" alt="Logo Kanan">'; }
            $html .= '<div class="kop-center">';
            $html .= '<div class="kop-kampus">SEKOLAH TINGGI ILMU KOMPUTER</div>';
            $html .= '<div class="kop-singkatan">STIKOM TUNAS BANGSA</div>';
            $html .= '<div class="kop-sk">SK MENDIKBUD RISTEK RI. NO. 513/E/O/2022 Tanggal 13 Juli 2022</div>';
            $html .= '<div class="kop-prodi">Program Studi: Diploma 3 (D3): Manajemen Informatika, Komputerisasi Akuntansi | Sarjana (S1): Sistem Informasi, Teknik Informatika | Magister (S2): Informatika</div>';
            $html .= '</div>';
            $html .= '</div>';

            // Judul
            $html .= '<div class="doc-judul">DAFTAR INVENTARIS RUANGAN</div>';

            // Info ruangan
            $html .= '<table class="doc-info">';
            $html .= '<tr><td class="lbl">Tipe Ruangan</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['tipe_ruangan']) . '</td></tr>';
            $html .= '<tr><td class="lbl">Lokasi</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['nama_gedung'] . ' / Lantai ' . $ruang['lantai']) . '</td></tr>';
            $html .= '<tr><td class="lbl">Nama Ruangan</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['nama_ruangan']) . '</td></tr>';
            $html .= '</table>';

            // Tabel barang
            $html .= '<table class="doc-table">';
            $html .= '<thead><tr><th style="width:5%">No</th><th style="width:35%">Nama Barang</th><th style="width:22%">Merek/Ukuran</th><th style="width:8%">Jumlah</th><th style="width:30%">Keterangan</th></tr></thead>';
            $html .= '<tbody>';
            if (empty($ruang['barang'])) {
                $html .= '<tr><td colspan="5" style="text-align:center"><em>Tidak ada barang terdaftar.</em></td></tr>';
            } else {
                foreach ($ruang['barang'] as $idx => $b) {
                    $html .= '<tr>';
                    $html .= '<td style="text-align:center">' . ($idx + 1) . '</td>';
                    $html .= '<td>' . htmlspecialchars($b['nama_barang']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($b['merek_ukuran']) . '</td>';
                    $html .= '<td style="text-align:center">' . htmlspecialchars($b['jumlah']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($b['kondisi']) . '</td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</tbody></table>';

            // Footer
            $html .= '<div class="doc-footer-note">Tidak dibenarkan memindahkan barang-barang yang ada pada daftar ini tanpa sepengetahuan penanggung jawab ruangan ini.<br>Data ini di Update terakhir pada ' . date('F Y') . '</div>';
            $html .= '<div style="text-align:right;margin-top:12px;font-size:11pt">';
            $html .= '<p>Pematangsiantar, ' . date('d F Y') . '</p><p>Mengetahui</p>';
            $html .= '</div>';
            $html .= '<table class="doc-ttd">';
            $html .= '<tr><td style="text-align:center;padding-top:50px"><strong>Dr. Dedy Hartama, S.T., M.Kom</strong><br>Ketua STIKOM Tunas Bangsa</td>';
            $html .= '<td style="text-align:center;padding-top:50px"><strong>Riki Winanjaya, M.Kom</strong><br>Wakil Ketua 2</td></tr>';
            $html .= '</table>';

            $html .= '</div>'; // .preview-page
        }
        return $html;
    }

    public function cetakPdf()
    {
        $ruanganData = $this->getDataCetak();

        // Generate HTML yang akan dikonversi ke PDF
        $html = $this->buildHtmlCetak($ruanganData);

        // Load dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'Times New Roman');
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Inventaris_Barang_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    public function cetakDocx()
    {
        $ruanganData = $this->getDataCetak();

        // Konfigurasi PhpWord
        $settings = \PhpOffice\PhpWord\Settings::setCompatibility(true);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // Format halaman A4
        $sectionStyle = [
            'paperSize'    => 'A4',
            'orientation'  => 'portrait',
            'marginTop'    => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginLeft'   => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3),
            'marginRight'  => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
        ];

        $section = $phpWord->addSection($sectionStyle);

        // === HEADER: Logo behind-text + teks kop terpusat ===
        $header = $section->addHeader();

        $logoKiriPath  = FCPATH . 'assets/img/logo_kiri.png';
        $logoKananPath = FCPATH . 'assets/img/logo_kanan.png';
        $logoKiriExists  = file_exists($logoKiriPath);
        $logoKananExists = file_exists($logoKananPath);

        // Logo KIRI — floating behind text, sudut kiri atas header
        if ($logoKiriExists) {
            $header->addImage($logoKiriPath, [
                'height'          => 60,
                'wrappingStyle'   => 'behind',
                'positioning'     => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
                'posHorizontal'   => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_LEFT,
                'posHorizontalRel'=> \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_MARGIN,
                'posVertical'     => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
                'posVerticalRel'  => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_MARGIN,
            ]);
        }

        // Logo KANAN — floating behind text, sudut kanan atas header, lebih kecil
        if ($logoKananExists) {
            $header->addImage($logoKananPath, [
                'height'          => 40,
                'wrappingStyle'   => 'behind',
                'positioning'     => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
                'posHorizontal'   => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                'posHorizontalRel'=> \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_MARGIN,
                'posVertical'     => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
                'posVerticalRel'  => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_MARGIN,
            ]);
        }

        // Teks kop surat — full width, centered
        $headerTable = $header->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF']);
        $headerTable->addRow();
        $hCell = $headerTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF', 'valign' => 'center']);
        $hCell->addText('SEKOLAH TINGGI ILMU KOMPUTER', [
            'name' => 'Times New Roman', 'size' => 14, 'bold' => true
        ], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $hCell->addText('STIKOM TUNAS BANGSA', [
            'name' => 'Times New Roman', 'size' => 16, 'bold' => true
        ], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $hCell->addText('SK MENDIKBUD RISTEK RI. NO. 513/E/O/2022 Tanggal 13 Juli 2022', [
            'name' => 'Times New Roman', 'size' => 9
        ], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $hCell->addText('Program Studi: Diploma 3 (D3): Manajemen Informatika, Komputerisasi Akuntansi | Sarjana (S1): Sistem Informasi, Teknik Informatika | Magister (S2): Informatika', [
            'name' => 'Times New Roman', 'size' => 8
        ], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // Garis pembatas bawah header
        $header->addLine(['width' => 5000, 'height' => 10]);

        // === FOOTER: Halaman / Tanggal ===
        $footer = $section->addFooter();
        $footer->addPreserveText(
            'Tidak dibenarkan memindahkan barang-barang yang ada pada daftar ini tanpa sepengetahuan penanggung jawab ruangan ini.',
            ['name' => 'Times New Roman', 'size' => 9, 'italic' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $footer->addText(
            'Data ini di Update terakhir pada ' . date('F Y'),
            ['name' => 'Times New Roman', 'size' => 9, 'italic' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        // === ISI PER RUANGAN ===
        $isFirst = true;
        foreach ($ruanganData as $ruang) {
            if (!$isFirst) {
                $section->addPageBreak();
            }
            $isFirst = false;

            // Judul
            $section->addText('DAFTAR INVENTARIS RUANGAN', [
                'name' => 'Times New Roman', 'size' => 14, 'bold' => true
            ], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 200]);

            // Info ruangan
            $infoRows = [
                ['Tipe Ruangan', $ruang['tipe_ruangan']],
                ['Lokasi',       $ruang['nama_gedung'] . ' / Lantai ' . $ruang['lantai']],
                ['Nama Ruangan', $ruang['nama_ruangan']],
            ];

            foreach ($infoRows as [$label, $val]) {
                $infoTable = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMarginTop' => 30, 'cellMarginBottom' => 30]);
                $infoTable->addRow();
                $infoTable->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(4), ['borderSize' => 0, 'borderColor' => 'FFFFFF'])
                    ->addText($label, ['name' => 'Times New Roman', 'size' => 12, 'bold' => true]);
                $infoTable->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5), ['borderSize' => 0, 'borderColor' => 'FFFFFF'])
                    ->addText(':', ['name' => 'Times New Roman', 'size' => 12]);
                $infoTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])
                    ->addText($val, ['name' => 'Times New Roman', 'size' => 12]);
            }

            $section->addTextBreak(1);

            // Tabel barang
            $tableStyle = [
                'borderSize'  => 6,
                'borderColor' => '000000',
                'cellMarginTop'    => 60,
                'cellMarginBottom' => 60,
                'cellMarginLeft'   => 60,
                'cellMarginRight'  => 60,
            ];

            $table = $section->addTable($tableStyle);

            // Header tabel
            $thStyle = ['bgColor' => 'D3D3D3', 'borderSize' => 6, 'borderColor' => '000000'];
            $thFont  = ['name' => 'Times New Roman', 'size' => 11, 'bold' => true];
            $thPar   = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];

            $table->addRow(null, ['tblHeader' => true]);
            $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(1),    $thStyle)->addText('No',          $thFont, $thPar);
            $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(6.5),  $thStyle)->addText('Nama Barang', $thFont, $thPar);
            $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.5),  $thStyle)->addText('Merek/Ukuran',$thFont, $thPar);
            $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.5),  $thStyle)->addText('Jumlah',      $thFont, $thPar);
            $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(4),    $thStyle)->addText('Keterangan',  $thFont, $thPar);

            $tdFont = ['name' => 'Times New Roman', 'size' => 11];
            $tdC    = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
            $tdL    = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT];

            if (empty($ruang['barang'])) {
                $table->addRow();
                $emptyCell = $table->addCell(null, ['gridSpan' => 5, 'borderSize' => 6, 'borderColor' => '000000']);
                $emptyCell->addText('Tidak ada barang pada ruangan ini.', ['name' => 'Times New Roman', 'size' => 11, 'italic' => true], $tdC);
            } else {
                foreach ($ruang['barang'] as $idx => $barang) {
                    $table->addRow();
                    $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(1),   ['borderSize' => 6, 'borderColor' => '000000'])->addText($idx + 1,                  $tdFont, $tdC);
                    $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(6.5), ['borderSize' => 6, 'borderColor' => '000000'])->addText($barang['nama_barang'],   $tdFont, $tdL);
                    $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.5), ['borderSize' => 6, 'borderColor' => '000000'])->addText($barang['merek_ukuran'],  $tdFont, $tdL);
                    $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.5), ['borderSize' => 6, 'borderColor' => '000000'])->addText($barang['jumlah'],        $tdFont, $tdC);
                    $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(4),   ['borderSize' => 6, 'borderColor' => '000000'])->addText($barang['kondisi'],       $tdFont, $tdL);
                }
            }

            // TTD
            $section->addTextBreak(2);
            $section->addText(
                'Pematangsiantar, ' . date('d F Y'),
                ['name' => 'Times New Roman', 'size' => 11],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]
            );
            $section->addText('Mengetahui', ['name' => 'Times New Roman', 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);
            $section->addTextBreak(3);

            $ttdTable = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF']);
            $ttdTable->addRow();
            $ttdTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(
                'Dr. Dedy Hartama, S.T., M.Kom',
                ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
            $ttdTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(
                'Riki Winanjaya, M.Kom',
                ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
            $ttdTable->addRow();
            $ttdTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(
                'Ketua STIKOM Tunas Bangsa',
                ['name' => 'Times New Roman', 'size' => 11],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
            $ttdTable->addCell(null, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])->addText(
                'Wakil Ketua 2',
                ['name' => 'Times New Roman', 'size' => 11],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
        }

        // Simpan & download
        $filename = 'Inventaris_Barang_' . date('Ymd_His') . '.docx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'docx_');

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpFile);

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }

    // ================================================================
    // CETAK XLSX
    // ================================================================
    public function cetakXlsx()
    {
        $ruanganData = $this->getDataCetak();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);

        $sheetIdx = 0;
        foreach ($ruanganData as $ruang) {
            if ($sheetIdx === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            // Nama sheet (maks 31 karakter untuk Excel)
            $sheetName = substr($ruang['nama_ruangan'], 0, 31);
            $sheet->setTitle($sheetName);

            // Style dasar
            $fontTNR = function(int $size = 12, bool $bold = false) {
                return ['font' => ['name' => 'Times New Roman', 'size' => $size, 'bold' => $bold]];
            };

            $centerAlign = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
            $leftAlign   = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,   'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];

            $row = 1;

            $logoKiriPath  = FCPATH . 'assets/img/logo_kiri.png';
            $logoKananPath = FCPATH . 'assets/img/logo_kanan.png';

            // === HEADER KOPSURAT ===
            // Baris 1: Nama kampus
            $sheet->getRowDimension($row)->setRowHeight(22);
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->setCellValue("B{$row}", 'SEKOLAH TINGGI ILMU KOMPUTER STIKOM TUNAS BANGSA');
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(14, true), $centerAlign));
            $row++;

            // Baris 2: SK
            $sheet->getRowDimension($row)->setRowHeight(14);
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->setCellValue("B{$row}", 'SK MENDIKBUD RISTEK RI. NO. 513/E/O/2022 Tanggal 13 Juli 2022');
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(9), $centerAlign));
            $row++;

            // Baris 3: Program Studi
            $sheet->getRowDimension($row)->setRowHeight(28);
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->setCellValue("B{$row}", 'Program Studi: D3: Manajemen Informatika, Komputerisasi Akuntansi | S1: Sistem Informasi, Teknik Informatika | S2: Informatika');
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(8), $centerAlign));
            $sheet->getStyle("B{$row}")->getAlignment()->setWrapText(true);

            // Logo kiri (A1:A3 merged & image)
            $sheet->mergeCells("A1:A3");
            $sheet->getColumnDimension('A')->setWidth(12);
            if (file_exists($logoKiriPath)) {
                $imgKiri = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $imgKiri->setName('Logo Kiri');
                $imgKiri->setDescription('Logo Kiri');
                $imgKiri->setPath($logoKiriPath);
                $imgKiri->setHeight(60);
                $imgKiri->setCoordinates('A1');
                $imgKiri->setOffsetX(4);
                $imgKiri->setOffsetY(4);
                $imgKiri->setWorksheet($sheet);
            }

            // Logo kanan (G1:G3 merged & image)
            $sheet->mergeCells("G1:G3");
            $sheet->getColumnDimension('G')->setWidth(12);
            if (file_exists($logoKananPath)) {
                $imgKanan = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $imgKanan->setName('Logo Kanan');
                $imgKanan->setDescription('Logo Kanan');
                $imgKanan->setPath($logoKananPath);
                $imgKanan->setHeight(60);
                $imgKanan->setCoordinates('G1');
                $imgKanan->setOffsetX(4);
                $imgKanan->setOffsetY(4);
                $imgKanan->setWorksheet($sheet);
            }

            // Garis bawah header (baris 3)
            $sheet->getStyle("A3:G3")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);
            $row++;

            // === JUDUL ===
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->setCellValue("B{$row}", 'DAFTAR INVENTARIS RUANGAN');
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(14, true), $centerAlign));
            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;
            $row++; // spasi

            // === INFO RUANGAN ===
            $infoItems = [
                ['Tipe Ruangan', $ruang['tipe_ruangan']],
                ['Lokasi',       $ruang['nama_gedung'] . ' / Lantai ' . $ruang['lantai']],
                ['Nama Ruangan', $ruang['nama_ruangan']],
            ];
            foreach ($infoItems as [$label, $val]) {
                $sheet->setCellValue("B{$row}", $label);
                $sheet->setCellValue("C{$row}", ':');
                $sheet->mergeCells("D{$row}:F{$row}");
                $sheet->setCellValue("D{$row}", $val);
                $sheet->getStyle("B{$row}:F{$row}")->applyFromArray($fontTNR(12, false));
                $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                $row++;
            }
            $row++; // spasi

            // === HEADER TABEL ===
            $headers = ['No', 'Nama Barang', 'Merek/Ukuran', 'Jumlah', 'Keterangan'];
            $cols    = ['B', 'C', 'D', 'E', 'F'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue($cols[$i] . $row, $h);
            }
            $sheet->getStyle("B{$row}:F{$row}")->applyFromArray(array_merge(
                $fontTNR(11, true),
                $centerAlign,
                ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]],
                ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]]
            ));
            $row++;

            // === DATA BARANG ===
            if (empty($ruang['barang'])) {
                $sheet->mergeCells("B{$row}:F{$row}");
                $sheet->setCellValue("B{$row}", 'Tidak ada barang pada ruangan ini.');
                $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(11, false), $centerAlign));
                $sheet->getStyle("B{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $row++;
            } else {
                foreach ($ruang['barang'] as $idx => $barang) {
                    $sheet->setCellValue("B{$row}", $idx + 1);
                    $sheet->setCellValue("C{$row}", $barang['nama_barang']);
                    $sheet->setCellValue("D{$row}", $barang['merek_ukuran']);
                    $sheet->setCellValue("E{$row}", $barang['jumlah']);
                    $sheet->setCellValue("F{$row}", $barang['kondisi']);

                    $sheet->getStyle("B{$row}:F{$row}")->applyFromArray(array_merge(
                        $fontTNR(11),
                        ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]]
                    ));
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $row++;
                }
            }

            $row += 2;

            // === FOOTER TANDA TANGAN ===
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->setCellValue("B{$row}", 'Tidak dibenarkan memindahkan barang-barang yang ada pada daftar ini tanpa sepengetahuan penanggung jawab ruangan ini.');
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(9, false), $centerAlign));
            $sheet->getStyle("B{$row}")->getFont()->setItalic(true);
            $sheet->getStyle("B{$row}")->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(30);
            $row++;

            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->setCellValue("B{$row}", 'Data ini di Update terakhir pada ' . date('F Y'));
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(9, false), $centerAlign));
            $sheet->getStyle("B{$row}")->getFont()->setItalic(true);
            $row++;

            $sheet->mergeCells("E{$row}:F{$row}");
            $sheet->setCellValue("E{$row}", 'Pematangsiantar, ' . date('d F Y'));
            $sheet->getStyle("E{$row}")->applyFromArray($fontTNR(11));
            $row++;

            $sheet->mergeCells("E{$row}:F{$row}");
            $sheet->setCellValue("E{$row}", 'Mengetahui');
            $sheet->getStyle("E{$row}")->applyFromArray($fontTNR(11));
            $row += 4; // ruang TTD

            $sheet->mergeCells("B{$row}:C{$row}");
            $sheet->setCellValue("B{$row}", 'Dr. Dedy Hartama, S.T., M.Kom');
            $sheet->mergeCells("E{$row}:F{$row}");
            $sheet->setCellValue("E{$row}", 'Riki Winanjaya, M.Kom');
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(11, true), $centerAlign));
            $sheet->getStyle("E{$row}")->applyFromArray(array_merge($fontTNR(11, true), $centerAlign));
            $row++;

            $sheet->mergeCells("B{$row}:C{$row}");
            $sheet->setCellValue("B{$row}", 'Ketua STIKOM Tunas Bangsa');
            $sheet->mergeCells("E{$row}:F{$row}");
            $sheet->setCellValue("E{$row}", 'Wakil Ketua 2');
            $sheet->getStyle("B{$row}")->applyFromArray(array_merge($fontTNR(11), $centerAlign));
            $sheet->getStyle("E{$row}")->applyFromArray(array_merge($fontTNR(11), $centerAlign));

            // === LEBAR KOLOM ===
            $sheet->getColumnDimension('A')->setWidth(12); // logo kiri
            $sheet->getColumnDimension('B')->setWidth(5);  // No
            $sheet->getColumnDimension('C')->setWidth(30); // Nama Barang
            $sheet->getColumnDimension('D')->setWidth(18); // Merek/Ukuran
            $sheet->getColumnDimension('E')->setWidth(10); // Jumlah
            $sheet->getColumnDimension('F')->setWidth(20); // Keterangan
            $sheet->getColumnDimension('G')->setWidth(12); // logo kanan

            // Setup print area & page setup
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            $sheet->getPageMargins()->setTop(1)->setBottom(1)->setLeft(1.2)->setRight(0.8);
            $sheet->getHeaderFooter()->setOddFooter(
                '&C&"Times New Roman,Italic"&8Tidak dibenarkan memindahkan barang-barang yang ada pada daftar ini tanpa sepengetahuan penanggung jawab ruangan ini.'
            );

            $sheetIdx++;
        }

        // Download
        $filename = 'Inventaris_Barang_' . date('Ymd_His') . '.xlsx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'xlsx_');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tmpFile);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }

    private function buildHtmlCetak(array $ruanganData): string
    {
        // Logo sebagai base64 agar bisa dirender oleh dompdf
        $logoKiriPath  = FCPATH . 'assets/img/logo_kiri.png';
        $logoKananPath = FCPATH . 'assets/img/logo_kanan.png';
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

            // Kop surat — logo absolute behind text
            $html .= '<div class="kop-outer">';
            if ($logoKiri)  { $html .= '<img class="kop-logo-kiri"  src="' . $logoKiri  . '" alt="Logo Kiri">'; }
            if ($logoKanan) { $html .= '<img class="kop-logo-kanan" src="' . $logoKanan . '" alt="Logo Kanan">'; }
            $html .= '<div class="kop-text">';
            $html .= '<p class="nama-kampus">SEKOLAH TINGGI ILMU KOMPUTER</p>';
            $html .= '<p class="nama-singkatan">STIKOM TUNAS BANGSA</p>';
            $html .= '<p class="sk">SK MENDIKBUD RISTEK RI. NO. 513/E/O/2022 Tanggal 13 Juli 2022</p>';
            $html .= '<p class="prodi">Program Studi: Diploma 3 (D3): Manajemen Informatika, Komputerisasi Akuntansi | Sarjana (S1): Sistem Informasi, Teknik Informatika | Magister (S2): Informatika</p>';
            $html .= '</div>';
            $html .= '</div>';

            // Judul
            $html .= '<div class="judul">DAFTAR INVENTARIS RUANGAN</div>';

            // Info ruangan
            $html .= '<table class="info-table">';
            $html .= '<tr><td class="label">Tipe Ruangan</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['tipe_ruangan']) . '</td></tr>';
            $html .= '<tr><td class="label">Lokasi</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['nama_gedung'] . ' / Lantai ' . $ruang['lantai']) . '</td></tr>';
            $html .= '<tr><td class="label">Nama Ruangan</td><td class="sep">:</td><td>' . htmlspecialchars($ruang['nama_ruangan']) . '</td></tr>';
            $html .= '</table>';

            // Tabel barang
            $html .= '<table class="data">';
            $html .= '<thead><tr>';
            $html .= '<th style="width:5%">No</th>';
            $html .= '<th style="width:35%">Nama Barang</th>';
            $html .= '<th style="width:22%">Merek/Ukuran</th>';
            $html .= '<th style="width:8%">Jumlah</th>';
            $html .= '<th style="width:30%">Keterangan</th>';
            $html .= '</tr></thead><tbody>';

            if (empty($ruang['barang'])) {
                $html .= '<tr><td colspan="5" class="center"><em>Tidak ada barang pada ruangan ini.</em></td></tr>';
            } else {
                foreach ($ruang['barang'] as $idx => $barang) {
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

            // Footer note & TTD
            $html .= '<div class="footer-note">Tidak dibenarkan memindahkan barang-barang yang ada pada daftar ini tanpa sepengetahuan penanggung jawab ruangan ini.<br>';
            $html .= 'Data ini di Update terakhir pada ' . date('F Y') . '</div>';

            $html .= '<p style="text-align:right;font-size:11pt;margin:12px 0 2px;">Pematangsiantar, ' . date('d F Y') . '</p>';
            $html .= '<p style="text-align:right;font-size:11pt;margin:0 0 60px;">Mengetahui</p>';

            $html .= '<table class="ttd-table"><tr>';
            $html .= '<td><p class="ttd-name">Dr. Dedy Hartama, S.T., M.Kom</p><p>Ketua STIKOM Tunas Bangsa</p></td>';
            $html .= '<td><p class="ttd-name">Riki Winanjaya, M.Kom</p><p>Wakil Ketua 2</p></td>';
            $html .= '</tr></table>';

            $html .= '</div>'; // .page
        }

        $html .= '</body></html>';
        return $html;
    }
}
