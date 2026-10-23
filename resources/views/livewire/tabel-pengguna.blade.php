{{-- Tabel Kelola Pengguna (Livewire): pencarian, filter peran, pagination tanpa reload. --}}
<div>
<x-halaman judul="👥 Kelola Pengguna">
    <x-slot:aksi>
        <a href="{{ route('pengguna.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
    </x-slot:aksi>
</x-halaman>

<x-galat />

<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input aria-label="Cari nama atau email…" type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama atau email…"
                       class="input flex-1 min-w-52">
                <select aria-label="Peran" wire:model.live="role" class="select max-w-xs">
                    <option value="">— Semua Peran —</option>
                    @foreach (\App\Models\User::ROLES as $nilai => $label)
                        <option value="{{ $nilai }}">{{ $label }}</option>
                    @endforeach
                </select>
                @if ($q !== '' || $role !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($q !== '' || $role !== '')
                <p class="text-sm opacity-60 mt-2">Menampilkan {{ $daftarUser->total() }} hasil.</p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nama / Email</th>
                        <th>Peran</th>
                        <th>Scope</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarUser as $u)
                        <tr>
                            <td>
                                <strong>{{ $u->name }}</strong><br>
                                <span class="text-sm opacity-60">{{ $u->email }}</span>
                            </td>
                            <td><span class="badge badge-primary">{{ $u->labelRole() }}</span></td>
                            <td class="text-sm">
                                @if ($u->role === 'guru' && $u->guru)
                                    {{ $u->guru->nama }}
                                @elseif ($u->role === 'kepala_sekolah' && $u->sekolah)
                                    {{ $u->sekolah->nama }}
                                @elseif ($u->role === 'ketua_pgri')
                                    Kec. {{ $u->kecamatan }}
                                @else
                                    <span class="opacity-50">—</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @if ($u->role !== 'kabid_ptk' && $u->id !== auth()->id() && ! session()->has(\App\Http\Controllers\ImpersonateController::SESSION_ASAL))
                                    <form method="POST" action="{{ route('impersonate.mulai', $u) }}" class="inline"
                                          onsubmit="return confirm('Masuk sebagai {{ $u->name }}? Anda bisa kembali ke akun Anda kapan saja lewat bilah kuning di atas.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info">🎭 Impersonate</button>
                                    </form>
                                @endif
                                <a href="{{ route('pengguna.edit', $u) }}" class="btn btn-sm btn-warning">Ubah</a>
                                <x-hapus :aksi="route('pengguna.destroy', $u)" tanya="Hapus akun {{ $u->name }}?" />
                            </td>
                        </tr>
                    @empty
                        <x-kosong pesan="Tidak ada pengguna yang cocok." kolom="4" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftarUser" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $daftarUser->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
