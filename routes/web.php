<?php

// Daftar rute aplikasi dengan hak akses per peran.
//   guru           → data diri sendiri
//   kepala_sekolah → CRUD guru di sekolahnya + kelola sekolahnya
//   ketua_pgri     → view se-kecamatan (read-only)
//   kabid_ptk      → administrator (CRUD penuh + kelola pengguna)
//   kadis          → reviewer (read-only semua)
//   pemda          → eksekutif daerah (dashboard ringkasan, read-only)

use App\Http\Controllers\AksesibilitasController;
use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistribusiController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KualitasDataController;
use App\Http\Controllers\PemdaController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ProyeksiController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SimulasiController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\SwapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsulanController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

// --- Halaman login (hanya untuk tamu/belum login) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Batasi percobaan login untuk menghambat brute-force berbasis IP.
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.proses');

    // Lupa & reset password via email (tamu)
    Route::get('/lupa-password', [PasswordResetController::class, 'mintaForm'])
        ->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'kirimTautan'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'simpanBaru'])
        ->middleware('throttle:10,1')
        ->name('password.update');
});

// --- Halaman dalam aplikasi (wajib login) ---
Route::middleware('auth')->group(function () {
    // Dashboard + peta rekomendasi: semua peran
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profil & ganti password sendiri: semua peran
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');
    Route::put('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // --- Modul Pemda: dashboard eksekutif (ringkasan se-kabupaten, read-only) ---
    // Lihat: pemda, kadis
    Route::get('/pemda', [PemdaController::class, 'index'])->name('pemda.dashboard')
        ->middleware('role:pemda,kadis');

    // --- Data Guru ---
    // Lihat: semua peran (controller membatasi cakupan data)
    // Referensi wilayah administrasi (read-only, semua peran): halaman
    // bertabel Livewire + API dropdown berjenjang untuk formulir.
    Route::get('/wilayah/provinsi', [WilayahController::class, 'provinsi'])->name('wilayah.provinsi');
    Route::get('/wilayah/kabupaten', [WilayahController::class, 'kabupaten'])->name('wilayah.kabupaten');
    Route::get('/wilayah/kecamatan', [WilayahController::class, 'kecamatan'])->name('wilayah.kecamatan');
    Route::get('/wilayah/desa', [WilayahController::class, 'desa'])->name('wilayah.desa');
    Route::get('/wilayah/api/provinsi', [WilayahController::class, 'apiProvinsi'])->name('wilayah.api.provinsi');
    Route::get('/wilayah/api/kabupaten', [WilayahController::class, 'apiKabupaten'])->name('wilayah.api.kabupaten');
    Route::get('/wilayah/api/kecamatan', [WilayahController::class, 'apiKecamatan'])->name('wilayah.api.kecamatan');
    Route::get('/wilayah/api/desa', [WilayahController::class, 'apiDesa'])->name('wilayah.api.desa');

    Route::get('/guru', [GuruController::class, 'index'])->name('guru.index')
        ->middleware('role:guru,kepala_sekolah,ketua_pgri,kabid_ptk,kadis');
    // Tambah: kabid_ptk, kepala_sekolah (guru baru masuk sekolahnya)
    Route::get('/guru/create', [GuruController::class, 'create'])->name('guru.create')
        ->middleware('role:kabid_ptk,kepala_sekolah');
    Route::post('/guru', [GuruController::class, 'store'])->name('guru.store')
        ->middleware('role:kabid_ptk,kepala_sekolah');
    // Import Excel: khusus kabid_ptk (administrator)
    Route::get('/guru/import', [GuruController::class, 'importForm'])->name('guru.import')
        ->middleware('role:kabid_ptk');
    Route::post('/guru/import', [GuruController::class, 'import'])->name('guru.import.proses')
        ->middleware('role:kabid_ptk');
    Route::get('/guru/contoh-template', [GuruController::class, 'unduhTemplate'])->name('guru.template')
        ->middleware('role:kabid_ptk');
    // Export Excel & cetak/PDF: kabid_ptk, kepala_sekolah, kadis (reviewer)
    Route::get('/guru/export', [GuruController::class, 'export'])->name('guru.export')
        ->middleware('role:kabid_ptk,kepala_sekolah,kadis');
    Route::get('/guru/cetak', [GuruController::class, 'cetak'])->name('guru.cetak')
        ->middleware('role:kabid_ptk,kepala_sekolah,kadis');
    // Ubah: guru (milik sendiri), kepala_sekolah (di sekolahnya), kabid_ptk
    Route::get('/guru/{guru}/edit', [GuruController::class, 'edit'])->name('guru.edit')
        ->middleware('role:guru,kepala_sekolah,kabid_ptk');
    Route::put('/guru/{guru}', [GuruController::class, 'update'])->name('guru.update')
        ->middleware('role:guru,kepala_sekolah,kabid_ptk');
    // Hapus: kabid_ptk, kepala_sekolah (di sekolahnya)
    Route::delete('/guru/{guru}', [GuruController::class, 'destroy'])->name('guru.destroy')
        ->middleware('role:kabid_ptk,kepala_sekolah');

    // --- Data Sekolah ---
    // Lihat: semua peran (controller membatasi cakupan data)
    Route::get('/sekolah', [SekolahController::class, 'index'])->name('sekolah.index')
        ->middleware('role:guru,kepala_sekolah,ketua_pgri,kabid_ptk,kadis');
    // Export Excel & cetak/PDF: kabid_ptk, kadis (reviewer)
    Route::get('/sekolah/export', [SekolahController::class, 'export'])->name('sekolah.export')
        ->middleware('role:kabid_ptk,kadis');
    Route::get('/sekolah/cetak', [SekolahController::class, 'cetak'])->name('sekolah.cetak')
        ->middleware('role:kabid_ptk,kadis');
    // Import Excel: khusus kabid_ptk (administrator)
    Route::get('/sekolah/import', [SekolahController::class, 'importForm'])->name('sekolah.import')
        ->middleware('role:kabid_ptk');
    Route::post('/sekolah/import', [SekolahController::class, 'import'])->name('sekolah.import.proses')
        ->middleware('role:kabid_ptk');
    Route::get('/sekolah/contoh-template', [SekolahController::class, 'unduhTemplate'])->name('sekolah.template')
        ->middleware('role:kabid_ptk');
    // Import rombel dari CSV ekspor Dapodik per kecamatan: khusus kabid_ptk (administrator)
    Route::get('/sekolah/import-rombel', [SekolahController::class, 'importRombelForm'])->name('sekolah.import-rombel')
        ->middleware('role:kabid_ptk');
    Route::post('/sekolah/import-rombel', [SekolahController::class, 'importRombel'])->name('sekolah.import-rombel.proses')
        ->middleware('role:kabid_ptk');
    // Terapkan analisis kebutuhan guru ke semua sekolah sekaligus: khusus kabid_ptk
    Route::post('/sekolah/analisis-massal/terapkan', [SekolahController::class, 'terapkanAnalisisMassal'])->name('sekolah.analisis.massal')
        ->middleware('role:kabid_ptk');
    // Tambah & hapus: kabid_ptk (administrator)
    Route::get('/sekolah/create', [SekolahController::class, 'create'])->name('sekolah.create')
        ->middleware('role:kabid_ptk');
    Route::post('/sekolah', [SekolahController::class, 'store'])->name('sekolah.store')
        ->middleware('role:kabid_ptk');
    Route::delete('/sekolah/{sekolah}', [SekolahController::class, 'destroy'])->name('sekolah.destroy')
        ->middleware('role:kabid_ptk');
    // Ubah: kepala_sekolah (milik sendiri), kabid_ptk
    Route::get('/sekolah/{sekolah}/edit', [SekolahController::class, 'edit'])->name('sekolah.edit')
        ->middleware('role:kepala_sekolah,kabid_ptk');
    Route::put('/sekolah/{sekolah}', [SekolahController::class, 'update'])->name('sekolah.update')
        ->middleware('role:kepala_sekolah,kabid_ptk');
    // Analisis kebutuhan guru (hitung otomatis dari rombel × JP): lihat semua peran,
    // terapkan hanya kepala_sekolah (milik sendiri) & kabid_ptk
    Route::get('/sekolah/{sekolah}/analisis-kebutuhan', [SekolahController::class, 'analisis'])
        ->name('sekolah.analisis')
        ->middleware('role:guru,kepala_sekolah,ketua_pgri,kabid_ptk,kadis');
    Route::post('/sekolah/{sekolah}/analisis-kebutuhan/terapkan', [SekolahController::class, 'terapkanAnalisis'])
        ->name('sekolah.analisis.terapkan')
        ->middleware('role:kepala_sekolah,kabid_ptk');

    // --- Kelola Pengguna: kabid_ptk (administrator) ---
    Route::resource('pengguna', UserController::class)
        ->except(['show'])
        ->middleware('role:kabid_ptk');

    // --- Pengaturan Pemda: konteks wilayah kerja aplikasi (kabid_ptk) ---
    Route::get('/pengaturan/pemda', [PengaturanController::class, 'edit'])->name('pengaturan.pemda')
        ->middleware('role:kabid_ptk');
    Route::put('/pengaturan/pemda', [PengaturanController::class, 'update'])->name('pengaturan.pemda.update')
        ->middleware('role:kabid_ptk');
    Route::delete('/pengaturan/pemda', [PengaturanController::class, 'destroy'])->name('pengaturan.pemda.destroy')
        ->middleware('role:kabid_ptk');

    // --- Jejak Audit: kabid_ptk (append-only, tanpa edit/hapus) ---
    Route::get('/aktivitas', [AktivitasController::class, 'index'])->name('aktivitas.index')
        ->middleware('role:kabid_ptk');

    // --- Kualitas Data: kabid_ptk, kadis (read-only) ---
    Route::get('/kualitas-data', [KualitasDataController::class, 'index'])->name('kualitas-data.index')
        ->middleware('role:kabid_ptk,kadis');
    Route::get('/kualitas-data/export', [KualitasDataController::class, 'export'])->name('kualitas-data.export')
        ->middleware('role:kabid_ptk,kadis');

    // --- Distribusi defisit/surplus: agregat saja (kabid, kadis, pemda) ---
    Route::get('/distribusi', [DistribusiController::class, 'index'])->name('distribusi.index')
        ->middleware('role:kabid_ptk,kadis,pemda');

    // --- Aksesibilitas: agregat jarak Haversine (kabid, kadis) ---
    Route::get('/aksesibilitas', [AksesibilitasController::class, 'index'])->name('aksesibilitas.index')
        ->middleware('role:kabid_ptk,kadis');

    // --- Simulasi what-if: read-only, tanpa ubah data (kabid, kadis) ---
    Route::get('/simulasi', [SimulasiController::class, 'index'])->name('simulasi.index')
        ->middleware('role:kabid_ptk,kadis');
    Route::post('/simulasi', [SimulasiController::class, 'jalankan'])->name('simulasi.jalankan')
        ->middleware('role:kabid_ptk,kadis');

    // --- Proyeksi: jujur tampilkan "data belum cukup" (kabid, kadis) ---
    Route::get('/proyeksi', [ProyeksiController::class, 'index'])->name('proyeksi.index')
        ->middleware('role:kabid_ptk,kadis');
    // --- Usulan Penempatan ---
    // Lihat: semua peran (controller membatasi cakupan data)
    Route::get('/usulan', [UsulanController::class, 'index'])->name('usulan.index')
        ->middleware('role:guru,kepala_sekolah,ketua_pgri,kabid_ptk,kadis');
    // Export Excel & cetak/PDF: kabid_ptk, kadis
    Route::get('/usulan/export', [UsulanController::class, 'export'])->name('usulan.export')
        ->middleware('role:kabid_ptk,kadis');
    Route::get('/usulan/cetak', [UsulanController::class, 'cetak'])->name('usulan.cetak')
        ->middleware('role:kabid_ptk,kadis');
    Route::get('/usulan/create', [UsulanController::class, 'create'])->name('usulan.create')
        ->middleware('role:guru,kepala_sekolah,kabid_ptk');
    Route::post('/usulan', [UsulanController::class, 'store'])->name('usulan.store')
        ->middleware('role:guru,kepala_sekolah,kabid_ptk');
    Route::get('/usulan/{usulan}', [UsulanController::class, 'show'])->name('usulan.show')
        ->middleware('role:guru,kepala_sekolah,ketua_pgri,kabid_ptk,kadis');
    // Proses: verifikasi (kabid), setujui (kadis), tolak (kabid/kadis)
    Route::post('/usulan/{usulan}/verifikasi', [UsulanController::class, 'verifikasi'])
        ->name('usulan.verifikasi')->middleware('role:kabid_ptk');
    Route::post('/usulan/{usulan}/setujui', [UsulanController::class, 'setujui'])
        ->name('usulan.setujui')->middleware('role:kadis');
    Route::post('/usulan/{usulan}/tolak', [UsulanController::class, 'tolak'])
        ->name('usulan.tolak')->middleware('role:kabid_ptk,kadis');

    // --- Mutasi Silang (Swap) ---
    // Lihat: semua peran (seperti Usulan)
    // Daftar swap: semua peran operasional; pemda hanya lewat dashboard
    // ringkasan (/pemda) sesuai dokumentasi peran (data minimization).
    Route::get('/swap', [SwapController::class, 'index'])->name('swap.index')
        ->middleware('role:guru,kepala_sekolah,ketua_pgri,kabid_ptk,kadis');
    // Generate rekomendasi: kabid_ptk (administrator)
    Route::post('/swap/generate', [SwapController::class, 'generate'])
        ->name('swap.generate')->middleware('role:kabid_ptk');
    // Setujui: kadis | Tolak: kabid_ptk, kadis
    Route::post('/swap/{swap}/setujui', [SwapController::class, 'setujui'])
        ->name('swap.setujui')->middleware('role:kadis');
    Route::post('/swap/{swap}/tolak', [SwapController::class, 'tolak'])
        ->name('swap.tolak')->middleware('role:kabid_ptk,kadis');

    // --- SPK Penempatan (Skenario 1: Pengisian Formasi) ---
    // Lihat: semua peran (seperti Usulan & Swap)
    // Daftar SPK: semua peran operasional; pemda hanya lewat dashboard
    // ringkasan (/pemda) sesuai dokumentasi peran (data minimization).
    Route::get('/spk', [SpkController::class, 'index'])->name('spk.index')
        ->middleware('role:guru,kepala_sekolah,ketua_pgri,kabid_ptk,kadis');
    // Generate rekomendasi: kabid_ptk (administrator)
    Route::post('/spk/generate', [SpkController::class, 'generate'])
        ->name('spk.generate')->middleware('role:kabid_ptk');
    // Setujui: kadis | Tolak: kabid_ptk, kadis
    Route::post('/spk/{spk}/setujui', [SpkController::class, 'setujui'])
        ->name('spk.setujui')->middleware('role:kadis');
    Route::post('/spk/{spk}/tolak', [SpkController::class, 'tolak'])
        ->name('spk.tolak')->middleware('role:kabid_ptk,kadis');

    // Telaahan staf: bahan keputusan PPK (kabid_ptk, kadis)
    Route::get('/spk/{spk}/telaahan', [SpkController::class, 'telaahan'])
        ->name('spk.telaahan')->middleware('role:kabid_ptk,kadis');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
