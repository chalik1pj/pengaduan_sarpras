<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InspeksiFasilitasModel;
use App\Models\FotoInspeksiModel;

class InspeksiController extends BaseController
{
    protected InspeksiFasilitasModel $inspeksiModel;
    protected FotoInspeksiModel      $fotoModel;

    public function __construct()
    {
        $this->inspeksiModel = new InspeksiFasilitasModel();
        $this->fotoModel     = new FotoInspeksiModel();
    }

    /**
     * Daftar semua inspeksi fasilitas
     */
    public function index()
    {
        $filterVerif    = $this->request->getGet('verif') ?? '';
        $filterKondisi  = $this->request->getGet('kondisi') ?? '';

        $query = $this->inspeksiModel->getWithDetail();

        if ($filterVerif === '0') {
            $query->where('inspeksi_fasilitas.diverifikasi', 0);
        } elseif ($filterVerif === '1') {
            $query->where('inspeksi_fasilitas.diverifikasi', 1);
        }
        if (!empty($filterKondisi)) {
            $query->where('inspeksi_fasilitas.kondisi_temuan', $filterKondisi);
        }

        $inspeksi = $query->orderBy('inspeksi_fasilitas.created_at', 'DESC')->findAll();

        foreach ($inspeksi as &$item) {
            $item['fotos'] = $this->fotoModel->getByInspeksi($item['id_inspeksi']);
        }
        unset($item);

        $belumVerif = $this->inspeksiModel->countBelumVerif();

        return view('admin/inspeksi/index', [
            'title'        => 'Inspeksi Fasilitas',
            'active'       => 'inspeksi',
            'inspeksi'     => $inspeksi,
            'belumVerif'   => $belumVerif,
            'filterVerif'  => $filterVerif,
            'filterKondisi'=> $filterKondisi,
        ]);
    }

    /**
     * Detail inspeksi fasilitas
     */
    public function show(int $id)
    {
        $inspeksi = $this->inspeksiModel->getOneWithDetail($id);

        if (!$inspeksi) {
            return redirect()->to(base_url('admin/inspeksi'))->with('error', 'Inspeksi tidak ditemukan.');
        }

        $fotos = $this->fotoModel->getByInspeksi($id);

        return view('admin/inspeksi/show', [
            'title'    => 'Detail Inspeksi',
            'active'   => 'inspeksi',
            'inspeksi' => $inspeksi,
            'fotos'    => $fotos,
        ]);
    }

    /**
     * Verifikasi inspeksi fasilitas
     */
    public function verifikasi(int $id)
    {
        $adminId      = session()->get('admin_id');
        $catatanVerif = $this->request->getPost('catatan_verif');
        $statusVerif  = (int)$this->request->getPost('diverifikasi');

        $inspeksi = $this->inspeksiModel->find($id);
        if (!$inspeksi) {
            return redirect()->back()->with('error', 'Inspeksi tidak ditemukan.');
        }

        $this->inspeksiModel->update($id, [
            'diverifikasi'  => $statusVerif,
            'id_verifikator'=> $adminId,
            'catatan_verif' => $catatanVerif,
            'waktu_verif'   => date('Y-m-d H:i:s'),
        ]);

        $msg = $statusVerif ? 'Inspeksi berhasil diverifikasi.' : 'Inspeksi ditandai belum terverifikasi.';
        return redirect()->back()->with('success', $msg);
    }
}
