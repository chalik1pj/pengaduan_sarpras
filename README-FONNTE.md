# 📱 Panduan Mendapatkan Token Fonnte untuk Notifikasi WhatsApp

Dokumen ini menjelaskan langkah-langkah mendapatkan **API Token Fonnte** untuk mengaktifkan fitur notifikasi WhatsApp di Sistem Pengaduan Fasilitas Kampus.

---

## Apa itu Fonnte?

[Fonnte](https://fonnte.com) adalah layanan **WhatsApp API gateway** berbasis Indonesia yang memungkinkan pengiriman pesan WhatsApp secara programatik menggunakan nomor WA pribadi atau bisnis Anda — tanpa biaya WhatsApp Business API resmi.

---

## 📋 Langkah-Langkah Mendapatkan Token

### Step 1 — Daftar / Login di Fonnte

1. Buka browser, kunjungi: **https://fonnte.com**
2. Klik tombol **"Daftar"** atau **"Register"**
3. Isi form pendaftaran dengan email dan password Anda
4. Verifikasi email jika diperlukan
5. Login ke dashboard Fonnte

---

### Step 2 — Tambahkan Device (Nomor WhatsApp)

1. Di dashboard Fonnte, klik menu **"Device"** atau **"Perangkat"**
2. Klik tombol **"Tambah Device"** / **"Add Device"**
3. Isi nama device (misalnya: `Admin Sarpras STIKOM`)
4. Klik **"Save"** / **"Simpan"**

---

### Step 3 — Connect WhatsApp (Scan QR Code)

1. Setelah device ditambahkan, klik tombol **"Connect"** / **"Hubungkan"**
2. Akan muncul **QR Code** di layar
3. Buka WhatsApp di HP admin/petugas:
   - Tap ⋮ (menu) → **Perangkat Tertaut** → **Tautkan Perangkat**
   - Arahkan kamera HP ke QR Code di layar
4. Tunggu hingga status berubah menjadi **"Connected"** / **"Terhubung"**

> ⚠️ **Penting:** HP yang digunakan harus tetap menyala dan terhubung internet agar pesan dapat terkirim.

---

### Step 4 — Salin Token API

1. Setelah device terhubung, kembali ke halaman **Device**
2. Klik device yang baru saja Anda buat
3. Cari bagian **"Token"** atau **"API Token"**
4. Klik tombol **"Salin"** atau **"Copy Token"**
5. Token berupa string panjang seperti:
   ```
   abcdef1234567890abcdef1234567890abcdef12
   ```

---

### Step 5 — Pasang Token di Aplikasi

1. Buka file **`.env`** di root project:
   ```
   c:\xampp\htdocs\pengaduan_sarpras\.env
   ```

2. Cari baris:
   ```env
   app.fonnteToken = ''
   ```

3. Ganti dengan token Anda:
   ```env
   app.fonnteToken = 'TOKEN_YANG_ANDA_COPY_DARI_FONNTE'
   ```

4. Simpan file `.env`

5. Pastikan nomor WA petugas di database sudah terisi dengan format:
   - ✅ **Benar:** `6281234567890` (diawali 62, tanpa +)
   - ❌ **Salah:** `081234567890` atau `+6281234567890`

---

### Step 6 — Tambahkan Nomor WA Petugas

1. Login sebagai **super_admin** di panel admin
2. Buka menu **Petugas**
3. Edit data petugas yang ingin menerima notifikasi
4. Isi kolom **No. WhatsApp** dengan format `628xxxxxxx`
5. Klik **Update**

Sistem akan otomatis mengirim notifikasi WA ke semua petugas aktif yang memiliki nomor WA setiap kali ada pengaduan baru masuk.

---

## 🧪 Cara Test Notifikasi

Setelah token dipasang, lakukan test:

1. Login sebagai **mahasiswa** (bisa buat akun baru via Register)
2. Buat pengaduan baru dengan mengisi semua field
3. Klik **"Kirim Pengaduan"**
4. Cek WhatsApp petugas — notifikasi harus masuk dalam beberapa detik

---

## ❓ FAQ

**Q: Apakah Fonnte gratis?**
A: Fonnte menyediakan paket gratis dengan kuota pesan terbatas. Untuk penggunaan lebih banyak, tersedia paket berbayar.

**Q: Apakah HP harus terus menyala?**
A: Ya, HP yang di-scan QR Code-nya harus tetap aktif dan terhubung internet agar pesan bisa terkirim.

**Q: Berapa lama token berlaku?**
A: Token berlaku selama device tetap terhubung. Jika HP di-logout dari WhatsApp Web, perlu scan ulang QR Code (token tetap sama, device perlu reconnect).

**Q: Apakah bisa menggunakan lebih dari 1 nomor?**
A: Ya, bisa menambahkan beberapa device di Fonnte, namun untuk sistem ini cukup 1 nomor sebagai pengirim.

**Q: Notifikasi tidak terkirim, kenapa?**
A: Kemungkinan penyebab:
- Token kosong atau salah di `.env`
- Device Fonnte terputus (HP mati/tidak ada internet)
- Nomor WA petugas format salah
- Cek log error di `writable/logs/log-YYYY-MM-DD.log`

---

## 🔗 Link Berguna

- Dashboard Fonnte: https://fonnte.com
- Dokumentasi API Fonnte: https://fonnte.com/docs
- Paket Harga Fonnte: https://fonnte.com/pricing
