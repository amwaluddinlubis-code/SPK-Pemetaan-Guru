<?php

// Daftar rute aplikasi dengan hak akses per peran.
//   guru           → data diri sendiri
//   kepala_sekolah → CRUD guru di sekolahnya + kelola sekolahnya
//   ketua_pgri     → view se-kecamatan (read-only)
//   kabid_ptk      → administrator (CRUD penuh + kelola pengguna)
//   kadis          → reviewer (read-only semua)
//   pemda          → eksekutif daerah (dashboard ringkasan, read-only)

use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\KualitasDataController;
use App\Http\Controllers\PendaftaranGuruController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SimulasiController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\SwapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsulanController;
use App\Http\Controllers\WilayahController;
use App\Livewire\AksesibilitasLivewire;
use App\Livewire\Dashboard;
use App\Livewire\DashboardPemda;
use App\Livewire\KualitasDataLivewire;
use App\Livewire\PendaftaranGuru;
use App\Livewire\PengaturanAkses;
use App\Livewire\PengaturanPemdaLivewire;
use App\Livewire\Proyeksi;
use App\Livewire\SimulasiLivewire;
use App\Livewire\TabelAktivitas;
use App\Livewire\TabelDesa;
use App\Livewire\TabelDistribusi;
use App\Livewire\TabelGuru;
use App\Livewire\TabelKabupaten;
use App\Livewire\TabelKecamatan;
use App\Livewire\TabelPengguna;
use App\Livewire\TabelProvinsi;
use App\Livewire\TabelSekolah;
use App\Livewire\TabelSpk;
use App\Livewire\TabelSwap;
use App\Livewire\TabelUsulan;
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

    // Pendaftaran akun mandiri bagi guru yang belum punya akun/NIP —
    // masuk antrean persetujuan operator dinas (kabid_ptk).
    Route::get('/daftar-guru', [PendaftaranGuruController::class, 'form'])
        ->name('daftar-guru');
    Route::post('/daftar-guru', [PendaftaranGuruController::class, 'simpan'])
        ->middleware('throttle:5,1')
        ->name('daftar-guru.simpan');
});

