<?php
// Controller Guru dengan scope berbeda per peran:
//   guru           → hanya data dirinya sendiri (boleh ubah)
//   kepala_sekolah → CRUD guru di sekolahnya sendiri
//   ketua_pgri     → lihat se-kecamatan (read-only)
//   kabid_ptk      → CRUD penuh (administrator)
//   kadis          → lihat semua (reviewer, read-only)

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Sekolah;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    // Aturan validasi dipakai ulang di store & update
    private function aturanValidasi(): array
    {
        return [
            'nip'                  => ['required', 'string', 'max:30'],
            'nama'                 => ['required', 'string', 'max:100'],
            'mapel'                => ['required', 'string', 'max:50'],
            'jenjang'              => ['required', 'in:SD,SMP,SMA'],
            'domisili_alamat'      => ['required', 'string', 'max:255'],
            'kecamatan'            => ['nullable', 'string', 'max:100'],
            'sekolah_id'           => ['nullable', 'exists:sekolah,id'],
            'domisili_lat'         => ['required', 'numeric', 'between:-90,90'],
            'domisili_lng'         => ['required', 'numeric', 'between:-180,180'],
            'sekolah_saat_ini'     => ['required', 'string', 'max:100'],
            'sekolah_saat_ini_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'sekolah_saat_ini_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Query dasar yang sudah dibatasi sesuai scope peran pengguna.
     */
    private function queryScope()
    {
        $user = auth()->user();
        $query = Guru::with('sekolah');

        if ($user->role === 'guru') {
            $query->where('id', $user->guru_id);
        } elseif ($user->role === 'kepala_sekolah') {
            // Hanya guru yang bertugas di sekolahnya
            $query->where('sekolah_id', $user->sekolah_id);
        } elseif ($user->role === 'ketua_pgri') {
            $query->where('kecamatan', $user->kecamatan);
        }
        // kabid_ptk, kadis: semua data

        return $query->orderBy('nama');
    }

    public function index(Request $request)
    {
        $query = $this->queryScope();

        // Pencarian teks: nama, NIP, mapel, alamat domisili, sekolah saat ini
        $kataKunci = trim($request->get('q', ''));
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('nip', 'like', "%{$kataKunci}%")
                    ->orWhere('mapel', 'like', "%{$kataKunci}%")
                    ->orWhere('domisili_alamat', 'like', "%{$kataKunci}%")
                    ->orWhere('sekolah_saat_ini', 'like', "%{$kataKunci}%");
            });
        }

        // Filter kecamatan (disembunyikan di view jika scope peran sudah tetap)
        $filterKecamatan = $request->get('kecamatan', '');
        if ($filterKecamatan !== '') {
            $query->where('kecamatan', $filterKecamatan);
        }

        $daftarGuru = $query->paginate(15)->withQueryString();
        $daftarKecamatan = $this->daftarKecamatan();

        return view('guru.index', compact('daftarGuru', 'daftarKecamatan'));
    }

    /**
     * Daftar kecamatan unik dari data sekolah (untuk filter & dropdown form).
     */
    private function daftarKecamatan()
    {
        return Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');
    }

    public function create()
    {
        return view('guru.form', [
            'guru' => new Guru(),
            'aksi' => 'Tambah',
            'daftarSekolah' => Sekolah::orderBy('nama')->get(),
            'daftarKecamatan' => $this->daftarKecamatan(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturanValidasi());
        $request->validate(['nip' => ['unique:guru,nip']]);

        // Kepala sekolah: guru baru otomatis masuk sekolahnya
        if (auth()->user()->role === 'kepala_sekolah') {
            $data['sekolah_id'] = auth()->user()->sekolah_id;
        }

        Guru::create($data);

        return redirect()->route('guru.index')
            ->with('sukses', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        $this->otorisasiAkses($guru);
        return view('guru.form', [
            'guru' => $guru,
            'aksi' => 'Ubah',
            'daftarSekolah' => Sekolah::orderBy('nama')->get(),
            'daftarKecamatan' => $this->daftarKecamatan(),
        ]);
    }

    public function update(Request $request, Guru $guru)
    {
        $this->otorisasiAkses($guru);

        $data = $request->validate($this->aturanValidasi());
        $request->validate(['nip' => ['unique:guru,nip,' . $guru->id]]);

        // Kepala sekolah tidak bisa memindahkan guru ke sekolah lain
        if (auth()->user()->role === 'kepala_sekolah') {
            $data['sekolah_id'] = auth()->user()->sekolah_id;
        }

        $guru->update($data);

        return redirect()->route('guru.index')
            ->with('sukses', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $this->otorisasiAkses($guru);
        $guru->delete();

        return redirect()->route('guru.index')
            ->with('sukses', 'Data guru berhasil dihapus.');
    }

    /**
     * Batasan akses per peran untuk ubah/hapus:
     *   guru           → hanya dirinya sendiri
     *   kepala_sekolah → hanya guru di sekolahnya
     *   kabid_ptk      → semua (diatur middleware)
     */
    private function otorisasiAkses(Guru $guru): void
    {
        $user = auth()->user();
        if ($user->role === 'guru' && $guru->id !== $user->guru_id) {
            abort(403, 'Anda hanya boleh mengelola data diri sendiri.');
        }
        if ($user->role === 'kepala_sekolah' && $guru->sekolah_id !== $user->sekolah_id) {
            abort(403, 'Anda hanya boleh mengelola guru di sekolah Anda.');
        }
    }
}
