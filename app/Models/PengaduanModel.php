<?php
namespace App\Models;
use CodeIgniter\Model;

class PengaduanModel extends Model
{
    protected $table      = 'pengaduan';
    protected $primaryKey = 'id_pengaduan';
    protected $allowedFields = [
        'kode_pengaduan','nim','id_ruangan','id_barang','id_kategori',
        'judul','deskripsi','foto_bukti','prioritas',
        'id_status_saat_ini','id_petugas_penangani','tanggal_selesai'
    ];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function generateKode()
    {
        $date   = date('Ymd');
        $prefix = 'PGD-' . $date . '-';
        $last   = $this->like('kode_pengaduan', $prefix, 'after')
            ->orderBy('id_pengaduan','DESC')->first();
        $num = $last ? (int) substr($last['kode_pengaduan'], -4) + 1 : 1;
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function getWithDetail()
    {
        return $this->select('pengaduan.*, mahasiswa.nama as nama_mahasiswa, mahasiswa.nim, ruangan.nama_ruangan, ruangan.lantai, gedung.nama_gedung, barang.nama_barang, kategori_pengaduan.nama_kategori, status_pengaduan.nama_status, petugas.nama as nama_petugas')
            ->join('mahasiswa','mahasiswa.nim = pengaduan.nim')
            ->join('ruangan','ruangan.id_ruangan = pengaduan.id_ruangan')
            ->join('gedung','gedung.id_gedung = ruangan.id_gedung')
            ->join('barang','barang.id_barang = pengaduan.id_barang','left')
            ->join('kategori_pengaduan','kategori_pengaduan.id_kategori = pengaduan.id_kategori')
            ->join('status_pengaduan','status_pengaduan.id_status = pengaduan.id_status_saat_ini')
            ->join('petugas','petugas.id_petugas = pengaduan.id_petugas_penangani','left');
    }

    public function getByNim($nim)
    {
        return $this->getWithDetail()
            ->where('pengaduan.nim', $nim)
            ->orderBy('pengaduan.tanggal_pengaduan','DESC')
            ->findAll();
    }

    public function getDashboardStats()
    {
        $db   = \Config\Database::connect();
        $q    = $db->query('SELECT id_status_saat_ini, COUNT(*) as total FROM pengaduan GROUP BY id_status_saat_ini');
        $rows = $q->getResultArray();
        $stats = [];
        foreach ($rows as $row) {
            $stats[$row['id_status_saat_ini']] = $row['total'];
        }
        return [
            'total'        => array_sum($stats),
            'menunggu'     => $stats[1] ?? 0,
            'diverifikasi' => $stats[2] ?? 0,
            'diproses'     => $stats[3] ?? 0,
            'selesai'      => $stats[4] ?? 0,
            'ditolak'      => $stats[5] ?? 0,
        ];
    }

    public function getStatsByNim($nim)
    {
        $db   = \Config\Database::connect();
        $q    = $db->query('SELECT id_status_saat_ini, COUNT(*) as total FROM pengaduan WHERE nim = ? GROUP BY id_status_saat_ini', [$nim]);
        $rows = $q->getResultArray();
        $stats = [];
        foreach ($rows as $row) {
            $stats[$row['id_status_saat_ini']] = $row['total'];
        }
        return $stats;
    }

    public function getByGedung()
    {
        $db = \Config\Database::connect();
        $q  = $db->query('SELECT g.nama_gedung, COUNT(p.id_pengaduan) as total FROM gedung g LEFT JOIN ruangan r ON r.id_gedung=g.id_gedung LEFT JOIN pengaduan p ON p.id_ruangan=r.id_ruangan GROUP BY g.id_gedung ORDER BY g.id_gedung');
        return $q->getResultArray();
    }

    public function getRecent($limit = 10)
    {
        return $this->getWithDetail()->orderBy('pengaduan.tanggal_pengaduan','DESC')->findAll($limit);
    }

    public function getMonthlyStats()
    {
        $db = \Config\Database::connect();
        $q  = $db->query("SELECT DATE_FORMAT(tanggal_pengaduan,'%Y-%m') as bulan, COUNT(*) as total FROM pengaduan WHERE tanggal_pengaduan >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY bulan ORDER BY bulan ASC");
        return $q->getResultArray();
    }
}
