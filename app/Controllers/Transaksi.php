<?php namespace App\Controllers;

use App\Models\TransaksiModel;
use App\Models\TransaksiDetailModel;
use App\Models\KayuModel;
use App\Models\GudangModel;
use App\Models\StockModel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel;


class Transaksi extends BaseController
{
    protected $transaksiModel;
    protected $transaksiDetailModel;
    protected $kayuModel;
    protected $gudangModel;
    protected $stockModel;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        $this->transaksiDetailModel = new TransaksiDetailModel();
        $this->kayuModel = new KayuModel();
        $this->gudangModel = new GudangModel();
        $this->stockModel = new StockModel();
    }

    // Di dalam class Transaksi
public function getDetailByKode($kode_transaksi)
{
    $transaksi = $this->transaksiModel->where('kode_transaksi', $kode_transaksi)->first();
    
    if (!$transaksi) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Transaksi tidak ditemukan'
        ]);
    }
    
    // Pastikan hanya transaksi masuk yang bisa diambil
    if ($transaksi['jenis_transaksi'] !== 'masuk') {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Hanya transaksi masuk (TM) yang dapat diambil'
        ]);
    }
    
    $detail = $this->transaksiDetailModel->getDetailByTransaksi($transaksi['id_transaksi']);
    
    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'transaksi' => $transaksi,
            'detail' => $detail
        ]
    ]);
}
    public function index()
    {
          $transaksi = $this->transaksiModel->select('transaksi.*, g1.nama_gudang as gudang_asal, g2.nama_gudang as gudang_tujuan, u.nama_lengkap as operator')
            ->join('gudang g1', 'g1.id_gudang = transaksi.id_gudang_asal', 'left')
            ->join('gudang g2', 'g2.id_gudang = transaksi.id_gudang_tujuan', 'left')
            ->join('users u', 'u.id = transaksi.created_by', 'left')
            ->orderBy('transaksi.created_at', 'DESC')
            ->findAll();

        // Ambil detail untuk setiap transaksi
        foreach ($transaksi as &$t) {
            $t['detail'] = $this->transaksiModel->getDetailTransaksi($t['id_transaksi']);
        }

        $data = [
            'title' => 'Daftar Transaksi',
            'transaksi' => $transaksi,
            'gudang_list' => $this->gudangModel->findAll() // Kirim data gudang ke view
        ];

        return view('transaksi/index', $data);
    }

    

    public function printLabelTransaksi($id_transaksi)
{
    // Ambil data transaksi
    $transaksi = $this->transaksiModel->getTransaksiWithDetails($id_transaksi);
    
    if (!$transaksi) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Generate QR Code
    $qrCode = Builder::create()
        ->writer(new PngWriter())
        ->data('TRANSAKSI-' . $transaksi['kode_transaksi'])
        ->encoding(new Encoding('UTF-8'))
        ->size(150)
        ->margin(10)
        ->build();

    // Siapkan data untuk view
    $data = [
        'transaksi' => $transaksi,
        'qrCode' => base64_encode($qrCode->getString())
    ];

    // Konfigurasi Dompdf
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'helvetica');

    // Generate PDF
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('transaksi/print_label', $data));
    $dompdf->setPaper('A5', 'landscape');
    $dompdf->render();

    // Ambil output PDF
    $pdfOutput = $dompdf->output();
    
    // Kembalikan sebagai response PDF
    return $this->response
        ->setContentType('application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="label-transaksi-' . $transaksi['kode_transaksi'] . '.pdf"')
        ->setHeader('Content-Length', (string) strlen($pdfOutput))
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->setHeader('Cache-Control', 'post-check=0, pre-check=0', false)
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
        ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
        ->setBody($pdfOutput);
}

    public function printLabelKayu($id_transaksi)
{
    $transaksi = $this->transaksiModel->getTransaksiWithDetails($id_transaksi);
    if (!$transaksi) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Generate QR Code untuk setiap item
    foreach ($transaksi['detail'] as &$item) {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($item['barcode'])
            ->encoding(new Encoding('UTF-8'))
            ->size(50)
            ->margin(2)
            ->build();

        $item['qrcode_base64'] = base64_encode($result->getString());
    }

    $data = [
        'transaksi' => $transaksi
    ];

    // Konfigurasi Dompdf
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('transaksi/print_label_kayu', $data));
    $dompdf->setPaper([0, 0, 210, 297], 'portrait');
    $dompdf->render();

    // Ambil output PDF
    $pdfOutput = $dompdf->output();
    
    // Return dengan Response Object CodeIgniter 4
    return $this->response
        ->setContentType('application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="label-kayu-' . $transaksi['kode_transaksi'] . '.pdf"')
        ->setHeader('Content-Length', (string) strlen($pdfOutput))
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', '0')
        ->setBody($pdfOutput);
}

    public function createMasuk()
    {
        $data = [
            'title' => 'Transaksi Masuk',
            'gudang' => $this->gudangModel->findAll(),
            'kayu' => $this->kayuModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('transaksi/masuk', $data);
    }
    
    public function saveMasuk()
    {
        $rules = [
            'id_gudang' => 'required',
            'tanggal' => 'required',
            'items' => 'required'
        ];
        
        if(!$this->validate($rules)) {
            return redirect()->to('/transaksi/masuk')->withInput();
        }
        
        // Simpan transaksi
        $kode_transaksi = $this->transaksiModel->generateCode('masuk');
        
        $this->transaksiModel->save([
            'kode_transaksi' => $kode_transaksi,
            'jenis_transaksi' => 'masuk',
            'id_gudang_tujuan' => $this->request->getVar('id_gudang'),
            'tanggal_transaksi' => $this->request->getVar('tanggal'),
            'keterangan' => $this->request->getVar('keterangan'),
            'created_by' => session()->get('id_user')
        ]);
        
        $id_transaksi = $this->transaksiModel->insertID();
        
        // Simpan detail transaksi dan update stock
        $items = json_decode($this->request->getVar('items'), true);
        
        foreach($items as $item) {
            $this->transaksiDetailModel->save([
                'id_transaksi' => $id_transaksi,
                'id_kayu' => $item['id_kayu'],
                'quantity' => $item['quantity']
            ]);
            
            // Update stock
            $this->stockModel->updateStock($item['id_kayu'], $this->request->getVar('id_gudang'), $item['quantity']);
        }
        
        session()->setFlashdata('pesan', 'Transaksi masuk berhasil disimpan.');
        return redirect()->to('/transaksi/detail/'.$id_transaksi);
    }
    
    // public function createKeluar()
    // {
    //     $data = [
    //         'title' => 'Transaksi Keluar',
    //         'gudang' => $this->gudangModel->findAll(),
    //         'kayu' => $this->kayuModel->findAll(),
    //         'validation' => \Config\Services::validation()
    //     ];
    //     return view('transaksi/keluar', $data);
    // }
    
    // public function saveKeluar()
    // {
    //     $rules = [
    //         'id_gudang' => 'required',
    //         'tanggal' => 'required',
    //         'items' => 'required'
    //     ];
        
    //     if(!$this->validate($rules)) {
    //         return redirect()->to('/transaksi/keluar')->withInput();
    //     }
        
    //     // Validasi stock cukup
    //     $items = json_decode($this->request->getVar('items'), true);
    //     $id_gudang = $this->request->getVar('id_gudang');
        
    //     foreach($items as $item) {
    //         $stock = $this->stockModel->where(['id_kayu' => $item['id_kayu'], 'id_gudang' => $id_gudang])->first();
    //         if(!$stock || $stock['quantity'] < $item['quantity']) {
    //             session()->setFlashdata('error', 'Stock tidak mencukupi untuk kayu dengan kode: '.$this->kayuModel->find($item['id_kayu'])['kode_kayu']);
    //             return redirect()->to('/transaksi/keluar')->withInput();
    //         }
    //     }
        
    //     // Simpan transaksi
    //     $kode_transaksi = $this->transaksiModel->generateCode('keluar');
        
    //     $this->transaksiModel->save([
    //         'kode_transaksi' => $kode_transaksi,
    //         'jenis_transaksi' => 'keluar',
    //         'id_gudang_asal' => $id_gudang,
    //         'tanggal_transaksi' => $this->request->getVar('tanggal'),
    //         'keterangan' => $this->request->getVar('keterangan'),
    //         'created_by' => session()->get('id_user')
    //     ]);
        
    //     $id_transaksi = $this->transaksiModel->insertID();
        
    //     // Simpan detail transaksi dan update stock
    //     foreach($items as $item) {
    //         $this->transaksiDetailModel->save([
    //             'id_transaksi' => $id_transaksi,
    //             'id_kayu' => $item['id_kayu'],
    //             'quantity' => $item['quantity']
    //         ]);
            
    //         // Update stock (quantity dikurangi)
    //         $this->stockModel->updateStock($item['id_kayu'], $id_gudang, -$item['quantity']);
    //     }
        
    //     session()->setFlashdata('pesan', 'Transaksi keluar berhasil disimpan.');
    //     return redirect()->to('/transaksi/detail/'.$id_transaksi);
    // }
    
    // public function createMutasi()
    // {
    //     $data = [
    //         'title' => 'Mutasi Antar Gudang',
    //         'gudang' => $this->gudangModel->findAll(),
    //         'kayu' => $this->kayuModel->findAll(),
    //         'validation' => \Config\Services::validation()
    //     ];
    //     return view('transaksi/mutasi', $data);
    // }
    
    // public function saveMutasi()
    // {
    //     $rules = [
    //         'id_gudang_asal' => 'required',
    //         'id_gudang_tujuan' => 'required|different[id_gudang_asal]',
    //         'tanggal' => 'required',
    //         'items' => 'required'
    //     ];
        
    //     if(!$this->validate($rules)) {
    //         return redirect()->to('/transaksi/mutasi')->withInput();
    //     }
        
    //     // Validasi stock cukup di gudang asal
    //     $items = json_decode($this->request->getVar('items'), true);
    //     $id_gudang_asal = $this->request->getVar('id_gudang_asal');
        
    //     foreach($items as $item) {
    //         $stock = $this->stockModel->where(['id_kayu' => $item['id_kayu'], 'id_gudang' => $id_gudang_asal])->first();
    //         if(!$stock || $stock['quantity'] < $item['quantity']) {
    //             session()->setFlashdata('error', 'Stock tidak mencukupi untuk kayu dengan kode: '.$this->kayuModel->find($item['id_kayu'])['kode_kayu']);
    //             return redirect()->to('/transaksi/mutasi')->withInput();
    //         }
    //     }
        
    //     // Simpan transaksi
    //     $kode_transaksi = $this->transaksiModel->generateCode('mutasi');
        
    //     $this->transaksiModel->save([
    //         'kode_transaksi' => $kode_transaksi,
    //         'jenis_transaksi' => 'mutasi',
    //         'id_gudang_asal' => $id_gudang_asal,
    //         'id_gudang_tujuan' => $this->request->getVar('id_gudang_tujuan'),
    //         'tanggal_transaksi' => $this->request->getVar('tanggal'),
    //         'keterangan' => $this->request->getVar('keterangan'),
    //         'created_by' => session()->get('id_user')
    //     ]);
        
    //     $id_transaksi = $this->transaksiModel->insertID();
        
    //     // Simpan detail transaksi dan update stock
    //     $id_gudang_tujuan = $this->request->getVar('id_gudang_tujuan');
        
    //     foreach($items as $item) {
    //         $this->transaksiDetailModel->save([
    //             'id_transaksi' => $id_transaksi,
    //             'id_kayu' => $item['id_kayu'],
    //             'quantity' => $item['quantity']
    //         ]);
            
    //         // Kurangi stock di gudang asal
    //         $this->stockModel->updateStock($item['id_kayu'], $id_gudang_asal, -$item['quantity']);
            
    //         // Tambah stock di gudang tujuan
    //         $this->stockModel->updateStock($item['id_kayu'], $id_gudang_tujuan, $item['quantity']);
    //     }
        
    //     session()->setFlashdata('pesan', 'Transaksi mutasi berhasil disimpan.');
    //     return redirect()->to('/transaksi/detail/'.$id_transaksi);
    // }
    
    public function detail($id)
    {
        $transaksi = $this->transaksiModel->select('transaksi.*, g1.nama_gudang as gudang_asal, g2.nama_gudang as gudang_tujuan, u.nama_lengkap as operator')
            ->join('gudang g1', 'g1.id_gudang = transaksi.id_gudang_asal', 'left')
            ->join('gudang g2', 'g2.id_gudang = transaksi.id_gudang_tujuan', 'left')
            ->join('users u', 'u.id = transaksi.created_by', 'left')
            ->find($id);
            
        if(!$transaksi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        $detail = $this->transaksiDetailModel->getDetailByTransaksi($id);
        
        $data = [
            'title' => 'Detail Transaksi',
            'transaksi' => $transaksi,
            'detail' => $detail
        ];
        
        return view('transaksi/detail', $data);
    }
    
public function print($id)
{
    // Ambil data transaksi dengan join
    $transaksi = $this->transaksiModel
        ->select('transaksi.*, g1.nama_gudang as gudang_asal, g2.nama_gudang as gudang_tujuan, u.nama_lengkap as operator')
        ->join('gudang g1', 'g1.id_gudang = transaksi.id_gudang_asal', 'left')
        ->join('gudang g2', 'g2.id_gudang = transaksi.id_gudang_tujuan', 'left')
        ->join('users u', 'u.id = transaksi.created_by', 'left')
        ->find($id);

    if (!$transaksi) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Ambil detail transaksi
    $detail = $this->transaksiModel->getDetailTransaksi($id);

    $data = [
        'transaksi' => $transaksi,
        'detail' => $detail
    ];

    // Konfigurasi Dompdf
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'helvetica');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('transaksi/print', $data));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Ambil output PDF
    $pdfOutput = $dompdf->output();

    // Return dengan Response Object
    return $this->response
        ->setContentType('application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="transaksi-' . $transaksi['kode_transaksi'] . '.pdf"')
        ->setHeader('Content-Length', (string) strlen($pdfOutput))
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->setHeader('Cache-Control', 'post-check=0, pre-check=0', false)
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
        ->setBody($pdfOutput);
}
    public function createKeluar()
{
    $data = [
        'title' => 'Transaksi Keluar Gudang',
        'gudang' => $this->gudangModel->findAll(),
        'kayu' => $this->kayuModel->getKayuWithStock(),
        'validation' => \Config\Services::validation()
    ];
    return view('transaksi/keluar', $data);
}

