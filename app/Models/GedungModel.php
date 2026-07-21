<?php
namespace App\Models;
use CodeIgniter\Model;

class GedungModel extends Model
{
    protected $table      = 'gedung';
    protected $primaryKey = 'id_gedung';
    protected $allowedFields = ['nama_gedung', 'keterangan'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
}
