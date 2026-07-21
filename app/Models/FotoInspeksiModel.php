<?php
namespace App\Models;
use CodeIgniter\Model;

class FotoInspeksiModel extends Model
{
    protected $table      = 'foto_inspeksi';
    protected $primaryKey = 'id_foto';
    protected $allowedFields = ['id_inspeksi','nama_file','path_file','urutan'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function getByInspeksi(int $idInspeksi): array
    {
        return $this->where('id_inspeksi', $idInspeksi)->orderBy('urutan','ASC')->findAll();
    }

    public function deleteByInspeksi(int $idInspeksi): void
    {
        $this->where('id_inspeksi', $idInspeksi)->delete();
    }
}
