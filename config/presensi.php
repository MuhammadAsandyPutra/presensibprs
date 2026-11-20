<?php

/*
|--------------------------------------------------------------------------
| Pengaturan Aplikasi Presensi
|--------------------------------------------------------------------------
| Nilai di sini adalah DEFAULT. Sebagian nilai (bobot KPI, kuota izin, dsb.)
| dapat diubah HR dari menu "Pengaturan" dan disimpan di tabel `pengaturan`;
| nilai di tabel akan menimpa nilai default ini. Akses lewat
| App\Models\Pengaturan::ambil('kpi.bobot_kehadiran').
*/

return [
    // Nama hari (ISO: 1 = Senin ... 7 = Minggu). Dipakai pada tabel konfigurasi_jamkerja.
    'hari' => [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'],

    // Hari kerja bawaan bagi karyawan yang belum diatur jadwalnya (Senin-Jumat).
    'hari_kerja_default' => [1, 2, 3, 4, 5],

    'gps' => [
        'akurasi_maks'   => 100, // meter; presensi ditolak bila akurasi GPS lebih buruk dari ini
        'review_akurasi' => 50,  // meter; di atas ini presensi diberi tanda "perlu review" HR
    ],

    'foto' => [
        'disk'          => 'private', // disk PRIVAT (storage/app/private); jangan pakai disk 'public'
        'maks_kb'       => 1500,
        'retensi_bulan' => 12,      // foto presensi lebih tua dari ini dihapus oleh presensi:bersihkan-foto
    ],

    'izin' => [
        'kuota_bulanan'      => 3, // jumlah pengajuan izin/sakit per bulan (cuti tidak dihitung)
        'sakit_mundur_hari'  => 7, // sakit boleh diajukan mundur maksimal sekian hari
        'lampiran_maks_kb'   => 2048,
    ],

    'cuti' => [
        'jatah_tahunan' => 12,
    ],

    'kpi' => [
        'bobot_kehadiran'      => 40,
        'bobot_ketepatan'      => 40,
        'bobot_disiplin'       => 20,
        'penalti_per_menit'    => 0.2, // poin disiplin berkurang per menit terlambat
        'penalti_alpa'         => 10,  // poin disiplin berkurang per hari tanpa keterangan
        'penalti_pulang_cepat' => 2,   // poin disiplin berkurang per kejadian pulang cepat
        'batas_sangat_baik'    => 90,
        'batas_baik'           => 80,
        'batas_cukup'          => 70,
    ],
];
