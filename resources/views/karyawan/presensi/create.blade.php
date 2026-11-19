@extends('layouts.karyawan')

@section('judul', 'Presensi')
@section('header', $presensi ? 'Presensi Pulang' : 'Presensi Masuk')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
@php
    $kantor = $cabang?->koordinat();
    $selesai = $presensi && $presensi->jam_out;
    $bisa = $kantor && $jamKerja && ! $selesai;
@endphp

<div class="px-3 pt-2">
    @if (! $cabang || ! $kantor)
        <div class="alert alert-danger">Lokasi kantor Anda belum diatur. Hubungi HR.</div>
    @elseif (! $jamKerja)
        <div class="alert alert-warning">Hari ini bukan hari kerja Anda (libur / di luar jadwal).</div>
    @elseif ($selesai)
        <div class="alert alert-success">Presensi hari ini sudah lengkap: masuk {{ \App\Support\Waktu::hm($presensi->jam_in) }}, pulang {{ \App\Support\Waktu::hm($presensi->jam_out) }}.</div>
    @endif

    @if ($bisa)
        <div class="small text-muted mb-2">
            {{ $jamKerja->nama_jam_kerja }} · masuk {{ \App\Support\Waktu::hm($jamKerja->jam_masuk) }}
            (presensi {{ \App\Support\Waktu::hm($jamKerja->awal_jam_masuk) }}–{{ \App\Support\Waktu::hm($jamKerja->akhir_jam_masuk) }}) ·
            pulang {{ \App\Support\Waktu::hm($jamKerja->jam_pulang) }}
        </div>

        <div class="video-selfie">
            <video id="video" autoplay playsinline muted></video>
            <div class="info"><span id="jam">--:--:--</span><br>{{ now()->translatedFormat('d M Y') }}</div>
        </div>

        <div id="statusGps" class="status-gps cari"><ion-icon name="locate-outline"></ion-icon><span id="teksGps">Mencari lokasi Anda…</span></div>
        <div id="peta"></div>

        <button id="tombol" type="button" class="btn btn-primary btn-block btn-lg mt-3" disabled>
            <ion-icon name="camera-outline"></ion-icon>
            <span id="teksTombol">{{ $presensi ? 'Presensi Pulang' : 'Presensi Masuk' }}</span>
        </button>
        <p class="text-center text-muted mt-2" style="font-size:12px">Foto selfie diambil langsung dari kamera. Pastikan wajah terlihat jelas.</p>
    @endif
</div>
@endsection

