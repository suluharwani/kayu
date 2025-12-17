<?php namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function register()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Registrasi Akun Baru',
            'validation' => \Config\Services::validation()
        ];
        return view('auth/register', $data);
    }

    // Proses Registrasi
    public function attemptRegister()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'password' => 'required|min_length[6]|max_length[255]',
            'pass_confirm' => 'matches[password]',
            'nama_lengkap' => 'required|max_length[100]',
            'role' => 'permit_empty|in_list[admin,gudang,manager]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Default role adalah 'gudang' jika tidak ditentukan
        $role = $this->request->getVar('role') ?? 'gudang';

        $this->userModel->save([
            'username' => $this->request->getVar('username'),
            'password' => $this->request->getVar('password'),
            'nama_lengkap' => $this->request->getVar('nama_lengkap'),
            'role' => $role,
            'status' => 'pending' // Status default pending
        ]);

        return redirect()->to('/login')->with('message', 'Registrasi berhasil! Silakan tunggu aktivasi dari admin.');
    }
    
    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Login - Sistem Manajemen Kayu',
            'validation' => \Config\Services::validation()
        ];
        return view('auth/login', $data);
    }
    
    public function attemptLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];
        
        if(!$this->validate($rules)) {
            return redirect()->to('/login')->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        
        $user = $this->userModel->getUserByUsername($username);
        
        if(!$user || !password_verify($password, $user['password'])) {
            return redirect()->to('/login')->withInput()->with('error', 'Username atau password salah');
        }
        
        // Validasi status akun
        if ($user['status'] != 'active') {
            $statusMessage = '';
            switch($user['status']) {
                case 'pending':
                    $statusMessage = 'Akun Anda menunggu aktivasi dari admin.';
                    break;
                case 'inactive':
                    $statusMessage = 'Akun Anda dinonaktifkan. Hubungi administrator.';
                    break;
                default:
                    $statusMessage = 'Akun Anda tidak aktif.';
            }
            return redirect()->to('/login')->withInput()->with('error', $statusMessage);
        }
        
        $sessionData = [
            'id_user' => $user['id'],
            'username' => $user['username'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role' => $user['role'],
            'status' => $user['status'],
            'logged_in' => true
        ];
        
        session()->set($sessionData);
        
        // Update last login
        $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        
        return redirect()->to('/dashboard')->with('message', 'Selamat datang '.$user['nama_lengkap']);
    }
    
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}