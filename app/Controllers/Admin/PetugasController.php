<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PetugasModel;

class PetugasController extends BaseController
{
    protected PetugasModel $model;

    public function __construct()
    {
        $this->model = new PetugasModel();
    }

    public function index()
    {
        // Hanya super_admin yang bisa akses
        if (session()->get('admin_level') !== 'super_admin') {
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'Akses ditolak.');
        }

        return view('admin/petugas/index', [
            'title'  => 'Manajemen Petugas',
            'items'  => $this->model->orderBy('nama','ASC')->findAll(),
            'active' => 'petugas',
        ]);
    }

    public function store()
    {
        if (session()->get('admin_level') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $rules = [
            'nama'        => 'required|min_length[3]|max_length[100]',
            'email'       => 'required|valid_email|is_unique[petugas.email]',
            'password'    => 'required|min_length[8]',
            'jabatan'     => 'permit_empty|max_length[100]',
            'no_wa'       => 'permit_empty|max_length[20]',
            'level_akses' => 'required|in_list[super_admin,admin,petugas]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'nama'        => $this->request->getPost('nama'),
            'email'       => $this->request->getPost('email'),
            'password'    => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'jabatan'     => $this->request->getPost('jabatan'),
            'no_wa'       => $this->request->getPost('no_wa'),
            'level_akses' => $this->request->getPost('level_akses'),
            'status_akun' => 'aktif',
        ]);

        return redirect()->back()->with('success', 'Petugas berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if (session()->get('admin_level') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $rules = [
            'nama'        => 'required|min_length[3]|max_length[100]',
            'email'       => "required|valid_email|is_unique[petugas.email,id_petugas,{$id}]",
            'jabatan'     => 'permit_empty|max_length[100]',
            'no_wa'       => 'permit_empty|max_length[20]',
            'level_akses' => 'required|in_list[super_admin,admin,petugas]',
            'status_akun' => 'required|in_list[aktif,nonaktif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama'        => $this->request->getPost('nama'),
            'email'       => $this->request->getPost('email'),
            'jabatan'     => $this->request->getPost('jabatan'),
            'no_wa'       => $this->request->getPost('no_wa'),
            'level_akses' => $this->request->getPost('level_akses'),
            'status_akun' => $this->request->getPost('status_akun'),
        ];

        $newPwd = $this->request->getPost('password');
        if (!empty($newPwd)) {
            if (strlen($newPwd) < 8) {
                return redirect()->back()->with('error', 'Password minimal 8 karakter.');
            }
            $data['password'] = password_hash($newPwd, PASSWORD_BCRYPT);
        }

        $this->model->update($id, $data);
        return redirect()->back()->with('success', 'Data petugas berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (session()->get('admin_level') !== 'super_admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        if ($id === (int)session()->get('admin_id')) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $this->model->delete($id);
        return redirect()->back()->with('success', 'Petugas berhasil dihapus.');
    }
}
