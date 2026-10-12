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
                <div class="alert alert-error text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4 mt-2">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <label class="form-control w-full">
                    <span class="label-text font-semibold">Email</span>
                    <input type="email" name="email" required value="{{ old('email', $email) }}"
                           class="input input-bordered w-full" readonly>
                </label>
                <label class="form-control w-full">
                    <span class="label-text font-semibold">Password Baru</span>
                    <input type="password" name="password" required class="input input-bordered w-full" autofocus>
                </label>
                <label class="form-control w-full">
                    <span class="label-text font-semibold">Ulangi Password Baru</span>
                    <input type="password" name="password_confirmation" required class="input input-bordered w-full">
                </label>
                <button type="submit" class="btn btn-primary w-full">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>
