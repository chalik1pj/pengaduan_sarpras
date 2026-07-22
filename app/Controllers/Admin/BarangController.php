<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BarangModel;
use App\Models\GedungModel;
use App\Models\RuanganModel;
use App\Services\InventarisExporter;

class BarangController extends BaseController
{
    protected BarangModel       $model;
    protected GedungModel       $gedungModel;
    protected RuanganModel      $ruanganModel;
    protected InventarisExporter $exporter;

    public function __construct()
    {
        $this->model        = new BarangModel();
        $this->gedungModel  = new GedungModel();
        $this->ruanganModel = new RuanganModel();
        $this->exporter     = new InventarisExporter();
    }

    public function index()
    {
        $filterGedung  = $this->request->getGet('gedung') ?? '';
        $filterRuangan = $this->request->getGet('ruangan') ?? '';
        $search        = $this->request->getGet('q') ?? '';

        $query = $this->model->withRuanganGedung();

        if (!empty($filterGedung) && is_numeric($filterGedung)) {
            $query->where('gedung.id_gedung', (int) $filterGedung);
        }
        if (!empty($filterRuangan) && is_numeric($filterRuangan)) {
            $query->where('barang.id_ruangan', (int) $filterRuangan);
        }
        if (!empty($search)) {
            $query->like('barang.nama_barang', $search);
        }

        $items = $query
            ->orderBy('gedung.nama_gedung', 'ASC')
            ->orderBy('ruangan.nama_ruangan', 'ASC')
            ->orderBy('barang.nama_barang', 'ASC')
            ->findAll();

        $ruanganList = [];
        if (!empty($filterGedung) && is_numeric($filterGedung)) {
            $ruanganList = $this->ruanganModel
                ->where('id_gedung', (int) $filterGedung)
                ->orderBy('nama_ruangan', 'ASC')
                ->findAll();
        }

        return view('admin/barang/index', [
            'title'         => 'Manajemen Barang / Fasilitas',
            'items'         => $items,
            'gedung'        => $this->gedungModel->orderBy('nama_gedung', 'ASC')->findAll(),
            'ruanganList'   => $ruanganList,
            'filterGedung'  => $filterGedung,
            'filterRuangan' => $filterRuangan,
            'search'        => $search,
            'active'        => 'barang',
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
            'id_ruangan'   => (int) $this->request->getPost('id_ruangan'),
            'nama_barang'  => $this->request->getPost('nama_barang'),
            'merek_ukuran' => $this->request->getPost('merek_ukuran'),
            'jumlah'       => (int) $this->request->getPost('jumlah'),
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
            'id_ruangan'   => (int) $this->request->getPost('id_ruangan'),
            'nama_barang'  => $this->request->getPost('nama_barang'),
            'merek_ukuran' => $this->request->getPost('merek_ukuran'),
            'jumlah'       => (int) $this->request->getPost('jumlah'),
            'kondisi'      => $this->request->getPost('kondisi'),
        ]);

        return redirect()->back()->with('success', 'Barang berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->back()->with('success', 'Barang berhasil dihapus.');
    }

    public function cetakPreview()
    {
        return view('admin/barang/preview', [
            'title'  => 'Preview Cetak Inventaris',
            'gedung' => $this->gedungModel->orderBy('nama_gedung', 'ASC')->findAll(),
            'active' => 'barang',
        ]);
    }

    public function cetakPreviewContent()
    {
        $ruanganIds = $this->request->getGet('ruangan') ?? [];
        if (!is_array($ruanganIds)) {
            $ruanganIds = $ruanganIds ? [(int) $ruanganIds] : [];
        }
        $ruanganIds = array_values(array_filter(array_map('intval', $ruanganIds)));

        if (empty($ruanganIds)) {
            return $this->response->setJSON(['html' => '', 'count' => 0]);
        }

        $ruanganData = $this->getDataCetak($ruanganIds);
        $html        = $this->exporter->buildPreviewHtml($ruanganData);

        return $this->response->setJSON(['html' => $html, 'count' => count($ruanganData)]);
    }

    public function cetakPdf()
    {
        $ruanganData = $this->getDataCetak();
        $this->exporter->streamPdf($ruanganData);
    }

    public function cetakDocx()
    {
        $ruanganData = $this->getDataCetak();
        $this->exporter->streamDocx($ruanganData);
    }

    public function cetakXlsx()
    {
        $ruanganData = $this->getDataCetak();
        $this->exporter->streamXlsx($ruanganData);
    }

    private function getDataCetak(array $forceIds = []): array
    {
        $db = \Config\Database::connect();

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
            $placeholders = implode(',', array_fill(0, count($forceIds), '?'));
            $where[]      = "r.id_ruangan IN ({$placeholders})";
            $params       = array_map('intval', $forceIds);
        } else {
            $filterGedung  = $this->request->getGet('gedung') ?? '';
            $filterRuangan = $this->request->getGet('ruangan');

            if (!empty($filterGedung) && is_numeric($filterGedung)) {
                $where[]  = 'g.id_gedung = ?';
                $params[] = (int) $filterGedung;
            }

            if (!empty($filterRuangan)) {
                if (is_array($filterRuangan)) {
                    $ids = array_filter(array_map('intval', $filterRuangan));
                    if (!empty($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $where[]      = "r.id_ruangan IN ({$placeholders})";
                        $params       = array_merge($params, array_values($ids));
                    }
                } elseif (is_numeric($filterRuangan)) {
                    $where[]  = 'r.id_ruangan = ?';
                    $params[] = (int) $filterRuangan;
                }
            }
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY g.nama_gedung ASC, r.lantai ASC, r.nama_ruangan ASC, b.nama_barang ASC';

        $rows = $db->query($sql, $params)->getResultArray();

        $ruangan = [];
        foreach ($rows as $row) {
            $roomId = $row['id_ruangan'];
            if (!isset($ruangan[$roomId])) {
                $ruangan[$roomId] = [
                    'id_ruangan'   => $row['id_ruangan'],
                    'nama_ruangan' => $row['nama_ruangan'],
                    'tipe_ruangan' => $row['tipe_ruangan'],
                    'lantai'       => $row['lantai'],
                    'nama_gedung'  => $row['nama_gedung'],
                    'barang'       => [],
                ];
            }
            if (!empty($row['id_barang'])) {
                $ruangan[$roomId]['barang'][] = [
                    'nama_barang'  => $row['nama_barang'],
                    'merek_ukuran' => $row['merek_ukuran'] ?? '-',
                    'jumlah'       => $row['jumlah'],
                    'kondisi'      => $row['kondisi'],
                ];
            }
        }

        return array_values($ruangan);
    }
}
