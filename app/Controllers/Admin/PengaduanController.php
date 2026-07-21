<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengaduanModel;
use App\Models\FotoPengaduanModel;
use App\Models\LogStatusModel;
use App\Models\StatusPengaduanModel;
use App\Models\PetugasModel;

class PengaduanController extends BaseController
{
    protected PengaduanModel       $pengaduanModel;
    protected FotoPengaduanModel   $fotoModel;
    protected LogStatusModel       $logModel;
    protected StatusPengaduanModel $statusModel;
    protected PetugasModel         $petugasModel;

    public function __construct()
    {
        $this->pengaduanModel = new PengaduanModel();
        $this->fotoModel      = new FotoPengaduanModel();
        $this->logModel       = new LogStatusModel();
        $this->statusModel    = new StatusPengaduanModel();
        $this->petugasModel   = new PetugasModel();
        helper(['whatsapp', 'url']);
    }

    public function index()
    {
        $filter   = $this->request->getGet('status') ?? '';
        $search   = $this->request->getGet('q') ?? '';
        $gedung   = $this->request->getGet('gedung') ?? '';

        $query = $this->pengaduanModel->getWithDetail();

        if (!empty($filter) && is_numeric($filter)) {
            $query->where('pengaduan.id_status_saat_ini', (int)$filter);
        }
        if (!empty($search)) {
            $query->groupStart()
                ->like('pengaduan.judul', $search)
                ->orLike('pengaduan.kode_pengaduan', $search)
                ->orLike('mahasiswa.nama', $search)
                ->groupEnd();
        }
        if (!empty($gedung) && is_numeric($gedung)) {
            $query->where('gedung.id_gedung', (int)$gedung);
        }

        $items    = $query->orderBy('pengaduan.tanggal_pengaduan','DESC')->findAll();
        $statuses = $this->statusModel->getOrdered();

        return view('admin/pengaduan/index', [
            'title'       => 'Manajemen Pengaduan',
            'items'       => $items,
            'statuses'    => $statuses,
            'filterStatus'=> $filter,
            'search'      => $search,
            'filterGedung'=> $gedung,
            'active'      => 'pengaduan',
        ]);
    }

    public function show(int $id)
    {
        $pengaduan = $this->pengaduanModel->getWithDetail()->find($id);
        if (!$pengaduan) {
            return redirect()->to(base_url('admin/pengaduan'))->with('error', 'Pengaduan tidak ditemukan.');
        }

        $fotos    = $this->fotoModel->getByPengaduan($id);
        $logs     = $this->logModel->getByPengaduan($id);
        $statuses = $this->statusModel->getOrdered();
        $petugas  = $this->petugasModel->getAktif();

        // Laporan progres petugas untuk pengaduan ini
        $laporanModel    = new \App\Models\LaporanPetugasModel();
        $fotoLaporanModel= new \App\Models\FotoLaporanModel();
        $laporan = $laporanModel->getByPengaduan($id);
        foreach ($laporan as &$lap) {
            $lap['fotos'] = $fotoLaporanModel->getByLaporan($lap['id_laporan']);
        }
        unset($lap);

        return view('admin/pengaduan/detail', [
            'title'     => 'Detail Pengaduan',
            'pengaduan' => $pengaduan,
            'fotos'     => $fotos,
            'logs'      => $logs,
            'statuses'  => $statuses,
            'petugas'   => $petugas,
            'laporan'   => $laporan,
            'active'    => 'pengaduan',
        ]);
    }

    public function updateStatus(int $id)
    {
        $idStatus  = (int)$this->request->getPost('id_status');
        $idPetugas = $this->request->getPost('id_petugas');
        $catatan   = $this->request->getPost('catatan');
        $adminId   = session()->get('admin_id');

        $pengaduan = $this->pengaduanModel->find($id);
        if (!$pengaduan) {
            return redirect()->back()->with('error', 'Pengaduan tidak ditemukan.');
        }

        $statusBerubah  = ($pengaduan['id_status_saat_ini'] != $idStatus);
        $idPetugasInt   = empty($idPetugas) ? null : (int)$idPetugas;
        $petugasBerubah = ($pengaduan['id_petugas_penangani'] != $idPetugasInt);

        // Siapkan data update pengaduan
        $updateData = [
            'id_status_saat_ini'   => $idStatus,
            'id_petugas_penangani' => $idPetugasInt
        ];

        // Jika selesai (4), catat tanggal selesai
        if ($idStatus === 4) {
            $updateData['tanggal_selesai'] = date('Y-m-d H:i:s');
        }

        // Lakukan update pada tabel pengaduan
        $this->pengaduanModel->update($id, $updateData);

        // Kelola histori log perubahan status
        $db = \Config\Database::connect();
        
        if ($statusBerubah) {
            // Trigger DB otomatis memasukkan 1 log perubahan status.
            // Kita cukup update catatan & id_petugas admin pengubah pada log terakhir tersebut.
            $txtCatatan = !empty($catatan) ? $catatan : 'Status diperbarui oleh petugas';
            $db->query(
                'UPDATE log_status_pengaduan SET catatan = ?, id_petugas = ? WHERE id_pengaduan = ? ORDER BY id_log DESC LIMIT 1',
                [$txtCatatan, $adminId, $id]
            );
        } else {
            // Status TIDAK berubah. Tetapi apakah ada perubahan petugas atau ada catatan baru yang dimasukkan?
            if ($petugasBerubah || !empty($catatan)) {
                if (empty($catatan)) {
                    $txtCatatan = $petugasBerubah ? 'Petugas penanggung jawab lapangan diperbarui' : 'Perubahan disimpan';
                } else {
                    $txtCatatan = $catatan;
                }

                // Masukkan log manual karena trigger tidak jalan (status tidak berubah)
                $db->query(
                    'INSERT INTO log_status_pengaduan (id_pengaduan, id_status_lama, id_status_baru, id_petugas, catatan) VALUES (?, ?, ?, ?, ?)',
                    [$id, $idStatus, $idStatus, $adminId, $txtCatatan]
                );
            }
        }

        // Kirim notifikasi WA jika petugas baru saja ditugaskan
        if ($petugasBerubah && $idPetugasInt !== null) {
            $this->kirimNotifPetugasDitugaskan($id, $idPetugasInt);
        }

        return redirect()->back()->with('success', 'Perubahan berhasil disimpan.');
    }

    /**
     * Kirim notifikasi WA ke petugas yang baru saja ditugaskan
     */
    private function kirimNotifPetugasDitugaskan(int $idPengaduan, int $idPetugas): void
    {
        $petugas = $this->petugasModel->find($idPetugas);
        if (!$petugas || empty($petugas['no_wa'])) {
            return;
        }

        $pengaduan = $this->pengaduanModel->getWithDetail()
            ->where('pengaduan.id_pengaduan', $idPengaduan)->first();

        if ($pengaduan) {
            notifikasi_petugas_ditugaskan($pengaduan, $petugas);
        }
    }
}