// --- Halaman dalam aplikasi (wajib login) ---
// Gerbang halaman modul: middleware PastikanAksesMenu (grup web) menolak
// rute modul yang tidak diizinkan Pengaturan Akses untuk peran pengguna —
// karena itu rute indeks/halaman di bawah tidak memakai middleware 'role'.
// Rute aksi (tambah/ubah/hapus/setujui/ekspor) TETAP memakai 'role' rinci
// seperti sebelumnya, dan ikut tertutup bila modulnya dimatikan di matriks.
Route::middleware('auth')->group(function () {
    // Dashboard + peta rekomendasi: semua peran
    Route::get('/', Dashboard::class)->name('dashboard');

    // Profil & ganti password sendiri: semua peran
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');
    Route::put('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // --- Modul Pemda: dashboard eksekutif (ringkasan se-kabupaten, read-only) ---
    // Lihat: pemda, kadis
    Route::get('/pemda', DashboardPemda::class)->name('pemda.dashboard');

    // --- Data Guru ---
    // Lihat: semua peran (controller membatasi cakupan data)
    // Referensi wilayah administrasi (read-only, semua peran): halaman
    // bertabel Livewire + API dropdown berjenjang untuk formulir.
    Route::get('/wilayah/provinsi', TabelProvinsi::class)->name('wilayah.provinsi');
    Route::get('/wilayah/kabupaten', TabelKabupaten::class)->name('wilayah.kabupaten');
    Route::get('/wilayah/kecamatan', TabelKecamatan::class)->name('wilayah.kecamatan');
    Route::get('/wilayah/desa', TabelDesa::class)->name('wilayah.desa');
    Route::get('/wilayah/api/provinsi', [WilayahController::class, 'apiProvinsi'])->name('wilayah.api.provinsi');
    Route::get('/wilayah/api/kabupaten', [WilayahController::class, 'apiKabupaten'])->name('wilayah.api.kabupaten');
    Route::get('/wilayah/api/kecamatan', [WilayahController::class, 'apiKecamatan'])->name('wilayah.api.kecamatan');
    Route::get('/wilayah/api/desa', [WilayahController::class, 'apiDesa'])->name('wilayah.api.desa');
    Route::get('/wilayah/api/koordinat', [WilayahController::class, 'apiKoordinat'])->name('wilayah.api.koordinat');

    Route::get('/guru', TabelGuru::class)->name('guru.index');
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
    Route::get('/sekolah', TabelSekolah::class)->name('sekolah.index');
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

    // --- Pendaftaran Guru: antrean persetujuan operator (kabid_ptk) ---
    Route::get('/pendaftaran', PendaftaranGuru::class)->name('pendaftaran.index');

    // --- Kelola Pengguna: kabid_ptk (administrator) ---
    Route::get('/pengguna', TabelPengguna::class)->name('pengguna.index')
        ->middleware('role:kabid_ptk');
    Route::resource('pengguna', UserController::class)
        ->except(['show', 'index'])
        ->middleware('role:kabid_ptk');

    // --- Pengaturan Pemda: konteks wilayah kerja aplikasi (kabid_ptk) ---
    Route::get('/pengaturan/pemda', PengaturanPemdaLivewire::class)->name('pengaturan.pemda')
        ->middleware('role:kabid_ptk');

    // --- Pengaturan Akses: matriks menu/route per peran (kabid_ptk) ---
    Route::get('/pengaturan/akses', PengaturanAkses::class)->name('pengaturan.akses')
        ->middleware('role:kabid_ptk');

    // --- Impersonate: admin masuk sebagai pengguna lain untuk membantu
    // memeriksa tampilannya, lalu kembali ke akunnya sendiri. Rute berhenti
    // sengaja tanpa middleware role: ia dipakai saat sedang menyamar
    // (pengguna aktif bukan admin) dan hanya bekerja bila ada sesi asal.
    Route::post('/impersonate/berhenti', [ImpersonateController::class, 'berhenti'])->name('impersonate.berhenti');
    Route::post('/impersonate/{user}', [ImpersonateController::class, 'mulai'])->name('impersonate.mulai')
        ->middleware('role:kabid_ptk');

    // --- Jejak Audit: kabid_ptk (append-only, tanpa edit/hapus) ---
    Route::get('/aktivitas', TabelAktivitas::class)->name('aktivitas.index');

    // --- Kualitas Data: kabid_ptk, kadis (read-only) ---
    Route::get('/kualitas-data', KualitasDataLivewire::class)->name('kualitas-data.index');
    Route::get('/kualitas-data/export', [KualitasDataController::class, 'export'])->name('kualitas-data.export')
        ->middleware('role:kabid_ptk,kadis');

    // --- Distribusi defisit/surplus: agregat saja (kabid, kadis, pemda) ---
    Route::get('/distribusi', TabelDistribusi::class)->name('distribusi.index');

    // --- Aksesibilitas: agregat jarak Haversine (kabid, kadis) ---
    Route::get('/aksesibilitas', AksesibilitasLivewire::class)->name('aksesibilitas.index');

    // --- Simulasi what-if: read-only, tanpa ubah data (kabid, kadis) ---
    Route::get('/simulasi', SimulasiLivewire::class)->name('simulasi.index');
    Route::post('/simulasi', [SimulasiController::class, 'jalankan'])->name('simulasi.jalankan')
        ->middleware('role:kabid_ptk,kadis');

    // --- Proyeksi: jujur tampilkan "data belum cukup" (kabid, kadis) ---
    Route::get('/proyeksi', Proyeksi::class)->name('proyeksi.index');
    // --- Usulan Penempatan ---
    // Lihat: semua peran (controller membatasi cakupan data)
    Route::get('/usulan', TabelUsulan::class)->name('usulan.index');
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
    Route::get('/swap', TabelSwap::class)->name('swap.index');
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
    Route::get('/spk', TabelSpk::class)->name('spk.index');
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
