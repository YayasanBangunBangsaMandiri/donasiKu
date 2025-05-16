<?php
/**
 * DonateHub - Konfigurasi Aplikasi
 */

// Konfigurasi dasar
define('APP_NAME', 'DonateHub');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost:8888/donasiKu');
define('BASEPATH', __DIR__ . '/..');

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Midtrans
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-xxxxxxxxxxxxxxxx');  // Ganti dengan client key Midtrans
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-xxxxxxxxxxxxxxxx');  // Ganti dengan server key Midtrans
define('MIDTRANS_ENVIRONMENT', 'sandbox');  // 'sandbox' atau 'production'
define('MIDTRANS_MERCHANT_ID', 'G123456789');  // Ganti dengan Merchant ID Midtrans

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
    10000 => '10.000',
    50000 => '50.000',
    100000 => '100.000',
    500000 => '500.000',
    1000000 => '1.000.000'
]);
define('ALLOW_CUSTOM_AMOUNT', true);
define('MIN_DONATION_AMOUNT', 10000); // Sesuai standar minimum Midtrans
define('MAX_DONATION_AMOUNT', 100000000); 