@if ($bisa)
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const KANTOR = { lat: {{ $kantor[0] }}, lng: {{ $kantor[1] }}, radius: {{ (int) $cabang->radius_cabang }} };
    const AKURASI_MAKS = {{ (int) $akurasiMaks }};
    const URL_SIMPAN = @json(route('presensi.store'));
    const URL_BERANDA = @json(route('dashboard'));
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    const video = document.getElementById('video');
    const tombol = document.getElementById('tombol');
    const kotakGps = document.getElementById('statusGps');
    const teksGps = document.getElementById('teksGps');
    let posisi = null, kameraSiap = false, mengirim = false;

    // Jam digital
    setInterval(() => { document.getElementById('jam').textContent = new Date().toLocaleTimeString('id-ID', { hour12: false }); }, 1000);

    // Peta (hanya tampilan; validasi jarak sebenarnya dilakukan di server)
    const peta = L.map('peta', { zoomControl: false }).setView([KANTOR.lat, KANTOR.lng], 17);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(peta);
    L.circle([KANTOR.lat, KANTOR.lng], { color: '#174d37', fillColor: '#174d37', fillOpacity: .2, radius: KANTOR.radius }).addTo(peta);
    let penanda = null;

    function jarak(lat1, lng1, lat2, lng2) {
        const R = 6371000, r = Math.PI / 180, dLat = (lat2 - lat1) * r, dLng = (lng2 - lng1) * r;
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * r) * Math.cos(lat2 * r) * Math.sin(dLng / 2) ** 2;
        return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    }

    function setStatus(kelas, ikon, teks) {
        kotakGps.className = 'status-gps ' + kelas;
        kotakGps.querySelector('ion-icon').setAttribute('name', ikon);
        teksGps.textContent = teks;
    }

    function perbarui() {
        tombol.disabled = !(posisi && kameraSiap && posisi.dalam && posisi.akurat) || mengirim;
    }

    function padaPosisi(p) {
        const d = jarak(p.coords.latitude, p.coords.longitude, KANTOR.lat, KANTOR.lng);
        posisi = { lat: p.coords.latitude, lng: p.coords.longitude, akurasi: p.coords.accuracy, dalam: d <= KANTOR.radius, akurat: p.coords.accuracy <= AKURASI_MAKS };

        if (!penanda) { penanda = L.marker([posisi.lat, posisi.lng]).addTo(peta); } else { penanda.setLatLng([posisi.lat, posisi.lng]); }
        peta.fitBounds(L.latLngBounds([[posisi.lat, posisi.lng], [KANTOR.lat, KANTOR.lng]]).pad(.4), { maxZoom: 18 });

        if (!posisi.akurat) setStatus('cari', 'warning-outline', 'Akurasi GPS ' + Math.round(posisi.akurasi) + ' m (maks ' + AKURASI_MAKS + ' m). Pindah ke area terbuka…');
        else if (!posisi.dalam) setStatus('salah', 'close-circle-outline', 'Anda ' + d + ' m dari kantor. Radius maksimal ' + KANTOR.radius + ' m.');
        else setStatus('ok', 'checkmark-circle-outline', 'Di dalam area kantor (' + d + ' m, akurasi ' + Math.round(posisi.akurasi) + ' m).');
        perbarui();
    }

    function galatGps(e) {
        const pesan = e.code === 1 ? 'Izin lokasi ditolak. Aktifkan izin lokasi untuk situs ini di pengaturan browser.' : 'Lokasi tidak dapat dibaca. Pastikan GPS aktif.';
        setStatus('salah', 'close-circle-outline', pesan);
    }

    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(padaPosisi, galatGps, { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 });
    } else {
        setStatus('salah', 'close-circle-outline', 'Perangkat/browser tidak mendukung GPS.');
    }

    // Kamera depan
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 960 } }, audio: false })
            .then(s => { video.srcObject = s; video.onloadedmetadata = () => { kameraSiap = true; perbarui(); }; window.addEventListener('pagehide', () => s.getTracks().forEach(t => t.stop())); })
            .catch(() => Swal.fire({ icon: 'error', title: 'Kamera tidak bisa diakses', text: 'Izinkan akses kamera untuk situs ini lalu muat ulang halaman.' }));
    } else {
        Swal.fire({ icon: 'error', title: 'Kamera tidak didukung', text: 'Gunakan browser terbaru dan buka lewat HTTPS.' });
    }

    function ambilFoto() {
        const lebar = 480, tinggi = Math.round(lebar * video.videoHeight / video.videoWidth);
        const kanvas = document.createElement('canvas');
        kanvas.width = lebar; kanvas.height = tinggi;
        kanvas.getContext('2d').drawImage(video, 0, 0, lebar, tinggi);
        return kanvas.toDataURL('image/jpeg', 0.8);
    }

    tombol.addEventListener('click', async () => {
        if (!posisi || mengirim) return;
        mengirim = true; perbarui();
        document.getElementById('teksTombol').textContent = 'Mengirim…';

        try {
            const res = await fetch(URL_SIMPAN, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ lokasi: posisi.lat + ',' + posisi.lng, akurasi: posisi.akurasi, foto: ambilFoto() }),
            });
            const json = await res.json().catch(() => ({}));

            if (res.ok && json.ok) {
                await Swal.fire({ icon: 'success', title: json.jenis === 'masuk' ? 'Presensi Masuk Berhasil' : 'Presensi Pulang Berhasil', text: json.pesan, timer: 2500, showConfirmButton: false });
                location.href = URL_BERANDA;
                return;
            }
            Swal.fire({ icon: 'error', title: 'Presensi gagal', text: json.pesan || json.message || 'Terjadi kesalahan. Coba lagi.' });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Tidak ada koneksi', text: 'Periksa jaringan Anda lalu coba lagi.' });
        }
        mengirim = false;
        document.getElementById('teksTombol').textContent = @json($presensi ? 'Presensi Pulang' : 'Presensi Masuk');
        perbarui();
    });
})();
</script>
@endpush
@endif
