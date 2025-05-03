# Website Pengecekan Kelulusan Siswa SMK NU 1 Slawi

Sistem pengecekan kelulusan siswa dengan fitur:
1. Pengecekan status kelulusan siswa berdasarkan nomor ujian dan password
2. Dashboard admin untuk mengelola data siswa
3. Dashboard superadmin untuk mengelola konfigurasi sistem

## Tech Stack
- PHP 7.4+ (Native)
- MySQL/MariaDB
- HTML5 + CSS3 + JavaScript
- Bootstrap 5
- Font Awesome 6

## Struktur Direktori
- `/`: File utama dan landing page
- `/assets`: File CSS, JavaScript, dan gambar
- `/admin`: Dashboard untuk admin
- `/superadmin`: Dashboard untuk superadmin
- `/config`: Konfigurasi database dan environment
- `/includes`: Komponen PHP yang dapat digunakan kembali

## Environment Configuration

Aplikasi ini menggunakan konfigurasi lingkungan (environment) untuk memudahkan proses deployment:

### Development Environment (Local)
Untuk menjalankan aplikasi di lingkungan development:

1. Pastikan file `config/env.php` diatur dengan:
   ```php
   $_ENV['APP_ENV'] = 'development';
   ```

2. URL yang digunakan akan otomatis menjadi `http://localhost/kelulusan2025`

### Production Environment (Server)
Untuk menjalankan aplikasi di server produksi:

1. Edit file `config/env.php` dan ubah konfigurasi menjadi:
   ```php
   $_ENV['APP_ENV'] = 'production';
   ```

2. URL yang digunakan akan otomatis menjadi `https://kelulusan.smknu1slawi.sch.id`

## Deployment ke Production

1. Upload semua file ke server hosting
2. Pastikan file `config/env.php` sudah diubah ke mode production
3. Konfigurasi database di `config/database.php` jika diperlukan
4. Pastikan permission file dan folder sudah benar
