<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to(base_url('admin/auth/login'))->with('error', 'Silakan login sebagai admin/petugas.');
        }

        if (session()->get('admin_status') === 'nonaktif') {
            session()->destroy();
            return redirect()->to(base_url('admin/auth/login'))->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        // Admin/super_admin tidak boleh akses halaman petugas
        $level = session()->get('admin_level');
        if (
            $level === 'petugas'
            && str_contains($request->getUri()->getPath(), '/admin/')
            && !str_contains($request->getUri()->getPath(), '/petugas/')
        ) {
            return redirect()->to(base_url('petugas/dashboard'));
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
