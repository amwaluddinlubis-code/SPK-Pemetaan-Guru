# Audit Keamanan Aplikasi — 11 Oktober 2026

## Cakupan dan batasan

Audit statis terhadap branch `main` sebelum perubahan pada branch
`security/login-throttle-demo-seeder`. Fokus pada autentikasi dan proses seed.
Ini bukan audit penetrasi dan tidak mencakup pengujian server production.

## Temuan terkonfirmasi

### SEC-AUTH-001 — Endpoint login tidak memiliki pembatasan percobaan

- **Risiko:** Tinggi jika login dapat dijangkau dari internet.
- **Bukti:** `routes/web.php` sebelumnya mendaftarkan POST `/login` tanpa
  middleware pembatasan laju; `AuthController@login` menerima percobaan berulang.
- **Dampak:** Memudahkan percobaan password otomatis terhadap akun.
- **Perbaikan di branch ini:** Tambahkan `throttle:5,1` pada POST login.
- **Verifikasi yang ditambahkan:** Tes fitur mengirim lima percobaan gagal lalu
  memastikan percobaan keenam pada jendela yang sama mendapat HTTP 429.
- **Batasan:** Batas berbasis IP dapat memengaruhi pengguna di balik NAT bersama.
  Konfigurasi rate limit perlu ditinjau berdasarkan pola trafik saat deployment.

### SEC-SEED-001 — Seeder membuat akun demo dengan password yang diketahui publik

- **Risiko:** Tinggi jika seeder dijalankan di staging/production.
- **Bukti:** `database/seeders/AdminSeeder.php` menyimpan email dan password
  demo yang mudah ditebak; `DatabaseSeeder` memanggil seeder akun tersebut.
  Dokumentasi sudah memperingatkan agar akun tidak dipakai di production, tetapi
  peringatan saja tidak mencegah akun dibuat.
- **Dampak:** `php artisan migrate --seed` pada lingkungan salah konfigurasi dapat
  membuat akun dengan kredensial yang dapat ditebak.
- **Perbaikan di branch ini:** `DatabaseSeeder` membatasi data guru contoh dan akun
  demo ke `local`/`testing`; `AdminSeeder` juga memiliki guard mandiri dan tidak
  membuat akun di lingkungan lain.
- **Verifikasi yang ditambahkan:** Tes fitur mengatur `app.env=production`, menjalankan
  `AdminSeeder`, lalu memastikan akun demo administrator tidak dibuat.
- **Catatan operasional:** Produksi tetap memerlukan prosedur provisioning akun awal
  yang aman. Jangan menyelesaikannya dengan mengaktifkan akun demo.
- **Risiko residual terkait:** `database.sql` juga merupakan dump demo yang berisi akun
  contoh/kredensial yang diketahui. README kini melarang impor dump tersebut ke staging/
  production; untuk production gunakan migrasi dan prosedur provisioning akun terpisah.

## Perubahan yang diajukan

- Pembatasan laju login.
- Guard lingkungan untuk seeder data contoh dan akun demo.
- Tes regresi untuk kedua kontrol.
- Pembaruan README tentang perilaku seeding per lingkungan.

## Belum diverifikasi / pekerjaan lanjutan

- Belum dilakukan uji penetrasi, audit dependency, atau pengujian deployment.
- Belum diverifikasi seluruh jalur kebocoran data pribadi/koordinat pada setiap peran.
- Belum ada fitur reset password aman, autentikasi multifaktor, atau audit log.
- Uji PHPUnit untuk branch ini harus menunggu hasil GitHub Actions; perubahan tidak
  dianggap lolos hanya karena workflow telah dibuat.
