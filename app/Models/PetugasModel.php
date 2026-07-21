<?php
namespace App\Models;
use CodeIgniter\Model;

class PetugasModel extends Model
{
    protected $table      = 'petugas';
    protected $primaryKey = 'id_petugas';
    protected $allowedFields = ['nama','email','password','jabatan','no_wa','level_akses','status_akun'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function getAktif()
    {
        return $this->where('status_akun','aktif')->findAll();
    }

    public function getWithWa()
    {
        return $this->where('status_akun','aktif')
            ->where('no_wa !=', '')
            ->where('no_wa IS NOT NULL', null, false)
            ->findAll();
    }

    /**
     * Ambil semua admin/super_admin aktif yang punya no_wa (untuk notifikasi)
     */
    public function getAdminList(): array
    {
        return $this->where('status_akun','aktif')
            ->whereIn('level_akses', ['admin','super_admin'])
            ->where('no_wa IS NOT NULL', null, false)
            ->where('no_wa !=', '')
            ->findAll();
    }
}
