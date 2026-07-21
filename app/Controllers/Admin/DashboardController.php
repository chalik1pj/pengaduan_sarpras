<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengaduanModel;
use App\Models\MahasiswaModel;
use App\Models\GedungModel;
use App\Models\RuanganModel;
use App\Models\BarangModel;
use App\Models\PetugasModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $pengaduanModel = new PengaduanModel();
        $mahasiswaModel = new MahasiswaModel();
        $gedungModel    = new GedungModel();
        $petugasModel   = new PetugasModel();

        $stats         = $pengaduanModel->getDashboardStats();
        $recent        = $pengaduanModel->getRecent(8);
        $byGedung      = $pengaduanModel->getByGedung();
        $monthly       = $pengaduanModel->getMonthlyStats();
        $totalMhs      = count($mahasiswaModel->findAll());
        $totalGedung   = count($gedungModel->findAll());
        $totalPetugas  = count($petugasModel->getAktif());

        return view('admin/dashboard', [
            'title'       => 'Dashboard Admin',
            'stats'       => $stats,
            'recent'      => $recent,
            'byGedung'    => $byGedung,
            'monthly'     => $monthly,
            'totalMhs'    => $totalMhs,
            'totalGedung' => $totalGedung,
            'totalPetugas'=> $totalPetugas,
            'active'      => 'dashboard',
        ]);
    }
}
