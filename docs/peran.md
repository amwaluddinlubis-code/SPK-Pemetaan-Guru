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

Pembatasan diterapkan server-side — bukan sekadar menyembunyikan menu —
dalam tiga lapisan: (1) **Pengaturan Akses** menjadi gerbang halaman modul
(middleware `PastikanAksesMenu` memetakan nama route ke menu di
`App\Services\RegistriMenu`; nilai bawaan matriksnya = tabel pada dokumen
ini); (2) middleware `role:` rinci pada route aksi (tambah/ubah/hapus/
setujui/ekspor); (3) scope peran pada query komponen Livewire/controller.
Akses langsung via URL oleh peran tak berwenang menghasilkan 403
(halaman ramah di `resources/views/errors/403.blade.php`).

## Pengaturan Akses (23 Okt 2026)

Administrator (Kabid PTK) dapat mengubah matriks di atas lewat menu
**Sistem → Pengaturan Akses**: menutup akses menyembunyikan menu dari
bilah sisi peran itu dan menolak route-nya (403); membuka akses di luar
bawaan benar-benar membuka halaman modulnya. Matriks mengatur **akses
membuka modul**, bukan hak aksi di dalamnya — aksi tetap dijaga lapisan
(2) dan (3). Pengaman: Kabid PTK tidak bisa menutup aksesnya sendiri ke
halaman Pengaturan Akses (anti-terkunci); Kelola Pengguna, Pengaturan
Pemda, dan Pengaturan Akses sendiri tetap khusus Kabid PTK. Setiap
perubahan tercatat di Jejak Audit (`akses-menu.ubah` / `akses-menu.reset`).
Sejak perbaikan audit UX (23 Okt 2026), bawaan menu **Eksekutif**
(`/pemda`) juga terbuka untuk Kabid PTK (akses lihat), agar administrator
yang mengatur konteks pemda dapat memeriksa dashboard eksekutifnya;
matriks tetap dapat diubah seperti menu lain.

## Impersonate (23 Okt 2026)

Kabid PTK dapat **masuk sebagai** pengguna lain dari Kelola Pengguna
untuk memeriksa tampilan/kendala — dengan bilah peringatan selama
penyamaran dan tombol kembali ke akun sendiri. Dilarang: menyamar sebagai
sesama Kabid PTK, akun sendiri, atau saat sedang menyamar. Mulai dan
berhenti tercatat di Jejak Audit (`impersonate.mulai` / `impersonate.berhenti`).

## Keputusan kebijakan pemda (11 Okt 2026)

Peran `pemda` dibatasi pada dashboard ringkasan karena dokumentasi peran
menyatakan "Dashboard Eksekutif — Ringkasan read-only" dan daftar SPK/swap
memuat data pribadi guru (NIP, domisili) di luar kebutuhan ringkasan. Jika
kebijakan berubah, longgarkan lewat **Pengaturan Akses** (bukan mengubah
kode) dan catat alasannya di sini.

**Catatan audit (diperbarui 23 Okt 2026):** catatan lama menyatakan query
daftar SPK/swap belum terisolasi per peran. Kini scope peran ditegakkan di
dalam komponen `TabelSpk`/`TabelSwap` (guru: milik sendiri; kepala
sekolah: se-sekolah; ketua PGRI: se-kecamatan) dan dicakup
`TabelLivewireTest`, di samping scope pada controller. Akses halaman
daftarnya sendiri kini digerbang Pengaturan Akses (lihat bagian di atas).
