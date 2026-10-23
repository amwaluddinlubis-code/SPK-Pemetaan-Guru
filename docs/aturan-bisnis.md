# Aturan Bisnis Kunci — PetaPenempatan ASN

1. **Jarak** = Haversine domisili → sekolah untuk komputasi massal
   (cepat, offline). **Jarak jalan** (via layanan routing) ditampilkan
   untuk rekomendasi final — hybrid dengan cache database dan fallback
   Haversine bila API gagal. Lihat `docs/qa.md` bagian routing.
2. **Ambang SPK 15 km**: hanya guru berjarak > 15 km yang masuk rekomendasi.
3. **Penghematan wajib positif**: kandidat yang lebih jauh otomatis gugur.
4. **Prioritas keluarga**: kode saat ini memprioritaskan guru berkeluarga
   pada SPK dan memberi bobot 1,5x pada swap. **Aturan ini perlu dasar
   kebijakan tertulis, transparansi, dan evaluasi dampak keadilan sebelum
   digunakan untuk keputusan operasional.** Status pernikahan tidak boleh
   dianggap otomatis mewakili tingkat kebutuhan atau kerentanan keluarga.
5. **Formasi tidak over-booking**: satu slot tidak dijanjikan ke dua guru;
   sisa kebutuhan dikurangi setiap penempatan disetujui. Lindungi juga proses
   generate dari konkurensi agar dua proses tidak memakai slot yang sama.
6. **Batas perhatian 5 km**: guru berjarak > 5 km ditandai di dashboard.
7. **Mutasi atomik**: `sekolah_id` + kolom denormalisasi diupdate dalam satu
   transaction. Sumber kebenaran koordinat = relasi `sekolah`; kolom
   denormalisasi hanya cache tampilan (`Guru::koordinatSekolah()`).
8. **Persetujuan terkunci**: `lockForUpdate` + validasi ulang status,
   sekolah asal, dan kapasitas. Terapkan pola aman yang konsisten pada
   penolakan maupun aksi perubahan status lainnya.
9. **Swap hanya win-win**, satu guru satu rekomendasi aktif.
10. **Satu usulan aktif** per guru.
11. **Scope peran**: tiap peran hanya melihat/mengubah sesuai wilayahnya.
    Terapkan dan uji scope pada semua halaman, endpoint, pencarian, ekspor,
    serta aksi baca/tulis—termasuk daftar SPK dan swap. Sejak 23 Okt 2026
    akses membuka modul diatur **Pengaturan Akses** (matriks peran × menu
    di `App\Services\RegistriMenu` + tabel `akses_menu`, digerbang
    middleware `PastikanAksesMenu`); bawaannya persis matriks pada
    `docs/peran.md`. Matriks tidak menaikkan hak aksi — tambah/ubah/hapus/
    menyetujui tetap mengikuti aturan peran rinci per route.
12. **Rem pemerataan (3T)**: guru di sekolah Terpencil/3T tidak ditarik
    keluar lewat SPK.
13. **Peringatan dampak sekolah asal**: bila sekolah asal kekurangan guru
    mapel yang sama (atau guru satu-satunya), tampil peringatan di kolom
    Kelayakan agar Kadis menimbang.
14. **Linearitas sertifikasi**: guru bersertifikat harus mengampu mapel
    sesuai sertifikat; ketidaklinearan ditandai di tabel guru, Kelayakan,
    dan telaahan staf.
15. **Status kepegawaian** PNS/PPPK/Honorer tercatat; mutasi PPPK
    memperhatikan perjanjian kerja dan ketentuan PPK. Data administratif —
    peran guru tidak bisa mengubahnya.
16. **Keputusan akhir oleh pejabat berwenang**: hasil SPK adalah rekomendasi
    pendukung keputusan, bukan SK atau keputusan mutasi resmi otomatis.
17. **Impersonate tercatat & terbatas**: hanya Kabid PTK yang dapat masuk
    sebagai pengguna lain; dilarang menyamar sebagai sesama Kabid PTK,
    akun sendiri, atau berlapis. Selama menyamar tampil bilah peringatan;
    mulai dan berhenti wajib tercatat di Jejak Audit. Tindakan selama
    impersonate berlaku sebagai akun yang disamar (termasuk scope datanya).
18. **Peringkat hibrida SAW + TOPSIS**: alternatif sekolah yang lolos
    saringan greedy pada SPK pengisian formasi diperingkat dengan skor
    akhir = rata-rata SAW dan TOPSIS atas kriteria penghematan jarak
    (40%), jarak tujuan (25%), sisa formasi (25%), dan prioritas daerah
    tujuan (10%). Skor dan snapshot kriterianya wajib tersimpan pada
    rekomendasi (`detail_skor`) agar dapat diaudit; rekomendasi lama
    tanpa skor tidak dianggap bernilai 0. Bobot adalah parameter
    kebijakan di `App\Services\MesinSawTopsis` — perubahannya perlu
    penetapan tertulis Dinas. Rumus lengkap: `docs/metode-saw-topsis.md`.
