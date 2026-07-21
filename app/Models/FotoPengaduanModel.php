<?php
namespace App\Models;
use CodeIgniter\Model;

class FotoPengaduanModel extends Model
{
    protected $table      = 'foto_pengaduan';
    protected $primaryKey = 'id_foto';
    protected $allowedFields = ['id_pengaduan','nama_file','path_file','urutan'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function getByPengaduan($id_pengaduan)
    {
        return $this->where('id_pengaduan', $id_pengaduan)->orderBy('urutan','ASC')->findAll();
    }
}
