<?php

namespace App\Services;

use App\Models\Guru;

// Linieritas guru menurut Kepmen Pendidikan Dasar dan Menengah
// No. 222/O/2025: kesesuaian BIDANG STUDI SERTIFIKASI (nama + kode pada
// sertifikat pendidik) dengan bidang tugas/mata pelajaran yang diampu.
//
// Data referensi BIDANG_SD / BIDANG_SMP diekstrak dari Lampiran II & III
// Kepmen tersebut. Peta linieritas memakai label mapel kanonis
// AnalisisKebutuhanGuru (mis. "Seni/Prakarya" menampung Prakarya, Seni
// Budaya, dan seni rumpun lainnya pada Permendikdasmen 13/2025) ditambah
// label non-kanonis yang tidak pernah jadi kebutuhan SPK (Koding-KA,
// Muatan Lokal, BK, Program Kebutuhan Khusus) agar peta tetap setia sumber.
//
// FASE 1: status hanya PERINGATAN informatif di hasil SPK, telaahan, dan
// usulan — tidak mengubah mesin pencocokan dan tidak menghalangi penetapan.
// Pagar: mapel Agama selalu "di luar cakupan" karena salinan Kepmen 222
// tidak memuat baris Pendidikan Agama (diduga diatur jalur Kemenag).
class Linieritas
{
    public const KODING = 'Koding dan Kecerdasan Artifisial';

    public const MULOK = 'Muatan Lokal';

    public const BK = 'Bimbingan Konseling';

    public const PKH = 'Program Kebutuhan Khusus';

    public const BIDANG_SD = [
        ['020', 'Guru Kelas TK'],
        ['021', 'Guru Kelas RA'],
        ['027', 'Guru Kelas SD/Umum (kelas awal dan akhir)'],
        ['028', 'Guru Kelas MI'],
        ['047/094/180/318', 'Matematika'],
        ['050', 'Pendidikan Kewarganagaraan'],
        ['054/156', 'Bahasa Indonesia'],
        ['057', 'Ilmu Pengetahuan Alam (Fisika)'],
        ['060', 'Ilmu Pengetahuan Sosial'],
        ['062', 'Bahasa Daerah'],
        ['063', 'Muatan lokal Lain-lain sesuai potensi daerah'],
        ['084', 'Pendidikan Kewarganegaraan (PKn)'],
        ['087', 'Bahasa Indonesia (Sastra)'],
        ['90/157/311', 'Bahasa Inggris'],
        ['097', 'Pengetahuan Alam (IPA Terpadu, Fisika)'],
        ['100', 'Pengetahuan Sosial (Sosiologi, IPS Terpadu)'],
        ['107', 'Pendidikan Jasmani (Olahraga & Kesehatan)'],
        ['114/207', 'Geografi'],
        ['117/204', 'Sejarah'],
        ['120', 'Ekonomi (Umum, Koperasi, Akuntansi)'],
        ['124/190/321', 'Biologi'],
        ['154', 'Pendidikan Pancasila dan Kewarganegaraan (PPKn)'],
        ['184/319', 'Fisika'],
        ['187/320', 'Kimia'],
        ['210', 'Ekonomi'],
        ['214', 'Sosiologi'],
        ['215', 'Antropologi'],
        ['217', 'Seni Budaya'],
        ['220', 'Pendidikan Jasmani Olah Raga dan Kesehatan'],
        ['225', 'Informatika'],
        ['310', 'Pendidikan Kewarganegaraan (PKn)'],
        ['330', 'Keterampilan Komputer dan Pengelolaan Informasi (KKPI)'],
        ['504', 'Kimia Umum'],
        ['514', 'Teknik Telekomunikasi'],
        ['523', 'Teknik Komputer dan Informatika'],
        ['524', 'Pengembangan Perangkat Lunak dan Gim'],
        ['524', 'Rekayasa Perangkat Lunak'],
        ['524', 'Teknologi Informasi dan Komunikasi'],
        ['525', 'Teknik Komputer dan Jaringan'],
        ['526', 'Multi Media'],
        ['527', 'TIK Khusus Lainnya'],
        ['562', 'Seni Rupa'],
        ['566', 'Seni Rupa Khusus Lainnya'],
        ['568', 'Seni Musik Klasik'],
        ['569', 'Seni Musik Non Klasik'],
        ['570', 'Seni Tari'],
        ['573', 'Seni Teater'],
        ['574', 'Seni Pertunjukan Lainnya'],
        ['600', 'Teknik Jaringan Akses'],
        ['625', 'Teknik Jaringan Komputer dan Telekomunikasi'],
        ['627', 'Seni Pertunjukan'],
        ['642', 'Tata Artistik'],
        ['746', 'Bahasa Jawa'],
        ['747', 'Bahasa Madura'],
        ['748', 'Bahasa Sunda'],
        ['749', 'Bahasa Daerah Lainnya'],
        ['750', 'Bahasa Bali'],
        ['800', 'Guru Pendidikan Luar Biasa/Pendidikan Khusus'],
        ['810', 'Guru Bimbingan Konseling (Konselor)'],
        ['861', 'Seni Musik'],
    ];

