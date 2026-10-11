<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — PetaPenempatan ASN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="text-5xl">🔄</div>
            <h1 class="text-2xl font-extrabold mt-3">Buat Password Baru</h1>
        </div>

        <div class="card bg-base-100 shadow-2xl">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-error mb-4">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.reset.proses') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label class="form-control w-full mb-4">
                        <span class="label-text font-semibold mb-1">📧 Email</span>
                        <input type="email" name="email" value="{{ old('email', $email) }}" required
                               class="input input-bordered w-full">
                    </label>
                    <label class="form-control w-full mb-4">
                        <span class="label-text font-semibold mb-1">🔒 Password Baru</span>
                        <input type="password" name="password" required
                               class="input input-bordered w-full" placeholder="Minimal 6 karakter">
                    </label>
                    <label class="form-control w-full mb-4">
                        <span class="label-text font-semibold mb-1">🔒 Ulangi Password Baru</span>
                        <input type="password" name="password_confirmation" required
                               class="input input-bordered w-full">
                    </label>
                    <button type="submit" class="btn btn-primary w-full">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
