<?php
/**
 * DonateHub - Konfigurasi Database
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'donatehub');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Kelas Database untuk mengelola koneksi database
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Eksekusi query dengan parameter yang aman
     * 
     * @param string $query SQL query dengan placeholder
     * @param array $params Parameter untuk dimasukkan ke query
     * @return PDOStatement
     */
    public function query($query, $params = []) {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Mendapatkan satu baris hasil
     * 
     * @param string $query SQL query
     * @param array $params Parameter untuk query
     * @return array|false Satu baris hasil atau false jika tidak ada
     */
    public function fetch($query, $params = []) {
        return $this->query($query, $params)->fetch();
    }
    
    /**
     * Mendapatkan semua baris hasil
     * 
     * @param string $query SQL query
     * @param array $params Parameter untuk query
     * @return array Semua baris hasil
     */
    public function fetchAll($query, $params = []) {
        return $this->query($query, $params)->fetchAll();
    }
    
    /**
     * Mendapatkan nilai tunggal
     * 
     * @param string $query SQL query
     * @param array $params Parameter untuk query
     * @return mixed Nilai tunggal hasil query
     */
    public function fetchColumn($query, $params = []) {
        return $this->query($query, $params)->fetchColumn();
    }
    
    /**
     * Mendapatkan ID terakhir yang dimasukkan
     * 
     * @return string Last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Memulai transaksi database
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaksi database
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaksi database
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
} 