    public const BIDANG_SMP = [
        ['062', 'Bahasa Daerah'],
        ['063', 'Muatan lokal Lain-lain sesuai potensi daerah'],
        ['084', 'Pendidikan Kewarganegaraan (PKn)'],
        ['087', 'Bahasa Indonesia (Sastra)'],
        ['90/157/311', 'Bahasa Inggris'],
        ['094/180/318', 'Matematika'],
        ['097', 'Ilmu Pengetahuan Alam (IPA)'],
        ['097', 'Pengetahuan Alam (IPA Terpadu, Fisika)'],
        ['100', 'Ilmu Pengetahuan Sosial (IPS)'],
        ['100', 'Pengetahuan Sosial (Sosiologi, IPS Terpadu)'],
        ['104', 'Kesenian, Budaya dan Keterampilan'],
        ['107', 'Pendidikan Jasmani (Olahraga & Kesehatan)'],
        ['110/224', 'Teknologi Informasi dan Komunikasi (TIK)'],
        ['114/207', 'Geografi'],
        ['117/204', 'Sejarah'],
        ['120', 'Ekonomi (Umum, Koperasi, Akuntansi)'],
        ['124/190/321', 'Biologi'],
        ['154', 'Pendidikan Pancasila dan Kewarganegaraan (PPKn)'],
        ['156', 'Bahasa Indonesia'],
        ['184/319', 'Fisika'],
        ['187/320', 'Kimia'],
        ['210', 'Ekonomi'],
        ['214', 'Sosiologi'],
        ['215', 'Antropologi'],
        ['217', 'Seni Budaya'],
        ['220', 'Pendidikan Jasmani Olah Raga dan Kesehatan'],
        ['225', 'Informatika'],
        ['227', 'Keterampilan'],
        ['310', 'Pendidikan Kewarganegaraan (PKn)'],
        ['330', 'Keterampilan Komputer dan Pengelolaan Informasi (KKPI)'],
        ['400', 'Teknik Bangunan'],
        ['401', 'Teknik Konstruksi Baja'],
        ['402', 'Teknik Konstruksi Kayu'],
        ['403', 'Teknik Konstruksi Batu dan Beton'],
        ['404', 'Teknik Pekerjaan Finishing'],
        ['405', 'Teknik Konstruksi Bangunan Sederhana'],
        ['406', 'Teknik Gambar Bangunan'],
        ['407', 'Teknik Plambing dan Sanitasi'],
        ['408', 'Teknik Bangunan/Sipil Lainnya'],
        ['409', 'Perabot Umum'],
        ['410', 'Perabot Kayu'],
        ['411', 'Perabot Logam'],
        ['412', 'Perabot Lainnya'],
        ['413', 'Teknik Ketenagalistrikan'],
        ['414', 'Teknik Transmisi Tenaga Listrik'],
        ['415', 'Teknik Pembangkit Tenaga Listrik'],
        ['416', 'Teknik Pemanfaatan Tenaga Listrik'],
        ['417', 'Teknik Distribusi Tenaga Listrik'],
        ['418', 'Teknik Listrik Industri'],
        ['419', 'Teknik Listrik/Elektro Lainnya'],
        ['420', 'Teknik Mesin'],
        ['421', 'Teknik Pengelasan'],
        ['422', 'Teknik Fabrikasi Logam'],
        ['423', 'Teknik Pengecoran Logam'],
        ['424', 'Teknik Pemesinan'],
        ['426', 'Teknik Gambar Mesin'],
        ['427', 'Teknik Mekanik Otomotif'],
        ['428', 'Teknik Alat Berat'],
        ['429', 'Teknik Perbaikan Bodi Otomotif'],
        ['430', 'Teknik Ototronik'],
        ['431', 'Teknik Mesin Lainnya'],
        ['432', 'Tata Boga'],
        ['433', 'Restoran'],
        ['434', 'Patiseri'],
        ['436', 'Tata Kecantikan'],
        ['437', 'Tata Kecantikan Kulit'],
        ['438', 'Tata Kecantikan Rambut'],
        ['439', 'Spa'],
        ['440', 'Tata Kecantikan Lainnya'],
        ['441/698', 'Tata Busana'],
        ['442', 'Design Busana'],
        ['445', 'Agribisnis Ternak Ruminansia/ Budidaya'],
        ['446', 'Agribisnis Ternak Unggas/ Budidaya'],
        ['447', 'Budidaya Ternak Harapan/Agribisnis'],
        ['448', 'Budidaya Ternak Lainnya'],
        ['449', 'Agribisnis Perikanan/Budidaya Ikan'],
        ['450', 'Budidaya Ikan Air Tawar'],
        ['451', 'Budidaya Ikan Air Laut'],
        ['452', 'Budidaya Ikan Air Payau'],
        ['453', 'Agribisnis Rumput Laut'],
        ['454', 'Budidaya Perikanan Lainnya'],
        ['455', 'Teknologi Hasil Pertanian'],
        ['456', 'Pengolahan Hasil Pertanian Pangan/Teknologi Pengolahan Hasil Pertanian'],
        ['457', 'Pengolahan Hasil Pertanian Non Pangan'],
        ['458', 'Pengawasan Mutu'],
        ['460', 'Desain dan Produksi Kriya Tekstil'],
        ['461', 'Desain dan Produksi Kriya Kulit'],
        ['462', 'Desain dan Produksi Kriya Keramik'],
        ['463', 'Desain dan Produksi Kriya Logam'],
        ['464', 'Desain dan Produksi Kriya Kayu'],
        ['465', 'Kriya Lainnya'],
        ['467', 'Pemesinan Pesawat Udara (Aircraft'],
        ['468', 'Konstruksi Rangka Pesawat Udara'],
        ['469', 'Konstruksi Badan Pesawat Udara (Aircraft'],
        ['470', 'Pemeliharaan dan Perbaikan Motor dan Rangka Pesawat Udara (Airframe Power Plant)'],
        ['471', 'Pemeliharaan dan Perbaikan Instrumen'],
        ['472', 'Kelistrikan Pesawat Udara (Aircraft'],
        ['473', 'Elektronika Pesawat Udara (Aviation'],
        ['474', 'Teknologi Pesawat Terbang Lainnya'],
        ['476', 'Teknik Konstruksi Kapal Baja'],
        ['477', 'Teknik Pengelasan Kapal'],
        ['478', 'Teknik Instalasi Pemesinan Kapal'],
        ['479', 'Kelistrikan Kapal'],
        ['480', 'Teknik Gambar Rancang Bangun Kapal'],
        ['481', 'Teknik Konstruksi Kapal Kayu'],
        ['482', 'Teknik Perkapalan Lainnya'],
        ['484', 'Teknik Pemintalan Serat Buatan'],
        ['485', 'Teknik Pembuatan Benang'],
        ['486', 'Teknik Pembuatan Kain'],
        ['487', 'Teknologi Pencelupan'],
        ['488', 'Teknologi Pencapan'],
        ['489', 'Teknologi Tekstil Khusus Lainnya'],
        ['490/834', 'Teknik Grafika'],
        ['491', 'Produksi Grafika'],
        ['492', 'Persiapan Grafika'],
        ['493', 'Grafika Khusus Lainnya'],
        ['494', 'Geologi Pertambangan Umum'],
        ['495', 'Geologi Pertambangan'],
        ['497', 'Geologi Khusus Lainnya'],
        ['499', 'Kontrol Proses'],
        ['500', 'Kontrol Mekanik'],
        ['504', 'Kimia Umum'],
        ['505', 'Teknik Kimia Industri'],
        ['506', 'Kimia Analisis'],
        ['507', 'Kimia Lainnya'],
        ['508', 'Pelayaran'],
        ['509', 'Nautika Kapal Niaga'],
        ['510', 'Teknika Kapal Niaga'],
        ['511', 'Nautika Kapal Penangkap Ikan'],
        ['512', 'Teknika Kapal Penangkap Ikan'],
        ['513', 'Teknika Kapal/Pelayaran lainnya'],
        ['514', 'Teknik Telekomunikasi'],
        ['515', 'Teknik Transmisi Radio'],
        ['516', 'Teknik Transmisi Kabel'],
        ['517', 'Teknik Suitsing'],
        ['518', 'Teknik Akses Radio'],
        ['519', 'Teknik Akses Kabel'],
        ['521', 'Teknik Survey dan Pemetaan'],
        ['522', 'Teknik Survei dan Pemetaan Lainnya'],
        ['523', 'Teknik Komputer dan Informatika'],
        ['524', 'Rekayasa Perangkat Lunak'],
        ['525', 'Teknik Komputer dan Jaringan'],
        ['526', 'Multimedia'],
        ['527', 'TIK Khusus Lainnya'],
        ['528', 'Teknik Broadcasting'],
        ['529', 'Teknik Produksi dan Penyiaran Program'],
        ['530', 'Teknik Produksi dan Penyiaran Program'],
        ['531', 'Teknik Radio, Televisi dan Film Lainnya'],
        ['533', 'Teknik Audio Video'],
        ['534', 'Teknik Elektronika Industri'],
        ['535', 'Teknik Elektronika Khusus Lainnya'],
        ['540', 'Akuntansi'],
        ['548', 'Usaha Layanan Pariwisata'],
        ['549', 'Perhotelan'],
        ['551', 'Agribisnis dan Agrotek'],
        ['552', 'Budidaya Tanaman Umum'],
        ['553', 'Agribisnis Tanaman Pangan dan Hortikultura/Budidaya Tanaman Pangan'],
        ['554', 'Budidaya Tanaman Sayuran'],
        ['555', 'Budidaya Tanaman Hias'],
        ['556', 'Budidaya Tanaman Buah Tahunan'],
        ['557', 'Budidaya Tanaman Buah Semusim'],
        ['558', 'Agribisnis Tanaman Perkebunan/Budidaya'],
        ['559', 'Pengolahan Hasil Hutan'],
        ['560', 'Pembibitan Tanaman/ Agribisnis'],
        ['562', 'Seni Rupa Umum'],
        ['564', 'Grafis Komunikasi'],
        ['565', 'Animasi'],
        ['566', 'Seni Rupa Khusus Lainnya'],
        ['567', 'Seni Pertunjukan Umum'],
        ['568', 'Seni Musik Klasik'],
        ['569', 'Seni Musik Non Klasik'],
        ['570', 'Seni Tari'],
        ['571', 'Seni Karawitan'],
        ['572', 'Seni Pedalangan'],
        ['573', 'Seni Teater'],
        ['574', 'Seni Pertunjukan Lainnya'],
        ['582', 'Teknologi Farmasi'],
        ['586', 'Teknik Kendaraan Ringan'],
        ['587', 'Teknik Sepeda Motor'],
        ['588', 'Teknik Konstruksi Kapal Fiberglass'],
        ['589', 'Interior Kapal'],
        ['590', 'Teknik Penyempurnaan Tekstil'],
        ['591', 'Garmen'],
        ['592', 'Teknik Pelayanan Produksi'],
        ['595', 'Teknik Produksi Perminyakan'],
        ['596', 'Teknik Pemboran Minyak'],
        ['597', 'Teknik Pengolahan Minyak, Gas, dan Petro'],
        ['598', 'Teknik Mekatronika'],
        ['599', 'Teknik Transmisi Telekomunikasi'],
        ['600', 'Teknik Jaringan Akses'],
        ['601', 'Farmasi Industri'],
        ['603', 'Seni Lukis'],
        ['604', 'Seni Patung'],
        ['605', 'Desain Komunikasi Visual'],
        ['606', 'Desain Produksi Interior dan Landscaping'],
        ['607', 'Usaha Perjalanan Wisata'],
        ['608', 'Jasa Boga'],
        ['609', 'Busana Butik'],
        ['610', 'Agribisnis Aneka Ternak'],
        ['611', 'Kesehatan Hewan'],
        ['612', 'Mekanisasi Pertanian'],
        ['613', 'Penyuluhan Pertanian'],
        ['614', 'Kehutanan (4 Tahun)'],
        ['616', 'Teknik Furnitur'],
        ['617', 'Teknik Instalasi Tenaga Listrik'],
        ['618', 'Teknik Otomasi Industri'],
        ['620', 'Teknik Perawatan Gedung'],
        ['621', 'Konstruksi dan Perawatan Bangunan Sipil'],
        ['622', 'Teknik Konstruksi dan Perumahan'],
        ['623', 'Desain Pemodelan dan Informasi Bangunan'],
        ['624', 'Teknik Pengelasan dan Fabrikasi Logam'],
        ['625', 'Teknik Jaringan Komputer dan Telekomunikasi'],
        ['626', 'Layanan Kesehatan'],
        ['627', 'Seni Pertunjukan'],
        ['641', 'Pemeranan'],
        ['642', 'Tata Artistik'],
        ['671', 'Geomatika'],
        ['672', 'Teknik Jaringan Tenaga Listrik'],
        ['673', 'Teknik Instalasi Pemanfaatan Tenaga Listrik'],
        ['674', 'Teknik Pemeliharaan Mekanik Industri'],
        ['675', 'Teknik Pergudangan'],
        ['676', 'Teknik Produksi Minyak dan Gas'],
        ['677', 'Teknik Pemboran Minyak dan Gas'],
        ['678', 'Teknik Elektronika Komunikasi'],
        ['679', 'Teknik Energi Hidro'],
        ['680', 'Teknik Energi Surya dan Angin'],
        ['681', 'Teknik Energi Biomassa'],
        ['682', 'Teknik Produksi dan penyiaran Program'],
        ['683', 'Pekerjaan Sosial'],
        ['685', 'Teknologi Pengolahan Hasil Perikanan'],
        ['686', 'Pengawasan Mutu Hasil Pertanian dan Perikanan'],
        ['687', 'Alat Mesin Pertanian'],
        ['688', 'Teknik Tanah dan Air'],
        ['689', 'Teknik Inventarisasi dan Pemetaan Hutan'],
        ['690', 'Teknik Konservasi Sumberdaya Hutan'],
        ['691', 'Teknik Rehabilitasi dan Reklamasi Hutan'],
        ['692', 'Teknik Produksi Hasil Hutan'],
        ['693', 'Budidaya Perikanan'],
        ['694', 'Budidaya Krustacea'],
        ['695', 'Budidaya Kekerangan'],
        ['696', 'Budidaya Rumput Laut'],
        ['698', 'Busana'],
        ['699', 'Desain Interior'],
        ['746', 'Bahasa Jawa'],
        ['747', 'Bahasa Madura'],
        ['748', 'Bahasa Sunda'],
        ['749', 'Bahasa Daerah Lainnya'],
        ['750', 'Bahasa Bali'],
        ['800', 'Guru Pendidikan Luar Biasa/Pendidikan Khusus'],
        ['802', 'Kepariwisataan'],
        ['803', 'Agribisnis Pengolahan Hasil Pertanian dan Perikanan'],
        ['810', 'Guru Bimbingan Konseling (Konselor)'],
        ['830', 'Teknologi Konstruksi dan Properti'],
        ['831', 'Teknik Geomatika dan Geospasial'],
        ['831', 'Teknik Geospasial'],
        ['832', 'Teknik Mesin'],
        ['833', 'Teknik Pesawat Udara'],
        ['835', 'Teknik Instrumentasi Industri'],
        ['836', 'Teknik Industri'],
        ['837', 'Teknik Tekstil'],
        ['838', 'Teknik Kimia'],
        ['839', 'Teknik Konstruksi Kapal'],
        ['840', 'Teknik Elektronika'],
        ['841', 'Teknik Perminyakan'],
        ['842', 'Teknik Energi Terbarukan'],
        ['843', 'Kesehatan Gigi'],
        ['844', 'Teknologi Laboratorium Medik'],
        ['845', 'Agribisnis Tanaman'],
        ['846', 'Agribisnis Ternak'],
        ['847', 'Kesehatan Hewan'],
        ['848', 'Agribisnis Pengolahan Hasil Pertanian'],
        ['849', 'Usaha Pertanian Terpadu'],
        ['851', 'Pelayaran Kapal Penangkap Ikan'],
        ['852', 'Pelayaran Kapal Niaga'],
        ['853', 'Agribisnis Perikanan'],
        ['854', 'Pengolahan Hasil Perikanan'],
        ['856', 'Manajemen Perkantoran'],
        ['857', 'Akuntansi dan Keuangan Lembaga'],
        ['859', 'Kuliner'],
        ['860', 'Desain dan Produksi Kriya'],
        ['861', 'Seni Musik'],
        ['862', 'Seni Broadcasting dan Film'],
    ];

