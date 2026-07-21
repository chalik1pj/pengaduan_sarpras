<?php
namespace App\Models;
use CodeIgniter\Model;

class KategoriPengaduanModel extends Model
{
    protected $table      = 'kategori_pengaduan';
    protected $primaryKey = 'id_kategori';
    protected $allowedFields = ['nama_kategori','deskripsi'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
}
