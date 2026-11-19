<?php

use Illuminate\Support\Facades\Schedule;

// Hapus foto presensi yang melewati masa retensi (lihat config/presensi.php).
Schedule::command('presensi:bersihkan-foto')->monthlyOn(1, '02:00');
