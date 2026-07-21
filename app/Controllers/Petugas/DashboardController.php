<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;
use App\Models\PengaduanModel;
use App\Models\LaporanPetugasModel;
use App\Models\InspeksiFasilitasModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $idPetugas = session()->get('admin_id');

        $pengaduanModel  = new PengaduanModel();
        $laporanModel    = new LaporanPetugasModel();
        $inspeksiModel   = new InspeksiFasilitasModel();

        // Pengaduan yang ditugaskan ke petugas ini
        $pengaduanDitugaskan = $pengaduanModel->getWithDetail()
            ->where('pengaduan.id_petugas_penangani', $idPetugas)
            ->orderBy('pengaduan.tanggal_pengaduan', 'DESC')
            ->findAll(5);

        $totalDitugaskan = $pengaduanModel
            ->where('id_petugas_penangani', $idPetugas)
            ->countAllResults();

        $totalDiproses = $pengaduanModel
            ->where('id_petugas_penangani', $idPetugas)
            ->where('id_status_saat_ini', 3)
            ->countAllResults();

        $totalSelesai = $pengaduanModel
            ->where('id_petugas_penangani', $idPetugas)
            ->where('id_status_saat_ini', 4)
            ->countAllResults();

        $totalLaporan   = $laporanModel->countByPetugas($idPetugas);
        $totalInspeksi  = $inspeksiModel->countByPetugas($idPetugas);

        return view('petugas/dashboard', [
            'title'               => 'Dashboard Petugas',
            'active'              => 'dashboard',
            'pengaduanDitugaskan' => $pengaduanDitugaskan,
            'totalDitugaskan'     => $totalDitugaskan,
            'totalDiproses'       => $totalDiproses,
            'totalSelesai'        => $totalSelesai,
            'totalLaporan'        => $totalLaporan,
            'totalInspeksi'       => $totalInspeksi,
        ]);
    }
}
