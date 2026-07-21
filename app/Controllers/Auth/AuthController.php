<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;
use App\Models\PetugasModel;

class AuthController extends BaseController
{
    protected MahasiswaModel $mahasiswaModel;
    protected PetugasModel   $petugasModel;

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->petugasModel   = new PetugasModel();
    }

    // ============================================================
    // MAHASISWA — LOGIN
    // ============================================================
    public function loginMahasiswa()
    {
        if (session()->get('mahasiswa_logged_in')) {
            return redirect()->to(base_url('mahasiswa/dashboard'));
        }
        return view('auth/login', ['title' => 'Login Mahasiswa']);
    }

    public function doLoginMahasiswa()
    {
        $nim      = $this->request->getPost('nim');
        $password = $this->request->getPost('password');

        if (empty($nim) || empty($password)) {
            return redirect()->back()->with('error', 'NIM dan password wajib diisi.');
        }

        // Cari berdasarkan NIM atau email
        $mahasiswa = $this->mahasiswaModel->where('nim', $nim)
            ->orWhere('email', $nim)->first();

        if (!$mahasiswa || !password_verify($password, $mahasiswa['password'])) {
            return redirect()->back()->with('error', 'NIM/Email atau password salah.')->withInput();
        }

        if ($mahasiswa['status_akun'] === 'nonaktif') {
            return redirect()->back()->with('error', 'Akun Anda telah dinonaktifkan. Hubungi admin.');
        }

        session()->set([
            'mahasiswa_logged_in' => true,
            'mahasiswa_nim'       => $mahasiswa['nim'],
            'mahasiswa_nama'      => $mahasiswa['nama'],
            'mahasiswa_email'     => $mahasiswa['email'],
            'mahasiswa_prodi'     => $mahasiswa['program_studi'],
            'mahasiswa_foto'      => $mahasiswa['foto_profil'],
            'mahasiswa_status'    => $mahasiswa['status_akun'],
        ]);

        return redirect()->to(base_url('mahasiswa/dashboard'))
            ->with('success', 'Selamat datang, ' . $mahasiswa['nama'] . '!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('auth/login'))->with('success', 'Berhasil keluar.');
    }

    // ============================================================
    // MAHASISWA — REGISTER
    // ============================================================
    public function register()
    {
        if (session()->get('mahasiswa_logged_in')) {
            return redirect()->to(base_url('mahasiswa/dashboard'));
        }
        return view('auth/register', ['title' => 'Daftar Akun Mahasiswa']);
    }

    public function doRegister()
    {
        $rules = [
            'nim'             => 'required|min_length[5]|max_length[11]|is_unique[mahasiswa.nim]',
            'nama'            => 'required|min_length[3]|max_length[100]',
            'email'           => 'required|valid_email|is_unique[mahasiswa.email]',
            'password'        => 'required|min_length[8]',
            'konfirm_password'=> 'required|matches[password]',
            'program_studi'   => 'required',
            'angkatan'        => 'required|integer|min_length[4]|max_length[4]',
        ];

        $messages = [
            'nim'             => ['is_unique' => 'NIM sudah terdaftar.'],
            'email'           => ['is_unique' => 'Email sudah terdaftar.'],
            'password'        => ['min_length' => 'Password minimal 8 karakter.'],
            'konfirm_password'=> ['matches' => 'Konfirmasi password tidak cocok.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->mahasiswaModel->insert([
            'nim'           => $this->request->getPost('nim'),
            'nama'          => $this->request->getPost('nama'),
            'email'         => $this->request->getPost('email'),
            'password'      => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'program_studi' => $this->request->getPost('program_studi'),
            'angkatan'      => $this->request->getPost('angkatan'),
            'no_hp'         => $this->request->getPost('no_hp'),
            'status_akun'   => 'aktif',
        ]);

        return redirect()->to(base_url('auth/login'))
            ->with('success', 'Akun berhasil dibuat! Silakan login.');
    }

    // ============================================================
    // ADMIN — LOGIN
    // ============================================================
    public function loginAdmin()
    {
        if (session()->get('admin_logged_in')) {
            $level = session()->get('admin_level');
            return redirect()->to(base_url($level === 'petugas' ? 'petugas/dashboard' : 'admin/dashboard'));
        }
        return view('auth/login_admin', ['title' => 'Login Admin / Petugas']);
    }

    public function doLoginAdmin()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (empty($email) || empty($password)) {
            return redirect()->back()->with('error', 'Email dan password wajib diisi.');
        }

        $petugas = $this->petugasModel->findByEmail($email);

        if (!$petugas || !password_verify($password, $petugas['password'])) {
            return redirect()->back()->with('error', 'Email atau password salah.')->withInput();
        }

        if ($petugas['status_akun'] === 'nonaktif') {
            return redirect()->back()->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        session()->set([
            'admin_logged_in'  => true,
            'admin_id'         => $petugas['id_petugas'],
            'admin_nama'       => $petugas['nama'],
            'admin_email'      => $petugas['email'],
            'admin_jabatan'    => $petugas['jabatan'],
            'admin_level'      => $petugas['level_akses'],
            'admin_status'     => $petugas['status_akun'],
        ]);

        $redirectUrl = $petugas['level_akses'] === 'petugas' ? 'petugas/dashboard' : 'admin/dashboard';
        return redirect()->to(base_url($redirectUrl))
            ->with('success', 'Selamat datang, ' . $petugas['nama'] . '!');
    }

    public function logoutAdmin()
    {
        session()->destroy();
        return redirect()->to(base_url('admin/auth/login'))->with('success', 'Berhasil keluar.');
    }
}
