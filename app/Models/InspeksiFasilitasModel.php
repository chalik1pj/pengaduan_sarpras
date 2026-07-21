<?php
namespace App\Models;
use CodeIgniter\Model;

class InspeksiFasilitasModel extends Model
{
    protected $table      = 'inspeksi_fasilitas';
    protected $primaryKey = 'id_inspeksi';
    protected $allowedFields = [
        'id_petugas','id_ruangan','id_barang','judul_inspeksi','kondisi_temuan',
        'deskripsi','rekomendasi','diverifikasi','id_verifikator','catatan_verif','waktu_verif'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $returnType = 'array';

    public function getWithDetail()
    {
        return $this->select('inspeksi_fasilitas.*, 
            petugas.nama as nama_petugas, petugas.jabatan,
            ruangan.nama_ruangan, ruangan.lantai, ruangan.tipe_ruangan,
            gedung.nama_gedung, gedung.id_gedung,
            barang.nama_barang,
            v.nama as nama_verifikator')
            ->join('petugas', 'petugas.id_petugas = inspeksi_fasilitas.id_petugas')
            ->join('ruangan', 'ruangan.id_ruangan = inspeksi_fasilitas.id_ruangan')
            ->join('gedung', 'gedung.id_gedung = ruangan.id_gedung')
            ->join('barang', 'barang.id_barang = inspeksi_fasilitas.id_barang', 'left')
            ->join('petugas v', 'v.id_petugas = inspeksi_fasilitas.id_verifikator', 'left');
    }

    public function getByPetugas(int $idPetugas): array
    {
        return $this->getWithDetail()
            ->where('inspeksi_fasilitas.id_petugas', $idPetugas)
            ->orderBy('inspeksi_fasilitas.created_at', 'DESC')
            ->findAll();
    }

    public function getAll(): array
    {
        return $this->getWithDetail()
            ->orderBy('inspeksi_fasilitas.created_at', 'DESC')
            ->findAll();
    }

    public function getOneWithDetail(int $id): ?array
    {
        return $this->getWithDetail()
            ->where('inspeksi_fasilitas.id_inspeksi', $id)
            ->first();
    }

    public function countByPetugas(int $idPetugas): int
    {
        return $this->where('id_petugas', $idPetugas)->countAllResults();
    }

    public function countBelumVerif(): int
    {
        return $this->where('diverifikasi', 0)->countAllResults();
    }
}
