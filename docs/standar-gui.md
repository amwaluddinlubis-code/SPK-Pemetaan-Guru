# Standar GUI — PetaPenempatan ASN

Versi 2.0 — 2026-10-24 (2.0: hasil redesign R0–R6; 1.1: 2026-10-23;
1.0: 2026-10-10). Acuan wajib untuk semua halaman aplikasi, agar
tampilan konsisten walau dikerjakan bergantian / dibantu AI.

## 1. Fondasi

- **Tailwind CSS v4** + **daisyUI v5** (di-build via Vite dari
  `resources/css/app.css`).
- **Blade** untuk template, **Livewire** untuk interaksi reaktif,
  **Alpine.js** (bawaan Livewire) untuk interaksi kecil.
- Jangan menambah framework CSS/JS baru tanpa persetujuan.
- Setelah mengubah CSS/JS: jalankan `npm run build`.

## 1b. Komponen Fondasi (wajib dipakai ulang)

Jangan menulis ulang markup di bawah — pakai komponen anonim
`resources/views/components/`:

| Komponen | Pakai | Untuk |
|---|---|---|
| `<x-halaman>` | `judul`, `ikon`, slot `aksi`/`deskripsi` | Header semua halaman index/show |
| `<x-kartu>` | `class` tambahan | Pembungkus kartu (`card bg-base-100 shadow-xl`) |
| `<x-kartu-kpi>` | label, nilai, konteks, nada, ikon | Kartu angka dashboard (wajib ada konteks, bukan angka telanjang) |
| `<x-lencana-status>` | `nada`, slot teks | Lencana status/warna bermakna (lihat §9) |
| `<x-modal-konfirmasi>` | dimuat sekali di layout | Semua konfirmasi aksi (pengganti confirm/prompt bawaan) |
| `<x-kosong>` | `pesan`, `kolom` (opsional) | Keadaan kosong (baris tabel / blok) |
| `<x-hapus>` | `aksi`, `tanya`, `label`, `ikonSaja`, `petunjuk` | Form hapus + konfirmasi |
| `<x-galat>` | — | Daftar error validasi di atas form |
| `<x-info-halaman>` | `data` (paginator) | "Menampilkan X–Y dari Z data" |
| `<x-alert>` | `tipe` (sukses/error/warning/info) | Alert umum + slot isi |
| `<x-ikon>` | `nama`, `class` | Ikon SVG (Heroicons outline) |
| `<x-searchable-select>` | `name`, `options`, `selected` | Combobox terpencarian (filter & form) |
| `<x-desa-select>` | mengikuti kecamatan terpilih | Pemilih desa dari API wilayah, tetap boleh ketik bebas |
| `<x-per-halaman>` | — (di dalam view Livewire tabel) | Pemilih baris per halaman 10/15/20/25/50/100 |
| `<x-chip-konteks>` | — | Chip cakupan konteks Pemda di halaman daftar |
| `komponen/kop-surat` | include | Kop surat resmi dokumen cetak (lihat §12) |

Aturan: komponen baru hanya bila pola dipakai ≥3 tempat; varian kecil
diselesaikan via atribut/`class` tambahan, bukan file baru.

## 2. Tema, Warna & Branding Pemda

- Tiga tema daisyUI: **terang / gelap / sistem**, dipilih pengguna
  lewat toggle tema di navbar (tersimpan di `localStorage tema-ppa`).
  Semua halaman WAJIB tampil baik di terang dan gelap.
- Warna aksen BUKAN warna tetap aplikasi: aksen mengikuti **branding
  Pemda aktif** (layanan `App\Services\Branding`, diatur Kabid PTK di
  Pengaturan Pemda: warna heks + logo). `Branding::gayaTema()`
  menimpa `--color-primary` dsb. untuk tema terang dan gelap.
- Tema gelap memakai heks aksen yang sama dengan tema terang
  (kontras teks primer terukur 5,47). JANGAN mencerahkan aksen di
  tema gelap — mencerahkan menurunkan kontras di bawah ambang AA.
- **Kontras teks minimal WCAG AA 4,5:1** untuk teks biasa (terukur
  pada QA R6: teks isi 17,7 terang / 14,8 gelap; tombol primer dan
  tautan bilah sisi 5,47; lencana 5,12–15,68).
