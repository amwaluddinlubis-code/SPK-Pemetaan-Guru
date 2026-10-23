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
19. **Linieritas penempatan (Kepmen 222/O/2025)**:
    kesesuaian bidang tugas tujuan dinilai dari **bidang studi sertifikat
    pendidik** guru (nama + kode, disimpan di data guru; kode diturunkan
    otomatis dari daftar Kepmen di `App\Services\Linieritas`), bukan dari
    label mapel sertifikasi. Status tampil sebagai lencana di hasil SPK,
    telaahan, dan usulan: Linier / Tidak linier — risiko tunjangan profesi
    (Permendikdasmen 10/2026 Pasal 4 ayat (2) huruf e) / Di luar cakupan
    Kepmen. Mapel **Agama selalu di luar cakupan** karena Kepmen 222 tidak
    memuat baris Pendidikan Agama; guru belum bersertifikat tidak diberi
    label. **Fase 2 (mesin)**: guru bersertifikat dengan bidang studi
    terisi juga menjadi kandidat formasi mapel linier kelompoknya
    (tingkat 2) dengan faktor penyesuai skor 0,85 di belakang mapel
    persis (tingkat 1); formasi yang diisi tercatat sebagai
    `mapel_tujuan` rekomendasi dan — bila disetujui — menjadi bidang
    tugas guru di sekolah baru, sementara bidang studi sertifikasinya
    tidak berubah. Linieritas tetap kelayakan + peringatan, bukan
    penolakan otomatis; keputusan akhir tetap pejabat berwenang.
    **Fase 3 (informasi pendukung)**: tabel SPK & telaahan menampilkan
    perkiraan beban mengajar di sekolah tujuan (rombel × JP mapel tujuan
    ÷ guru mapel itu termasuk yang ditempatkan) dengan peringatan bila
    < 24 JP, serta info Tunjangan Khusus 1× gaji pokok bila sekolah
    tujuan berkategori daerah khusus. Kualitas Data memeriksa tugas
    tidak linier, bidang studi serdik kosong, dan pendukung Tambahan
    Penghasilan yang kurang (NUPTK / pendidikan < S-1 bagi yang belum
    bersertifikat). **Pemisahan label agama**: mapel kanonis memuat
    Agama Islam & Agama Kristen sendiri-sendiri (bukan lagi "Agama");
    analisis massal menghitung Agama Islam dengan rumus baku, kebutuhan
    Agama Kristen diisi manual per sekolah karena data rombel per agama
    tidak tersedia; baris kebutuhan "Agama" lama diubah namanya menjadi
    "Agama Islam" oleh migrasi data. **Rekomendasi lintas jenjang**
    adalah pengaturan dinas (`App\Services\PengaturanSpk`, diatur di
    Pengaturan Pemda): Opsi B (bawaan) mengizinkan rekomendasi lintas
    jenjang dengan lencana tingkat 2 dinilai dari jenjang guru; Opsi A
    melarang mesin membuat rekomendasi lintas jenjang sama sekali.
20. **Akun guru berbasis NIP & impor massal**: username akun guru =
    NIP guru dan tidak dapat diubah oleh guru; password bawaan = NIP
    juga dan wajib diganti saat login pertama (akun ditandai
    `harus_ganti_password`, aplikasi terkunci ke halaman profil sampai
    diganti). Akun dibuatkan otomatis (`App\Services\AkunGuru`) saat
    guru ditambah operator, saat NIP-nya dilengkapi/diubah, saat
    pendaftaran mandiri disetujui, dan saat impor massal. Guru yang
    belum punya NIP belum bisa memiliki akun; ia boleh **mendaftar
    mandiri** di halaman publik `/daftar-guru` dan akunnya hanya dibuat
    setelah operator dinas (kabid_ptk) menyetujui di menu Pendaftaran
    Guru. Status kepegawaian mencakup **PPPK Paruh Waktu**. Impor guru
    massal berkas persiapan P3K memakai perintah
    `guru:impor-p3k <berkas> [--ganti-semua]`: menambahkan sekolah baru
    dari sheet "Sekolah Baru" (NPSN/alamat/koordinat menyusul dari
    Dapodik), mengimpor sheet "Siap Impor", dan — dengan
    `--ganti-semua` — menghapus seluruh data guru lama beserta hasil
    SPK, usulan, dan akun gurunya lebih dulu. Domisili guru hasil impor
    kosong (diisi bertahap; mesin SPK & analisis jarak menyaringnya)
    dan pendidikan terakhir tersimpan berformat "Jenjang - Bidang"
    (mis. "S1 - Informatika").
