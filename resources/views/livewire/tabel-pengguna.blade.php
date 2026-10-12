{{-- Tabel Kelola Pengguna (Livewire): pencarian, filter peran, pagination tanpa reload. --}}
<div>
    <div class="card bg-base-100 shadow-xl mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama atau email…"
                       class="input input-bordered flex-1 min-w-52">
                <select wire:model.live="role" class="select select-bordered max-w-xs">
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
    </div>

    <div class="card bg-base-100 shadow-xl overflow-hidden">
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
                                <a href="{{ route('pengguna.edit', $u) }}" class="btn btn-sm btn-warning">Ubah</a>
                                <form method="POST" action="{{ route('pengguna.destroy', $u) }}" class="inline"
                                      onsubmit="return confirm('Hapus akun {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-error">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center opacity-60 py-8">Tidak ada pengguna yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $daftarUser->links() }}</div>
</div>
