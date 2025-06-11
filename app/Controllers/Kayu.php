<?php namespace App\Controllers;

use App\Models\KayuModel;
use App\Models\JenisKayuModel;
use App\Models\GudangModel;
use App\Models\StockModel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
class Kayu extends BaseController
{
    protected $kayuModel;
    protected $jenisKayuModel;
    protected $gudangModel;
    protected $stockModel;

    public function __construct()
    {
        $this->kayuModel = new KayuModel();
        $this->jenisKayuModel = new JenisKayuModel();
        $this->gudangModel = new GudangModel();
        $this->stockModel = new StockModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Data Kayu',
            'kayu' => $this->kayuModel->getKayuWithJenis(),
            'validation' => \Config\Services::validation()
        ];
        return view('kayu/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Data Kayu',
            'jenis_kayu' => $this->jenisKayuModel->findAll(),
            'gudang' => $this->gudangModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('kayu/create', $data);
    }

    public function store()
    {
        $rules = [
            'id_jenis' => 'required',
            'panjang' => 'required|numeric',
            'lebar' => 'required|numeric',
            'tebal' => 'required|numeric',
            'grade' => 'required',
            'kualitas' => 'required',
            'id_gudang' => 'required',
            'quantity' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Hitung volume
        $panjang = $this->request->getVar('panjang');
        $lebar = $this->request->getVar('lebar');
        $tebal = $this->request->getVar('tebal');
        $volume = ($panjang * $lebar * $tebal) / 1000000; // Konversi ke m3

        // Generate kode kayu dan barcode
        $id_jenis = $this->request->getVar('id_jenis');
        $kode_kayu = $this->kayuModel->generateCode($id_jenis);
        $barcode = $this->kayuModel->generateBarcode($kode_kayu);

        // Simpan data kayu
        $this->kayuModel->save([
            'kode_kayu' => $kode_kayu,
            'id_jenis' => $id_jenis,
            'panjang' => $panjang,
            'lebar' => $lebar,
            'tebal' => $tebal,
            'volume' => $volume,
            'grade' => $this->request->getVar('grade'),
            'kualitas' => $this->request->getVar('kualitas'),
            'barcode' => $barcode
        ]);

        $id_kayu = $this->kayuModel->insertID();

        // Update stock
        $id_gudang = $this->request->getVar('id_gudang');
        $quantity = $this->request->getVar('quantity');
        $this->stockModel->updateStock($id_kayu, $id_gudang, $quantity);

        return redirect()->to('/kayu')->with('message', 'Data kayu berhasil ditambahkan');
    }

    public function edit($id)
    {
        $kayu = $this->kayuModel->find($id);
        if (!$kayu) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Data Kayu',
            'kayu' => $kayu,
            'jenis_kayu' => $this->jenisKayuModel->findAll(),
            'gudang' => $this->gudangModel->findAll(),
            'stock' => $this->stockModel->where('id_kayu', $id)->first(),
            'validation' => \Config\Services::validation()
        ];
        return view('kayu/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'id_jenis' => 'required',
            'panjang' => 'required|numeric',
            'lebar' => 'required|numeric',
            'tebal' => 'required|numeric',
            'grade' => 'required',
            'kualitas' => 'required',
            'id_gudang' => 'required',
            'quantity' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Hitung volume
        $panjang = $this->request->getVar('panjang');
        $lebar = $this->request->getVar('lebar');
        $tebal = $this->request->getVar('tebal');
        $volume = ($panjang * $lebar * $tebal) / 1000000; // Konversi ke m3

        // Update data kayu
        $this->kayuModel->save([
            'id_kayu' => $id,
            'id_jenis' => $this->request->getVar('id_jenis'),
            'panjang' => $panjang,
            'lebar' => $lebar,
            'tebal' => $tebal,
            'volume' => $volume,
            'grade' => $this->request->getVar('grade'),
            'kualitas' => $this->request->getVar('kualitas')
        ]);

        // Update stock
        $id_gudang = $this->request->getVar('id_gudang');
        $quantity = $this->request->getVar('quantity');
        $this->stockModel->updateStock($id, $id_gudang, $quantity);

        return redirect()->to('/kayu')->with('message', 'Data kayu berhasil diupdate');
    }

    public function delete($id)
    {
        // Cek apakah kayu ada di transaksi
        if ($this->kayuModel->hasTransactions($id)) {
            return redirect()->to('/kayu')->with('error', 'Data kayu tidak bisa dihapus karena sudah digunakan dalam transaksi');
        }

        $this->kayuModel->delete($id);
        $this->stockModel->where('id_kayu', $id)->delete();

        return redirect()->to('/kayu')->with('message', 'Data kayu berhasil dihapus');
    }

    public function barcode($id)
    {
        $kayu = $this->kayuModel->getKayu($id);
        if (!$kayu) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Barcode Kayu',
            'kayu' => $kayu
        ];
        return view('kayu/barcode', $data);
    }

public function printBarcode($id)
{
    $kayu = $this->kayuModel->getKayu($id);
    if (!$kayu) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Generate barcode image langsung (tanpa melalui route terpisah)
    $barcode = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $barcodeImage = 'data:image/png;base64,' . base64_encode(
        $barcode->getBarcode($kayu['barcode'], $barcode::TYPE_CODE_128, 2, 50)
    );
    
    $data = [
        'kayu' => $kayu,
        'barcodeImage' => $barcodeImage
    ];
    
    // Setup DOMPDF dengan opsi yang lebih baik
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('kayu/print_barcode', $data));
    