    /**
     * Peta linieritas SD: nama bidang studi (huruf kecil) → mapel linier.
     *
     * @var array<string, array<int, string>>
     */
    public const PETA_SD = [
        'guru kelas tk' => ['Guru Kelas'],
        'guru kelas ra' => ['Guru Kelas'],
        'guru kelas sd/umum (kelas awal dan akhir)' => ['Guru Kelas', self::KODING],
        'guru kelas mi' => ['Guru Kelas', self::KODING],
        'matematika' => ['Guru Kelas', self::KODING],
        'pendidikan kewarganagaraan' => ['Guru Kelas', self::KODING],
        'bahasa indonesia' => ['Guru Kelas', self::KODING],
        'ilmu pengetahuan alam (fisika)' => ['Guru Kelas', self::KODING],
        'ilmu pengetahuan sosial' => ['Guru Kelas', self::KODING],
        'bahasa daerah' => [self::MULOK],
        'muatan lokal lain-lain sesuai potensi daerah' => [self::MULOK],
        'pendidikan kewarganegaraan (pkn)' => ['Guru Kelas', self::KODING],
        'bahasa indonesia (sastra)' => ['Guru Kelas', self::KODING],
        'bahasa inggris' => ['Bahasa Inggris', 'Guru Kelas', self::KODING],
        'pengetahuan alam (ipa terpadu, fisika)' => ['Guru Kelas', self::KODING],
        'pengetahuan sosial (sosiologi, ips terpadu)' => ['Guru Kelas', self::KODING],
        'pendidikan jasmani (olahraga & kesehatan)' => ['PJOK'],
        'geografi' => ['Guru Kelas', self::KODING],
        'sejarah' => ['Guru Kelas', self::KODING],
        'ekonomi (umum, koperasi, akuntansi)' => ['Guru Kelas', self::KODING],
        'biologi' => ['Guru Kelas', self::KODING],
        'pendidikan pancasila dan kewarganegaraan (ppkn)' => ['Guru Kelas', self::KODING],
        'fisika' => ['Guru Kelas', self::KODING],
        'kimia' => ['Guru Kelas', self::KODING],
        'ekonomi' => ['Guru Kelas', self::KODING],
        'sosiologi' => ['Guru Kelas', self::KODING],
        'antropologi' => ['Guru Kelas', self::KODING],
        'seni budaya' => ['Seni/Prakarya'],
        'pendidikan jasmani olah raga dan kesehatan' => ['PJOK'],
        'informatika' => [self::KODING],
        'keterampilan komputer dan pengelolaan informasi (kkpi)' => ['Guru Kelas', self::KODING],
        'kimia umum' => ['Guru Kelas', self::KODING],
        'teknik telekomunikasi' => [self::KODING],
        'teknik komputer dan informatika' => [self::KODING],
        'teknologi informasi dan komunikasi' => [self::KODING],
        'rekayasa perangkat lunak' => [self::KODING],
        'pengembangan perangkat lunak dan gim' => [self::KODING],
        'teknik komputer dan jaringan' => [self::KODING],
        'multi media' => [self::KODING],
        'tik khusus lainnya' => [self::KODING],
        'seni rupa' => ['Seni/Prakarya'],
        'seni rupa khusus lainnya' => ['Seni/Prakarya'],
        'seni musik klasik' => ['Seni/Prakarya'],
        'seni musik non klasik' => ['Seni/Prakarya'],
        'seni tari' => ['Seni/Prakarya'],
        'seni teater' => ['Seni/Prakarya'],
        'seni pertunjukan lainnya' => ['Seni/Prakarya'],
        'teknik jaringan akses' => [self::KODING],
        'teknik jaringan komputer dan telekomunikasi' => [self::KODING],
        'seni pertunjukan' => ['Seni/Prakarya'],
        'tata artistik' => ['Seni/Prakarya'],
        'bahasa jawa' => [self::MULOK],
        'bahasa madura' => [self::MULOK],
        'bahasa sunda' => [self::MULOK],
        'bahasa daerah lainnya' => [self::MULOK],
        'bahasa bali' => [self::MULOK],
        'guru pendidikan luar biasa/pendidikan khusus' => ['Guru Kelas', self::PKH],
        'guru bimbingan konseling (konselor)' => ['Guru Kelas', self::KODING],
        'seni musik' => ['Seni/Prakarya'],
    ];