public function saveKeluar()
{
    $rules = [
        'id_gudang' => 'required',
        'tanggal' => 'required',
        'keterangan' => 'permit_empty',
        'items' => 'required'
    ];

    if (!$this->validate($rules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    $items = json_decode($this->request->getVar('items'), true);
    $id_gudang = $this->request->getVar('id_gudang');

    // Validasi stock cukup
    foreach ($items as $item) {
        $stock = $this->stockModel->where([
            'id_kayu' => $item['id_kayu'],
            'id_gudang' => $id_gudang
        ])->first();

        if (!$stock || $stock['quantity'] < $item['quantity']) {
            $kayu = $this->kayuModel->find($item['id_kayu']);
            return redirect()->back()->withInput()->with('error', 
                "Stock tidak mencukupi untuk kayu {$kayu['kode_kayu']}. Stock tersedia: " . ($stock['quantity'] ?? 0));
        }
    }

    // Simpan transaksi
    $kode_transaksi = $this->transaksiModel->generateCode('keluar');

    $this->transaksiModel->save([
        'kode_transaksi' => $kode_transaksi,
        'jenis_transaksi' => 'keluar',
        'id_gudang_asal' => $id_gudang,
        'tanggal_transaksi' => $this->request->getVar('tanggal'),
        'keterangan' => $this->request->getVar('keterangan'),
        'created_by' => session()->get('id_user')
    ]);

    $id_transaksi = $this->transaksiModel->insertID();

    // Simpan detail transaksi dan update stock
    foreach ($items as $item) {
        $this->transaksiDetailModel->save([
            'id_transaksi' => $id_transaksi,
            'id_kayu' => $item['id_kayu'],
            'quantity' => $item['quantity']
        ]);

        // Kurangi stock
        $this->stockModel->updateStock($item['id_kayu'], $id_gudang, -$item['quantity']);
    }

    return redirect()->to("/transaksi/detail/$id_transaksi")->with('message', 'Transaksi keluar berhasil disimpan');
}

public function createMutasi()
{
    $data = [
        'title' => 'Mutasi Antar Gudang',
        'gudang' => $this->gudangModel->findAll(),
        'kayu' => $this->kayuModel->getKayuWithStock(),
        'validation' => \Config\Services::validation()
    ];
    return view('transaksi/mutasi', $data);
}

public function saveMutasi()
{
    $rules = [
        'id_gudang_asal' => 'required',
        'id_gudang_tujuan' => 'required',
        'tanggal' => 'required',
        'keterangan' => 'permit_empty',
        'items' => 'required'
    ];

    if (!$this->validate($rules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    $items = json_decode($this->request->getVar('items'), true);
    $id_gudang_asal = $this->request->getVar('id_gudang_asal');
    $id_gudang_tujuan = $this->request->getVar('id_gudang_tujuan');

    // Validasi stock cukup di gudang asal
    foreach ($items as $item) {
        $stock = $this->stockModel->where([
            'id_kayu' => $item['id_kayu'],
            'id_gudang' => $id_gudang_asal
        ])->first();

        if (!$stock || $stock['quantity'] < $item['quantity']) {
            $kayu = $this->kayuModel->find($item['id_kayu']);
            return redirect()->back()->withInput()->with('error', 
                "Stock tidak mencukupi untuk kayu {$kayu['kode_kayu']} di gudang asal. Stock tersedia: " . ($stock['quantity'] ?? 0));
        }
    }

    // Simpan transaksi
    $kode_transaksi = $this->transaksiModel->generateCode('mutasi');

    $this->transaksiModel->save([
        'kode_transaksi' => $kode_transaksi,
        'jenis_transaksi' => 'mutasi',
        'id_gudang_asal' => $id_gudang_asal,
        'id_gudang_tujuan' => $id_gudang_tujuan,
        'tanggal_transaksi' => $this->request->getVar('tanggal'),
        'keterangan' => $this->request->getVar('keterangan'),
        'created_by' => session()->get('id_user')
    ]);

    $id_transaksi = $this->transaksiModel->insertID();

    // Simpan detail transaksi dan update stock
    foreach ($items as $item) {
        $this->transaksiDetailModel->save([
            'id_transaksi' => $id_transaksi,
            'id_kayu' => $item['id_kayu'],
            'quantity' => $item['quantity']
        ]);

        // Kurangi stock di gudang asal
        $this->stockModel->updateStock($item['id_kayu'], $id_gudang_asal, -$item['quantity']);
        
        // Tambah stock di gudang tujuan
        $this->stockModel->updateStock($item['id_kayu'], $id_gudang_tujuan, $item['quantity']);
    }

    return redirect()->to("/transaksi/detail/$id_transaksi")->with('message', 'Transaksi mutasi berhasil disimpan');
}

private function getGlobalStock()
{
    $stockData = $this->stockModel->getStockGlobal();
    
    $result = [];
    foreach ($stockData as $stock) {
        $result[] = [
            'id_kayu' => $stock['id_kayu'],
            'kode_kayu' => $stock['kode_kayu'],
            'nama_jenis' => $stock['nama_jenis'],
            'id_gudang' => $stock['id_gudang'],
            'nama_gudang' => $stock['nama_gudang'],
            'quantity' => $stock['quantity'],
            'volume_total' => $stock['quantity'] * $stock['volume'],
            'lokasi_rak' => $stock['lokasi_rak']
        ];
    }
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $result,
        'total_records' => count($result)
    ]);
}

/**
 * Mendapatkan stock berdasarkan jenis kayu tertentu
 */
private function getStockByKayu($id_kayu)
{
    // Validasi kayu exists
    $kayu = $this->kayuModel->find($id_kayu);
    if (!$kayu) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Kayu tidak ditemukan'
        ]);
    }
    
    $stockData = $this->stockModel->where('id_kayu', $id_kayu)->findAll();
    
    $result = [];
    $totalQuantity = 0;
    $totalVolume = 0;
    
    foreach ($stockData as $stock) {
        $gudang = $this->gudangModel->find($stock['id_gudang']);
        
        $result[] = [
            'id_stock' => $stock['id_stock'],
            'id_gudang' => $stock['id_gudang'],
            'nama_gudang' => $gudang['nama_gudang'],
            'quantity' => $stock['quantity'],
            'volume' => $kayu['volume'],
            'volume_total' => $stock['quantity'] * $kayu['volume'],
            'lokasi_rak' => $stock['lokasi_rak']
        ];
        
        $totalQuantity += $stock['quantity'];
        $totalVolume += ($stock['quantity'] * $kayu['volume']);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'kayu' => [
            'id_kayu' => $kayu['id_kayu'],
            'kode_kayu' => $kayu['kode_kayu'],
            'nama_jenis' => $this->getJenisKayuName($kayu['id_jenis']),
            'panjang' => $kayu['panjang'],
            'lebar' => $kayu['lebar'],
            'tebal' => $kayu['tebal'],
            'volume_per_pcs' => $kayu['volume']
        ],
        'stock' => $result,
        'summary' => [
            'total_quantity' => $totalQuantity,
            'total_volume' => $totalVolume,
            'total_gudang' => count($result)
        ]
    ]);
}

