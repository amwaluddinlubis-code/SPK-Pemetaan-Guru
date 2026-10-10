# Proses Bisnis — PetaPenempatan ASN

Dokumen ini menjelaskan cara kerja aplikasi dari sudut pandang pengguna:
siapa saja yang memakai, alur kerja tiap fitur, dan aturan-aturan penting
yang dijaga sistem.

## 1. Gambaran Umum

**PetaPenempatan ASN** adalah sistem informasi pemetaan penempatan Guru ASN
berbasis domisili untuk Dinas Pendidikan Kabupaten Mandailing Natal.

Masalah yang diselesaikan: guru sering ditempatkan jauh dari domisilinya,
padahal ada sekolah lain yang kekurangan guru dan lebih dekat ke rumah guru
tersebut. Aplikasi ini memetakan domisili guru vs lokasi sekolah, lalu
memberi rekomendasi penempatan yang lebih adil dan efisien — lewat tiga
jalur: usulan, mutasi silang, dan SPK pengisian formasi.

## 2. Aktor dan Peran

| Peran | Scope Data | Hak Akses |
|---|---|---|
| Guru | Dirinya sendiri | Lihat data & rekomendasi sendiri; boleh ubah datanya sendiri; mengajukan usulan pindah |
| Kepala Sekolah | Sekolahnya | CRUD guru di sekolahnya; ubah data & kebutuhan sekolahnya |
| Ketua PGRI Kecamatan | Satu kecamatan | Lihat saja (read-only) data se-kecamatan |
| Kabid PTK | Se-kabupaten | Administrator: CRUD penuh guru, sekolah, kelola pengguna; verifikasi usulan |
| Kadis Pendidikan | Semua | Reviewer: lihat semua (read-only); menyetujui/menolak usulan, SPK, dan swap |
| Pemerintah Daerah | Semua | Eksekutif: dashboard ringkasan se-kabupaten (read-only) |

## 3. Proses Bisnis Utama

### P1. Pendataan Guru dan Sekolah

Fondasi semua fitur. Data guru: NIP (unik), nama, mapel, jenjang
(SD/SMP/SMA), alamat domisili, desa/kelurahan, kecamatan, dan titik
koordinat domisili. Data sekolah: NPSN (unik), nama, alamat,
desa/kelurahan, kecamatan, titik koordinat, plus kebutuhan guru per mapel
(misal: Matematika kurang 2 orang).

Pemasukan data lewat form atau import Excel (khusus Kabid PTK).
Kepala Sekolah hanya bisa mengelola guru di sekolahnya sendiri.

### P2. Usulan Penempatan

Jalur pengajuan dari bawah (bottom-up): guru yang ingin pindah mengajukan
usulan penempatan.

Alur status:

    diajukan → diverifikasi (oleh Kabid PTK) → disetujui (oleh Kadis)
                                                      ↘ ditolak

- Guru mengajukan usulan pindah ke sekolah tujuan.
- Kabid PTK memverifikasi kelayakan usulan.
- Kadis menyetujui atau menolak. Saat disetujui, data guru **otomatis
  dimutasi** ke sekolah tujuan dalam satu database transaction (aman:
  berhasil semua atau batal semua).
- Aturan: seorang guru tidak boleh memiliki 2 usulan yang aktif
  bersamaan.

### P3. Mutasi Silang (Swap)

Jalur rekomendasi sistem: aplikasi mencari **pasangan dua guru** yang jika
bertukar sekolah, **keduanya** menjadi lebih dekat ke domisilinya
masing-masing (win-win). Satu guru hanya boleh muncul di **satu**
rekomendasi aktif — pasangan baru tidak dibuat untuk guru yang sudah
punya rekomendasi menunggu.

Alur status:

    menunggu → disetujui (oleh Kadis) → kedua guru otomatis bertukar
              ↘ ditolak
              ↘ batal_karena_konflik (jika salah satu guru sudah pindah)

- Tombol "Generate" memindai seluruh data guru dan hanya menampilkan
  pasangan yang saling menguntungkan. Jika tidak ada pasangan win-win,
  hasilnya nol — itu normal, bukan error.
- **Pemilihan pasangan**: semua pasangan win-win yang valid dikumpulkan,
  lalu dipilih secara greedy berdasarkan **total penghematan terbesar**
  (maximum-weight matching sederhana) — satu guru hanya di satu rekomendasi.
- **Syarat administratif**: mapel harus sama, **jenjang harus sama**
  (mutasi lintas jenjang butuh persetujuan kepegawaian khusus), dan tidak
  ada pihak yang sedang punya usulan aktif.
- Kadis menyetujui atau menolak tiap rekomendasi.
- **Keamanan persetujuan**: rekomendasi dan kedua guru dikunci di dalam
  transaction (urutan id konsisten anti-deadlock); posisi kedua guru
  divalidasi ulang — jika salah satunya sudah berpindah sekolah,
  persetujuan digagalkan tanpa mutasi parsial.

### P4. SPK Pengisian Formasi