    /**
     * Peta linieritas SMP: nama bidang studi (huruf kecil) → mapel linier.
     * Bidang kejuruan yang tidak tercantum eksplisit mengikuti pola dominan
     * Lampiran III (linier ke Prakarya) — lihat mapelLinier().
     *
     * @var array<string, array<int, string>>
     */
    public const PETA_SMP = [
        'bahasa daerah' => ['Seni/Prakarya', self::MULOK],
        'muatan lokal lain-lain sesuai potensi daerah' => [self::MULOK],
        'pendidikan kewarganegaraan (pkn)' => ['Pancasila'],
        'bahasa indonesia (sastra)' => ['Bahasa Indonesia'],
        'bahasa inggris' => ['Bahasa Inggris'],
        'matematika' => ['Matematika', 'Informatika', self::KODING],
        'pengetahuan alam (ipa terpadu, fisika)' => ['IPA', 'Seni/Prakarya', 'Informatika'],
        'ilmu pengetahuan alam (ipa)' => ['IPA', 'Seni/Prakarya', 'Informatika'],
        'pengetahuan sosial (sosiologi, ips terpadu)' => ['IPS', 'Seni/Prakarya'],
        'ilmu pengetahuan sosial (ips)' => ['IPS', 'Seni/Prakarya'],
        'kesenian, budaya dan keterampilan' => ['Seni/Prakarya'],
        'pendidikan jasmani (olahraga & kesehatan)' => ['PJOK'],
        'teknologi informasi dan komunikasi (tik)' => ['Seni/Prakarya', 'Informatika', self::KODING],
        'geografi' => ['IPS'],
        'sejarah' => ['IPS'],
        'ekonomi (umum, koperasi, akuntansi)' => ['IPS', 'Seni/Prakarya'],
        'biologi' => ['IPA', 'Seni/Prakarya', 'Informatika'],
        'pendidikan pancasila dan kewarganegaraan (ppkn)' => ['Pancasila'],
        'bahasa indonesia' => ['Bahasa Indonesia'],
        'fisika' => ['IPA', 'Seni/Prakarya', 'Informatika'],
        'kimia' => ['IPA', 'Seni/Prakarya', 'Informatika'],
        'ekonomi' => ['IPS', 'Seni/Prakarya'],
        'sosiologi' => ['IPS'],
        'antropologi' => ['IPS'],
        'seni budaya' => ['Seni/Prakarya'],
        'pendidikan jasmani olah raga dan kesehatan' => ['PJOK'],
        'informatika' => ['Seni/Prakarya', 'Informatika', self::KODING],
        'keterampilan' => ['Seni/Prakarya'],
        'keterampilan komputer dan pengelolaan informasi (kkpi)' => ['Seni/Prakarya', 'Informatika', self::KODING],
        'bahasa jawa' => ['Seni/Prakarya', self::MULOK],
        'bahasa madura' => ['Seni/Prakarya', self::MULOK],
        'bahasa sunda' => ['Seni/Prakarya', self::MULOK],
        'bahasa daerah lainnya' => ['Seni/Prakarya', self::MULOK],
        'bahasa bali' => ['Seni/Prakarya', self::MULOK],
        'guru pendidikan luar biasa/pendidikan khusus' => [self::PKH],
        'guru bimbingan konseling (konselor)' => [self::BK],
        'teknik kimia' => ['IPA', 'Seni/Prakarya'],
        'teknik elektronika' => ['Seni/Prakarya', 'Informatika', self::KODING],
        'teknik instrumentasi industri' => ['Seni/Prakarya', 'Informatika', self::KODING],
        'desain dan produksi kriya' => ['Seni/Prakarya'],
        'seni musik' => ['Seni/Prakarya'],
        'seni broadcasting dan film' => ['Seni/Prakarya', 'Informatika'],
    ];

