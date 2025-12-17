<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $currentRoute = $request->getUri()->getPath();
    
        // Daftar route yang boleh diakses tanpa login
        $allowedRoutes = ['login', 'auth/attemptLogin', 'register', 'auth/attemptRegister', '/', 'home', 'logout'];
        
        // Jika mencoba mengakses route yang diizinkan, lanjutkan
        if (in_array($currentRoute, $allowedRoutes)) {
            return;
        }
        
        // Cek apakah user sudah login
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }
        
        // Cek status user - hanya user dengan status 'active' yang bisa akses
        $userStatus = session()->get('status');
        if ($userStatus !== 'active') {
            session()->destroy();
            
            $message = '';
            switch($userStatus) {
                case 'pending':
                    $message = 'Akun Anda menunggu aktivasi. Hubungi administrator.';
                    break;
                case 'inactive':
                    $message = 'Akun Anda dinonaktifkan. Hubungi administrator.';
                    break;
                default:
                    $message = 'Akun Anda tidak aktif.';
            }
            
            return redirect()->to('/login')->with('error', $message);
        }
        
        // Cek role untuk route tertentu yang membutuhkan authorization
        if ($arguments) {
            $userRole = session()->get('role');
            
            // Jika role tidak termasuk dalam arguments yang diizinkan
            if (!in_array($userRole, $arguments)) {
                // Redirect berdasarkan role
                switch($userRole) {
                    case 'admin':
                    case 'manager':
                        return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
                    case 'gudang':
                        // Staff gudang tidak bisa akses manajemen user
                        if (strpos($currentRoute, 'pengguna') !== false) {
                            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke manajemen pengguna');
                        }
                        break;
                }
            }
        }
        
        // Middleware khusus untuk manajemen pengguna (hanya admin dan manager)
        if (strpos($currentRoute, 'pengguna') !== false && $currentRoute !== 'pengguna/profile') {
            $userRole = session()->get('role');
            if (!in_array($userRole, ['admin', 'manager'])) {
                return redirect()->to('/dashboard')->with('error', 'Hanya admin dan manager yang dapat mengakses manajemen pengguna');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa setelah request
    }
}