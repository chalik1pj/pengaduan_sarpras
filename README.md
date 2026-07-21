# 📋 Sistem Pengaduan & Manajemen Fasilitas Kampus
### STIKOM Tunas Bangsa Pematangsiantar

Aplikasi web untuk pelaporan, penanganan pengaduan fasilitas sarana prasarana kampus, serta pengelolaan inventaris barang ruangan. Dibangun menggunakan **CodeIgniter 4** dan desain **Modern Dark UI**.

---

## ✨ Fitur Utama

| Fitur | Keterangan |
|---|---|
| 🎓 **Portal Mahasiswa** | Login via NIM/Email, buat pengaduan dengan cascading dropdown (Gedung → Ruangan → Barang), upload max 3 foto bukti, & tracking status real-time. |
| 👮 **Portal Petugas** | Halaman khusus petugas untuk melihat tugas pengaduan yang diberikan, mengisi laporan progres, dan mengunggah foto bukti penanganan. |
| 🏢 **Admin & Super Admin Panel** | Manajemen Pengaduan (verifikasi & penugasan petugas), Manajemen Gedung, Ruangan, Barang/Fasilitas, Mahasiswa, dan Petugas. |
| 📱 **Notifikasi WhatsApp (Fonnte API)** | Notifikasi WA otomatis ke Admin/Super Admin saat mahasiswa mengadu, notif ke Petugas saat ditugaskan, dan notif ke Admin saat Petugas memperbarui progres. |
| 🖨️ **Preview & Cetak Inventaris** | Fitur cetak inventaris barang per ruangan (hanya `super_admin`) dengan **Halaman Preview Live**, multi-select ruangan, font Times New Roman, Kop Surat bertanda tangan lengkap + 2 Logo (posisi *behind text*), serta unduh format **PDF**, **DOCX**, dan **XLSX**. |
| 🔒 **Keamanan & Anti-BFCache** | Guard session anti-back setelah logout dengan header `Cache-Control: no-store` dan BFCache script guard. |
| 📱 **Responsive UI** | Tampilan modern dark theme dengan visual konsisten dan responsif di perangkat mobile/desktop. |

---

## 🛠️ Requirements & Dependensi

- **PHP 8.0+** (dengan ekstensi `extension=gd` diaktifkan untuk pembuatan PDF/Gambar)
- **MySQL / MariaDB 5.7+**
- **XAMPP** (atau web server Apache/Nginx lain)
- **Composer** (untuk library `dompdf/dompdf`, `phpoffice/phpword`, `phpoffice/phpspreadsheet`)
- Akun **Fonnte API** (untuk fitur notifikasi WhatsApp)

---

## 🚀 Instalasi

### Langkah 1 Import Database

1. Buka **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Buat database baru bernama `db_pengaduan_fasilitas`
3. Import file SQL utama:
   ```
   database/migrations/sistem_pengaduan_fasilitas_kampus.sql
   ```
4. Import file SQL tambahan (menambah kolom, trigger, & tabel pendukung):
   ```
   database/migrations/additional.sql
   ```

### Langkah 2 Install Library Composer

Jalankan perintah berikut pada terminal di folder project untuk mengunduh library cetak dokumen:
```bash
composer require "phpoffice/phpword:^1.4" "phpoffice/phpspreadsheet:^2.4" "dompdf/dompdf:^2.0"
```

### Langkah 3 Konfigurasi `.env`

File `.env` sudah berada di root project. Pastikan konfigurasi database sudah benar:

```env
database.default.hostname = localhost
database.default.database = db_pengaduan_fasilitas
database.default.username = root
database.default.password = 
```

Untuk fitur **Notifikasi WhatsApp**, isi token Fonnte:
```env
app.fonnteToken = 'TOKEN_FONNTE_ANDA'
```

### Langkah 4 Akses Aplikasi

Buka browser dan kunjungi:
```
http://localhost/pengaduan_sarpras/public/
```

---

## 🔐 Akun Default & Hak Akses

| Role | Access URL | Credentials Default | Hak Akses Utama |
|---|---|---|---|
| **Super Admin** | `/admin/auth/login` | `admin@tunasbangsa.ac.id` / `password` | Akses Penuh (Manajemen Petugas, Verifikasi Pengaduan, Cetak/Preview Inventaris PDF/DOCX/XLSX) |
| **Admin** | `/admin/auth/login` | *Dibuat oleh Super Admin* | Verifikasi pengaduan, menugaskan petugas, manajemen fasilitas & mahasiswa |
| **Petugas** | `/admin/auth/login` | *Dibuat oleh Super Admin* | Otomatis diarahkan ke Portal Petugas (`/petugas/dashboard`), mengisi laporan progres & bukti foto penanganan |
| **Mahasiswa** | `/auth/login` | *Register Mandiri* / Admin | Membuat pengaduan kerusakan fasilitas & memantau status histori |

---

## 📁 Struktur Folder Penting

