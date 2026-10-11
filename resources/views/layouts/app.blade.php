<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'PetaPenempatan ASN')</title>

    <!-- Aset Vite: Tailwind + daisyUI (resources/css/app.css) & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Style & script khusus halaman (mis. Leaflet di dashboard) -->
    @stack('kepala')

    <!-- Style komponen Livewire -->
    @livewireStyles
</head>
<body class="bg-base-200 min-h-screen">

    <!-- ===== Navbar (daisyUI) ===== -->
    <div class="navbar bg-primary text-primary-content shadow-lg sticky top-0 z-40">
        <div class="navbar-start">
            <!-- Menu mobile: Alpine.js untuk buka/tutup -->
            <div class="dropdown" x-data="{ buka: false }">
                <button class="btn btn-ghost lg:hidden" @click="buka = !buka" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <ul x-show="buka" @click.away="buka = false"
                    class="menu menu-sm dropdown-content mt-3 z-[50] p-2 shadow bg-base-100 text-base-content rounded-box w-52">
                    <li><a href="{{ route('dashboard') }}">🗺️ Dashboard</a></li>
                    <li><a href="{{ route('guru.index') }}">👩‍🏫 Data Guru</a></li>
                    <li><a href="{{ route('sekolah.index') }}">🏫 Data Sekolah</a></li>
                    <li><a href="{{ route('usulan.index') }}">📝 Usulan</a></li>
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <li><a href="{{ route('pengguna.index') }}">👥 Kelola Pengguna</a></li>
                    @endif
                </ul>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost text-xl">🗺️ PetaPenempatan ASN</a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 font-semibold">
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('guru.index') }}" class="{{ request()->routeIs('guru.*') ? 'active' : '' }}">Data Guru</a></li>
                <li><a href="{{ route('sekolah.index') }}" class="{{ request()->routeIs('sekolah.*') ? 'active' : '' }}">Data Sekolah</a></li>
                <li><a href="{{ route('usulan.index') }}" class="{{ request()->routeIs('usulan.*') ? 'active' : '' }}">Usulan</a></li>
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <li><a href="{{ route('pengguna.index') }}" class="{{ request()->routeIs('pengguna.*') ? 'active' : '' }}">Pengguna</a></li>
                @endif
            </ul>
        </div>
        <div class="navbar-end">
            {{-- Badge peran pengguna --}}
            <span class="badge badge-accent mr-2 hidden md:inline-flex">{{ auth()->user()->labelRole() }}</span>
            <a href="{{ route('profil.show') }}" class="hidden sm:inline mr-3 text-sm font-semibold link link-hover">{{ auth()->user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
            </form>
        </div>
    </div>

    <!-- ===== Isi halaman ===== -->
    <main class="container mx-auto px-4 py-6 max-w-6xl">
        {{-- Pesan sukses (mis. setelah tambah/ubah/hapus data) --}}
        @if (session('sukses'))
            <div class="alert alert-success mb-4" x-data="{ tampil: true }" x-show="tampil">
                <span>{{ session('sukses') }}</span>
                <button class="btn btn-sm btn-ghost" @click="tampil = false">✕</button>
            </div>
        @endif

        {{-- Rincian baris gagal import (jika ada) --}}
        @if (session('gagal_import'))
            <div class="alert alert-warning mb-4" x-data="{ tampil: true }" x-show="tampil">
                <div>
                    <p class="font-semibold">Beberapa baris gagal diimport:</p>
                    <ul class="list-disc list-inside text-sm max-h-32 overflow-y-auto">
                        @foreach (session('gagal_import') as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
                <button class="btn btn-sm btn-ghost" @click="tampil = false">✕</button>
            </div>
        @endif

        @yield('konten')
    </main>

    <footer class="text-center text-sm opacity-60 pb-6">
        PetaPenempatan ASN v1.0 — dibuat dengan Laravel + Livewire + daisyUI
    </footer>

    <!-- Script khusus halaman -->
    @stack('skrip')

    <!-- Script komponen Livewire (wajib ada) -->
    @livewireScripts
</body>
</html>
