<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================
// HALAMAN PUBLIK
// ============================================================
$routes->get('/', 'Auth\AuthController::loginMahasiswa');
$routes->get('/beranda', 'Auth\AuthController::loginMahasiswa');

// ============================================================
// AUTENTIKASI MAHASISWA
// ============================================================
$routes->group('auth', function ($routes) {
    $routes->get('login',    'Auth\AuthController::loginMahasiswa');
    $routes->post('login',   'Auth\AuthController::doLoginMahasiswa');
    $routes->get('register', 'Auth\AuthController::register');
    $routes->post('register','Auth\AuthController::doRegister');
    $routes->get('logout',   'Auth\AuthController::logout');
});

// ============================================================
// AUTENTIKASI ADMIN / PETUGAS
// ============================================================
$routes->group('admin/auth', function ($routes) {
    $routes->get('login',  'Auth\AuthController::loginAdmin');
    $routes->post('login', 'Auth\AuthController::doLoginAdmin');
    $routes->get('logout', 'Auth\AuthController::logoutAdmin');
});

// ============================================================
// MAHASISWA (Dilindungi AuthFilter)
// ============================================================
$routes->group('mahasiswa', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Mahasiswa\DashboardController::index');

    // Pengaduan
    $routes->get('pengaduan',           'Mahasiswa\PengaduanController::index');
    $routes->get('pengaduan/buat',      'Mahasiswa\PengaduanController::create');
    $routes->post('pengaduan/simpan',   'Mahasiswa\PengaduanController::store');
    $routes->get('pengaduan/(:num)',    'Mahasiswa\PengaduanController::show/$1');
    $routes->post('pengaduan/(:num)/delete', 'Mahasiswa\PengaduanController::delete/$1');

    // Profil
    $routes->get('profil',            'Mahasiswa\ProfilController::index');
    $routes->post('profil/update',    'Mahasiswa\ProfilController::update');
    $routes->post('profil/password',  'Mahasiswa\ProfilController::updatePassword');
});

// ============================================================
// ADMIN / PETUGAS (Dilindungi AdminFilter)
// ============================================================
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // Pengaduan
    $routes->get('pengaduan',                    'Admin\PengaduanController::index');
    $routes->get('pengaduan/(:num)',             'Admin\PengaduanController::show/$1');
    $routes->post('pengaduan/(:num)/status',     'Admin\PengaduanController::updateStatus/$1');
    $routes->post('pengaduan/(:num)/assign',     'Admin\PengaduanController::assign/$1');

    // Gedung
    $routes->get('gedung',              'Admin\GedungController::index');
    $routes->post('gedung/simpan',      'Admin\GedungController::store');
    $routes->post('gedung/update/(:num)', 'Admin\GedungController::update/$1');
    $routes->post('gedung/hapus/(:num)', 'Admin\GedungController::delete/$1');

    // Ruangan
    $routes->get('ruangan',              'Admin\RuanganController::index');
    $routes->post('ruangan/simpan',      'Admin\RuanganController::store');
    $routes->post('ruangan/update/(:num)', 'Admin\RuanganController::update/$1');
    $routes->post('ruangan/hapus/(:num)', 'Admin\RuanganController::delete/$1');

    // Barang
    $routes->get('barang',              'Admin\BarangController::index');
    $routes->post('barang/simpan',      'Admin\BarangController::store');
    $routes->post('barang/update/(:num)', 'Admin\BarangController::update/$1');
    $routes->post('barang/hapus/(:num)', 'Admin\BarangController::delete/$1');

    // Petugas
    $routes->get('petugas',              'Admin\PetugasController::index');
    $routes->post('petugas/simpan',      'Admin\PetugasController::store');
    $routes->post('petugas/update/(:num)', 'Admin\PetugasController::update/$1');
    $routes->post('petugas/hapus/(:num)', 'Admin\PetugasController::delete/$1');

    // Mahasiswa
    $routes->get('mahasiswa',                    'Admin\MahasiswaController::index');
    $routes->post('mahasiswa/toggle/(:alphanum)', 'Admin\MahasiswaController::toggle/$1');
});

// ============================================================
// PETUGAS (Dilindungi PetugasFilter — hanya level petugas)
// ============================================================
$routes->group('petugas', ['filter' => 'petugas'], function ($routes) {
    $routes->get('dashboard',                       'Petugas\DashboardController::index');

    // Pengaduan yang ditugaskan
    $routes->get('pengaduan',                       'Petugas\PengaduanController::index');
    $routes->get('pengaduan/(:num)',                'Petugas\PengaduanController::show/$1');
    $routes->post('pengaduan/(:num)/laporan',       'Petugas\PengaduanController::simpanLaporan/$1');

    // Inspeksi fasilitas
    $routes->get('inspeksi',                        'Petugas\InspeksiController::index');
    $routes->get('inspeksi/buat',                   'Petugas\InspeksiController::create');
    $routes->post('inspeksi/simpan',                'Petugas\InspeksiController::store');
    $routes->get('inspeksi/(:num)',                 'Petugas\InspeksiController::show/$1');
});

// ============================================================
// ADMIN — Tambahan route verifikasi laporan & inspeksi
// ============================================================
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    // Laporan petugas (verifikasi)
    $routes->get('laporan',                              'Admin\LaporanController::index');
    $routes->get('laporan/(:num)',                       'Admin\LaporanController::show/$1');
    $routes->post('laporan/(:num)/verifikasi',           'Admin\LaporanController::verifikasi/$1');

    // Inspeksi fasilitas (verifikasi)
    $routes->get('inspeksi',                             'Admin\InspeksiController::index');
    $routes->get('inspeksi/(:num)',                      'Admin\InspeksiController::show/$1');
    $routes->post('inspeksi/(:num)/verifikasi',          'Admin\InspeksiController::verifikasi/$1');
});

// ============================================================
// API AJAX (Cascading Dropdown)
// ============================================================
$routes->group('api', function ($routes) {
    $routes->get('ruangan/(:num)',  'Api\DropdownController::ruangan/$1');
    $routes->get('barang/(:num)',   'Api\DropdownController::barang/$1');
    $routes->get('gedung',          'Api\DropdownController::gedung');
});

// ============================================================
// CETAK INVENTARIS BARANG (Hanya Super Admin)
// ============================================================
$routes->group('admin/barang', ['filter' => 'super_admin'], function ($routes) {
    $routes->get('cetak/pdf',             'Admin\BarangController::cetakPdf');
    $routes->get('cetak/docx',            'Admin\BarangController::cetakDocx');
    $routes->get('cetak/xlsx',            'Admin\BarangController::cetakXlsx');
    $routes->get('cetak/preview',         'Admin\BarangController::cetakPreview');
    $routes->get('cetak/preview-content', 'Admin\BarangController::cetakPreviewContent');
});
