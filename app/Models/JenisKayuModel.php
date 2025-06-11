<?php namespace App\Models;

use CodeIgniter\Model;

class JenisKayuModel extends Model
{
    protected $table = 'jenis_kayu';
    protected $primaryKey = 'id_jenis';
    protected $allowedFields = ['id_jenis','kode_jenis', 'nama_jenis', 'deskripsi', 'kategori', 'harga_per_volume'];
    protected $useTimestamps = true;
    
    public function generateCode()
    {
        $last = $this->orderBy('id_jenis', 'DESC')->first();
        $no = 1;
        if($last) {
            $no = (int) substr($last['kode_jenis'], 1) + 1;
        }
        return 'J' . str_pad($no, 4, '0', STR_PAD_LEFT);
    }
    
    public function getKayuByJenis($id_jenis)
    {
        return $this->db->table('kayu')
            ->where('id_jenis', $id_jenis)
            ->get()
            ->getResultArray();
    }
}