<?php namespace App\Models;

use CodeIgniter\Model;

class GudangModel extends Model
{
    protected $table = 'gudang';
    protected $primaryKey = 'id_gudang';
    protected $allowedFields = ['kode_gudang', 'nama_gudang', 'alamat', 'kapasitas', 'penanggung_jawab'];
    protected $useTimestamps = true;
    
    public function generateCode()
    {
        $last = $this->orderBy('id_gudang', 'DESC')->first();
        $no = 1;
        if($last) {
            $no = (int) substr($last['kode_gudang'], 1) + 1;
        }
        return 'G' . str_pad($no, 4, '0', STR_PAD_LEFT);
    }
    
    public function getStockSummary($id_gudang)
    {
        return $this->db->table('stock')
            ->select('SUM(quantity) as total_item, SUM(quantity * kayu.volume) as total_volume')
            ->join('kayu', 'kayu.id_kayu = stock.id_kayu')
            ->where('id_gudang', $id_gudang)
            ->get()
            ->getRowArray();
    }
}