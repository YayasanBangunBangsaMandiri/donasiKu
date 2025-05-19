<?php
/**
 * DonateHub - Konfigurasi Aplikasi (CONTOH)
 * 
 * Salin file ini ke config.php dan sesuaikan dengan konfigurasi Anda
 */

// Konfigurasi dasar
define('APP_NAME', 'DonateHub');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/donasiKu');

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Doku
define('DOKU_CLIENT_ID', 'xxxxxxxxxxxxxxxx');  // Ganti dengan client ID Doku
define('DOKU_SECRET_KEY', 'xxxxxxxxxxxxxxxx');  // Ganti dengan secret key Doku
define('DOKU_ENVIRONMENT', 'sandbox');  // 'sandbox' atau 'production'
define('DOKU_MERCHANT_ID', 'xxxxxxxx');  // Ganti dengan Merchant ID Doku

// Konfigurasi Email
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'noreply@donatehub.com');
define('MAIL_FROM_NAME', 'DonateHub');

// Konfigurasi keamanan
define('ENCRYPTION_KEY', hash('sha256', 'donatehub-secret-key'));  // Ganti dengan kunci rahasia Anda
define('SESSION_LIFETIME', 7200); // dalam detik (2 jam)
define('ENABLE_2FA', true);

// Konfigurasi upload file
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Konfigurasi donasi
define('DEFAULT_DONATION_AMOUNTS', [
    50000 => '50.000',
    100000 => '100.000',
    500000 => '500.000',
    1000000 => '1.000.000'
]);
define('ALLOW_CUSTOM_AMOUNT', true);
define('MIN_DONATION_AMOUNT', 10000);
define('MAX_DONATION_AMOUNT', 100000000); 