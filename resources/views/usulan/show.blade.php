{{-- Detail usulan + tombol proses sesuai peran --}}
@extends('layouts.app')
@section('judul', 'Detail Usulan — PetaPenempatan ASN')

@section('konten')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">📝 Detail Usulan</h1>
        <a href="{{ route('usulan.index') }}" class="btn btn-ghost">← Kembali</a>
    </div>

    <x-galat />

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
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend" for="f-catatan">Catatan (opsional)</label>
                            <textarea id="f-catatan" name="catatan" rows="2" class="textarea w-full"></textarea>
                        </fieldset>
                        <button type="submit" class="btn btn-info w-full mt-2">✔️ Verifikasi</button>
                    </form>
                @endif

                @if ($usulan->status === 'diverifikasi' && auth()->user()->hasRole('kadis'))
                    <p class="text-sm opacity-70">Sebagai Kadis, persetujuan akan <strong>memutasi guru</strong> ke sekolah tujuan.</p>
                    <form method="POST" action="{{ route('usulan.setujui', $usulan) }}">
                        @csrf
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend" for="f-catatan-2">Catatan (opsional)</label>
                            <textarea id="f-catatan-2" name="catatan" rows="2" class="textarea w-full"></textarea>
                        </fieldset>
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
                            <fieldset class="fieldset w-full">
                                <label class="fieldset-legend" for="f-catatan-3">Alasan penolakan (wajib)</label>
                                <textarea id="f-catatan-3" name="catatan" rows="2" required class="textarea w-full"></textarea>
                            </fieldset>
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

    <div class="card bg-base-100 shadow mt-4">
        <div class="card-body">
            <h2 class="card-title text-lg">Riwayat Transisi Status</h2>
            <ul class="timeline timeline-vertical">
                @forelse (\App\Models\ActivityLog::riwayat($usulan)->get() as $r)
                    <li>
                        <div class="timeline-start text-sm opacity-60">{{ $r->waktu->format('d/m/Y H:i') }}</div>
                        <div class="timeline-middle">📌</div>
                        <div class="timeline-end timeline-box text-sm">
                            <strong>{{ $r->aksi }}</strong> oleh {{ $r->user?->name ?? 'sistem' }}
                            @if (($r->perubahan['status_sebelum'] ?? null))
                                <div>{{ $r->perubahan['status_sebelum'] }} → {{ $r->perubahan['status_sesudah'] }}</div>
                            @endif
                            @if (($r->perubahan['alasan'] ?? null))
                                <div class="italic">“{{ $r->perubahan['alasan'] }}”</div>
                            @endif
                        </div>
                        <hr>
                    </li>
                @empty
                    <li class="text-sm opacity-60">Belum ada riwayat tercatat.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
