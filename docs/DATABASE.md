# Database NetCalc

## Konfigurasi bawaan: SQLite

NetCalc memakai SQLite pada `database/database.sqlite`. PHP dari XAMPP dapat menjalankannya tanpa mengaktifkan MySQL di XAMPP Control Panel.

Gunakan pengaturan berikut pada `.env`:

```dotenv
DB_CONNECTION=sqlite
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Untuk checkout baru:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan config:clear
php artisan migrate
php artisan migrate:status
```

`DB_DATABASE` dapat dihapus atau dikomentari saat menggunakan lokasi SQLite bawaan. Jika menentukan lokasi SQLite sendiri, gunakan path absolut yang sesuai mesin tersebut.

## Pilihan lain: MySQL atau MariaDB dari XAMPP

Langkah ini opsional dan tidak diperlukan untuk fitur NetCalc saat ini.

1. Aktifkan MySQL dari XAMPP Control Panel.
2. Buat database kosong bernama `netcalc`, misalnya melalui phpMyAdmin. Gunakan port yang ditetapkan pada instalasi XAMPP Anda.
3. Ubah pengaturan lokal `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=netcalc
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Isi username dan password sesuai instalasi sendiri. Contoh password kosong hanya berlaku jika akun lokal memang diatur demikian. Pastikan ekstensi `pdo_mysql` aktif pada PHP yang digunakan terminal.

4. Terapkan konfigurasi dan buat tabel:

```bash
php artisan config:clear
php artisan migrate
php artisan migrate:status
```

Perubahan ini memilih database yang baru; data SQLite tidak otomatis dipindahkan ke MySQL. Mengganti database juga tidak memindahkan aktivitas latihan dari browser ke server.

## Lokasi data

| Data | Lokasi bawaan |
| --- | --- |
| Tabel Laravel | `database/database.sqlite` |
| Session | `storage/framework/sessions` |
| Cache aplikasi | `storage/framework/cache/data` |
| Log aplikasi | `storage/logs` |
| Riwayat latihan, statistik, dan input untuk lanjutkan latihan | localStorage browser, key `netcalc-activities-v1` |
| Preferensi animasi | localStorage browser, key `netcalc-motion-paused` |

Aktivitas dibatasi 50 latihan terakhir, dengan lima aktivitas terbaru ditampilkan di dashboard. Membuka input yang sama memperbarui aktivitas sebelumnya. Penyimpanan mengikuti origin: `localhost`, `127.0.0.1`, HTTP, HTTPS, dan port yang berbeda memiliki penyimpanan browser masing-masing.

`.env`, database lokal, session, cache, dan log tidak perlu diunggah ke repository. Pengembang lain membuat konfigurasi dan database sendiri melalui petunjuk instalasi.
