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
        // Ekstrak bagian hexadecimal dari kode terakhir
        $last_code = $last['kode_kayu'];
        $hex_part = substr($last_code, -4); // Ambil 4 karakter terakhir
        $no = hexdec($hex_part) + 1; // Konversi ke decimal dan tambah 1
    }
    
    // Konversi ke hexadecimal dan pad dengan leading zeros
    $hex_no = str_pad(dechex($no), 4, '0', STR_PAD_LEFT);
    
    return $jenis['kode_jenis'] . '-' . $hex_no;
}
    
    public function generateBarcode($kode_kayu)
    {
        return  $kode_kayu;
    }
    
    public function hasTransactions($id_kayu)
    {
        return $this->db->table('transaksi_detail')
            ->where('id_kayu', $id_kayu)
            ->countAllResults() > 0;
    }
    public function getKayuWithStock()
{
    return $this->select('kayu.*, jenis_kayu.nama_jenis')
        ->join('jenis_kayu', 'jenis_kayu.id_jenis = kayu.id_jenis')
        ->join('stock', 'stock.id_kayu = kayu.id_kayu')
        ->where('stock.quantity >', 0)
        ->groupBy('kayu.id_kayu')
        ->findAll();
}


public function getStockByGudang($id_kayu, $id_gudang)
{
    $stock = $this->db->table('stock')
        ->where('id_kayu', $id_kayu)
        ->where('id_gudang', $id_gudang)
        ->get()
        ->getRowArray();
    
    return $stock ? $stock['quantity'] : 0;
}
}