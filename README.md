# SIPINLAB API

API REST untuk **SIPINLAB – Sistem Informasi Peminjaman Alat Laboratorium**.
Dibangun dengan Laravel 13, Laravel Sanctum (token API), dan MySQL.
Proyek ini disediakan dosen sebagai *backend* praktikum Pemrograman Frontend.

## Kebutuhan

- PHP 8.3 atau lebih baru (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `intl`)
- Composer 2
- MySQL 8 atau MariaDB 10.4+ (XAMPP/Laragon)

## Menjalankan di komputer lokal

```bash
# 1. Pasang dependensi PHP (berkas composer.lock akan dibuat otomatis)
composer install

# 2. Salin konfigurasi dan buat kunci aplikasi
cp .env.example .env          # Windows PowerShell: copy .env.example .env
php artisan key:generate

# 3. Buat basis data kosong bernama "sipinlab" di phpMyAdmin,
#    lalu sesuaikan DB_USERNAME dan DB_PASSWORD di .env

# 4. Buat tabel dan isi data contoh
php artisan migrate --seed

# 5. Jalankan server
php artisan serve
```

API dapat diakses di `http://127.0.0.1:8000/api`.

Akun contoh (kata sandi keduanya `password`):

| Surel | Peran |
| --- | --- |
| admin@sipinlab.test | admin (boleh menghapus data) |
| petugas@sipinlab.test | petugas |

Mengulang dari awal (hapus semua tabel lalu isi ulang): `php artisan migrate:fresh --seed`

## Daftar endpoint

Semua endpoint selain `GET /api` dan `POST /api/login` memerlukan header
`Authorization: Bearer <token>` dan `Accept: application/json`.

| Metode | Endpoint | Keterangan |
| --- | --- | --- |
| GET | /api | Informasi API (uji koneksi) |
| POST | /api/login | Masuk, mengembalikan token |
| GET | /api/me | Data pengguna yang sedang masuk |
| POST | /api/logout | Keluar (hapus token) |
| GET | /api/dashboard | Ringkasan angka dasbor |
| GET | /api/kategori | Daftar kategori |
| POST | /api/kategori | Tambah kategori |
| GET | /api/kategori/{id} | Detail kategori |
| PUT | /api/kategori/{id} | Ubah kategori |
| DELETE | /api/kategori/{id} | Hapus kategori (admin) |
| GET | /api/alat | Daftar alat: `search`, `kategori_id`, `kondisi`, `tersedia=1`, `page`, `per_page` |
| POST | /api/alat | Tambah alat |
| GET | /api/alat/{id} | Detail alat |
| PUT | /api/alat/{id} | Ubah alat |
| DELETE | /api/alat/{id} | Hapus alat (admin) |
| GET | /api/peminjaman | Daftar peminjaman: `status` (dipinjam/dikembalikan/terlambat), `search`, `page` |
| POST | /api/peminjaman | Catat peminjaman (stok berkurang otomatis) |
| GET | /api/peminjaman/{id} | Detail peminjaman |
| PATCH | /api/peminjaman/{id}/kembali | Catat pengembalian (stok bertambah) |

Kode status yang perlu ditangani *frontend*:
`200/201` berhasil, `401` token tidak ada/kedaluwarsa, `403` peran tidak diizinkan,
`404` data tidak ditemukan, `422` validasi gagal (lihat objek `errors`), `429` terlalu banyak percobaan masuk.

## Deploy ke Render

Repositori ini sudah berisi `Dockerfile` dan `docker/start.sh`. Lihat Modul 11.
Variabel lingkungan minimal di Render:

```
APP_KEY=base64:...            (hasil php artisan key:generate --show)
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<nama-layanan>.onrender.com
DB_CONNECTION=mysql
DB_HOST=...  DB_PORT=4000  DB_DATABASE=sipinlab  DB_USERNAME=...  DB_PASSWORD=...
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
CORS_ALLOWED_ORIGINS=https://<proyek-anda>.vercel.app
LOG_CHANNEL=stderr
SEED_DATA=true
```
