<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — PetaPenempatan ASN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="text-5xl">🔑</div>
            <h1 class="text-2xl font-extrabold mt-3">Lupa Password</h1>
            <p class="text-sm opacity-60 mt-1">Masukkan email akun Anda — link reset akan dikirim ke sana.</p>
        </div>

        <div class="card bg-base-100 shadow-2xl">
            <div class="card-body">
                @if (session('sukses'))
                    <div class="alert alert-success mb-4 text-sm">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error mb-4">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.kirim') }}">
                    @csrf
                    <label class="form-control w-full mb-4">
                        <span class="label-text font-semibold mb-1">📧 Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="input input-bordered w-full" placeholder="nama@contoh.id">
                    </label>
                    <button type="submit" class="btn btn-primary w-full">Kirim Link Reset</button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="link link-primary text-sm">← Kembali ke halaman masuk</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
