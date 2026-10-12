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
    serta aksi baca/tulis—termasuk daftar SPK dan swap.
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
