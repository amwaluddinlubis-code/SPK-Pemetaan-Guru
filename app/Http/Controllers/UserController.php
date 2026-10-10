<?php
// Controller CRUD Pengguna (akun login 5 peran).
// Hanya untuk kadis & kabid_ptk (dibatasi middleware 'role' di routes).
// Route: /pengguna

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['guru', 'sekolah']);

        // Pencarian teks: nama, email
        $kataKunci = trim($request->get('q', ''));
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('name', 'like', "%{$kataKunci}%")
                    ->orWhere('email', 'like', "%{$kataKunci}%");
            });
        }

        // Filter peran
        $filterRole = $request->get('role', '');
        if ($filterRole !== '' && array_key_exists($filterRole, User::ROLES)) {
            $query->where('role', $filterRole);
        }

        $daftarUser = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('pengguna.index', compact('daftarUser'));
    }

    public function create()
    {
        return view('pengguna.form', [
            'user' => new User(['role' => 'guru']),
            'aksi' => 'Tambah',
            'daftarGuru' => Guru::orderBy('nama')->get(),
            'daftarSekolah' => Sekolah::orderBy('nama')->get(),
            'daftarKecamatan' => $this->daftarKecamatan(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('pengguna.index')
            ->with('sukses', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna)
    {
        // Jangan biarkan admin menonaktifkan dirinya sendiri
        return view('pengguna.form', [
            'user' => $pengguna,
            'aksi' => 'Ubah',
            'daftarGuru' => Guru::orderBy('nama')->get(),
            'daftarSekolah' => Sekolah::orderBy('nama')->get(),
            'daftarKecamatan' => $this->daftarKecamatan(),
        ]);
    }

    public function update(Request $request, User $pengguna)
    {
        $data = $this->validasi($request, $pengguna->id);

        // Password opsional saat ubah: kosong = tidak diganti
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $pengguna->update($data);

        return redirect()->route('pengguna.index')
            ->with('sukses', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        // Cegah menghapus akun sendiri
        if ($pengguna->id === auth()->id()) {
            return back()->withErrors(['hapus' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $pengguna->delete();

        return redirect()->route('pengguna.index')
            ->with('sukses', 'Akun pengguna berhasil dihapus.');
    }

    /**
     * Daftar kecamatan unik dari data sekolah (untuk dropdown form Ketua PGRI).
     */
    private function daftarKecamatan()
    {
        return Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');
    }

    private function validasi(Request $request, ?int $kecualiId = null): array
    {
        $aturan = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255',
                        Rule::unique('users', 'email')->ignore($kecualiId)],
            'role'  => ['required', Rule::in(array_keys(User::ROLES))],
            // Relasi opsional sesuai peran
            'guru_id'    => ['nullable', 'exists:guru,id'],
            'sekolah_id' => ['nullable', 'exists:sekolah,id'],
            'kecamatan'  => ['nullable', 'string', 'max:100'],
        ];

        // Password wajib saat tambah, opsional saat ubah
        if ($kecualiId) {
            $aturan['password'] = ['nullable', 'string', 'min:6'];
        } else {
            $aturan['password'] = ['required', 'string', 'min:6'];
        }

        $data = $request->validate($aturan);

        // Bersihkan relasi yang tidak relevan dengan peran
        if (($data['role'] ?? null) !== 'guru') {
            $data['guru_id'] = null;
        }
        if (($data['role'] ?? null) !== 'kepala_sekolah') {
            $data['sekolah_id'] = null;
        }
        if (($data['role'] ?? null) !== 'ketua_pgri') {
            $data['kecamatan'] = null;
        }

        return $data;
    }
}
