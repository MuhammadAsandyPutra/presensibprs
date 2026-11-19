# E-Presensi BPRS (Laravel)

Sistem presensi **GPS + foto selfie**, pengajuan **izin / sakit / cuti**, dan **KPI disiplin** untuk HR.
Dua portal terpisah dalam satu aplikasi (mengikuti struktur proyek referensi `presensibprs`):

| Portal | URL | Login | Guard | Untuk |
|---|---|---|---|---|
| Karyawan (mobile-first) | `/` | NIK + sandi | `karyawan` | Presensi, riwayat, izin/cuti, profil |
| HR & Admin (desktop) | `/panel` | Email + sandi | `user` | Monitoring, persetujuan, KPI, laporan, data master, pengaturan |

Peran panel: **hr** (semua kecuali konfigurasi sistem) dan **admin** (semua, termasuk Lokasi Kantor, Jam Kerja, Log Aktivitas).

> **Status verifikasi:** kode ditulis dan diperiksa secara statis (struktur, route, view, direktif Blade), tetapi
> **belum dijalankan** karena lingkungan penyusunnya tidak punya PHP/Composer. Jalankan `php artisan test`
> (lihat bawah) sebelum dipakai; bila ada yang gagal, perbaikannya biasanya kecil dan mudah dilacak lewat pesan tes.

---

## 1. Menjalankan di komputer (VS Code)

Prasyarat: **PHP 8.3+** (ekstensi `gd`, `mbstring`, `pdo_sqlite` atau `pdo_mysql`), **Composer 2**.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite            # Windows: type nul > database\database.sqlite
php artisan migrate
php artisan db:seed                       # master minimal + akun admin (sandi acak ditampilkan SEKALI)
php artisan db:seed --class=DemoSeeder    # OPSIONAL: karyawan & presensi contoh
php artisan serve
```

Buka `http://localhost:8000` (karyawan) dan `http://localhost:8000/panel` (HR/Admin).
Data demo: karyawan **1001–1006**, sandi **karyawan123**.
Kamera & GPS browser hanya berfungsi di `localhost` atau **HTTPS**.

Akun panel tambahan: `php artisan presensi:buat-pengguna hr@bprs.test "Nama HR" --role=hr`

Ekstensi VS Code yang disarankan: *PHP Intelephense*, *Laravel Blade Snippets*, *Laravel Extra Intellisense*.

## 2. Memakai database lama dari proyek referensi

Migrasi di proyek ini **aman untuk database yang sudah berisi tabel** (`hasTable/hasColumn`): tabel lama dibiarkan,
hanya kolom baru yang ditambahkan. **Backup database dulu**, lalu:

```bash
php artisan migrate
```

Kolom baru: `karyawan.aktif/jatah_cuti`, `jam_kerja.toleransi_menit`, `presensi.jarak_in/jarak_out/akurasi_in/perlu_review/ip_address/catatan`,
`pengajuan_izin.tgl_selesai/jumlah_hari/lampiran/catatan_hr/diputuskan_*`, `users.role` (default `hr`), tabel baru `hari_libur`, `pengaturan`, `log_aktivitas`.
Setelah itu jadikan akun utama Anda admin: `UPDATE users SET role='admin' WHERE email='...';`

Catatan kompatibilitas data lama:
* `pengajuan_izin.status` = **jenis** (1 izin, 2 sakit, **3 cuti** baru); `status_approved` = keputusan (0 menunggu, 1 setuju, 2 tolak, 3 dibatalkan).
* Foto lama di `storage/app/public/uploads/absensi` **tidak** otomatis terbaca. Foto baru disimpan privat di `storage/app/private/presensi`.
  Pindahkan berkas lama ke sana bila ingin tetap tampil (path pada kolom `foto_in/foto_out` relatif terhadap disk privat).

## 3. Peta kode (di mana mengubah apa)

