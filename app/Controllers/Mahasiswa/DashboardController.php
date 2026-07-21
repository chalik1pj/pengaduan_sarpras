<?php

namespace App\Controllers\Mahasiswa;

use App\Controllers\BaseController;
use App\Models\PengaduanModel;
use App\Models\StatusPengaduanModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $pengaduanModel = new PengaduanModel();
        $nim = session()->get('mahasiswa_nim');

        $stats  = $pengaduanModel->getStatsByNim($nim);
        $recent = $pengaduanModel->getByNim($nim);

        // Hitung total
        $total = array_sum($stats);

        $data = [
            'title'   => 'Dashboard Mahasiswa',
            'stats'   => $stats,
            'total'   => $total,
            'recent'  => array_slice($recent, 0, 5),
            'active'  => 'dashboard',
        ];

        return view('mahasiswa/dashboard', $data);
    }
}
