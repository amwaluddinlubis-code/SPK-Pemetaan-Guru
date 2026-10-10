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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            'usulan' => new UsulanPenempatan,
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
            'guru_id' => ['required', 'exists:guru,id'],
            'sekolah_id' => ['required', 'exists:sekolah,id'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        // FLOW-002: kunci baris guru selama pemeriksaan + pembuatan agar dua
        // pengajuan bersamaan untuk guru yang sama tidak lolos bersamaan.
        try {
            DB::transaction(function () use ($data) {
                $guru = Guru::whereKey($data['guru_id'])->lockForUpdate()->firstOrFail();
                $this->otorisasiAjukan($guru);

                // Sekolah tujuan harus berbeda & benar-benar butuh mapel guru ini
                if ($guru->sekolah_id && (int) $data['sekolah_id'] === (int) $guru->sekolah_id) {
                    throw ValidationException::withMessages(['sekolah_id' => 'Sekolah tujuan sama dengan sekolah saat ini.']);
                }
                $sekolah = Sekolah::with('kebutuhan')->findOrFail($data['sekolah_id']);
                if ($sekolah->butuhMapel($guru->mapel) <= 0) {
                    throw ValidationException::withMessages(['sekolah_id' => "Sekolah ini tidak membutuhkan guru {$guru->mapel}."]);
                }

                // Satu guru hanya boleh punya satu usulan aktif (dicek ulang di dalam lock)
                if (UsulanPenempatan::where('guru_id', $guru->id)
                    ->whereIn('status', UsulanPenempatan::AKTIF)->exists()) {
                    throw ValidationException::withMessages(['guru_id' => 'Guru ini masih punya usulan yang berjalan.']);
                }

                UsulanPenempatan::create([
                    'guru_id' => $guru->id, 'sekolah_id' => $sekolah->id,
                    'alasan' => $data['alasan'], 'status' => 'diajukan',
                    'diajukan_oleh' => auth()->id(),
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e; // 403/404 dari otorisasi tetap jadi respons HTTP semestinya
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Pengajuan gagal: '.$e->getMessage())->withInput();
        }

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
     * Kabid: diajukan → diverifikasi. Dikunci dalam transaction (FLOW-001).
     */
    public function verifikasi(Request $request, UsulanPenempatan $usulan)
    {
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        try {
            DB::transaction(function () use ($usulan, $data) {
                $u = UsulanPenempatan::whereKey($usulan->id)->lockForUpdate()->firstOrFail();
                if ($u->status !== 'diajukan') {
                    throw new \RuntimeException('Usulan sudah diproses sebelumnya.');
                }
                $u->update([
                    'status' => 'diverifikasi', 'diproses_oleh' => auth()->id(),
                    'catatan' => $data['catatan'] ?? null,
                ]);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Verifikasi gagal: '.$e->getMessage());
        }

        return back()->with('sukses', 'Usulan diverifikasi, menunggu persetujuan Kadis.');
    }

    /**
     * Kadis: diverifikasi → disetujui. Data guru otomatis dimutasi ke sekolah
     * tujuan. Rekomendasi + guru dikunci, status divalidasi ulang (FLOW-001).
     */
    public function setujui(Request $request, UsulanPenempatan $usulan)
    {
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        try {
            DB::transaction(function () use ($usulan, $data) {
                $u = UsulanPenempatan::whereKey($usulan->id)->lockForUpdate()->firstOrFail();
                if ($u->status !== 'diverifikasi') {
                    throw new \RuntimeException(
                        'Usulan belum diverifikasi Kabid atau sudah diproses sebelumnya.');
                }

                $guru = Guru::whereKey($u->guru_id)->lockForUpdate()->firstOrFail();
                $sekolah = Sekolah::findOrFail($u->sekolah_id);

                $u->update([
                    'status' => 'disetujui', 'diproses_oleh' => auth()->id(),
                    'catatan' => $data['catatan'] ?? null,
                ]);

                // Mutasi: pindahkan guru ke sekolah tujuan (atomik dengan status)
                $guru->update([
                    'sekolah_id' => $sekolah->id,
                    'sekolah_saat_ini' => $sekolah->nama,
                    'sekolah_saat_ini_lat' => $sekolah->lat,
                    'sekolah_saat_ini_lng' => $sekolah->lng,
                ]);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Persetujuan gagal: '.$e->getMessage());
        }

        return back()->with('sukses', 'Usulan disetujui, data guru telah dimutasi.');
    }

    /**
     * Kabid/Kadis: diajukan/diverifikasi → ditolak (catatan wajib).
     * Dikunci dalam transaction (FLOW-001).
     */
    public function tolak(Request $request, UsulanPenempatan $usulan)
    {
        $data = $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        try {
            DB::transaction(function () use ($usulan, $data) {
                $u = UsulanPenempatan::whereKey($usulan->id)->lockForUpdate()->firstOrFail();
                if (! $u->masihAktif()) {
                    throw new \RuntimeException('Usulan sudah final, tidak bisa ditolak.');
                }
                $u->update([
                    'status' => 'ditolak', 'diproses_oleh' => auth()->id(),
                    'catatan' => $data['catatan'],
                ]);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Penolakan gagal: '.$e->getMessage());
        }

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
