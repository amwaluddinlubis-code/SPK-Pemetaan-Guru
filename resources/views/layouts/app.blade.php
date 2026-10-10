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

    <!-- ===== Layout Sidebar (daisyUI drawer) ===== -->
    <div class="drawer lg:drawer-open">
        <input id="menu-sidebar" type="checkbox" class="drawer-toggle" />

        <!-- ===== Konten utama ===== -->
        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Bilah atas: hanya tampil di mobile/tablet -->
            <div class="navbar bg-primary text-primary-content shadow-lg lg:hidden sticky top-0 z-30">
                <div class="navbar-start">
                    <label for="menu-sidebar" class="btn btn-ghost" aria-label="Buka menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </label>
                </div>
                <div class="navbar-center">
                    <span class="text-lg font-bold">🗺️ PetaPenempatan ASN</span>
                </div>
                <div class="navbar-end">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
                    </form>
                </div>
            </div>

            <main class="container mx-auto px-4 py-6 max-w-6xl flex-1 w-full">
                {{-- Pesan sukses (mis. setelah tambah/ubah/hapus data) --}}
                @if (session('sukses'))
                    <div class="alert alert-success mb-4" x-data="{ tampil: true }" x-show="tampil">
                        <span>{{ session('sukses') }}</span>
                        <button class="btn btn-sm btn-ghost" @click="tampil = false">✕</button>
                    </div>
                @endif

                @yield('konten')
            </main>

            <footer class="text-center text-sm opacity-60 pb-6">
                PetaPenempatan ASN v1.0 — dibuat dengan Laravel + Livewire + daisyUI
            </footer>
        </div>

        <!-- ===== Sidebar ===== -->
        <div class="drawer-side z-40">
            <label for="menu-sidebar" aria-label="Tutup menu" class="drawer-overlay"></label>
            <aside class="w-64 min-h-screen bg-primary text-primary-content flex flex-col">
                <!-- Brand -->
                <div class="p-4 border-b border-primary-content/20">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold">🗺️ PetaPenempatan ASN</a>
                    <p class="text-xs opacity-70 mt-1">Sistem Informasi Penempatan Guru</p>
                </div>

                <!-- Menu navigasi -->
                <ul class="menu p-4 flex-1 w-full font-semibold">
                    <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">🗺️ Dashboard</a></li>
                    @if (auth()->user()->hasRole('pemda', 'kadis'))
                        <li><a href="{{ route('pemda.dashboard') }}" class="{{ request()->routeIs('pemda.*') ? 'active' : '' }}">🏛️ Eksekutif</a></li>
                    @endif
                    <li><a href="{{ route('guru.index') }}" class="{{ request()->routeIs('guru.*') ? 'active' : '' }}">👩‍🏫 Data Guru</a></li>
                    <li><a href="{{ route('sekolah.index') }}" class="{{ request()->routeIs('sekolah.*') ? 'active' : '' }}">🏫 Data Sekolah</a></li>
                    <li><a href="{{ route('usulan.index') }}" class="{{ request()->routeIs('usulan.*') ? 'active' : '' }}">📝 Usulan</a></li>
                    <li><a href="{{ route('swap.index') }}" class="{{ request()->routeIs('swap.*') ? 'active' : '' }}">🔄 Mutasi Silang</a></li>
                    <li><a href="{{ route('spk.index') }}" class="{{ request()->routeIs('spk.*') ? 'active' : '' }}">🎯 SPK Penempatan</a></li>
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <li><a href="{{ route('pengguna.index') }}" class="{{ request()->routeIs('pengguna.*') ? 'active' : '' }}">👥 Kelola Pengguna</a></li>
                    @endif
                </ul>

                <!-- Info pengguna + keluar -->
                <div class="p-4 border-t border-primary-content/20">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="avatar placeholder">
                            <div class="bg-primary-content/20 text-primary-content rounded-full w-10">
                                <span class="text-lg">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold truncate">{{ auth()->user()->name }}</p>
                            <span class="badge badge-accent badge-sm">{{ auth()->user()->labelRole() }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm w-full">🚪 Keluar</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>

    <!-- Script khusus halaman -->
    @stack('skrip')

    <!-- Script komponen Livewire (wajib ada) -->
    @livewireScripts
</body>
</html>
