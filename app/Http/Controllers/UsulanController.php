<?php
// Controller Usulan Penempatan guru (alur persetujuan berlapis):
//   mengajukan      → guru (diri sendiri), kepala_sekolah (guru sekolahnya), kabid_ptk
//   verifikasi      → kabid_ptk (diajukan → diverifikasi)
//   setujui         → kadis (diverifikasi → disetujui; data guru otomatis dimutasi)
//   tolak           → kabid_ptk/kadis (dari diajukan/diverifikasi → ditolak)
//   lihat           → semua peran sesuai scope (ketua_pgri: se-kecamatan, read-only)
// Route: /usulan

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\UsulanPenempatan;
use Illuminate\Http\Request;

class UsulanController extends Controller
{
    /**
     * Query dasar yang sudah dibatasi sesuai scope peran pengguna.
     */
    private function queryScope()
    {
        $user = auth()->user();
        $query = UsulanPenempatan::with(['guru', 'sekolah', 'pengaju']);

        if ($user->role === 'guru') {
            $query->where('guru_id', $user->guru_id);
        } elseif ($user->role === 'kepala_sekolah') {
            $query->whereHas('guru', fn ($q) => $q->where('sekolah_id', $user->sekolah_id));
        } elseif ($user->role === 'ketua_pgri') {
            $query->whereHas('guru', fn ($q) => $q->where('kecamatan', $user->kecamatan));
        }
        // kabid_ptk, kadis: semua data

        return $query->latest();
    }

    public function index(Request $request)
    {
        $query = $this->queryScope();

        // Pencarian nama/NIP guru
        $kataKunci = trim($request->get('q', ''));
        if ($kataKunci !== '') {
            $query->whereHas('guru', function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('nip', 'like', "%{$kataKunci}%");
            });
        }

        // Filter status
        $filterStatus = $request->get('status', '');
        if ($filterStatus !== '' && array_key_exists($filterStatus, UsulanPenempatan::STATUS)) {
            $query->where('status', $filterStatus);
        }

        $daftarUsulan = $query->paginate(15)->withQueryString();

