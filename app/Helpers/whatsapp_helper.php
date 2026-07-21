<?php

/**
 * WhatsApp Helper — Fonnte API
 * Kirim notifikasi WA ke petugas saat ada pengaduan baru
 */
if (!function_exists('kirim_wa_fonnte')) {
    /**
     * Kirim pesan WhatsApp via Fonnte API
     *
     * @param  string $target  Nomor HP format 62xxxx (tanpa +)
     * @param  string $message Isi pesan (plain text / WhatsApp formatting)
     * @param  string $token   Fonnte API token
     * @return array           Response dari Fonnte
     */
    function kirim_wa_fonnte(string $target, string $message, string $token = ''): array
    {
        if (empty($token)) {
            $token = env('app.fonnteToken', '');
        }

        if (empty($token) || empty($target)) {
            return ['status' => false, 'reason' => 'Token atau nomor target kosong'];
        }

        // Bersihkan nomor: hilangkan karakter non-digit, pastikan diawali 62
        $target = preg_replace('/\D/', '', $target);
        if (str_starts_with($target, '0')) {
            $target = '62' . substr($target, 1);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'target'  => $target,
                'message' => $message,
            ]),
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $token,
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL error: ' . $err];
        }

        return json_decode($response, true) ?? ['status' => false, 'reason' => 'Invalid response'];
    }
}

if (!function_exists('notifikasi_pengaduan_baru')) {
    /**
     * Kirim notifikasi WA ke semua petugas aktif yang punya no_wa
     * saat ada pengaduan baru masuk.
     *
     * @param  array  $pengaduan Data pengaduan (minimal: kode, judul, nama_mahasiswa, gedung, ruangan, prioritas)
     * @param  array  $petugas   Array data petugas dari DB
     * @param  string $token     Fonnte token
     */
    function notifikasi_pengaduan_baru(array $pengaduan, array $petugas, string $token = ''): void
    {
        if (empty($petugas)) {
            return;
        }

        $prioritasLabel = match ($pengaduan['prioritas'] ?? 'sedang') {
            'tinggi' => '🔴 TINGGI',
            'sedang' => '🟡 SEDANG',
            'rendah' => '🟢 RENDAH',
            default  => '🟡 SEDANG',
        };

        $adminUrl = base_url('admin/pengaduan/' . ($pengaduan['id_pengaduan'] ?? ''));

        $pesan = <<<MSG
🔔 *PENGADUAN BARU MASUK*
Sistem Pengaduan Fasilitas - STIKOM Tunas Bangsa

📋 *Kode:* {$pengaduan['kode_pengaduan']}
👤 *Mahasiswa:* {$pengaduan['nama_mahasiswa']} ({$pengaduan['nim']})
📌 *Judul:* {$pengaduan['judul']}
🏢 *Lokasi:* {$pengaduan['nama_gedung']} - {$pengaduan['nama_ruangan']}
🏷️ *Kategori:* {$pengaduan['nama_kategori']}
⚡ *Prioritas:* {$prioritasLabel}
🕐 *Waktu:* {$pengaduan['tanggal_pengaduan']}

Silakan tindak lanjuti di dashboard admin:
{$adminUrl}
MSG;

        foreach ($petugas as $p) {
            if (!empty($p['no_wa'])) {
                kirim_wa_fonnte($p['no_wa'], $pesan, $token);
            }
        }
    }
}

if (!function_exists('notifikasi_petugas_ditugaskan')) {
    /**
     * Kirim notifikasi WA ke petugas saat admin menugaskan pengaduan
     *
     * @param  array  $pengaduan Data pengaduan
     * @param  array  $petugas   Data petugas yang ditugaskan
     * @param  string $token     Fonnte token
     */
    function notifikasi_petugas_ditugaskan(array $pengaduan, array $petugas, string $token = ''): void
    {
        if (empty($petugas['no_wa'])) {
            return;
        }

        $prioritasLabel = match ($pengaduan['prioritas'] ?? 'sedang') {
            'tinggi' => '🔴 TINGGI',
            'sedang' => '🟡 SEDANG',
            'rendah' => '🟢 RENDAH',
            default  => '🟡 SEDANG',
        };

        $petugasUrl = base_url('petugas/pengaduan/' . ($pengaduan['id_pengaduan'] ?? ''));

        $pesan = <<<MSG
🔔 *PENUGASAN PENGADUAN*
Sistem Pengaduan Fasilitas - STIKOM Tunas Bangsa

Anda telah ditugaskan untuk menangani pengaduan berikut:

📋 *Kode:* {$pengaduan['kode_pengaduan']}
📌 *Judul:* {$pengaduan['judul']}
🏢 *Lokasi:* {$pengaduan['nama_gedung']} - {$pengaduan['nama_ruangan']}
🏷️ *Kategori:* {$pengaduan['nama_kategori']}
⚡ *Prioritas:* {$prioritasLabel}
👤 *Pelapor:* {$pengaduan['nama_mahasiswa']}

Silakan tindak lanjuti dan upload laporan progres penanganan melalui:
{$petugasUrl}
MSG;

        kirim_wa_fonnte($petugas['no_wa'], $pesan, $token);
    }
}

