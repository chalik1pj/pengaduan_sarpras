<?php
namespace App\Models;
use CodeIgniter\Model;

class StatusPengaduanModel extends Model
{
    protected $table      = 'status_pengaduan';
    protected $primaryKey = 'id_status';
    protected $allowedFields = ['nama_status','urutan','keterangan'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function getOrdered()
    {
        return $this->orderBy('urutan','ASC')->findAll();
    }
}
