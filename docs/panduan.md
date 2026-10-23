# Panduan — PetaPenempatan ASN

Sistem informasi pemetaan penempatan Guru ASN berbasis domisili untuk
Dinas Pendidikan Kabupaten Mandailing Natal.

Teknologi: Laravel 13 + MySQL 8 + Livewire 4 + Tailwind CSS/daisyUI.
Status per **23 Oktober 2026**: prototipe fungsional yang terus dikembangkan. Sejak 12 Okt 2026 ditambahkan: integrasi referensi wilayah Kemendagri, Pengaturan Pemda (konteks wilayah ketat), pemilih terpencarian, import rombel Dapodik + analisis kebutuhan massal, pagination daisyUI + pemilih baris per halaman, **Pengaturan Akses** (matriks menu/route per peran), dan **Impersonate**. Aplikasi **belum dinyatakan siap production**. Hasil tes harus dikaitkan dengan commit/run CI yang benar-benar diverifikasi; jangan menganggap angka terdahulu sebagai bukti untuk commit terbaru. (Verifikasi lokal terakhir: suite penuh 167 test hijau pada commit `1341d68`.)

## Masalah yang Diselesaikan

Guru sering ditempatkan jauh dari domisilinya, padahal ada sekolah lain
yang kekurangan guru dan lebih dekat ke rumah guru tersebut. Aplikasi ini
memetakan domisili guru vs lokasi sekolah, lalu memberi rekomendasi
penempatan yang lebih adil dan efisien lewat tiga jalur: **usulan**,
**mutasi silang**, dan **SPK pengisian formasi**.

## Daftar Dokumen

| Dokumen | Isi |
|---|---|
| `docs/peran.md` | Peran pengguna, scope data, dan matriks hak akses |
| `docs/alur-kerja.md` | Alur kerja utama: pendataan, usulan, swap, SPK, dashboard pemda |
| `docs/aturan-bisnis.md` | 17 aturan bisnis kunci |
| `docs/model-data.md` | Ringkasan tabel dan relasi database |
| `docs/mesin-pemerataan.md` | Fitur tambahan: kualitas data, distribusi, simulasi, aksesibilitas, proyeksi, jejak audit |
| `docs/metode-saw-topsis.md` | Metode peringkat SPK: greedy + SAW–TOPSIS (kriteria, bobot, rumus, contoh hitung) |
| `docs/qa.md` | Aturan QA, hasil verifikasi, strategi locking, QA browser |
| `docs/roadmap.md` | To-do, prioritas menuju production, risiko |
| `docs/audit.md` | Log audit statis dan temuan keamanan (append-only) |
| `docs/standar-gui.md` | Pola tampilan & checklist halaman baru |
| `docs/deployment.md` | Checklist deployment production |
| `docs/verifikasi-data.md` | Prosedur verifikasi lapangan DATA-001 |
| `docs/kebijakan-bobot-keluarga.md` | Draf dasar kebijakan bobot keluarga GOV-001 |

Dokumen terkait lain: `.cursorrules` (aturan coding & alur kerja),
`README.md` (instalasi).