/**
 * Mendapatkan stock berdasarkan gudang tertentu
 */
private function getStockByGudang($id_gudang)
{
    // Validasi gudang exists
    $gudang = $this->gudangModel->find($id_gudang);
    if (!$gudang) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gudang tidak ditemukan'
        ]);
    }
    
    $stockData = $this->stockModel->getStockByGudang($id_gudang);
    
    $result = [];
    $totalQuantity = 0;
    $totalVolume = 0;
    
    foreach ($stockData as $stock) {
        $result[] = [
            'id_stock' => $stock['id_stock'],
            'id_kayu' => $stock['id_kayu'],
            'kode_kayu' => $stock['kode_kayu'],
            'nama_jenis' => $stock['nama_jenis'],
            'quantity' => $stock['quantity'],
            'volume_per_pcs' => $stock['volume'],
            'volume_total' => $stock['quantity'] * $stock['volume'],
            'lokasi_rak' => $stock['lokasi_rak']
        ];
        
        $totalQuantity += $stock['quantity'];
        $totalVolume += ($stock['quantity'] * $stock['volume']);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'gudang' => [
            'id_gudang' => $gudang['id_gudang'],
            'kode_gudang' => $gudang['kode_gudang'],
            'nama_gudang' => $gudang['nama_gudang'],
            'alamat' => $gudang['alamat'],
            'kapasitas' => $gudang['kapasitas']
        ],
        'stock' => $result,
        'summary' => [
            'total_quantity' => $totalQuantity,
            'total_volume' => $totalVolume,
            'total_items' => count($result)
        ]
    ]);
}

