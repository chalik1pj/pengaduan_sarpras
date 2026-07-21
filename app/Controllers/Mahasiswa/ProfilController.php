<?php

namespace App\Controllers\Mahasiswa;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;

class ProfilController extends BaseController
{
    public function index()
    {
        $nim      = session()->get('mahasiswa_nim');
        $model    = new MahasiswaModel();
        $mahasiswa= $model->find($nim);

        return view('mahasiswa/profil', [
            'title'     => 'Profil Saya',
            'mahasiswa' => $mahasiswa,
            'active'    => 'profil',
        ]);
    }

    public function update()
    {
        $nim   = session()->get('mahasiswa_nim');
        $model = new MahasiswaModel();

        $rules = [
            'nama'          => 'required|min_length[3]|max_length[100]',
            'email'         => 'required|valid_email|is_unique[mahasiswa.email,nim,' . $nim . ']',
            'program_studi' => 'required',
            'no_hp'         => 'permit_empty|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'nama'          => $this->request->getPost('nama'),
            'email'         => $this->request->getPost('email'),
            'program_studi' => $this->request->getPost('program_studi'),
            'angkatan'      => $this->request->getPost('angkatan'),
            'no_hp'         => $this->request->getPost('no_hp'),
        ];

        // Upload foto profil jika ada
        $foto = $this->request->getFile('foto_profil');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $allowedMimes = ['image/jpeg','image/png','image/webp'];
            if (!in_array($foto->getMimeType(), $allowedMimes)) {
                return redirect()->back()->with('error', 'Format foto profil tidak didukung.');
            }
            if ($foto->getSize() > 1048576) {
                return redirect()->back()->with('error', 'Ukuran foto profil maksimal 1MB.');
            }
            $uploadPath = FCPATH . 'uploads/profil/';
            if (!is_dir($uploadPath)) { mkdir($uploadPath, 0777, true); }
            $newName = 'profil_' . $nim . '_' . time() . '.' . $foto->getExtension();
            $foto->move($uploadPath, $newName);
            $updateData['foto_profil'] = 'uploads/profil/' . $newName;

            // Update session foto
            session()->set('mahasiswa_foto', $updateData['foto_profil']);
        }

        $model->update($nim, $updateData);

        session()->set([
            'mahasiswa_nama'  => $updateData['nama'],
            'mahasiswa_email' => $updateData['email'],
            'mahasiswa_prodi' => $updateData['program_studi'],
        ]);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword()
    {
        $nim   = session()->get('mahasiswa_nim');
        $model = new MahasiswaModel();
        $mhs   = $model->find($nim);

        $oldPwd  = $this->request->getPost('password_lama');
        $newPwd  = $this->request->getPost('password_baru');
        $konfirm = $this->request->getPost('konfirm_password');

        if (!password_verify($oldPwd, $mhs['password'])) {
            return redirect()->back()->with('error', 'Password lama tidak sesuai.');
        }
        if ($newPwd !== $konfirm) {
            return redirect()->back()->with('error', 'Konfirmasi password baru tidak cocok.');
        }
        if (strlen($newPwd) < 8) {
            return redirect()->back()->with('error', 'Password baru minimal 8 karakter.');
        }

        $model->update($nim, ['password' => password_hash($newPwd, PASSWORD_BCRYPT)]);
        return redirect()->back()->with('success', 'Password berhasil diperbarui.');
    }
}
