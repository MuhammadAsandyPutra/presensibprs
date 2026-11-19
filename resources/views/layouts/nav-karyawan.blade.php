{{-- Menu bawah portal karyawan. Tombol kamera di tengah = presensi. --}}
<div class="appBottomMenu">
    <a href="{{ route('dashboard') }}" class="item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <div class="col"><ion-icon name="home-outline"></ion-icon><strong>Beranda</strong></div>
    </a>
    <a href="{{ route('histori') }}" class="item {{ request()->routeIs('histori') ? 'active' : '' }}">
        <div class="col"><ion-icon name="receipt-outline"></ion-icon><strong>Riwayat</strong></div>
    </a>
    <a href="{{ route('presensi.create') }}" class="item">
        <div class="col"><div class="action-button large"><ion-icon name="camera"></ion-icon></div></div>
    </a>
    <a href="{{ route('izin.index') }}" class="item {{ request()->routeIs('izin.*') ? 'active' : '' }}">
        <div class="col"><ion-icon name="calendar-outline"></ion-icon><strong>Izin/Cuti</strong></div>
    </a>
    <a href="{{ route('profil.edit') }}" class="item {{ request()->routeIs('profil.*') ? 'active' : '' }}">
        <div class="col"><ion-icon name="person-outline"></ion-icon><strong>Profil</strong></div>
    </a>
</div>