```
app/
  Enums/            Status & jenis (JenisPengajuan, StatusPengajuan, StatusHarian, KategoriKpi) -> label & warna di satu tempat
  Models/           Eloquent + relasi. Tabel lama dipertahankan (karyawan, presensi, cabang, jam_kerja, ...)
  Services/         <<< ATURAN BISNIS ada di sini, bukan di controller >>>
      PresensiService   validasi lokasi/radius/jam/foto + simpan masuk/pulang
      PengajuanService  kuota izin, saldo cuti, bentrok tanggal, alur setujui/tolak/batal
      JadwalService     jam kerja karyawan per hari + hari libur
      RekapService      status per hari (hadir/terlambat/izin/sakit/cuti/alpa) -> dasar laporan, dashboard, KPI
      KpiService        perhitungan skor KPI (rumus di komentar kelas)
  Http/Controllers/
      Auth/ Karyawan/ Admin/    controller tipis per portal
      BerkasController          penyaji foto/lampiran privat dengan otorisasi
  Http/Requests/    validasi form (satu kelas per form)
  Http/Middleware/  EnsureUserRole (role:admin,hr), SecurityHeaders
  Support/          Geo (Haversine), Waktu, Csv (aman dari formula injection)
config/presensi.php default kebijakan (radius GPS, kuota izin, bobot KPI, retensi foto)
routes/web.php      semua URL, dikelompokkan per portal
resources/views/    layouts/{karyawan,admin,auth} + folder per fitur
tests/              Unit (Geo, Waktu, JamKerja, Csv) & Feature (portal, presensi, pengajuan, KPI)
```

Konvensi: *controller → Form Request → Service → Model*. Logika baru masuk ke `Services/`, bukan controller/Blade.
Tambah jenis pengajuan baru = tambah case di `Enums/JenisPengajuan` + aturan di `PengajuanService`.
Ubah rumus KPI = `KpiService::ringkas()` (bobot & penalti bisa diubah HR dari menu **Pengaturan**, tanpa deploy).

## 4. Aturan bisnis ringkas

**Presensi** (semua divalidasi di server, memakai jam server zona `Asia/Makassar`):
lokasi terbaca → akurasi GPS ≤ batas → jarak ke kantor ≤ radius cabang (Haversine) → bukan hari libur/di luar jadwal →
tidak sedang izin/cuti disetujui → masuk hanya di jendela `awal_jam_masuk`–`akhir_jam_masuk`, pulang tidak sebelum `jam_pulang` →
satu baris per karyawan per hari. Presensi **mencurigakan** (akurasi > 50 m, atau koordinat identik 3 hari berturut-turut) diberi tanda
*perlu review* untuk HR; tidak ditolak otomatis.

**Izin/sakit/cuti:** hari kerja dihitung dari jadwal karyawan dikurangi hari libur; tanggal tidak boleh bentrok; izin/sakit punya kuota
bulanan; cuti dibatasi saldo tahunan (menunggu + disetujui ikut dihitung); sakit boleh mundur N hari; karyawan bisa membatalkan selagi menunggu;
HR dapat menyetujui, menolak (alasan wajib), atau membatalkan keputusan. Semua keputusan tercatat di Log Aktivitas.

**KPI disiplin** (periode bulanan; hari mendatang & hari ini yang belum presensi tidak dihitung):
`kehadiran% = hadir / (hari kerja − izin/sakit/cuti disetujui)`, `ketepatan% = (hadir − terlambat)/hadir`,
`disiplin% = 100 − menit_terlambat×0,2 − alpa×10 − pulang_cepat×2`; skor = rata-rata berbobot (default 40/40/20). Kategori: ≥90 Sangat Baik, ≥80 Baik, ≥70 Cukup.
Ini KPI **kedisiplinan**; KPI kinerja pekerjaan (target/output) adalah modul terpisah.

## 5. Keamanan yang sudah diterapkan

Guard & portal terpisah, role admin/hr, kata sandi ter-hash, rate limit login (5/menit) & presensi, `session()->regenerate()` saat login,
CSRF di semua form/AJAX, validasi via Form Request, **foto & lampiran di disk privat** dan hanya dibuka lewat route berotorisasi (pemilik atau HR/Admin),
cek kepemilikan pada pembatalan pengajuan, CSV bebas formula injection, header keamanan (nosniff, frame, referrer, permissions-policy),
karyawan tidak dihapus (cukup dinonaktifkan), jejak audit aksi HR, retensi foto otomatis (`presensi:bersihkan-foto`, default 12 bulan).

