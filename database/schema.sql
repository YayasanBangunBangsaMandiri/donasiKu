-- DonateHub Database Schema

-- Hapus tabel jika sudah ada
DROP TABLE IF EXISTS payment_guides;
DROP TABLE IF EXISTS payment_logs;
DROP TABLE IF EXISTS campaign_updates;
DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS campaigns;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS categories;

-- Tabel Kategori
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Pengguna
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_picture VARCHAR(255) DEFAULT 'default.jpg',
    role ENUM('super_admin', 'admin', 'staff', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    verification_token VARCHAR(100),
    remember_token VARCHAR(100),
    two_factor_secret VARCHAR(100) NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Kampanye
CREATE TABLE campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    goal_amount DECIMAL(15, 2) NOT NULL,
    current_amount DECIMAL(15, 2) DEFAULT 0.00,
    featured_image VARCHAR(255) NOT NULL,
    banner_image VARCHAR(255) NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'active', 'completed', 'rejected') DEFAULT 'pending',
    is_featured BOOLEAN DEFAULT FALSE,
    allow_custom_amount BOOLEAN DEFAULT TRUE,
    donation_amounts JSON NULL, -- Untuk menyimpan jumlah donasi preset khusus kampanye
    donation_info TEXT NULL, -- Contoh: "100k = 1 school kit for a child"
    meta_tags VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Tabel Donasi
CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    user_id INT NULL, -- Bisa NULL untuk donasi anonim
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    amount DECIMAL(15, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_channel VARCHAR(50) NULL, -- BCA, BRI, Mandiri, OVO, dll.
    transaction_id VARCHAR(100) NULL, -- ID transaksi dari Midtrans
    order_id VARCHAR(100) NOT NULL, -- Order ID untuk Midtrans
    va_number VARCHAR(100) NULL, -- Nomor Virtual Account
    payment_url VARCHAR(255) NULL, -- URL pembayaran dari Midtrans
    message TEXT,
    is_anonymous BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'success', 'failed', 'canceled', 'refunded', 'expired') DEFAULT 'pending',
    payment_expiry TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    settlement_time TIMESTAMP NULL,
    refund_time TIMESTAMP NULL,
    notify_success BOOLEAN DEFAULT FALSE, -- Apakah notifikasi sukses sudah dikirim
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Update Kampanye
CREATE TABLE campaign_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
);

-- Tabel Panduan Pembayaran
CREATE TABLE payment_guides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_method VARCHAR(50) NOT NULL,
    payment_channel VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    steps TEXT NOT NULL, -- JSON untuk langkah-langkah
    image_path VARCHAR(255) NULL, -- Untuk tutorial GIF/gambar
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Log Pembayaran (untuk webhook dan debugging)
CREATE TABLE payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NULL,
    order_id VARCHAR(100) NULL,
    transaction_id VARCHAR(100) NULL,
    payment_type VARCHAR(50) NULL,
    payload TEXT NOT NULL, -- Untuk menyimpan JSON payload dari Midtrans
    status VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE SET NULL
);

-- Tabel Pesan Kontak
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    responded_by INT NULL,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert data kategori awal
INSERT INTO categories (name, slug, description, icon) VALUES
('Kesehatan', 'kesehatan', 'Bantuan untuk biaya pengobatan dan perawatan kesehatan', 'fa-heartbeat'),
('Pendidikan', 'pendidikan', 'Bantuan untuk biaya pendidikan dan beasiswa', 'fa-graduation-cap'),
('Bencana Alam', 'bencana-alam', 'Bantuan untuk korban bencana alam', 'fa-wind'),
('Sosial', 'sosial', 'Bantuan untuk kegiatan sosial dan kemanusiaan', 'fa-hands-helping'),
('Lingkungan', 'lingkungan', 'Bantuan untuk pelestarian lingkungan', 'fa-leaf');

-- Insert panduan pembayaran
INSERT INTO payment_guides (payment_method, payment_channel, title, description, steps) VALUES
('bank_transfer', 'bca', 'Cara Bayar Via Transfer BCA', 'Panduan langkah demi langkah untuk pembayaran melalui transfer BCA', '["Login ke Mobile Banking BCA", "Pilih menu Transfer", "Pilih BCA Virtual Account", "Masukkan nomor Virtual Account", "Masukkan nominal yang akan dibayarkan", "Masukkan PIN", "Konfirmasi pembayaran"]'),
('bank_transfer', 'bri', 'Cara Bayar Via Transfer BRI', 'Panduan langkah demi langkah untuk pembayaran melalui transfer BRI', '["Login ke Mobile Banking BRI", "Pilih menu Transfer", "Pilih BRI Virtual Account", "Masukkan nomor Virtual Account", "Masukkan nominal yang akan dibayarkan", "Masukkan PIN", "Konfirmasi pembayaran"]'),
('bank_transfer', 'mandiri', 'Cara Bayar Via Transfer Mandiri', 'Panduan langkah demi langkah untuk pembayaran melalui transfer Mandiri', '["Login ke Mobile Banking Mandiri", "Pilih menu Transfer", "Pilih Mandiri Virtual Account", "Masukkan nomor Virtual Account", "Masukkan nominal yang akan dibayarkan", "Masukkan PIN", "Konfirmasi pembayaran"]'),
('e-wallet', 'gopay', 'Cara Bayar Via GoPay', 'Panduan langkah demi langkah untuk pembayaran melalui GoPay', '["Buka aplikasi Gojek", "Pilih menu Pay", "Scan QR Code pembayaran", "Masukkan nominal yang akan dibayarkan", "Konfirmasi pembayaran"]'),
('e-wallet', 'ovo', 'Cara Bayar Via OVO', 'Panduan langkah demi langkah untuk pembayaran melalui OVO', '["Buka aplikasi OVO", "Pilih menu Scan", "Scan QR Code pembayaran", "Masukkan nominal yang akan dibayarkan", "Konfirmasi pembayaran"]');

-- Insert admin user
INSERT INTO users (name, email, password, role, email_verified_at, two_factor_enabled) VALUES
('Super Admin', 'super.admin@donatehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NOW(), TRUE),
('Admin', 'admin@donatehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), FALSE); 