<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('mahasiswa_logged_in')) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('mahasiswa_status') === 'nonaktif') {
            session()->destroy();
            return redirect()->to(base_url('auth/login'))->with('error', 'Akun Anda telah dinonaktifkan.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Cegah browser meng-cache halaman yang memerlukan autentikasi.
        // Ini memastikan tombol Back tidak menampilkan halaman setelah logout.
        return $response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');
    }
}
