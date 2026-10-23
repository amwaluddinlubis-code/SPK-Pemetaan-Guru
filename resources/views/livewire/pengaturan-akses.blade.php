{{-- Matriks Pengaturan Akses (Livewire): saklar per pasangan menu × peran.
     Menutup akses menyembunyikan menu dari bilah sisi DAN menolak rutenya
     (403) lewat middleware PastikanAksesMenu. --}}
<div>
    <div class="alert alert-info mb-4">
        <span>
            Atur menu/modul apa saja yang boleh dibuka setiap peran. Perubahan langsung berlaku:
            menu hilang dari bilah sisi dan alamatnya ditolak (403) untuk peran tersebut.
            Izin bawaan mengikuti aturan lama aplikasi; tombol di bawah mengembalikan semuanya ke bawaan.
            Catatan: saklar ini mengatur akses membuka modul — hak melakukan aksi di dalamnya
            (tambah/ubah/hapus/menyetujui) tetap mengikuti aturan peran rinci yang sudah ada,
            dan Kelola Pengguna serta halaman Pengaturan tetap khusus Kabid PTK.
        </span>
    </div>

    @if ($pesan !== '')
        <div class="alert alert-success mb-4" wire:key="pesan-akses">
            <span>{{ $pesan }}</span>
        </div>
    @endif

    <x-kartu class="overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Menu / Modul</th>
                        @foreach ($daftarPeran as $labelPeran)
                            <th class="text-center">{{ $labelPeran }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grupMenu as $namaGrup => $items)
                        @if ($namaGrup !== '')
                            <tr>
                                <td colspan="{{ 1 + count($daftarPeran) }}" class="bg-base-200 font-bold">
                                    {{ $namaGrup }}
                                </td>
                            </tr>
                        @endif
                        @foreach ($items as $kunci => $item)
                            <tr>
                                <td>
                                    <span class="font-medium">{{ $item['label'] }}</span><br>
                                    <span class="text-xs opacity-60">{{ $item['rute'] }}</span>
                                </td>
                                @foreach ($daftarPeran as $role => $labelPeran)
                                    <td class="text-center">
                                        @if ($kunci === $kunciSuper && $role === $peranSuper)
                                            <input type="checkbox" class="toggle toggle-primary" checked disabled
                                                   title="Selalu aktif agar administrator tidak terkunci dari halaman ini">
                                        @elseif (in_array($kunci, $kunciAdminPenuh, true) && $role !== $peranSuper)
                                            <input type="checkbox" class="toggle" disabled
                                                   @checked($matriks[$kunci][$role])
                                                   title="Modul administrasi ini khusus Kabid PTK (dijaga middleware peran di rutenya)">
                                        @else
                                            <input type="checkbox" class="toggle toggle-primary"
                                                   wire:change="toggle('{{ $kunci }}', '{{ $role }}')"
                                                   @checked($matriks[$kunci][$role])
                                                   aria-label="Akses {{ $item['label'] }} untuk {{ $labelPeran }}">
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <button type="button" class="btn btn-outline" wire:click="resetBawaan"
                wire:confirm="Kembalikan semua akses ke bawaan? Penyimpangan yang sudah diatur akan dihapus.">
            Kembalikan ke Bawaan
        </button>
        <span class="text-sm opacity-60">Setiap perubahan tercatat di Jejak Audit.</span>
    </div>
</div>
