<?php

// Komponen Livewire halaman penuh: Pengaturan Konteks Pemda — memilih
// wilayah kerja resmi aplikasi (provinsi atau kabupaten/kota) yang
// menjadi konteks seluruh modul. Hanya kabid_ptk (middleware role di
// route). Pilihan kabupaten dimuat reaktif dari provinsi terpilih;
// simpan/lepas tercatat di Jejak Audit.

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\WilayahKabupaten;
use App\Models\WilayahProvinsi;
use App\Services\KonteksPemda;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pengaturan Pemda — PetaPenempatan ASN')]
class PengaturanPemdaLivewire extends Component
{
    public string $level = 'kabupaten';

    public string $kodeProvinsi = '';

    public string $kodeKabupaten = '';

    public function mount(): void
    {
        $this->level = KonteksPemda::level() ?: 'kabupaten';
        $this->kodeProvinsi = KonteksPemda::kodeProvinsi() ?: '';
        $this->kodeKabupaten = KonteksPemda::kodeKabupaten() ?: '';
    }

    /** Ganti provinsi → pilihan kabupaten tidak berlaku lagi. */
    public function updatedKodeProvinsi(): void
    {
        $this->kodeKabupaten = '';
    }

    protected function rules(): array
    {
        return [
            'level' => ['required', 'in:provinsi,kabupaten'],
            'kodeProvinsi' => ['required', 'exists:wilayah_provinsi,kode'],
            'kodeKabupaten' => ['nullable', 'required_if:level,kabupaten', 'exists:wilayah_kabupaten,kode'],
        ];
    }

    protected function messages(): array
    {
        return [
            'kodeKabupaten.required_if' => 'Pilih kabupaten/kota untuk level Kabupaten/Kota.',
        ];
    }

    /** Simpan konteks Pemda terpilih. */
    public function simpan()
    {
        $this->validate();

        if ($this->level === 'kabupaten') {
            $kab = WilayahKabupaten::find($this->kodeKabupaten);
            if ($kab === null || $kab->provinsi_kode !== $this->kodeProvinsi) {
                $this->addError('kodeKabupaten', 'Kabupaten/kota tidak berada di provinsi terpilih.');

                return null;
            }
        }

        KonteksPemda::simpan(
            $this->level,
            $this->kodeProvinsi,
            $this->level === 'kabupaten' ? $this->kodeKabupaten : null
        );
        ActivityLog::catatAman('pengaturan.pemda', null, 'sukses', [
            'pemda' => KonteksPemda::namaPemda(),
            'level' => $this->level,
        ]);

        session()->flash('sukses', 'Konteks Pemda disimpan: '.KonteksPemda::namaPemda().'. Data di luar wilayah ini sekarang disembunyikan.');

        return $this->redirectRoute('pengaturan.pemda');
    }

    /** Lepas konteks Pemda (aplikasi kembali menampilkan semua wilayah). */
    public function lepas()
    {
        $sebelumnya = KonteksPemda::namaPemda();
        KonteksPemda::hapus();
        ActivityLog::catatAman('pengaturan.pemda.lepas', null, 'sukses', [
            'pemda_sebelumnya' => $sebelumnya,
        ]);

        session()->flash('sukses', 'Konteks Pemda dilepas. Aplikasi menampilkan semua wilayah.');

        return $this->redirectRoute('pengaturan.pemda');
    }

    public function render()
    {
        $opsiKabupaten = $this->kodeProvinsi !== ''
            ? WilayahKabupaten::where('provinsi_kode', $this->kodeProvinsi)
                ->orderBy('nama')->get()
                ->map(fn ($k) => ['value' => $k->kode, 'label' => $k->nama])->all()
            : [];

        return view('livewire.pengaturan-pemda-index', [
            'daftarProvinsi' => WilayahProvinsi::orderBy('nama')->get(),
            'opsiKabupaten' => $opsiKabupaten,
            'konteksAktif' => KonteksPemda::aktif(),
            'namaPemda' => KonteksPemda::namaPemda(),
            'jumlahKecamatan' => KonteksPemda::daftarKecamatan()->count(),
            // Sekolah::count() & Guru::count() sudah terbatasi konteks
            // (global scope); pembandingnya diambil tanpa scope.
            'sekolahDalam' => Sekolah::count(),
            'sekolahTotal' => Sekolah::withoutGlobalScopes()->count(),
            'guruDalam' => Guru::count(),
            'guruTotal' => Guru::withoutGlobalScopes()->count(),
        ]);
    }
}
