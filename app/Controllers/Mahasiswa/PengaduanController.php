<?php

namespace App\Controllers\Mahasiswa;

use App\Controllers\BaseController;
use App\Models\PengaduanModel;
use App\Models\FotoPengaduanModel;
use App\Models\LogStatusModel;
use App\Models\GedungModel;
use App\Models\RuanganModel;
use App\Models\BarangModel;
use App\Models\KategoriPengaduanModel;
use App\Models\StatusPengaduanModel;
use App\Models\PetugasModel;

class PengaduanController extends BaseController
{
    protected PengaduanModel       $pengaduanModel;
    protected FotoPengaduanModel   $fotoModel;
    protected LogStatusModel       $logModel;
    protected GedungModel          $gedungModel;
    protected RuanganModel         $ruanganModel;
    protected BarangModel          $barangModel;
    protected KategoriPengaduanModel $kategoriModel;
    protected StatusPengaduanModel   $statusModel;
    protected PetugasModel           $petugasModel;

    public function __construct()
    {
        helper(['whatsapp', 'filesystem']);
        $this->pengaduanModel = new PengaduanModel();
        $this->fotoModel      = new FotoPengaduanModel();
        $this->logModel       = new LogStatusModel();
        $this->gedungModel    = new GedungModel();
        $this->ruanganModel   = new RuanganModel();
        $this->barangModel    = new BarangModel();
        $this->kategoriModel  = new KategoriPengaduanModel();
        $this->statusModel    = new StatusPengaduanModel();
        $this->petugasModel   = new PetugasModel();
    }

    public function index()
    {
        $nim   = session()->get('mahasiswa_nim');
        $items = $this->pengaduanModel->getByNim($nim);

        return view('mahasiswa/pengaduan/index', [
            'title'  => 'Riwayat Pengaduan',
            'items'  => $items,
            'active' => 'pengaduan',
        ]);
    }

    public function create()
    {
        return view('mahasiswa/pengaduan/create', [
            'title'     => 'Buat Pengaduan Baru',
            'gedung'    => $this->gedungModel->orderBy('nama_gedung','ASC')->findAll(),
            'kategori'  => $this->kategoriModel->findAll(),
            'active'    => 'buat',
        ]);
    }

    public function store()
    {
        $nim = session()->get('mahasiswa_nim');

        $rules = [
            'id_gedung'   => 'required|integer',
            'id_ruangan'  => 'required|integer',
            'id_kategori' => 'required|integer',
            'judul'       => 'required|min_length[5]|max_length[150]',
            'deskripsi'   => 'required|min_length[10]',
            'prioritas'   => 'required|in_list[rendah,sedang,tinggi]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $files     = $this->request->getFiles();
        $fotoFiles = $files['foto_bukti'] ?? [];

        if (!is_array($fotoFiles)) {
            $fotoFiles = [$fotoFiles];
        }

        $validFotos = [];
        foreach ($fotoFiles as $file) {
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Hanya file gambar (JPG, PNG, GIF, WEBP) yang diizinkan.');
                }
                if ($file->getSize() > 1048576) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Ukuran setiap foto maksimal 1MB.');
                }
                $validFotos[] = $file;
            }
        }

        // Maksimal 3 foto
        if (count($validFotos) > 3) {
            return redirect()->back()->withInput()
                ->with('error', 'Maksimal 3 foto yang dapat diunggah.');
        }

        $kode        = $this->pengaduanModel->generateKode();

        $idBarang = $this->request->getPost('id_barang');
        $idBarang = (!empty($idBarang) && is_numeric($idBarang)) ? (int)$idBarang : null;

        $dataInsert = [
            'kode_pengaduan'    => $kode,
            'nim'               => $nim,
            'id_ruangan'        => (int)$this->request->getPost('id_ruangan'),
            'id_barang'         => $idBarang,
            'id_kategori'       => (int)$this->request->getPost('id_kategori'),
            'judul'             => $this->request->getPost('judul'),
            'deskripsi'         => $this->request->getPost('deskripsi'),
            'prioritas'         => $this->request->getPost('prioritas'),
            'id_status_saat_ini' => 1,
        ];

        $idPengaduan = $this->pengaduanModel->insert($dataInsert, true);

        if (!$idPengaduan) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pengaduan. Silakan coba lagi.');
        }

        // Upload foto
        $uploadPath = FCPATH . 'uploads/pengaduan/' . $idPengaduan . '/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        foreach ($validFotos as $urutan => $file) {
            $newName = 'foto_' . ($urutan + 1) . '_' . time() . '.' . $file->getExtension();
            $file->move($uploadPath, $newName);
            $this->fotoModel->insert([
                'id_pengaduan' => $idPengaduan,
                'nama_file'    => $newName,
                'path_file'    => 'uploads/pengaduan/' . $idPengaduan . '/' . $newName,
                'urutan'       => $urutan + 1,
            ]);
        }

        // Notifikasi WhatsApp ke semua petugas aktif
        try {
            $token   = env('app.fonnteToken', '');
            $petugas = $this->petugasModel->getAdminList();

            if (!empty($token) && !empty($petugas)) {
                $pengaduanData = $this->pengaduanModel->getWithDetail()->find($idPengaduan);
                if ($pengaduanData) {
                    notifikasi_pengaduan_baru($pengaduanData, $petugas, $token);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'WhatsApp notif error: ' . $e->getMessage());
        }

        return redirect()->to(base_url('mahasiswa/pengaduan/' . $idPengaduan))
            ->with('success', 'Pengaduan berhasil dikirim! Kode: ' . $kode);
    }

    public function show(int $id)
    {
        $nim        = session()->get('mahasiswa_nim');
        $pengaduan  = $this->pengaduanModel->getWithDetail()->where('pengaduan.nim', $nim)->find($id);

        if (!$pengaduan) {
            return redirect()->to(base_url('mahasiswa/pengaduan'))->with('error', 'Pengaduan tidak ditemukan.');
        }

        $fotos     = $this->fotoModel->getByPengaduan($id);
        $logs      = $this->logModel->getByPengaduan($id);
        $allStatus = $this->statusModel->getOrdered();

        return view('mahasiswa/pengaduan/detail', [
            'title'     => 'Detail Pengaduan',
            'pengaduan' => $pengaduan,
            'fotos'     => $fotos,
            'logs'      => $logs,
            'allStatus' => $allStatus,
            'active'    => 'pengaduan',
        ]);
    }

    public function delete(int $id)
    {
        $nim       = session()->get('mahasiswa_nim');
        $pengaduan = $this->pengaduanModel->where('nim', $nim)->where('id_status_saat_ini', 1)->find($id);

        if (!$pengaduan) {
            return redirect()->back()->with('error', 'Pengaduan tidak dapat dihapus.');
        }

        $this->pengaduanModel->delete($id);
        return redirect()->to(base_url('mahasiswa/pengaduan'))->with('success', 'Pengaduan berhasil dihapus.');
    }
}