    // Set ukuran kertas khusus untuk label (misalnya: 3x2 inci)
    $dompdf->setPaper([0, 0, 216, 144], 'portrait'); // 3x2 inci dalam points (72pt per inci)
    
    $dompdf->render();
    $dompdf->stream('barcode-'.$kayu['kode_kayu'].'.pdf', [
        'Attachment' => false // false untuk preview di browser, true untuk download otomatis
    ]);
}
public function printBarcodeBatch($ids)
{
    $kayuList = [];
    $barcode = new \Picqer\Barcode\BarcodeGeneratorPNG();
    
    foreach (explode(',', $ids) as $id) {
        $kayu = $this->kayuModel->getKayu($id);
        if ($kayu) {
            $kayu['barcodeImage'] = 'data:image/png;base64,' . base64_encode(
                $barcode->getBarcode($kayu['barcode'], $barcode::TYPE_CODE_128, 2, 50)
            );
            $kayuList[] = $kayu;
        }
    }
    
    $data = ['kayuList' => $kayuList];
    
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('kayu/print_barcode_batch', $data));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('barcodes.pdf');
}
public function printQrCode($id)
{
    $kayu = $this->kayuModel->getKayu($id);
    if (!$kayu) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Data to encode in QR Code
    $qrContent = json_encode([
        'kode' => $kayu['kode_kayu'],
        'jenis' => $kayu['nama_jenis'],
        'dimensi' => $kayu['panjang'].'x'.$kayu['lebar'].'x'.$kayu['tebal'],
        'volume' => $kayu['volume'],
        'grade' => $kayu['grade']
    ]);

    // Create QR Code (v6.x syntax)
    $qrCode = QrCode::create($qrContent)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setSize(300)
        ->setMargin(10)
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
        ->setForegroundColor(new Color(0, 0, 0)) // Black
        ->setBackgroundColor(new Color(255, 255, 255)); // White

    // Generate PNG
    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    // Convert to base64 for embedding in HTML
    $qrCodeImage = 'data:image/png;base64,' . base64_encode($result->getString());

    $data = [
        'kayu' => $kayu,
        'qrCodeImage' => $qrCodeImage
    ];

    // PDF Generation (unchanged)
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('kayu/print_qrcode', $data));
    $dompdf->setPaper([0, 0, 162.28, 90.71], 'portrait');
    $dompdf->render();
    $dompdf->stream('qrcode-'.$kayu['kode_kayu'].'.pdf', [
        'Attachment' => false
    ]);
}

public function printQrCodeBatch($ids)
{
    $kayuList = [];
    foreach (explode(',', $ids) as $id) {
        $kayu = $this->kayuModel->getKayu($id);
        if ($kayu) {
            $qrContent = json_encode([
                'kode' => $kayu['kode_kayu'],
                'jenis' => $kayu['nama_jenis'],
                'dimensi' => $kayu['panjang'].'x'.$kayu['lebar'].'x'.$kayu['tebal']
            ]);

            $qrCode = QrCode::create($qrContent)
                ->setSize(200)
                ->setMargin(10);

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            $kayu['qrCodeImage'] = 'data:image/png;base64,' . base64_encode($result->getString());
            $kayuList[] = $kayu;
        }
    }

    // Rest of the PDF generation remains the same
    $data = ['kayuList' => $kayuList];
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('kayu/print_qrcode_batch', $data));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('qrcodes.pdf');
}
}