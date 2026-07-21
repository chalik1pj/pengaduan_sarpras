<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RuanganModel;
use App\Models\GedungModel;

class RuanganController extends BaseController
{
    protected RuanganModel $model;
    protected GedungModel  $gedungModel;

    public function __construct()
    {
        $this->model       = new RuanganModel();
        $this->gedungModel = new GedungModel();
    }

    public function index()
    {
        $filterGedung = $this->request->getGet('gedung') ?? '';
        $search       = $this->request->getGet('q') ?? '';

        $query = $this->model->withGedung();

        if (!empty($filterGedung) && is_numeric($filterGedung)) {
            $query->where('ruangan.id_gedung', (int)$filterGedung);
        }
        if (!empty($search)) {
            $query->like('ruangan.nama_ruangan', $search);
        }

        $items = $query->orderBy('gedung.nama_gedung','ASC')->orderBy('ruangan.lantai','ASC')->orderBy('ruangan.nama_ruangan','ASC')->findAll();

        return view('admin/ruangan/index', [
            'title'       => 'Manajemen Ruangan',
            'items'       => $items,
            'gedung'      => $this->gedungModel->orderBy('nama_gedung','ASC')->findAll(),
            'filterGedung'=> $filterGedung,
            'search'      => $search,
            'active'      => 'ruangan',
        ]);
    }

    public function store()
    {
        $rules = [
            'id_gedung'    => 'required|integer',
            'nama_ruangan' => 'required|min_length[2]|max_length[100]',
            'tipe_ruangan' => 'required|max_length[100]',
            'lantai'       => 'required|integer',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->insert([
            'id_gedung'    => (int)$this->request->getPost('id_gedung'),
            'nama_ruangan' => $this->request->getPost('nama_ruangan'),
            'tipe_ruangan' => $this->request->getPost('tipe_ruangan'),
            'lantai'       => (int)$this->request->getPost('lantai'),
        ]);
        return redirect()->back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $rules = [
            'id_gedung'    => 'required|integer',
            'nama_ruangan' => 'required|min_length[2]|max_length[100]',
            'tipe_ruangan' => 'required|max_length[100]',
            'lantai'       => 'required|integer',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->update($id, [
            'id_gedung'    => (int)$this->request->getPost('id_gedung'),
            'nama_ruangan' => $this->request->getPost('nama_ruangan'),
            'tipe_ruangan' => $this->request->getPost('tipe_ruangan'),
            'lantai'       => (int)$this->request->getPost('lantai'),
        ]);
        return redirect()->back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->back()->with('success', 'Ruangan berhasil dihapus.');
    }
}
