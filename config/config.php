<?php
/**
 * DonateHub - Konfigurasi Aplikasi
 */

// Konfigurasi dasar
define('APP_NAME', 'DonateHub');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Platform Donasi Online');
define('CONTACT_EMAIL', 'noreply@donatehub.com');
define('CONTACT_PHONE', '+6281234567890');
define('BASE_URL', 'http://localhost/donasiKu');
define('BASEPATH', dirname(__DIR__));
define('PUBLIC_PATH', BASEPATH . '/public');
define('DEBUG_MODE', true); // Set to true to enable debug logging
define('MAINTENANCE_MODE', false); // Set to true to enable maintenance mode
define('ENABLED_PAYMENT_METHODS', ['bank_transfer', 'credit_card', 'e_wallet']); // Available payment methods

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Doku
define('DOKU_CLIENT_ID', 'BRN-0289-1747666046225');  // Client ID Doku
define('DOKU_SECRET_KEY', 'SK-gvaZhzhyTyu4MDaXK1Cu');  // Secret key Doku
define('DOKU_ENVIRONMENT', 'sandbox');  // 'sandbox' atau 'production'
define('DOKU_MERCHANT_ID', 'BRN-0289-1747666046225');  // Merchant ID Doku
define('DOKU_PUBLIC_KEY', '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhPFmu6gXnZ0T5Wo/FLewgHIizkvS/IxHWrvD1MZlaZ/EYdTo+FHf9QsNOleyRsq9kP6b+8bVDveWIZq5Zwy5N/5qUxwa3HGfkkKorH7aBpTQmOzYajOWQbMoisoo1ui5O9ju0Rg5no1zwtupJixZgp2B+iKFTswqh7bO2MzjGCnLP95vdIIYBetwR84JWHV3hsDznaeAXhPJQq8awb9JAMje2ZcXFz0ilJD9BqTMYb4Vj1qsPgQXeqQ6m7dtj5i6AqYACiKgWg/uSkzy5IO5nJjZs7BwlHKZZXqnxItJr9O/8DC+x+VOI1EOiYrBKQPeIJNbTPrVz5OEtJLHE3Ic9wIDAQAB
-----END PUBLIC KEY-----');

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