```
pengaduan_sarpras/
├── app/
│   ├── Config/
│   │   ├── Routes.php          ← Routing aplikasi (Auth, Mahasiswa, Petugas, Admin, Cetak)
│   │   └── Filters.php         ← Filter auth, admin, & super_admin
│   ├── Controllers/
│   │   ├── Auth/               ← Auth Mahasiswa & Admin/Petugas
│   │   ├── Mahasiswa/          ← Dashboard, Form Pengaduan, Profil Mahasiswa
│   │   ├── Petugas/            ← Dashboard Petugas & Laporan Progres Penanganan
│   │   ├── Admin/              ← Dashboard Admin, Verifikasi, BarangController (CRUD & Cetak)
│   │   └── Api/                ← Cascading dropdown API (Gedung → Ruangan → Barang)
│   ├── Filters/
│   │   ├── AuthFilter.php      ← Middleware Mahasiswa
│   │   ├── AdminFilter.php     ← Middleware Admin/Petugas
│   │   └── SuperAdminFilter.php← Middleware Khusus Super Admin (Cetak)
│   ├── Helpers/
│   │   └── whatsapp_helper.php ← Fonnte WA notification helper
│   ├── Models/                 ← Models (Pengaduan, Barang, Ruangan, Gedung, Petugas, dll)
│   └── Views/
│       ├── layouts/            ← Layout Auth, Mahasiswa, Admin, & Petugas
│       ├── auth/               ← Tampilan Login & Register
│       ├── mahasiswa/          ← Tampilan Mahasiswa
│       ├── petugas/            ← Tampilan Portal Petugas
│       └── admin/              ← Tampilan Admin & Preview Cetak Barang
├── database/migrations/
│   ├── sistem_pengaduan_fasilitas_kampus.sql
│   └── additional.sql
├── public/
│   ├── assets/
│   │   ├── css/style.css       ← Modern Dark UI Styling
│   │   ├── img/                ← Logo Kiri & Logo Kanan untuk Kop Surat
│   │   └── js/                 ← Main JS & Admin scripts
│   └── index.php
└── writable/
    └── uploads/                ← Direktori file upload foto bukti & profil
```

---

## 🗺️ Routing Utama

| URL | Method | Akses | Keterangan |
|---|---|---|---|
| `/auth/login` | GET/POST | Publik | Halaman Login Mahasiswa |
| `/auth/register` | GET/POST | Publik | Halaman Registrasi Mahasiswa |
| `/admin/auth/login` | GET/POST | Publik | Halaman Login Admin / Super Admin / Petugas |
| `/mahasiswa/dashboard` | GET | Mahasiswa | Dashboard Statistik Mahasiswa |
| `/mahasiswa/pengaduan/buat` | GET/POST | Mahasiswa | Form Buat Pengaduan Fasilitas |
| `/petugas/dashboard` | GET | Petugas | Portal Petugas   Daftar Tugas Penanganan |
| `/admin/dashboard` | GET | Admin / Super Admin | Dashboard Utama Admin |
| `/admin/pengaduan` | GET | Admin / Super Admin | Verifikasi & Penugasan Petugas |
| `/admin/barang` | GET | Admin / Super Admin | Manajemen Inventaris Barang |
| `/admin/barang/cetak/preview` | GET | Super Admin | Halaman Live Preview Cetak & Pilih Ruangan |
| `/admin/barang/cetak/pdf` | GET | Super Admin | Download Laporan Inventaris format PDF |
| `/admin/barang/cetak/docx` | GET | Super Admin | Download Laporan Inventaris format DOCX |
| `/admin/barang/cetak/xlsx` | GET | Super Admin | Download Laporan Inventaris format XLSX |

---

## 📱 Alur Notifikasi WhatsApp (Fonnte)

1. **Pengaduan Baru (Mahasiswa → Admin/Super Admin)**:
   Saat mahasiswa mengirim pengaduan baru, notifikasi WA dikirim ke semua akun **Admin & Super Admin** yang memiliki nomor WA aktif.
2. **Penugasan Petugas (Admin/Super Admin → Petugas)**:
   Saat Admin/Super Admin memverifikasi pengaduan dan menugaskan petugas, notifikasi WA dikirim langsung ke nomor WA **Petugas** yang ditunjuk.
3. **Laporan Progres (Petugas → Admin/Super Admin)**:
   Saat Petugas memperbarui progres penanganan (beserta foto bukti), notifikasi WA dikirim ke **Admin & Super Admin** untuk verifikasi kelayakan fasilitas.

> ℹ️ *Format nomor WA di database menggunakan awalan `628xxxxxxx` (tanpa tanda `+` atau `08`).*

---

## 🖨️ Fitur Cetak & Preview Inventaris Barang

- **Akses Terbatas**: Khusus akun berpangkat `super_admin`.
- **Halaman Live Preview**: Memungkinkan pengguna memilih gedung dan mencentang beberapa ruangan (*multi-select*), melihat tampilan dokumen cetak secara langsung di browser dengan kontrol *Zoom In/Out*, lalu memilih format unduhan.
- **Standar Format Dokumen**:
  - Font: **Times New Roman**
  - Kop Surat: **STIKOM Tunas Bangsa** dengan 2 Logo Kampus (*behind text*) di posisi kiri dan kanan.
  - Detail Ruangan: Tipe Ruangan, Lokasi (Gedung & Lantai), Nama Ruangan, dan Tabel Daftar Barang.
  - Legalitas: Catatan aturan pemindahan barang + Kolom Tanda Tangan Ketua STIKOM Tunas Bangsa & Wakil Ketua 2.

---

## 🐛 Troubleshooting

**❌ Error PDF / Gambar tidak muncul pada laporan cetak**
- Pastikan ekstensi PHP GD telah diaktifkan di file `php.ini` (`extension=gd`).

**❌ Notifikasi WA tidak terkirim**
- Pastikan `app.fonnteToken` di file `.env` telah diisi dengan token aktif dari Fonnte.
- Pastikan format nomor telepon petugas/admin di database menggunakan format internasional (contoh: `628123456789`).

**❌ Sesi masih bisa diakses setelah Logout saat menekan tombol Back browser**
- Sistem telah dilengkapi guard BFCache & header anti-cache. Jika mengalami caching lokal pada browser, lakukan clear browser cache/hard refresh (`Ctrl + F5`).

---

## 📞 Kontak & Informasi

Dikembangkan untuk **STIKOM Tunas Bangsa** Pematangsiantar, Sumatera Utara.