        return view('usulan.index', compact('daftarUsulan'));
    }

    public function create()
    {
        return view('usulan.form', [
            'usulan' => new UsulanPenempatan(),
            'daftarGuru' => $this->guruBisaDiusulkan(),
            'daftarSekolah' => Sekolah::orderBy('nama')->get(),
        ]);
    }

    /**
     * Pilihan guru yang boleh diusulkan sesuai peran:
     * guru → dirinya sendiri; kepala_sekolah → guru di sekolahnya;
     * kabid_ptk → semua (kecuali yang sudah punya usulan aktif).
     */
    private function guruBisaDiusulkan()
    {
        $user = auth()->user();
        $query = Guru::orderBy('nama');

        if ($user->role === 'guru') {
            $query->where('id', $user->guru_id);
        } elseif ($user->role === 'kepala_sekolah') {
            $query->where('sekolah_id', $user->sekolah_id);
        }

        return $query->whereNotIn('id', function ($q) {
            $q->select('guru_id')->from('usulan_penempatan')
                ->whereIn('status', UsulanPenempatan::AKTIF);
        })->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guru_id'    => ['required', 'exists:guru,id'],
            'sekolah_id' => ['required', 'exists:sekolah,id'],
            'alasan'     => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $guru = Guru::findOrFail($data['guru_id']);
        $this->otorisasiAjukan($guru);

        // Sekolah tujuan harus berbeda & benar-benar butuh mapel guru ini
        if ($guru->sekolah_id && (int) $data['sekolah_id'] === (int) $guru->sekolah_id) {
            return back()->withErrors(['sekolah_id' => 'Sekolah tujuan sama dengan sekolah saat ini.'])
                ->withInput();
        }
        $sekolah = Sekolah::with('kebutuhan')->findOrFail($data['sekolah_id']);
        if ($sekolah->butuhMapel($guru->mapel) <= 0) {
            return back()->withErrors(['sekolah_id' =>
                "Sekolah ini tidak membutuhkan guru {$guru->mapel}."])->withInput();
        }

        // Satu guru hanya boleh punya satu usulan aktif
        if (UsulanPenempatan::where('guru_id', $guru->id)
                ->whereIn('status', UsulanPenempatan::AKTIF)->exists()) {
            return back()->withErrors(['guru_id' => 'Guru ini masih punya usulan yang berjalan.'])
                ->withInput();
        }

        UsulanPenempatan::create([
            'guru_id' => $guru->id, 'sekolah_id' => $sekolah->id,
            'alasan' => $data['alasan'], 'status' => 'diajukan',
            'diajukan_oleh' => auth()->id(),
        ]);

        return redirect()->route('usulan.index')
            ->with('sukses', 'Usulan penempatan berhasil diajukan.');
    }

    public function show(UsulanPenempatan $usulan)
    {
        $this->otorisasiLihat($usulan);
        $usulan->load(['guru', 'sekolah.kebutuhan', 'pengaju', 'pemroses']);

        return view('usulan.show', compact('usulan'));
    }

    /**
     * Kabid: diajukan → diverifikasi.
     */
    public function verifikasi(Request $request, UsulanPenempatan $usulan)
    {
        abort_unless($usulan->status === 'diajukan', 422, 'Usulan sudah diproses.');
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        $usulan->update([
            'status' => 'diverifikasi', 'diproses_oleh' => auth()->id(),
            'catatan' => $data['catatan'] ?? null,
        ]);

        return back()->with('sukses', 'Usulan diverifikasi, menunggu persetujuan Kadis.');
    }

    /**
     * Kadis: diverifikasi → disetujui. Data guru otomatis dimutasi ke sekolah tujuan.
     */
    public function setujui(Request $request, UsulanPenempatan $usulan)
    {
        abort_unless($usulan->status === 'diverifikasi', 422, 'Usulan belum diverifikasi Kabid.');
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        $usulan->update([
            'status' => 'disetujui', 'diproses_oleh' => auth()->id(),
            'catatan' => $data['catatan'] ?? null,
        ]);

        // Mutasi: pindahkan guru ke sekolah tujuan
        $sekolah = $usulan->sekolah;
        $usulan->guru->update([
            'sekolah_id' => $sekolah->id,
            'sekolah_saat_ini' => $sekolah->nama,
            'sekolah_saat_ini_lat' => $sekolah->lat,
            'sekolah_saat_ini_lng' => $sekolah->lng,
        ]);

        return back()->with('sukses', 'Usulan disetujui, data guru telah dimutasi.');
    }

    /**
     * Kabid/Kadis: diajukan/diverifikasi → ditolak (catatan wajib).
     */
    public function tolak(Request $request, UsulanPenempatan $usulan)
    {
        abort_unless($usulan->masihAktif(), 422, 'Usulan sudah final.');
        $data = $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        $usulan->update([
            'status' => 'ditolak', 'diproses_oleh' => auth()->id(),
            'catatan' => $data['catatan'],
        ]);

        return back()->with('sukses', 'Usulan ditolak.');
    }

    /**
     * Pengaju harus berhak atas guru yang diusulkan.
     */
    private function otorisasiAjukan(Guru $guru): void
    {
        $user = auth()->user();
        if ($user->role === 'guru' && $guru->id !== $user->guru_id) {
            abort(403, 'Anda hanya boleh mengajukan untuk diri sendiri.');
        }
        if ($user->role === 'kepala_sekolah' && $guru->sekolah_id !== $user->sekolah_id) {
            abort(403, 'Anda hanya boleh mengajukan guru di sekolah Anda.');
        }
    }

    /**
     * Melihat usulan dibatasi scope yang sama seperti index.
     */
    private function otorisasiLihat(UsulanPenempatan $usulan): void
    {
        $user = auth()->user();
        $guru = $usulan->guru;
        if ($user->role === 'guru' && $guru->id !== $user->guru_id) {
            abort(403);
        }
        if ($user->role === 'kepala_sekolah' && $guru->sekolah_id !== $user->sekolah_id) {
            abort(403);
        }
        if ($user->role === 'ketua_pgri' && $guru->kecamatan !== $user->kecamatan) {
            abort(403);
        }
    }
}
