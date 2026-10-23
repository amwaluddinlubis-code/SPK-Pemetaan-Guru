<?php

// Controller Pengaturan Pemda: memilih wilayah kerja resmi aplikasi
// (provinsi atau kabupaten/kota) yang menjadi konteks seluruh modul.
// Hanya administrator (kabid_ptk, dibatasi middleware 'role' di routes).

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\WilayahKabupaten;
use App\Models\WilayahProvinsi;
use App\Services\KonteksPemda;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    /** Halaman pengaturan konteks Pemda. */
    public function edit()
    {
        return view('pengaturan.pemda', [
            'daftarProvinsi' => WilayahProvinsi::orderBy('nama')->get(),
            'level' => old('level', KonteksPemda::level()),
            'kodeProvinsi' => old('kode_provinsi', KonteksPemda::kodeProvinsi()),
            'kodeKabupaten' => old('kode_kabupaten', KonteksPemda::kodeKabupaten()),
            'namaPemda' => KonteksPemda::namaPemda(),
            'konteksAktif' => KonteksPemda::aktif(),
            'jumlahKecamatan' => KonteksPemda::daftarKecamatan()->count(),
            // Sekolah::count() & Guru::count() sudah terbatasi konteks (global scope);
            // pembandingnya diambil tanpa scope.
            'sekolahDalam' => Sekolah::count(),
            'sekolahTotal' => Sekolah::withoutGlobalScopes()->count(),
            'guruDalam' => Guru::count(),
            'guruTotal' => Guru::withoutGlobalScopes()->count(),
        ]);
    }

    /** Simpan konteks Pemda terpilih. */
    public function update(Request $request)
    {
        $data = $request->validate([
            'level' => ['required', 'in:provinsi,kabupaten'],
            'kode_provinsi' => ['required', 'exists:wilayah_provinsi,kode'],
            'kode_kabupaten' => ['nullable', 'required_if:level,kabupaten', 'exists:wilayah_kabupaten,kode'],
        ], [
            'kode_kabupaten.required_if' => 'Pilih kabupaten/kota untuk level Kabupaten/Kota.',
        ]);

        if ($data['level'] === 'kabupaten') {
            $kab = WilayahKabupaten::find($data['kode_kabupaten']);
            if ($kab === null || $kab->provinsi_kode !== $data['kode_provinsi']) {
                return back()->withInput()
                    ->withErrors(['kode_kabupaten' => 'Kabupaten/kota tidak berada di provinsi terpilih.']);
            }
        }

        KonteksPemda::simpan($data['level'], $data['kode_provinsi'], $data['kode_kabupaten'] ?? null);
        ActivityLog::catatAman('pengaturan.pemda', null, 'sukses', [
            'pemda' => KonteksPemda::namaPemda(),
            'level' => $data['level'],
        ]);

        return redirect()->route('pengaturan.pemda')
            ->with('sukses', 'Konteks Pemda disimpan: '.KonteksPemda::namaPemda().'. Data di luar wilayah ini sekarang disembunyikan.');
    }

    /** Lepas konteks Pemda (aplikasi kembali menampilkan semua wilayah). */
    public function destroy()
    {
        $sebelumnya = KonteksPemda::namaPemda();
        KonteksPemda::hapus();
        ActivityLog::catatAman('pengaturan.pemda.lepas', null, 'sukses', [
            'pemda_sebelumnya' => $sebelumnya,
        ]);

        return redirect()->route('pengaturan.pemda')
            ->with('sukses', 'Konteks Pemda dilepas. Aplikasi menampilkan semua wilayah.');
    }
}
