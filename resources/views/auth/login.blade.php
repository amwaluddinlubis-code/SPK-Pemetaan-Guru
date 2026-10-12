<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — PetaPenempatan ASN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">

<div class="min-h-screen flex">

    <!-- ===== Panel kiri: branding (disembunyikan di layar kecil) ===== -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden
                bg-linear-to-br from-blue-900 via-indigo-800 to-purple-900 text-white">
        <!-- Lingkaran dekoratif -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-16 w-[30rem] h-[30rem] bg-fuchsia-500/20 rounded-full blur-3xl"></div>

        <div class="relative z-10 flex flex-col justify-center px-16 max-w-2xl">
            <div class="text-6xl mb-6">🗺️</div>
            <h1 class="text-4xl font-extrabold mb-4 leading-tight">
                PetaPenempatan ASN
            </h1>
            <p class="text-lg text-white/75 mb-10">
                Sistem pemetaan penempatan Guru ASN berbasis domisili —
                menempatkan guru lebih dekat dengan rumahnya.
            </p>

            <ul class="space-y-5 text-white/90">
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-400 text-emerald-950 font-bold">✓</span>
                    <span><strong>Peta interaktif</strong><br>
                    <span class="text-white/70 text-sm">Visualisasi domisili guru & lokasi sekolah dalam satu peta.</span></span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-300 text-amber-950 font-bold">✓</span>
                    <span><strong>Rekomendasi otomatis</strong><br>
                    <span class="text-white/70 text-sm">Sistem menyarankan sekolah terdekat yang membutuhkan mapel guru.</span></span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-300 text-sky-950 font-bold">✓</span>
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
                <div class="text-5xl">🗺️</div>
                <h1 class="text-2xl font-extrabold mt-3">PetaPenempatan ASN</h1>
                <p class="text-sm opacity-60 mt-1">Penempatan guru berbasis domisili</p>
            </div>

            <div class="card bg-base-100 shadow-2xl">
                <div class="card-body sm:p-8">
                    <h2 class="card-title text-2xl">Selamat datang kembali 👋</h2>
                    <p class="opacity-60 mb-4">Masuk untuk mengelola data penempatan guru</p>

                    {{-- Pesan error (validasi / login gagal) --}}
                    <x-galat />

                    <form method="POST" action="{{ route('login.proses') }}">
                        @csrf

                        <fieldset class="fieldset w-full mb-4">
                            <legend class="fieldset-legend font-semibold mb-1">📧 Email</legend>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   required autofocus placeholder="nama@contoh.id"
                                   class="input w-full focus:input-primary">
                        </fieldset>

                        <fieldset class="fieldset w-full mb-2">
                            <legend class="fieldset-legend font-semibold mb-1">🔒 Password</legend>
                            <div class="relative">
                                <input type="password" id="input-password" name="password"
                                       required placeholder="••••••••"
                                       class="input w-full pr-12 focus:input-primary">
                                {{-- Tombol intip password (JavaScript murni) --}}
                                <button type="button" id="btn-intip"
                                        class="btn btn-ghost btn-sm absolute right-1 top-1/2 -translate-y-1/2"
                                        title="Tampilkan/sembunyikan password">👁️</button>
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
                    </form>
                </div>
            </div>

            <p class="text-xs text-center opacity-50 mt-6">
                🔐 Data guru dilindungi — hanya pengguna terdaftar yang bisa mengakses.
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
    this.textContent = sedangTampil ? '👁️' : '🙈';
});
</script>

</body>
</html>
