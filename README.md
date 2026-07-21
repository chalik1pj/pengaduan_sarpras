# 📋 Sistem Pengaduan Fasilitas Kampus
### STIKOM Tunas Bangsa — Pematangsiantar

Aplikasi web untuk pelaporan dan pengelolaan pengaduan fasilitas sarana prasarana kampus, dibangun dengan **CodeIgniter 4** dan desain **Modern Dark UI**.

---

## ✨ Fitur Utama

| Fitur | Keterangan |
|---|---|
| 🎓 **Login Mahasiswa** | Login via NIM atau Email |
| 📝 **Buat Pengaduan** | Form dengan cascading dropdown Gedung → Ruangan → Barang |
| 📸 **Upload Foto Bukti** | Maks. 3 foto, masing-masing maks. 1MB |
| 📱 **Notifikasi WhatsApp** | Petugas dapat notif WA otomatis via Fonnte API |
| 📊 **Dashboard Admin** | Statistik, grafik, dan manajemen pengaduan |
| 🏢 **Manajemen Aset** | CRUD Gedung, Ruangan, Barang |
| 👥 **Manajemen Pengguna** | CRUD Petugas (super_admin only) + status mahasiswa |
| 📈 **Tracking Status** | Progress bar + timeline log perubahan status |
| 📱 **Responsive** | Tampilan mobile-friendly |

---

## 🛠️ Requirements

- PHP 8.0+
- MySQL / MariaDB 5.7+
- XAMPP (atau server web lainnya)
- Composer (sudah terinstall di `C:\xampp\php\`)
- Akun Fonnte (untuk notifikasi WhatsApp)

---

## 🚀 Instalasi

### Langkah 1 — Import Database

1. Buka **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Buat database baru bernama `db_pengaduan_fasilitas`
3. Import file SQL utama:
   ```
   database/migrations/sistem_pengaduan_fasilitas_kampus.sql
   ```
4. Import file SQL tambahan (menambah kolom & trigger):
   ```
   database/migrations/additional.sql
   ```

### Langkah 2 — Konfigurasi `.env`

File `.env` sudah ada di root project. Pastikan konfigurasi database sesuai:

```env
database.default.hostname = localhost
database.default.database = db_pengaduan_fasilitas
database.default.username = root
database.default.password = 
```

Untuk fitur **notifikasi WhatsApp**, isi token Fonnte:
```env
app.fonnteToken = 'TOKEN_FONNTE_ANDA'
```

> 📖 Lihat **[README-FONNTE.md](README-FONNTE.md)** untuk panduan mendapatkan token.

### Langkah 3 — Akses Aplikasi

Buka browser dan kunjungi:
```
http://localhost/pengaduan_sarpras/public/
```

---

## 🔐 Akun Default

### Admin
| Field | Value |
|---|---|
| Email | `admin@tunasbangsa.ac.id` |
| Password | `password` |
| Level | `super_admin` |

> ⚠️ **SEGERA GANTI PASSWORD** setelah login pertama melalui menu Petugas!

### Mahasiswa
Mahasiswa dapat **mendaftar sendiri** melalui halaman Register, atau admin membuat akun baru.

---

## 📁 Struktur Folder Penting

```
pengaduan_sarpras/
├── app/
│   ├── Config/
│   │   ├── Routes.php          ← Semua routing
│   │   └── Filters.php         ← Auth filter terdaftar
│   ├── Controllers/
│   │   ├── Auth/               ← Login, Register, Logout
│   │   ├── Mahasiswa/          ← Dashboard, Pengaduan, Profil
│   │   ├── Admin/              ← Dashboard, Pengaduan, CRUD
│   │   └── Api/                ← Cascading dropdown API
│   ├── Filters/
│   │   ├── AuthFilter.php      ← Guard mahasiswa
│   │   └── AdminFilter.php     ← Guard admin/petugas
│   ├── Helpers/
│   │   └── whatsapp_helper.php ← Fonnte WA notification
│   ├── Models/                 ← Semua Model database
│   └── Views/
│       ├── layouts/            ← Template auth, mahasiswa, admin
│       ├── auth/               ← Login, Register, Login Admin
│       ├── mahasiswa/          ← Views mahasiswa
│       └── admin/              ← Views admin
├── database/migrations/
│   ├── sistem_pengaduan_fasilitas_kampus.sql  ← SQL utama
│   └── additional.sql          ← SQL tambahan (foto, WA, trigger)
├── public/
│   └── assets/
│       ├── css/style.css       ← Design system
│       └── js/
│           ├── main.js         ← Sidebar, dropdown, upload
│           └── admin.js        ← Chart.js, modal helpers
└── writable/
    └── uploads/                ← Folder upload (auto-created)
        ├── pengaduan/{id}/     ← Foto bukti pengaduan
        └── profil/             ← Foto profil mahasiswa
```

---

## 🗺️ Routing Utama

| URL | Keterangan |
|---|---|
| `/auth/login` | Login mahasiswa |
| `/auth/register` | Registrasi mahasiswa |
| `/admin/auth/login` | Login admin/petugas |
| `/mahasiswa/dashboard` | Dashboard mahasiswa |
| `/mahasiswa/pengaduan/buat` | Form buat pengaduan |
| `/admin/dashboard` | Dashboard admin |
| `/admin/pengaduan` | Daftar semua pengaduan |
| `/api/ruangan/{id}` | AJAX: ruangan by gedung |
| `/api/barang/{id}` | AJAX: barang by ruangan |

---

## 📱 Notifikasi WhatsApp (Fonnte)

Notifikasi WhatsApp otomatis dikirim ke **semua petugas aktif yang memiliki nomor WA** saat:
- Ada pengaduan baru masuk dari mahasiswa

Format nomor WA petugas di database: `628xxxxxxx` (tanpa tanda `+`)

---

## 🐛 Troubleshooting

**❌ Error 404 setelah instalasi**
- Pastikan `mod_rewrite` Apache aktif di XAMPP
- Cek file `.htaccess` di folder `public/`

**❌ Database connection failed**
- Pastikan MySQL XAMPP sudah running
- Cek nama database, username, password di `.env`

**❌ Foto tidak bisa diupload**
- Pastikan folder `writable/uploads/` ada dan writable
- Cek `upload_max_filesize` dan `post_max_size` di `php.ini` (min 5M)

**❌ Notifikasi WA tidak terkirim**
- Pastikan token Fonnte sudah diisi di `.env`
- Pastikan nomor WA petugas format `628xxx` (bukan `08xxx`)
- Cek log error CI4 di `writable/logs/`

---

## 📞 Kontak

Dikembangkan untuk **STIKOM Tunas Bangsa** — Pematangsiantar, Sumatera Utara.
