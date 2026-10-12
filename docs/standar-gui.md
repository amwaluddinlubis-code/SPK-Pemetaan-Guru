# Standar GUI — PetaPenempatan ASN

Versi 1.0 — 2026-10-10. Acuan wajib untuk semua halaman aplikasi,
agar tampilan konsisten walau dikerjakan bergantian / dibantu AI.

## 1. Fondasi

- **Tailwind CSS v4** + **daisyUI** (di-build via Vite dari `resources/css/app.css`).
- **Blade** untuk template, **Livewire** untuk interaksi reaktif, **Alpine.js**
  (bawaan Livewire) untuk interaksi kecil seperti tutup alert.
- Jangan menambah framework CSS/JS baru tanpa persetujuan.
- Setelah mengubah CSS/JS: jalankan `npm run build` (atau `npm run dev`
  saat pengembangan).

## 1b. Komponen Fondasi (wajib dipakai ulang)

Jangan menulis ulang markup di bawah — pakai komponen anonim `resources/views/components/`:

| Komponen | Pakai | Untuk |
|---|---|---|
| `<x-halaman>` | `judul`, slot `aksi`/`deskripsi` | Header semua halaman index/show |
| `<x-kartu>` | `class` tambahan | Pembungkus kartu (`card bg-base-100 shadow-xl`) |
| `<x-kosong>` | `pesan`, `kolom` (opsional) | Keadaan kosong (baris tabel / blok) |
| `<x-hapus>` | `aksi`, `tanya`, `label` | Form hapus + konfirmasi |
| `<x-galat>` | — | Daftar error validasi di atas form |
| `<x-info-halaman>` | `data` (paginator) | "Menampilkan X–Y dari Z data" |
| `<x-alert>` | `tipe` (sukses/error/warning/info) | Alert umum + slot isi |
| `<x-ikon>` | `nama`, `class` | Ikon Heroicons outline |

Aturan: komponen baru hanya bila pola dipakai ≥3 tempat; varian kecil
diselesaikan via atribut/`class` tambahan, bukan file baru.

## 2. Tema & Warna

- Tema daisyUI: `light` (`<html data-theme="light">` di layout).
- Warna primer aplikasi: **indigo/violet** (`bg-primary`, bawaan tema).
  Navbar memakai `navbar bg-primary text-primary-content`.
- Jangan memakai warna heksadesimal mentah di view; selalu pakai
  kelas semantik daisyUI (`primary`, `secondary`, `success`, `warning`,
  `error`, `info`, `base-100`, dst.) agar ikut tema.

## 3. Tipografi

- Font: bawaan sistem (tidak ada font kustom — jangan tambah kecuali disetujui).
- Judul halaman: `h1` dengan `text-2xl font-bold`.
- Judul kartu: `card-title` (daisyUI).
- Teks penjelas/petunjuk: `text-sm opacity-60` atau `opacity-70`.

## 4. Struktur Halaman

Semua halaman (kecuali login) memakai `layouts.app`:

```blade
@extends('layouts.app')
@section('judul', 'Nama Halaman — PetaPenempatan ASN')
@section('konten')
    {{-- isi --}}
@endsection
```

- Format judul tab browser: `Nama Halaman — PetaPenempatan ASN`
  (jangan lupa akhiran "— PetaPenempatan ASN").
- Konten otomatis terbungkus `main.container.mx-auto.px-4.py-6.max-w-6xl`.
- Pola kepala halaman (header + tombol aksi kanan):

```blade
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">[ikon] Nama Halaman</h1>
    <a href="..." class="btn btn-primary">+ Tambah Data</a>
</div>
```

- Tombol aksi utama selalu di kanan atas, di bawah navbar.
- Tombol aksi hanya tampil untuk peran yang berhak
  (`@if (auth()->user()->hasRole(...))`).

## 5. Tombol

| Fungsi | Kelas | Contoh |
|---|---|---|
| Aksi utama halaman | `btn btn-primary` | Tambah, Simpan, Generate |
| Aksi tabel (kecil) | `btn btn-sm` + warna | Lihat / Ubah / Hapus |
| Setujui | `btn btn-success btn-sm` | Setujui |
| Tolak / Hapus | `btn btn-error btn-sm` | Tolak, Hapus |
| Peringatan | `btn btn-sm btn-warning` | Verifikasi |
| Sekunder / batal | `btn btn-ghost` atau `btn btn-secondary` | Batal, Kembali |
| Keluar | `btn btn-ghost btn-sm` | di navbar |

- Urutan tombol aksi di tabel: **Lihat → Ubah/Setujui → Tolak/Hapus**
  (dari paling aman ke paling merusak).
- Aksi merusak (hapus, tolak, generate ulang) **wajib** dialog konfirmasi
  browser: `onsubmit="return confirm('...?...')"` pada form.
- Teks tombol: kata kerja Bahasa Indonesia ("Tambah Guru", bukan "Add").

## 6. Tabel Data

```blade
<div class="card bg-base-100 shadow-xl">
    <div class="card-body p-0 overflow-x-auto">
        <table class="table table-zebra">
            <thead>...</thead>
            <tbody>...</tbody>
        </table>
    </div>
</div>
```