21. **Keamanan antrean Pendaftaran Guru**: halaman Pendaftaran Guru
    hanya untuk kabid_ptk — digerbang matriks Pengaturan Akses DAN
    otorisasi di dalam komponen Livewire (`mount` + setiap aksi),
    karena endpoint pembaruan Livewire tidak melewati gerbang rute.
    Jejak Audit tidak pernah menyimpan nilai NIP: `ActivityLog`
    menyensor kunci sensitif (termasuk `nip` dan `username` — username
    guru adalah NIP-nya) dan pemanggil tidak mencantumkan NIP pada
    perubahan yang dicatat.
22. **Formasi & reservasi bersama lintas alur**: perhitungan formasi
    resmi ada di `KebutuhanGuru::sisaDenganAlokasi()`. Target =
    `kebutuhan_guru.jumlah_target` (atribut `jumlah` memetakannya;
    diisi analisis kebutuhan sebagai kebutuhan bruto sekolah);
    tersedia = target − guru yang sudah bertugas pada formasi itu −
    reservasi aktif. Reservasi = rekomendasi SPK berstatus
    `rekomendasi` + usulan berstatus aktif menuju formasi itu
    (dihitung dari status, bukan angka tersimpan, dan satu guru hanya
    menahan satu slot per formasi — penolakan/pembatalan otomatis
    melepaskannya). Generate SPK, pengajuan usulan, dan KEDUA
    persetujuan (SPK & usulan, oleh kadis) memakai perhitungan yang
    sama dan memvalidasi ulang di dalam transaksi (baris kebutuhan
    dikunci, reservasi item yang diproses dikecualikan). Persetujuan
    TIDAK mengubah target: mutasi guru ke tujuanlah yang mengisi
    formasi pada perhitungan berikutnya, sehingga angka Distribusi
    tetap konsisten dan satu slot tidak dapat dialokasikan dua kali
    lintas alur.
23. **Verifikasi koordinat sekolah**: koordinat hasil seed/impor
    berstatus **belum terverifikasi** secara bawaan dan tidak boleh
    menjadi tujuan rekomendasi (generate SPK dan Peta Rekomendasi
    hanya memakai sekolah terverifikasi). Verifikasi menyimpan titik
    terverifikasi (`koordinat_terverifikasi_lat/lng`) beserta waktu,
    petugas, sumber, dan catatan; sekolah terverifikasi selama titik
    saat ini sama dengan titik terverifikasi — mengubah titik
    otomatis membatalkan statusnya (harus diverifikasi ulang). Yang
    berwenang: kabid_ptk & kadis untuk semua sekolah dalam konteks
    pemda, kepala sekolah hanya untuk sekolahnya sendiri; guru tidak
    dapat memvalidasi. Kabid_ptk dapat memverifikasi massal dari
    halaman Data Sekolah. Prosedur lapangan: `docs/verifikasi-data.md`.
24. **Branding pemda (Redesign R0).** Warna aksen & logo aplikasi diatur
    Kabid PTK di Pengaturan Pemda (kunci `branding_warna`/`branding_logo`
    di tabel pengaturan, layanan `App\Services\Branding`) dan diterapkan
    ke seluruh aplikasi lewat variabel tema daisyUI (primary/secondary/
    accent untuk tema terang & gelap) yang disuntikkan layout. Warna
    harus hex #rrggbb; logo PNG/JPG/SVG maks 1 MB di disk public.
    Mengganti keduanya tidak memerlukan perubahan kode; tanpa pengaturan,
    berlaku warna bawaan #2563eb dan ikon bawaan. Judul tab mengikuti
    nama pemda konteks aktif.
25. **Kerangka & header baku (Redesign R1).** Bilah atas hanya memuat
    nama aplikasi, lencana konteks pemda, dan pemilih tema; profil &
    keluar tinggal di kaki bilah sisi. Kerangka (layout, bilah sisi,
    bilah atas, spanduk) bebas emoji — semua ikon memakai komponen
    `x-ikon`. Setiap halaman memakai header `x-halaman` dengan prop
    `judul` (tanpa emoji) dan `ikon` yang sama dengan ikon menunya di
    RegistriMenu; deskripsi lewat slot `deskripsi`, tombol lewat slot
    `aksi`. Status menu aktif ditandai latar + garis tepi di bilah sisi.
26. **Kartu KPI dashboard (Redesign R2).** Semua angka ringkasan di
    Dashboard Pemetaan & Dashboard Eksekutif memakai komponen
    `x-kartu-kpi`: label, ikon, angka, dan SATU baris konteks berupa
    komposisi/perbandingan nyata (mis. guru berdomisili terpetakan,
    sekolah per jenjang, guru per status kepegawaian) — dilarang konteks
    pengisi ("terdata di sistem") bila komposisi tersedia. Nada warna
    bermakna: bahaya/peringatan hanya saat nilai > 0, sukses saat bersih.
    Terkait: jarak guru yang domisilinya belum terpetakan adalah
    TIDAK DIKETAHUI (null) — `Guru::jarakKeSekolahSaatIni()` mengembalikan
    null, tidak pernah dihitung dari titik (0,0).
