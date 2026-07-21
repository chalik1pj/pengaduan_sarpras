<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LaporanPetugasModel;
use App\Models\FotoLaporanModel;

class LaporanController extends BaseController
{
    protected LaporanPetugasModel $laporanModel;
    protected FotoLaporanModel    $fotoModel;

    public function __construct()
    {
        $this->laporanModel = new LaporanPetugasModel();
        $this->fotoModel    = new FotoLaporanModel();
    }

    /**
     * Daftar semua laporan petugas
     */
    public function index()
    {
        $filterVerif = $this->request->getGet('verif') ?? '';

        $query = $this->laporanModel->getWithDetail();

        if ($filterVerif === '0') {
            $query->where('laporan_petugas.diverifikasi', 0);
        } elseif ($filterVerif === '1') {
            $query->where('laporan_petugas.diverifikasi', 1);
        }

        $laporan = $query->orderBy('laporan_petugas.created_at', 'DESC')->findAll();

        foreach ($laporan as &$item) {
            $item['fotos'] = $this->fotoModel->getByLaporan($item['id_laporan']);
        }
        unset($item);

        $belumVerif = $this->laporanModel->where('diverifikasi', 0)->countAllResults();

        return view('admin/laporan/index', [
            'title'      => 'Laporan Petugas',
            'active'     => 'laporan',
            'laporan'    => $laporan,
            'belumVerif' => $belumVerif,
            'filterVerif'=> $filterVerif,
        ]);
    }

    /**
     * Detail laporan petugas
     */
    public function show(int $id)
    {
        $laporan = $this->laporanModel->getWithDetail()
            ->where('laporan_petugas.id_laporan', $id)->first();

        if (!$laporan) {
            return redirect()->to(base_url('admin/laporan'))->with('error', 'Laporan tidak ditemukan.');
        }

        $laporan['fotos'] = $this->fotoModel->getByLaporan($id);

        return view('admin/laporan/show', [
            'title'   => 'Detail Laporan',
            'active'  => 'laporan',
            'laporan' => $laporan,
        ]);
    }

    /**
     * Verifikasi laporan petugas
     */
    public function verifikasi(int $id)
    {
        $adminId      = session()->get('admin_id');
        $catatanVerif = $this->request->getPost('catatan_verif');
        $statusVerif  = (int)$this->request->getPost('diverifikasi');

        $laporan = $this->laporanModel->find($id);
        if (!$laporan) {
            return redirect()->back()->with('error', 'Laporan tidak ditemukan.');
        }

        $this->laporanModel->update($id, [
            'diverifikasi'  => $statusVerif,
            'id_verifikator'=> $adminId,
            'catatan_verif' => $catatanVerif,
            'waktu_verif'   => date('Y-m-d H:i:s'),
        ]);

        $msg = $statusVerif ? 'Laporan berhasil diverifikasi.' : 'Laporan ditandai belum terverifikasi.';
        return redirect()->back()->with('success', $msg);
    }
}
