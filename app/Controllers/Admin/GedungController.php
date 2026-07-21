<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GedungModel;

class GedungController extends BaseController
{
    protected GedungModel $model;

    public function __construct()
    {
        $this->model = new GedungModel();
    }

    public function index()
    {
        return view('admin/gedung/index', [
            'title'  => 'Manajemen Gedung',
            'items'  => $this->model->orderBy('nama_gedung','ASC')->findAll(),
            'active' => 'gedung',
        ]);
    }

    public function store()
    {
        $rules = [
            'nama_gedung' => 'required|min_length[2]|max_length[50]|is_unique[gedung.nama_gedung]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->insert([
            'nama_gedung' => $this->request->getPost('nama_gedung'),
            'keterangan'  => $this->request->getPost('keterangan'),
        ]);
        return redirect()->back()->with('success', 'Gedung berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $existing = $this->model->find($id);
        if (!$existing) { return redirect()->back()->with('error', 'Data tidak ditemukan.'); }

        $rules = [
            'nama_gedung' => "required|min_length[2]|max_length[50]|is_unique[gedung.nama_gedung,id_gedung,{$id}]",
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->update($id, [
            'nama_gedung' => $this->request->getPost('nama_gedung'),
            'keterangan'  => $this->request->getPost('keterangan'),
        ]);
        return redirect()->back()->with('success', 'Gedung berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->back()->with('success', 'Gedung berhasil dihapus.');
    }
}
