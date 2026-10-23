<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $namaPemdaLogin = \App\Services\KonteksPemda::namaPemda();
        $logoLogin = \App\Services\Branding::logoUrl();
    @endphp
    <title>Masuk — {{ $namaPemdaLogin ?? 'PetaPenempatan ASN' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Branding pemda: warna aksen menimpa variabel tema daisyUI (R0) -->
    <style>{!! \App\Services\Branding::gayaTema() !!}</style>
</head>
<body class="min-h-screen bg-base-200">

<div class="min-h-screen flex">

    <!-- ===== Panel kiri: branding (disembunyikan di layar kecil) ===== -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden text-white"
         style="background: linear-gradient(140deg, var(--color-primary), color-mix(in oklab, var(--color-primary) 52%, black));">
        <!-- Lingkaran dekoratif -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-16 w-[30rem] h-[30rem] bg-white/10 rounded-full blur-3xl"></div>

        <div class="relative z-10 flex flex-col justify-center px-16 max-w-2xl">
            @if ($logoLogin)
                <img src="{{ $logoLogin }}" alt="Logo pemda" class="h-20 w-20 rounded-2xl bg-white object-contain p-1.5 mb-6">
            @else
                <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15"><x-ikon nama="peta" class="h-11 w-11" /></div>
            @endif
            <h1 class="text-4xl font-extrabold mb-4 leading-tight">
                PetaPenempatan ASN
            </h1>
            @if ($namaPemdaLogin)
                <p class="text-xl font-semibold text-white/85 -mt-2 mb-4">{{ $namaPemdaLogin }}</p>
            @endif
            <p class="text-lg text-white/75 mb-10">
                Sistem pemetaan penempatan Guru ASN berbasis domisili —
                menempatkan guru lebih dekat dengan rumahnya.
            </p>

            <ul class="space-y-5 text-white/90">
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/15 text-white font-bold">✓</span>
                    <span><strong>Peta interaktif</strong><br>
                    <span class="text-white/70 text-sm">Visualisasi domisili guru & lokasi sekolah dalam satu peta.</span></span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/15 text-white font-bold">✓</span>
                    <span><strong>Rekomendasi otomatis</strong><br>
                    <span class="text-white/70 text-sm">Sistem menyarankan sekolah terdekat yang membutuhkan mapel guru.</span></span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/15 text-white font-bold">✓</span>
                    <span><strong>Data aman</strong><br>
                    <span class="text-white/70 text-sm">Data pribadi guru hanya bisa diakses pengguna terdaftar.</span></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- ===== Panel kanan: form login ===== -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-md">

            <!-- Logo khusus layar kecil -->
            <div class="text-center lg:hidden mb-8">
                @if ($logoLogin)
                    <img src="{{ $logoLogin }}" alt="Logo pemda" class="h-16 w-16 mx-auto rounded-2xl bg-white object-contain p-1 shadow">
                @else
                    <div class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-primary text-primary-content"><x-ikon nama="peta" class="h-9 w-9" /></div>
                @endif
                <h1 class="text-2xl font-extrabold mt-3">PetaPenempatan ASN</h1>
                <p class="text-sm opacity-60 mt-1">{{ $namaPemdaLogin ?? 'Penempatan guru berbasis domisili' }}</p>
            </div>

            <div class="card bg-base-100 shadow-2xl">
                <div class="card-body sm:p-8">
                    <h2 class="card-title text-2xl">Selamat datang kembali</h2>
                    <p class="opacity-60 mb-4">Masuk untuk mengelola data penempatan guru</p>

                    {{-- Pesan error (validasi / login gagal) --}}
                    <x-galat />

                    <form method="POST" action="{{ route('login.proses') }}">
                        @csrf

                        <fieldset class="fieldset w-full mb-4">
                            <label class="fieldset-legend font-semibold mb-1" for="f-login">Email / NIP</label>
                            <input id="f-login" type="text" name="login" value="{{ old('login') }}"
                                   required autofocus placeholder="Email pejabat, atau NIP bagi guru"
                                   class="input w-full focus:input-primary">
                            <p class="text-xs opacity-60 mt-1">Guru masuk dengan NIP sebagai username &amp; password awal.</p>
                        </fieldset>

                        <fieldset class="fieldset w-full mb-2">
                            <label class="fieldset-legend font-semibold mb-1" for="input-password">Password</label>
                            <div class="relative">
                                <input type="password" id="input-password" name="password"
                                       required placeholder="••••••••"
                                       class="input w-full pr-12 focus:input-primary">
                                {{-- Tombol intip password (JavaScript murni) --}}
                                <button type="button" id="btn-intip"
                                        class="btn btn-ghost btn-sm absolute right-1 top-1/2 -translate-y-1/2"
                                        title="Tampilkan/sembunyikan password" aria-label="Tampilkan/sembunyikan password"><x-ikon nama="mata" class="h-5 w-5" /></button>
                            </div>
                        </fieldset>

                        <label class="label cursor-pointer justify-start gap-2 mb-5">
                            <input type="checkbox" name="remember"
                                   class="checkbox checkbox-primary checkbox-sm">
                            <span class="label-text">Ingat saya di perangkat ini</span>
                        </label>

                        <button type="submit" class="btn btn-primary w-full btn-lg">
                            Masuk →
                        </button>
                        <div class="text-center mt-3">
                            <a href="{{ route('password.request') }}" class="link link-primary text-sm">Lupa password?</a>
                        </div>
                        <div class="text-center mt-2">
                            <a href="{{ route('daftar-guru') }}" class="link link-primary text-sm">Belum punya akun? Daftar sebagai guru →</a>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-xs text-center opacity-50 mt-6">
                Data guru dilindungi — hanya pengguna terdaftar yang bisa mengakses.
            </p>
        </div>
    </div>
</div>

<script>
// Toggle tampil/sembunyi password (JavaScript murni, tanpa library)
document.getElementById('btn-intip').addEventListener('click', function () {
    const input = document.getElementById('input-password');
    const sedangTampil = input.type === 'text';
    input.type = sedangTampil ? 'password' : 'text';
});
</script>

</body>
</html>
