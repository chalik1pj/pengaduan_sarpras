<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;
use App\Models\PengaduanModel;
use App\Models\FotoPengaduanModel;
use App\Models\LogStatusModel;
use App\Models\LaporanPetugasModel;
use App\Models\FotoLaporanModel;
use App\Models\PetugasModel;

class PengaduanController extends BaseController
{
    protected PengaduanModel      $pengaduanModel;
    protected FotoPengaduanModel  $fotoModel;
    protected LogStatusModel      $logModel;
    protected LaporanPetugasModel $laporanModel;
    protected FotoLaporanModel    $fotoLaporanModel;
    protected PetugasModel        $petugasModel;

    public function __construct()
    {
        $this->pengaduanModel   = new PengaduanModel();
        $this->fotoModel        = new FotoPengaduanModel();
        $this->logModel         = new LogStatusModel();
        $this->laporanModel     = new LaporanPetugasModel();
        $this->fotoLaporanModel = new FotoLaporanModel();
        $this->petugasModel     = new PetugasModel();
        helper(['whatsapp', 'url', 'form']);
    }

    public function index()
    {
        $idPetugas = session()->get('admin_id');
        $filterStatus = $this->request->getGet('status') ?? '';
        $search       = $this->request->getGet('q') ?? '';

        $query = $this->pengaduanModel->getWithDetail()
            ->where('pengaduan.id_petugas_penangani', $idPetugas);

        if (!empty($filterStatus) && is_numeric($filterStatus)) {
            $query->where('pengaduan.id_status_saat_ini', (int)$filterStatus);
        }
        if (!empty($search)) {
            $query->like('pengaduan.judul', $search);
        }

        $pengaduan = $query->orderBy('pengaduan.tanggal_pengaduan', 'DESC')->findAll();

        return view('petugas/pengaduan/index', [
            'title'        => 'Pengaduan Saya',
            'active'       => 'pengaduan',
            'pengaduan'    => $pengaduan,
            'filterStatus' => $filterStatus,
            'search'       => $search,
        ]);
    }

    /**
     * Detail pengaduan + form upload laporan progres
     */
    public function show(int $id)
    {
        $idPetugas = session()->get('admin_id');

        $pengaduan = $this->pengaduanModel->getWithDetail()
            ->where('pengaduan.id_pengaduan', $id)
            ->where('pengaduan.id_petugas_penangani', $idPetugas)
            ->first();

        if (!$pengaduan) {
            return redirect()->to(base_url('petugas/pengaduan'))
                ->with('error', 'Pengaduan tidak ditemukan atau bukan tugas Anda.');
        }

        $fotosPengaduan = $this->fotoModel->getByPengaduan($id);
        $logs           = $this->logModel->getByPengaduan($id);
        $laporan        = $this->laporanModel->getByPengaduan($id);

        foreach ($laporan as &$lap) {
            $lap['fotos'] = $this->fotoLaporanModel->getByLaporan($lap['id_laporan']);
        }
        unset($lap);

        return view('petugas/pengaduan/detail', [
            'title'         => 'Detail Pengaduan',
            'active'        => 'pengaduan',
            'pengaduan'     => $pengaduan,
            'fotosPengaduan'=> $fotosPengaduan,
            'logs'          => $logs,
            'laporan'       => $laporan,
        ]);
    }

    /**
     * Simpan laporan progres petugas + upload foto
     */
    public function simpanLaporan(int $idPengaduan)
    {
        $idPetugas = session()->get('admin_id');

        // Pastikan pengaduan ini memang tugas petugas ini
        $pengaduan = $this->pengaduanModel->getWithDetail()
            ->where('pengaduan.id_pengaduan', $idPengaduan)
            ->where('pengaduan.id_petugas_penangani', $idPetugas)
            ->first();

        if (!$pengaduan) {
            return redirect()->to(base_url('petugas/pengaduan'))
                ->with('error', 'Pengaduan tidak ditemukan atau bukan tugas Anda.');
        }

        $rules = [
            'judul_laporan' => 'required|min_length[5]|max_length[200]',
            'deskripsi'     => 'required|min_length[10]',
            'status_laporan'=> 'required|in_list[proses,selesai]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Simpan laporan
        $idLaporan = $this->laporanModel->insert([
            'id_pengaduan'  => $idPengaduan,
            'id_petugas'    => $idPetugas,
            'judul_laporan' => $this->request->getPost('judul_laporan'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'status_laporan'=> $this->request->getPost('status_laporan'),
        ]);

        // Upload foto bukti (max 3, 1MB each)
        $uploadedFiles = $this->request->getFiles();
        if (!empty($uploadedFiles['foto_laporan']['foto_laporan'] ?? [])) {
            $uploadPath = FCPATH . 'uploads/laporan/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $urutan = 1;
            foreach ($uploadedFiles['foto_laporan']['foto_laporan'] as $foto) {
                if ($foto->isValid() && !$foto->hasMoved() && $urutan <= 3) {
                    if ($foto->getSize() > 1048576) {
                        continue; // skip > 1MB
                    }
                    $namaFile = $foto->getRandomName();
                    $foto->move($uploadPath, $namaFile);
                    $this->fotoLaporanModel->insert([
                        'id_laporan' => $idLaporan,
                        'nama_file'  => $namaFile,
                        'path_file'  => 'uploads/laporan/' . $namaFile,
                        'urutan'     => $urutan,
                    ]);
                    $urutan++;
                }
            }
        }

        $adminList = $this->petugasModel->getAdminList();

        $laporanData = [
            'id_pengaduan'  => $idPengaduan,
            'kode_pengaduan'=> $pengaduan['kode_pengaduan'],
            'judul_laporan' => $this->request->getPost('judul_laporan'),
            'nama_petugas'  => session()->get('admin_nama'),
            'status_laporan'=> $this->request->getPost('status_laporan'),
            'created_at'    => date('d M Y H:i'),
        ];

        notifikasi_laporan_petugas($laporanData, $adminList);

        return redirect()->back()->with('success', 'Laporan progres berhasil dikirim. Admin akan segera memverifikasi.');
    }
}
