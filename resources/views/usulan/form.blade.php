{{-- Form pengajuan usulan penempatan baru --}}
@extends('layouts.app')
@section('judul', 'Ajukan Usulan — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">📝 Ajukan Usulan Penempatan</h1>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-error">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('usulan.store') }}">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Guru yang diusulkan</span>
                        <select name="guru_id" class="select select-bordered w-full" required>
                            <option value="">— Pilih guru —</option>
                            @foreach ($daftarGuru as $g)
                                <option value="{{ $g->id }}"
                                    {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                    {{ $g->nama }} — {{ $g->mapel }} ({{ $g->sekolah_saat_ini }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Sekolah Tujuan</span>
                        <select name="sekolah_id" class="select select-bordered w-full" required>
                            <option value="">— Pilih sekolah —</option>
                            @foreach ($daftarSekolah as $s)
                                <option value="{{ $s->id }}"
                                    {{ old('sekolah_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }} ({{ $s->kecamatan ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control w-full sm:col-span-2">
                        <span class="label-text font-semibold">Alasan Pengajuan (min. 10 karakter)</span>
                        <textarea name="alasan" rows="4" required
                                  class="textarea textarea-bordered w-full"
                                  placeholder="mis. Jarak domisili ke sekolah saat ini 12 km; sekolah tujuan butuh guru Matematika dan hanya 1,5 km dari rumah.">{{ old('alasan') }}</textarea>
                    </label>
                </div>
                <p class="text-xs opacity-60 mt-2">
                    💡 Sekolah tujuan harus membutuhkan mapel guru yang diusulkan.
                    Alur: Diajukan → Diverifikasi Kabid → Disetujui Kadis (data guru otomatis dimutasi).
                </p>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">📨 Kirim Usulan</button>
                    <a href="{{ route('usulan.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