Jalur rekomendasi pengisian kekurangan guru (top-down): untuk setiap guru
yang jarak domisilinya ke sekolah saat ini **lebih dari 15 km**
(AMBANG_KM), sistem mencari **3 sekolah terbaik** yang kekurangan formasi
untuk mapel guru tersebut. Perhitungan jarak memakai rumus Haversine yang
dijalankan langsung di MySQL.

**Aturan kelayakan rekomendasi** (agar bisa dipertanggungjawabkan):

1. Sekolah tujuan **harus lebih dekat** daripada sekolah saat ini —
   penghematan S = jarak_sekarang − jarak_tujuan harus positif.
   Kandidat yang lebih jauh otomatis gugur.
2. Sisa formasi (kebutuhan − alokasi rekomendasi aktif) harus tersedia —
   satu formasi tidak boleh dijanjikan ke dua guru.
3. Rekomendasi diurutkan berdasarkan **penghematan terbesar**, bukan
   jarak tujuan terdekat.
4. Nilai penghematan (`hemat_km`) dan sekolah asal disimpan di tiap
   rekomendasi sebagai bukti.

Alur status:

    rekomendasi → disetujui (oleh Kadis) → guru otomatis dimutasi
                  ↘ ditolak

**Keamanan persetujuan**: baris rekomendasi dan data guru dikunci
(`lockForUpdate`) di dalam transaction; status, posisi sekolah asal guru,
dan sisa formasi **divalidasi ulang** — jika guru sudah pindah atau
formasi sudah penuh, persetujuan digagalkan (tidak ada mutasi parsial
atau persetujuan ganda). Sisa kebutuhan formasi dikurangi setiap ada
penempatan yang disetujui.

### P5. Dashboard Eksekutif Pemda

Halaman ringkasan untuk pimpinan daerah. Murni lihat data (read-only),
tanpa tombol ubah:

- Kartu ringkasan: total guru, total sekolah, total kebutuhan guru,
  dan jumlah hal yang menunggu keputusan.
- Tabel distribusi per kecamatan (23 kecamatan): jumlah guru, sekolah,
  kebutuhan.
- Tabel distribusi per jenjang (SD/SMP/SMA): guru vs kebutuhan, plus
  kolom defisit.
- Rekap status usulan, SPK, dan mutasi silang.

## 4. Aturan Bisnis Kunci

1. **Jarak** dihitung dengan rumus Haversine dari koordinat domisili ke
   koordinat sekolah (jarak garis lurus, bukan jarak jalan).
2. **Ambang SPK 15 km**: hanya guru yang bertugas lebih dari 15 km dari
   domisilinya yang masuk rekomendasi SPK.
3. **Penghematan wajib positif**: rekomendasi SPK hanya untuk sekolah
   tujuan yang lebih dekat daripada sekolah saat ini.
4. **Formasi tidak over-booking**: satu slot kebutuhan tidak dijanjikan
   ke dua guru; sisa kebutuhan dikurangi setiap penempatan disetujui.
5. **Batas perhatian 5 km**: guru yang berjarak lebih dari 5 km
   ditandai "butuh perhatian" di dashboard.
6. **Mutasi atomik & sumber koordinat tunggal**: setiap persetujuan yang
   memindahkan guru selalu memperbarui `sekolah_id` DAN kolom denormalisasi
   (`sekolah_saat_ini`, lat, lng) dalam satu transaction. Namun **sumber
   kebenaran tunggal** untuk koordinat sekolah adalah relasi `sekolah`
   (tabel `sekolah`) — semua perhitungan jarak lewat
   `Guru::koordinatSekolah()` / `jarakKeSekolahSaatIni()`; kolom
   denormalisasi hanya cache tampilan.
7. **Persetujuan terkunci**: rekomendasi dan data guru dikunci
   (`lockForUpdate`) saat persetujuan; status, sekolah asal, dan
   kapasitas divalidasi ulang — mencegah persetujuan ganda dan mutasi
   parsial.
8. **Swap hanya win-win**: rekomendasi mutasi silang hanya dibuat jika
   kedua guru diuntungkan, dan satu guru hanya di satu rekomendasi aktif.
9. **Satu usulan aktif**: guru tidak bisa mengajukan usulan baru selama
   masih ada usulan berstatus diajukan/diverifikasi.
10. **Scope peran**: setiap peran hanya melihat/mengubah data sesuai
    wilayahnya (diri sendiri / sekolah / kecamatan / kabupaten).

## 5. Model Data (Ringkas)

| Tabel | Isi |
|---|---|
| users | Akun login + peran |
| guru | Data guru + domisili + koordinat + sekolah saat ini |
| sekolah | Data sekolah + alamat + koordinat |
| kebutuhan_guru | Kebutuhan guru per sekolah per mapel |
| usulan_penempatan | Usulan pindah + status alur |
| hasil_spks | Rekomendasi SPK + status |
| rekomendasi_swaps | Rekomendasi tukar + status |

Relasi utama: guru milik satu sekolah (`sekolah_id`); sekolah punya
banyak kebutuhan guru; usulan/SPK/swap merujuk ke guru (dan sekolah
tujuan).
