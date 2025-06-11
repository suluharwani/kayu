<?php namespace App\Models;

use CodeIgniter\Model;

class TransaksiDetailModel extends Model
{
    protected $table = 'transaksi_detail';
    protected $primaryKey = 'id_detail';
    protected $allowedFields = ['id_transaksi', 'id_kayu', 'quantity'];
    
    public function getDetailByTransaksi($id_transaksi)
    {
        return $this->select('transaksi_detail.*, kayu.kode_kayu, kayu.panjang, kayu.lebar, kayu.tebal, kayu.volume, jenis_kayu.nama_jenis')
            ->join('kayu', 'kayu.id_kayu = transaksi_detail.id_kayu')
            ->join('jenis_kayu', 'jenis_kayu.id_jenis = kayu.id_jenis')
            ->where('id_transaksi', $id_transaksi)
            ->findAll();
    }
}