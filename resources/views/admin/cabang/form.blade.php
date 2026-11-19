@extends('layouts.admin')

@php $edit = $cabang->exists; $k = $cabang->koordinat() ?? [-3.388, 114.635]; @endphp
@section('judul', $edit ? 'Ubah Cabang' : 'Tambah Cabang')

@section('content')
<form method="POST" class="card" action="{{ $edit ? route('panel.cabang.update', $cabang) : route('panel.cabang.store') }}">
    @csrf @if ($edit) @method('PUT') @endif
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label required">Kode</label>
                @if ($edit)<input type="text" class="form-control" value="{{ $cabang->kode_cabang }}" disabled>
                @else<input type="text" name="kode_cabang" class="form-control" value="{{ old('kode_cabang') }}" maxlength="10" required>@endif</div>
            <div class="col-md-5"><label class="form-label required">Nama cabang</label><input type="text" name="nama_cabang" class="form-control" value="{{ old('nama_cabang', $cabang->nama_cabang) }}" maxlength="100" required></div>
            <div class="col-md-4"><label class="form-label required">Radius presensi (meter)</label><input type="number" id="radius" name="radius_cabang" class="form-control" min="20" max="2000" value="{{ old('radius_cabang', $cabang->radius_cabang) }}" required></div>
            <div class="col-12">
                <label class="form-label required">Koordinat (latitude,longitude)</label>
                <input type="text" id="lokasi" name="lokasi_cabang" class="form-control" value="{{ old('lokasi_cabang', $cabang->lokasi_cabang) }}" placeholder="-3.388000,114.635000" required>
                <div class="form-hint">Klik peta untuk menaruh titik kantor, atau geser penanda. Titik yang akurat menentukan keberhasilan presensi.</div>
            </div>
            <div class="col-12"><div id="peta" style="height:360px;border-radius:8px"></div></div>
        </div>
    </div>
    <div class="card-footer text-end"><a href="{{ route('panel.cabang.index') }}" class="btn btn-link">Batal</a><button class="btn btn-primary">Simpan</button></div>
</form>
@endsection

@push('scripts')
<script>
    const input = document.getElementById('lokasi'), radius = document.getElementById('radius');
    const awal = [{{ $k[0] }}, {{ $k[1] }}];
    const peta = L.map('peta').setView(awal, 17);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(peta);
    const penanda = L.marker(awal, { draggable: true }).addTo(peta);
    const lingkaran = L.circle(awal, { color: '#174d37', fillOpacity: .15, radius: +radius.value || 100 }).addTo(peta);

    function setTitik(ll) {
        penanda.setLatLng(ll); lingkaran.setLatLng(ll);
        input.value = ll.lat.toFixed(6) + ',' + ll.lng.toFixed(6);
    }
    peta.on('click', e => setTitik(e.latlng));
    penanda.on('dragend', () => setTitik(penanda.getLatLng()));
    radius.addEventListener('input', () => lingkaran.setRadius(+radius.value || 0));
    input.addEventListener('change', () => {
        const p = input.value.split(',').map(Number);
        if (p.length === 2 && p.every(n => !isNaN(n))) { const ll = L.latLng(p[0], p[1]); penanda.setLatLng(ll); lingkaran.setLatLng(ll); peta.setView(ll); }
    });
</script>
@endpush
