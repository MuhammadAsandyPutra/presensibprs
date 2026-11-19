@extends('layouts.admin')

@section('judul', 'Izin, Sakit & Cuti')

@section('content')
<form method="GET" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">Cari nama / NIK</label><input type="text" name="cari" class="form-control" value="{{ $filter['cari'] ?? '' }}"></div>
        <div class="col-md-2"><label class="form-label">Jenis</label>
            <select name="jenis" class="form-select"><option value="">Semua</option>
                @foreach ($jenisList as $j)<option value="{{ $j->value }}" @selected(isset($filter['jenis']) && (int) $filter['jenis'] === $j->value)>{{ $j->label() }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label">Status</label>
            <select name="status" class="form-select"><option value="">Semua</option>
                @foreach ($statusList as $s)<option value="{{ $s->value }}" @selected(isset($filter['status']) && (int) $filter['status'] === $s->value)>{{ $s->label() }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label">Dari</label><input type="date" name="dari" class="form-control" value="{{ $filter['dari'] ?? '' }}"></div>
        <div class="col-md-2"><label class="form-label">Sampai</label><input type="date" name="sampai" class="form-control" value="{{ $filter['sampai'] ?? '' }}"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100"><i class="ti ti-search"></i></button></div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table card-table table-vcenter">
            <thead><tr><th>Karyawan</th><th>Jenis</th><th>Tanggal</th><th>Keterangan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse ($daftar as $p)
                <tr>
                    <td><div class="fw-bold">{{ $p->karyawan?->nama_lengkap ?? $p->nik }}</div><div class="text-secondary small">{{ $p->nik }} · {{ $p->karyawan?->departemen?->nama_dept }}</div></td>
                    <td><span class="badge bg-{{ $p->jenis()->warna() }}-lt">{{ $p->jenis()->label() }}</span></td>
                    <td>{{ $p->tgl_izin->translatedFormat('d M Y') }}@if ($p->tgl_selesai && ! $p->tgl_selesai->isSameDay($p->tgl_izin)) – {{ $p->tgl_selesai->translatedFormat('d M Y') }}@endif
                        <div class="text-secondary small">{{ $p->jumlah_hari }} hari kerja</div></td>
                    <td style="max-width:280px;white-space:normal">{{ $p->keterangan }}
                        @if ($p->lampiran)<div><a href="{{ route('berkas.lampiran', $p) }}" target="_blank" rel="noopener"><i class="ti ti-paperclip"></i> Lampiran</a></div>@endif
                        @if ($p->catatan_hr)<div class="text-secondary small">Catatan HR: {{ $p->catatan_hr }}</div>@endif</td>
                    <td><span class="badge bg-{{ $p->status_approved->warna() }}-lt">{{ $p->status_approved->label() }}</span></td>
                    <td class="text-end text-nowrap">
                        @if ($p->status_approved === \App\Enums\StatusPengajuan::Menunggu)
                            <button type="button" class="btn btn-sm btn-success btn-putus" data-bs-toggle="modal" data-bs-target="#modalPutus" data-url="{{ route('panel.pengajuan.putuskan', $p) }}" data-keputusan="setuju" data-nama="{{ $p->karyawan?->nama_lengkap }}">Setujui</button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-putus" data-bs-toggle="modal" data-bs-target="#modalPutus" data-url="{{ route('panel.pengajuan.putuskan', $p) }}" data-keputusan="tolak" data-nama="{{ $p->karyawan?->nama_lengkap }}">Tolak</button>
                        @elseif (in_array($p->status_approved, [\App\Enums\StatusPengajuan::Disetujui, \App\Enums\StatusPengajuan::Ditolak], true))
                            <form method="POST" action="{{ route('panel.pengajuan.kembalikan', $p) }}" class="d-inline" onsubmit="return confirm('Kembalikan pengajuan ini ke status Menunggu?')">
                                @csrf <button class="btn btn-sm btn-outline-secondary">Batalkan keputusan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada pengajuan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $daftar->links() }}</div>
</div>

<div class="modal modal-blur fade" id="modalPutus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><form method="POST" id="formPutus" class="modal-content">
        @csrf
        <input type="hidden" name="keputusan" id="inputKeputusan">
        <div class="modal-header"><h5 class="modal-title" id="judulPutus"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <label class="form-label" id="labelCatatan">Catatan (opsional)</label>
            <textarea name="catatan" id="inputCatatan" class="form-control" rows="3" maxlength="255"></textarea>
        </div>
        <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="tombolPutus">Simpan</button></div>
    </form></div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('modalPutus').addEventListener('show.bs.modal', (e) => {
        const d = e.relatedTarget.dataset, setuju = d.keputusan === 'setuju';
        document.getElementById('formPutus').action = d.url;
        document.getElementById('inputKeputusan').value = d.keputusan;
        document.getElementById('judulPutus').textContent = (setuju ? 'Setujui' : 'Tolak') + ' pengajuan ' + d.nama;
        document.getElementById('labelCatatan').textContent = setuju ? 'Catatan (opsional)' : 'Alasan penolakan (wajib)';
        document.getElementById('inputCatatan').required = !setuju;
        document.getElementById('inputCatatan').value = '';
        const t = document.getElementById('tombolPutus'); t.textContent = setuju ? 'Setujui' : 'Tolak'; t.className = 'btn ' + (setuju ? 'btn-success' : 'btn-danger');
    });
</script>
@endpush
