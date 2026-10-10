<?php
// Controller Sekolah dengan scope berbeda per peran:
//   kepala_sekolah → hanya sekolahnya (bisa ubah + atur kebutuhan guru)
//   ketua_pgri     → lihat se-kecamatan (read-only)
//   guru           → lihat semua (read-only, untuk rekomendasi)
//   kabid_ptk      → CRUD penuh
//   kadis          → CRUD penuh

namespace App\Http\Controllers;

use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SekolahController extends Controller
{
    /**
     * Query dasar yang sudah dibatasi sesuai scope peran pengguna.
     */
    private function queryScope()
    {
        $user = auth()->user();
        $query = Sekolah::with('kebutuhan');

        if ($user->role === 'kepala_sekolah') {
            // Kepala sekolah hanya melihat sekolahnya sendiri
            $query->where('id', $user->sekolah_id);
        } elseif ($user->role === 'ketua_pgri') {
            // Ketua PGRI hanya melihat se-kecamatan
            $query->where('kecamatan', $user->kecamatan);
        }
        // guru, kabid_ptk, kadis: semua data

        return $query->orderBy('nama');
    }

    public function index()
    {
        $daftarSekolah = $this->queryScope()->paginate(15);
        return view('sekolah.index', compact('daftarSekolah'));
    }

    public function create()
    {
        return view('sekolah.form', ['sekolah' => new Sekolah(), 'aksi' => 'Tambah']);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            $sekolah = Sekolah::create([
                'npsn' => $data['npsn'], 'nama' => $data['nama'],
                'alamat' => $data['alamat'], 'kecamatan' => $data['kecamatan'] ?? null,
                'lat' => $data['lat'], 'lng' => $data['lng'],
            ]);
            $this->simpanKebutuhan($sekolah, $data['kebutuhan'] ?? []);
        });

        return redirect()->route('sekolah.index')
            ->with('sukses', 'Data sekolah berhasil ditambahkan.');
    }

    public function edit(Sekolah $sekolah)
    {
        $this->otorisasiUbah($sekolah);
        $sekolah->load('kebutuhan');
        return view('sekolah.form', ['sekolah' => $sekolah, 'aksi' => 'Ubah']);
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $this->otorisasiUbah($sekolah);
        $data = $this->validasi($request, $sekolah->id);

        DB::transaction(function () use ($sekolah, $data) {
            $sekolah->update([
                'npsn' => $data['npsn'], 'nama' => $data['nama'],
                'alamat' => $data['alamat'], 'kecamatan' => $data['kecamatan'] ?? null,
                'lat' => $data['lat'], 'lng' => $data['lng'],
            ]);
            // Hapus kebutuhan lama, simpan yang baru (cara paling sederhana & aman)
            $sekolah->kebutuhan()->delete();
            $this->simpanKebutuhan($sekolah, $data['kebutuhan'] ?? []);
        });

        return redirect()->route('sekolah.index')
            ->with('sukses', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete(); // kebutuhan ikut terhapus (cascade)

        return redirect()->route('sekolah.index')
            ->with('sukses', 'Data sekolah berhasil dihapus.');
    }

    /**
     * Kepala sekolah hanya boleh mengubah sekolahnya sendiri.
     */
    private function otorisasiUbah(Sekolah $sekolah): void
    {
        $user = auth()->user();
        if ($user->role === 'kepala_sekolah' && $sekolah->id !== $user->sekolah_id) {
            abort(403, 'Anda hanya boleh mengubah sekolah Anda sendiri.');
        }
    }

    private function validasi(Request $request, ?int $kecualiId = null): array
    {
        return $request->validate([
            'npsn'   => ['required', 'string', 'max:20', 'unique:sekolah,npsn' . ($kecualiId ? ',' . $kecualiId : '')],
            'nama'   => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'lat'    => ['required', 'numeric', 'between:-90,90'],
            'lng'    => ['required', 'numeric', 'between:-180,180'],
            'kebutuhan'        => ['nullable', 'array'],
            'kebutuhan.*.mapel'  => ['required_with:kebutuhan.*.jumlah', 'string', 'max:50'],
            'kebutuhan.*.jumlah' => ['required_with:kebutuhan.*.mapel', 'integer', 'min:1'],
        ]);
    }

    /**
     * Simpan daftar kebutuhan dari form ke tabel kebutuhan_guru.
     * Baris kosong (tanpa mapel) dilewati.
     */
    private function simpanKebutuhan(Sekolah $sekolah, array $daftar): void
    {
        foreach ($daftar as $baris) {
            if (empty($baris['mapel'])) {
                continue;
            }
            KebutuhanGuru::create([
                'sekolah_id' => $sekolah->id,
                'mapel'      => $baris['mapel'],
                'jumlah'     => (int) $baris['jumlah'],
            ]);
        }
    }
}
