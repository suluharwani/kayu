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
use Endroid\QrCode\Builder\Builder;

use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel;
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
        'kualitas' => 'required'
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

    // Simpan data kayu tanpa stock
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

    return redirect()->to('/kayu')->with('message', 'Data kayu berhasil ditambahkan (tanpa stock)');
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
    // Ambil data kayu
    $kayu = $this->kayuModel->getKayu($id);
    
    if (!$kayu) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Generate barcode image
    $barcode = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $barcodeImage = 'data:image/png;base64,' . base64_encode(
        $barcode->getBarcode($kayu['barcode'], $barcode::TYPE_CODE_128, 2, 50)
    );
    
    $data = [
        'kayu' => $kayu,
        'barcodeImage' => $barcodeImage
    ];
    
    // Konfigurasi Dompdf
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'helvetica');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('kayu/print_barcode', $data));
    
    // Set ukuran kertas untuk label (3x2 inci)
    $dompdf->setPaper([0, 0, 216, 144], 'portrait'); // 3x2 inci dalam points (72pt per inci)
    
    $dompdf->render();

    // Ambil output PDF
    $pdfOutput = $dompdf->output();
    
    // Return dengan Response Object
    return $this->response
        ->setContentType('application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="barcode-' . $kayu['kode_kayu'] . '.pdf"')
        ->setHeader('Content-Length', (string) strlen($pdfOutput))
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
        ->setBody($pdfOutput);
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
    
    // Validasi jika tidak ada data
    if (empty($kayuList)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
    
    $data = ['kayuList' => $kayuList];
    
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'helvetica');
    
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('kayu/print_barcode_batch', $data));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Ambil output PDF
    $pdfOutput = $dompdf->output();
    
    // Generate nama file
    $filename = 'barcode-batch-' . date('Ymd-His') . '.pdf';
    
    // Return dengan Response Object
    return $this->response
        ->setContentType('application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
        ->setHeader('Content-Length', (string) strlen($pdfOutput))
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
        ->setBody($pdfOutput);
}
public function printQrCode($id)
{
    // Ambil data kayu
    $kayu = $this->kayuModel->getKayu($id);
    
    if (!$kayu) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Generate QR Code
    $qrCode = Builder::create()
        ->writer(new PngWriter())
        ->data($kayu['kode_kayu'])
        ->encoding(new Encoding('UTF-8'))
        ->size(150)
        ->margin(2)
        ->build();

    $data = [
        'kayu' => $kayu,
        'qrcode_base64' => base64_encode($qrCode->getString())
    ];

    // Konfigurasi Dompdf
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'helvetica');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('kayu/print_qrcode', $data));
    $dompdf->setPaper([0, 0, 210, 297], 'portrait');
    $dompdf->render();

    // Ambil output PDF
    $pdfOutput = $dompdf->output();
    
    // Return dengan Response Object
    return $this->response
        ->setContentType('application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="label-kayu-' . $kayu['kode_kayu'] . '.pdf"')
        ->setHeader('Content-Length', (string) strlen($pdfOutput))
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
        ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
        ->setBody($pdfOutput);
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

    // Jika tidak ada data yang valid
    if (empty($kayuList)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Rest of the PDF generation remains the same
    $data = ['kayuList' => $kayuList];
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'helvetica');
    
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml(view('kayu/print_qrcode_batch', $data));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Ambil output PDF
    $pdfOutput = $dompdf->output();
    
    // Generate nama file berdasarkan jumlah dan kode kayu
    $filename = 'qrcode-batch-' . date('Ymd-His') . '.pdf';
    
    // Return dengan response object
    return $this->response
        ->setContentType('application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
        ->setHeader('Content-Length', (string) strlen($pdfOutput))
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
        ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
        ->setBody($pdfOutput);
}
}