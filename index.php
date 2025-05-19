<?php
/**
 * DonateHub - Platform Donasi Online
 * Entry point utama aplikasi
 */

// Memulai session
session_start();

// Load autoloader dari Composer
require_once __DIR__ . '/vendor/autoload.php';

// Pastikan config dimuat
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
}

// Add error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug log function
function debug_log($message) {
    // Only log if debugging is enabled
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        $logFile = __DIR__ . '/debug.log';
        // Use error suppression operator to prevent warnings
        @file_put_contents($logFile, date('Y-m-d H:i:s') . ': ' . $message . "\n", FILE_APPEND);
    }
}

// Parse URL
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Log the URL being processed
debug_log('Processing URL: ' . print_r($url, true));

// Debug info (untuk debugging)
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "URL: " . print_r($url, true) . "\n";
    echo "Controller: " . (isset($url[0]) ? $url[0] : 'Home') . "\n";
    echo "Method: " . (isset($url[1]) ? $url[1] : 'index') . "\n";
    echo "Params: " . print_r(array_slice($url, 2), true) . "\n";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "</pre>";
    exit;
}

// Tentukan controller
$controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : 'HomeController';
$controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';

// Tentukan method
$method = isset($url[1]) ? $url[1] : 'index';

// Jika method mengandung tanda '-', konversi ke underscore
$method = str_replace('-', '_', $method);

// Parameter
$params = array_slice($url, 2);

// Log controller, method, and params information
debug_log("Controller: $controllerName, Method: $method, Params: " . print_r($params, true));
debug_log("Controller file exists: " . (file_exists($controllerFile) ? 'Yes' : 'No'));

// Periksa apakah file controller ada
if (!file_exists($controllerFile)) {
    debug_log("Controller file not found: $controllerFile");
    $controllerName = 'ErrorController';
    $controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';
    $method = 'notFound';
    $params = [];
}

// Buat namespace untuk controller
$controllerNamespace = 'App\\Controllers\\' . $controllerName;

// Tambahkan error handling untuk class not found
try {
    // Periksa apakah class ada
    if (!class_exists($controllerNamespace)) {
        debug_log("Controller class not found: $controllerNamespace");
        throw new Exception("Controller class not found: $controllerNamespace");
    }
    
    // Inisialisasi controller
    $controller = new $controllerNamespace();
    
    // Periksa apakah method ada
    if (!method_exists($controller, $method)) {
        debug_log("Method not found: $controllerNamespace::$method");
        
        // Special case for donation/form/slug - we need to preserve the slug
        if ($controllerName === 'DonationController' && $method === 'form' && !empty($params)) {
            debug_log("Special case for donation form with slug: " . print_r($params, true));
            $controllerName = 'ErrorController';
            $controllerNamespace = 'App\\Controllers\\' . $controllerName;
            $controller = new $controllerNamespace();
            $method = 'notFound';
            $params = [];
        } else {
            debug_log("Redirecting to error controller for method not found");
            $controllerName = 'ErrorController';
            $controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';
            $method = 'notFound';
            $params = [];
            
            // Inisialisasi controller error
            $controllerNamespace = 'App\\Controllers\\' . $controllerName;
            $controller = new $controllerNamespace();
        }
    } else {
        debug_log("Method exists: $controllerNamespace::$method");
    }
} catch (Throwable $e) {
    // Log error
    debug_log("Routing error: " . $e->getMessage());
    error_log("Routing error: " . $e->getMessage());
    
    // Route to error controller
    $controllerName = 'ErrorController';
    $controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';
    $method = 'notFound';
    $params = [];
    
    // Inisialisasi controller error
    $controllerNamespace = 'App\\Controllers\\' . $controllerName;
    $controller = new $controllerNamespace();
}

// Log the final routing decision
debug_log("Final routing: $controllerNamespace::$method with params: " . print_r($params, true));

// Jalankan method dengan parameter
call_user_func_array([$controller, $method], $params); 