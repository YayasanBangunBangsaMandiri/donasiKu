# DonateHub - Platform Donasi dengan Integrasi Midtrans

Platform donasi online yang memungkinkan pengguna untuk membuat kampanye donasi dan menerima donasi melalui berbagai metode pembayaran dengan integrasi Midtrans.

## Fitur Utama

### Untuk Donatur
- Donasi dengan jumlah preset atau custom
- Pembayaran melalui Midtrans (Virtual Account, E-wallet, Kartu Kredit)
- Panduan pembayaran interaktif untuk setiap metode pembayaran
- Notifikasi pembayaran real-time
- Riwayat donasi untuk pengguna yang terdaftar

### Untuk Admin
- Dashboard admin dengan statistik donasi
- Manajemen kampanye (tambah, edit, hapus, duplikat)
- Manajemen donasi (lihat, edit, batalkan)
- Laporan donasi (export PDF/Excel)
- Pengaturan situs dan konfigurasi donasi

## Teknologi

- PHP (MVC Pattern)
- MySQL
- Midtrans Payment Gateway
- Bootstrap 5
- JavaScript / jQuery
- Chart.js untuk visualisasi data

## Persyaratan Sistem

- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Ekstensi PHP: PDO, cURL, JSON, OpenSSL
- Web server (Apache/Nginx)
- Akun Midtrans (Sandbox untuk pengembangan)

## Instalasi

1. Clone repositori ini:
   ```
   git clone https://github.com/username/donatehub.git
   ```

2. Buat database MySQL:
   ```
   mysql -u root -p < database/schema.sql
   ```

3. Konfigurasi aplikasi:
   - Salin `config/config.example.php` ke `config/config.php`
   - Edit `config/config.php` dan sesuaikan konfigurasi database dan Midtrans

4. Install dependensi Midtrans:
   ```
   composer require midtrans/midtrans-php
   ```

5. Pastikan direktori `public/uploads` dapat ditulis oleh web server:
   ```
   chmod -R 755 public/uploads
   ```

6. Akses aplikasi melalui web browser:
   ```
   http://localhost/donatehub
   ```

## Konfigurasi Midtrans

1. Daftar akun di [Midtrans](https://midtrans.com/)
2. Dapatkan Client Key dan Server Key dari dashboard Midtrans
3. Update konfigurasi di `config/config.php`:
   ```php
   define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-XXXXXXXXXXXXXXXX');
   define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-XXXXXXXXXXXXXXXX');
   define('MIDTRANS_ENVIRONMENT', 'sandbox'); // 'sandbox' atau 'production'
   define('MIDTRANS_MERCHANT_ID', 'GXXXXXXXX');
   ```

4. Konfigurasi webhook di dashboard Midtrans:
   - URL: `https://yourdomain.com/donation/notification`
   - Pilih semua jenis notifikasi

## Struktur Direktori

```
donatehub/
├── app/
│   ├── controllers/    # Controller aplikasi
│   ├── models/         # Model data
│   ├── views/          # Template view
│   └── helpers/        # Helper functions
├── config/             # File konfigurasi
├── database/           # SQL schema dan migrations
├── public/             # Aset publik (CSS, JS, gambar)
│   ├── css/
│   ├── js/
│   ├── img/
│   └── uploads/        # Upload file
└── vendor/             # Dependensi pihak ketiga
```

## Akun Default

- Admin:
  - Email: admin@donatehub.com
  - Password: password

## Pengembangan Lanjutan

Proyek ini dirancang dengan pola MVC untuk memudahkan migrasi ke Laravel di masa depan. Struktur kode dan konvensi penamaan mengikuti praktik Laravel untuk memudahkan proses migrasi.

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE). 