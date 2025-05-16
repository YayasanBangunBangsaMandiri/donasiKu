<?php
// Cek ekstensi PHP yang tersedia
echo "<h2>Cek Ekstensi PHP</h2>";
echo "<pre>";
echo "Ekstensi PDO tersedia: " . (extension_loaded('pdo') ? 'Ya' : 'Tidak') . "\n";
echo "Ekstensi PDO MySQL tersedia: " . (extension_loaded('pdo_mysql') ? 'Ya' : 'Tidak') . "\n";
echo "Ekstensi MySQLi tersedia: " . (extension_loaded('mysqli') ? 'Ya' : 'Tidak') . "\n";
echo "Ekstensi MySQLnd tersedia: " . (extension_loaded('mysqlnd') ? 'Ya' : 'Tidak') . "\n";
echo "</pre>";

// Cek koneksi database dengan PDO
echo "<h2>Cek Koneksi Database dengan PDO</h2>";
echo "<pre>";
try {
    $host = 'localhost';
    $dbname = 'donatehub';
    $username = 'root';
    $password = '';
    
    // Coba koneksi PDO
    echo "Mencoba koneksi PDO...\n";
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Koneksi PDO berhasil!\n";
} catch(PDOException $e) {
    echo "Koneksi PDO gagal: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Cek koneksi database dengan MySQLi
echo "<h2>Cek Koneksi Database dengan MySQLi</h2>";
echo "<pre>";
if (extension_loaded('mysqli')) {
    try {
        $host = 'localhost';
        $dbname = 'donatehub';
        $username = 'root';
        $password = '';
        
        // Coba koneksi MySQLi
        echo "Mencoba koneksi MySQLi...\n";
        $conn = new mysqli($host, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Koneksi gagal: " . $conn->connect_error);
        }
        echo "Koneksi MySQLi berhasil!\n";
        $conn->close();
    } catch(Exception $e) {
        echo "Koneksi MySQLi gagal: " . $e->getMessage() . "\n";
    }
} else {
    echo "Ekstensi MySQLi tidak tersedia\n";
}
echo "</pre>";

// Informasi PHP
echo "<h2>Informasi PHP</h2>";
echo "<pre>";
echo "Versi PHP: " . phpversion() . "\n";
echo "Lokasi php.ini: " . php_ini_loaded_file() . "\n";
echo "Sistem Operasi: " . PHP_OS . "\n";
echo "</pre>";

// Daftar semua ekstensi yang dimuat
echo "<h2>Daftar Semua Ekstensi PHP yang Dimuat</h2>";
echo "<pre>";
$loaded_extensions = get_loaded_extensions();
sort($loaded_extensions);
echo implode("\n", $loaded_extensions);
echo "</pre>";
?> 