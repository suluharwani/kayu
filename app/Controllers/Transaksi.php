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

    public function index()
    {
        $data = [
            'title' => 'Data Transaksi',
            'transaksi' => $this->transaksiModel->select('transaksi.*, g1.nama_gudang as gudang_asal, g2.nama_gudang as gudang_tujuan')
                ->join('gudang g1', 'g1.id_gudang = transaksi.id_gudang_asal', 'left')
                ->join('gudang g2', 'g2.id_gudang = transaksi.id_gudang_tujuan', 'left')
                ->orderBy('created_at', 'DESC')
                ->findAll()
        ];
        return view('transaksi/index', $data);
    }

    public function printLabelTransaksi($id_transaksi)
    {
        $transaksi = $this->transaksiModel->getTransaksiWithDetails($id_transaksi);
        if (!$transaksi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $qrCode =Builder::create()
    ->writer(new PngWriter())
    ->data('TRANSAKSI-' . $transaksi['kode_transaksi'])
    ->encoding(new Encoding('UTF-8'))
    ->size(150)
    ->margin(10)
    ->build();

        $data = [
            'transaksi' => $transaksi,
            'qrCode' => base64_encode($qrCode->getString())
        ];

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml(view('transaksi/print_label', $data));
        $dompdf->setPaper('A5', 'landscape');
        $dompdf->render();

        $dompdf->stream('label-transaksi-' . $transaksi['kode_transaksi'] . '.pdf', [
            'Attachment' => false
        ]);
    }

    public function printLabelKayu($id_transaksi)
    {
        $transaksi = $this->transaksiModel->getTransaksiWithDetails($id_transaksi);
        if (!$transaksi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

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

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml(view('transaksi/print_label_kayu', $data));
        $dompdf->setPaper([0, 0, 210, 297], 'portrait');
        $dompdf->render();
        $dompdf->stream('label-kayu-' . $transaksi['kode_transaksi'] . '.pdf', [
            'Attachment' => false
        ]);
    }
    // public function printLabelKayu($id_transaksi)
    // {
    //     $transaksi = $this->transaksiModel->getTransaksiWithDetails($id_transaksi);
    //     if (!$transaksi) {
    //         throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    //     }

    //     $data = [
    //         'transaksi' => $transaksi,
    //         'barcodeGenerator' => new \Picqer\Barcode\BarcodeGeneratorPNG()
    //     ];
    //     // var_dump($data);
    //     // die();
    //     $options = new \Dompdf\Options();
    //     $options->set('isRemoteEnabled', true);
        
    //     $dompdf = new \Dompdf\Dompdf($options);
    //     $dompdf->loadHtml(view('transaksi/print_label_kayu', $data));
    //     $dompdf->setPaper([0, 0, 800, 500], 'portrait'); // Ukuran label kecil
        
    //     $dompdf->render();
    //     $dompdf->stream('label-kayu-'.$transaksi['kode_transaksi'].'.pdf', [
    //         'Attachment' => false
    //     ]);
    // }
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
            'transaksi' => $transaksi,
            'detail' => $detail
        ];
        
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml(view('transaksi/print', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('transaksi-'.$transaksi['kode_transaksi'].'.pdf');
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
}