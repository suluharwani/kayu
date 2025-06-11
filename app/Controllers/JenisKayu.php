<?php namespace App\Controllers;

use App\Models\JenisKayuModel;

class JenisKayu extends BaseController
{
    protected $jenisKayuModel;

    public function __construct()
    {
        $this->jenisKayuModel = new JenisKayuModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Master Jenis Kayu',
            'jenis_kayu' => $this->jenisKayuModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('master/jenis_kayu/index', $data);
    }

    public function create()
    {
        if (!$this->validate([
            'nama_jenis' => 'required|is_unique[jenis_kayu.nama_jenis]',
            'kategori' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->jenisKayuModel->save([
            'kode_jenis' => $this->jenisKayuModel->generateCode(),
            'nama_jenis' => $this->request->getVar('nama_jenis'),
            'kategori' => $this->request->getVar('kategori'),
            'deskripsi' => $this->request->getVar('deskripsi'),
            'harga_per_volume' => $this->request->getVar('harga_per_volume')
        ]);

        return redirect()->to('/jenis-kayu')->with('message', 'Data jenis kayu berhasil ditambahkan');
    }

    public function update($id)
    {
        if (!$this->validate([
            'nama_jenis' => "required|is_unique[jenis_kayu.nama_jenis,id_jenis,$id]",
            'kategori' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->jenisKayuModel->save([
            'id_jenis' => $id,
            'nama_jenis' => $this->request->getVar('nama_jenis'),
            'kategori' => $this->request->getVar('kategori'),
            'deskripsi' => $this->request->getVar('deskripsi'),
            'harga_per_volume' => $this->request->getVar('harga_per_volume')
        ]);

        return redirect()->to('/jenis-kayu')->with('message', 'Data jenis kayu berhasil diupdate');
    }

    public function delete($id)
    {
        // Cek apakah jenis kayu digunakan di data kayu
        if ($this->jenisKayuModel->hasKayu($id)) {
            return redirect()->to('/jenis-kayu')->with('error', 'Jenis kayu tidak bisa dihapus karena sudah digunakan');
        }

        $this->jenisKayuModel->delete($id);
        return redirect()->to('/jenis-kayu')->with('message', 'Data jenis kayu berhasil dihapus');
    }
    // Di JenisKayu.php
public function exportExcel()
{
    $jenisKayu = $this->jenisKayuModel->findAll();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Header
    $sheet->setCellValue('A1', 'No');
    $sheet->setCellValue('B1', 'Kode Jenis');
    $sheet->setCellValue('C1', 'Nama Jenis');
    $sheet->setCellValue('D1', 'Kategori');
    $sheet->setCellValue('E1', 'Harga/m3');
    
    // Data
    $no = 1;
    $row = 2;
    foreach($jenisKayu as $jk) {
        $sheet->setCellValue('A'.$row, $no++);
        $sheet->setCellValue('B'.$row, $jk['kode_jenis']);
        $sheet->setCellValue('C'.$row, $jk['nama_jenis']);
        $sheet->setCellValue('D'.$row, $jk['kategori']);
        $sheet->setCellValue('E'.$row, $jk['harga_per_volume']);
        $row++;
    }
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="jenis_kayu.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
}
}