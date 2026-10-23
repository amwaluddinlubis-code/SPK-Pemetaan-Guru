<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <select wire:model="role" class="select w-full">
                    <option value="">Semua peran</option>
                    @foreach (['kabid_ptk', 'kadis', 'guru', 'kepala_sekolah', 'pemda'] as $r)
                        <option value="{{ $r }}" @if($r === $role) selected @endif>
                            {{ ucfirst($r) }}
                        </option>
                    @endforeach
                </select>
                @if ($role !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost ms-2">Reset</button>
                @endif
            </div>
            @if ($role !== '')
                <p class="text-sm opacity-60 mt-2">Menampilkan {{ $data->total() }} user role: {{ ucfirst($role) }}</p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden hidden md:block">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @if ($u->role === 'guru')
                                    <span class="badge badge-outline">Guru</span>
                                @elseif ($u->role === 'kabid_ptk')
                                    <span class="badge badge-primary">Kabid PTK</span>
                                @elseif ($u->role === 'kadis')
                                    <span class="badge badge-info">KADIS</span>
                                @elseif ($u->role === 'kepala_sekolah')
                                    <span class="badge badge-success">Kepala Sekolah</span>
                                @else
                                    <span class="badge badge-warning">Pemda</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if (auth()->user()->hasRole('kabid_ptk'))
                                    <x-hapus :aksi="route('pengguna.destroy', $u)" tanya="Hapus user {{$u->nama}}?" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-8 opacity-60">Tidak ada data pengguna yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="grid gap-3 md:hidden">
        @forelse ($data as $u)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <p class="font-bold">{{ $u->name }}</p>
                    <p class="text-sm opacity-60">{{ $u->email }}</p>
                    <span class="badge {{ match($u->role, 'kabid_ptk') => 'badge-primary', 'kadis' => 'badge-info', 'guru' => 'badge-outline', 'kepala_sekolah' => 'badge-success', 'pemda' => 'badge-warning' }}">
                        {{ ucfirst($u->role) }}
                    </span>
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <x-hapus :aksi="route('pengguna.destroy', $u)" tanya="Hapus user {{$u->nama}}?" />
                    @endif
                </div>
            </div>
        @empty
            <x-kosong pesan="Tidak ada data pengguna yang cocok." />
        @endforelse
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            {{ $data->links() }}
        </div>
        <div></div>
    </div>
</div>