- Di view, selalu pakai kelas semantik daisyUI (`primary`,
  `secondary`, `success`, `warning`, `error`, `info`, `base-100`,
  dst.) — jangan menulis warna heks mentah kecuali di layanan
  Branding dan penanda peta.
- Logo & nama Pemda (konteks aktif) tampil di bilah sisi, navbar,
  halaman masuk, pendaftaran publik, dan kop dokumen cetak.

## 3. Tipografi

- Font: **Plus Jakarta Sans** (dimuat di layout; token font daisyUI).
- Judul tab browser mengikuti Pemda aktif: `Nama Halaman — {Pemda}`.
- Judul halaman: lewat `<x-halaman>` (h1 + ikon SVG opsional).
- Judul kartu: `card-title` (daisyUI).
- Angka pada tabel & KPI memakai kelas `.angka` (tabular-nums) agar
  digit sejajar.
- Teks penjelas/petunjuk: `text-sm opacity-60` atau `opacity-70`.

## 4. Struktur Halaman & Navigasi

Semua halaman (kecuali login) memakai `layouts.app`:

- Kepala halaman memakai `<x-halaman>`: judul + ikon di kiri,
  tombol aksi di slot `aksi` kanan atas.
- **Navbar ramping**: hanya judul/konteks + toggle tema. Profil &
  Keluar berada di **kaki bilah sisi**, bukan di navbar.
- Bilah sisi: grup menu dari `RegistriMenu` tampil sebagai
  `<details class="menu-grup">` yang bisa dilipat; grup berisi
  halaman aktif selalu terbuka; status lipatan grup lain diingat di
  `localStorage spk-menu-grup`. Judul grup memakai huruf kapital
  kecil; warna teks judul grup MEWARISI warna tema (sama dengan item
  anak — bukan putih statis). Item aktif ditandai jelas.
- Konten terbungkus `main.container.mx-auto.px-4.py-6` (lebar penuh
  wadah, maksimum breakpoint container 1536px).
- Tombol aksi hanya tampil untuk peran yang berhak
  (`@if (auth()->user()->hasRole(...))`) — dan otorisasi tetap
  ditegakkan di server.

## 5. Tombol & Tooltip

