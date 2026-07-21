<?php
namespace App\Models;
use CodeIgniter\Model;

class RuanganModel extends Model
{
    protected $table      = 'ruangan';
    protected $primaryKey = 'id_ruangan';
    protected $allowedFields = ['id_gedung','nama_ruangan','tipe_ruangan','lantai'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $returnType = 'array';

    public function getByGedung($id_gedung)
    {
        return $this->where('id_gedung', $id_gedung)
            ->orderBy('lantai','ASC')->orderBy('nama_ruangan','ASC')->findAll();
    }

    public function withGedung()
    {
        return $this->select('ruangan.*, gedung.nama_gedung')
            ->join('gedung','gedung.id_gedung = ruangan.id_gedung');
    }
}
