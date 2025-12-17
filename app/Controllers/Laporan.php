<?php namespace App\Controllers;

use App\Models\TransaksiModel;
use App\Models\StockModel;
use App\Models\KayuModel;

class Laporan extends BaseController
{
    protected $transaksiModel;
    protected $stockModel;
    protected $kayuModel;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        $this->stockModel = new StockModel();
        $this->kayuModel = new KayuModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Menu Laporan',
            'validation' => \Config\Services::validation()
        ];
        return view('laporan/index', $data);
    }

    public function transaksi()
    {
        $start_date = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end_date = $this->request->getGet('end_date') ?? date('Y-m-t');
        $jenis = $this->request->getGet('jenis') ?? 'all';

        $data = [
            'title' => 'Laporan Transaksi',
            'transaksi' => $this->transaksiModel->getLaporanTransaksi($start_date, $end_date, $jenis),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'jenis' => $jenis
        ];

        return view('laporan/transaksi', $data);
    }

    public function stock()
    {
        $id_gudang = $this->request->getGet('id_gudang') ?? 'all';
        $kategori = $this->request->getGet('kategori') ?? 'all';

        $data = [
            'title' => 'Laporan Stock',
            'stock' => $this->stockModel->getLaporanStock($id_gudang, $kategori),
            'gudang' => $this->stockModel->getGudangList(),
            'selected_gudang' => $id_gudang,
            'kategori' => $kategori
        ];

        return view('laporan/stock', $data);
    }

    public function mutasi()
    {
        $start_date = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end_date = $this->request->getGet('end_date') ?? date('Y-m-t');

        $data = [
            'title' => 'Laporan Mutasi',
            'mutasi' => $this->transaksiModel->getLaporanMutasi($start_date, $end_date),
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

        return view('laporan/mutasi', $data);
    }

    public function printLaporan($type)
    {
        header('Content-Type: application/pdf');
        header('Cache-Control: private, max-age=0, must-revalidate');
        $method = $type;
        if (!method_exists($this, $method)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $html = $this->$method();
        
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("laporan-{$type}.pdf", ['Attachment' => false]);
    }
    private function transaksiPdf()
{
    $start_date = $this->request->getGet('start_date') ?? date('Y-m-01');
    $end_date = $this->request->getGet('end_date') ?? date('Y-m-t');
    $jenis = $this->request->getGet('jenis') ?? 'all';

    $data = [
        'transaksi' => $this->transaksiModel->getLaporanTransaksi($start_date, $end_date, $jenis),
        'start_date' => $start_date,
        'end_date' => $end_date,
        'jenis' => $jenis
    ];

    return view('laporan/pdf/transaksi_pdf', $data);
}

private function stockPdf()
{
    $id_gudang = $this->request->getGet('id_gudang') ?? 'all';
    $kategori = $this->request->getGet('kategori') ?? 'all';

    $data = [
        'stock' => $this->stockModel->getLaporanStock($id_gudang, $kategori),
        'gudang' => $this->stockModel->getGudangList(),
        'selected_gudang' => $id_gudang,
        'kategori' => $kategori
    ];

    return view('laporan/pdf/stock_pdf', $data);
}

private function mutasiPdf()
{
    $start_date = $this->request->getGet('start_date') ?? date('Y-m-01');
    $end_date = $this->request->getGet('end_date') ?? date('Y-m-t');

    $data = [
        'mutasi' => $this->transaksiModel->getLaporanMutasi($start_date, $end_date),
        'start_date' => $start_date,
        'end_date' => $end_date
    ];

    return view('laporan/pdf/mutasi_pdf', $data);
}
}