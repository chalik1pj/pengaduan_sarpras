<?php
namespace App\Models;
use CodeIgniter\Model;

class MahasiswaModel extends Model
{
    protected $table      = 'mahasiswa';
    protected $primaryKey = 'nim';
    protected $allowedFields = ['nim','nama','email','password','program_studi','angkatan','no_hp','foto_profil','status_akun'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $returnType = 'array';

    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function findByNim($nim)
    {
        return $this->find($nim);
    }
}
