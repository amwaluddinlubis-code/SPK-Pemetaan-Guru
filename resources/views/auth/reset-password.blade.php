<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — PetaPenempatan ASN</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <h1 class="card-title text-2xl">Buat Password Baru</h1>
            <p class="text-sm opacity-70">Password minimal 8 karakter.</p>

            @if ($errors->any())
                <x-galat />
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4 mt-2">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Email</legend>
                    <input type="email" name="email" required value="{{ old('email', $email) }}"
                           class="input w-full" readonly>
                </fieldset>
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Password Baru</legend>
                    <input type="password" name="password" required class="input w-full" autofocus>
                </fieldset>
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Ulangi Password Baru</legend>
                    <input type="password" name="password_confirmation" required class="input w-full">
                </fieldset>
                <button type="submit" class="btn btn-primary w-full">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>
