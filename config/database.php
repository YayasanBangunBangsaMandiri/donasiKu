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
        // Cek apakah ekstensi pdo_mysql tersedia
        if (extension_loaded('pdo_mysql')) {
            // Gunakan PDO jika tersedia
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Koneksi database gagal (PDO): " . $e->getMessage());
            }
        } else if (extension_loaded('mysqli')) {
            // Gunakan MySQLi sebagai alternatif
            try {
                $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                
                if ($this->connection->connect_error) {
                    throw new Exception($this->connection->connect_error);
                }
                
                // Set charset
                $this->connection->set_charset(DB_CHARSET);
            } catch (Exception $e) {
                die("Koneksi database gagal (MySQLi): " . $e->getMessage());
            }
        } else {
            die("Koneksi database gagal: Ekstensi pdo_mysql atau mysqli tidak tersedia. Harap aktifkan salah satu ekstensi di php.ini Anda.");
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
     * @return PDOStatement|mysqli_stmt
     */
    public function query($query, $params = []) {
        if (extension_loaded('pdo_mysql') && $this->connection instanceof PDO) {
            // PDO query
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } else if ($this->connection instanceof mysqli) {
            // MySQLi query
            $stmt = $this->connection->prepare($query);
            
            if ($stmt === false) {
                die("Error dalam persiapan query: " . $this->connection->error);
            }
            
            if (!empty($params)) {
                // Buat array parameter dan tipe data
                $types = '';
                $values = [];
                
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                    $values[] = $param;
                }
                
                // Referensi parameter untuk bind_param
                $refs = [];
                foreach ($values as $key => $value) {
                    $refs[$key] = &$values[$key];
                }
                
                // Tambahkan tipe ke awal array
                array_unshift($refs, $types);
                
                // Bind parameter
                call_user_func_array([$stmt, 'bind_param'], $refs);
            }
            
            $stmt->execute();
            return $stmt;
        }
        
        return null;
    }
    
    /**
     * Mendapatkan satu baris hasil
     * 
     * @param string $query SQL query
     * @param array $params Parameter untuk query
     * @return array|false Satu baris hasil atau false jika tidak ada
     */
    public function fetch($query, $params = []) {
        if (extension_loaded('pdo_mysql') && $this->connection instanceof PDO) {
            return $this->query($query, $params)->fetch();
        } else if ($this->connection instanceof mysqli) {
            $stmt = $this->query($query, $params);
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row;
        }
        
        return false;
    }
    
    /**
     * Mendapatkan semua baris hasil
     * 
     * @param string $query SQL query
     * @param array $params Parameter untuk query
     * @return array Semua baris hasil
     */
    public function fetchAll($query, $params = []) {
        if (extension_loaded('pdo_mysql') && $this->connection instanceof PDO) {
            return $this->query($query, $params)->fetchAll();
        } else if ($this->connection instanceof mysqli) {
            $stmt = $this->query($query, $params);
            $result = $stmt->get_result();
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $rows;
        }
        
        return [];
    }
    
    /**
     * Mendapatkan nilai tunggal
     * 
     * @param string $query SQL query
     * @param array $params Parameter untuk query
     * @return mixed Nilai tunggal hasil query
     */
    public function fetchColumn($query, $params = []) {
        if (extension_loaded('pdo_mysql') && $this->connection instanceof PDO) {
            return $this->query($query, $params)->fetchColumn();
        } else if ($this->connection instanceof mysqli) {
            $stmt = $this->query($query, $params);
            $result = $stmt->get_result();
            $row = $result->fetch_array(MYSQLI_NUM);
            $stmt->close();
            return $row ? $row[0] : false;
        }
        
        return false;
    }
    
    /**
     * Mendapatkan ID terakhir yang dimasukkan
     * 
     * @return string|int Last insert ID
     */
    public function lastInsertId() {
        if (extension_loaded('pdo_mysql') && $this->connection instanceof PDO) {
            return $this->connection->lastInsertId();
        } else if ($this->connection instanceof mysqli) {
            return $this->connection->insert_id;
        }
        
        return 0;
    }
    
    /**
     * Memulai transaksi database
     */
    public function beginTransaction() {
        if (extension_loaded('pdo_mysql') && $this->connection instanceof PDO) {
            return $this->connection->beginTransaction();
        } else if ($this->connection instanceof mysqli) {
            return $this->connection->begin_transaction();
        }
        
        return false;
    }
    
    /**
     * Commit transaksi database
     */
    public function commit() {
        if (extension_loaded('pdo_mysql') && $this->connection instanceof PDO) {
            return $this->connection->commit();
        } else if ($this->connection instanceof mysqli) {
            return $this->connection->commit();
        }
        
        return false;
    }
    
    /**
     * Rollback transaksi database
     */
    public function rollback() {
        if (extension_loaded('pdo_mysql') && $this->connection instanceof PDO) {
            return $this->connection->rollBack();
        } else if ($this->connection instanceof mysqli) {
            return $this->connection->rollback();
        }
        
        return false;
    }
} 