/**
 * Mendapatkan stock spesifik untuk kayu tertentu di gudang tertentu
 */
private function getSpecificStock($id_kayu, $id_gudang)
{
    // Validasi kayu exists
    $kayu = $this->kayuModel->find($id_kayu);
    if (!$kayu) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Kayu tidak ditemukan'
        ]);
    }
    
    // Validasi gudang exists
    $gudang = $this->gudangModel->find($id_gudang);
    if (!$gudang) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gudang tidak ditemukan'
        ]);
    }
    
    $stock = $this->stockModel->where([
        'id_kayu' => $id_kayu,
        'id_gudang' => $id_gudang
    ])->first();
    
    $quantity = $stock ? $stock['quantity'] : 0;
    $volumeTotal = $quantity * $kayu['volume'];
    
    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'kayu' => [
                'id_kayu' => $kayu['id_kayu'],
                'kode_kayu' => $kayu['kode_kayu'],
                'nama_jenis' => $this->getJenisKayuName($kayu['id_jenis']),
                'panjang' => $kayu['panjang'],
                'lebar' => $kayu['lebar'],
                'tebal' => $kayu['tebal'],
                'volume_per_pcs' => $kayu['volume']
            ],
            'gudang' => [
                'id_gudang' => $gudang['id_gudang'],
                'kode_gudang' => $gudang['kode_gudang'],
                'nama_gudang' => $gudang['nama_gudang']
            ],
            'stock' => [
                'quantity' => $quantity,
                'volume_total' => $volumeTotal,
                'lokasi_rak' => $stock ? $stock['lokasi_rak'] : null
            ]
        ]
    ]);
}

