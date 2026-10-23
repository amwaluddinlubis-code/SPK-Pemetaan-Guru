<?php

// Halaman operator: antrean pendaftaran akun guru mandiri.
// Menyetujui = data guru dibuat (atau ditautkan bila NIP sudah ada)
// dan akun login dibuatkan (username & password bawaan = NIP).

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\PendaftaranGuru as ModelPendaftaran;
use App\Services\AkunGuru;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pendaftaran Guru — PetaPenempatan ASN')]
class PendaftaranGuru extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'menunggu';

    /** Catatan penolakan per baris (diisi operator sebelum menekan Tolak). */
    public array $catatan = [];

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function setujui(int $id): void
    {
        $this->otorisasiOperator();

        $pendaftaran = ModelPendaftaran::where('status', 'menunggu')->findOrFail($id);

        DB::transaction(function () use ($pendaftaran) {
            $guru = $pendaftaran->nip
                ? Guru::where('nip', $pendaftaran->nip)->first()
                : null;

            if (! $guru) {
                $guru = Guru::create([
                    'nip' => $pendaftaran->nip,
                    'nama' => $pendaftaran->nama,
                    'mapel' => $pendaftaran->mapel ?: 'Guru Kelas',
                    'jenjang' => $pendaftaran->jenjang ?: 'SD',
                    'status_kepegawaian' => $pendaftaran->status_kepegawaian ?: 'pppk',
                    'status_pernikahan' => 'belum_kawin',
                    'sekolah_id' => $pendaftaran->sekolah_id,
                    'sekolah_saat_ini' => $pendaftaran->namaSekolah(),
                ]);
            }

            $akun = AkunGuru::buatkan($guru);

            $pendaftaran->update([
                'status' => 'disetujui',
                'guru_id' => $guru->id,
                'diproses_oleh' => auth()->id(),
                'catatan_operator' => $akun
                    ? 'Akun aktif: username & password awal = NIP guru.'
                    : 'Data guru dibuat; akun menyusul setelah operator melengkapi NIP.',
            ]);

            // Jejak audit tidak menyimpan nilai NIP — cukup nama & guru_id.
            ActivityLog::catat('pendaftaran.disetujui', $pendaftaran, 'sukses', [
                'nama' => $pendaftaran->nama,
                'guru_id' => $guru->id,
            ]);
        });

        session()->flash('sukses', "Pendaftaran {$pendaftaran->nama} disetujui.");
    }

    public function tolak(int $id): void
    {
        $this->otorisasiOperator();

        $pendaftaran = ModelPendaftaran::where('status', 'menunggu')->findOrFail($id);

        $pendaftaran->update([
            'status' => 'ditolak',
            'diproses_oleh' => auth()->id(),
            'catatan_operator' => trim((string) ($this->catatan[$id] ?? '')) ?: null,
        ]);

        ActivityLog::catat('pendaftaran.ditolak', $pendaftaran, 'sukses', [
            'nama' => $pendaftaran->nama,
        ]);

        session()->flash('sukses', "Pendaftaran {$pendaftaran->nama} ditolak.");
    }

    public function render()
    {
        $this->otorisasiOperator();

        $daftar = ModelPendaftaran::with(['sekolah', 'penilai'])
            ->when($this->tab === 'menunggu',
                fn ($q) => $q->where('status', 'menunggu')->orderBy('created_at'),
                fn ($q) => $q->where('status', '!=', 'menunggu')->orderByDesc('updated_at'))
            ->paginate(15);

        return view('livewire.pendaftaran-guru', [
            'daftar' => $daftar,
            'jumlahMenunggu' => ModelPendaftaran::where('status', 'menunggu')->count(),
        ]);
    }

    private function otorisasiOperator(): void
    {
        abort_unless(auth()->user()?->role === 'kabid_ptk', 403);
    }
}
