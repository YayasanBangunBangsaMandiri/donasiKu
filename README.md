# DonasiKu - Platform Donasi Online

DonasiKu adalah platform donasi online untuk mengelola kampanye penggalangan dana dengan integrasi pembayaran Midtrans.

## Fitur Utama

- Manajemen kampanye donasi
- Integrasi pembayaran Midtrans (Transfer Bank, E-Wallet, QRIS)
- Preset dan custom donation amounts
- Panduan pembayaran interaktif
- Notifikasi real-time untuk donasi
- Dashboard admin untuk manajemen kampanye dan transaksi
- Keamanan dengan 2FA untuk admin

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache atau Nginx)
- Composer untuk manajemen dependensi
- Akun Midtrans (Sandbox atau Production)
- Ekstensi PHP: pdo_mysql atau mysqli

## Instalasi

1. **Clone repository**

```bash
git clone https://github.com/username/donasiKu.git
cd donasiKu
```

2. **Install dependensi dengan Composer**

```bash
composer install
```

3. **Setup database**

- Buat database MySQL baru
- Jalankan file setup.bat di folder database untuk membuat struktur database
- Atau import manual file schema.sql ke database Anda

```bash
cd database
setup.bat
```

4. **Konfigurasi**

- Edit file `config/config.php` sesuai dengan lingkungan Anda
- Ubah konfigurasi database di `config/database.php`
- Sesuaikan konfigurasi Midtrans dengan sandbox key atau production key Anda

5. **Jalankan aplikasi**

```bash
php -S localhost:8888
```

## Mengatasi Masalah "could not find driver"

Jika Anda mengalami error "Koneksi database gagal: could not find driver", ini menunjukkan bahwa ekstensi PHP PDO_MySQL belum diaktifkan. Ikuti langkah-langkah berikut:

1. **Aktifkan ekstensi PDO_MySQL**:
   - Buka file php.ini Anda (lokasi dapat ditemukan dengan menjalankan `php -i | findstr php.ini`)
   - Cari dan hapus tanda komentar (;) di depan baris:
     ```
     ;extension=pdo_mysql
     ;extension=mysqli
     ```
   - Menjadi:
     ```
     extension=pdo_mysql
     extension=mysqli
     ```
   - Simpan file dan restart server web Anda