if (!function_exists('notifikasi_laporan_petugas')) {
    /**
     * Kirim notifikasi WA ke semua admin & super_admin saat petugas upload laporan progres
     *
     * @param  array  $laporan     Data laporan petugas
     * @param  array  $adminList   Array data admin/super_admin
     * @param  string $token       Fonnte token
     */
    function notifikasi_laporan_petugas(array $laporan, array $adminList, string $token = ''): void
    {
        if (empty($adminList)) {
            return;
        }

        $adminUrl = base_url('admin/pengaduan/' . ($laporan['id_pengaduan'] ?? ''));

        $pesan = <<<MSG
📢 *LAPORAN PROGRES PETUGAS*
Sistem Pengaduan Fasilitas - STIKOM Tunas Bangsa

Petugas telah mengunggah laporan progres penanganan:

📋 *Pengaduan:* {$laporan['kode_pengaduan']}
📌 *Judul Laporan:* {$laporan['judul_laporan']}
👷 *Petugas:* {$laporan['nama_petugas']}
🔧 *Status:* {$laporan['status_laporan']}
🕐 *Waktu:* {$laporan['created_at']}

Silakan verifikasi laporan di dashboard admin:
{$adminUrl}
MSG;

        foreach ($adminList as $admin) {
            if (!empty($admin['no_wa'])) {
                kirim_wa_fonnte($admin['no_wa'], $pesan, $token);
            }
        }
    }
}

if (!function_exists('notifikasi_inspeksi_fasilitas')) {
    /**
     * Kirim notifikasi WA ke semua admin & super_admin saat petugas membuat laporan inspeksi fasilitas
     *
     * @param  array  $inspeksi  Data inspeksi
     * @param  array  $adminList Array data admin/super_admin
     * @param  string $token     Fonnte token
     */
    function notifikasi_inspeksi_fasilitas(array $inspeksi, array $adminList, string $token = ''): void
    {
        if (empty($adminList)) {
            return;
        }

        $kondisiLabel = match ($inspeksi['kondisi_temuan'] ?? 'baik') {
            'baik'            => '✅ Baik',
            'perlu_perbaikan' => '⚠️ Perlu Perbaikan',
            'rusak_berat'     => '🔴 Rusak Berat',
            default           => '⚠️ Perlu Perbaikan',
        };

        $adminUrl = base_url('admin/inspeksi/' . ($inspeksi['id_inspeksi'] ?? ''));

        $pesan = <<<MSG
🔍 *LAPORAN INSPEKSI FASILITAS*
Sistem Pengaduan Fasilitas - STIKOM Tunas Bangsa

Petugas telah melaporkan hasil inspeksi fasilitas:

📌 *Judul:* {$inspeksi['judul_inspeksi']}
🏢 *Lokasi:* {$inspeksi['nama_gedung']} - {$inspeksi['nama_ruangan']}
📦 *Barang:* {$inspeksi['nama_barang']}
🔎 *Kondisi:* {$kondisiLabel}
👷 *Petugas:* {$inspeksi['nama_petugas']}
🕐 *Waktu:* {$inspeksi['created_at']}

Silakan verifikasi dan tindak lanjuti di dashboard admin:
{$adminUrl}
MSG;

        foreach ($adminList as $admin) {
            if (!empty($admin['no_wa'])) {
                kirim_wa_fonnte($admin['no_wa'], $pesan, $token);
            }
        }
    }
}
