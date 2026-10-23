<?php

// Pendaftaran akun mandiri guru (publik, untuk tamu).
// Guru yang belum punya akun — termasuk yang NIP-nya belum tercatat —
// boleh mendaftar; data guru & akun BARU dibuat setelah operator dinas
// (kabid_ptk) menyetujui di halaman Pendaftaran Guru.
// Route: GET/POST /daftar-guru

namespace App\Http\Controllers;

use App\Models\PendaftaranGuru;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\AnalisisKebutuhanGuru;
use Illuminate\Http\Request;

class PendaftaranGuruController extends Controller
{
    public function form()
    {
        return view('auth.daftar-guru', [
            'daftarSekolah' => Sekolah::orderBy('nama')->get(['id', 'nama', 'jenjang']),
            'daftarMapel' => array_merge(AnalisisKebutuhanGuru::daftarKanonis(), ['Bimbingan Konseling']),
        ]);
    }

    public function simpan(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nip' => ['nullable', 'string', 'max:30'],
            'mapel' => ['nullable', 'string', 'max:50'],
            'jenjang' => ['nullable', 'in:TK,SD,SMP'],
            'status_kepegawaian' => ['nullable', 'in:pns,pppk,pppk_paruh_waktu,honorer'],
            'sekolah_id' => ['nullable', 'exists:sekolah,id'],
            'nama_sekolah' => ['nullable', 'string', 'max:150'],
            'no_hp' => ['required', 'string', 'max:30'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi agar operator dapat menghubungi Anda.',
        ]);

        $nip = trim((string) ($data['nip'] ?? '')) ?: null;

        // NIP yang sudah punya akun tidak perlu mendaftar lagi.
        if ($nip && User::where('username', $nip)->exists()) {
            return back()->withInput()
                ->withErrors(['nip' => 'NIP ini sudah memiliki akun. Silakan masuk dengan NIP Anda sebagai username & password awal.']);
        }

        // Satu antrean aktif per NIP.
        if ($nip && PendaftaranGuru::where('nip', $nip)->where('status', 'menunggu')->exists()) {
            return back()->withInput()
                ->withErrors(['nip' => 'Pendaftaran dengan NIP ini sudah masuk antrean dan sedang menunggu persetujuan operator.']);
        }

        if (empty($data['sekolah_id']) && empty($data['nama_sekolah'])) {
            return back()->withInput()
                ->withErrors(['sekolah_id' => 'Pilih sekolah tempat bertugas, atau isi nama sekolah bila belum ada di daftar.']);
        }

        PendaftaranGuru::create([
            'nama' => $data['nama'],
            'nip' => $nip,
            'mapel' => ! empty($data['mapel']) ? AnalisisKebutuhanGuru::kanonis($data['mapel']) : null,
            'jenjang' => $data['jenjang'] ?? null,
            'status_kepegawaian' => $data['status_kepegawaian'] ?? null,
            'sekolah_id' => $data['sekolah_id'] ?? null,
            'nama_sekolah' => $data['nama_sekolah'] ?? null,
            'no_hp' => $data['no_hp'],
            'status' => 'menunggu',
        ]);

        return redirect()->route('daftar-guru')
            ->with('sukses', 'Pendaftaran diterima dan menunggu persetujuan operator dinas. Setelah disetujui, Anda dapat masuk dengan NIP sebagai username & password awal.');
    }
}