    /** Normalisasi nama bidang studi untuk pencocokan. */
    public static function kunciNama(?string $nama): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $nama)));
    }

    /**
     * Daftar bidang studi untuk pilihan isian (gabungan SD + SMP, unik per
     * nama). Setiap entri: ['kode' => string, 'nama' => string].
     *
     * @return array<int, array{kode: string, nama: string}>
     */
    public static function daftarBidangStudi(): array
    {
        $gabung = [];
        foreach ([self::BIDANG_SD, self::BIDANG_SMP] as $daftar) {
            foreach ($daftar as [$kode, $nama]) {
                $gabung[self::kunciNama($nama)] ??= ['kode' => $kode, 'nama' => $nama];
            }
        }
        $hasil = array_values($gabung);
        usort($hasil, fn ($a, $b) => strcasecmp($a['nama'], $b['nama']));

        return $hasil;
    }

    /** Kode bidang studi dari namanya (per jenjang bila diketahui). */
    public static function kodeUntuk(?string $nama, ?string $jenjang = null): ?string
    {
        $kunci = self::kunciNama($nama);
        if ($kunci === '') {
            return null;
        }
        $daftar = strtoupper((string) $jenjang) === 'SD'
            ? [self::BIDANG_SD, self::BIDANG_SMP]
            : [self::BIDANG_SMP, self::BIDANG_SD];
        foreach ($daftar as $list) {
            foreach ($list as [$kode, $n]) {
                if (self::kunciNama($n) === $kunci) {
                    return $kode;
                }
            }
        }

        return null;
    }

    /**
     * Mapel-mapel yang linier untuk satu bidang studi pada satu jenjang.
     * Null bila bidang studi tidak dikenal di Kepmen 222.
     *
     * @return array<int, string>|null
     */
    public static function mapelLinier(?string $bidangStudi, ?string $jenjang): ?array
    {
        $kunci = self::kunciNama($bidangStudi);
        if ($kunci === '') {
            return null;
        }
        if (strtoupper((string) $jenjang) === 'SD') {
            if (isset(self::PETA_SD[$kunci])) {
                return self::PETA_SD[$kunci];
            }
            // Bidang hanya ada di daftar SD tapi belum terpetakan: tak dinilai.
            foreach (self::BIDANG_SD as [, $n]) {
                if (self::kunciNama($n) === $kunci) {
                    return null;
                }
            }

            return null;
        }
        if (isset(self::PETA_SMP[$kunci])) {
            return self::PETA_SMP[$kunci];
        }
        // Pola dominan Lampiran III: bidang kejuruan lain → Prakarya.
        foreach (self::BIDANG_SMP as [, $n]) {
            if (self::kunciNama($n) === $kunci) {
                return ['Seni/Prakarya'];
            }
        }

        return null;
    }

    /**
     * Status linieritas seorang guru terhadap mapel tujuan di satu jenjang.
     *
     * @return array{status: string, label: ?string, kelas: ?string}
     */
    public static function statusUntuk(Guru $guru, ?string $jenjangTujuan, string $mapelTujuan): array
    {
        $mapel = AnalisisKebutuhanGuru::kanonis($mapelTujuan);

        // Semua label agama (Agama, Agama Islam, Agama Kristen, dst.)
        // berada di luar cakupan Kepmen 222 — lihat pagar linieritas.
        if (str_starts_with($mapel, 'Agama')) {
            return ['status' => 'luar_cakupan',
                'label' => 'Di luar cakupan Kepmen 222',
                'kelas' => 'badge-info'];
        }
        if (! $guru->sudah_sertifikasi) {
            return ['status' => 'tanpa_label', 'label' => null, 'kelas' => null];
        }
        if (empty($guru->bidang_studi_serdik)) {
            return ['status' => 'belum_ada_data',
                'label' => 'Bidang studi serdik belum diisi',
                'kelas' => 'badge-warning'];
        }
        $jenjang = $jenjangTujuan ?: $guru->jenjang;
        $linier = self::mapelLinier($guru->bidang_studi_serdik, $jenjang);
        if ($linier === null) {
            return ['status' => 'tidak_dapat_dinilai',
                'label' => 'Bidang studi tak terdaftar di Kepmen 222',
                'kelas' => 'badge-ghost'];
        }

        return in_array($mapel, $linier, true)
            ? ['status' => 'linier', 'label' => 'Linier', 'kelas' => 'badge-success']
            : ['status' => 'tidak_linier',
                'label' => 'Tidak linier — risiko tunjangan profesi',
                'kelas' => 'badge-error'];
    }
}
