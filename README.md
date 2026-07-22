<div align="center">

# 📋 Sistem Pengaduan & Manajemen Fasilitas Kampus
### STIKOM Tunas Bangsa Pematangsiantar

Aplikasi web berbasis **CodeIgniter 4** untuk pelaporan pengaduan kerusakan fasilitas sarana dan prasarana kampus, pengelolaan penanganan oleh petugas, serta manajemen inventaris barang per ruangan.

[![PHP](https://img.shields.io/badge/PHP-8.0+-blue)](https://www.php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-orange)](https://codeigniter.com/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-blue)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Academic-green)](#)

</div>

---

## 📖 Tentang Proyek

**Sistem Pengaduan Fasilitas Sarpras** adalah aplikasi web yang dibangun untuk memudahkan pengelolaan pelaporan kerusakan fasilitas di lingkungan kampus STIKOM Tunas Bangsa. Sistem ini menyatukan tiga peran utama:

- **Mahasiswa** melaporkan kerusakan fasilitas (ruangan, barang, gedung) secara digital beserta foto bukti
- **Petugas** menerima notifikasi, mengerjakan penanganan, dan melaporkan progres langsung dari portal mereka
- **Admin / Super Admin** memverifikasi laporan, menugaskan petugas, dan mengelola seluruh data fasilitas kampus

Selain pengaduan, terdapat fitur **manajemen inventaris barang** yang memungkinkan Super Admin mencetak daftar barang per ruangan dalam format PDF, DOCX, maupun XLSX dengan kop surat resmi kampus.

---

## ✨ Fitur Utama

### 🎓 Portal Mahasiswa
- Login menggunakan NIM atau Email
- Buat pengaduan kerusakan fasilitas dengan:
  - Cascading dropdown: **Gedung → Lantai → Ruangan → Barang**
  - Upload hingga **3 foto bukti** kerusakan
  - Pilihan prioritas: Rendah / Sedang / Tinggi
- Pantau **status real-time** pengaduan yang dikirim
- Lihat histori semua pengaduan beserta log perubahan status
- Edit profil akun (nama, email, nomor telepon, foto)

### 👮 Portal Petugas
- Melihat daftar tugas pengaduan yang diberikan admin
- Mengisi **laporan progres** penanganan (judul, deskripsi, status: Proses / Selesai)
- Upload foto bukti penanganan (hingga 3 foto per laporan)
- Membuat **laporan inspeksi fasilitas** mandiri (tanpa harus ada pengaduan)
- Menerima notifikasi WhatsApp otomatis saat ditugaskan

### 🏢 Admin Panel
- **Verifikasi Pengaduan**: Meninjau pengaduan masuk dan menugaskan petugas yang sesuai
- **Update Status**: Mengubah status pengaduan (Menunggu → Diverifikasi → Diproses → Selesai / Ditolak)
- **Manajemen Gedung & Ruangan**: CRUD data gedung dan ruangan kampus
- **Manajemen Inventaris Barang**: CRUD data barang per ruangan
- **Manajemen Mahasiswa**: Kelola akun mahasiswa (aktif / non-aktif, reset password)
- **Manajemen Petugas**: Tambah/edit/hapus akun petugas beserta nomor WA
- **Laporan Inspeksi**: Melihat laporan inspeksi yang dibuat petugas

### 👑 Fitur Eksklusif Super Admin
- Semua akses Admin
- **Cetak Inventaris Barang** (PDF / DOCX / XLSX) dengan kop surat resmi kampus
- **Live Preview** dokumen sebelum dicetak: pilih gedung & ruangan secara multi-select, zoom in/out
- Format dokumen: font Times New Roman, 2 logo kampus (kiri & kanan, *behind text*), kolom tanda tangan

### 📱 Notifikasi WhatsApp (Fonnte)
- Notif ke Admin/Super Admin saat pengaduan baru masuk
- Notif ke Petugas saat ditugaskan menangani pengaduan
- Notif ke Admin/Super Admin saat petugas mengupdate progres

### 🔒 Keamanan
- Sistem autentikasi berbasis session terpisah (Mahasiswa vs Admin/Petugas)
- Guard anti-BFCache: setelah logout, tombol *Back* browser tidak mengembalikan halaman terautentikasi
- Filter middleware per role: `auth`, `admin`, `super_admin`

---

## 🛠️ Requirements & Dependensi

| Kebutuhan | Versi Minimum |
|---|---|
| PHP | 8.0+ (ekstensi `gd` diaktifkan) |
| MySQL / MariaDB | 5.7+ |
| Composer | 2.x |
| Web Server | XAMPP / Apache / Nginx |
| **Library** | `dompdf/dompdf`, `phpoffice/phpword`, `phpoffice/phpspreadsheet` |
| **Opsional** | Akun Fonnte API (untuk notifikasi WhatsApp) |

---

## 🚀 Instalasi & Setup

### Langkah 1 Persiapkan Database

1. Buka **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Buat database baru (nama bebas, contoh: `db_pengaduan_sarpras`)
3. Import file SQL utama:
   ```
   database/migrations/sistem_pengaduan_fasilitas_kampus.sql
   ```
4. Import file SQL tambahan (trigger, kolom pendukung, tabel tambahan):
   ```
   database/migrations/additional.sql
   ```

### Langkah 2 Install Library Composer

Buka terminal di folder root project, lalu jalankan:

```bash
composer require "phpoffice/phpword:^1.4" "phpoffice/phpspreadsheet:^2.4" "dompdf/dompdf:^2.0"
```

### Langkah 3 Konfigurasi `.env`

Salin file `env` menjadi `.env` (jika belum ada), lalu sesuaikan:

```env
# Koneksi Database
database.default.hostname = localhost
database.default.database = db_pengaduan_sarpras
database.default.username = root
database.default.password =

# Token Fonnte (WhatsApp)  opsional
app.fonnteToken = TOKEN_FONNTE_ANDA
```

> ⚠️ Jika tidak menggunakan notifikasi WhatsApp, biarkan `fonnteToken` kosong  fitur lain tetap berjalan normal.

### Langkah 4 Aktifkan Ekstensi PHP GD

Buka `php.ini` (biasanya di `C:\xampp\php\php.ini`), cari dan hilangkan tanda `;` di baris:

```ini
extension=gd
```

Restart Apache setelah menyimpan.

### Langkah 5 Akses Aplikasi

Buka browser dan kunjungi:

```
http://localhost/pengaduan_sarpras/public/
```

---

## 👤 Panduan Penggunaan Client

### Sebagai Mahasiswa

**Login & Akses:**
1. Buka `http://localhost/pengaduan_sarpras/public/auth/login`
2. Login dengan **NIM** atau **email** yang sudah terdaftar
3. Jika belum punya akun, klik **Daftar Sekarang** untuk registrasi mandiri

**Membuat Pengaduan:**
1. Dari dashboard, klik tombol **Buat Pengaduan**
2. Pilih **Gedung** → secara otomatis memuat pilihan **Ruangan** (cascading)
3. Opsional: pilih **Barang** yang rusak (di dalam ruangan tersebut)
4. Pilih **Kategori Kerusakan** dan **Tingkat Prioritas**
5. Isi **Judul** dan **Deskripsi** kerusakan secara jelas
6. Upload **foto bukti** kerusakan (maks. 3 foto, maks. 1MB per foto)
7. Klik **Kirim Pengaduan**  kode unik pengaduan akan tampil

**Memantau Status Pengaduan:**
- Buka menu **Riwayat Pengaduan**
- Klik pengaduan untuk melihat detail & **log perubahan status**
- Status berjalan: `Menunggu` → `Diverifikasi` → `Diproses` → `Selesai` / `Ditolak`

---

### Sebagai Petugas

**Login & Akses:**
1. Buka `http://localhost/pengaduan_sarpras/public/admin/auth/login`
2. Login dengan **email** dan **password** yang diberikan Super Admin
3. Sistem otomatis mengarahkan ke **Portal Petugas**

**Mengelola Tugas Pengaduan:**
1. Dashboard menampilkan daftar pengaduan yang **ditugaskan kepada Anda**
2. Klik pengaduan untuk melihat detail: deskripsi, foto bukti mahasiswa, dan log status
3. Klik **Kirim Laporan Progres** untuk melaporkan perkembangan penanganan
4. Isi **judul laporan**, **deskripsi tindakan**, pilih status `Proses` atau `Selesai`
5. Upload **foto bukti penanganan** (opsional, maks. 3 foto)
6. Klik **Kirim**  Admin akan otomatis menerima notifikasi WhatsApp

**Laporan Inspeksi Mandiri:**
1. Buka menu **Inspeksi Fasilitas**
2. Klik **Buat Laporan Inspeksi**
3. Pilih ruangan yang diperiksa, isi temuan dan kondisi
4. Admin otomatis dinotifikasi via WhatsApp

---

### Sebagai Admin

**Login & Akses:**
1. Buka `http://localhost/pengaduan_sarpras/public/admin/auth/login`
2. Login dengan akun admin yang diberikan Super Admin

**Mengelola Pengaduan Masuk:**
1. Buka menu **Manajemen Pengaduan**
2. Daftar semua pengaduan tampil dengan badge status berwarna
3. Gunakan filter status atau kolom pencarian untuk menyaring
4. Klik **Detail** untuk meninjau lengkap (foto bukti, data mahasiswa, lokasi)
5. Klik **Verifikasi & Tugaskan** → pilih petugas dari dropdown → simpan
6. Sistem mengirim notifikasi WA otomatis ke petugas yang ditunjuk
7. Untuk menolak, klik **Tolak** → isi alasan penolakan

**Memperbarui Status Pengaduan:**
1. Dari detail pengaduan, klik **Update Status**
2. Pilih status baru dan tambahkan catatan opsional
3. Log perubahan tersimpan dan bisa dilihat mahasiswa secara real-time

**Manajemen Data Fasilitas:**
- **Gedung**: Tambah/edit/hapus gedung kampus (nama, alamat, kode)
- **Ruangan**: Tambah/edit/hapus ruangan (tipe, gedung, lantai, kapasitas)
- **Barang**: Tambah/edit/hapus inventaris barang per ruangan (nama, kode, kondisi, jumlah)
- **Mahasiswa**: Aktifkan/nonaktifkan akun, reset password mahasiswa
- **Petugas**: Tambah akun petugas baru beserta nomor WA aktif

---

### Sebagai Super Admin

Super Admin memiliki semua akses Admin, ditambah:

**Cetak Inventaris Barang:**
1. Buka menu **Inventaris → Preview Cetak**
2. Pilih **Gedung** dari dropdown
3. Centang satu atau beberapa **Ruangan** yang akan dicetak
4. Gunakan kontrol **Zoom In / Zoom Out** untuk melihat preview dokumen
5. Pilih format unduhan:
   - 📄 **PDF**  dokumen siap cetak dengan kop surat
   - 📝 **DOCX**  format Word yang bisa diedit
   - 📊 **XLSX**  format Excel untuk pengolahan data
6. Klik tombol format yang diinginkan → file langsung terunduh

---

## 🔐 Akun Default & Hak Akses

| Role | URL Login | Kredensial Default | Hak Akses |
|---|---|---|---|
| **Super Admin** | `/admin/auth/login` | `admin@tunasbangsa.ac.id` / `password` | Akses penuh + cetak inventaris |
| **Admin** | `/admin/auth/login` | *Dibuat oleh Super Admin* | Verifikasi pengaduan, manajemen fasilitas |
| **Petugas** | `/admin/auth/login` | *Dibuat oleh Super Admin* | Portal petugas, laporan progres & inspeksi |
| **Mahasiswa** | `/auth/login` | *Registrasi mandiri atau dibuat Admin* | Buat & pantau pengaduan |

---

## 📱 Alur Sistem  End to End

```
[Mahasiswa]        [Admin/Super Admin]         [Petugas]
    │                      │                       │
    ├─ Buat Pengaduan ──►  │                       │
    │  + Foto Bukti        │                       │
    │                      │ (notif WA masuk)      │
    │                      ├─ Verifikasi ──────►   │
    │                      │ Tugaskan Petugas      │ (notif WA masuk)
    │                      │                       │
    │  ◄── Update Status ──┤◄── Laporan Progres ───┤
    │  (real-time tracking)│   + Foto Penanganan   │
    │                      │ (notif WA masuk)      │
    │  ◄── Status Selesai ─┤                       │
```

---

## 🗺️ Routing Utama

| URL | Akses | Keterangan |
|---|---|---|
| `/auth/login` | Publik | Login Mahasiswa |
| `/auth/register` | Publik | Registrasi Mahasiswa |
| `/admin/auth/login` | Publik | Login Admin / Petugas |
| `/mahasiswa/dashboard` | Mahasiswa | Dashboard statistik |
| `/mahasiswa/pengaduan/buat` | Mahasiswa | Form buat pengaduan |
| `/mahasiswa/pengaduan` | Mahasiswa | Riwayat pengaduan |
| `/petugas/dashboard` | Petugas | Dashboard & daftar tugas |
| `/petugas/pengaduan/{id}` | Petugas | Detail & laporan progres |
| `/petugas/inspeksi` | Petugas | Laporan inspeksi mandiri |
| `/admin/dashboard` | Admin+ | Dashboard utama admin |
| `/admin/pengaduan` | Admin+ | Verifikasi & penugasan |
| `/admin/gedung` | Admin+ | Manajemen gedung |
| `/admin/ruangan` | Admin+ | Manajemen ruangan |
| `/admin/barang` | Admin+ | Manajemen inventaris barang |
| `/admin/mahasiswa` | Admin+ | Manajemen akun mahasiswa |
| `/admin/petugas` | Admin+ | Manajemen akun petugas |
| `/admin/barang/cetak/preview` | **Super Admin** | Live preview cetak inventaris |
| `/admin/barang/cetak/pdf` | **Super Admin** | Download PDF inventaris |
| `/admin/barang/cetak/docx` | **Super Admin** | Download DOCX inventaris |
| `/admin/barang/cetak/xlsx` | **Super Admin** | Download XLSX inventaris |

---

## 📁 Struktur Folder

```
pengaduan_sarpras/
├── app/
│   ├── Config/
│   │   ├── Routes.php                ← Semua routing aplikasi
│   │   └── Filters.php               ← Filter auth, admin, super_admin
│   ├── Controllers/
│   │   ├── Auth/                     ← Login & logout (Mahasiswa & Admin)
│   │   ├── Mahasiswa/                ← Dashboard, pengaduan, profil
│   │   ├── Petugas/                  ← Dashboard, laporan, inspeksi
│   │   ├── Admin/                    ← Verifikasi, CRUD, cetak barang
│   │   └── Api/                      ← Endpoint cascading dropdown
│   ├── Filters/
│   │   ├── AuthFilter.php            ← Middleware sesi mahasiswa
│   │   ├── AdminFilter.php           ← Middleware sesi admin/petugas
│   │   └── SuperAdminFilter.php      ← Middleware khusus super admin
│   ├── Helpers/
│   │   └── whatsapp_helper.php       ← Fungsi notifikasi WA (Fonnte)
│   ├── Models/                       ← Semua model database
│   ├── Services/
│   │   └── InventarisExporter.php    ← Service cetak PDF/DOCX/XLSX
│   └── Views/
│       ├── layouts/                  ← Template layout (auth, mahasiswa, petugas, admin)
│       ├── auth/                     ← Halaman login & register
│       ├── mahasiswa/                ← Halaman portal mahasiswa
│       ├── petugas/                  ← Halaman portal petugas
│       └── admin/                    ← Halaman panel admin
├── database/
│   └── migrations/
│       ├── sistem_pengaduan_fasilitas_kampus.sql   ← Schema utama
│       └── additional.sql                          ← Kolom & trigger tambahan
├── public/
│   ├── assets/
│   │   ├── css/                      ← Stylesheet modular (app.css + modul)
│   │   ├── js/                       ← JavaScript (main.js, admin.js)
│   │   └── logo.png                  ← Logo kampus
│   └── uploads/                      ← Foto bukti & profil yang diupload
└── .env                              ← Konfigurasi environment (DB, token WA)
```

---

## 🐛 Troubleshooting

**❌ Halaman blank / error saat akses pertama kali**
- Pastikan semua library Composer sudah terinstall: jalankan `composer install`
- Cek konfigurasi `.env`  pastikan nama database dan kredensial sudah benar

**❌ Error PDF / Gambar tidak muncul pada laporan cetak**
- Aktifkan ekstensi PHP GD di `php.ini`: hilangkan `;` pada baris `extension=gd`
- Restart Apache setelah mengubah `php.ini`

**❌ Notifikasi WA tidak terkirim**
- Pastikan `app.fonnteToken` di `.env` sudah diisi dengan token aktif dari [Fonnte](https://fonnte.com/)
- Pastikan format nomor telepon di database menggunakan format internasional: `628123456789` (tanpa `+`, tanpa `08`)

**❌ Sesi masih bisa diakses setelah Logout (tombol Back browser)**
- Sistem sudah dilengkapi guard BFCache & header `Cache-Control: no-store`
- Jika masih terjadi, lakukan hard refresh: `Ctrl + Shift + R` atau `Ctrl + F5`

**❌ Cascading dropdown Gedung/Ruangan tidak berfungsi**
- Pastikan endpoint `/api/ruangan?gedung_id=` dapat diakses (tidak terblokir filter)
- Periksa Console browser untuk melihat error JavaScript

**❌ Error `Not unique table/alias` di halaman petugas**
- Pastikan tidak ada panggilan ganda `getWithDetail()` pada instance model yang sama  setiap request harus menggunakan instance baru atau memanggil `getWithDetail()` sekali per query

---

## 📞 Informasi Proyek

| | |
|---|---|
| **Dibangun untuk** | STIKOM Tunas Bangsa, Pematangsiantar, Sumatera Utara |
| **Framework** | CodeIgniter 4 |
| **Database** | MySQL / MariaDB |
| **Library Cetak** | Dompdf (PDF), PhpWord (DOCX), PhpSpreadsheet (XLSX) |
| **Notifikasi** | Fonnte WhatsApp API |
| **CSS** | Vanilla CSS  Arsitektur modular (`base/`, `layout/`, `components/`, `pages/`) |
