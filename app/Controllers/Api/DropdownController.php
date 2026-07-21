<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\GedungModel;
use App\Models\RuanganModel;
use App\Models\BarangModel;
use CodeIgniter\HTTP\ResponseInterface;

class DropdownController extends BaseController
{
    public function gedung()
    {
        $model = new GedungModel();
        $data  = $model->orderBy('nama_gedung','ASC')->findAll();
        return $this->response->setJSON($data);
    }

    public function ruangan(int $id_gedung)
    {
        $model = new RuanganModel();
        $data  = $model->getByGedung($id_gedung);
        return $this->response->setJSON($data);
    }

    public function barang(int $id_ruangan)
    {
        $model = new BarangModel();
        $data  = $model->getByRuangan($id_ruangan);
        return $this->response->setJSON($data);
    }
}
