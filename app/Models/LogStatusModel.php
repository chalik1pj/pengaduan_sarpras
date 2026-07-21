<?php
namespace App\Models;
use CodeIgniter\Model;

class LogStatusModel extends Model
{
    protected $table      = 'log_status_pengaduan';
    protected $primaryKey = 'id_log';
    protected $allowedFields = ['id_pengaduan','id_status_lama','id_status_baru','id_petugas','catatan'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function getByPengaduan($id_pengaduan)
    {
        return $this->select('log_status_pengaduan.*, s_baru.nama_status as status_baru, s_lama.nama_status as status_lama, petugas.nama as nama_petugas')
            ->join('status_pengaduan s_baru','s_baru.id_status = log_status_pengaduan.id_status_baru')
            ->join('status_pengaduan s_lama','s_lama.id_status = log_status_pengaduan.id_status_lama','left')
            ->join('petugas','petugas.id_petugas = log_status_pengaduan.id_petugas','left')
            ->where('log_status_pengaduan.id_pengaduan', $id_pengaduan)
            ->orderBy('log_status_pengaduan.waktu_perubahan','ASC')
            ->findAll();
    }
}