**Standar utama (aturan bisnis #29, #32): semua tombol menu di
kepala halaman dan semua tombol aksi hanya berupa IKON**, tersusun
horizontal, dengan **tooltip daisyUI** sebagai labelnya:

```blade
<div class="grup-aksi grup-aksi-bungkus">
    <a href="..." class="btn btn-sm btn-accent tooltip tooltip-bottom"
       data-tip="Export Excel" aria-label="Export Excel">
        <x-ikon nama="unduh" class="h-4 w-4" />
    </a>
    {{-- tombol berikutnya menyatu sebagai grup (pola join) --}}
</div>
```

- Grup tombol memakai `.grup-aksi` (pola join, `btn-sm`); tambahkan
  `.grup-aksi-bungkus` hanya di bilah kepala halaman agar boleh
  membungkus di layar sempit. JANGAN membungkus grup di dalam sel
  tabel.
- Tooltip: kepala halaman `tooltip-bottom`; **aksi baris tabel
  `tooltip-left`** (agar tidak terpotong tepi kanan wadah tabel).
  Tooltip pada `<form>` pembungkus untuk tombol submit form.
- `aria-label` WAJIB pada setiap tombol hanya-ikon.
- Warna mengikuti fungsi: primer = aksi utama/tambah; `accent` =
  ekspor; `neutral` = cetak; `secondary` = impor & aksi massal;
  `info` = lihat/analisis; `warning` = ubah; `success` = setujui;
  `error` = tolak/hapus.
- Urutan tombol aksi di tabel: **Lihat → Ubah/Setujui → Tolak/Hapus**
  (dari paling aman ke paling merusak).
- Teks tombol hanya dipertahankan pada tombol formulir (Simpan,
  Batal, Kembali, Lanjut) dan aksi keputusan berlabel di halaman
  detail — bukan pada menu/aksi daftar.
- **Konfirmasi WAJIB modal daisyUI** `<x-modal-konfirmasi>`
  (atribut `data-konfirmasi`, `data-judul`, `data-pesan`,
  `data-tombol`, `data-warna`; varian alasan wajib `data-alasan`).
  DILARANG `confirm()`, `prompt()`, `alert()`, dan `wire:confirm`
  bawaan peramban.

## 6. Tabel Data

```blade
<div class="card bg-base-100 shadow-xl">
    <div class="card-body p-0 overflow-x-auto">
        <table class="table tabel-baku">
            <thead>...</thead>
            <tbody>...</tbody>
        </table>
    </div>
</div>
```

- Semua tabel memakai kelas **`.tabel-baku`** (kepala kolom kapital
  kecil, pemisah baris halus, zebra tipis, angka tabular).
- `overflow-x-auto` wajib agar tabel bisa digeser di HP.
- Baris kosong memakai `<x-kosong>` (ikon + pesan yang menjelaskan
  langkah berikutnya), bukan sel teks polos.
- Pagination di bawah tabel memakai **tema daisyUI terpusat**
  (`config/livewire.php`: `pagination_theme=daisyui`; pola `join` +
  `btn btn-sm join-item`, halaman aktif `btn-active`), dirender
  lewat `$data->links()` pada komponen Livewire. Di sampingnya
  tampil `<x-info-halaman>` dan `<x-per-halaman>`.
- Tabel interaktif WAJIB komponen Livewire `Tabel*` — lihat Aturan
  Livewire di AGENTS.md repo.

## 7. Formulir (daisyUI v5)

```blade
<fieldset class="fieldset w-full">
    <label class="fieldset-legend font-semibold" for="f-nama">Nama Label</label>
    <input id="f-nama" type="text" name="nama" value="{{ old('nama') }}"
           class="input w-full">
    @error('nama')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
</fieldset>
```

- Input teks: `input w-full`; pilihan: `select w-full`; teks
  panjang: `textarea w-full`; berkas: `file-input w-full`
  (bordered adalah bawaan v5).
- **Jangan pakai** kelas v4 yang sudah dihapus di daisyUI v5:
  `form-control`, `label-text` (di dalam form), `input-bordered`,
  `select-bordered`, `textarea-bordered`, `file-input-bordered`.
- **Error validasi WAJIB menempel di field-nya** (`@error` tepat di
  bawah input), selain ringkasan `alert alert-error` di atas form.
  Pesan validasi Bahasa Indonesia dari `lang/id/` — jangan menulis
  pesan Inggris bawaan.
- Form panjang dibagi **seksi/tab berpenjelas**: setiap seksi punya
  kalimat penjelas singkat di kepalanya; form multi-tab menandai
  jumlah error per tab dan membuka tab error pertama.
- Pertahankan isian lama dengan `old('field')` di semua field.
- Field yang tidak boleh diubah peran aktif ditampilkan **nonaktif
  (disabled) + catatan penjelas**, BUKAN disembunyikan atau dibiarkan
  bisa diedit lalu dibuang server (pola form guru, aturan bisnis
  #33).
- Istilah koordinat: **Lintang (Latitude)** dan **Bujur (Longitude)**.
- Form tambah/ubah dibungkus `card bg-base-100 shadow-xl` +
  `card-body`.

## 8. Alert & Notifikasi

- Sukses: `layouts.app` otomatis dari `session('sukses')` →
  `alert alert-success mb-4` dengan tombol tutup Alpine. Controller
  cukup `return back()->with('sukses', 'Pesan...')`.
- Error validasi form: `alert alert-error` di atas form + error per
  field (§7).
- Jangan membuat format notifikasi baru di tiap halaman.

## 9. Lencana Status

Satu status = satu nada warna, di semua halaman, lewat
**`<x-lencana-status>`** (teks tidak pernah membungkus:
`whitespace-nowrap`):

| Nada | Untuk |
|---|---|
| `sukses` | disetujui, aktif, terverifikasi, selesai |
| `peringatan` | menunggu, diajukan, rekomendasi, perlu perhatian |
| `bahaya` | ditolak, nonaktif, gagal |
| `info` | diverifikasi, dalam proses, netral-informatif |
| `utama` | status utama/penekanan merek |
| `gelap` | draf, batal, arsip |
| `netral` | lain-lain tanpa makna khusus |

Pemetaan status → nada didefinisikan di komponen/model, jangan
menulis logika warna menyebar di Blade.

## 10. Bahasa & Ikon

- Seluruh teks antarmuka **Bahasa Indonesia**.
- Ikon **SELALU SVG** (`<x-ikon>`), termasuk judul halaman, tombol,
  dan penanda status. **Emoji dilarang** di antarmuka (kerangka,
  tabel, tombol, peta, dokumen cetak).
- Istilah baku (jangan diganti-ganti):

| Pakai | Jangan pakai |
|---|---|
| Guru | Teacher / Pengajar |
| Sekolah | School |
| Penempatan | Placement |
| Usulan | Proposal / Pengajuan |
| Mutasi Silang | Swap saja |
| Disetujui / Ditolak | Approve / Reject |
| Lintang / Bujur | Latitude / Longitude saja |

## 11. Peta (Leaflet)

- Peta memakai Leaflet + tile OpenStreetMap, menjadi **fokus layar**
  (wadah relatif setinggi viewport, aturan bisnis #30).
- Panel rekomendasi **melayang** di kiri atas (maks 320px, bisa
  dilipat); legenda tetap di kiri bawah; kontrol zoom kanan bawah.
- Warna penanda baku: **guru** dan **sekolah** dibedakan konsisten
  di semua peta; mengarahkan kursor/klik kartu kandidat **menyorot**
  penandanya (amber, radius membesar).
- Popup penanda: nama + info kunci, maksimal 3 baris, tanpa emoji.
- Pemilih titik di form (`komponen/peta-picker`): bingkai
  `rounded-box`, petunjuk berikon info, tombol GPS berikon lokasi.

## 12. Dokumen Cetak

- Semua dokumen cetak (telaahan staf, daftar guru/sekolah/usulan)
  memakai **kop surat bersama** `komponen/kop-surat`: logo Pemda +
  nama Pemda konteks (huruf besar) + "DINAS PENDIDIKAN" + subjudul
  sistem. DILARANG kop statis per dokumen.
- Pola cetak: `@page` A4 dengan margin baku; blok tanda tangan
  `page-break-inside: avoid`; isi analisis tidak diubah oleh
  pemolesan tampilan.

## 13. Responsif & Aksesibilitas

- Uji tiap halaman di lebar **390px (HP)** dan **1440px (desktop)**,
  tema terang dan gelap (sapuan QA R6: 31 rute desktop, 5 rute
  gelap, 4 rute HP — semua 200, nol error JS).
- Fokus keyboard harus terlihat (outline fokus bawaan dipertahankan;
  ada tautan "Lewati ke konten utama").
- Kontras mengikuti §2 (AA 4,5:1 teks biasa).
- Panel branding halaman login disembunyikan di layar kecil
  (`hidden lg:flex`).

## 14. Checklist Halaman Baru

- [ ] Judul tab mengikuti Pemda aktif
- [ ] Kepala halaman `<x-halaman>` + slot aksi ikon-saja bertooltip (§5)
- [ ] Tabel interaktif komponen Livewire `Tabel*` + `.tabel-baku` + `<x-kosong>`
- [ ] Form pola fieldset v5 (§7) + `old()` + error menempel di field
- [ ] Status memakai `<x-lencana-status>` (§9)
- [ ] Aksi merusak memakai modal konfirmasi daisyUI (§5)
- [ ] Ikon SVG saja, tanpa emoji; teks Bahasa Indonesia, istilah baku §10
- [ ] Tampil baik di 390px & 1440px, tema terang & gelap, kontras AA
- [ ] `npm run build` sukses setelah ubah CSS/JS; suite tes hijau

## 15. Riwayat

- 1.0 (2026-10-10): standar awal.
- 1.1 (2026-10-23): wadah lebar penuh; catatan audit judul tab &
  badge manual (sudah dibereskan di 2.0).
- 2.0 (2026-10-24): hasil redesign R0–R6 — branding Pemda & tema
  gelap, Plus Jakarta Sans, bilah sisi grup lipat, kartu KPI
  berkonteks, tabel baku & lencana status, modal konfirmasi,
  tombol ikon-saja bertooltip daisyUI, layar peta melayang, form
  berkelompok dengan error per field, kop surat bersama, standar
  kontras & fokus terukur.
