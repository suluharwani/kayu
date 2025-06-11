<?php namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    protected $allowedFields = ['kode_transaksi', 'jenis_transaksi', 'id_gudang_asal', 'id_gudang_tujuan', 'tanggal_transaksi', 'keterangan', 'created_by'];
    protected $useTimestamps = true;
    
    public function generateCode($jenis)
    {
        $prefix = '';
        switch($jenis) {
            case 'masuk': $prefix = 'TM'; break;
            case 'keluar': $prefix = 'TK'; break;
            case 'mutasi': $prefix = 'MT'; break;
        }
        
        $last = $this->where('jenis_transaksi', $jenis)
                    ->orderBy('id_transaksi', 'DESC')
                    ->first();
        
        $no = 1;
        if($last) {
            $no = (int) substr($last['kode_transaksi'], -4) + 1;
        }
        
        return $prefix . date('ymd') . str_pad($no, 4, '0', STR_PAD_LEFT);
    }
      public function getTransaksiWithDetails($id_transaksi)
    {
        $transaksi = $this->select('transaksi.*, g1.nama_gudang as gudang_asal, g2.nama_gudang as gudang_tujuan, u.nama_lengkap as operator')
            ->join('gudang g1', 'g1.id_gudang = transaksi.id_gudang_asal', 'left')
            ->join('gudang g2', 'g2.id_gudang = transaksi.id_gudang_tujuan', 'left')
            ->join('users u', 'u.id = transaksi.created_by', 'left')
            ->find($id_transaksi);
            
        if ($transaksi) {
            $transaksi['detail'] = $this->db->table('transaksi_detail td')
                ->select('td.*,k.grade, k.kode_kayu,k.barcode, k.panjang, k.lebar, k.tebal, k.volume, jk.nama_jenis')
                ->join('kayu k', 'k.id_kayu = td.id_kayu')
                ->join('jenis_kayu jk', 'jk.id_jenis = k.id_jenis')
                ->where('td.id_transaksi', $id_transaksi)
                ->get()
                ->getResultArray();
        }
        
        return $transaksi;
    }
    public function getLaporanTransaksi($start_date, $end_date, $jenis)
{
    $builder = $this->select('transaksi.*, g1.nama_gudang as gudang_asal, g2.nama_gudang as gudang_tujuan, u.nama_lengkap as operator')
        ->join('gudang g1', 'g1.id_gudang = transaksi.id_gudang_asal', 'left')
        ->join('gudang g2', 'g2.id_gudang = transaksi.id_gudang_tujuan', 'left')
        ->join('users u', 'u.id = transaksi.created_by', 'left')
        ->where("DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'");

    if ($jenis != 'all') {
        $builder->where('jenis_transaksi', $jenis);
    }

    return $builder->orderBy('tanggal_transaksi', 'DESC')->findAll();
}

public function getLaporanMutasi($start_date, $end_date)
{
    return $this->select('transaksi.*, g1.nama_gudang as gudang_asal, g2.nama_gudang as gudang_tujuan')
        ->join('gudang g1', 'g1.id_gudang = transaksi.id_gudang_asal')
        ->join('gudang g2', 'g2.id_gudang = transaksi.id_gudang_tujuan')
        ->where('jenis_transaksi', 'mutasi')
        ->where("DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'")
        ->orderBy('tanggal_transaksi', 'DESC')
        ->findAll();
}
public function getDetailTransaksi($id_transaksi)
{
    return $this->db->table('transaksi_detail td')
        ->select('td.*, k.kode_kayu, jk.nama_jenis, k.panjang, k.lebar, k.tebal, k.volume')
        ->join('kayu k', 'k.id_kayu = td.id_kayu')
        ->join('jenis_kayu jk', 'jk.id_jenis = k.id_jenis')
        ->where('td.id_transaksi', $id_transaksi)
        ->get()
        ->getResultArray();
}

}