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
        $allowedRoutes = ['login', 'auth/attemptLogin', 'register', 'auth/attemptRegister', '/', 'home'];
        
        // Jika mencoba mengakses route yang diizinkan, lanjutkan
        if (in_array($currentRoute, $allowedRoutes)) {
            return;
        }
        
        // Jika belum login, redirect ke halaman login
        // if (!session()->get('logged_in')) {
        //     return redirect()->to('/login');
        // }
        
        // Cek role jika ada argument
        if ($arguments) {
            $userRole = session()->get('role');
            
            if (!in_array($userRole, $arguments)) {
                return redirect()->to('/unauthorized');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa setelah request
    }
}