27. **Tabel data baku (Redesign R3).** Semua tabel data memakai kelas
    `tabel-baku` (kepala kolom kapital redup, baris sejajar tengah, hover
    halus) di samping kelas daisyUI. Lencana status memakai komponen
    `x-lencana-status` dengan peta warna tetap (sukses= tuntas/
    terverifikasi, peringatan= menunggu/perhatian, bahaya= ditolak/
    tidak linear, info= proses, gelap= status kepegawaian, netral=
    lainnya) dan tidak pernah membungkus teks (whitespace-nowrap).
    Tombol aksi selalu ikon SVG + label (pensil= Ubah, sampah= Hapus,
    mata= Lihat, centang= Setujui, silang= Tolak); emoji dilarang pada
    tabel & tombol halamannya. Keadaan kosong memakai `x-kosong`
    (ikon + pesan). Angka pada kolom numerik rata kanan & tabular
    (kelas `angka`).
28. **Grup menu bilah sisi bisa dilipat.** Judul grup RegistriMenu adalah
    parent (elemen `<details class="menu-grup">`), menu di bawahnya adalah
    child. Grup yang berisi halaman aktif selalu terbuka; status lipat
    grup lain diingat per peramban (localStorage `spk-menu-grup`).
    Item tanpa grup (mis. Eksekutif) tetap tampil rata.
29. **Tombol aksi baris tabel & konfirmasi (melengkapi #27).** Tombol
    aksi di kolom Aksi tabel digabung dalam grup menyatu (`.grup-aksi`,
    pola join), tampil HANYA ikon — label menjadi tooltip `title` +
    `aria-label` (tooltip bawaan peramban dipakai karena tooltip CSS
    terpotong wadah tabel bergulir). Tombol aksi di kepala halaman tetap
    ikon + label, berukuran kecil (`btn-sm`) dan juga digabung
    `.grup-aksi`. Semua konfirmasi bawaan peramban (`confirm`, `prompt`,
    `wire:confirm`) DILARANG: pakai modal DaisyUI global
    `x-modal-konfirmasi` — form biasa lewat atribut `data-konfirmasi`
    (varian alasan wajib lewat `data-alasan`), tombol Livewire lewat
    `konfirmasiModal()`/`konfirmasiDariData()`.
30. **Layar peta (Redesign R4).** Peta adalah fokus layar: wadah
    relatif setinggi viewport; panel rekomendasi melayang di tepi kiri
    atas selebar maks 320px dan bisa dilipat; legenda tetap di kiri
    bawah; kontrol zoom di kanan bawah. Kartu kandidat menyorot penanda
    sekolah terkait di peta (hover/klik). Pemilih koordinat memakai
    bingkai yang sama (`rounded-box`, border base-300, bayangan halus)
    dan petunjuk berikon — tanpa emoji.
31. **Formulir, dokumen & halaman publik (Redesign R5).** Formulir
    panjang dikelompokkan per bagian dengan penjelas singkat di kepala
    grup (form guru: 3 tab; form sekolah: seksi Identitas — Lokasi &
    Koordinat — Kebutuhan Guru). Kesalahan validasi WAJIB menempel di
    field terkait (`@error` di bawah field), bukan hanya ringkasan di
    atas formulir. Dokumen cetak (telaahan staf & cetak daftar) memakai
    kop surat bersama `komponen/kop-surat`: logo pemda + nama pemda
    konteks aktif + Dinas Pendidikan — dilarang menulis nama/alamat
    pemda secara statis. Halaman login & pendaftaran publik mengikuti
    branding pemda (warna aksen & logo dari Pengaturan Pemda), tanpa
    gradasi/ikon generik dan tanpa emoji. Warna font parent grup menu
    bilah sisi sama dengan item anaknya (mewarisi warna tema, bukan
    putih statis).
32. **Semua tombol menu & aksi hanya ikon + tooltip daisyUI**
    (menyempurnakan #29). Tombol di kepala halaman dan tombol aksi di
    semua halaman tampil HANYA ikon (horizontal, digabung `.grup-aksi`
    bila lebih dari satu) — label menjadi tooltip daisyUI
    (`tooltip` + `data-tip`), BUKAN tooltip bawaan peramban: kepala
    halaman memakai `tooltip-bottom`, aksi baris tabel memakai
    `tooltip-left` (agar tidak terpotong tepi kanan wadah tabel).
    `aria-label` tetap wajib pada setiap tombol hanya-ikon.