/**
 * Helper function untuk mendapatkan nama jenis kayu
 */
private function getJenisKayuName($id_jenis)
{
    $jenisModel = new \App\Models\JenisKayuModel();
    $jenis = $jenisModel->find($id_jenis);
    return $jenis ? $jenis['nama_jenis'] : 'Unknown';
}
public function getStock($id_kayu, $id_gudang)
{
    // Validasi parameter
    if (!$id_kayu || !$id_gudang) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Parameter id_kayu dan id_gudang diperlukan'
        ]);
    }

    // Cek apakah kayu exists
    $kayu = $this->kayuModel->find($id_kayu);
    if (!$kayu) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Kayu tidak ditemukan'
        ]);
    }

    // Cek apakah gudang exists
    $gudang = $this->gudangModel->find($id_gudang);
    if (!$gudang) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gudang tidak ditemukan'
        ]);
    }

    // Ambil stock dari database
    $stock = $this->stockModel->where([
        'id_kayu' => $id_kayu,
        'id_gudang' => $id_gudang
    ])->first();

    $quantity = $stock ? $stock['quantity'] : 0;

    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'id_kayu' => $id_kayu,
            'id_gudang' => $id_gudang,
            'quantity' => $quantity,
            'kode_kayu' => $kayu['kode_kayu'],
            'nama_gudang' => $gudang['nama_gudang']
        ]
    ]);
}
}