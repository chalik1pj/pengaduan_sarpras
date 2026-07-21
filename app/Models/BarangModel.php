<?php
namespace App\Models;
use CodeIgniter\Model;

class BarangModel extends Model
{
    protected $table      = 'barang';
    protected $primaryKey = 'id_barang';
    protected $allowedFields = ['id_ruangan','nama_barang','merek_ukuran','jumlah','kondisi'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function getByRuangan($id_ruangan)
    {
        return $this->where('id_ruangan', $id_ruangan)->orderBy('nama_barang','ASC')->findAll();
    }

    public function withRuanganGedung()
    {
        return $this->select('barang.*, ruangan.nama_ruangan, ruangan.lantai, gedung.nama_gedung, gedung.id_gedung')
            ->join('ruangan','ruangan.id_ruangan = barang.id_ruangan')
            ->join('gedung','gedung.id_gedung = ruangan.id_gedung');
    }
}
