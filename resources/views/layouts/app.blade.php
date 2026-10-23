<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'PetaPenempatan ASN')</title>

    <!-- Aset Vite: Tailwind + daisyUI (resources/css/app.css) & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tema awal (cegah flicker): dibaca sebelum render -->
    <script>
    (function(){try{var t=localStorage.getItem('tema-ppa')||'system';if(t==='system'){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
    </script>

    <!-- Style & script khusus halaman (mis. Leaflet di dashboard) -->
    @stack('kepala')

    <!-- Style komponen Livewire -->
    @livewireStyles
</head>
<body class="bg-base-200 min-h-screen">
    {{-- Skip link untuk navigasi keyboard --}}
    <a href="#konten-utama" class="skip-link" data-testid="skip-link">Lewati ke konten utama</a>

    <!-- ===== Layout Sidebar (daisyUI drawer) ===== -->
    <div class="drawer lg:drawer-open">
        <input id="menu-sidebar" type="checkbox" class="drawer-toggle" />

        <!-- ===== Konten utama ===== -->
        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Bilah atas: selalu tampil & freeze (sticky) di semua viewport -->
            <div class="navbar bg-primary text-primary-content shadow-lg sticky top-0 z-30 px-2 sm:px-4" data-testid="navbar-utama">
                <div class="navbar-start">
                    <label for="menu-sidebar" class="btn btn-ghost lg:hidden" aria-label="Buka menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </label>
                </div>
                <div class="navbar-center">
                    <div class="flex flex-col items-center leading-tight">
                        <span class="text-lg font-bold hidden sm:inline"><x-ikon nama="peta" class="h-5 w-5 inline" /> PetaPenempatan ASN</span>
                        <span class="text-lg font-bold sm:hidden"><x-ikon nama="peta" class="h-5 w-5 inline" /> Peta ASN</span>
                        @if (! empty($konteksPemdaWilayah))
                            @if (auth()->user()->hasRole('kabid_ptk'))
                                <a href="{{ route('pengaturan.pemda') }}" class="badge badge-secondary badge-sm mt-0.5 h-6 max-w-36 truncate sm:max-w-none" title="Konteks Pemda aktif — klik untuk mengatur">📍 {{ $konteksPemdaWilayah }}</a>
                            @else
                                <span class="badge badge-secondary badge-sm mt-0.5 h-6 max-w-36 truncate sm:max-w-none" title="Konteks Pemda aktif">📍 {{ $konteksPemdaWilayah }}</span>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="navbar-end">
                    {{-- Toggle tema: light / dark / system --}}
                    <div class="dropdown dropdown-end">
                        <button tabindex="0" class="btn btn-ghost btn-sm" data-testid="toggle-tema" aria-label="Ganti tema">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </button>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 text-base-content rounded-box z-50 w-40 p-2 shadow-xl">
                            <li><button data-tema="light">☀️ Terang</button></li>
                            <li><button data-tema="dark">🌙 Gelap</button></li>
                            <li><button data-tema="system">💻 Sistem</button></li>
                        </ul>
                    </div>
                    <a href="{{ route('profil.show') }}" class="btn btn-ghost btn-sm" aria-label="Profil">
                        <span class="hidden md:inline">👤 {{ auth()->user()->name }}</span>
                        <span class="md:hidden">👤</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
                    </form>
                </div>
            </div>

            @if (! empty($impersonateAsal))
                {{-- Bilah mode impersonate: selalu tampil selama admin menyamar --}}
                <div class="bg-warning text-warning-content px-4 py-2 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-sm font-semibold" data-testid="bilah-impersonate">
                    <span>🎭 Mode impersonate: Anda masuk sebagai {{ auth()->user()->name }} ({{ auth()->user()->labelRole() }}), menyamar dari akun {{ $impersonateAsal->name }}.</span>
                    <form method="POST" action="{{ route('impersonate.berhenti') }}">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-neutral">↩ Kembali ke akun saya</button>
                    </form>
                </div>
            @endif

            <main id="konten-utama" class="container mx-auto px-4 py-6 flex-1 w-full">
                {{-- Spanduk konteks: admin diingatkan selama Pemda belum diatur --}}
                @if (auth()->user()->hasRole('kabid_ptk') && empty($konteksPemdaAktif))
                    <div class="alert alert-warning mb-4" role="alert"
                         x-data="{ tampil: localStorage.getItem('spandukKonteksPemda') !== 'tutup' }"
                         x-show="tampil" style="display: none;">
                        <span>⚠️ Aplikasi <strong>belum berkonteks Pemda</strong> — data semua wilayah masih tampil. <a href="{{ route('pengaturan.pemda') }}" class="link font-semibold">Atur sekarang</a></span>
                        <button class="btn btn-sm btn-ghost" aria-label="Tutup pengingat"
                                @click="tampil = false; localStorage.setItem('spandukKonteksPemda', 'tutup')">✕</button>
                    </div>
                @endif

                {{-- Pesan sukses (mis. setelah tambah/ubah/hapus data) --}}
                @if (session('sukses'))
                    <div class="alert alert-success mb-4" x-data="{ tampil: true }" x-show="tampil">
                        <span>{{ session('sukses') }}</span>
                        <button class="btn btn-sm btn-ghost" @click="tampil = false">✕</button>
                    </div>
                @endif

                {{-- Pesan gagal (mis. validasi persetujuan tidak lolos) --}}
                @if (session('gagal'))
                    <div class="alert alert-error mb-4" x-data="{ tampil: true }" x-show="tampil">
                        <span>{{ session('gagal') }}</span>
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
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold"><x-ikon nama="peta" class="h-6 w-6 inline" /> PetaPenempatan ASN</a>
                    <p class="text-xs opacity-70 mt-1">Sistem Informasi Penempatan Guru</p>
                    @if (! empty($konteksPemdaNama))
                        <p class="text-xs font-semibold mt-1">{{ $konteksPemdaNama }}</p>
                    @endif
                </div>

                <!-- Menu navigasi -->
                <ul class="menu p-4 flex-1 w-full font-semibold" data-testid="menu-sidebar">
                    <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><x-ikon nama="dashboard" class="h-5 w-5" /> Dashboard</a></li>
                    {{-- Item menu dari RegistriMenu: tampil bila peran pengguna
                         diizinkan Pengaturan Akses ($menuTerlihat dari composer). --}}
                    @foreach (\App\Services\RegistriMenu::grup() as $namaGrup => $itemsGrup)
                        @php $itemsTerlihat = array_filter($itemsGrup, fn ($item, $kunci) => $menuTerlihat[$kunci] ?? false, ARRAY_FILTER_USE_BOTH); @endphp
                        @if (count($itemsTerlihat) > 0)
                            @if ($namaGrup !== '')
                                <li class="menu-title mt-2"><span>{{ $namaGrup }}</span></li>
                            @endif
                            @foreach ($itemsTerlihat as $item)
                                <li><a href="{{ route($item['rute']) }}" class="{{ request()->routeIs($item['pola']) ? 'active' : '' }}"><x-ikon :nama="$item['ikon']" class="h-5 w-5" /> {{ $item['label'] }}</a></li>
                            @endforeach
                        @endif
                    @endforeach
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
                        <a href="{{ route('profil.show') }}" class="btn btn-ghost btn-sm w-full mb-1">👤 Profil Saya</a>
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