Yang **perlu Anda lakukan** di produksi: `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, HTTPS wajib, ganti sandi admin awal, backup database terjadwal
(dan uji restore), batasi akses `storage/` dan `.env` (lihat bagian deploy), siapkan persetujuan karyawan untuk data wajah & lokasi (UU PDP).
Batas yang jujur: aplikasi web tidak dapat mendeteksi *fake GPS* sekuat aplikasi native; itulah gunanya penanda *perlu review*.

## 6. Deploy ke Hostinger (subdomain)

1. hPanel → **PHP Configuration**: pilih **PHP 8.3+**, aktifkan `gd`, `mbstring`, `pdo_mysql`, `fileinfo`.
2. Buat database MySQL, catat nama/user/sandi. Isi `.env` produksi (salin dari `.env.example`):
   `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://subdomain-anda`, `DB_CONNECTION=mysql` + kredensial, `SESSION_SECURE_COOKIE=true`.
3. Unggah proyek ke **di luar** folder web, mis. `domains/asandyschodingers.online/presensi_app/` (bukan di `public_html`).
4. Folder web subdomain (mis. `public_html/presensi`) harus berisi **isi folder `public/`**; ubah dua baris di `index.php` agar menunjuk ke aplikasi:
   ```php
   require __DIR__.'/../../presensi_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../../presensi_app/bootstrap/app.php';
   ```
   (sesuaikan jumlah `../` dengan lokasi sebenarnya). Jika hPanel mengizinkan mengatur *document root* subdomain ke `.../public`, itu cara paling bersih.
5. Via SSH: `composer install --no-dev --optimize-autoloader`, `php artisan key:generate`, `php artisan migrate --force`, `php artisan db:seed --force`,
   `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
6. Izin tulis untuk `storage/` dan `bootstrap/cache/`. Aktifkan SSL untuk subdomain.
7. Cron (hPanel → Cron Jobs, tiap menit): `php /path/ke/presensi_app/artisan schedule:run >> /dev/null 2>&1`.
8. Sebelum dipakai: login `/panel`, set **koordinat kantor yang akurat** di *Lokasi Kantor*, atur *Jam Kerja*, tambah *Hari Libur*, lalu tambah karyawan.

Aset admin (Tabler, Leaflet) dan SweetAlert/Ionicons dimuat dari CDN; ganti dengan berkas lokal bila server wajib offline.

## 7. Pengujian

```bash
php artisan test
```
Cakupan: Haversine & parsing lokasi, perhitungan terlambat/pulang cepat, pemisahan portal & role, alur presensi (radius, akurasi, jam, ganda, foto tidak valid),
pengajuan (hari kerja, saldo cuti, bentrok, kuota, persetujuan), dan perhitungan KPI. Sebelum rilis, uji manual dengan 5–10 karyawan di berbagai HP/browser.

## 8. Perbedaan penting dari proyek referensi

* Logika dipindah dari controller (query mentah `DB::table`, `echo "success|..."`) ke Service/Model/Enum; respons presensi kini JSON.
* Waktu memakai **jam server**, presensi ganda dicegah (unique + validasi), pulang tidak bisa ditimpa berulang, radius & akurasi divalidasi di server.
* Foto tidak lagi di `public/storage`; akses lewat route berotorisasi.
* Migrasi lengkap untuk semua tabel (di referensi hanya sebagian) + log aktivitas, hari libur, pengaturan.
* Fitur baru: cuti & saldo, kuota, bentrok tanggal, KPI + ekspor CSV, rekap bulanan, penanda review, role HR/Admin, jadwal per hari.
* Dependensi frontend lokal yang tidak dipakai (webcam.js, materialize, amcharts, Vite/Tailwind) dibuang; kamera memakai `getUserMedia` langsung.
* Fitur referensi yang **tidak** dibawa: papan peringkat harian di beranda karyawan (mengungkap jam tiba rekan kerja), unduhan Excel berbasis header HTML (diganti CSV), set jam kerja massal lewat modal.