2. **Gunakan XAMPP terbaru**:
   - XAMPP sudah menyertakan ekstensi PDO_MySQL yang diaktifkan secara default
   - Download dari [website resmi XAMPP](https://www.apachefriends.org/)

3. **Gunakan file php.ini alternatif**:
   - Salin file php.ini dari instalasi XAMPP ke direktori C:\eRaporSMK\php\ atau
   - Buat file php.ini baru di direktori proyek dengan isi:
     ```
     extension=pdo_mysql
     extension=mysqli
     ```
   - Jalankan dengan perintah: `php -c php.ini -S localhost:8888`

4. **Untuk Instalasi PHP dengan Path Non-Standar (seperti eRaporSMK)**:
   - Buat file php.ini di direktori proyek dengan isi berikut:
     ```
     [PHP]
     extension_dir = "C:\path\ke\direktori\ext"
     extension=C:\path\ke\direktori\ext\php_pdo_mysql.dll
     extension=C:\path\ke\direktori\ext\php_mysqli.dll
     ```
   - Pastikan path mengarah ke direktori ekstensi PHP yang benar
   - Gunakan file batch `jalankan-server.bat` yang disediakan untuk menjalankan server

5. **Verifikasi Ekstensi Terinstall**:
   - Jalankan file `cek-php-ext.php` dengan perintah: `php -c php.ini cek-php-ext.php`
   - Pastikan ekstensi PDO, PDO_MySQL, MySQLi, dan MySQLnd tersedia

## Akses Login

### Akun Admin
- URL: http://localhost:8888/donasiKu/auth/login
- Email default: admin@donasiku.com
- Password default: admin123

### Akun Pengguna
- URL: http://localhost:8888/donasiKu/auth/login
- Pendaftaran baru: http://localhost:8888/donasiKu/auth/register

### Keamanan Akun
- Verifikasi email diperlukan untuk akun baru
- Two-Factor Authentication (2FA) tersedia untuk akun admin
- Fitur reset password tersedia melalui email

## Tata Cara Donasi

### Membuat Donasi Tanpa Login
1. Buka halaman utama: http://localhost:8888/donasiKu
2. Pilih kampanye yang ingin didonasikan
3. Tentukan jumlah donasi (preset atau custom)
4. Isi data diri (nama, email, nomor telepon)
5. Pilih metode pembayaran melalui Midtrans
6. Ikuti petunjuk pembayaran sesuai metode yang dipilih
7. Setelah pembayaran berhasil, status donasi akan diperbarui otomatis

### Membuat Donasi dengan Login
1. Login ke akun Anda
2. Pilih kampanye dari beranda atau menu kampanye
3. Tentukan jumlah donasi
4. Data diri akan terisi otomatis dari profil
5. Pilih metode pembayaran
6. Selesaikan pembayaran
7. Pantau status donasi di dashboard Anda

### Melacak Donasi
1. Login ke akun Anda
2. Akses menu "Donasi Saya"
3. Lihat semua riwayat donasi dan statusnya
4. Unduh bukti donasi untuk keperluan pajak atau dokumentasi

## Tutorial Dashboard Admin

### Akses Dashboard Admin
1. Login menggunakan akun admin: http://localhost:8888/donasiKu/auth/login
2. Dashboard admin dapat diakses di: http://localhost:8888/donasiKu/admin/dashboard

### Manajemen Kampanye
1. Akses menu "Kampanye" di sidebar admin
2. Lihat semua kampanye dengan fitur pencarian dan filter
3. Buat kampanye baru:
   - Klik tombol "Tambah Kampanye"
   - Isi formulir (judul, deskripsi, target donasi, gambar)
   - Upload foto/gambar kampanye
   - Tentukan durasi kampanye
   - Pilih kategori dan tag
   - Klik "Simpan" untuk membuat kampanye

4. Edit kampanye:
   - Klik tombol "Edit" pada kampanye yang ingin diubah
   - Perbarui informasi kampanye
   - Klik "Simpan" untuk menyimpan perubahan

5. Kelola status kampanye:
   - Aktif/Nonaktifkan kampanye
   - Arsipkan kampanye selesai
   - Tandai kampanye sebagai pilihan/featured

### Manajemen Donasi
1. Akses menu "Donasi" di sidebar admin
2. Lihat semua transaksi donasi dengan detail lengkap
3. Filter donasi berdasarkan status, tanggal, atau kampanye
4. Lihat detail donasi dengan mengklik tombol "Lihat Detail"
5. Update status donasi secara manual jika diperlukan
6. Ekspor data donasi ke Excel/CSV untuk laporan

### Manajemen Pengguna
1. Akses menu "Pengguna" di sidebar admin
2. Lihat dan kelola semua akun pengguna
3. Aktifkan/Nonaktifkan akun pengguna
4. Reset password pengguna
5. Tambahkan admin baru (khusus Super Admin)

### Laporan dan Statistik
1. Dashboard menampilkan ringkasan statistik
   - Total donasi terkumpul
   - Jumlah kampanye aktif
   - Jumlah donatur
   - Grafik donasi per periode
2. Laporan terperinci tersedia di menu "Laporan"
3. Filter laporan berdasarkan periode, kampanye, atau metode pembayaran
4. Ekspor laporan untuk keperluan audit atau dokumentasi

## Konfigurasi Midtrans

Untuk menggunakan fitur pembayaran, Anda perlu mendaftar di [Midtrans](https://midtrans.com/) dan mendapatkan API key:

1. Daftar dan login ke dashboard Midtrans
2. Dapatkan Client Key dan Server Key dari menu Settings > Access Keys
3. Update file `config/config.php` dengan key yang didapat:

```php
// Konfigurasi Midtrans
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-xxxxxxxxxxxxxxxx');  // Ganti dengan client key Midtrans
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-xxxxxxxxxxxxxxxx');  // Ganti dengan server key Midtrans
define('MIDTRANS_ENVIRONMENT', 'sandbox');  // 'sandbox' atau 'production'
define('MIDTRANS_MERCHANT_ID', 'G123456789');  // Ganti dengan Merchant ID Midtrans
```

## Penggunaan

### Manajemen Kampanye
1. Login ke akun admin
2. Akses menu Kampanye
3. Buat kampanye baru dengan judul, deskripsi, dan target donasi

### Proses Donasi
1. Pengguna memilih kampanye
2. Pilih jumlah donasi (preset atau custom)
3. Isi formulir data diri
4. Pilih metode pembayaran
5. Selesaikan pembayaran melalui Midtrans
6. Sistem akan otomatis memperbarui status donasi setelah pembayaran berhasil

### Callback Midtrans
Pastikan endpoint callback bisa diakses oleh Midtrans:
- Notification URL: `https://your-domain.com/donation/notification`
- Finish URL: `https://your-domain.com/donation/finish/{id}`
- Unfinish URL: `https://your-domain.com/donation/unfinish/{id}`
- Error URL: `https://your-domain.com/donation/error/{id}`

## Struktur Database

Sistem menggunakan beberapa tabel utama:
- `users`: Menyimpan data pengguna dan admin
- `campaigns`: Menyimpan data kampanye donasi
- `donations`: Menyimpan transaksi donasi
- `payment_guides`: Menyimpan panduan pembayaran
- `payment_logs`: Menyimpan log webhook Midtrans

## Kontribusi

Silakan berkontribusi pada proyek ini dengan membuat pull request atau melaporkan issue.

## Lisensi

[MIT License](LICENSE) 