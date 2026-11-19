@extends('layouts.admin')

@section('judul', 'Monitoring Presensi')

@section('content')
<form method="GET" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="{{ $tanggal->toDateString() }}"></div>
        <div class="col-md-4">
            <label class="form-label">Cabang</label>
            <select name="kode_cabang" class="form-select">
                <option value="">Semua cabang</option>
                @foreach ($cabangList as $c)<option value="{{ $c->kode_cabang }}" @selected($kodeCabang === $c->kode_cabang)>{{ $c->nama_cabang }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="ti ti-search me-1"></i>Tampilkan</button></div>
    </div>
</form>

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Sudah presensi ({{ $presensi->count() }})</h3></div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter table-striped">
            <thead><tr><th>Karyawan</th><th>Masuk</th><th>Foto</th><th>Pulang</th><th>Foto</th><th>Jarak</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($presensi as $p)
                @php $menit = $p->menitTerlambat(); $kantor = $p->karyawan?->cabang?->koordinat(); $lok = \App\Support\Geo::parse($p->lokasi_in); @endphp
                <tr>
                    <td>
                        <div class="fw-bold">{{ $p->karyawan?->nama_lengkap ?? $p->nik }}</div>
                        <div class="text-secondary small">{{ $p->nik }} · {{ $p->karyawan?->departemen?->nama_dept }}</div>
                    </td>
                    <td>{{ \App\Support\Waktu::hm($p->jam_in) }}</td>
                    <td>@if ($p->foto_in)<a href="{{ route('berkas.presensi', [$p, 'in']) }}" target="_blank"><img src="{{ route('berkas.presensi', [$p, 'in']) }}" class="rounded" width="44" height="44" style="object-fit:cover" loading="lazy" alt="Foto masuk"></a>@endif</td>
                    <td>{{ \App\Support\Waktu::hm($p->jam_out) }}</td>
                    <td>@if ($p->foto_out)<a href="{{ route('berkas.presensi', [$p, 'out']) }}" target="_blank"><img src="{{ route('berkas.presensi', [$p, 'out']) }}" class="rounded" width="44" height="44" style="object-fit:cover" loading="lazy" alt="Foto pulang"></a>@endif</td>
                    <td>{{ $p->jarak_in !== null ? $p->jarak_in.' m' : '—' }}@if ($p->akurasi_in)<div class="text-secondary small">±{{ $p->akurasi_in }} m</div>@endif</td>
                    <td>
                        @if ($menit > 0)<span class="badge bg-orange-lt">Terlambat {{ $menit }} mnt</span>@else<span class="badge bg-green-lt">Tepat waktu</span>@endif
                        @if ($p->perlu_review)<span class="badge bg-red-lt" title="Akurasi GPS rendah atau koordinat berulang">Perlu review</span>@endif
                    </td>
                    <td class="text-end">
                        @if ($lok && $kantor)
                            <button type="button" class="btn btn-sm btn-outline-primary btn-peta" data-bs-toggle="modal" data-bs-target="#modalPeta"
                                data-nama="{{ $p->karyawan?->nama_lengkap }}" data-lat="{{ $lok[0] }}" data-lng="{{ $lok[1] }}"
                                data-klat="{{ $kantor[0] }}" data-klng="{{ $kantor[1] }}" data-radius="{{ $p->karyawan->cabang->radius_cabang }}">
                                <i class="ti ti-map-pin"></i> Peta
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-4">Belum ada presensi pada tanggal ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Belum presensi / tanpa keterangan ({{ $belum->count() }})</h3></div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter">
            <thead><tr><th>Karyawan</th><th>Departemen</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($belum as $x)
                <tr>
                    <td>{{ $x['karyawan']->nama_lengkap }} <span class="text-secondary small">({{ $x['karyawan']->nik }})</span></td>
                    <td>{{ $x['karyawan']->departemen?->nama_dept }}</td>
                    <td><span class="badge bg-{{ $x['baris']['status']->warna() }}-lt">{{ $x['baris']['status']->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-secondary py-3">Tidak ada.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal modal-blur fade" id="modalPeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="judulPeta">Lokasi presensi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body"><div id="peta" style="height:420px;border-radius:8px"></div>
            <div class="small text-secondary mt-2"><span class="text-primary">●</span> Lingkaran = radius kantor &nbsp; <span class="text-danger">●</span> Penanda = posisi karyawan saat presensi masuk</div>
        </div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
    let peta = null, lapisan = [];
    document.getElementById('modalPeta').addEventListener('shown.bs.modal', (e) => {
        const data = e.relatedTarget.dataset;
        document.getElementById('judulPeta').textContent = 'Lokasi presensi: ' + data.nama;
        if (!peta) {
            peta = L.map('peta');
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(peta);
        }
        lapisan.forEach(l => peta.removeLayer(l)); lapisan = [];
        const k = [+data.klat, +data.klng], p = [+data.lat, +data.lng];
        lapisan.push(L.circle(k, { color: '#174d37', fillOpacity: .15, radius: +data.radius }).addTo(peta));
        lapisan.push(L.marker(p).addTo(peta));
        peta.invalidateSize();
        peta.fitBounds(L.latLngBounds([k, p]).pad(.5), { maxZoom: 18 });
    });
</script>
@endpush
