<?php namespace App\Controllers;

use App\Models\UserModel;

class Pengguna extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Pengguna',
            'users' => $this->userModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('pengguna/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Pengguna Baru',
            'validation' => \Config\Services::validation()
        ];
        return view('pengguna/create', $data);
    }

    public function store()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'password' => 'required|min_length[6]|max_length[255]',
            'pass_confirm' => 'matches[password]',
            'nama_lengkap' => 'required|max_length[100]',
            'role' => 'required|in_list[admin,gudang,manager]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->save([
            'username' => $this->request->getVar('username'),
            'password' => $this->request->getVar('password'),
            'nama_lengkap' => $this->request->getVar('nama_lengkap'),
            'role' => $this->request->getVar('role')
        ]);

        return redirect()->to('/pengguna')->with('message', 'Pengguna berhasil ditambahkan');
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Pengguna',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];
        return view('pengguna/edit', $data);
    }

    public function update($id)
    {

        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'nama_lengkap' => 'required|max_length[100]',
            'role' => 'required|in_list[admin,gudang,manager]'
        ];

        // Jika username diubah
        if ($user['username'] != $this->request->getVar('username')) {
            $rules['username'] = 'required|min_length[3]|max_length[20]|is_unique[users.username]';
        }

        // Jika password diubah
        if ($this->request->getVar('password')) {
            $rules['password'] = 'min_length[6]|max_length[255]';
            $rules['pass_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => $id,
            'username' => $this->request->getVar('username'),
            'nama_lengkap' => $this->request->getVar('nama_lengkap'),
            'role' => $this->request->getVar('role')
        ];

        

        // Update password jika diisi
        if ($this->request->getVar('password')) {
            $data['password'] = $this->request->getVar('password');
        }

        $this->userModel->where('id',$id)->set($data)->update();
        

        return redirect()->to('/pengguna')->with('message', 'Data pengguna berhasil diupdate');
    }

    public function delete($id)
    {
        // Cek apakah user sedang login
        if (session()->get('id_user') == $id) {
            return redirect()->to('/pengguna')->with('error', 'Tidak dapat menghapus akun yang sedang digunakan');
        }

        $this->userModel->delete($id);
        return redirect()->to('/pengguna')->with('message', 'Pengguna berhasil dihapus');
    }

    public function profile()
    {
        $id = session()->get('id_user');
        $user = $this->userModel->find($id);
        
        $data = [
            'title' => 'Profil Saya',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];
        return view('pengguna/profile', $data);
    }

    public function updateProfile()
    {
        $id = session()->get('id_user');
        $user = $this->userModel->find($id);
        
        $rules = [
            'nama_lengkap' => 'required|max_length[100]'
        ];

        // Jika username diubah
        if ($user['username'] != $this->request->getVar('username')) {
            $rules['username'] = 'required|min_length[3]|max_length[20]|is_unique[users.username]';
        }

        // Jika password diubah
        if ($this->request->getVar('password')) {
            $rules['password'] = 'min_length[6]|max_length[255]';
            $rules['pass_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => $id,
            'username' => $this->request->getVar('username'),
            'nama_lengkap' => $this->request->getVar('nama_lengkap')
        ];
        
        // Update password jika diisi
        if ($this->request->getVar('password')) {
            $data['password'] = $this->request->getVar('password');
        }

        $this->userModel->save($data);

        // Update session
        session()->set('username', $data['username']);
        session()->set('nama_lengkap', $data['nama_lengkap']);

        return redirect()->to('/pengguna/profile')->with('message', 'Profil berhasil diupdate');
    }
}