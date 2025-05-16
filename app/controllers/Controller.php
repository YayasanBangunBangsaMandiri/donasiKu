<?php
namespace App\Controllers;

/**
 * Controller dasar yang akan diwarisi oleh semua controller
 */
class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = \Database::getInstance();
    }
    
    /**
     * Render view dengan data
     * 
     * @param string $view Path ke file view
     * @param array $data Data yang akan digunakan dalam view
     * @return void
     */
    protected function view($view, $data = []) {
        // Ekstrak data untuk digunakan dalam view
        extract($data);
        
        // Path lengkap ke file view
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        // Cek apakah file view ada
        if (!file_exists($viewPath)) {
            die("View tidak ditemukan: $viewPath");
        }
        
        // Mulai output buffering
        ob_start();
        
        // Include view
        include $viewPath;
        
        // Ambil konten dari buffer dan bersihkan buffer
        $content = ob_get_clean();
        
        // Output konten
        echo $content;
    }
    
    /**
     * Redirect ke URL lain
     * 
     * @param string $url URL tujuan
     * @return void
     */
    protected function redirect($url) {
        header("Location: " . BASE_URL . '/' . $url);
        exit;
    }
    
    /**
     * Kirim respons JSON
     * 
     * @param mixed $data Data yang akan dikirim sebagai JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Validasi input
     * 
     * @param array $data Data yang akan divalidasi
     * @param array $rules Aturan validasi
     * @return array Array berisi error jika ada
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $ruleList = explode('|', $rule);
            
            foreach ($ruleList as $singleRule) {
                // Cek apakah rule memiliki parameter
                if (strpos($singleRule, ':') !== false) {
                    list($ruleName, $ruleParam) = explode(':', $singleRule);
                } else {
                    $ruleName = $singleRule;
                    $ruleParam = null;
                }
                
                // Validasi berdasarkan jenis rule
                switch ($ruleName) {
                    case 'required':
                        if (!isset($data[$field]) || empty(trim($data[$field]))) {
                            $errors[$field][] = "Field $field wajib diisi";
                        }
                        break;
                        
                    case 'email':
                        if (isset($data[$field]) && !empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "Field $field harus berupa email yang valid";
                        }
                        break;
                        
                    case 'min':
                        if (isset($data[$field]) && strlen($data[$field]) < $ruleParam) {
                            $errors[$field][] = "Field $field minimal harus $ruleParam karakter";
                        }
                        break;
                        
                    case 'max':
                        if (isset($data[$field]) && strlen($data[$field]) > $ruleParam) {
                            $errors[$field][] = "Field $field maksimal $ruleParam karakter";
                        }
                        break;
                        
                    case 'numeric':
                        if (isset($data[$field]) && !is_numeric($data[$field])) {
                            $errors[$field][] = "Field $field harus berupa angka";
                        }
                        break;
                        
                    case 'min_value':
                        if (isset($data[$field]) && is_numeric($data[$field]) && $data[$field] < $ruleParam) {
                            $errors[$field][] = "Field $field minimal bernilai $ruleParam";
                        }
                        break;
                        
                    case 'max_value':
                        if (isset($data[$field]) && is_numeric($data[$field]) && $data[$field] > $ruleParam) {
                            $errors[$field][] = "Field $field maksimal bernilai $ruleParam";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Mendapatkan input dari request
     * 
     * @param string $key Nama field
     * @param mixed $default Nilai default jika field tidak ada
     * @return mixed
     */
    protected function input($key, $default = null) {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        } else if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        
        return $default;
    }
    
    /**
     * Mendapatkan semua input dari request
     * 
     * @return array
     */
    protected function allInput() {
        return array_merge($_GET, $_POST);
    }
    
    /**
     * Set flash message ke session
     * 
     * @param string $key Kunci pesan
     * @param string $message Isi pesan
     * @return void
     */
    protected function setFlash($key, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        
        $_SESSION['flash'][$key] = $message;
    }
    
    /**
     * Mendapatkan flash message dari session
     * 
     * @param string $key Kunci pesan
     * @return string|null
     */
    protected function getFlash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        
        return null;
    }
    
    /**
     * Cek apakah request adalah AJAX
     * 
     * @return bool
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Enkripsi data
     * 
     * @param string $data Data yang akan dienkripsi
     * @return string
     */
    protected function encrypt($data) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Dekripsi data
     * 
     * @param string $data Data terenkripsi
     * @return string
     */
    protected function decrypt($data) {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
    }
} 