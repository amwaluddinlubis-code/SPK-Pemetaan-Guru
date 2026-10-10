<?php
// Daftar rute aplikasi dengan hak akses per peran.
//   guru           → data diri sendiri
//   kepala_sekolah → CRUD guru di sekolahnya + kelola sekolahnya
//   ketua_pgri     → view se-kecamatan (read-only)
//   kabid_ptk      → administrator (CRUD penuh + kelola pengguna)
//   kadis          → reviewer (read-only semua)

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\SwapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsulanController;
use Illuminate\Support\Facades\Route;

// --- Halaman login (hanya untuk tamu/belum login) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
});

// --- Halaman dalam aplikasi (wajib login) ---
Route::middleware('auth')->group(function () {
    // Dashboard + peta rekomendasi: semua peran
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- Data Guru ---
    // Lihat: semua peran (controller membatasi cakupan data)
    Route::get('/guru', [GuruController::class, 'index'])->name('guru.index');
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
    Route::get('/sekolah', [SekolahController::class, 'index'])->name('sekolah.index');
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

    // --- Kelola Pengguna: kabid_ptk (administrator) ---
    Route::resource('pengguna', UserController::class)
        ->except(['show'])
        ->middleware('role:kabid_ptk');

    // --- Usulan Penempatan ---
    // Lihat: semua peran (controller membatasi cakupan data)
    Route::get('/usulan', [UsulanController::class, 'index'])->name('usulan.index');
    Route::get('/usulan/create', [UsulanController::class, 'create'])->name('usulan.create')
        ->middleware('role:guru,kepala_sekolah,kabid_ptk');
    Route::post('/usulan', [UsulanController::class, 'store'])->name('usulan.store')
        ->middleware('role:guru,kepala_sekolah,kabid_ptk');
    Route::get('/usulan/{usulan}', [UsulanController::class, 'show'])->name('usulan.show');
    // Proses: verifikasi (kabid), setujui (kadis), tolak (kabid/kadis)
    Route::post('/usulan/{usulan}/verifikasi', [UsulanController::class, 'verifikasi'])
        ->name('usulan.verifikasi')->middleware('role:kabid_ptk');
    Route::post('/usulan/{usulan}/setujui', [UsulanController::class, 'setujui'])
        ->name('usulan.setujui')->middleware('role:kadis');
    Route::post('/usulan/{usulan}/tolak', [UsulanController::class, 'tolak'])
        ->name('usulan.tolak')->middleware('role:kabid_ptk,kadis');

    // --- Mutasi Silang (Swap) ---
    // Lihat: semua peran (seperti Usulan)
    Route::get('/swap', [SwapController::class, 'index'])->name('swap.index');
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
    Route::get('/spk', [SpkController::class, 'index'])->name('spk.index');
    // Generate rekomendasi: kabid_ptk (administrator)
    Route::post('/spk/generate', [SpkController::class, 'generate'])
        ->name('spk.generate')->middleware('role:kabid_ptk');
    // Setujui: kadis | Tolak: kabid_ptk, kadis
    Route::post('/spk/{spk}/setujui', [SpkController::class, 'setujui'])
        ->name('spk.setujui')->middleware('role:kadis');
    Route::post('/spk/{spk}/tolak', [SpkController::class, 'tolak'])
        ->name('spk.tolak')->middleware('role:kabid_ptk,kadis');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
