# DonasiKu - Platform Donasi Online

DonasiKu adalah platform donasi online untuk mengelola kampanye penggalangan dana dengan integrasi pembayaran Midtrans.

## Panduan Penggunaan

### Untuk Donatur

#### Cara Donasi Tanpa Login
1. Kunjungi website DonasiKu di `http://localhost/donasiKu`
2. Pilih kampanye yang ingin didukung dari halaman beranda
3. Klik tombol "Donasi Sekarang"
4. Tentukan jumlah donasi (pilih nominal yang tersedia atau masukkan jumlah kustom)
5. Isi data diri (nama, email, nomor telepon, dan pesan dukungan)
6. Pilih metode pembayaran melalui Midtrans
7. Ikuti instruksi pembayaran sesuai metode yang dipilih
8. Setelah pembayaran selesai, Anda akan menerima notifikasi melalui email
9. Cek status donasi melalui halaman "Cek Status" atau melalui link di email

#### Cara Donasi dengan Akun
1. Login ke akun Anda melalui `http://localhost/donasiKu/auth/login`
   - Email: (email yang telah didaftarkan)
   - Password: (password yang telah dibuat)
2. Pilih kampanye dari beranda atau menu kampanye
3. Tentukan jumlah donasi
4. Data diri akan terisi otomatis dari profil Anda
5. Pilih metode pembayaran
6. Selesaikan pembayaran
7. Pantau status donasi di dashboard akun Anda

#### Cara Mendaftar Akun Baru
1. Kunjungi `http://localhost/donasiKu/auth/register`
2. Isi formulir pendaftaran dengan data:
   - Nama Lengkap
   - Email (harus valid untuk verifikasi)
   - Password (minimal 8 karakter)
   - Konfirmasi Password
3. Klik tombol "Daftar"
4. Cek email Anda untuk tautan verifikasi
5. Klik tautan verifikasi untuk mengaktifkan akun
6. Login dengan email dan password yang telah didaftarkan

#### Fitur untuk Donatur
- Riwayat donasi lengkap
- Bukti/kuitansi donasi yang dapat diunduh
- Update kampanye dari penggalang dana
- Profil donatur yang dapat diedit
- Notifikasi donasi via email dan WhatsApp (opsional)

### Untuk Admin

#### Login ke Dashboard Admin
1. Akses `http://localhost/donasiKu/auth/login`
2. Masukkan kredensial admin:
   - Email: `admin@donatehub.com`
   - Password: `admin123`
   - **ATAU**
   - Email: `super.admin@donatehub.com`
   - Password: `admin123`
3. Anda akan diarahkan ke dashboard admin di `http://localhost/donasiKu/admin/dashboard`

#### Manajemen Kampanye
1. Dari dashboard admin, klik menu "Kampanye" di sidebar
2. Di halaman Kampanye, Anda dapat:
   - Melihat semua kampanye dengan filter dan pencarian
   - Menambahkan kampanye baru dengan tombol "Tambah Kampanye"
   - Mengedit kampanye dengan klik tombol "Edit"
   - Mengubah status kampanye (aktif/nonaktif)
   - Menghapus kampanye (hati-hati, tindakan ini tidak bisa dibatalkan)

#### Manajemen Donasi
1. Dari dashboard admin, klik menu "Donasi" di sidebar
2. Di halaman Donasi, Anda dapat:
   - Melihat semua donasi dengan filter status, tanggal, dll
   - Melihat detail donasi dengan klik "Lihat Detail"
   - Mengonfirmasi donasi manual jika diperlukan
   - Mengunduh bukti donasi dalam format PDF
   - Mengirim notifikasi kepada donatur
   - Menghapus donasi (hanya untuk admin)

#### Pengaturan Sistem
1. Dari dashboard admin, klik menu "Pengaturan" di sidebar
2. Di halaman Pengaturan, Anda dapat mengonfigurasi:
   - Informasi umum aplikasi (nama, deskripsi, dll)
   - Konfigurasi Midtrans untuk pembayaran
   - Pengaturan email untuk notifikasi
   - Nominal donasi default
   - Batas minimum dan maksimum donasi

