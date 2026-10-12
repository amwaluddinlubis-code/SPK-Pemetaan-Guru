# Peran dan Hak Akses — PetaPenempatan ASN

| Peran | Scope Data yang Diharapkan | Hak Akses |
|---|---|---|
| Guru | Dirinya sendiri | Lihat data & rekomendasi sendiri; ubah datanya sendiri; mengajukan usulan pindah |
| Kepala Sekolah | Sekolahnya | CRUD guru di sekolahnya; ubah data & kebutuhan sekolahnya |
| Ketua PGRI Kecamatan | Satu kecamatan | Lihat saja (read-only) data se-kecamatan |
| Kabid PTK | Se-kabupaten | Administrator: CRUD penuh guru, sekolah, kelola pengguna; verifikasi usulan |
| Kadis Pendidikan | Semua | Reviewer: lihat semua; menyetujui/menolak usulan, SPK, dan swap |
| Pemerintah Daerah | Ringkasan | Eksekutif: dashboard (`/`) + dashboard ringkasan (`/pemda`), read-only |

## Matriks akses SPK & swap (aktual, terverifikasi 11 Okt 2026)

| Endpoint | guru | kepala_sekolah | ketua_pgri | kabid_ptk | kadis | pemda |
|---|---|---|---|---|---|---|
| GET /spk, GET /swap (daftar) | milik sendiri | se-sekolah | se-kecamatan | semua | semua | **403** (hanya ringkasan) |
| POST /spk/generate, /swap/generate | 403 | 403 | 403 | ya | 403 | 403 |
| POST /spk/{id}/setujui, /swap/{id}/setujui | 403 | 403 | 403 | 403 | ya | 403 |
| POST /spk/{id}/tolak, /swap/{id}/tolak | 403 | 403 | 403 | ya | ya | 403 |
| GET /spk/{id}/telaahan | 403 | 403 | 403 | ya | ya | 403 |

Pembatasan diterapkan server-side via middleware `role:` pada route dan
`terapkanScopePeran()` pada query controller — bukan sekadar
menyembunyikan menu. Akses langsung via URL oleh peran tak berwenang
menghasilkan 403 (halaman ramah di `resources/views/errors/403.blade.php`).

## Keputusan kebijakan pemda (11 Okt 2026)

Peran `pemda` dibatasi pada dashboard ringkasan karena dokumentasi peran
menyatakan "Dashboard Eksekutif — Ringkasan read-only" dan daftar SPK/swap
memuat data pribadi guru (NIP, domisili) di luar kebutuhan ringkasan. Jika
kebijakan membutuhkan akses daftar, longgarkan middleware `role:` pada
route `/spk` & `/swap` dan catat alasannya di sini.

**Catatan audit:** cakupan pada tabel di atas adalah kebijakan yang harus
dipenuhi, bukan jaminan bahwa setiap halaman sudah menerapkannya. Audit
statis pada `main` menemukan query daftar SPK dan swap yang perlu dibatasi
menurut peran. Jangan anggap data pada halaman tersebut sudah terisolasi
untuk setiap peran sampai perbaikan dan tes regresi terverifikasi.
