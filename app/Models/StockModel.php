<?php namespace App\Models;

use CodeIgniter\Model;

class StockModel extends Model
{
    protected $table = 'stock';
    protected $primaryKey = 'id_stock';
    protected $allowedFields = ['id_kayu', 'id_gudang', 'quantity', 'lokasi_rak'];
    protected $useTimestamps = true;
    
    public function updateStock($id_kayu, $id_gudang, $quantity, $lokasi_rak = null)
    {
        $stock = $this->where(['id_kayu' => $id_kayu, 'id_gudang' => $id_gudang])->first();
        
        if($stock) {
            $newQuantity = $stock['quantity'] + $quantity;
            if($newQuantity < 0) {
                return false; // tidak boleh minus
            }
            
            $data = ['quantity' => $newQuantity];
            if($lokasi_rak) {
                $data['lokasi_rak'] = $lokasi_rak;
            }
            
            return $this->update($stock['id_stock'], $data);
        } else {
            if($quantity < 0) {
                return false; // tidak boleh minus
            }
            return $this->insert([
                'id_kayu' => $id_kayu,
                'id_gudang' => $id_gudang,
                'quantity' => $quantity,
                'lokasi_rak' => $lokasi_rak
            ]);
        }
    }
    
    public function getStockByGudang($id_gudang)
    {
        return $this->select('stock.*, kayu.*, jenis_kayu.nama_jenis, jenis_kayu.kode_jenis')
            ->join('kayu', 'kayu.id_kayu = stock.id_kayu')
            ->join('jenis_kayu', 'jenis_kayu.id_jenis = kayu.id_jenis')
            ->where('stock.id_gudang', $id_gudang)
            ->orderBy('jenis_kayu.nama_jenis', 'ASC')
            ->orderBy('kayu.grade', 'ASC')
            ->findAll();
    }
    
    public function getStockGlobal()
    {
        return $this->select('stock.*, kayu.*, jenis_kayu.nama_jenis, gudang.nama_gudang')
            ->join('kayu', 'kayu.id_kayu = stock.id_kayu')
            ->join('jenis_kayu', 'jenis_kayu.id_jenis = kayu.id_jenis')
            ->join('gudang', 'gudang.id_gudang = stock.id_gudang')
            ->orderBy('gudang.nama_gudang', 'ASC')
            ->orderBy('jenis_kayu.nama_jenis', 'ASC')
            ->findAll();
    }
    public function getLaporanStock($id_gudang, $kategori)
{
    $builder = $this->select('stock.*, kayu.*, jenis_kayu.nama_jenis, jenis_kayu.kategori, gudang.nama_gudang')
        ->join('kayu', 'kayu.id_kayu = stock.id_kayu')
        ->join('jenis_kayu', 'jenis_kayu.id_jenis = kayu.id_jenis')
        ->join('gudang', 'gudang.id_gudang = stock.id_gudang')
        ->where('stock.quantity >', 0);

    if ($id_gudang != 'all') {
        $builder->where('stock.id_gudang', $id_gudang);
    }

    if ($kategori != 'all') {
        $builder->where('jenis_kayu.kategori', $kategori);
    }

    return $builder->orderBy('gudang.nama_gudang')->orderBy('jenis_kayu.nama_jenis')->findAll();
}

public function getGudangList()
{
    return $this->db->table('gudang')->orderBy('nama_gudang')->get()->getResultArray();
}
}