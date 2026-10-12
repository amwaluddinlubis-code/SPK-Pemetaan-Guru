@extends('layouts.app')

@section('judul', 'Simulasi Kebijakan — PetaPenempatan ASN')

@section('konten')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Simulasi Kebijakan “What-If”</h1>
    <p class="text-sm opacity-70 mt-1">Uji skenario parameter tanpa mengubah data operasional. Hasil bersifat hipotetis, bukan keputusan resmi.</p>
</div>

<form method="POST" action="{{ route('simulasi.jalankan') }}" class="card bg-base-100 shadow p-6 max-w-xl">
    @csrf
    <div class="grid gap-4">
        <div>
            <label class="label"><span class="label-text font-semibold">Ambang jarak (km) *</span></label>
            <input type="number" name="ambang_km" value="{{ old('ambang_km', 15) }}" min="1" max="500" step="0.5" class="input input-bordered w-full" required>
            <p class="text-xs opacity-60 mt-1">Hanya guru yang jaraknya melebihi ambang ini yang dipertimbangkan.</p>
            @error('ambang_km')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label"><span class="label-text font-semibold">Mapel</span></label>
            <select name="mapel" class="select select-bordered w-full">
                <option value="">Semua mapel</option>
                @foreach ($daftarMapel as $m)
                    <option value="{{ $m }}" @selected(old('mapel') === $m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label"><span class="label-text font-semibold">Kecamatan domisili</span></label>
            <select name="kecamatan" class="select select-bordered w-full">
                <option value="">Semua kecamatan</option>
                @foreach ($daftarKecamatan as $k)
                    <option value="{{ $k }}" @selected(old('kecamatan') === $k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="sertakan_3t" value="1" class="checkbox" @checked(old('sertakan_3t'))>
                <span class="label-text">Sertakan guru dari sekolah 3T sebagai kandidat</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Jalankan Simulasi</button>
    </div>
</form>
@endsection
