{{-- Detail usulan + tombol proses sesuai peran --}}
@extends('layouts.app')
@section('judul', 'Detail Usulan — PetaPenempatan ASN')

@section('konten')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">📝 Detail Usulan</h1>
        <a href="{{ route('usulan.index') }}" class="btn btn-ghost">← Kembali</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">
                    {{ $usulan->guru->nama }}
                    <span class="badge {{ $usulan->badgeStatus() }}">{{ $usulan->labelStatus() }}</span>
                </h2>
                <dl class="text-sm space-y-1 mt-2">
                    <div class="flex gap-2"><dt class="w-36 opacity-60">NIP</dt><dd>{{ $usulan->guru->nip }}</dd></div>
                    <div class="flex gap-2"><dt class="w-36 opacity-60">Mapel</dt><dd>{{ $usulan->guru->mapel }} ({{ $usulan->guru->jenjang }})</dd></div>
                    <div class="flex gap-2"><dt class="w-36 opacity-60">Domisili</dt><dd>{{ $usulan->guru->domisili_alamat }}</dd></div>
                    <div class="flex gap-2"><dt class="w-36 opacity-60">Sekolah saat ini</dt><dd>{{ $usulan->guru->sekolah_saat_ini }}</dd></div>
                    <div class="flex gap-2"><dt class="w-36 opacity-60">Sekolah tujuan</dt><dd><strong>{{ $usulan->sekolah->nama }}</strong> ({{ $usulan->sekolah->kecamatan ?? '-' }})</dd></div>
                    <div class="flex gap-2"><dt class="w-36 opacity-60">Diajukan oleh</dt><dd>{{ $usulan->pengaju->name ?? '—' }} • {{ $usulan->created_at->format('d M Y H:i') }}</dd></div>
                    @if ($usulan->pemroses)
                        <div class="flex gap-2"><dt class="w-36 opacity-60">Diproses oleh</dt><dd>{{ $usulan->pemroses->name }} • {{ $usulan->updated_at->format('d M Y H:i') }}</dd></div>
                    @endif
                </dl>
                <div class="divider">Alasan</div>
                <p class="text-sm">{{ $usulan->alasan }}</p>
                @if ($usulan->catatan)
                    <div class="divider">Catatan verifikasi</div>
                    <p class="text-sm">{{ $usulan->catatan }}</p>
                @endif
            </div>
        </div>

        {{-- Panel proses sesuai peran & status --}}
        <div class="card bg-base-100 shadow-xl h-fit">
            <div class="card-body">
                <h2 class="card-title">⚙️ Proses Usulan</h2>

                @if ($usulan->status === 'diajukan' && auth()->user()->hasRole('kabid_ptk'))
                    <p class="text-sm opacity-70">Sebagai Kabid PTK, verifikasi usulan ini agar diteruskan ke Kadis.</p>
                    <form method="POST" action="{{ route('usulan.verifikasi', $usulan) }}">
                        @csrf
                        <label class="form-control w-full">
                            <span class="label-text">Catatan (opsional)</span>
                            <textarea name="catatan" rows="2" class="textarea textarea-bordered w-full"></textarea>
                        </label>
                        <button type="submit" class="btn btn-info w-full mt-2">✔️ Verifikasi</button>
                    </form>
                @endif

                @if ($usulan->status === 'diverifikasi' && auth()->user()->hasRole('kadis'))
                    <p class="text-sm opacity-70">Sebagai Kadis, persetujuan akan <strong>memutasi guru</strong> ke sekolah tujuan.</p>
                    <form method="POST" action="{{ route('usulan.setujui', $usulan) }}">
                        @csrf
                        <label class="form-control w-full">
                            <span class="label-text">Catatan (opsional)</span>
                            <textarea name="catatan" rows="2" class="textarea textarea-bordered w-full"></textarea>
                        </label>
                        <button type="submit" class="btn btn-success w-full mt-2"
                                onclick="return confirm('Setujui dan mutasi guru ke {{ $usulan->sekolah->nama }}?')">
                            ✅ Setujui & Mutasi
                        </button>
                    </form>
                @endif

                @if ($usulan->masihAktif() && auth()->user()->hasRole('kabid_ptk', 'kadis'))
                    @if (! (auth()->user()->hasRole('kadis') && $usulan->status === 'diajukan'))
                        <div class="divider">atau</div>
                        <form method="POST" action="{{ route('usulan.tolak', $usulan) }}">
                            @csrf
                            <label class="form-control w-full">
                                <span class="label-text">Alasan penolakan (wajib)</span>
                                <textarea name="catatan" rows="2" required class="textarea textarea-bordered w-full"></textarea>
                            </label>
                            <button type="submit" class="btn btn-error btn-outline w-full mt-2"
                                    onclick="return confirm('Tolak usulan ini?')">✕ Tolak</button>
                        </form>
                    @endif
                @endif

                @if (! $usulan->masihAktif())
                    <div class="alert {{ $usulan->status === 'disetujui' ? 'alert-success' : 'alert-error' }}">
                        <span class="text-sm">Usulan sudah final: <strong>{{ $usulan->labelStatus() }}</strong>.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
