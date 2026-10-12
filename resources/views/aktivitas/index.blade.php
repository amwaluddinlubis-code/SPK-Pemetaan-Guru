@extends('layouts.app')

@section('judul', 'Jejak Audit — PetaPenempatan ASN')

@section('konten')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Jejak Audit Operasional</h1>
    <span class="text-sm opacity-60">Append-only: catatan tidak bisa diubah/dihapus lewat aplikasi.</span>
</div>

<form method="GET" action="{{ route('aktivitas.index') }}" class="card bg-base-100 shadow p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div>
            <label class="label"><span class="label-text">Aksi</span></label>
            <select name="aksi" class="select select-bordered select-sm w-full">
                <option value="">Semua</option>
                @foreach ($daftarAksi as $a)
                    <option value="{{ $a }}" @selected(request('aksi') === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label"><span class="label-text">Aktor</span></label>
            <select name="user_id" class="select select-bordered select-sm w-full">
                <option value="">Semua</option>
                @foreach ($daftarUser as $u)
                    <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }} ({{ $u->role }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label"><span class="label-text">Hasil</span></label>
            <select name="hasil" class="select select-bordered select-sm w-full">
                <option value="">Semua</option>
                <option value="sukses" @selected(request('hasil') === 'sukses')>Sukses</option>
                <option value="gagal" @selected(request('hasil') === 'gagal')>Gagal</option>
            </select>
        </div>
        <div>
            <label class="label"><span class="label-text">Dari</span></label>
            <input type="date" name="dari" value="{{ request('dari') }}" class="input input-bordered input-sm w-full">
        </div>
        <div>
            <label class="label"><span class="label-text">Sampai</span></label>
            <input type="date" name="sampai" value="{{ request('sampai') }}" class="input input-bordered input-sm w-full">
        </div>
    </div>
    <div class="mt-3 flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('aktivitas.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead>
            <tr><th>Waktu</th><th>Aktor</th><th>Aksi</th><th>Entitas</th><th>Hasil</th><th>Detail</th></tr>
        </thead>
        <tbody>
            @forelse ($log as $l)
                <tr>
                    <td class="whitespace-nowrap">{{ $l->waktu->format('d/m/Y H:i') }}</td>
                    <td>{{ $l->user?->name ?? '—' }}</td>
                    <td><span class="badge badge-outline">{{ $l->aksi }}</span></td>
                    <td>{{ $l->entitas_tipe }}{{ $l->entitas_id ? ' #'.$l->entitas_id : '' }}</td>
                    <td>
                        <span class="badge {{ $l->hasil === 'sukses' ? 'badge-success' : 'badge-error' }}">{{ $l->hasil }}</span>
                    </td>
                    <td class="text-xs">
                        @if ($l->perubahan)
                            @foreach ($l->perubahan as $k => $v)
                                <div><span class="font-semibold">{{ $k }}:</span> {{ is_array($v) ? json_encode($v) : $v }}</div>
                            @endforeach
                        @else
                            <span class="opacity-40">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center opacity-60 py-6">Belum ada catatan audit.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $log->links() }}</div>
@endsection
