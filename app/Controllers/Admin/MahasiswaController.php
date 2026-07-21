<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;

class MahasiswaController extends BaseController
{
    protected MahasiswaModel $model;

    public function __construct()
    {
        $this->model = new MahasiswaModel();
    }

    public function index()
    {
        $search = $this->request->getGet('q') ?? '';
        $status = $this->request->getGet('status') ?? '';

        $query = $this->model;
        if (!empty($search)) {
            $query = $query->groupStart()->like('nama', $search)->orLike('nim', $search)->orLike('email', $search)->groupEnd();
        }
        if (!empty($status)) {
            $query = $query->where('status_akun', $status);
        }

        $items = $query->orderBy('nama','ASC')->findAll();

        return view('admin/mahasiswa/index', [
            'title'  => 'Manajemen Mahasiswa',
            'items'  => $items,
            'search' => $search,
            'status' => $status,
            'active' => 'mahasiswa',
        ]);
    }

    public function toggle(string $nim)
    {
        $mhs = $this->model->find($nim);
        if (!$mhs) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $newStatus = $mhs['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
        $this->model->update($nim, ['status_akun' => $newStatus]);
        $label = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Akun mahasiswa berhasil {$label}.");
    }
}
