<?php
namespace App\Models;
use CodeIgniter\Model;

class LaporanPetugasModel extends Model
{
    protected $table      = 'laporan_petugas';
    protected $primaryKey = 'id_laporan';
    protected $allowedFields = [
        'id_pengaduan','id_petugas','judul_laporan','deskripsi',
        'status_laporan','diverifikasi','id_verifikator','catatan_verif','waktu_verif'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $returnType = 'array';

    public function getWithDetail()
    {
        return $this->select('laporan_petugas.*, petugas.nama as nama_petugas, petugas.jabatan,
            pengaduan.kode_pengaduan, pengaduan.judul as judul_pengaduan,
            v.nama as nama_verifikator')
            ->join('petugas', 'petugas.id_petugas = laporan_petugas.id_petugas')
            ->join('pengaduan', 'pengaduan.id_pengaduan = laporan_petugas.id_pengaduan')
            ->join('petugas v', 'v.id_petugas = laporan_petugas.id_verifikator', 'left');
    }

    public function getByPengaduan(int $idPengaduan): array
    {
        return $this->getWithDetail()
            ->where('laporan_petugas.id_pengaduan', $idPengaduan)
            ->orderBy('laporan_petugas.created_at', 'DESC')
            ->findAll();
    }

    public function getByPetugas(int $idPetugas): array
    {
        return $this->getWithDetail()
            ->where('laporan_petugas.id_petugas', $idPetugas)
            ->orderBy('laporan_petugas.created_at', 'DESC')
            ->findAll();
    }

    public function countByPetugas(int $idPetugas): int
    {
        return $this->where('id_petugas', $idPetugas)->countAllResults();
    }
}
