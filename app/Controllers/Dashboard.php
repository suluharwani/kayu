<?php namespace App\Controllers;

use App\Models\TransaksiModel;
use App\Models\GudangModel;
use App\Models\StockModel;

class Dashboard extends BaseController
{
    protected $transaksiModel;
    protected $gudangModel;
    protected $stockModel;
    
    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        $this->gudangModel = new GudangModel();
        $this->stockModel = new StockModel();
    }
    
    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'total_gudang' => $this->gudangModel->countAll(),
            'transaksi_terakhir' => $this->transaksiModel->select('transaksi.*, g1.nama_gudang as gudang_asal, g2.nama_gudang as gudang_tujuan')
                ->join('gudang g1', 'g1.id_gudang = transaksi.id_gudang_asal', 'left')
                ->join('gudang g2', 'g2.id_gudang = transaksi.id_gudang_tujuan', 'left')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->findAll(),
            'stock_rendah' => $this->stockModel->select('stock.*, kayu.kode_kayu, jenis_kayu.nama_jenis, gudang.nama_gudang')
                ->join('kayu', 'kayu.id_kayu = stock.id_kayu')
                ->join('jenis_kayu', 'jenis_kayu.id_jenis = kayu.id_jenis')
                ->join('gudang', 'gudang.id_gudang = stock.id_gudang')
                ->where('stock.quantity <', 10)
                ->orderBy('stock.quantity', 'ASC')
                ->findAll()
        ];
        
        return view('dashboard/index', $data);
    }
}