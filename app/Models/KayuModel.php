<?php namespace App\Models;

use CodeIgniter\Model;

class KayuModel extends Model
{
    protected $table = 'kayu';
    protected $primaryKey = 'id_kayu';
    protected $allowedFields = ['kode_kayu', 'id_jenis', 'panjang', 'lebar', 'tebal', 'volume', 'grade', 'kualitas', 'barcode'];
    protected $useTimestamps = true;
    
    public function getKayu($id){
          return $this->select('kayu.*, jenis_kayu.nama_jenis, jenis_kayu.kode_jenis')
            ->join('jenis_kayu', 'jenis_kayu.id_jenis = kayu.id_jenis')
            ->where('kayu.id_kayu', $id)
            ->findAll()[0];
    }
    public function getKayuWithJenis()
    {
        return $this->select('kayu.*, jenis_kayu.nama_jenis, jenis_kayu.kode_jenis')
            ->join('jenis_kayu', 'jenis_kayu.id_jenis = kayu.id_jenis')
            ->findAll();
    }
    
    public function generateCode($id_jenis)
    {
        $jenisKayuModel = new \App\Models\JenisKayuModel();
        $jenis = $jenisKayuModel->find($id_jenis);
        $last = $this->where('id_jenis', $id_jenis)->orderBy('id_kayu', 'DESC')->first();
        
        $no = 1;
        if ($last) {
            $no = (int) substr($last['kode_kayu'], -4) + 1;
        }
        
        return $jenis['kode_jenis'] . '-' . str_pad($no, 4, '0', STR_PAD_LEFT);
    }
    
    public function generateBarcode($kode_kayu)
    {
        return 'KAYU-' . $kode_kayu . '-' . bin2hex(random_bytes(4));
    }
    
    public function hasTransactions($id_kayu)
    {
        return $this->db->table('transaksi_detail')
            ->where('id_kayu', $id_kayu)
            ->countAllResults() > 0;
    }
}