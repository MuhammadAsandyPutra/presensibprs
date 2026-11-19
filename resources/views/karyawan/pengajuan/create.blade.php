@extends('layouts.karyawan')

@section('judul', 'Ajukan Izin / Cuti')
@section('header', 'Pengajuan Baru')

@section('content')
<form method="POST" action="{{ route('izin.store') }}" enctype="multipart/form-data" class="kartu form-mobile" style="margin-top:12px">
    @csrf

    <div class="d-flex mb-3" style="gap:8px">
        <div class="flex-fill text-center p-2" style="background:var(--hijau-muda);border-radius:12px">
            <div style="font-size:20px;font-weight:700;color:var(--hijau)">{{ $sisaCuti }}</div>
            <div style="font-size:11px;color:var(--redam)">Sisa cuti (hari)</div>
        </div>
        <div class="flex-fill text-center p-2" style="background:var(--hijau-muda);border-radius:12px">
            <div style="font-size:20px;font-weight:700;color:var(--hijau)">{{ $sisaKuota }}</div>
            <div style="font-size:11px;color:var(--redam)">Sisa kuota izin/sakit bulan ini</div>
        </div>
    </div>

    <div class="form-group">
        <label for="jenis">Jenis pengajuan</label>
        <select name="jenis" id="jenis" class="form-control" required>
            @foreach ($jenis as $j)
                <option value="{{ $j->value }}" @selected((string) old('jenis', 1) === (string) $j->value)>{{ $j->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col-6 form-group">
            <label for="tgl_izin">Tanggal mulai</label>
            <input type="date" name="tgl_izin" id="tgl_izin" class="form-control" value="{{ old('tgl_izin', now()->toDateString()) }}" required>
        </div>
        <div class="col-6 form-group">
            <label for="tgl_selesai">Sampai (opsional)</label>
            <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" value="{{ old('tgl_selesai') }}">
        </div>
    </div>

    <div class="form-group">
        <label for="keterangan">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="3" class="form-control" maxlength="500" required placeholder="Tulis alasan singkat">{{ old('keterangan') }}</textarea>
    </div>

    <div class="form-group">
        <label for="lampiran">Lampiran <span id="petunjukLampiran" class="text-muted" style="font-weight:400">(opsional, JPG/PNG/PDF)</span></label>
        <input type="file" name="lampiran" id="lampiran" class="form-control" accept=".jpg,.jpeg,.png,.pdf" style="padding:6px">
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg">Kirim Pengajuan</button>
</form>
@endsection

@push('scripts')
<script>
    const jenis = document.getElementById('jenis'), petunjuk = document.getElementById('petunjukLampiran');
    const ubah = () => { petunjuk.textContent = jenis.value === '2' ? '(sangat disarankan: foto surat dokter)' : '(opsional, JPG/PNG/PDF)'; };
    jenis.addEventListener('change', ubah); ubah();
</script>
@endpush
