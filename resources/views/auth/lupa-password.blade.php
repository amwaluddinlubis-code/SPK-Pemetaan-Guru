<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — PetaPenempatan ASN</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <h1 class="card-title text-2xl">Lupa Password</h1>
            <p class="text-sm opacity-70">Masukkan email akun Anda. Kami akan mengirimkan tautan untuk mereset password.</p>

            @if (session('sukses'))
                <div class="alert alert-success text-sm">{{ session('sukses') }}</div>
            @endif
            @if ($errors->any())
                <x-galat />
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4 mt-2">
                @csrf
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Email</legend>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="input w-full" placeholder="nama@contoh.id" autofocus>
                </fieldset>
                <button type="submit" class="btn btn-primary w-full">Kirim Tautan Reset</button>
            </form>

            <a href="{{ route('login') }}" class="link link-primary text-sm mt-2">← Kembali ke halaman login</a>
        </div>
    </div>
</body>
</html>
