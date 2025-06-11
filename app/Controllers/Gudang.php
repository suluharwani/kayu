<?php namespace App\Controllers;

use App\Models\GudangModel;
use App\Models\StockModel;

class Gudang extends BaseController
{
    protected $gudangModel;
    protected $stockModel;

    public function __construct()
    {
        $this->gudangModel = new GudangModel();
        $this->stockModel = new StockModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Master Gudang',
            'gudang' => $this->gudangModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('master/gudang/index', $data);
    }

    public function create()
    {
        if (!$this->validate([
            'nama_gudang' => 'required|is_unique[gudang.nama_gudang]',
            'alamat' => 'required',
            'kapasitas' => 'required|numeric'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->gudangModel->save([
            'kode_gudang' => $this->gudangModel->generateCode(),
            'nama_gudang' => $this->request->getVar('nama_gudang'),
            'alamat' => $this->request->getVar('alamat'),
            'kapasitas' => $this->request->getVar('kapasitas'),
            'penanggung_jawab' => $this->request->getVar('penanggung_jawab')
        ]);

        return redirect()->to('/gudang')->with('message', 'Data gudang berhasil ditambahkan');
    }

    public function update($id)
    {
        if (!$this->validate([
            'nama_gudang' => "required|is_unique[gudang.nama_gudang,id_gudang,$id]",
            'alamat' => 'required',
            'kapasitas' => 'required|numeric'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->gudangModel->save([
            'id_gudang' => $id,
            'nama_gudang' => $this->request->getVar('nama_gudang'),
            'alamat' => $this->request->getVar('alamat'),
            'kapasitas' => $this->request->getVar('kapasitas'),
            'penanggung_jawab' => $this->request->getVar('penanggung_jawab')
        ]);

        return redirect()->to('/gudang')->with('message', 'Data gudang berhasil diupdate');
    }

    public function delete($id)
    {
        // Cek apakah gudang memiliki stock
        if ($this->stockModel->where('id_gudang', $id)->countAllResults() > 0) {
            return redirect()->to('/gudang')->with('error', 'Gudang tidak bisa dihapus karena masih memiliki stock');
        }

        $this->gudangModel->delete($id);
        return redirect()->to('/gudang')->with('message', 'Data gudang berhasil dihapus');
    }
}