- Selalu `table-zebra`; pakai `table-sm` jika kolom > 6.
- `overflow-x-auto` wajib agar tabel bisa digeser di HP.
- Baris kosong: satu `<tr>` dengan pesan, contoh:
  "Belum ada rekomendasi SPK. Klik Generate SPK untuk mencari penempatan."
- Pagination Laravel standar di bawah tabel (`$data->links()`).

## 7. Formulir (daisyUI v5)

```blade
<fieldset class="fieldset w-full mb-4">
    <legend class="fieldset-legend font-semibold mb-1">Nama Label</legend>
    <input type="text" name="nama" value="{{ old('nama') }}"
           class="input w-full">
</fieldset>
```

- Input teks: `input w-full` (bordered by default di v5)
- Pilihan: `select w-full`
- Teks panjang: `textarea w-full`
- File: `file-input w-full`
- **Jangan pakai** kelas v4 yang sudah dihapus di daisyUI v5:
  `form-control`, `label-text` (di dalam form), `input-bordered`,
  `select-bordered`, `textarea-bordered`, `file-input-bordered`
  — kelas-kelas itu tidak ada efeknya di v5.
- Label checkbox/toggle tetap pakai pola v5 `<label class="label">`
  + `<span class="label-text">`.
- Pertahankan isian lama dengan `old('nama')` dan tampilkan error
  validasi di atas form (`$errors->any()` → `alert alert-error`).
- Form tambah/ubah dibungkus `card bg-base-100 shadow-xl` + `card-body`.

## 8. Alert & Notifikasi

- Sukses (data tersimpan, disetujui, dsb.): ditaruh di `layouts.app`
  otomatis dari `session('sukses')` → `alert alert-success mb-4`
  dengan tombol tutup (✕) Alpine. Controller cukup:
  `return back()->with('sukses', 'Pesan...')`.
- Error validasi form: `alert alert-error` di atas form.
- Jangan membuat format notifikasi baru di tiap halaman.

## 9. Badge Status

Satu status = satu warna, berlaku di semua halaman:

| Status | Kelas badge |
|---|---|
| menunggu, diajukan, rekomendasi | `badge-warning` |
| diverifikasi | `badge-info` |
| disetujui | `badge-success` |
| ditolak | `badge-error` |
| batal_karena_konflik, nonaktif, draf | `badge-ghost` |

- Definisikan lewat method `badgeStatus()` di Model, jangan tulis
  logika warna di Blade.
- Tab filter status: `badge badge-lg`, aktif = `badge-primary`,
  nonaktif = `badge-ghost`.

## 10. Bahasa & Ikon

- Seluruh teks antarmuka **Bahasa Indonesia**.
- Ikon memakai **emoji secukupnya** di judul halaman dan tombol utama
  (contoh: peta, target, amplop) — konsisten, jangan campur gaya
  emoji dan SVG di satu halaman.
- Istilah baku (jangan diganti-ganti):

| Pakai | Jangan pakai |
|---|---|
| Guru | Teacher / Pengajar |
| Sekolah | School |
| Penempatan | Placement |
| Usulan | Proposal / Pengajuan |
| Mutasi Silang | Swap saja |
| Disetujui / Ditolak | Approve / Reject |

## 11. Peta (Leaflet)

- Peta memakai Leaflet + tile OpenStreetMap.
- Warna marker baku: **biru** = guru (domisili), **hijau** = sekolah.
- Legenda warna selalu ditampilkan di bawah peta.
- Popup marker: nama + info kunci (mapel / kecamatan), maksimal 3 baris.

## 12. Responsif (HP)

- Layout memakai container responsif bawaan; tabel wajib
  `overflow-x-auto`.
- Panel branding di halaman login disembunyikan di layar kecil
  (`hidden lg:flex`).
- Uji tiap halaman baru di lebar 360px (HP) dan 1366px (laptop).

## 13. Checklist Halaman Baru

- [ ] `@section('judul', '... — PetaPenempatan ASN')`
- [ ] Header `h1.text-2xl.font-bold` + tombol aksi kanan (sesuai peran)
- [ ] Tabel interaktif WAJIB komponen Livewire `Tabel*` (lihat Aturan Livewire di AGENTS.md): pola kartu + `table-zebra` + `overflow-x-auto`
- [ ] Form pakai pola `form-control` + `old()` + error validasi
- [ ] Badge status mengikuti tabel warna §9
- [ ] Aksi merusak punya `confirm()`
- [ ] Teks Bahasa Indonesia, istilah baku §10
- [ ] Tampil baik di 360px dan 1366px
- [ ] `npm run build` sukses setelah ubah CSS/JS

## 14. Catatan Audit 2026-10-10

Ditemukan saat penyusunan standar ini (untuk dibereskan bertahap):

1. `swap/index` dan `spk/index`: `@section('judul')` belum memakai
   akhiran "— PetaPenempatan ASN".
2. Warna badge status swap/SPK belum memakai `badgeStatus()` di Model
   (masih ditulis manual di Blade) — samakan dengan pola Usulan.
