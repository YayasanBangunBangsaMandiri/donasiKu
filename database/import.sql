-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS donatehub;

-- Gunakan database
USE donatehub;

-- Impor skema dari file schema.sql
SOURCE schema.sql;

-- Tambahkan data admin default
INSERT INTO users (name, email, password, role, email_verified_at) VALUES
('Admin', 'admin@donatehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW());

-- Tambahkan data kategori awal
INSERT INTO categories (name, slug, description) VALUES
('Kesehatan', 'kesehatan', 'Bantuan untuk biaya pengobatan dan perawatan kesehatan'),
('Pendidikan', 'pendidikan', 'Bantuan untuk biaya pendidikan dan beasiswa'),
('Bencana Alam', 'bencana-alam', 'Bantuan untuk korban bencana alam'),
('Sosial', 'sosial', 'Bantuan untuk kegiatan sosial dan kemanusiaan'),
('Lingkungan', 'lingkungan', 'Bantuan untuk pelestarian lingkungan'); 