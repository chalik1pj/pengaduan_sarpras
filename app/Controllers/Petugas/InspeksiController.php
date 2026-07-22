<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;
use App\Models\InspeksiFasilitasModel;
use App\Models\FotoInspeksiModel;
use App\Models\GedungModel;
use App\Models\RuanganModel;
use App\Models\BarangModel;
use App\Models\PetugasModel;

class InspeksiController extends BaseController
{
    protected InspeksiFasilitasModel $inspeksiModel;
    protected FotoInspeksiModel      $fotoInspeksiModel;
    protected GedungModel            $gedungModel;
    protected PetugasModel           $petugasModel;

    public function __construct()
    {
        $this->inspeksiModel     = new InspeksiFasilitasModel();
        $this->fotoInspeksiModel = new FotoInspeksiModel();
        $this->gedungModel       = new GedungModel();
        $this->petugasModel      = new PetugasModel();
        helper(['whatsapp', 'url', 'form']);
    }

    public function index()
    {
        $idPetugas = session()->get('admin_id');
        $inspeksi  = $this->inspeksiModel->getByPetugas($idPetugas);

        foreach ($inspeksi as &$item) {
            $item['fotos'] = $this->fotoInspeksiModel->getByInspeksi($item['id_inspeksi']);
        }
        unset($item);

        return view('petugas/inspeksi/index', [
            'title'    => 'Inspeksi Fasilitas',
            'active'   => 'inspeksi',
            'inspeksi' => $inspeksi,
        ]);
    }

    public function create()
    {
        return view('petugas/inspeksi/create', [
            'title'  => 'Buat Inspeksi Fasilitas',
            'active' => 'inspeksi',
            'gedung' => $this->gedungModel->orderBy('nama_gedung', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        $idPetugas = session()->get('admin_id');

        $rules = [
            'id_ruangan'    => 'required|integer',
            'judul_inspeksi'=> 'required|min_length[5]|max_length[200]',
            'kondisi_temuan'=> 'required|in_list[baik,perlu_perbaikan,rusak_berat]',
            'deskripsi'     => 'required|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idBarang = $this->request->getPost('id_barang');

        $idInspeksi = $this->inspeksiModel->insert([
            'id_petugas'     => $idPetugas,
            'id_ruangan'     => (int)$this->request->getPost('id_ruangan'),
            'id_barang'      => empty($idBarang) ? null : (int)$idBarang,
            'judul_inspeksi' => $this->request->getPost('judul_inspeksi'),
            'kondisi_temuan' => $this->request->getPost('kondisi_temuan'),
            'deskripsi'      => $this->request->getPost('deskripsi'),
            'rekomendasi'    => $this->request->getPost('rekomendasi'),
        ]);

        // Upload foto (max 3, 1MB each)
        $uploadedFiles = $this->request->getFiles();
        if (!empty($uploadedFiles['foto_inspeksi']['foto_inspeksi'] ?? [])) {
            $uploadPath = FCPATH . 'uploads/inspeksi/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $urutan = 1;
            foreach ($uploadedFiles['foto_inspeksi']['foto_inspeksi'] as $foto) {
                if ($foto->isValid() && !$foto->hasMoved() && $urutan <= 3) {
                    if ($foto->getSize() > 1048576) {
                        continue;
                    }
                    $namaFile = $foto->getRandomName();
                    $foto->move($uploadPath, $namaFile);
                    $this->fotoInspeksiModel->insert([
                        'id_inspeksi' => $idInspeksi,
                        'nama_file'   => $namaFile,
                        'path_file'   => 'uploads/inspeksi/' . $namaFile,
                        'urutan'      => $urutan,
                    ]);
                    $urutan++;
                }
            }
        }

        $inspeksiData = $this->inspeksiModel->getOneWithDetail($idInspeksi);

        if ($inspeksiData) {
            $inspeksiData['nama_petugas'] = session()->get('admin_nama');
            $inspeksiData['created_at']   = date('d M Y H:i');
            $inspeksiData['nama_barang']  = $inspeksiData['nama_barang'] ?? 'Umum/Ruangan';
            $adminList = $this->petugasModel->getAdminList();
            notifikasi_inspeksi_fasilitas($inspeksiData, $adminList);
        }

        return redirect()->to(base_url('petugas/inspeksi'))
            ->with('success', 'Laporan inspeksi fasilitas berhasil dikirim. Admin akan segera memverifikasi.');
    }

    public function show(int $id)
    {
        $idPetugas = session()->get('admin_id');
        $inspeksi  = $this->inspeksiModel->getOneWithDetail($id);

        if (!$inspeksi || $inspeksi['id_petugas'] !== $idPetugas) {
            return redirect()->to(base_url('petugas/inspeksi'))
                ->with('error', 'Inspeksi tidak ditemukan atau bukan milik Anda.');
        }

        $fotos = $this->fotoInspeksiModel->getByInspeksi($id);

        return view('petugas/inspeksi/detail', [
            'title'    => 'Detail Inspeksi',
            'active'   => 'inspeksi',
            'inspeksi' => $inspeksi,
            'fotos'    => $fotos,
        ]);
    }
}
