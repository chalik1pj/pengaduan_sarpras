<?php
namespace App\Models;
use CodeIgniter\Model;

class FotoLaporanModel extends Model
{
    protected $table      = 'foto_laporan_petugas';
    protected $primaryKey = 'id_foto';
    protected $allowedFields = ['id_laporan','nama_file','path_file','urutan'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function getByLaporan(int $idLaporan): array
    {
        return $this->where('id_laporan', $idLaporan)->orderBy('urutan','ASC')->findAll();
    }

    public function deleteByLaporan(int $idLaporan): void
    {
        $this->where('id_laporan', $idLaporan)->delete();
    }
}
