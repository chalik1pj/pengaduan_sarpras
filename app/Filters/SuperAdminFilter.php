<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SuperAdminFilter — hanya level 'super_admin' yang bisa akses route ini
 */
class SuperAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to(base_url('admin/auth/login'))->with('error', 'Silakan login sebagai admin.');
        }

        if (session()->get('admin_level') !== 'super_admin') {
            return redirect()->to(base_url('admin/barang'))->with('error', 'Akses ditolak. Fitur ini hanya untuk Super Admin.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');
    }
}