#### Laporan dan Analitik
1. Dashboard admin menampilkan grafik dan statistik:
   - Total donasi terkumpul
   - Jumlah donatur aktif
   - Jumlah kampanye aktif
   - Tren donasi berdasarkan periode
2. Export data donasi ke Excel dan PDF dari halaman Donasi

## Troubleshooting

### Masalah Login (Redirect ke 404.php)

Jika Anda mengalami masalah saat login dimana halaman selalu diarahkan ke 404.php, coba solusi berikut:

1. **Pastikan menggunakan email dan password yang benar:**
   - Admin: `admin@donatehub.com` / `admin123`
   - Super Admin: `super.admin@donatehub.com` / `admin123`

2. **Periksa file `.htaccess`**
   - Pastikan file .htaccess terkonfigurasi dengan benar
   - Pastikan mod_rewrite diaktifkan di server Apache Anda

3. **Periksa file dan folder menggunakan kapitalisasi yang benar**
   - Di Linux, nama file/folder bersifat case-sensitive
   - Pastikan semua file controller menggunakan kapitalisasi yang benar (misalnya AdminController.php, bukan admincontroller.php)

4. **Pastikan database terhubung dengan benar**
   - Cek konfigurasi di `config/database.php`
   - Pastikan database `donatehub` sudah dibuat dan tabel-tabelnya sudah diimpor

5. **Cek error log**
   - Lihat error log PHP di `/opt/lampp/logs/error_log`
   - Aktifkan debug mode untuk melihat detail error

### Masalah Database

Jika Anda mengalami masalah dengan database:

1. **Periksa koneksi database**
   ```
   mysql -u root -e "SHOW DATABASES;" | grep donatehub
   ```

2. **Reset password admin jika lupa**
   ```
   UPDATE users SET password = '$2y$10$bslhAWqkGANA3h7ggzJR.OlnFtYNRVjtU1/ywbZRSn6Qwo.hpTgC.' WHERE email IN ('admin@donatehub.com', 'super.admin@donatehub.com');
   ```
   (Password akan diatur ke 'admin123')

3. **Cek struktur tabel users**
   ```
   mysql -u root -e "DESCRIBE donatehub.users;"
   ```

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache dengan mod_rewrite diaktifkan)
- Composer untuk manajemen dependensi
- Akun Midtrans (Sandbox atau Production)
- Ekstensi PHP: pdo_mysql atau mysqli, gd, curl, mbstring

## Instalasi Cepat

1. **Clone repository**
   ```bash
   git clone https://github.com/username/donasiKu.git
   cd donasiKu
   ```

2. **Install dependensi**
   ```bash
   composer install
   ```

3. **Setup database**
   ```bash
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS donatehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root donatehub < database/schema.sql
   ```

4. **Konfigurasi aplikasi**
   - Edit file `config/config.php` dan `config/database.php`
   - Sesuaikan BASE_URL dengan lokasi instalasi Anda

5. **Atur izin direktori**
   ```bash
   chmod -R 755 /opt/lampp/htdocs/donasiKu
   chmod -R 777 /opt/lampp/htdocs/donasiKu/public/uploads
   ```

6. **Akses aplikasi**
   - Buka browser dan kunjungi `http://localhost/donasiKu`
   - Login admin: `admin@donatehub.com` / `admin123`

## Fitur Utama

- Manajemen kampanye donasi
- Integrasi pembayaran Midtrans (Transfer Bank, E-Wallet, QRIS)
- Preset dan custom donation amounts
- Panduan pembayaran interaktif
- Notifikasi real-time untuk donasi
- Dashboard admin untuk manajemen kampanye dan transaksi
- Keamanan dengan 2FA untuk admin
- Laporan donasi dalam format Excel dan PDF
- Sistem notifikasi email dan WhatsApp

## Struktur Aplikasi

Aplikasi menggunakan pola MVC (Model-View-Controller):
- `app/Controllers`: Mengatur logika aplikasi
- `app/Models`: Mengelola data dan interaksi dengan database
- `app/Views`: Tampilan aplikasi
- `app/Helpers`: Fungsi-fungsi pembantu
- `config`: Konfigurasi aplikasi dan database
- `public`: Aset publik (CSS, JS, gambar)
- `database`: File-file SQL
- `vendor`: Dependensi pihak ketiga (dikelola oleh Composer) 