<?php

// Komponen Livewire halaman penuh: Pengaturan Konteks Pemda — memilih
// wilayah kerja resmi aplikasi (provinsi atau kabupaten/kota) yang
// menjadi konteks seluruh modul. Hanya kabid_ptk (middleware role di
// route). Pilihan kabupaten dimuat reaktif dari provinsi terpilih;
// simpan/lepas tercatat di Jejak Audit.

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\Pengaturan;
use App\Models\Sekolah;
use App\Models\WilayahKabupaten;
use App\Models\WilayahProvinsi;
use App\Services\Branding;
use App\Services\KonteksPemda;
use App\Services\PengaturanSpk;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Pengaturan Pemda — PetaPenempatan ASN')]
class PengaturanPemdaLivewire extends Component
{
    use WithFileUploads;

    public string $level = 'kabupaten';

    public string $kodeProvinsi = '';

    public string $kodeKabupaten = '';

    /** Mode rekomendasi lintas jenjang (PengaturanSpk): 'larang'|'boleh'. */
    public string $lintasJenjang = 'larang';

    /** Warna aksen aplikasi (Branding, Redesign R0): hex #rrggbb. */
    public string $warnaAksen = '';

    /** Berkas logo baru yang dipilih (belum disimpan). */
    public $logoBaru;

    public function mount(): void
    {
        $this->level = KonteksPemda::level() ?: 'kabupaten';
        $this->kodeProvinsi = KonteksPemda::kodeProvinsi() ?: '';
        $this->kodeKabupaten = KonteksPemda::kodeKabupaten() ?: '';
        $this->lintasJenjang = PengaturanSpk::modeLintasJenjang();
        $this->warnaAksen = Branding::warna();
    }

    /** Simpan warna aksen & logo pemda (Redesign R0). */
    public function simpanBranding()
    {
        abort_unless(auth()->user()?->hasRole('kabid_ptk'), 403);

        $this->validate([
            'warnaAksen' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logoBaru' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg', 'max:1024'],
        ], [
            'warnaAksen.regex' => 'Warna harus dalam format hex, mis. #2563eb.',
            'logoBaru.mimes' => 'Logo harus berupa berkas PNG, JPG, atau SVG.',
            'logoBaru.max' => 'Ukuran logo maksimal 1 MB.',
        ]);

        Pengaturan::simpan(Branding::KUNCI_WARNA, strtolower($this->warnaAksen));

        if ($this->logoBaru !== null) {
            $lama = Branding::logoPath();
            $path = $this->logoBaru->store('branding', 'public');
            Pengaturan::simpan(Branding::KUNCI_LOGO, $path);
            if ($lama !== null && $lama !== $path) {
                Storage::disk('public')->delete($lama);
            }
            $this->reset('logoBaru');
        }

        ActivityLog::catatAman('pengaturan.branding', null, 'sukses', [
            'warna' => Branding::warna(),
            'logo' => Branding::logoPath(),
        ]);
        session()->flash('sukses', 'Branding disimpan: warna aksen & logo kini berlaku di seluruh aplikasi.');

        return $this->redirectRoute('pengaturan.pemda');
    }

    /** Hapus logo pemda (kembali ke ikon bawaan). */
    public function hapusLogo()
    {
        abort_unless(auth()->user()?->hasRole('kabid_ptk'), 403);

        $lama = Branding::logoPath();
        Pengaturan::simpan(Branding::KUNCI_LOGO, '');
        if ($lama !== null) {
            Storage::disk('public')->delete($lama);
        }

        ActivityLog::catatAman('pengaturan.branding', null, 'sukses', ['logo' => null]);
        session()->flash('sukses', 'Logo pemda dihapus; aplikasi kembali memakai ikon bawaan.');

        return $this->redirectRoute('pengaturan.pemda');
    }

    /** Simpan pilihan perilaku rekomendasi lintas jenjang. */
    public function simpanLintasJenjang()
    {
        $this->validate([
            'lintasJenjang' => ['required', 'in:larang,boleh'],
        ]);

        PengaturanSpk::simpanModeLintasJenjang($this->lintasJenjang);
        ActivityLog::catatAman('pengaturan.lintas_jenjang', null, 'sukses', [
            'mode' => $this->lintasJenjang,
        ]);

        session()->flash('sukses', 'Pengaturan rekomendasi lintas jenjang disimpan: '
            .PengaturanSpk::MODE_LINTAS_JENJANG[$this->lintasJenjang].'.');

        return $this->redirectRoute('pengaturan.pemda');
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
